<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Partner Deal Model
 * Handles all database operations for partner deals/leads
 */
class Partner_deal_model extends App_Model
{
    private $table = 'tblpartner_deals';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get all deals
     */
    public function get_all($where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        return $this->db->select('tblpartner_deals.*, tblpartner_resellers.company_name as partner_name')
                        ->join('tblpartner_resellers', 'tblpartner_resellers.id = tblpartner_deals.partner_id')
                        ->get($this->table)
                        ->result();
    }
    
    /**
     * Get single deal
     */
    public function get($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }
    
    /**
     * Get deals by partner
     */
    public function get_by_partner($partner_id, $where = [])
    {
        $this->db->where('partner_id', $partner_id);
        
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        return $this->db->order_by('submitted_at', 'DESC')
                        ->get($this->table)
                        ->result();
    }
    
    /**
     * Get recent deals
     */
    public function get_recent($limit = 10)
    {
        return $this->db->select('tblpartner_deals.*, tblpartner_resellers.company_name as partner_name')
                        ->join('tblpartner_resellers', 'tblpartner_resellers.id = tblpartner_deals.partner_id')
                        ->order_by('tblpartner_deals.submitted_at', 'DESC')
                        ->limit($limit)
                        ->get($this->table)
                        ->result();
    }
    
    /**
     * Get recent deals by partner
     */
    public function get_recent_by_partner($partner_id, $limit = 10)
    {
        return $this->db->where('partner_id', $partner_id)
                        ->order_by('submitted_at', 'DESC')
                        ->limit($limit)
                        ->get($this->table)
                        ->result();
    }
    
    /**
     * Add new deal
     */
    public function add($data)
    {
        $this->db->insert($this->table, $data);
        $insert_id = $this->db->insert_id();
        
        if ($insert_id) {
            log_activity('New Deal Registered [ID: ' . $insert_id . ', Deal#: ' . $data['deal_number'] . ']');
            
            // Log partner activity
            $this->log_partner_activity($data['partner_id'], 'deal_submitted', 'Deal #' . $data['deal_number'] . ' submitted');
        }
        
        return $insert_id;
    }
    
    /**
     * Update deal
     */
    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $id);
        $result = $this->db->update($this->table, $data);
        
        if ($result) {
            log_activity('Deal Updated [ID: ' . $id . ']');
        }
        
        return $result;
    }
    
    /**
     * Delete deal
     */
    public function delete($id)
    {
        $deal = $this->get($id);
        
        $this->db->where('id', $id);
        $result = $this->db->delete($this->table);
        
        if ($result) {
            log_activity('Deal Deleted [ID: ' . $id . ', Deal#: ' . $deal->deal_number . ']');
        }
        
        return $result;
    }
    
    /**
     * Count deals
     */
    public function count_all($where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        return $this->db->count_all_results($this->table);
    }
    
    /**
     * Count active deals (not won/lost)
     */
    public function count_all_active()
    {
        return $this->db->where_not_in('status', ['won', 'lost'])
                        ->count_all_results($this->table);
    }
    
    /**
     * Count deals by partner
     */
    public function count_by_partner($partner_id, $where = [])
    {
        $this->db->where('partner_id', $partner_id);
        
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        return $this->db->count_all_results($this->table);
    }
    
    /**
     * Get total revenue from won deals
     */
    public function get_total_revenue()
    {
        $result = $this->db->select('SUM(COALESCE(actual_value, estimated_value)) as total')
                           ->where('status', 'won')
                           ->get($this->table)
                           ->row();
        
        return $result->total ?? 0;
    }
    
    /**
     * Get total revenue by partner
     */
    public function get_total_revenue_by_partner($partner_id)
    {
        $result = $this->db->select('SUM(COALESCE(actual_value, estimated_value)) as total')
                           ->where('partner_id', $partner_id)
                           ->where('status', 'won')
                           ->get($this->table)
                           ->row();
        
        return $result->total ?? 0;
    }
    
    /**
     * Check for duplicate leads
     */
    public function check_duplicate($company, $email, $phone)
    {
        $this->db->group_start()
                 ->where('company_name', $company)
                 ->or_where('email', $email)
                 ->or_where('phone', $phone)
                 ->group_end();
        
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Find potential duplicates
     */
    public function find_duplicates($company, $email, $phone)
    {
        $this->db->group_start()
                 ->where('company_name', $company)
                 ->or_where('email', $email)
                 ->or_where('phone', $phone)
                 ->group_end()
                 ->where('status !=', 'lost');
        
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Get pipeline statistics
     */
    public function get_pipeline_stats()
    {
        $statuses = ['pending', 'approved', 'new', 'contacted', 'quotation_sent', 'negotiation', 'won', 'lost'];
        $stats = [];
        
        foreach ($statuses as $status) {
            $count = $this->db->where('status', $status)->count_all_results($this->table);
            $value = $this->db->select('SUM(COALESCE(actual_value, estimated_value)) as total')
                              ->where('status', $status)
                              ->get($this->table)
                              ->row();
            
            $stats[$status] = [
                'count' => $count,
                'value' => $value->total ?? 0
            ];
        }
        
        return $stats;
    }
    
    /**
     * Get status distribution by partner
     */
    public function get_status_distribution($partner_id)
    {
        return $this->db->select('status, COUNT(*) as count')
                        ->where('partner_id', $partner_id)
                        ->group_by('status')
                        ->get($this->table)
                        ->result_array();
    }
    
    /**
     * Get monthly statistics for partner
     */
    public function get_monthly_stats($partner_id, $months = 12)
    {
        $start_date = date('Y-m-d', strtotime("-$months months"));
        
        return $this->db->select('
                DATE_FORMAT(submitted_at, "%Y-%m") as month,
                COUNT(*) as total_deals,
                SUM(CASE WHEN status = "won" THEN 1 ELSE 0 END) as won_deals,
                SUM(CASE WHEN status = "won" THEN COALESCE(actual_value, estimated_value) ELSE 0 END) as revenue
            ')
            ->where('partner_id', $partner_id)
            ->where('submitted_at >=', $start_date)
            ->group_by('month')
            ->order_by('month', 'ASC')
            ->get($this->table)
            ->result_array();
    }
    
    /**
     * Get last deal number for auto-increment
     */
    public function get_last_number()
    {
        $result = $this->db->select('id')
                           ->order_by('id', 'DESC')
                           ->limit(1)
                           ->get($this->table)
                           ->row();
        
        return $result ? $result->id : 0;
    }
    
    /**
     * Get deals pending approval
     */
    public function get_pending_approval($limit = null)
    {
        $this->db->select('tblpartner_deals.*, tblpartner_resellers.company_name as partner_name')
                 ->join('tblpartner_resellers', 'tblpartner_resellers.id = tblpartner_deals.partner_id')
                 ->where('tblpartner_deals.approval_status', 'pending')
                 ->order_by('tblpartner_deals.submitted_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Log partner activity
     */
    private function log_partner_activity($partner_id, $type, $description)
    {
        $this->db->insert('tblpartner_activities', [
            'partner_id' => $partner_id,
            'activity_type' => $type,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get deals by service type
     */
    public function get_by_service_type($service_type, $start_date = null, $end_date = null)
    {
        $this->db->where('service_type', $service_type);
        
        if ($start_date) {
            $this->db->where('submitted_at >=', $start_date);
        }
        
        if ($end_date) {
            $this->db->where('submitted_at <=', $end_date . ' 23:59:59');
        }
        
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get deals by territory
     */
    public function get_by_territory($territory, $start_date = null, $end_date = null)
    {
        $this->db->where('location LIKE', '%' . $territory . '%');
        
        if ($start_date) {
            $this->db->where('submitted_at >=', $start_date);
        }
        
        if ($end_date) {
            $this->db->where('submitted_at <=', $end_date . ' 23:59:59');
        }
        
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Update deal with CRM lead ID
     */
    public function link_to_crm_lead($deal_id, $lead_id)
    {
        return $this->update($deal_id, [
            'lead_id' => $lead_id
        ]);
    }
    
    /**
     * Update deal with project ID
     */
    public function link_to_project($deal_id, $project_id)
    {
        return $this->update($deal_id, [
            'project_id' => $project_id
        ]);
    }
}
