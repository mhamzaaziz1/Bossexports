<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Payments extends ClientsController
{
 

	public function gocardlessPayment() {
       
        $gateway = $this->payment_modes_model->get('gocardless');
        $gateway->instance->complete_client( $this->input->get('redirect_flow_id') );

        $invoice_id = $_SESSION['invoice_id'];

        $amount =  $this->db->select('amount');
        $this->db->from('tblinvoicepaymentrecords');
        $this->db->where('invoiceid',$_SESSION['data']['invoiceid']);
        $amount = $this->db->get()->result();

        $totPayedAmmount = 0;
        foreach ($amount as $am) {
            $totPayedAmmount =  $totPayedAmmount + $am->amount;
        }
        

        $data_tblinvoice = [];
        if ((float)($_SESSION['data']['amount'] + $totPayedAmmount."<br>") == (float)($_SESSION['data']['invoice']->total)) {
            $data_tblinvoices = array(
                'status'=>2
            );      
        }
        else {
             $data_tblinvoices = array(
                'status'=>3
            );
        }
        

        $this->db->where('id', $_SESSION['data']['invoiceid']);
        $this->db->update('tblinvoices',$data_tblinvoices);


        $data_payments = array(
            'invoiceid'=> $_SESSION['data']['invoiceid'],
            'amount' => $_SESSION['data']['amount'],
            'paymentmode' => $_SESSION['data']['paymentmode'],
            'daterecorded' => $_SESSION['data']['invoice']->datecreated,
            'date' => $_SESSION['data']['invoice']->date,
            'transactionid' => $_SESSION['payment']->records[0]->id,

        );

        $this->db->set($data_payments);
        $this->db->insert('tblinvoicepaymentrecords');

        redirect($_SESSION['url']);
    }

}