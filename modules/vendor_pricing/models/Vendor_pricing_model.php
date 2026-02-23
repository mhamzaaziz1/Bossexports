<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vendor_pricing_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Submit vendor pricing for a PO
     * @param  int $po_id
     * @param  array $data (item_code => vendor_price)
     * @return boolean
     */
    public function submit_vendor_pricing($po_id, $data)
    {
        $affectedRows = 0;
        
        // Remove existing pending submissions for this PO
        $this->db->where('pur_order_id', $po_id);
        $this->db->where('status', 'pending');
        $this->db->delete(db_prefix() . 'vendor_pricing_po_details');

        foreach ($data as $item_code => $price) {
            $insert_data = [
                'pur_order_id' => $po_id,
                'item_code'    => $item_code,
                'vendor_price' => $price,
                'status'       => 'pending',
                'date_submitted' => date('Y-m-d H:i:s')
            ];

            $this->db->insert(db_prefix() . 'vendor_pricing_po_details', $insert_data);
            $affectedRows++;
        }

        return $affectedRows > 0;
    }

    /**
     * Get submitted vendor pricing for a specific PO
     */
    public function get_vendor_pricing($po_id)
    {
        $this->db->where('pur_order_id', $po_id);
        return $this->db->get(db_prefix() . 'vendor_pricing_po_details')->result_array();
    }

    /**
     * Get all POs that have vendor pricing submissions
     */
    public function get_pos_with_pricing()
    {
        $this->db->select('v.pur_order_id, v.status, MAX(v.date_submitted) as date_submitted, p.pur_order_number, p.pur_order_name, v2.company as vendor_name');
        $this->db->from(db_prefix() . 'vendor_pricing_po_details v');
        $this->db->join(db_prefix() . 'pur_orders p', 'p.id = v.pur_order_id', 'left');
        $this->db->join(db_prefix() . 'pur_vendor v2', 'v2.userid = p.vendor', 'left');
        $this->db->group_by('v.pur_order_id, v.status, p.pur_order_number, p.pur_order_name, v2.company');
        $this->db->order_by('date_submitted', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Accept vendor pricing
     */
    public function accept_vendor_pricing($po_id)
    {
        $vendor_prices = $this->get_vendor_pricing($po_id);
        if(empty($vendor_prices)) return false;

        $this->load->model('purchase/purchase_model');

        foreach ($vendor_prices as $vp) {
            $this->db->where('pur_order', $po_id);
            $this->db->where('item_code', $vp['item_code']);
            $detail = $this->db->get(db_prefix() . 'pur_order_detail')->row();
            
            if ($detail) {
                $qty = $detail->quantity;
                $new_price = $vp['vendor_price'];
                $into_money = $qty * $new_price;
                
                $total = $into_money; // Assuming basic setup where no tax logic strictly applies to the core row unless recalced natively
                
                $this->db->where('pur_order', $po_id);
                $this->db->where('item_code', $vp['item_code']);
                $this->db->update(db_prefix() . 'pur_order_detail', [
                    'unit_price' => $new_price,
                    'into_money' => $into_money,
                    'total'      => $total,
                    'total_money'=> $total
                ]);
            }
        }

        // Recalculate PO totals
        $this->db->select_sum('total');
        $this->db->select_sum('into_money');
        $this->db->where('pur_order', $po_id);
        $res = $this->db->get(db_prefix() . 'pur_order_detail')->row();

        if ($res) {
            $this->db->where('id', $po_id);
            $this->db->update(db_prefix() . 'pur_orders', [
                'subtotal' => $res->into_money,
                'total'    => $res->total
            ]);
        }

        $this->db->where('pur_order_id', $po_id);
        $this->db->update(db_prefix() . 'vendor_pricing_po_details', ['status' => 'accepted']);

        return true;
    }

    /**
     * Reject vendor pricing
     */
    public function reject_vendor_pricing($po_id)
    {
        $this->db->where('pur_order_id', $po_id);
        $this->db->update(db_prefix() . 'vendor_pricing_po_details', ['status' => 'rejected']);
        return true;
    }
}
