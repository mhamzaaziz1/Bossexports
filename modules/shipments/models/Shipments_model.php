<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Shipments_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get shipment by ID
     */
    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            $shipment = $this->db->get(db_prefix() . 'shipment_headers')->row();

            if ($shipment) {
                $shipment->lines = $this->get_lines($id);
                $shipment->costs = $this->get_costs($id);
            }
            return $shipment;
        }

        return $this->db->get(db_prefix() . 'shipment_headers')->result();
    }

    /**
     * Add new shipment
     */
    public function add($data)
    {
        $ignore = ['shipment_id', 'new_qty', 'new_fob', 'new_weight', 'new_volume', 'new_duty', 'cost_amount', 'cost_currency', 'cost_rate', 'edit_qty', 'edit_fob', 'edit_weight', 'edit_volume', 'edit_duty'];
        foreach($ignore as $key){
            if(isset($data[$key])) unset($data[$key]);
        }
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'shipment_headers', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Shipment Created [ID: ' . $insert_id . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * Update shipment
     */
    public function update($data, $id)
    {
        $ignore = ['shipment_id', 'new_qty', 'new_fob', 'new_weight', 'new_volume', 'new_duty', 'cost_amount', 'cost_currency', 'cost_rate', 'edit_qty', 'edit_fob', 'edit_weight', 'edit_volume', 'edit_duty'];
        foreach($ignore as $key){
            if(isset($data[$key])) unset($data[$key]);
        }
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'shipment_headers', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Shipment Updated [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    /**
     * Delete shipment and all related lines/costs
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'shipment_headers');

        if ($this->db->affected_rows() > 0) {
            $this->db->where('shipment_id', $id);
            $this->db->delete(db_prefix() . 'shipment_lines');

            $this->db->where('shipment_id', $id);
            $this->db->delete(db_prefix() . 'shipment_cost_allocations');

            log_activity('Shipment Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    /**
     * Get lines for a shipment
     */
    public function get_lines($shipment_id)
    {
        $this->db->select(db_prefix() . 'shipment_lines.*, ' . db_prefix() . 'items.description as item_name, ' . db_prefix() . 'items.commodity_code');
        $this->db->join(db_prefix() . 'items', db_prefix() . 'items.id = ' . db_prefix() . 'shipment_lines.item_id', 'left');
        $this->db->where('shipment_id', $shipment_id);
        return $this->db->get(db_prefix() . 'shipment_lines')->result_array();
    }

    /**
     * Add line to shipment
     */
    public function add_line($data)
    {
        $this->db->insert(db_prefix() . 'shipment_lines', $data);
        return $this->db->insert_id();
    }

    /**
     * Update shipment line
     */
    public function update_line($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'shipment_lines', $data);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Get costs for a shipment
     */
    public function get_costs($shipment_id)
    {
        $this->db->select(db_prefix() . 'shipment_cost_allocations.*, ' . db_prefix() . 'cost_definitions.name as cost_name, ' . db_prefix() . 'cost_definitions.layer_level, ' . db_prefix() . 'cost_definitions.allocation_default');
        $this->db->join(db_prefix() . 'cost_definitions', db_prefix() . 'cost_definitions.id = ' . db_prefix() . 'shipment_cost_allocations.cost_def_id', 'left');
        $this->db->where('shipment_id', $shipment_id);
        return $this->db->get(db_prefix() . 'shipment_cost_allocations')->result_array();
    }

    /**
     * Add cost to shipment
     */
    public function add_cost($data)
    {
        $this->db->insert(db_prefix() . 'shipment_cost_allocations', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Commit Shipment (ACID Compliant)
     */
    public function commit_shipment($id)
    {
        $this->db->trans_start();

        // 1. Lock Shipment
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'shipment_headers', ['status' => 'Closed']);
        
        // 2. Calculate Final Costs
        $this->load->library('shipments/landed_cost_engine');
        $results = $this->landed_cost_engine->calculate_shipment($id);
        
        foreach($results as $line_res) {
            // Update Shipment Line with final figures
            $this->db->where('id', $line_res['id']);
            $this->db->update(db_prefix() . 'shipment_lines', [
                'landed_cost' => $line_res['total_landed'],
                // We could also save the breakdown if we expanded the table columns
            ]);
            
            // 3. Update Inventory Item Cost
            // We get the item_id from the line
            $line = $this->db->get_where(db_prefix() . 'shipment_lines', ['id' => $line_res['id']])->row();
            
            if($line && $line->item_id) {
                // Update tblitems rate/purchase_price
                // Note: 'rate' is usually Sales Price. 'purchase_price' might be a custom field or standard if using Purchase module.
                // For this core implementation, we will update 'rate' (standard field) but usually you want a separate cost field.
                // Let's assume there is a 'purchase_price' column if Purchase module is active, otherwise maybe just log it.
                // Or better, let's use a Hook to let other modules handle the actual item update if they want, 
                // but we will update the 'rate' for now as a demo or if the user wants it.
                // However, updating 'rate' (sales price) based on cost is dangerous.
                // Safe bet: Update nothing on tblitems directly unless explicitly configured, 
                // BUT the requirement says: "Update tblitems.rate (or purchase_price) with the new Landed Cost."
                
                // Let's check if purchase_price exists
                $data_to_update = [];
                if ($this->db->field_exists('purchase_price', db_prefix() . 'items')) {
                     $data_to_update['purchase_price'] = $line_res['unit_landed'];
                }
                
                // Only update if we have something to update
                if(!empty($data_to_update)) {
                    $this->db->where('id', $line->item_id);
                    $this->db->update(db_prefix() . 'items', $data_to_update);
                }
                
                // Trigger Hook for other modules (e.g. Accounting)
                hooks()->do_action('shipment_item_landed', [
                    'item_id' => $line->item_id,
                    'new_cost' => $line_res['unit_landed'],
                    'shipment_id' => $id
                ]);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return false;
        }
        
        return true;
    }
    
    /**
    * Get cost definitions
    */
    public function get_cost_definitions($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'cost_definitions')->row();
        }
        return $this->db->get(db_prefix() . 'cost_definitions')->result_array();
    }

    /**
     * Add new cost definition
     */
    public function add_cost_definition($data)
    {
        $this->db->insert(db_prefix() . 'cost_definitions', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Cost Definition Added [ID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * Update cost definition
     */
    public function update_cost_definition($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'cost_definitions', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Cost Definition Updated [ID: ' . $id . ', Name: ' . $data['name'] . ']');
            return true;
        }
        return false;
    }

    /**
     * Delete cost definition
     */
    public function delete_cost_definition($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'cost_definitions');
        if ($this->db->affected_rows() > 0) {
            log_activity('Cost Definition Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }
}
