<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Bond_returns extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('bookings_model');
        $this->load->model('clients_model');
        $this->load->helper('date');
        $this->start_date = date("01/m/Y");
        if(date("d/m/Y") == date("01/m/Y")) $this->start_date = date("d/m/Y", mktime(0,0,0,date('n')-1, 1, date("Y")));
        $this->end_date = date("d/m/Y");
    }

    public function index()
    {
        close_setup_menu();
        $data = $this->input->get();
        if(isset($data['range'])){
            $range = explode(' - ', $data['range']);
            $this->start_date = $range[0];
            $this->end_date = $range[1];
        }
        $data['statuses'] = $this->bookings_model->get_booking_statuses();
        $data['title']    = _l('bond_returns');
        $data['start_date'] = $this->start_date;
        $data['end_date'] = $this->end_date;
        $this->load->view('admin/bond_returns/manage', $data);
    }
    
    public function table($clientid = '')
    {
        $data = $this->input->get();
        if(isset($data['range'])){
            $range = explode(' - ', $data['range']);
            $this->start_date = $range[0];
            $this->end_date = $range[1];
        }
        $this->app->get_table_data(module_views_path(BOOKING_MODULE_NAME, 'admin/tables/bond_returns'), [
            'clientid' => $clientid,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date
        ]);
    }

}
