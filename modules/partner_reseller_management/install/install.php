<?php
/**
 * Partner & Reseller Management — Install Script
 * Creates 18 tables, default settings, default tier ladder, default badges,
 * default commission product config, and seeds territories.
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Partner_Reseller_Management_Install
{
    private $CI;
    private $prefix;

    public function __construct()
    {
        $this->CI     = &get_instance();
        $this->CI->load->dbforge();
        $this->prefix = db_prefix(); // tbl
    }

    public function run()
    {
        $this->t_partners();
        $this->t_partner_hierarchy();
        $this->t_partner_contacts();
        $this->t_partner_documents();
        $this->t_partner_tier_levels();
        $this->t_partner_products();
        $this->t_deals();
        $this->t_deal_history();
        $this->t_commissions();
        $this->t_recurring_commissions();
        $this->t_clawbacks();
        $this->t_wallet_ledger();
        $this->t_payouts();
        $this->t_mdf_funds();
        $this->t_mdf_campaigns();
        $this->t_training_courses();
        $this->t_training_progress();
        $this->t_certifications();
        $this->t_badges();
        $this->t_partner_badges();
        $this->t_audit_log();
        $this->t_partner_sessions();
        $this->t_settings();
        $this->seed_settings();
        $this->seed_tier_levels();
        $this->seed_badges();
    }

    /* =====================================================================
     *  Helper for adding a forge field of type INT/UNSIGNED/AI primary key
     * =================================================================== */
    private function pk()
    {
        return [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
        ];
    }

    private function ts()
    {
        return [
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ];
    }

    private function create($table, array $fields, array $keys = [], $unique = [])
    {
        $this->CI->dbforge->add_field($fields);
        $this->CI->dbforge->add_key('id', true);
        foreach ($keys as $k) {
            $this->CI->dbforge->add_key($k);
        }
        $res = $this->CI->dbforge->create_table($table, true, [
            'ENGINE'  => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
        if (!$res) {
            $error = $this->CI->db->error();
            file_put_contents(__DIR__.'/db_errors.log', date('Y-m-d H:i:s')." | Failed {$table}: " . print_r($error, true) . "\n", FILE_APPEND);
            
            // Manual reset to prevent CI dbforge cascade failure bug
            if (isset($this->CI->dbforge)) {
                $this->CI->dbforge->fields = [];
                $this->CI->dbforge->keys = [];
                $this->CI->dbforge->primary_keys = [];
            }
        }
        foreach ($unique as $col) {
            // Best-effort unique index (forge doesn't expose unique on add_key)
            try {
                $this->CI->db->query("ALTER TABLE `{$this->prefix}{$table}` ADD UNIQUE KEY `uniq_{$col}` (`{$col}`)");
            } catch (\Exception $e) {
                // Ignore if key already exists
            }
        }
    }

    /* =====================================================================
     *  TABLE: prm_partners (master)
     * =================================================================== */
    private function t_partners()
    {
        $f = array_merge($this->pk(), [
            'partner_code'      => ['type' => 'VARCHAR', 'constraint' => 30],
            'company_name'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'partner_type'      => ['type' => 'ENUM', 'constraint' => ['reseller','referral','contractor','corporate','distributor','affiliate','msp','vad'], 'default' => 'reseller'],
            'tier_level_id'     => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'parent_partner_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'comment' => 'For multi-tier hierarchy'],
            'email'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'phone'             => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'website'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'logo'              => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'address'           => ['type' => 'TEXT', 'null' => true],
            'city'              => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'state'             => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'country'           => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'zip_code'          => ['type' => 'VARCHAR', 'constraint' => 20,  'null' => true],
            'currency'          => ['type' => 'VARCHAR', 'constraint' => 5,   'default' => 'USD'],
            'language'          => ['type' => 'VARCHAR', 'constraint' => 10,  'default' => 'english'],
            'timezone'          => ['type' => 'VARCHAR', 'constraint' => 50,  'null' => true],
            'trade_license'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'tax_number'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'iban'              => ['type' => 'VARCHAR', 'constraint' => 50,  'null' => true],
            'swift'             => ['type' => 'VARCHAR', 'constraint' => 30,  'null' => true],
            'bank_name'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'contract_start'    => ['type' => 'DATE', 'null' => true],
            'contract_end'      => ['type' => 'DATE', 'null' => true],
            'commission_type'   => ['type' => 'ENUM', 'constraint' => ['fixed','percentage','tiered','hybrid','product_based'], 'default' => 'percentage'],
            'commission_value'  => ['type' => 'DECIMAL', 'constraint' => '12,4', 'default' => 0],
            'commission_config' => ['type' => 'TEXT', 'null' => true, 'comment' => 'JSON'],
            'override_rate'     => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0, 'comment' => '% override paid to parent partner'],
            'territory_ids'     => ['type' => 'TEXT', 'null' => true, 'comment' => 'CSV/JSON'],
            'allowed_products'  => ['type' => 'TEXT', 'null' => true, 'comment' => 'CSV/JSON'],
            'status'            => ['type' => 'ENUM', 'constraint' => ['draft','pending','active','suspended','terminated'], 'default' => 'pending'],
            'kyc_status'        => ['type' => 'ENUM', 'constraint' => ['unverified','submitted','approved','rejected'], 'default' => 'unverified'],
            'portal_access'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'username'          => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'password_hash'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            '2fa_secret'        => ['type' => 'VARCHAR', 'constraint' => 64,  'null' => true],
            '2fa_enabled'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'last_login'        => ['type' => 'DATETIME', 'null' => true],
            'last_login_ip'     => ['type' => 'VARCHAR', 'constraint' => 45,  'null' => true],
            'failed_login_attempts' => ['type' => 'INT', 'constraint' => 4, 'default' => 0],
            'locked_until'      => ['type' => 'DATETIME', 'null' => true],
            'total_deals'       => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'total_revenue'     => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'total_commission'  => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'lifetime_score'    => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'wallet_balance'    => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'wallet_pending'    => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'sla_score'         => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0, 'comment' => 'Performance scorecard 0-100'],
            'assigned_manager'  => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'comment' => 'staff id'],
            'notes'             => ['type' => 'TEXT', 'null' => true],
            'metadata'          => ['type' => 'TEXT', 'null' => true, 'comment' => 'JSON for custom fields'],
            'created_by'        => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ], $this->ts());

        $this->create('prm_partners', $f, ['partner_code','email','status','tier_level_id','parent_partner_id'], ['partner_code','email','username']);
    }

    /* =====================================================================
     *  TABLE: prm_partner_hierarchy
     *  Closure table for fast ancestor / descendant lookup (multi-tier)
     * =================================================================== */
    private function t_partner_hierarchy()
    {
        $f = array_merge($this->pk(), [
            'ancestor_id'   => ['type' => 'INT', 'constraint' => 11],
            'descendant_id' => ['type' => 'INT', 'constraint' => 11],
            'depth'         => ['type' => 'INT', 'constraint' => 4, 'default' => 0],
        ], $this->ts());
        $this->create('prm_partner_hierarchy', $f, ['ancestor_id','descendant_id']);
    }

    private function t_partner_contacts()
    {
        $f = array_merge($this->pk(), [
            'partner_id'  => ['type' => 'INT', 'constraint' => 11],
            'first_name'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'last_name'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'email'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'phone'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'position'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'is_primary'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'can_login'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'password_hash'=> ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ], $this->ts());
        $this->create('prm_partner_contacts', $f, ['partner_id']);
    }

    private function t_partner_documents()
    {
        $f = array_merge($this->pk(), [
            'partner_id'    => ['type' => 'INT', 'constraint' => 11],
            'document_type' => ['type' => 'VARCHAR', 'constraint' => 80, 'comment' => 'trade_license, passport, vat_cert, contract...'],
            'file_name'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'file_path'     => ['type' => 'VARCHAR', 'constraint' => 500],
            'file_size'     => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'mime'          => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'expiry_date'   => ['type' => 'DATE', 'null' => true],
            'verified'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'verified_by'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'verified_at'   => ['type' => 'DATETIME', 'null' => true],
            'esign_id'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'comment' => 'External e-sign reference'],
            'uploaded_by'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ], $this->ts());
        $this->create('prm_partner_documents', $f, ['partner_id','document_type']);
    }

    private function t_partner_tier_levels()
    {
        $f = array_merge($this->pk(), [
            'name'              => ['type' => 'VARCHAR', 'constraint' => 100],
            'slug'              => ['type' => 'VARCHAR', 'constraint' => 50],
            'min_revenue'       => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'min_deals'         => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'commission_boost'  => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0, 'comment' => 'extra % on top of base'],
            'mdf_quota'         => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'badge_color'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '#888'],
            'priority'          => ['type' => 'INT', 'constraint' => 4, 'default' => 0],
            'description'       => ['type' => 'TEXT', 'null' => true],
        ], $this->ts());
        $this->create('prm_partner_tier_levels', $f, ['slug']);
    }

    private function t_partner_products()
    {
        $f = array_merge($this->pk(), [
            'name'             => ['type' => 'VARCHAR', 'constraint' => 200],
            'sku'              => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'description'      => ['type' => 'TEXT', 'null' => true],
            'commission_type'  => ['type' => 'ENUM', 'constraint' => ['fixed','percentage'], 'default' => 'percentage'],
            'commission_value' => ['type' => 'DECIMAL', 'constraint' => '10,4', 'default' => 0],
            'recurring'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'recurring_period' => ['type' => 'ENUM', 'constraint' => ['monthly','quarterly','yearly'], 'null' => true],
            'recurring_cycles' => ['type' => 'INT', 'constraint' => 4, 'default' => 0, 'comment' => '0=unlimited'],
            'active'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ], $this->ts());
        $this->create('prm_partner_products', $f, ['sku']);
    }

    /* =====================================================================
     *  DEALS — registered leads with collision protection
     * =================================================================== */
    private function t_deals()
    {
        $f = array_merge($this->pk(), [
            'partner_id'         => ['type' => 'INT', 'constraint' => 11],
            'deal_number'        => ['type' => 'VARCHAR', 'constraint' => 30],
            'client_name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'company_name'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'email'              => ['type' => 'VARCHAR', 'constraint' => 100],
            'phone'              => ['type' => 'VARCHAR', 'constraint' => 50],
            'location'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'service_type'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'product_id'         => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'business_unit'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'estimated_value'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'actual_value'       => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'currency'           => ['type' => 'VARCHAR', 'constraint' => 5, 'default' => 'USD'],
            'fx_rate'            => ['type' => 'DECIMAL', 'constraint' => '12,6', 'default' => 1],
            'description'        => ['type' => 'TEXT', 'null' => true],
            'requirements'       => ['type' => 'TEXT', 'null' => true],
            'expected_close_date'=> ['type' => 'DATE', 'null' => true],
            'lock_expires_at'    => ['type' => 'DATETIME', 'null' => true, 'comment' => 'Deal protection window'],
            'pipeline_stage'     => ['type' => 'ENUM', 'constraint' => ['submitted','approved','contacted','qualified','proposal','negotiation','won','lost','withdrawn','expired'], 'default' => 'submitted'],
            'approval_status'    => ['type' => 'ENUM', 'constraint' => ['pending','approved','rejected','info_required'], 'default' => 'pending'],
            'duplicate_flag'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'duplicate_of'       => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'fingerprint'        => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'comment' => 'sha hash for fast lookup'],
            'lead_id'            => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'comment' => 'Perfex lead'],
            'project_id'         => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'invoice_id'         => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'commission_amount'  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'commission_status'  => ['type' => 'ENUM', 'constraint' => ['pending','calculated','approved','paid','clawed_back'], 'default' => 'pending'],
            'win_probability'    => ['type' => 'INT', 'constraint' => 3, 'null' => true, 'comment' => 'AI 0-100'],
            'lead_score'         => ['type' => 'INT', 'constraint' => 4, 'null' => true],
            'ai_classification'  => ['type' => 'TEXT', 'null' => true],
            'notes'              => ['type' => 'TEXT', 'null' => true],
            'admin_notes'        => ['type' => 'TEXT', 'null' => true],
            'rejection_reason'   => ['type' => 'TEXT', 'null' => true],
            'assigned_to'        => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'comment' => 'staff id'],
            'submitted_at'       => ['type' => 'DATETIME', 'null' => true],
            'approved_at'        => ['type' => 'DATETIME', 'null' => true],
            'approved_by'        => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'closed_at'          => ['type' => 'DATETIME', 'null' => true],
        ], $this->ts());
        $this->create('prm_deals', $f, ['partner_id','deal_number','approval_status','pipeline_stage','fingerprint','lead_id'], ['deal_number']);
    }

    private function t_deal_history()
    {
        $f = array_merge($this->pk(), [
            'deal_id'      => ['type' => 'INT', 'constraint' => 11],
            'event'        => ['type' => 'VARCHAR', 'constraint' => 80],
            'from_stage'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'to_stage'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'note'         => ['type' => 'TEXT', 'null' => true],
            'actor_type'   => ['type' => 'ENUM', 'constraint' => ['partner','staff','system'], 'default' => 'system'],
            'actor_id'     => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ], $this->ts());
        $this->create('prm_deal_history', $f, ['deal_id']);
    }

    /* =====================================================================
     *  COMMISSIONS
     * =================================================================== */
    private function t_commissions()
    {
        $f = array_merge($this->pk(), [
            'partner_id'        => ['type' => 'INT', 'constraint' => 11],
            'deal_id'           => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'recurring_id'      => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'commission_number' => ['type' => 'VARCHAR', 'constraint' => 30],
            'kind'              => ['type' => 'ENUM', 'constraint' => ['deal','override','recurring','bonus','spiff','adjustment','clawback'], 'default' => 'deal'],
            'deal_value'        => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'commission_type'   => ['type' => 'ENUM', 'constraint' => ['fixed','percentage','tiered','hybrid'], 'default' => 'percentage'],
            'commission_rate'   => ['type' => 'DECIMAL', 'constraint' => '8,4', 'default' => 0],
            'commission_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'currency'          => ['type' => 'VARCHAR', 'constraint' => 5, 'default' => 'USD'],
            'fx_rate'           => ['type' => 'DECIMAL', 'constraint' => '12,6', 'default' => 1],
            'tax_amount'        => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'net_amount'        => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'split_partners'    => ['type' => 'TEXT', 'null' => true, 'comment' => 'JSON [{partner_id,pct}]'],
            'payment_condition' => ['type' => 'ENUM', 'constraint' => ['on_approval','project_completion','invoice_clearance','multi_stage'], 'default' => 'invoice_clearance'],
            'payment_stages'    => ['type' => 'TEXT', 'null' => true, 'comment' => 'JSON'],
            'status'            => ['type' => 'ENUM', 'constraint' => ['pending','calculated','approved','on_hold','paid','clawed_back','cancelled'], 'default' => 'pending'],
            'invoice_number'    => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'invoice_date'      => ['type' => 'DATE', 'null' => true],
            'paid_amount'       => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'paid_date'         => ['type' => 'DATE', 'null' => true],
            'payout_id'         => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'notes'             => ['type' => 'TEXT', 'null' => true],
            'approved_by'       => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'approved_at'       => ['type' => 'DATETIME', 'null' => true],
        ], $this->ts());
        $this->create('prm_commissions', $f, ['partner_id','deal_id','status','kind','payout_id'], ['commission_number']);
    }

    private function t_recurring_commissions()
    {
        $f = array_merge($this->pk(), [
            'partner_id'        => ['type' => 'INT', 'constraint' => 11],
            'deal_id'           => ['type' => 'INT', 'constraint' => 11],
            'product_id'        => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'period'            => ['type' => 'ENUM', 'constraint' => ['monthly','quarterly','yearly']],
            'rate'              => ['type' => 'DECIMAL', 'constraint' => '8,4', 'default' => 0],
            'cycles_total'      => ['type' => 'INT', 'constraint' => 4, 'default' => 0, 'comment' => '0=unlimited'],
            'cycles_paid'       => ['type' => 'INT', 'constraint' => 4, 'default' => 0],
            'next_run_date'     => ['type' => 'DATE', 'null' => true],
            'base_amount'       => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'status'            => ['type' => 'ENUM', 'constraint' => ['active','paused','ended','cancelled'], 'default' => 'active'],
        ], $this->ts());
        $this->create('prm_recurring_commissions', $f, ['partner_id','deal_id','status','next_run_date']);
    }

    private function t_clawbacks()
    {
        $f = array_merge($this->pk(), [
            'commission_id' => ['type' => 'INT', 'constraint' => 11],
            'partner_id'    => ['type' => 'INT', 'constraint' => 11],
            'amount'        => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'reason'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'reason_code'   => ['type' => 'ENUM', 'constraint' => ['refund','cancellation','chargeback','dispute','fraud','other'], 'default' => 'other'],
            'recovered'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_by'    => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ], $this->ts());
        $this->create('prm_clawbacks', $f, ['partner_id','commission_id']);
    }

    /* =====================================================================
     *  WALLET & PAYOUTS
     * =================================================================== */
    private function t_wallet_ledger()
    {
        $f = array_merge($this->pk(), [
            'partner_id'   => ['type' => 'INT', 'constraint' => 11],
            'entry_type'   => ['type' => 'ENUM', 'constraint' => ['credit','debit','hold','release']],
            'amount'       => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'balance_after'=> ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'currency'     => ['type' => 'VARCHAR', 'constraint' => 5, 'default' => 'USD'],
            'reference_type'=> ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'reference_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'description'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ], $this->ts());
        $this->create('prm_wallet_ledger', $f, ['partner_id','entry_type']);
    }

    private function t_payouts()
    {
        $f = array_merge($this->pk(), [
            'partner_id'      => ['type' => 'INT', 'constraint' => 11],
            'payout_number'   => ['type' => 'VARCHAR', 'constraint' => 30],
            'commission_ids'  => ['type' => 'TEXT'],
            'total_amount'    => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'currency'        => ['type' => 'VARCHAR', 'constraint' => 5, 'default' => 'USD'],
            'payment_method'  => ['type' => 'VARCHAR', 'constraint' => 50],
            'reference'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status'          => ['type' => 'ENUM', 'constraint' => ['requested','approved','processing','paid','rejected','failed'], 'default' => 'requested'],
            'requested_at'    => ['type' => 'DATETIME', 'null' => true],
            'approved_by'     => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'approved_at'     => ['type' => 'DATETIME', 'null' => true],
            'paid_at'         => ['type' => 'DATETIME', 'null' => true],
            'notes'           => ['type' => 'TEXT', 'null' => true],
        ], $this->ts());
        $this->create('prm_payouts', $f, ['partner_id','status'], ['payout_number']);
    }

    /* =====================================================================
     *  MDF (Market Development Fund) & Co-marketing campaigns
     * =================================================================== */
    private function t_mdf_funds()
    {
        $f = array_merge($this->pk(), [
            'partner_id'  => ['type' => 'INT', 'constraint' => 11],
            'fiscal_year' => ['type' => 'YEAR'],
            'allocated'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'used'        => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'remaining'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'currency'    => ['type' => 'VARCHAR', 'constraint' => 5, 'default' => 'USD'],
            'expires_at'  => ['type' => 'DATE', 'null' => true],
            'notes'       => ['type' => 'TEXT', 'null' => true],
        ], $this->ts());
        $this->create('prm_mdf_funds', $f, ['partner_id','fiscal_year']);
    }

    private function t_mdf_campaigns()
    {
        $f = array_merge($this->pk(), [
            'partner_id'    => ['type' => 'INT', 'constraint' => 11],
            'fund_id'       => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'campaign_type' => ['type' => 'ENUM', 'constraint' => ['digital_ads','event','content','training','print','other'], 'default' => 'digital_ads'],
            'budget'        => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'spent'         => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'expected_leads'=> ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'actual_leads'  => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'roi'           => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 0],
            'start_date'    => ['type' => 'DATE', 'null' => true],
            'end_date'      => ['type' => 'DATE', 'null' => true],
            'status'        => ['type' => 'ENUM', 'constraint' => ['draft','submitted','approved','running','completed','rejected'], 'default' => 'draft'],
            'description'   => ['type' => 'TEXT', 'null' => true],
            'attachments'   => ['type' => 'TEXT', 'null' => true, 'comment' => 'JSON'],
        ], $this->ts());
        $this->create('prm_mdf_campaigns', $f, ['partner_id','status']);
    }

    /* =====================================================================
     *  TRAINING & CERTIFICATIONS
     * =================================================================== */
    private function t_training_courses()
    {
        $f = array_merge($this->pk(), [
            'title'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'description'   => ['type' => 'TEXT', 'null' => true],
            'duration_min'  => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'difficulty'    => ['type' => 'ENUM', 'constraint' => ['beginner','intermediate','advanced','expert'], 'default' => 'beginner'],
            'content_url'   => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'video_url'     => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'quiz_questions'=> ['type' => 'TEXT', 'null' => true, 'comment' => 'JSON'],
            'pass_score'    => ['type' => 'INT', 'constraint' => 3, 'default' => 70],
            'awards_badge_id'=> ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'awards_points' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'tier_required' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'mandatory'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'active'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ], $this->ts());
        $this->create('prm_training_courses', $f, ['slug','active']);
    }

    private function t_training_progress()
    {
        $f = array_merge($this->pk(), [
            'partner_id'   => ['type' => 'INT', 'constraint' => 11],
            'contact_id'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'course_id'    => ['type' => 'INT', 'constraint' => 11],
            'progress_pct' => ['type' => 'INT', 'constraint' => 3, 'default' => 0],
            'score'        => ['type' => 'INT', 'constraint' => 3, 'null' => true],
            'attempts'     => ['type' => 'INT', 'constraint' => 4, 'default' => 0],
            'started_at'   => ['type' => 'DATETIME', 'null' => true],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
            'passed'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ], $this->ts());
        $this->create('prm_training_progress', $f, ['partner_id','course_id','passed']);
    }

    private function t_certifications()
    {
        $f = array_merge($this->pk(), [
            'partner_id'   => ['type' => 'INT', 'constraint' => 11],
            'contact_id'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'cert_name'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'cert_code'    => ['type' => 'VARCHAR', 'constraint' => 80],
            'issued_at'    => ['type' => 'DATE'],
            'expires_at'   => ['type' => 'DATE', 'null' => true],
            'pdf_path'     => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'verification_code'=>['type'=>'VARCHAR','constraint'=>40, 'null'=>true],
            'revoked'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ], $this->ts());
        $this->create('prm_certifications', $f, ['partner_id','cert_code'], ['verification_code']);
    }

    /* =====================================================================
     *  GAMIFICATION
     * =================================================================== */
    private function t_badges()
    {
        $f = array_merge($this->pk(), [
            'name'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 80],
            'description' => ['type' => 'TEXT', 'null' => true],
            'icon'        => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'fa-medal'],
            'color'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '#f0ad4e'],
            'rule_json'   => ['type' => 'TEXT', 'null' => true, 'comment' => 'Awarding rules'],
            'points'      => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ], $this->ts());
        $this->create('prm_badges', $f, ['slug','active']);
    }

    private function t_partner_badges()
    {
        $f = array_merge($this->pk(), [
            'partner_id' => ['type' => 'INT', 'constraint' => 11],
            'badge_id'   => ['type' => 'INT', 'constraint' => 11],
            'awarded_at' => ['type' => 'DATETIME'],
        ], $this->ts());
        $this->create('prm_partner_badges', $f, ['partner_id','badge_id']);
    }

    /* =====================================================================
     *  AUDIT & SECURITY
     * =================================================================== */
    private function t_audit_log()
    {
        $f = array_merge($this->pk(), [
            'actor_type'    => ['type' => 'ENUM', 'constraint' => ['staff','partner','system','api']],
            'actor_id'      => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'action'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'entity_type'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'entity_id'     => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'before_state'  => ['type' => 'TEXT', 'null' => true],
            'after_state'   => ['type' => 'TEXT', 'null' => true],
            'ip_address'    => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'severity'      => ['type' => 'ENUM', 'constraint' => ['info','notice','warning','critical'], 'default' => 'info'],
        ], $this->ts());
        $this->create('prm_audit_log', $f, ['actor_type','entity_type','action','severity']);
    }

    private function t_partner_sessions()
    {
        $f = array_merge($this->pk(), [
            'partner_id'  => ['type' => 'INT', 'constraint' => 11],
            'session_token'=>['type' => 'VARCHAR', 'constraint' => 128],
            'ip_address'  => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'expires_at'  => ['type' => 'DATETIME'],
            'revoked'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ], $this->ts());
        $this->create('prm_partner_sessions', $f, ['partner_id','session_token']);
    }

    private function t_settings()
    {
        $f = array_merge($this->pk(), [
            'setting_key'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'setting_value' => ['type' => 'TEXT', 'null' => true],
            'setting_type'  => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'text'],
            'description'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_public'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ], $this->ts());
        $this->create('prm_settings', $f, ['setting_key'], ['setting_key']);
    }

    /* =====================================================================
     *  SEEDS
     * =================================================================== */
    private function seed_settings()
    {
        $defaults = [
            ['prm_self_signup',           '1',  'boolean', 'Allow partners to self-register'],
            ['prm_auto_approve_partner',  '0',  'boolean', 'Auto-approve new partner registrations'],
            ['prm_auto_create_lead',      '1',  'boolean', 'Auto-create CRM lead on deal approval'],
            ['prm_lead_source_id',        '0',  'number',  'Default lead source id'],
            ['prm_lead_default_status',   '1',  'number',  'Default lead status id'],
            ['prm_duplicate_detection',   '1',  'boolean', 'Enable duplicate detection'],
            ['prm_duplicate_threshold',   '85', 'number',  'Fuzzy match threshold (0-100)'],
            ['prm_deal_lock_days',        '30', 'number',  'Days a registered deal is locked from collision'],
            ['prm_default_currency',      'USD','text',    'Base currency'],
            ['prm_multi_currency',        '1',  'boolean', 'Enable multi-currency'],
            ['prm_payout_min_amount',     '100','number',  'Minimum payout request amount'],
            ['prm_payout_schedule',       'monthly','select','Default payout schedule'],
            ['prm_2fa_required_admin',    '1',  'boolean', 'Require 2FA for admin partners'],
            ['prm_session_timeout',       '120','number',  'Partner session timeout (minutes)'],
            ['prm_max_login_attempts',    '5',  'number',  'Max failed logins before lockout'],
            ['prm_login_lockout_minutes', '30', 'number',  'Lockout duration in minutes'],
            ['prm_notify_email',          '1',  'boolean', 'Email notifications enabled'],
            ['prm_notify_sms',            '0',  'boolean', 'SMS notifications enabled'],
            ['prm_notify_whatsapp',       '0',  'boolean', 'WhatsApp notifications enabled'],
            ['prm_notify_telegram',       '0',  'boolean', 'Telegram notifications enabled'],
            ['prm_whatsapp_provider',     'meta','select', 'WhatsApp provider (meta/twilio/360dialog)'],
            ['prm_whatsapp_token',        '',   'password','WhatsApp API token'],
            ['prm_whatsapp_phone_id',     '',   'text',    'WhatsApp phone number id'],
            ['prm_telegram_bot_token',    '',   'password','Telegram bot token'],
            ['prm_sms_gateway',           '',   'select',  'SMS gateway'],
            ['prm_webhook_secret',        bin2hex(random_bytes(16)), 'password', 'Outgoing webhook signing secret'],
            ['prm_ai_lead_scoring',       '0',  'boolean', 'Enable AI lead scoring'],
            ['prm_ai_provider',           'openai','select','AI provider (openai/anthropic/local)'],
            ['prm_ai_api_key',            '',   'password','AI API key'],
            ['prm_ai_model',              'gpt-4o-mini','text','AI model'],
            ['prm_gamification_enabled',  '1',  'boolean', 'Enable gamification'],
            ['prm_mdf_enabled',           '1',  'boolean', 'Enable MDF/co-marketing'],
            ['prm_training_enabled',      '1',  'boolean', 'Enable training & certification'],
            ['prm_recurring_enabled',     '1',  'boolean', 'Enable recurring commissions (AMC/SaaS)'],
            ['prm_clawback_window_days',  '90', 'number',  'Clawback eligibility window after pay'],
            ['prm_tax_handling',          'inclusive','select','Tax inclusive/exclusive on commissions'],
            ['prm_default_vat_rate',      '5',  'number',  'Default VAT rate (e.g., UAE 5%)'],
            ['prm_portal_branding_logo',  '',   'text',    'Partner portal logo URL'],
            ['prm_portal_brand_color',    '#2C3E50','text','Partner portal primary color'],
            ['prm_portal_welcome',        'Welcome to our Partner Portal','text', 'Portal welcome message'],
            ['prm_pdf_template_invoice',  'default','select','Commission invoice PDF template'],
        ];
        foreach ($defaults as $row) {
            if ($this->CI->db->where('setting_key', $row[0])->count_all_results($this->prefix . 'prm_settings') == 0) {
                $this->CI->db->insert($this->prefix . 'prm_settings', [
                    'setting_key'   => $row[0],
                    'setting_value' => $row[1],
                    'setting_type'  => $row[2],
                    'description'   => $row[3],
                    'is_public'     => 0,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    private function seed_tier_levels()
    {
        $tiers = [
            ['Bronze',    'bronze',    0,        0,  0, 5000,  '#CD7F32', 1, 'Entry-level partner'],
            ['Silver',    'silver',    50000,    5,  1, 10000, '#C0C0C0', 2, 'Established partner'],
            ['Gold',      'gold',      250000,   20, 2, 25000, '#FFD700', 3, 'Top performer'],
            ['Platinum',  'platinum',  1000000,  50, 3, 50000, '#E5E4E2', 4, 'Strategic partner'],
            ['Diamond',   'diamond',   5000000,  200,5, 100000,'#B9F2FF', 5, 'Elite partner'],
        ];
        foreach ($tiers as $t) {
            if ($this->CI->db->where('slug', $t[1])->count_all_results($this->prefix . 'prm_partner_tier_levels') == 0) {
                $this->CI->db->insert($this->prefix . 'prm_partner_tier_levels', [
                    'name'              => $t[0],
                    'slug'              => $t[1],
                    'min_revenue'       => $t[2],
                    'min_deals'         => $t[3],
                    'commission_boost'  => $t[4],
                    'mdf_quota'         => $t[5],
                    'badge_color'       => $t[6],
                    'priority'          => $t[7],
                    'description'       => $t[8],
                    'created_at'        => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    private function seed_badges()
    {
        $badges = [
            ['First Deal','first-deal','Submitted your first deal','fa-flag','#5BC0DE',
             json_encode(['type'=>'deal_count','op'=>'>=','value'=>1]), 50],
            ['Closer','closer','Won 10 deals','fa-handshake-o','#5CB85C',
             json_encode(['type'=>'wins','op'=>'>=','value'=>10]), 200],
            ['Six-Figure','six-figure','Generated $100k+ revenue','fa-money','#F0AD4E',
             json_encode(['type'=>'revenue','op'=>'>=','value'=>100000]), 500],
            ['Certified','certified','Completed mandatory certification','fa-certificate','#9B59B6',
             json_encode(['type'=>'certification','op'=>'=','value'=>1]), 100],
            ['Streak Master','streak-master','5 deals in 30 days','fa-fire','#E74C3C',
             json_encode(['type'=>'streak','op'=>'>=','value'=>5]), 300],
        ];
        foreach ($badges as $b) {
            if ($this->CI->db->where('slug', $b[1])->count_all_results($this->prefix . 'prm_badges') == 0) {
                $this->CI->db->insert($this->prefix . 'prm_badges', [
                    'name'        => $b[0],
                    'slug'        => $b[1],
                    'description' => $b[2],
                    'icon'        => $b[3],
                    'color'       => $b[4],
                    'rule_json'   => $b[5],
                    'points'      => $b[6],
                    'active'      => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
