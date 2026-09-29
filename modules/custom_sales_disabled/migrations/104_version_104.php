<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_104 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        $payment_modes = get_option('custom_sales_depoist_invoice_allowed_payment_modes');
        $payment_modes = empty($payment_modes) ? [] : json_decode($payment_modes);
        if (empty($payment_modes)) {
            $payment_modes = [];
            $CI->load->model('payment_modes_model');
            $modes = $CI->payment_modes_model->get('', [
                'expenses_only !=' => 1,
            ]);
            foreach ($modes as $mode) {
                if ($mode['selected_by_default'] == 0) {
                    continue;
                }
                $payment_modes[] = $mode['id'];
            }
        }

        $CI->invoices_model->db->where('deposit_from_estimate_id > 0');
        $invoices = $CI->invoices_model->get();

        foreach ($invoices as $key => $invoice) {
            $allowed_payment_modes = unserialize($invoice['allowed_payment_modes']) ?? [];
            if (empty($allowed_payment_modes)) {
                $CI->invoices_model->db->where('id', $invoice['id'])->update(db_prefix() . 'invoices', ['allowed_payment_modes' => serialize($payment_modes)]);
            }
        }

        require(__DIR__ . '/../install.php');
    }

    public function down()
    {
    }
}
