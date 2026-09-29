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
                 'id'               => $line['id'],
                 'item_name'        => isset($line['item_name']) ? $line['item_name'] : 'Item #' . $line['item_id'],
                 'qty_shipped'      => $line['qty_shipped'],
                 'base_fob'         => bcmul($line['qty_shipped'], $line['unit_fob_price'], 4),
                 'freight_cost'     => '0',
                 'emenumentus_cost' => '0',
                 'cif_cost'         => '0',
                 'duty_amount'      => '0',
                 'vat_amount'       => '0',
                 'other_costs'      => '0',
                 'individual_costs' => [],
                 'total_landed'     => '0',
                 'unit_landed'      => '0'
             ];
        }

        foreach ($shipment->costs as $cost) {
            $cost_amount_base = bcmul($cost['total_amount'], $cost['exchange_rate'], 4);
            $method = $cost['allocation_method'] ? $cost['allocation_method'] : $cost['allocation_default'];
            
            // User Rule: Freight and Port Handling always distribute by price (value)
            $lowered_name = strtolower($cost['cost_name']);
            if (strpos($lowered_name, 'freight') !== false || strpos($lowered_name, 'port handling') !== false) {
                $method = 'value';
            }

            $allocated = $this->allocate_cost($cost_amount_base, $method, $shipment->lines, $totals);
            
            foreach ($allocated as $line_id => $amount) {
                $cost_name = $cost['cost_name'];
                $lines_calculated[$line_id]['individual_costs'][$cost_name] = bcadd(
                    isset($lines_calculated[$line_id]['individual_costs'][$cost_name]) ? $lines_calculated[$line_id]['individual_costs'][$cost_name] : '0',
                    $amount,
                    4
                );

                if (strpos($lowered_name, 'freight') !== false) {
                    $lines_calculated[$line_id]['freight_cost'] = bcadd($lines_calculated[$line_id]['freight_cost'], $amount, 4);
                } elseif (strpos($lowered_name, 'port handling') !== false) {
                    $lines_calculated[$line_id]['other_costs'] = bcadd($lines_calculated[$line_id]['other_costs'], $amount, 4);
                } else {
                    $lines_calculated[$line_id]['other_costs'] = bcadd($lines_calculated[$line_id]['other_costs'], $amount, 4);
                }
            }
        }
        
        // 3. Finalize Formulas per item
        foreach($lines_calculated as $line_id => &$vals) {
            // Formula: CIF = FOB + Freight + Emenumentus (2% of FOB + Freight)
            $fob_plus_freight = bcadd($vals['base_fob'], $vals['freight_cost'], 4);
            
            // Emenumentus is 2% for (fob + freight)
            $vals['emenumentus_cost'] = bcmul($fob_plus_freight, '0.02', 4);
            
            // CIF = FOB + Freight + Emenumentus
            $vals['cif_cost'] = bcadd($fob_plus_freight, $vals['emenumentus_cost'], 4);
            
            // 4. Customs: CIF * duty_percent
            $line_ref = $this->find_line($shipment->lines, $line_id);
            $duty_rate = bcdiv($line_ref['duty_percent'], '100', 4);
            $vals['duty_amount'] = bcmul($vals['cif_cost'], $duty_rate, 4);
            
            // 5. VAT: vat%age * SUM(custom + CIF)
            $vat_base = bcadd($vals['cif_cost'], $vals['duty_amount'], 4);
            $vat_rate = bcdiv($line_ref['vat_percent'], '100', 4);
            $vals['vat_amount'] = bcmul($vat_base, $vat_rate, 4);
            
            // 6. Total Landed: CIF + Customs + VAT + Port/Other
            $vals['total_landed'] = bcadd($vals['cif_cost'], $vals['duty_amount'], 4);
            $vals['total_landed'] = bcadd($vals['total_landed'], $vals['vat_amount'], 4);
            $vals['total_landed'] = bcadd($vals['total_landed'], $vals['other_costs'], 4);
            
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
