<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Aging_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get Customer Aging Data
     */
    public function get_customer_aging_data($client_id = null)
    {
        $this->load->model('statement_model');
        
        if ($client_id) {
            return $this->statement_model->get_invoices_aging($client_id);
        }
        
        $this->db->select('userid, company');
        $this->db->from(db_prefix() . 'clients');
        $clients = $this->db->get()->result_array();
        
        $report = [];
        foreach ($clients as $client) {
            $aging = $this->statement_model->get_invoices_aging($client['userid']);
            
            $remaining_balance = (float)$aging['current'];

            // 1-7 Days
            $bucket_val = (float)($aging['1_7'] ?? 0);
            if ($remaining_balance > 0) {
                if ($remaining_balance < $bucket_val) {
                    $days_1_7 = $remaining_balance;
                    $remaining_balance = 0;
                } else {
                    $days_1_7 = $bucket_val;
                    $remaining_balance = $remaining_balance - $bucket_val;
                }
            } else {
                $days_1_7 = 0;
            }

            // 8-14 Days
            $bucket_val = (float)($aging['8_14'] ?? 0);
            if ($remaining_balance > 0) {
                if ($remaining_balance < $bucket_val) {
                    $days_8_14 = $remaining_balance;
                    $remaining_balance = 0;
                } else {
                    $days_8_14 = $bucket_val;
                    $remaining_balance = $remaining_balance - $bucket_val;
                }
            } else {
                $days_8_14 = 0;
            }

            // 15-21 Days
            $bucket_val = (float)($aging['15_21'] ?? 0);
            if ($remaining_balance > 0) {
                if ($remaining_balance < $bucket_val) {
                    $days_15_21 = $remaining_balance;
                    $remaining_balance = 0;
                } else {
                    $days_15_21 = $bucket_val;
                    $remaining_balance = $remaining_balance - $bucket_val;
                }
            } else {
                $days_15_21 = 0;
            }

            // 22-30 Days
            $bucket_val = (float)($aging['22_30'] ?? 0);
            if ($remaining_balance > 0) {
                if ($remaining_balance < $bucket_val) {
                    $days_22_30 = $remaining_balance;
                    $remaining_balance = 0;
                } else {
                    $days_22_30 = $bucket_val;
                    $remaining_balance = $remaining_balance - $bucket_val;
                }
            } else {
                $days_22_30 = 0;
            }

            // 31-60 Days
            $bucket_val = (float)($aging['31_60'] ?? 0);
            if ($remaining_balance > 0) {
                if ($remaining_balance < $bucket_val) {
                    $days_31_60 = $remaining_balance;
                    $remaining_balance = 0;
                } else {
                    $days_31_60 = $bucket_val;
                    $remaining_balance = $remaining_balance - $bucket_val;
                }
            } else {
                $days_31_60 = 0;
            }

            // 61-90 Days
            $bucket_val = (float)($aging['61_90'] ?? 0);
            if ($remaining_balance > 0) {
                if ($remaining_balance < $bucket_val) {
                    $days_61_90 = $remaining_balance;
                    $remaining_balance = 0;
                } else {
                    $days_61_90 = $bucket_val;
                    $remaining_balance = $remaining_balance - $bucket_val;
                }
            } else {
                $days_61_90 = 0;
            }

            // Over 90 Days
            $bucket_val = (float)($aging['over_90'] ?? 0);
            if ($remaining_balance > 0) {
                $over_90 = $remaining_balance;
            } else {
                $over_90 = 0;
            }
            $remaining_balance = 0; // Everything is consumed by over 90, or wait, no, current takes the remainder!
            // Wait, in clients_aging.php: "Any remaining balance goes here. $current = $remaining_balance;"
            // But actually we assign $over_90 = $remaining_balance; and then $current = $remaining_balance;?
            // In clients_aging.php it was:
            // $over_90 = $remaining_balance;
            // $current = $remaining_balance;
            // Then it prints $aging['current'] which is the TOTAL BALANCE!
            // Yes, clients_aging.php prints $aging['current'] as the TOTAL balance (which is visually wrong, it labels it "Current", but that's what it did).
            
            // Wait, I should make sure the return array has the right keys.
            $aging['current_calc'] = $remaining_balance; // Not displayed usually, but let's just keep the original structure
            
            $aging['1_7'] = $days_1_7;
            $aging['8_14'] = $days_8_14;
            $aging['15_21'] = $days_15_21;
            $aging['22_30'] = $days_22_30;
            $aging['31_60'] = $days_31_60;
            $aging['61_90'] = $days_61_90;
            $aging['over_90'] = $over_90;

            $aging['total_unpaid'] = isset($aging['total']) ? $aging['total'] : 0;
            if ($aging['total_unpaid'] == 0) {
                // $aging['current'] holds the total balance from before_balance()
                $aging['total_unpaid'] = $aging['current'];
            }
            
            $aging['company'] = $client['company'];
            $aging['userid'] = $client['userid'];
            $report[] = $aging;
        }
        return $report;
    }

    /**
     * Get Vendor Aging Data
     */
    public function get_vendor_aging_data($vendor_id = null)
    {
        $this->db->select('userid, company');
        $this->db->from(db_prefix() . 'pur_vendor');
        if ($vendor_id) {
            $this->db->where('userid', $vendor_id);
        }
        $vendors = $this->db->get()->result_array();
        
        $report = [];
        foreach ($vendors as $vendor) {
            $aging = $this->calculate_vendor_aging($vendor['userid']);
            if ($aging['total_unpaid'] > 0) {
                $aging['company'] = $vendor['company'];
                $aging['userid'] = $vendor['userid'];
                $report[] = $aging;
            }
        }
        return $report;
    }

    private function calculate_vendor_aging($vendor_id)
    {
        $this->db->select('id, total, delivery_date as duedate, order_date');
        $this->db->from(db_prefix() . 'pur_orders');
        $this->db->where('vendor', $vendor_id);
        $this->db->where('status !=', 5);
        $orders = $this->db->get()->result_array();
        
        $data = [
            'current' => 0,
            '1_7' => 0,
            '8_14' => 0,
            '15_21' => 0,
            '22_30' => 0,
            '31_60' => 0,
            '61_90' => 0,
            '91_120' => 0,
            'over_120' => 0,
            'total_unpaid' => 0
        ];

        foreach ($orders as $order) {
            $this->db->select_sum('amount');
            $this->db->where('pur_order', $order['id']);
            $paid = $this->db->get(db_prefix() . 'pur_order_payment')->row()->amount;
            
            $unpaid = $order['total'] - $paid;
            if ($unpaid <= 0) continue;

            $data['total_unpaid'] += $unpaid;
            
            $due_date = $order['duedate'] ? $order['duedate'] : $order['order_date'];
            $diff = floor((time() - strtotime($due_date)) / (60 * 60 * 24));

            if ($diff <= 0) {
                $data['current'] += $unpaid;
            } else if ($diff <= 7) {
                $data['1_7'] += $unpaid;
            } else if ($diff <= 14) {
                $data['8_14'] += $unpaid;
            } else if ($diff <= 21) {
                $data['15_21'] += $unpaid;
            } else if ($diff <= 30) {
                $data['22_30'] += $unpaid;
            } else if ($diff <= 60) {
                $data['31_60'] += $unpaid;
            } else if ($diff <= 90) {
                $data['61_90'] += $unpaid;
            } else if ($diff <= 120) {
                $data['91_120'] += $unpaid;
            } else {
                $data['over_120'] += $unpaid;
            }
        }
        return $data;
    }

    /**
     * Get Item Aging Data
     */
    public function get_item_aging_data()
    {
        $this->db->select('id, description, commodity_code');
        $items = $this->db->get(db_prefix() . 'items')->result_array();
        
        $report = [];
        foreach ($items as $item) {
            $this->db->select_sum('inventory_number');
            $this->db->where('commodity_id', $item['id']);
            $qty = $this->db->get(db_prefix() . 'inventory_manage')->row()->inventory_number;
            
            if ($qty <= 0) continue;

            $this->db->select('order_date');
            $this->db->from(db_prefix() . 'pur_orders');
            $this->db->join(db_prefix() . 'pur_order_detail', db_prefix() . 'pur_order_detail.pur_order = ' . db_prefix() . 'pur_orders.id');
            $this->db->group_start();
            $this->db->where(db_prefix() . 'pur_order_detail.item_code', $item['id']);
            $this->db->or_where(db_prefix() . 'pur_order_detail.description', $item['description']);
            $this->db->group_end();
            $this->db->order_by('order_date', 'DESC');
            $this->db->limit(1);
            $last_purchase = $this->db->get()->row();
            
            $date = $last_purchase ? $last_purchase->order_date : date('Y-m-d');
            $diff = floor((time() - strtotime($date)) / (60 * 60 * 24));

            $report[] = [
                'id' => $item['id'],
                'description' => $item['description'],
                'code' => $item['commodity_code'],
                'qty' => $qty,
                'days_since_purchase' => $diff,
                'aging_category' => $this->get_aging_category($diff)
            ];
        }
        return $report;
    }

    private function get_aging_category($days)
    {
        if ($days <= 30) return '0-30 ' . _l('days');
        if ($days <= 60) return '31-60 ' . _l('days');
        if ($days <= 90) return '61-90 ' . _l('days');
        if ($days <= 120) return '91-120 ' . _l('days');
        return '120+ ' . _l('days');
    }

    public function send_reports_by_cron()
    {
        $period = aging_get_option('aging_cron_period');
        $employee_id = aging_get_option('aging_target_employee');
        if (!$employee_id) return;

        $last_sent = aging_get_option('aging_last_sent');
        $now = time();
        
        $should_send = false;
        if ($period == 'daily' && ($now - $last_sent) >= 86400) $should_send = true;
        if ($period == 'weekly' && ($now - $last_sent) >= 604800) $should_send = true;
        if ($period == 'monthly' && ($now - $last_sent) >= 2592000) $should_send = true;

        if ($should_send) {
            $this->send_report_email($employee_id);
            aging_update_option('aging_last_sent', $now);
        }
    }

    public function send_report_email($staff_id)
    {
        $CI = &get_instance();
        $CI->load->model('staff_model');
        $staff = $CI->staff_model->get($staff_id);
        if (!$staff) return;

        $this->load->library(AGING_MODULE_NAME . '/aging_pdf');
        
        $data['items'] = $this->get_item_aging_data();
        $data['customers'] = $this->get_customer_aging_data();
        $data['vendors'] = $this->get_vendor_aging_data();
        
        $pdf = aging_pdf($data);
        $content = $pdf->Output('Aging_Report_' . date('Y-m-d') . '.pdf', 'S');
        
        // Use standard mailer
        $CI->load->library('email');
        $CI->email->from(get_option('smtp_email'), get_option('companyname'));
        $CI->email->to($staff->email);
        $CI->email->subject(_l('aging_report') . ' - ' . date('Y-m-d'));
        $CI->email->message('Please find attached the aging report.');
        $CI->email->attach($content, 'attachment', 'Aging_Report_' . date('Y-m-d') . '.pdf', 'application/pdf');
        $CI->email->send();
    }
}
