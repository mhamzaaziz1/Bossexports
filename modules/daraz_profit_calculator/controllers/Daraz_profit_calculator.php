<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Daraz_profit_calculator extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('daraz_model');
    }

    /**
     * Dashboard / Calculator
     */
    public function index()
    {
        $data['title'] = 'Daraz Profit Calculator';
        $data['categories'] = $this->daraz_model->get_categories();
        $this->load->view('daraz_calculator', $data);
    }

    /**
     * Manage Categories
     */
    public function categories()
    {
        if ($this->input->post()) {
            if (!$this->input->post('id')) {
                $this->daraz_model->add_category($this->input->post());
                set_alert('success', 'Category added successfully');
            } else {
                // Update logic...
            }
            redirect(admin_url('daraz_profit_calculator/categories'));
        }

        $data['title'] = 'Daraz Categories';
        $data['categories'] = $this->daraz_model->get_categories();
        $this->load->view('categories', $data);
    }

    /**
     * Settings
     */
    public function settings()
    {
        $data['title'] = 'Daraz Settings';
        $this->load->view('settings', $data);
    }

    /**
     * AJAX endpoint to get item category
     */
    public function get_item_category($id)
    {
        $this->db->select('daraz_category_id');
        $this->db->where('id', $id);
        $item = $this->db->get(db_prefix() . 'items')->row();
        echo json_encode(['daraz_category_id' => ($item ? $item->daraz_category_id : '')]);
    }

    /**
     * Delete Category
     */
    public function delete_category($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'daraz_categories');
        set_alert('success', 'Category deleted successfully');
        redirect(admin_url('daraz_profit_calculator/categories'));
    }
}
