<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Custom_sales extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('string');
    }

    /**
     * Get the list of item title for  given resources
     *
     * @param mixed $resources_id
     * @param string $resources_type
     * @return void
     */
    function get_items_titles($resources_id, $resources_type)
    {
        $titles = (object)$this->custom_sales_model->get_items_titles($resources_id, $resources_type);
        echo json_encode(['titles' => $titles]);
        exit;
    }

    function get_title_list()
    {
        $titles = $this->custom_sales_model->get_title_list();
        echo json_encode(['titles' => $titles]);
        exit;
    }

    /**
     * Update titles order from table preivew drag/drop
     *
     * @param string $resources_type
     * @return void
     */
    function update_title_order($resources_type)
    {
        $data = $this->input->post();
        foreach ($data['data'] as $order) {
            $this->db->where('resources', $resources_type);
            $this->db->where('id', $order[0]);
            $this->db->update($this->custom_sales_model->items_titles_table, [
                'child_item_order' => $order[1] ?? "",
            ]);
        }
    }

    /**
     * Create deposit invoices for an estimate
     *
     * @param mixed $estimate_id
     * @return void
     */
    function deposit_invoice($estimate_id)
    {
        if ($this->input->post() && (int)get_option('custom_sales_allow_deposit_invoice')) {
            $status = 'danger';
            $message = 'unkown';
            $url = '';
            $currency = get_base_currency();

            try {
                $this->load->model('estimates_model');
                $estimate = $this->estimates_model->get($estimate_id);

                $data = [];

                $dates = $this->input->post('date');
                $amounts = $this->input->post('amount');

                if (count($dates) !== count($amounts))
                    throw new \Exception(_l("custom_sales_invalid_data"), 1);

                $sum = 0;
                foreach ($amounts as $amount) {
                    $sum += (float)$amount;
                }

                if ($sum !== (float)$estimate->total)
                    throw new \Exception(_l("custom_sales_deposit_invoice_amount_ne_estimate", $estimate->total . ' ' . $currency->symbol), 1);

                // Ensure all dates are future and right
                $first_invoice_date = strtotime($dates[0]);
                foreach ($dates as $key => $date) {

                    $datestamp = strtotime($date);

                    if ($datestamp  < strtotime(date('Y-m-d', time())))
                        throw new \Exception(_l("custom_sales_deposit_invoice_date_invalid", $key + 1), 1);

                    if ($key > 0 && $datestamp < $first_invoice_date)
                        throw new \Exception(_l("custom_sales_deposit_invoice_gt_first_invoice_date", $key + 1), 1);

                    $data[$key] = ['amount' => $amounts[$key], 'date' => $date];
                }

                // Create invoice and add child invoices is_recurring_from
                if ($first_invoice_id = $this->custom_sales_model->create_deposit_invoices($estimate->id, $data)) {
                    $status = 'success';
                    $message = _l('added_successfully', _l('invoice'));
                    $url = admin_url('invoices/list_invoices/' . $first_invoice_id);
                }
            } catch (\Throwable $th) {
                $status = 'danger';
                $message = $th->getMessage();
            }

            echo json_encode(['url' => $url, 'message' => $message, 'status' => $status, 'data' => [$dates, $amounts]]);
            exit;
        }
    }

    public function remove_pdf_background_image()
    {
        if (staff_cant('delete', 'settings')) {
            access_denied('settings');
        }

        $image = get_option('custom_sales_pdf_background_image');
        $path = get_upload_path_by_type('company') . '/' . $image;
        if (file_exists($path)) {
            unlink($path);
        }

        update_option('custom_sales_pdf_background_image', '');

        $redUrl = admin_url('settings?group=' . CUSTOM_SALES_MODULE_NAME);
        if ($this->input->get('active_tab')) {
            $redUrl .= '&tab=' . $this->input->get('active_tab');
        }
        redirect($redUrl);
    }
}
