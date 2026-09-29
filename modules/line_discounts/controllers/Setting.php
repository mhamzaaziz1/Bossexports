<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Setting extends AdminController {


    public function __construct()
    {

        parent::__construct();

        if ( staff_can('discount_setting',  'discount_setting'))
        {

        }
        else
            access_denied('line_discount_setting');


    }

    public function index()
    {

        $data['title'] = _l('line_discount_setting');

        $this->load->view('v_settings' , $data);

    }


    public function save_setting()
    {

        if ( $this->input->is_ajax_request() )
        {

            $opt_name = $this->input->post('name');
            $value = $this->input->post('value');

            if ( is_array( $value ) )
                $value = json_encode( $value );


            line_discount_update_option_value( $opt_name , $value );

            echo json_encode( [  'message' => _l('updated_successfully' , _l('setting') ) ] );

        }

    }


}
