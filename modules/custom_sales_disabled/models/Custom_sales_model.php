<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Custom_sales_model extends App_Model
{
    public $items_titles_table;

    public function __construct()
    {
        parent::__construct();
        $this->items_titles_table = db_prefix() . 'custom_sales_items_titles';
    }

    public function get_items_titles($resources_id, $resources_type)
    {
        return $this->db->where('resources', $resources_type)->where('resources_id', $resources_id)->order_by('child_item_order')->get($this->items_titles_table)->result();
    }

    public function get_title_list()
    {
        return $this->db->distinct()->select('title')->get($this->items_titles_table)->result();
    }

    public function save_items_titles($resources_id, $resources_type, $data = [])
    {
        $this->db->where('resources', $resources_type)->where('resources_id', $resources_id)->delete($this->items_titles_table);

        if (empty($data)) return true;

        foreach ($data as $row) {
            $set = [
                'resources' => $resources_type,
                'resources_id' => $resources_id,
                'child_item_order' => $row['child_item_order'],
                'title' => $row['title']
            ];

            $this->db->insert($this->items_titles_table, $set);
        }
        return true;
    }

    /**
     * Convert estimate to invoice and create deposit invoices
     *
     * @param mixed $estimate_id
     * @param array $deposits
     * @return int|bool Return the first invoice id or false.
     */
    public function create_deposit_invoices($estimate_id, $deposits)
    {
        $_invoices_id_list = [];

        // Create first invoice
        $first_invoice_id = $this->estimates_model->convert_to_invoice($estimate_id, false, false);

        if (!$first_invoice_id)
            throw new \Exception(_l("custom_sales_error_creating_first_invoice"), 1);

        $first_deposit = $deposits[0];
        $first_deposit['date'] = _d(date('Y-m-d', strtotime($first_deposit['date'])));
        $first_invoice_date = $first_deposit['date'];

        $first_invoice = $this->invoices_model->get($first_invoice_id);

        $estimate_total = $first_invoice->total;

        $payment_modes = get_option('custom_sales_depoist_invoice_allowed_payment_modes');
        $payment_modes = empty($payment_modes) ? [] : json_decode($payment_modes);
        if (empty($payment_modes)) {
            // Revert to the default
            $payment_modes = empty($first_invoice->allowed_payment_modes) ? [] : unserialize($first_invoice->allowed_payment_modes);
        }

        // Fallback to getting gateway ourselve
        if (empty($payment_modes)) {
            $payment_modes = [];
            $this->load->model('payment_modes_model');
            $modes = $this->payment_modes_model->get('', [
                'expenses_only !=' => 1,
            ]);
            foreach ($modes as $mode) {
                if ($mode['selected_by_default'] == 0) {
                    continue;
                }
                $payment_modes[] = $mode['id'];
            }
        }

        // Update invoice for the date set by user
        $data = [
            'recurring' => 0,
            'cycles' => 0,
            'total_cycles' => 0,
            'custom_recurring' => 0,
            'recurring_type' => null,
            'subtotal' => $first_deposit['amount'],
            'total' => $first_deposit['amount'],
            'deposit_from_estimate_id' => $estimate_id,
            'last_recurring_date' => $first_invoice_date,
            'date' => $first_invoice_date,
            'duedate' => $first_invoice_date,
            'billing_street' => $first_invoice->billing_street,
            'allowed_payment_modes' => $payment_modes // Always ensure this is set
        ];

        if (get_option('invoice_due_after') != 0) {
            $data['duedate'] = _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime(date('Y-m-d')))));
        }

        $this->invoices_model->update($data, $first_invoice_id);
        $this->update_sales_total_tax_column($first_invoice_id, $first_deposit['amount'], $estimate_total);


        $_invoices_id_list[] = $first_invoice_id;

        try {
            // Now add child invoices
            foreach ($deposits as $key => $deposit) {
                if ($key === 0) continue;

                $invoice_id = $this->invoices_model->copy($first_invoice_id);

                if (!$invoice_id)
                    throw new \Exception(_l("custom_sales_error_creating_deposit_invoice", $key + 1), 1);

                $deposit['date'] = _d($deposit['date']);

                $data = [
                    'recurring' => 0,
                    'date' => $deposit['date'],
                    'duedate' => $deposit['date'],
                    'is_recurring_from' => $first_invoice_id,
                    'subtotal' => $deposit['amount'],
                    'total' => $deposit['amount'],
                    'deposit_from_estimate_id' => $estimate_id,
                    'billing_street' => $first_invoice->billing_street,
                ];
                if (get_option('invoice_due_after') != 0) {
                    $data['duedate'] = _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime($deposit['date']))));
                }


                $data['recurring_type']   = null;
                $data['custom_recurring'] = 0;
                $data['cycles']              = 0;
                $data['total_cycles']        = 0;
                $data['last_recurring_date'] = null;
                $data['allowed_payment_modes'] = $payment_modes; // Always ensure this is set

                $this->invoices_model->update($data, $invoice_id);
                $this->update_sales_total_tax_column($invoice_id, $deposit['amount'], $estimate_total);
            }

            return $first_invoice_id;
        } catch (\Throwable $th) {

            // Rollback
            foreach ($_invoices_id_list as $_invoice_id) {
                $this->invoices_model->delete($_invoice_id);
            }

            throw new \Exception($th->getMessage(), 1);
        }
        return false;
    }

    function update_sales_total_tax_column($id, $amount, $total)
    {
        $table =  db_prefix() . 'invoices';

        $CI = &get_instance();
        $CI->db->select('discount_percent, discount_type, discount_total, subtotal, total, total_tax');
        $CI->db->from($table);
        $CI->db->where('id', $id);
        $data = $CI->db->get()->row();

        $split_percent = (($amount / $total) * 100);
        $new_total_tax = ($split_percent / 100) * $data->total_tax;

        $new_subtotal = $amount - $new_total_tax;

        $CI->db->where('id', $id);
        $CI->db->update($table, [
            'total_tax' => $new_total_tax,
            'subtotal' => $new_subtotal,
        ]);
    }
}