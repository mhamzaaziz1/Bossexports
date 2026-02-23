<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Landed_cost_engine
{
    private $ci;
    
    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('shipments/shipments_model');
        
        if (!extension_loaded('bcmath')) {
            throw new Exception('The BCMath extension is required for this module.');
        }
    }

    /**
     * Main entry point to calculate the shipment costs
     * @param int $shipment_id
     * @return array Calculation results
     */
    public function calculate_shipment($shipment_id)
    {
        $shipment = $this->ci->shipments_model->get($shipment_id);
        if (!$shipment) {
            return ['error' => 'Shipment not found'];
        }
        
        // Hydrate data
        $shipment->lines = $this->ci->shipments_model->get_lines($shipment_id);
        $shipment->costs = $this->ci->shipments_model->get_costs($shipment_id);

        // 1. Normalize Currencies & Base Values (Layer 1 FOB)
        // For now assuming all lines are already in base currency or converted at input time
        // In a real scenario we'd do currency conversion here if lines had different currencies.
        // We will sum up the total FOB value, Weight, Volume
        
        $totals = [
            'fob' => '0',
            'weight' => '0',
            'volume' => '0',
            'qty' => '0'
        ];
        
        foreach ($shipment->lines as $line) {
            $totals['fob'] = bcadd($totals['fob'], bcmul($line['qty_shipped'], $line['unit_fob_price'], 4), 4);
            $totals['weight'] = bcadd($totals['weight'], $line['net_weight_kg'], 4);
            $totals['volume'] = bcadd($totals['volume'], $line['volume_cbm'], 4);
            $totals['qty'] = bcadd($totals['qty'], $line['qty_shipped'], 4);
        }
        
        // 2. Calculate Costs (Layer 2 CIF, Layer 3 Duty, Layer 4 Landed)
        // We need to iterate through costs and allocate them to lines
        
        $lines_calculated = [];
        foreach($shipment->lines as $line) {
             $lines_calculated[$line['id']] = [
                 'id' => $line['id'],
                 'item_name' => isset($line['item_name']) ? $line['item_name'] : 'Item #' . $line['item_id'],
                 'qty_shipped' => $line['qty_shipped'],
                 'base_fob' => bcmul($line['qty_shipped'], $line['unit_fob_price'], 4),
                 'allocated_costs' => '0',
                 'duty_amount' => '0',
                 'total_landed' => '0',
                 'unit_landed' => '0'
             ];
        }

        foreach ($shipment->costs as $cost) {
            // Normalize cost amount to base currency
            // cost->total_amount is in cost->currency. 
            // cost->exchange_rate is rate to convert cost->currency to Base.
            // Formula: Amount * Rate (if Rate is "Foreign to Base")
            // We'll assume exchange_rate is Multiplier.
            
            $cost_amount_base = bcmul($cost['total_amount'], $cost['exchange_rate'], 4);
            
            // Determine Allocation Method
            $method = $cost['allocation_method'] ? $cost['allocation_method'] : $cost['allocation_default']; // fallback to def
            
            // Allocate this cost to all lines
            $allocated = $this->allocate_cost($cost_amount_base, $method, $shipment->lines, $totals);
            
            // Add to lines
            foreach ($allocated as $line_id => $amount) {
                $lines_calculated[$line_id]['allocated_costs'] = bcadd($lines_calculated[$line_id]['allocated_costs'], $amount, 4);
                
                // If this is a PRE-DUTY cost (Layer 2), it contributes to Duty Base? 
                // Creating a simplified model:
                // Layer 2 (Freight/Ins) -> included in Duty calculation
                // Layer 3 (Duty) -> is the Duty itself
                // Layer 4 (Post-Duty) -> added after duty
                
                // We need to know the layer of the cost.
                // $cost['layer_level']
                
                // Actually, to do this correctly, we should separate costs by layer.
                // But for now, let's just track "Allocated Cost" generic.
                // Wait, User Story 2: "System distinguishes between Pre-Duty Costs and Post-Duty Costs"
                
                if($cost['layer_level'] == 2) {
                    $lines_calculated[$line_id]['cif_cost'] = isset($lines_calculated[$line_id]['cif_cost']) ? 
                        bcadd($lines_calculated[$line_id]['cif_cost'], $amount, 4) : $amount;
                } else if ($cost['layer_level'] == 4) {
                    $lines_calculated[$line_id]['post_duty_cost'] = isset($lines_calculated[$line_id]['post_duty_cost']) ? 
                        bcadd($lines_calculated[$line_id]['post_duty_cost'], $amount, 4) : $amount;
                }
            }
        }
        
        // 3. Calculate Duty (Layer 3)
        // Duty = (Base FOB + Layer 2 Costs) * Duty Rate
        foreach($lines_calculated as $line_id => &$vals) {
            $base = $vals['base_fob'];
            $cif_add = isset($vals['cif_cost']) ? $vals['cif_cost'] : 0;
            
            $duty_base = bcadd($base, $cif_add, 4);
            
            // Find the line to get duty percent
            $line_ref = $this->find_line($shipment->lines, $line_id);
            $duty_rate = bcdiv($line_ref['duty_percent'], '100', 4);
            
            $vals['duty_amount'] = bcmul($duty_base, $duty_rate, 4);
            
            // 4. Final Sum (Layer 4)
            // Landed Cost = Base FOB + All Allocated Costs + Duty Amount
            $vals['total_landed'] = bcadd($vals['base_fob'], $vals['allocated_costs'], 4);
            $vals['total_landed'] = bcadd($vals['total_landed'], $vals['duty_amount'], 4);
            
            // Unit Cost
            if($line_ref['qty_shipped'] > 0) {
                $vals['unit_landed'] = bcdiv($vals['total_landed'], $line_ref['qty_shipped'], 4);
            }
        }
        
        return $lines_calculated;
    }
    
    private function find_line($lines, $id) {
        foreach($lines as $line) {
            if($line['id'] == $id) return $line;
        }
        return null;
    }

    /**
     * Allocates a total amount across lines based on a method
     */
    private function allocate_cost($total_amount, $method, $lines, $totals)
    {
        $allocation = [];
        $running_total = '0';
        $count = count($lines);
        $i = 0;
        
        foreach ($lines as $line) {
            $i++;
            $ratio = '0';
            
            if ($method == 'value') {
                $line_val = bcmul($line['qty_shipped'], $line['unit_fob_price'], 4);
                if($totals['fob'] > 0) $ratio = bcdiv($line_val, $totals['fob'], 10);
            } elseif ($method == 'weight') {
                if($totals['weight'] > 0) $ratio = bcdiv($line['net_weight_kg'], $totals['weight'], 10);
            } elseif ($method == 'volume') {
                if($totals['volume'] > 0) $ratio = bcdiv($line['volume_cbm'], $totals['volume'], 10);
            } elseif ($method == 'quantity') {
                if($totals['qty'] > 0) $ratio = bcdiv($line['qty_shipped'], $totals['qty'], 10);
            }
            
            if ($i == $count) {
                // Last item gets the remainder to ensure precision sum matches exactly
                $allocation[$line['id']] = bcsub($total_amount, $running_total, 4);
            } else {
                $share = bcmul($total_amount, $ratio, 4);
                $allocation[$line['id']] = $share;
                $running_total = bcadd($running_total, $share, 4);
            }
        }
        
        return $allocation;
    }
}
