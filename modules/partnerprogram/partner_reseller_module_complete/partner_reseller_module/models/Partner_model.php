<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Partner Model
 * Handles all database operations for partners
 */
class Partner_model extends App_Model
{
    private $table = 'tblpartner_resellers';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get all partners
     */
    public function get_all($where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get single partner
     */
    public function get($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }
    
    /**
     * Get partner by username
     */
    public function get_by_username($username)
    {
        return $this->db->where('username', $username)->get($this->table)->row();
    }
    
    /**
     * Get partner by email
     */
    public function get_by_email($email)
    {
        return $this->db->where('email', $email)->get($this->table)->row();
    }
    
    /**
     * Add new partner
     */
    public function add($data)
    {
        $this->db->insert($this->table, $data);
        $insert_id = $this->db->insert_id();
        
        if ($insert_id) {
            log_activity('New Partner Created [ID: ' . $insert_id . ', Company: ' . $data['company_name'] . ']');
        }
        
        return $insert_id;
    }
    
    /**
     * Update partner
     */
    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $id);
        $result = $this->db->update($this->table, $data);
        
        if ($result) {
            log_activity('Partner Updated [ID: ' . $id . ']');
        }
        
        return $result;
    }
    
    /**
     * Delete partner
     */
    public function delete($id)
    {
        $partner = $this->get($id);
        
        $this->db->where('id', $id);
        $result = $this->db->delete($this->table);
        
        if ($result) {
            // Delete related data
            $this->db->where('partner_id', $id)->delete('tblpartner_contacts');
            $this->db->where('partner_id', $id)->delete('tblpartner_documents');
            $this->db->where('partner_id', $id)->delete('tblpartner_notifications');
            $this->db->where('partner_id', $id)->delete('tblpartner_activities');
            
            log_activity('Partner Deleted [ID: ' . $id . ', Company: ' . $partner->company_name . ']');
        }
        
        return $result;
    }
    
    /**
     * Count partners
     */
    public function count_all($where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        return $this->db->count_all_results($this->table);
    }
    
    /**
     * Check if username exists
     */
    public function username_exists($username, $exclude_id = null)
    {
        $this->db->where('username', $username);
        
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        
        return $this->db->count_all_results($this->table) > 0;
    }
    
    /**
     * Get partner contacts
     */
    public function get_contacts($partner_id)
    {
        return $this->db->where('partner_id', $partner_id)
                        ->order_by('is_primary', 'DESC')
                        ->get('tblpartner_contacts')
                        ->result();
    }
    
    /**
     * Add contact
     */
    public function add_contact($data)
    {
        // If this is primary, unset other primary contacts
        if ($data['is_primary'] == 1) {
            $this->db->where('partner_id', $data['partner_id'])
                     ->update('tblpartner_contacts', ['is_primary' => 0]);
        }
        
        $this->db->insert('tblpartner_contacts', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Update contact
     */
    public function update_contact($id, $data)
    {
        // If setting as primary, unset other primary contacts
        if (isset($data['is_primary']) && $data['is_primary'] == 1) {
            $contact = $this->db->where('id', $id)->get('tblpartner_contacts')->row();
            
            if ($contact) {
                $this->db->where('partner_id', $contact->partner_id)
                         ->where('id !=', $id)
                         ->update('tblpartner_contacts', ['is_primary' => 0]);
            }
        }
        
        $this->db->where('id', $id);
        return $this->db->update('tblpartner_contacts', $data);
    }
    
    /**
     * Delete contact
     */
    public function delete_contact($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('tblpartner_contacts');
    }
    
    /**
     * Get partner documents
     */
    public function get_documents($partner_id)
    {
        return $this->db->where('partner_id', $partner_id)
                        ->order_by('uploaded_at', 'DESC')
                        ->get('tblpartner_documents')
                        ->result();
    }
    
    /**
     * Add document
     */
    public function add_document($data)
    {
        $this->db->insert('tblpartner_documents', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Delete document
     */
    public function delete_document($id)
    {
        $document = $this->db->where('id', $id)->get('tblpartner_documents')->row();
        
        if ($document && file_exists($document->file_path)) {
            unlink($document->file_path);
        }
        
        $this->db->where('id', $id);
        return $this->db->delete('tblpartner_documents');
    }
    
    /**
     * Get partner activities
     */
    public function get_activities($partner_id, $limit = 50)
    {
        return $this->db->where('partner_id', $partner_id)
                        ->order_by('created_at', 'DESC')
                        ->limit($limit)
                        ->get('tblpartner_activities')
                        ->result();
    }
    
    /**
     * Log activity
     */
    public function log_activity($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('tblpartner_activities', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Get partner notifications
     */
    public function get_notifications($partner_id, $limit = null)
    {
        $this->db->where('partner_id', $partner_id)
                 ->order_by('date_sent', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get('tblpartner_notifications')->result();
    }
    
    /**
     * Get unread notifications
     */
    public function get_unread_notifications($partner_id, $limit = null)
    {
        $this->db->where('partner_id', $partner_id)
                 ->where('is_read', 0)
                 ->order_by('date_sent', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get('tblpartner_notifications')->result();
    }
    
    /**
     * Mark notifications as read
     */
    public function mark_notifications_read($partner_id, $notification_ids = null)
    {
        $this->db->where('partner_id', $partner_id);
        
        if ($notification_ids) {
            $this->db->where_in('id', $notification_ids);
        }
        
        return $this->db->update('tblpartner_notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Update partner statistics
     */
    public function update_statistics($partner_id)
    {
        // Get total deals
        $total_deals = $this->db->where('partner_id', $partner_id)
                                ->count_all_results('tblpartner_deals');
        
        // Get total revenue (won deals)
        $revenue_query = $this->db->select('SUM(COALESCE(actual_value, estimated_value)) as total')
                                  ->where('partner_id', $partner_id)
                                  ->where('status', 'won')
                                  ->get('tblpartner_deals')
                                  ->row();
        
        $total_revenue = $revenue_query->total ?? 0;
        
        // Get total commission
        $commission_query = $this->db->select('SUM(paid_amount) as total')
                                     ->where('partner_id', $partner_id)
                                     ->where('status', 'paid')
                                     ->get('tblpartner_commissions')
                                     ->row();
        
        $total_commission = $commission_query->total ?? 0;
        
        // Calculate conversion rate
        $won_deals = $this->db->where('partner_id', $partner_id)
                              ->where('status', 'won')
                              ->count_all_results('tblpartner_deals');
        
        $conversion_rate = $total_deals > 0 ? round(($won_deals / $total_deals) * 100, 2) : 0;
        
        // Update partner record
        return $this->update($partner_id, [
            'total_deals' => $total_deals,
            'total_revenue' => $total_revenue,
            'total_commission' => $total_commission,
            'conversion_rate' => $conversion_rate
        ]);
    }
    
    /**
     * Get partner performance metrics
     */
    public function get_performance_metrics($partner_id, $start_date = null, $end_date = null)
    {
        $this->db->select('
            COUNT(*) as total_deals,
            SUM(CASE WHEN status = "won" THEN 1 ELSE 0 END) as won_deals,
            SUM(CASE WHEN status = "lost" THEN 1 ELSE 0 END) as lost_deals,
            SUM(CASE WHEN status IN ("new", "contacted", "quotation_sent", "negotiation") THEN 1 ELSE 0 END) as active_deals,
            SUM(CASE WHEN status = "won" THEN COALESCE(actual_value, estimated_value) ELSE 0 END) as total_revenue,
            AVG(CASE WHEN status = "won" THEN COALESCE(actual_value, estimated_value) ELSE NULL END) as avg_deal_value
        ')
        ->where('partner_id', $partner_id);
        
        if ($start_date) {
            $this->db->where('submitted_at >=', $start_date);
        }
        
        if ($end_date) {
            $this->db->where('submitted_at <=', $end_date . ' 23:59:59');
        }
        
        return $this->db->get('tblpartner_deals')->row();
    }
}
