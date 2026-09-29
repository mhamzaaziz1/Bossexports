<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Custom_flyout_menu extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        
        // Check if user has proper permissions using Perfex's built-in functions
        if (!is_admin() && !has_permission('settings', '', 'view')) {
            access_denied('custom_flyout_menu');
        }
        
        $this->load->model('custom_flyout_menu_model');
    }
    
    // ========== PAGES ==========
    
    public function admin_menu()
    {
        $data['title'] = _l('cfm_admin_menu_builder');
        $data['area'] = 'admin';
        $this->load->view('admin/menu_builder', $data);
    }
    
    public function client_menu()
    {
        $data['title'] = _l('cfm_client_menu_builder');
        $data['area'] = 'client';
        $this->load->view('admin/menu_builder', $data);
    }
    
    public function settings()
    {
        if ($this->input->post()) {
            $settings = $this->input->post('settings');
            if ($settings && is_array($settings)) {
                foreach ($settings as $key => $value) {
                    cfm_update_setting($key, $value);
                }
                set_alert('success', _l('settings_updated'));
                redirect(admin_url('custom_flyout_menu/settings'));
            }
        }
        
        $data['title'] = _l('cfm_settings');
        $this->load->view('admin/settings', $data);
    }
    
    // ========== AJAX API ==========
    
    public function get_menu_structure()
    {
        $area = $this->input->get('area') ?: 'admin';
        $data = $this->custom_flyout_menu_model->get_full_structure($area, true);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    
    public function save_group()
    {
        if (!is_admin() && !has_permission('settings', '', 'edit')) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            exit;
        }
        
        if ($this->input->post()) {
            $data = $this->input->post();
            $id = $this->custom_flyout_menu_model->save_group($data);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'id' => $id]);
        }
    }
    
    public function delete_group($id)
    {
        if (!is_admin() && !has_permission('settings', '', 'delete')) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            exit;
        }
        
        $result = $this->custom_flyout_menu_model->delete_group($id);
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }
    
    public function save_item()
    {
        if (!is_admin() && !has_permission('settings', '', 'edit')) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            exit;
        }
        
        if ($this->input->post()) {
            $data = $this->input->post();
            $id = $this->custom_flyout_menu_model->save_item($data);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'id' => $id]);
        }
    }
    
    public function delete_item($id)
    {
        if (!is_admin() && !has_permission('settings', '', 'delete')) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            exit;
        }
        
        $result = $this->custom_flyout_menu_model->delete_item($id);
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }
    
    public function update_sort_order()
    {
        if (!is_admin() && !has_permission('settings', '', 'edit')) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            exit;
        }
        
        $items = $this->input->post('items');
        if ($items && is_array($items)) {
            $result = $this->custom_flyout_menu_model->update_sort_orders($items);
            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
        }
    }
    
    public function toggle_group_status($id = '')
    {
        if (!is_admin() && !has_permission('settings', '', 'edit')) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            exit;
        }
        
        if ($id) {
            $group = $this->db->get_where(db_prefix() . 'cfm_menu_groups', ['id' => $id])->row();
            if ($group) {
                $new_status = $group->active == 1 ? 0 : 1;
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'cfm_menu_groups', ['active' => $new_status]);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'status' => $new_status]);
                exit;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    }
    
    public function toggle_item_status($id = '')
    {
        if (!is_admin() && !has_permission('settings', '', 'edit')) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            exit;
        }
        
        if ($id) {
            $item = $this->db->get_where(db_prefix() . 'cfm_menu_items', ['id' => $id])->row();
            if ($item) {
                $new_status = $item->active == 1 ? 0 : 1;
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'cfm_menu_items', ['active' => $new_status]);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'status' => $new_status]);
                exit;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    }
}
