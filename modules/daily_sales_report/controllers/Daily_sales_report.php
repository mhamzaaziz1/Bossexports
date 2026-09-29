<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Daily_sales_report extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('daily_sales_report_model');
    }

    public function index()
    {
        if (!is_admin()) {
            access_denied('Daily Sales Report Settings');
        }

        $data['title'] = _l('daily_sales_report_settings');
        $data['reports'] = $this->daily_sales_report_model->get_reports();
        $data['logs'] = $this->daily_sales_report_model->get_logs();

        $this->load->view('manage_reports', $data);
    }

    public function global_settings()
    {
        if (!is_admin()) {
            access_denied('Daily Sales Report Global Settings');
        }

        $data['title'] = _l('daily_sales_report_global_settings');
        $this->load->view('global_settings', $data);
    }

    public function api_settings()
    {
        if (!is_admin()) {
            access_denied('Daily Sales Report Settings');
        }

        if ($this->input->post()) {
            update_option('optimetriks_client_id', $this->input->post('optimetriks_client_id'));
            update_option('optimetriks_email', $this->input->post('optimetriks_email'));
            update_option('optimetriks_password', $this->input->post('optimetriks_password'));
            update_option('optimetriks_stock_warehouse_id', $this->input->post('optimetriks_stock_warehouse_id'));

            set_alert('success', _l('settings_updated'));
            redirect(admin_url('daily_sales_report/api_settings'));
        }

        $data['title'] = 'Optimetriks API Settings';
        $data['warehouses'] = $this->daily_sales_report_model->get_warehouses();
        $this->load->view('api_settings', $data);
    }

    public function test_optimetriks_connection()
    {
        if (!is_admin()) {
            ajax_access_denied();
        }

        $client_id = $this->input->post('client_id');
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        if (empty($client_id) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all credential fields before testing.']);
            die();
        }

        $login_url = 'https://v3-0.fieldproapp.com/web/web/users/login';
        $payload = json_encode(['email' => $email, 'password' => $password]);
        
        $ch = curl_init($login_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'client-id: ' . $client_id
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $auth_token = '';
        if ($http_code == 200) {
            $resp_data = json_decode($response, true);
            if (isset($resp_data['data']['data']['token'])) {
                $auth_token = $resp_data['data']['data']['token'];
            } elseif (isset($resp_data['data']['token'])) {
                $auth_token = $resp_data['data']['token'];
            } elseif (isset($resp_data['auth_token'])) {
                $auth_token = $resp_data['auth_token'];
            }
        }

        if (!empty($auth_token)) {
            echo json_encode(['success' => true, 'message' => 'Connection successful! Authentication token received.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Connection failed. HTTP Code: ' . $http_code . '. Response: ' . $response]);
        }
    }

    public function report($id = '')
    {
        if (!is_admin()) {
            access_denied('Daily Sales Report Settings');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            
            $save_data = [
                'name' => $data['name'],
                'emails' => $data['emails'],
                'items' => isset($data['items']) ? json_encode($data['items']) : '[]',
                'supplier_ids' => json_encode($this->filter_supplier_ids($data)),
                'active' => isset($data['active']) ? 1 : 0
            ];

            if ($id == '') {
                $id = $this->daily_sales_report_model->add_report($save_data);
                set_alert('success', _l('added_successfully', _l('daily_sales_report')));
                redirect(admin_url('daily_sales_report'));
            } else {
                $success = $this->daily_sales_report_model->update_report($id, $save_data);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('daily_sales_report')));
                }
                redirect(admin_url('daily_sales_report'));
            }
        }

        if ($id == '') {
            $title = _l('daily_sales_report_add_new');
        } else {
            $data['report'] = $this->daily_sales_report_model->get_report($id);
            $title = _l('daily_sales_report_edit');
            
            // Today's sales for the preview
            $tracked_item_ids = json_decode($data['report']['items'], true);
            if (!$tracked_item_ids) {
                $tracked_item_ids = [];
            }
            $supplier_ids = $this->daily_sales_report_model->get_report_supplier_ids($data['report']);
            $data['tracked_item_details'] = $this->daily_sales_report_model->get_item_details($tracked_item_ids);
            foreach ($data['tracked_item_details'] as &$item) {
                $item['supplier_id'] = isset($supplier_ids[$item['id']]) ? $supplier_ids[$item['id']] : '';
            }
            unset($item);
            $data['today_sales'] = $this->daily_sales_report_model->get_today_sales_data($tracked_item_ids, '', '', $supplier_ids);
        }

        $data['title'] = $title;
        
        // Fetch drop downs
        $data['warehouses'] = $this->daily_sales_report_model->get_warehouses();
        $data['commodity_types'] = $this->daily_sales_report_model->get_commodity_types();
        $data['commodity_groups'] = $this->daily_sales_report_model->get_commodity_groups();
        $data['sub_groups'] = $this->daily_sales_report_model->get_sub_groups();
        $data['colors'] = $this->daily_sales_report_model->get_colors();
        $data['sizes'] = $this->daily_sales_report_model->get_sizes();
        $data['styles'] = $this->daily_sales_report_model->get_styles();
        $data['models'] = $this->daily_sales_report_model->get_models();

        $this->load->view('report', $data);
    }

    /**
     * Convert a posted date (display format or Y-m-d) to Y-m-d; returns false when empty or invalid
     */
    private function parse_report_date($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return false;
        }
        $date = to_sql_date($value);
        $parsed = DateTime::createFromFormat('Y-m-d', (string) $date);
        if (!$parsed || $parsed->format('Y-m-d') !== $date) {
            return false;
        }
        return $date;
    }

    /**
     * Keep supplier IDs only for items still tracked on the report, dropping empty values
     */
    private function filter_supplier_ids($data)
    {
        $items = isset($data['items']) ? $data['items'] : [];
        $posted = isset($data['supplier_ids']) && is_array($data['supplier_ids']) ? $data['supplier_ids'] : [];
        $supplier_ids = [];
        foreach ($items as $item_id) {
            if (isset($posted[$item_id]) && trim($posted[$item_id]) !== '') {
                $supplier_ids[$item_id] = trim($posted[$item_id]);
            }
        }
        return $supplier_ids;
    }

    public function delete($id)
    {
        if (!is_admin()) {
            access_denied('Daily Sales Report Settings');
        }
        if (!$id) {
            redirect(admin_url('daily_sales_report'));
        }
        $response = $this->daily_sales_report_model->delete_report($id);
        if ($response == true) {
            set_alert('success', _l('daily_sales_report_success_delete'));
        }
        redirect(admin_url('daily_sales_report'));
    }

    public function send_manual($id)
    {
        if (!is_admin()) {
            access_denied('Daily Sales Report Settings');
        }
        $result = $this->daily_sales_report_model->send_manual_report($id);
        if ($result) {
            set_alert('success', _l('daily_sales_report_success_manual_send'));
        } else {
            set_alert('warning', 'Could not send report. Make sure emails and items are configured and there are sales for the selected date.');
        }
        redirect(admin_url('daily_sales_report'));
    }

    public function send_manual_custom()
    {
        if (!is_admin()) {
            access_denied('Daily Sales Report Settings');
        }
        if ($this->input->post()) {
            $id = $this->input->post('report_id');
            // Missing/invalid dates fall back to today
            $start_date = $this->parse_report_date($this->input->post('report_start_date')) ?: date('Y-m-d');
            $end_date = $this->parse_report_date($this->input->post('report_end_date')) ?: $start_date;

            // Re-validate just in case
            if ((strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) > 31) {
                set_alert('warning', 'Date range cannot exceed 31 days.');
                redirect(admin_url('daily_sales_report'));
            }

            $result = $this->daily_sales_report_model->send_manual_report($id, $start_date, $end_date);
            if ($result) {
                set_alert('success', _l('daily_sales_report_success_manual_send'));
            } else {
                set_alert('warning', 'Could not send report. Make sure emails and items are configured and there are sales for the selected date range.');
            }
        }
        redirect(admin_url('daily_sales_report'));
    }

    public function send_optimetriks_only()
    {
        if (!is_admin()) {
            access_denied('Daily Sales Report Settings');
        }

        if ($this->input->post()) {
            $report_id = $this->input->post('report_id');
            // Missing/invalid dates fall back to today (an empty date would otherwise become 1970-01-01)
            $start_date = $this->parse_report_date($this->input->post('report_start_date')) ?: date('Y-m-d');
            $end_date = $this->parse_report_date($this->input->post('report_end_date')) ?: $start_date;

            $report = $this->daily_sales_report_model->get_report($report_id);
            if (!$report) {
                set_alert('danger', _l('daily_sales_report_not_found'));
                redirect(admin_url('daily_sales_report'));
            }

            $items_option = $report['items'];
            if (empty($items_option) || $items_option == '[]') {
                set_alert('danger', 'No items selected for this report.');
                redirect(admin_url('daily_sales_report'));
            }

            $selected_item_ids = json_decode($items_option, true);
            $supplier_ids = $this->daily_sales_report_model->get_report_supplier_ids($report);
            $current = strtotime($start_date);
            $end = strtotime($end_date);
            
            $failed = [];
            $errors = [];
            $results = [];

            while ($current <= $end) {
                $curr_date = date('Y-m-d', $current);
                $opti_zips = $this->daily_sales_report_model->send_to_optimetriks($curr_date, $selected_item_ids, $supplier_ids);
                if ($this->daily_sales_report_model->optimetriks_error) {
                    $errors[] = $curr_date . ': ' . $this->daily_sales_report_model->optimetriks_error;
                }

                foreach ($opti_zips as $z) {
                    $results[] = $z;
                    if (!$z['success'] && empty($z['skipped'])) {
                        $failed[] = $curr_date . ' ' . $z['stream'] . ' (HTTP ' . $z['http_code'] . ': ' . substr(strip_tags($z['response']), 0, 150) . ')';
                    }
                    // Clean up local temp files after sending
                    $this->daily_sales_report_model->cleanup_optimetriks_zip($z['path']);
                }

                $current = strtotime('+1 day', $current);
            }

            $sent = $this->daily_sales_report_model->log_optimetriks_send($report, $start_date, $end_date, $results, $errors);

            if (!empty($errors) && $sent == 0 && empty($failed)) {
                set_alert('danger', implode(' ', array_unique($errors)));
            } elseif (!empty($failed)) {
                set_alert('danger', 'Sent ' . $sent . ' file(s); ' . count($failed) . ' rejected by Optimetriks: ' . implode('; ', $failed));
            } elseif ($sent > 0) {
                $skipped = array_filter($results, function ($z) {
                    return !empty($z['skipped']);
                });
                $skipped_note = $skipped ? ' ' . count($skipped) . ' file(s) skipped because there was no data (see log).' : '';
                set_alert('success', 'Successfully sent ' . $sent . ' files to Optimetriks API.' . $skipped_note);
            } else {
                set_alert('warning', 'No Optimetriks data found for the selected date range.');
            }
            redirect(admin_url('daily_sales_report'));
        }
        redirect(admin_url('daily_sales_report'));
    }

    public function preview_manual()
    {
        if (!is_admin()) {
            access_denied('Daily Sales Report Settings');
        }
        if ($this->input->post()) {
            $id = $this->input->post('report_id');
            $start_date = $this->parse_report_date($this->input->post('report_start_date'));
            $end_date = $this->parse_report_date($this->input->post('report_end_date'));

            if (!$start_date) { $start_date = date('Y-m-d'); }
            if (!$end_date) { $end_date = $start_date; }

            if (strtotime($end_date) < strtotime($start_date)) {
                $temp = $start_date;
                $start_date = $end_date;
                $end_date = $temp;
            }

            $diff_days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);
            if ($diff_days > 31) {
                set_alert('warning', 'Date range cannot exceed 31 days.');
                redirect(admin_url('daily_sales_report'));
            }
            
            $data['report'] = $this->daily_sales_report_model->get_report($id);
            if (!$data['report']) {
                redirect(admin_url('daily_sales_report'));
            }
            
            $tracked_item_ids = json_decode($data['report']['items'], true);
            if (!$tracked_item_ids) {
                $tracked_item_ids = [];
            }
            $supplier_ids = $this->daily_sales_report_model->get_report_supplier_ids($data['report']);
            $data['sales'] = $this->daily_sales_report_model->get_today_sales_data($tracked_item_ids, $start_date, $end_date, $supplier_ids);
            
            // Get Optimetriks datasets
            $data['opti_stock'] = $this->daily_sales_report_model->get_optimetriks_stock_level($tracked_item_ids, $supplier_ids, $end_date);
            // Since we can preview a range, we'll get the optimetriks data for the end_date or loop? 
            // The API is per day, but preview can be range. Let's show the data for the range by using start and end date if we can, 
            // but the model methods take a single $date. Let's just loop and merge for preview.
            
            $opti_sell_out = [];
            $opti_sell_out_lines = [];
            $opti_customers = [];
            $current = strtotime($start_date);
            $end = strtotime($end_date);
            while ($current <= $end) {
                $curr_date = date('Y-m-d', $current);
                $opti_sell_out = array_merge($opti_sell_out, $this->daily_sales_report_model->get_optimetriks_sell_out($curr_date, $tracked_item_ids));
                $opti_sell_out_lines = array_merge($opti_sell_out_lines, $this->daily_sales_report_model->get_optimetriks_sell_out_lines($curr_date, $tracked_item_ids, $supplier_ids));
                $opti_customers = array_merge($opti_customers, $this->daily_sales_report_model->get_optimetriks_customers($curr_date, $tracked_item_ids));
                $current = strtotime('+1 day', $current);
            }
            $data['opti_sell_out'] = $opti_sell_out;
            $data['opti_sell_out_lines'] = $opti_sell_out_lines;
            $data['opti_customers'] = $opti_customers;
            
            $date_label = $start_date == $end_date ? _d($start_date) : _d($start_date) . ' - ' . _d($end_date);
            $data['title'] = _l('daily_sales_report_manual_preview_title') . ' - ' . $date_label;
            
            $data['start_date'] = $start_date;
            $data['end_date'] = $end_date;
            $data['is_opti_only'] = $this->input->post('is_opti_only') ? 1 : 0;
            
            $this->load->view('preview_manual', $data);
        } else {
            redirect(admin_url('daily_sales_report'));
        }
    }

    public function ajax_get_items()
    {
        if (!is_admin()) {
            ajax_access_denied();
        }

        $warehouse_id = $this->input->post('warehouse_id');
        $commodity_type_id = $this->input->post('commodity_type_id');
        $group_id = $this->input->post('group_id');
        $sub_group_id = $this->input->post('sub_group_id');
        $color_id = $this->input->post('color_id');
        $size_id = $this->input->post('size_id');
        $style_id = $this->input->post('style_id');
        $model_id = $this->input->post('model_id');

        $items = $this->daily_sales_report_model->get_items_by_filters($warehouse_id, $commodity_type_id, $group_id, $sub_group_id, $color_id, $size_id, $style_id, $model_id);
        
        $log_data = [
            'post' => $_POST,
            'items_count' => count($items)
        ];
        file_put_contents(FCPATH . 'ajax_log.txt', print_r($log_data, true) . "\n", FILE_APPEND);
        
        echo json_encode($items);
    }

    public function download_log($id)
    {
        if (!is_admin()) {
            access_denied('Daily Sales Report Logs');
        }

        $log = $this->daily_sales_report_model->get_log($id);
        if (!$log) {
            blank_page('Log not found', 'danger');
        }

        $date = date('Y-m-d', strtotime($log['date_sent']));
        $is_optimetriks = strpos($log['email_to'], 'Optimetriks') !== false;
        $filename = ($is_optimetriks ? 'optimetriks_upload_' : 'daily_sales_report_') . $date . ($is_optimetriks ? '.txt' : '.csv');

        header('Content-Type: ' . ($is_optimetriks ? 'text/plain' : 'text/csv'));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $log['csv_content'];
        die;
    }
}
