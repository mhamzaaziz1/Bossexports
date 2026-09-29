<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Daraz_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get categories
     */
    public function get_categories($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'daraz_categories')->row();
        }
        return $this->db->get(db_prefix() . 'daraz_categories')->result_array();
    }

    /**
     * Get item with daraz data
     */
    public function get_item_with_daraz($id)
    {
        $this->db->select(db_prefix() . 'items.*, ' . db_prefix() . 'daraz_categories.name as daraz_category_name, ' . db_prefix() . 'daraz_categories.commission_percentage');
        $this->db->from(db_prefix() . 'items');
        $this->db->join(db_prefix() . 'daraz_categories', db_prefix() . 'daraz_categories.id = ' . db_prefix() . 'items.daraz_category_id', 'left');
        $this->db->where(db_prefix() . 'items.id', $id);
        return $this->db->get()->row();
    }
    
    /**
    * Add/Edit Category logic
    */
    public function add_category($data)
    {
        $this->db->insert(db_prefix() . 'daraz_categories', $data);
        return $this->db->insert_id();
    }
}
