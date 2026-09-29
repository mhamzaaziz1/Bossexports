<?php
/**
 * Partner & Reseller Management — Uninstall Script
 * Drops all 23 module tables.  ⚠ Destructive — irreversible.
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Partner_Reseller_Management_Uninstall
{
    private $CI;
    private $prefix;

    public function __construct()
    {
        $this->CI     = &get_instance();
        $this->CI->load->dbforge();
        $this->prefix = db_prefix();
    }

    public function run()
    {
        $tables = [
            'prm_partner_sessions',
            'prm_audit_log',
            'prm_partner_badges',
            'prm_badges',
            'prm_certifications',
            'prm_training_progress',
            'prm_training_courses',
            'prm_mdf_campaigns',
            'prm_mdf_funds',
            'prm_payouts',
            'prm_wallet_ledger',
            'prm_clawbacks',
            'prm_recurring_commissions',
            'prm_commissions',
            'prm_deal_history',
            'prm_deals',
            'prm_partner_products',
            'prm_partner_tier_levels',
            'prm_partner_documents',
            'prm_partner_contacts',
            'prm_partner_hierarchy',
            'prm_partners',
            'prm_settings',
        ];
        foreach ($tables as $t) {
            $this->CI->dbforge->drop_table($this->prefix . $t, true);
        }
        return true;
    }
}
