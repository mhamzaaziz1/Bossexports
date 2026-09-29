<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Logistics extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_item_logistics($id)
    {
        if ($this->input->is_ajax_request()) {
            $this->db->select('weight, volume');
            $this->db->where('id', $id);
            $item = $this->db->get(db_prefix() . 'items')->row();

            echo json_encode([
                'success' => true,
                'weight' => $item ? $item->weight : 0,
                'volume' => $item ? $item->volume : 0,
            ]);
        }
    }
}
