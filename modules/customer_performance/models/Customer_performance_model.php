<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Customer_performance_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_matrix_stats($client_id)
    {
        $stats = [
            'total_invoiced' => 0,
            'total_paid' => 0,
            'outstanding_balance' => 0,
            'open_invoices' => 0,
            'open_estimates' => 0,
        ];

        // Total Invoiced & Outstanding
        $this->db->select('SUM(total) as total_invoiced');
        $this->db->where('clientid', $client_id);
        $this->db->where('status !=', Invoices_model::STATUS_CANCELLED);
        $invoices = $this->db->get(db_prefix() . 'invoices')->row();

        if ($invoices && $invoices->total_invoiced) {
            $stats['total_invoiced'] = $invoices->total_invoiced;
        }

        // Total Paid
        $this->db->select('SUM(amount) as total_paid');
        $this->db->join(db_prefix() . 'invoices', db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
        $this->db->where(db_prefix() . 'invoices.clientid', $client_id);
        $payments = $this->db->get(db_prefix() . 'invoicepaymentrecords')->row();

        if ($payments && $payments->total_paid) {
            $stats['total_paid'] = $payments->total_paid;
        }

        $stats['outstanding_balance'] = $stats['total_invoiced'] - $stats['total_paid'];

        // Open Invoices
        $this->db->where('clientid', $client_id);
        $this->db->where_not_in('status', [Invoices_model::STATUS_PAID, Invoices_model::STATUS_CANCELLED]);
        $stats['open_invoices'] = $this->db->count_all_results(db_prefix() . 'invoices');

        // Open Estimates
        $this->db->where('clientid', $client_id);
        $this->db->where_not_in('status', [3, 4]); // 3=Declined, 4=Accepted
        $stats['open_estimates'] = $this->db->count_all_results(db_prefix() . 'estimates');

        return $stats;
    }

    public function get_last_transactions($client_id, $limit = 5)
    {
        // We will fetch the last 5 invoices as transactions
        $this->db->where('clientid', $client_id);
        $this->db->limit($limit);
        $this->db->order_by('datecreated', 'desc');
        return $this->db->get(db_prefix() . 'invoices')->result_array();
    }

    public function get_item_last_prices($client_id, $item_id, $limit = 5)
    {
        $item_description = null;
        
        // Fetch the item description first before building the main query
        // so we don't mix CodeIgniter Active Record states and cause SQL errors
        if (!empty($item_id) && is_numeric($item_id)) {
            $item = $this->db->get_where(db_prefix() . 'items', ['id' => $item_id])->row();
            if ($item) {
                $item_description = $item->description;
            }
        }

        if (!$item_description) {
            return []; // If we can't find an item description, we can't search itemable
        }

        $desc_escaped = $this->db->escape($item_description);
        $client_id_esc = (int)$client_id;
        $limit_esc = (int)$limit;

        // Build a UNION query to get the last 5 prices from Invoices, Estimates, and Proposals
        $sql = "
            (SELECT rate, qty, date, id as rel_id, 'Invoice' as source
             FROM " . db_prefix() . "itemable
             JOIN " . db_prefix() . "invoices ON " . db_prefix() . "invoices.id = " . db_prefix() . "itemable.rel_id
             WHERE rel_type = 'invoice' AND clientid = $client_id_esc AND description = $desc_escaped)
            UNION
            (SELECT rate, qty, date, id as rel_id, 'Estimate' as source
             FROM " . db_prefix() . "itemable
             JOIN " . db_prefix() . "estimates ON " . db_prefix() . "estimates.id = " . db_prefix() . "itemable.rel_id
             WHERE rel_type = 'estimate' AND clientid = $client_id_esc AND description = $desc_escaped)
            UNION
            (SELECT rate, qty, date, id as rel_id, 'Proposal' as source
             FROM " . db_prefix() . "itemable
             JOIN " . db_prefix() . "proposals ON " . db_prefix() . "proposals.id = " . db_prefix() . "itemable.rel_id
             WHERE rel_type = 'proposal' AND rel_id = $client_id_esc AND " . db_prefix() . "proposals.rel_type = 'customer' AND description = $desc_escaped)
            ORDER BY date DESC
            LIMIT $limit_esc
        ";

        return $this->db->query($sql)->result_array();
    }
}
