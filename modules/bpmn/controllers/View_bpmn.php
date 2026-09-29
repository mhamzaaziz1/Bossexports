<?php

//defined('BASEPATH') or exit('No direct script access allowed');

$check =  __dir__ ;


class View_bpmn extends App_Controller {

	public function __construct()
    {
        parent::__construct();
    }

    public function bpmn($id, $hash)
    {
    	$CI = &get_instance();
		$CI->db->where('id', $id);
        $CI->db->where('hash', $hash);
        $data['bpmn'] = $CI->db->get(db_prefix() . 'bpmn')->row();
		$this->load->view('bpmn_preview', $data);
		
	}
}