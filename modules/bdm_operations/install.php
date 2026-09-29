<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * BDM Operations — Advanced Installer (UAE Edition)
 * Senior Dev Build · IT & Fiber Optic Infrastructure
 * Aligned with the BDM Process & Marketing Plan (Advanced Planning Document)
 */

$charset = $CI->db->char_set;
$prefix  = db_prefix();

/* ----------------------------------------------------------------------- */
/* 1. BRANDS                                                               */
/* ----------------------------------------------------------------------- */
if (!$CI->db->table_exists($prefix . 'brands')) {
    $CI->db->query('CREATE TABLE `' . $prefix . "brands` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `description` text DEFAULT NULL,
      `country_id` int(11) DEFAULT NULL,
      `category` varchar(100) DEFAULT NULL,
      `website` varchar(255) DEFAULT NULL,
      `logo` varchar(255) DEFAULT NULL,
      `created_by` int(11) NOT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `country_id` (`country_id`),
      KEY `created_by` (`created_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . ';');
} else {
    foreach (['country_id' => 'int(11) DEFAULT NULL',
              'category'   => "varchar(100) DEFAULT NULL",
              'website'    => "varchar(255) DEFAULT NULL",
              'logo'       => "varchar(255) DEFAULT NULL"] as $col => $def) {
        if (!$CI->db->field_exists($col, $prefix . 'brands')) {
            $CI->db->query('ALTER TABLE `' . $prefix . 'brands` ADD `' . $col . '` ' . $def . ';');
        }
    }
}

/* ----------------------------------------------------------------------- */
/* 2. DISTRIBUTORS / VENDORS (Supplier Onboarding)                          */
/* ----------------------------------------------------------------------- */
if (!$CI->db->table_exists($prefix . 'distributors')) {
    $CI->db->query('CREATE TABLE `' . $prefix . "distributors` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `brand_id` int(11) DEFAULT NULL,
      `classification` varchar(100) DEFAULT NULL,
      `segment` varchar(100) DEFAULT NULL,
      `contact_name` varchar(255) DEFAULT NULL,
      `email` varchar(255) DEFAULT NULL,
      `phone` varchar(50) DEFAULT NULL,
      `whatsapp` varchar(50) DEFAULT NULL,
      `website` varchar(255) DEFAULT NULL,
      `address` text DEFAULT NULL,
      `emirate` varchar(100) DEFAULT NULL,
      `trade_license_no` varchar(100) DEFAULT NULL,
      `vat_trn` varchar(50) DEFAULT NULL,
      `cr_number` varchar(100) DEFAULT NULL,
      `iso_certified` tinyint(1) DEFAULT 0,
      `vendor_role` varchar(150) DEFAULT NULL,
      `products_services` text DEFAULT NULL,
      `price_range` varchar(100) DEFAULT NULL,
      `assigned_bdm` int(11) DEFAULT NULL,
      `follow_up_date` date DEFAULT NULL,
      `status` varchar(50) DEFAULT 'Active',
      `notes` text DEFAULT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `brand_id` (`brand_id`),
      KEY `assigned_bdm` (`assigned_bdm`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . ';');
} else {
    $cols = [
        'classification'    => "varchar(100) DEFAULT NULL",
        'segment'           => "varchar(100) DEFAULT NULL",
        'whatsapp'          => "varchar(50) DEFAULT NULL",
        'website'           => "varchar(255) DEFAULT NULL",
        'address'           => "text DEFAULT NULL",
        'emirate'           => "varchar(100) DEFAULT NULL",
        'trade_license_no'  => "varchar(100) DEFAULT NULL",
        'vat_trn'           => "varchar(50) DEFAULT NULL",
        'cr_number'         => "varchar(100) DEFAULT NULL",
        'iso_certified'     => "tinyint(1) DEFAULT 0",
        'vendor_role'       => "varchar(150) DEFAULT NULL",
        'products_services' => "text DEFAULT NULL",
        'price_range'       => "varchar(100) DEFAULT NULL",
        'follow_up_date'    => "date DEFAULT NULL",
        'status'            => "varchar(50) DEFAULT 'Active'",
        'notes'             => "text DEFAULT NULL",
    ];
    foreach ($cols as $col => $def) {
        if (!$CI->db->field_exists($col, $prefix . 'distributors')) {
            $CI->db->query('ALTER TABLE `' . $prefix . 'distributors` ADD `' . $col . '` ' . $def . ';');
        }
    }
}

/* ----------------------------------------------------------------------- */
/* 3. VR REGISTRATIONS — Vendor/Client Onboarding (10-step pipeline)        */
/* ----------------------------------------------------------------------- */
if (!$CI->db->table_exists($prefix . 'vr_registrations')) {
    $CI->db->query('CREATE TABLE `' . $prefix . "vr_registrations` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `lead_id` int(11) DEFAULT NULL,
      `client_id` int(11) DEFAULT NULL,
      `registration_type` varchar(100) DEFAULT 'Supplier Onboarding',
      `pipeline_step` tinyint(2) DEFAULT 1,
      `status` varchar(50) DEFAULT 'New Referral',
      `priority` varchar(20) DEFAULT 'Medium',
      `segment` varchar(100) DEFAULT NULL,
      `decision_maker` varchar(255) DEFAULT NULL,
      `decision_maker_role` varchar(150) DEFAULT NULL,
      `email` varchar(255) DEFAULT NULL,
      `phone` varchar(50) DEFAULT NULL,
      `expected_value` decimal(15,2) DEFAULT 0.00,
      `won_value` decimal(15,2) DEFAULT 0.00,
      `currency` varchar(10) DEFAULT 'AED',
      `lead_source` varchar(100) DEFAULT NULL,
      `documents_submitted` text DEFAULT NULL,
      `compliance_iso` tinyint(1) DEFAULT 0,
      `compliance_trade_license` tinyint(1) DEFAULT 0,
      `compliance_vat_trn` tinyint(1) DEFAULT 0,
      `compliance_cr` tinyint(1) DEFAULT 0,
      `registration_date` date DEFAULT NULL,
      `target_close_date` date DEFAULT NULL,
      `follow_up_date` date DEFAULT NULL,
      `assigned_bdm` int(11) DEFAULT NULL,
      `notes` text DEFAULT NULL,
      `created_at` datetime NOT NULL,
      `updated_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `company_id` (`company_id`),
      KEY `assigned_bdm` (`assigned_bdm`),
      KEY `pipeline_step` (`pipeline_step`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . ';');
} else {
    $cols = [
        'registration_type'        => "varchar(100) DEFAULT 'Supplier Onboarding'",
        'pipeline_step'            => "tinyint(2) DEFAULT 1",
        'priority'                 => "varchar(20) DEFAULT 'Medium'",
        'segment'                  => "varchar(100) DEFAULT NULL",
        'decision_maker'           => "varchar(255) DEFAULT NULL",
        'decision_maker_role'      => "varchar(150) DEFAULT NULL",
        'email'                    => "varchar(255) DEFAULT NULL",
        'phone'                    => "varchar(50) DEFAULT NULL",
        'expected_value'           => "decimal(15,2) DEFAULT 0.00",
        'won_value'                => "decimal(15,2) DEFAULT 0.00",
        'currency'                 => "varchar(10) DEFAULT 'AED'",
        'lead_source'              => "varchar(100) DEFAULT NULL",
        'documents_submitted'      => "text DEFAULT NULL",
        'compliance_iso'           => "tinyint(1) DEFAULT 0",
        'compliance_trade_license' => "tinyint(1) DEFAULT 0",
        'compliance_vat_trn'       => "tinyint(1) DEFAULT 0",
        'compliance_cr'            => "tinyint(1) DEFAULT 0",
        'target_close_date'        => "date DEFAULT NULL",
        'follow_up_date'           => "date DEFAULT NULL",
        'updated_at'               => "datetime NOT NULL DEFAULT '" . date('Y-m-d H:i:s') . "'",
        'lead_id'                  => "int(11) DEFAULT NULL",
        'client_id'                => "int(11) DEFAULT NULL",
    ];
    foreach ($cols as $col => $def) {
        if (!$CI->db->field_exists($col, $prefix . 'vr_registrations')) {
            $CI->db->query('ALTER TABLE `' . $prefix . 'vr_registrations` ADD `' . $col . '` ' . $def . ';');
        }
    }
}

/* ----------------------------------------------------------------------- */
/* 4. MASTER COMPANIES                                                     */
/* ----------------------------------------------------------------------- */
if (!$CI->db->table_exists($prefix . 'vr_master_companies')) {
    $CI->db->query('CREATE TABLE `' . $prefix . "vr_master_companies` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_name` varchar(255) NOT NULL,
      `registration_number` varchar(100) DEFAULT NULL,
      `industry` varchar(100) DEFAULT NULL,
      `emirate` varchar(100) DEFAULT NULL,
      `country_id` int(11) DEFAULT NULL,
      `website` varchar(255) DEFAULT NULL,
      `phone` varchar(50) DEFAULT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_company_name` (`company_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . ';');
} else {
    foreach (['industry'   => 'varchar(100) DEFAULT NULL',
              'emirate'    => 'varchar(100) DEFAULT NULL',
              'country_id' => 'int(11) DEFAULT NULL',
              'website'    => 'varchar(255) DEFAULT NULL',
              'phone'      => 'varchar(50) DEFAULT NULL'] as $col => $def) {
        if (!$CI->db->field_exists($col, $prefix . 'vr_master_companies')) {
            $CI->db->query('ALTER TABLE `' . $prefix . 'vr_master_companies` ADD `' . $col . '` ' . $def . ';');
        }
    }
}

/* ----------------------------------------------------------------------- */
/* 5. VR ACTIVITY LOGS (audit trail)                                        */
/* ----------------------------------------------------------------------- */
if (!$CI->db->table_exists($prefix . 'vr_activity_logs')) {
    $CI->db->query('CREATE TABLE `' . $prefix . "vr_activity_logs` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `registration_id` int(11) NOT NULL,
      `staff_id` int(11) NOT NULL,
      `action_description` text NOT NULL,
      `metadata` text DEFAULT NULL,
      `date_created` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `registration_id` (`registration_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . ';');
} else {
    if (!$CI->db->field_exists('metadata', $prefix . 'vr_activity_logs')) {
        $CI->db->query('ALTER TABLE `' . $prefix . 'vr_activity_logs` ADD `metadata` text DEFAULT NULL;');
    }
}

/* ----------------------------------------------------------------------- */
/* 6. BDM ACTIVITIES (visits/calls/email/whatsapp/linkedin)                 */
/* ----------------------------------------------------------------------- */
if (!$CI->db->table_exists($prefix . 'bdm_activities')) {
    $CI->db->query('CREATE TABLE `' . $prefix . "bdm_activities` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NOT NULL,
      `type` varchar(30) NOT NULL,
      `channel` varchar(50) DEFAULT NULL,
      `customer_name` varchar(255) NOT NULL,
      `contact_person` varchar(255) DEFAULT NULL,
      `phone` varchar(50) DEFAULT NULL,
      `email` varchar(255) DEFAULT NULL,
      `outcome` varchar(50) DEFAULT NULL,
      `description` text DEFAULT NULL,
      `next_action` varchar(255) DEFAULT NULL,
      `follow_up_date` date DEFAULT NULL,
      `registration_id` int(11) DEFAULT NULL,
      `partner_id` int(11) DEFAULT NULL,
      `lead_id` int(11) DEFAULT NULL,
      `date_created` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `staff_id` (`staff_id`),
      KEY `type` (`type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . ';');
} else {
    $cols = [
        'channel'         => "varchar(50) DEFAULT NULL",
        'phone'           => "varchar(50) DEFAULT NULL",
        'email'           => "varchar(255) DEFAULT NULL",
        'outcome'         => "varchar(50) DEFAULT NULL",
        'next_action'     => "varchar(255) DEFAULT NULL",
        'registration_id' => "int(11) DEFAULT NULL",
        'partner_id'      => "int(11) DEFAULT NULL",
        'lead_id'         => "int(11) DEFAULT NULL",
    ];
    foreach ($cols as $col => $def) {
        if (!$CI->db->field_exists($col, $prefix . 'bdm_activities')) {
            $CI->db->query('ALTER TABLE `' . $prefix . 'bdm_activities` ADD `' . $col . '` ' . $def . ';');
        }
    }
}

/* ----------------------------------------------------------------------- */
/* 7. BDM PARTNERS — Inbound + Outbound channels                            */
/* ----------------------------------------------------------------------- */
if (!$CI->db->table_exists($prefix . 'bdm_partners')) {
    $CI->db->query('CREATE TABLE `' . $prefix . "bdm_partners` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_name` varchar(255) NOT NULL,
      `partner_type` varchar(50) NOT NULL,
      `model` varchar(50) DEFAULT NULL,
      `status` varchar(50) DEFAULT 'Pending',
      `contact_person` varchar(255) DEFAULT NULL,
      `email` varchar(255) DEFAULT NULL,
      `phone` varchar(50) DEFAULT NULL,
      `whatsapp` varchar(50) DEFAULT NULL,
      `emirate` varchar(100) DEFAULT NULL,
      `territory` varchar(150) DEFAULT NULL,
      `agreement_date` date DEFAULT NULL,
      `agreement_expiry` date DEFAULT NULL,
      `commission_pct` decimal(5,2) DEFAULT '0.00',
      `margin_pct` decimal(5,2) DEFAULT '0.00',
      `assigned_bdm` int(11) DEFAULT NULL,
      `total_deals` int(11) DEFAULT 0,
      `total_revenue` decimal(15,2) DEFAULT 0.00,
      `total_commission` decimal(15,2) DEFAULT 0.00,
      `notes` text DEFAULT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `assigned_bdm` (`assigned_bdm`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . ';');
} else {
    $cols = [
        'model'            => "varchar(50) DEFAULT NULL",
        'whatsapp'         => "varchar(50) DEFAULT NULL",
        'emirate'          => "varchar(100) DEFAULT NULL",
        'territory'        => "varchar(150) DEFAULT NULL",
        'agreement_date'   => "date DEFAULT NULL",
        'agreement_expiry' => "date DEFAULT NULL",
        'margin_pct'       => "decimal(5,2) DEFAULT '0.00'",
        'total_deals'      => "int(11) DEFAULT 0",
        'total_revenue'    => "decimal(15,2) DEFAULT 0.00",
        'total_commission' => "decimal(15,2) DEFAULT 0.00",
        'notes'            => "text DEFAULT NULL",
    ];
    foreach ($cols as $col => $def) {
        if (!$CI->db->field_exists($col, $prefix . 'bdm_partners')) {
            $CI->db->query('ALTER TABLE `' . $prefix . 'bdm_partners` ADD `' . $col . '` ' . $def . ';');
        }
    }
}

/* ----------------------------------------------------------------------- */
/* 8. BDM PARTNER COMMISSIONS — payout ledger                               */
/* ----------------------------------------------------------------------- */
if (!$CI->db->table_exists($prefix . 'bdm_partner_commissions')) {
    $CI->db->query('CREATE TABLE `' . $prefix . "bdm_partner_commissions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `partner_id` int(11) NOT NULL,
      `invoice_id` int(11) DEFAULT NULL,
      `registration_id` int(11) DEFAULT NULL,
      `deal_reference` varchar(150) DEFAULT NULL,
      `deal_value` decimal(15,2) NOT NULL DEFAULT 0.00,
      `commission_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
      `commission_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
      `currency` varchar(10) DEFAULT 'AED',
      `status` varchar(30) DEFAULT 'Accrued',
      `accrued_at` datetime DEFAULT NULL,
      `paid_at` datetime DEFAULT NULL,
      `notes` text DEFAULT NULL,
      `created_by` int(11) DEFAULT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `partner_id` (`partner_id`),
      KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . ';');
}

/* ----------------------------------------------------------------------- */
/* 9. BDM TENDERS — Government / Corporate tender tracking                  */
/* ----------------------------------------------------------------------- */
if (!$CI->db->table_exists($prefix . 'bdm_tenders')) {
    $CI->db->query('CREATE TABLE `' . $prefix . "bdm_tenders` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `tender_ref` varchar(100) DEFAULT NULL,
      `title` varchar(255) NOT NULL,
      `client_company` varchar(255) DEFAULT NULL,
      `segment` varchar(100) DEFAULT NULL,
      `emirate` varchar(100) DEFAULT NULL,
      `value` decimal(15,2) DEFAULT 0.00,
      `currency` varchar(10) DEFAULT 'AED',
      `submission_deadline` date DEFAULT NULL,
      `status` varchar(50) DEFAULT 'Identified',
      `assigned_bdm` int(11) DEFAULT NULL,
      `documents_required` text DEFAULT NULL,
      `outcome` varchar(50) DEFAULT NULL,
      `notes` text DEFAULT NULL,
      `created_at` datetime NOT NULL,
      `updated_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `assigned_bdm` (`assigned_bdm`),
      KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . ';');
}

/* ----------------------------------------------------------------------- */
/* 10. BDM MARKETING CAMPAIGNS — 5-channel tracking                          */
/* ----------------------------------------------------------------------- */
if (!$CI->db->table_exists($prefix . 'bdm_campaigns')) {
    $CI->db->query('CREATE TABLE `' . $prefix . "bdm_campaigns` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `channel` varchar(50) NOT NULL,
      `status` varchar(50) DEFAULT 'Planned',
      `start_date` date DEFAULT NULL,
      `end_date` date DEFAULT NULL,
      `budget` decimal(15,2) DEFAULT 0.00,
      `cost_actual` decimal(15,2) DEFAULT 0.00,
      `target_audience` text DEFAULT NULL,
      `recipients` int(11) DEFAULT 0,
      `delivered` int(11) DEFAULT 0,
      `opens` int(11) DEFAULT 0,
      `clicks` int(11) DEFAULT 0,
      `replies` int(11) DEFAULT 0,
      `leads_generated` int(11) DEFAULT 0,
      `meetings_booked` int(11) DEFAULT 0,
      `deals_closed` int(11) DEFAULT 0,
      `revenue_generated` decimal(15,2) DEFAULT 0.00,
      `notes` text DEFAULT NULL,
      `owner` int(11) DEFAULT NULL,
      `created_by` int(11) DEFAULT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `channel` (`channel`),
      KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . ';');
}

/* ----------------------------------------------------------------------- */
/* 11. BDM KPI TARGETS — monthly target & actual comparison                 */
/* ----------------------------------------------------------------------- */
if (!$CI->db->table_exists($prefix . 'bdm_kpi_targets')) {
    $CI->db->query('CREATE TABLE `' . $prefix . "bdm_kpi_targets` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `period_year` int(11) NOT NULL,
      `period_month` tinyint(2) NOT NULL,
      `staff_id` int(11) DEFAULT NULL,
      `metric_key` varchar(60) NOT NULL,
      `target_value` decimal(15,2) NOT NULL DEFAULT 0.00,
      `actual_value` decimal(15,2) NOT NULL DEFAULT 0.00,
      `notes` text DEFAULT NULL,
      `created_at` datetime NOT NULL,
      `updated_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `period` (`period_year`, `period_month`),
      KEY `metric_key` (`metric_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . ';');

    /* Seed default monthly targets per the BDM Plan (firm-wide row) */
    $year  = (int) date('Y');
    $month = (int) date('m');
    $defaults = [
        'leads'              => 50,
        'visits'             => 100,
        'calls'              => 600,
        'vendor_regs'        => 10,
        'client_approvals'   => 3,
        'proposals'          => 20,
        'deals'              => 4,
        'revenue_aed'        => 200000,
        'partner_regs'       => 2,
        'email_open_rate'    => 28,
        'linkedin_growth'    => 200,
        'conversion_rate'    => 8,
    ];
    foreach ($defaults as $k => $v) {
        $CI->db->insert($prefix . 'bdm_kpi_targets', [
            'period_year'  => $year,
            'period_month' => $month,
            'staff_id'     => 0,
            'metric_key'   => $k,
            'target_value' => $v,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }
}

/* ----------------------------------------------------------------------- */
/* 12. BDM EMAIL CADENCE — Day 1 / 4 / 10 / 21 sequence per registration    */
/* ----------------------------------------------------------------------- */
if (!$CI->db->table_exists($prefix . 'bdm_email_cadence')) {
    $CI->db->query('CREATE TABLE `' . $prefix . "bdm_email_cadence` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `registration_id` int(11) NOT NULL,
      `step` tinyint(2) NOT NULL,
      `scheduled_at` datetime NOT NULL,
      `sent_at` datetime DEFAULT NULL,
      `subject` varchar(255) DEFAULT NULL,
      `status` varchar(30) DEFAULT 'Scheduled',
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `registration_id` (`registration_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . ';');
}

/* ----------------------------------------------------------------------- */
/* 13. Run v1 → v2 data migration (idempotent)                              */
/* ----------------------------------------------------------------------- */
$migration_file = __DIR__ . '/migrations/migration_v2.php';
if (file_exists($migration_file)) {
    require_once($migration_file);
}
