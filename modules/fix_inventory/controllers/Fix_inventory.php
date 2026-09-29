<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fix_inventory extends AdminController
{
    public function __construct() {
        parent::__construct();
        $this->load->model('fix_inventory/fix_inventory_model');
    }

    /**
     * Main dashboard
     */
    public function index() {
        if (!is_admin()) {
            access_denied('fix_inventory');
        }

        $data['title'] = 'Fix Inventory Discrepancies';
        $data['discrepancies'] = $this->fix_inventory_model->get_reconciliation();
        $this->load->view('fix_inventory/manage', $data);
    }

    /**
     * Fix item inventory
     */
    public function fix_item() {
        if (!is_admin()) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            die;
        }

        $item_id = $this->input->post('item_id');
        $warehouse_id = $this->input->post('warehouse_id');
        $success = $this->fix_inventory_model->fix_item($item_id, $warehouse_id);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Item fixed successfully' : 'Failed to fix item'
        ]);
    }

    /**
     * Fix all
     */
    public function fix_all() {
        if (!is_admin()) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            die;
        }

        $success = $this->fix_inventory_model->fix_all();
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'All inventory gaps fixed successfully' : 'Failed to fix all gaps'
        ]);
    }

    /**
     * Audit Report
     */
    public function audit_report() {
        if (!is_admin()) {
            access_denied('fix_inventory');
        }

        $item_id = $this->input->get('item_id');
        $warehouse_id = $this->input->get('warehouse_id');

        $data['title'] = 'Product Audit Report';
        $data['selected_item'] = $item_id;
        $data['selected_warehouse'] = $warehouse_id;

        // Get items and warehouses for dropdowns
        $data['items'] = $this->db->get(db_prefix().'items')->result_array();
        $data['warehouses'] = $this->db->get(db_prefix().'warehouse')->result_array();

        if ($item_id) {
            $data['history'] = $this->fix_inventory_model->get_audit_history($item_id, $warehouse_id);

            // Get actual quantity
            $this->db->select('SUM(inventory_number) as actual_qty');
            $this->db->where('commodity_id', $item_id);
            if ($warehouse_id != '') {
                $this->db->where('warehouse_id', $warehouse_id);
            }
            $actual = $this->db->get(db_prefix().'inventory_manage')->row();
            $data['actual_qty'] = $actual ? (float)$actual->actual_qty : 0;
        } else {
            $data['history'] = [];
            $data['actual_qty'] = 0;
        }

        $this->load->view('fix_inventory/audit_report', $data);
    }
}
