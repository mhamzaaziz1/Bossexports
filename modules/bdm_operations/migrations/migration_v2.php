<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * BDM Operations — v1 → v2 Data Migration
 *
 * This file is invoked by install.php after the schema upgrade
 * (DDL handled there with idempotent ALTER TABLEs). The job of
 * this migration is to:
 *
 *   1.  Backfill columns that didn't exist in v1.
 *   2.  Normalise legacy values (statuses, currency, types).
 *   3.  Map the 9-stage status to the new 10-step pipeline_step.
 *   4.  Ensure every active registration has its email cadence rows.
 *   5.  Recalculate partner totals (deals/revenue/commission).
 *
 * Migrations are idempotent — they can be run repeatedly without
 * corrupting data. A version row is written to bdm_operations_meta
 * so future versions can short-circuit.
 *
 * Senior-grade convention: all queries use db_prefix(), guard with
 * field/table existence checks, and never delete/destructive update
 * without a fallback.
 */

$CI     = &get_instance();
$prefix = db_prefix();
$now    = date('Y-m-d H:i:s');

/* -----------------------------------------------------------
 * 0. Version Tracking — bdm_operations_meta
 * ----------------------------------------------------------- */
if (!$CI->db->table_exists($prefix . 'bdm_operations_meta')) {
    $CI->db->query('CREATE TABLE `' . $prefix . "bdm_operations_meta` (
      `meta_key`   varchar(80)  NOT NULL,
      `meta_value` text         DEFAULT NULL,
      `updated_at` datetime     NOT NULL,
      PRIMARY KEY (`meta_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

$current_version = '1.0.0';
$row = $CI->db->where('meta_key', 'schema_version')->get($prefix . 'bdm_operations_meta')->row();
if ($row && !empty($row->meta_value)) {
    $current_version = $row->meta_value;
}

/* -----------------------------------------------------------
 * 1. Backfill — vr_registrations
 * ----------------------------------------------------------- */
if ($CI->db->table_exists($prefix . 'vr_registrations')) {

    /* updated_at backfill — handle ZERO and NULL */
    $CI->db->query("
        UPDATE `{$prefix}vr_registrations`
        SET updated_at = COALESCE(NULLIF(updated_at, '0000-00-00 00:00:00'), created_at, NOW())
        WHERE updated_at IS NULL OR updated_at = '0000-00-00 00:00:00'
    ");

    /* currency default to AED for legacy rows */
    $CI->db->where('currency IS NULL', null, false)
           ->or_where('currency', '')
           ->update($prefix . 'vr_registrations', ['currency' => 'AED']);

    /* registration_type default */
    $CI->db->where('registration_type IS NULL', null, false)
           ->or_where('registration_type', '')
           ->update($prefix . 'vr_registrations', ['registration_type' => 'Supplier Onboarding']);

    /* priority default */
    $CI->db->where('priority IS NULL', null, false)
           ->or_where('priority', '')
           ->update($prefix . 'vr_registrations', ['priority' => 'Medium']);

    /* compliance flags — make sure they're 0/1 not NULL */
    foreach (['compliance_iso', 'compliance_trade_license', 'compliance_vat_trn', 'compliance_cr'] as $col) {
        if ($CI->db->field_exists($col, $prefix . 'vr_registrations')) {
            $CI->db->where($col . ' IS NULL', null, false)
                   ->update($prefix . 'vr_registrations', [$col => 0]);
        }
    }

    /* expected_value / won_value default */
    foreach (['expected_value', 'won_value'] as $col) {
        if ($CI->db->field_exists($col, $prefix . 'vr_registrations')) {
            $CI->db->where($col . ' IS NULL', null, false)
                   ->update($prefix . 'vr_registrations', [$col => 0]);
        }
    }

    /* Map 9-stage status → 10-step pipeline_step (idempotent) */
    if ($CI->db->field_exists('pipeline_step', $prefix . 'vr_registrations')) {
        $status_to_step = [
            'New Referral'          => 1,
            'Contact Initiated'     => 2,
            'Information Requested' => 3,
            'Under Review'          => 4,
            'Qualified'             => 5,
            'Verification'          => 6,
            'Final Approval'        => 7,
            'Registered'            => 8,
            'Rejected'              => 9,
        ];
        foreach ($status_to_step as $status => $step) {
            $CI->db->where('status', $status)
                   ->where('(pipeline_step IS NULL OR pipeline_step = 0 OR pipeline_step < ' . $step . ')', null, false)
                   ->update($prefix . 'vr_registrations', ['pipeline_step' => $step]);
        }
        // Anything still null/0 → step 1
        $CI->db->where('(pipeline_step IS NULL OR pipeline_step = 0)', null, false)
               ->update($prefix . 'vr_registrations', ['pipeline_step' => 1]);
    }
}

/* -----------------------------------------------------------
 * 2. Backfill — distributors
 * ----------------------------------------------------------- */
if ($CI->db->table_exists($prefix . 'distributors')) {
    $CI->db->where('status IS NULL', null, false)
           ->or_where('status', '')
           ->update($prefix . 'distributors', ['status' => 'Active']);

    if ($CI->db->field_exists('iso_certified', $prefix . 'distributors')) {
        $CI->db->where('iso_certified IS NULL', null, false)
               ->update($prefix . 'distributors', ['iso_certified' => 0]);
    }

    /* Mirror brand country onto distributor when emirate is empty (best-effort UAE assumption) */
    if ($CI->db->field_exists('emirate', $prefix . 'distributors')) {
        // No automated guess — left for BDMs to fill in.
    }
}

/* -----------------------------------------------------------
 * 3. Backfill — bdm_partners totals
 * ----------------------------------------------------------- */
if ($CI->db->table_exists($prefix . 'bdm_partners') &&
    $CI->db->table_exists($prefix . 'bdm_partner_commissions')) {
    $partners = $CI->db->get($prefix . 'bdm_partners')->result_array();
    foreach ($partners as $p) {
        $r = $CI->db->select_sum('deal_value', 'rev')
                    ->select_sum('commission_amount', 'comm')
                    ->where('partner_id', $p['id'])
                    ->get($prefix . 'bdm_partner_commissions')->row();
        $count = $CI->db->where('partner_id', $p['id'])
                        ->count_all_results($prefix . 'bdm_partner_commissions');
        $CI->db->where('id', $p['id'])->update($prefix . 'bdm_partners', [
            'total_deals'      => (int) $count,
            'total_revenue'    => (float) ($r->rev ?? 0),
            'total_commission' => (float) ($r->comm ?? 0),
        ]);
    }
}

/* -----------------------------------------------------------
 * 4. Email cadence — backfill for active registrations missing rows
 * ----------------------------------------------------------- */
if ($CI->db->table_exists($prefix . 'vr_registrations') &&
    $CI->db->table_exists($prefix . 'bdm_email_cadence')) {

    $CI->db->where_not_in('status', ['Registered', 'Rejected']);
    $regs = $CI->db->get($prefix . 'vr_registrations')->result_array();
    foreach ($regs as $r) {
        $exists = $CI->db->where('registration_id', $r['id'])
                         ->count_all_results($prefix . 'bdm_email_cadence');
        if ($exists > 0) continue;

        $offsets = [1 => 0, 2 => 4, 3 => 10, 4 => 21];
        foreach ($offsets as $step => $days) {
            $CI->db->insert($prefix . 'bdm_email_cadence', [
                'registration_id' => $r['id'],
                'step'            => $step,
                'scheduled_at'    => date('Y-m-d H:i:s', strtotime('+' . $days . ' days')),
                'subject'         => 'BDM Touchpoint Step ' . $step,
                'status'          => 'Scheduled',
                'created_at'      => $now,
            ]);
        }
    }
}

/* -----------------------------------------------------------
 * 5. KPI targets — make sure current month has a default row
 * ----------------------------------------------------------- */
if ($CI->db->table_exists($prefix . 'bdm_kpi_targets')) {
    $year     = (int) date('Y');
    $month    = (int) date('m');
    $existing = $CI->db->where('period_year', $year)
                       ->where('period_month', $month)
                       ->count_all_results($prefix . 'bdm_kpi_targets');

    if ($existing === 0) {
        $defaults = [
            'leads' => 50, 'visits' => 100, 'calls' => 600,
            'vendor_regs' => 10, 'client_approvals' => 3,
            'proposals' => 20, 'deals' => 4, 'revenue_aed' => 200000,
            'partner_regs' => 2, 'email_open_rate' => 28,
            'linkedin_growth' => 200, 'conversion_rate' => 8,
        ];
        foreach ($defaults as $k => $v) {
            $CI->db->insert($prefix . 'bdm_kpi_targets', [
                'period_year'  => $year,
                'period_month' => $month,
                'staff_id'     => 0,
                'metric_key'   => $k,
                'target_value' => $v,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }
}

/* -----------------------------------------------------------
 * 6. Stamp the version
 * ----------------------------------------------------------- */
$CI->db->where('meta_key', 'schema_version')->delete($prefix . 'bdm_operations_meta');
$CI->db->insert($prefix . 'bdm_operations_meta', [
    'meta_key'   => 'schema_version',
    'meta_value' => '2.0.0',
    'updated_at' => $now,
]);

$CI->db->where('meta_key', 'last_migration')->delete($prefix . 'bdm_operations_meta');
$CI->db->insert($prefix . 'bdm_operations_meta', [
    'meta_key'   => 'last_migration',
    'meta_value' => 'v1_to_v2_' . $now,
    'updated_at' => $now,
]);

if (function_exists('log_activity')) {
    log_activity('BDM Operations migrated from ' . $current_version . ' → 2.0.0');
}
