<?php
/**
 * Partner Reseller Module - Installation Script
 * Creates all necessary database tables and configurations
 */

defined('BASEPATH') or exit('No direct script access allowed');

class Partner_Reseller_Install
{
    private $CI;
    
    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->dbforge();
    }
    
    /**
     * Run installation
     */
    public function run()
    {
        $this->create_partners_table();
        $this->create_partner_contacts_table();
        $this->create_partner_documents_table();
        $this->create_partner_deals_table();
        $this->create_partner_commissions_table();
        $this->create_partner_payments_table();
        $this->create_partner_notifications_table();
        $this->create_partner_activities_table();
        $this->create_partner_settings_table();
        $this->create_partner_territories_table();
        $this->insert_default_settings();
        $this->create_partner_portal_permissions();
        
        return true;
    }
    
    /**
     * Partners Table
     */
    private function create_partners_table()
    {
        $fields = [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'company_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'partner_type' => [
                'type'       => 'ENUM',
                'constraint' => ['reseller', 'referral', 'contractor', 'corporate'],
                'default'    => 'reseller'
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'unique'     => true
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'website' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true
            ],
            'state' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true
            ],
            'country' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'UAE'
            ],
            'zip_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true
            ],
            'trade_license' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true
            ],
            'tax_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true
            ],
            'contract_start_date' => [
                'type' => 'DATE',
                'null' => true
            ],
            'contract_end_date' => [
                'type' => 'DATE',
                'null' => true
            ],
            'contract_document' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'commission_type' => [
                'type'       => 'ENUM',
                'constraint' => ['fixed', 'percentage', 'tiered'],
                'default'    => 'fixed'
            ],
            'commission_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00
            ],
            'commission_config' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON config for tiered commissions'
            ],
            'territory' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array of territories'
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'active', 'suspended', 'inactive'],
                'default'    => 'pending'
            ],
            'kyc_verified' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 0
            ],
            'portal_access' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 1
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
                'null'       => true
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true
            ],
            'last_login' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'total_deals' => [
                'type'    => 'INT',
                'constraint' => 11,
                'default' => 0
            ],
            'total_revenue' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00
            ],
            'total_commission' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00
            ],
            'conversion_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00
            ],
            'assigned_to' => [
                'type'    => 'INT',
                'constraint' => 11,
                'null'    => true,
                'comment' => 'Staff member managing this partner'
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ];
        
        $this->CI->dbforge->add_field($fields);
        $this->CI->dbforge->add_key('id', true);
        $this->CI->dbforge->add_key('email');
        $this->CI->dbforge->add_key('status');
        $this->CI->dbforge->create_table('tblpartner_resellers', true);
    }
    
    /**
     * Partner Contacts Table
     */
    private function create_partner_contacts_table()
    {
        $fields = [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'partner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true
            ],
            'position' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true
            ],
            'is_primary' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0
            ],
            'can_login' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0
            ],
            'active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ];
        
        $this->CI->dbforge->add_field($fields);
        $this->CI->dbforge->add_key('id', true);
        $this->CI->dbforge->add_key('partner_id');
        $this->CI->dbforge->create_table('tblpartner_contacts', true);
    }
    
    /**
     * Partner Documents Table
     */
    private function create_partner_documents_table()
    {
        $fields = [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'partner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true
            ],
            'document_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'file_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'file_path' => [
                'type' => 'TEXT',
            ],
            'file_size' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'uploaded_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true
            ],
            'uploaded_at' => [
                'type' => 'DATETIME',
            ],
            'verified' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0
            ],
            'verified_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true
            ],
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ];
        
        $this->CI->dbforge->add_field($fields);
        $this->CI->dbforge->add_key('id', true);
        $this->CI->dbforge->add_key('partner_id');
        $this->CI->dbforge->create_table('tblpartner_documents', true);
    }
    
    /**
     * Partner Deals Table (Lead Registration)
     */
    private function create_partner_deals_table()
    {
        $fields = [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'partner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true
            ],
            'deal_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true
            ],
            'client_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'company_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true
            ],
            'service_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'CCTV, Fiber, IT, AMC, etc.'
            ],
            'business_unit' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'For multi-website support'
            ],
            'estimated_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00
            ],
            'actual_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'requirements' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'expected_close_date' => [
                'type' => 'DATE',
                'null' => true
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected', 'info_required', 'new', 'contacted', 'quotation_sent', 'negotiation', 'won', 'lost'],
                'default'    => 'pending'
            ],
            'approval_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected', 'info_required'],
                'default'    => 'pending'
            ],
            'duplicate_flag' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0
            ],
            'duplicate_of' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Reference to original deal if duplicate'
            ],
            'lead_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Perfex CRM lead ID after approval'
            ],
            'project_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Perfex CRM project ID if converted'
            ],
            'commission_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00
            ],
            'commission_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'calculated', 'approved', 'paid'],
                'default'    => 'pending'
            ],
            'commission_paid_date' => [
                'type' => 'DATE',
                'null' => true
            ],
            'win_probability' => [
                'type'       => 'INT',
                'constraint' => 3,
                'null'       => true,
                'comment'    => 'AI predicted win probability (0-100)'
            ],
            'ai_classification' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'AI-generated lead classification data'
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'admin_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Internal notes not visible to partner'
            ],
            'rejection_reason' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'assigned_to' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Staff member assigned to this deal'
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'closed_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ];
        
        $this->CI->dbforge->add_field($fields);
        $this->CI->dbforge->add_key('id', true);
        $this->CI->dbforge->add_key('partner_id');
        $this->CI->dbforge->add_key('deal_number');
        $this->CI->dbforge->add_key('status');
        $this->CI->dbforge->add_key('approval_status');
        $this->CI->dbforge->create_table('tblpartner_deals', true);
    }
    
    /**
     * Partner Commissions Table
     */
    private function create_partner_commissions_table()
    {
        $fields = [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'partner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true
            ],
            'deal_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true
            ],
            'commission_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true
            ],
            'deal_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00
            ],
            'commission_type' => [
                'type'       => 'ENUM',
                'constraint' => ['fixed', 'percentage', 'tiered'],
            ],
            'commission_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true
            ],
            'commission_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00
            ],
            'split_partners' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array for split commissions'
            ],
            'payment_condition' => [
                'type'       => 'ENUM',
                'constraint' => ['project_completion', 'invoice_clearance', 'multi_stage'],
                'default'    => 'project_completion'
            ],
            'payment_stage' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array for multi-stage payments'
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'calculated', 'approved', 'paid', 'cancelled'],
                'default'    => 'pending'
            ],
            'invoice_generated' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0
            ],
            'invoice_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true
            ],
            'invoice_date' => [
                'type' => 'DATE',
                'null' => true
            ],
            'payment_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true
            ],
            'payment_reference' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true
            ],
            'paid_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00
            ],
            'paid_date' => [
                'type' => 'DATE',
                'null' => true
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'paid_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true
            ]
        ];
        
        $this->CI->dbforge->add_field($fields);
        $this->CI->dbforge->add_key('id', true);
        $this->CI->dbforge->add_key('partner_id');
        $this->CI->dbforge->add_key('deal_id');
        $this->CI->dbforge->add_key('status');
        $this->CI->dbforge->create_table('tblpartner_commissions', true);
    }
    
    /**
     * Partner Payments Table
     */
    private function create_partner_payments_table()
    {
        $fields = [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'partner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true
            ],
            'payment_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true
            ],
            'commission_ids' => [
                'type' => 'TEXT',
                'comment' => 'JSON array of commission IDs included in this payment'
            ],
            'total_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00
            ],
            'payment_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'payment_reference' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true
            ],
            'payment_date' => [
                'type' => 'DATE',
            ],
            'bank_details' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON bank account information'
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'processed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ]
        ];
        
        $this->CI->dbforge->add_field($fields);
        $this->CI->dbforge->add_key('id', true);
        $this->CI->dbforge->add_key('partner_id');
        $this->CI->dbforge->create_table('tblpartner_payments', true);
    }
    
    /**
     * Partner Notifications Table
     */
    private function create_partner_notifications_table()
    {
        $fields = [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'partner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'link' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'info',
                'comment'    => 'info, success, warning, danger'
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0
            ],
            'read_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'date_sent' => [
                'type' => 'DATETIME',
            ]
        ];
        
        $this->CI->dbforge->add_field($fields);
        $this->CI->dbforge->add_key('id', true);
        $this->CI->dbforge->add_key('partner_id');
        $this->CI->dbforge->add_key('is_read');
        $this->CI->dbforge->create_table('tblpartner_notifications', true);
    }
    
    /**
     * Partner Activities Table
     */
    private function create_partner_activities_table()
    {
        $fields = [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'partner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true
            ],
            'activity_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'description' => [
                'type' => 'TEXT',
            ],
            'reference_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'ID of related entity (deal, commission, etc.)'
            ],
            'reference_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Type of related entity'
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Staff ID if action by admin'
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ]
        ];
        
        $this->CI->dbforge->add_field($fields);
        $this->CI->dbforge->add_key('id', true);
        $this->CI->dbforge->add_key('partner_id');
        $this->CI->dbforge->create_table('tblpartner_activities', true);
    }
    
    /**
     * Partner Settings Table
     */
    private function create_partner_settings_table()
    {
        $fields = [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'setting_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true
            ],
            'setting_value' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'setting_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'text'
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ];
        
        $this->CI->dbforge->add_field($fields);
        $this->CI->dbforge->add_key('id', true);
        $this->CI->dbforge->add_key('setting_key');
        $this->CI->dbforge->create_table('tblpartner_settings', true);
    }
    
    /**
     * Partner Territories Table
     */
    private function create_partner_territories_table()
    {
        $fields = [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'territory_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'country' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'UAE'
            ],
            'state' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ]
        ];
        
        $this->CI->dbforge->add_field($fields);
        $this->CI->dbforge->add_key('id', true);
        $this->CI->dbforge->create_table('tblpartner_territories', true);
    }
    
    /**
     * Insert Default Settings
     */
    private function insert_default_settings()
    {
        $default_settings = [
            [
                'setting_key'   => 'partner_self_signup',
                'setting_value' => '1',
                'setting_type'  => 'boolean',
                'description'   => 'Allow partners to self-register'
            ],
            [
                'setting_key'   => 'partner_auto_approve',
                'setting_value' => '0',
                'setting_type'  => 'boolean',
                'description'   => 'Automatically approve new partner registrations'
            ],
            [
                'setting_key'   => 'deal_auto_create_lead',
                'setting_value' => '1',
                'setting_type'  => 'boolean',
                'description'   => 'Automatically create CRM lead after deal approval'
            ],
            [
                'setting_key'   => 'commission_payment_condition',
                'setting_value' => 'invoice_clearance',
                'setting_type'  => 'select',
                'description'   => 'Default commission payment condition'
            ],
            [
                'setting_key'   => 'duplicate_detection',
                'setting_value' => '1',
                'setting_type'  => 'boolean',
                'description'   => 'Enable duplicate lead detection'
            ],
            [
                'setting_key'   => 'notification_email',
                'setting_value' => '1',
                'setting_type'  => 'boolean',
                'description'   => 'Send email notifications'
            ],
            [
                'setting_key'   => 'notification_whatsapp',
                'setting_value' => '0',
                'setting_type'  => 'boolean',
                'description'   => 'Send WhatsApp notifications'
            ],
            [
                'setting_key'   => 'notification_telegram',
                'setting_value' => '0',
                'setting_type'  => 'boolean',
                'description'   => 'Send Telegram notifications'
            ],
            [
                'setting_key'   => 'partner_whatsapp_api_key',
                'setting_value' => '',
                'setting_type'  => 'text',
                'description'   => 'WhatsApp API Key'
            ],
            [
                'setting_key'   => 'partner_whatsapp_api_url',
                'setting_value' => '',
                'setting_type'  => 'text',
                'description'   => 'WhatsApp API URL'
            ],
            [
                'setting_key'   => 'partner_telegram_bot_token',
                'setting_value' => '',
                'setting_type'  => 'text',
                'description'   => 'Telegram Bot Token'
            ],
            [
                'setting_key'   => 'ai_lead_classification',
                'setting_value' => '0',
                'setting_type'  => 'boolean',
                'description'   => 'Enable AI lead classification'
            ],
            [
                'setting_key'   => 'ai_win_probability',
                'setting_value' => '0',
                'setting_type'  => 'boolean',
                'description'   => 'Enable AI win probability prediction'
            ],
            [
                'setting_key'   => 'deal_lock_period',
                'setting_value' => '30',
                'setting_type'  => 'number',
                'description'   => 'Days to lock deal after registration (prevent conflicts)'
            ],
            [
                'setting_key'   => 'commission_tier_config',
                'setting_value' => json_encode([
                    ['min' => 0, 'max' => 10000, 'rate' => 5],
                    ['min' => 10001, 'max' => 50000, 'rate' => 7],
                    ['min' => 50001, 'max' => 999999999, 'rate' => 10]
                ]),
                'setting_type'  => 'json',
                'description'   => 'Default tiered commission configuration'
            ]
        ];
        
        foreach ($default_settings as $setting) {
            $setting['updated_at'] = date('Y-m-d H:i:s');
            $this->CI->db->insert('tblpartner_settings', $setting);
        }
    }
    
    /**
     * Create Portal Permissions
     */
    private function create_partner_portal_permissions()
    {
        // Add custom permissions for partner module
        $permissions = [
            'partner_reseller' => [
                'capabilities' => [
                    'view',
                    'create',
                    'edit',
                    'delete',
                    'approve_deals',
                    'manage_commissions',
                    'view_reports'
                ]
            ]
        ];
        
        // This would integrate with Perfex's permission system
        // Implementation depends on Perfex CRM version
    }
}
