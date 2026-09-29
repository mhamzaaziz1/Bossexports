<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: BDM Operations (Advanced — UAE)
Description: Business Development Manager command suite for IT & Fiber Optic Infrastructure companies. Implements the 4-model BDM strategy, 5-channel marketing, 10-step pipeline, partner commissions, tenders, KPI targets, and UAE compliance tracking.
Version: 2.0.0
Author: Senior Engineering
Requires at least: 2.3.2
*/

define('BDM_OPERATIONS_MODULE_NAME', 'bdm_operations');

/* ============================================================
 *  Hooks
 * ============================================================ */
hooks()->add_action('admin_init', 'bdm_operations_init_menu_items');
hooks()->add_action('admin_init', 'bdm_operations_init_permissions');
hooks()->add_action('lead_modal_profile_bottom', 'bdm_operations_lead_referral_button');
hooks()->add_action('after_invoice_added', 'bdm_operations_after_invoice_added');
hooks()->add_action('invoice_paid', 'bdm_operations_invoice_paid');
hooks()->add_action('after_cron_run', 'bdm_operations_cron');

register_language_files(BDM_OPERATIONS_MODULE_NAME, [BDM_OPERATIONS_MODULE_NAME]);

/* ============================================================
 *  Activation
 * ============================================================ */
register_activation_hook(BDM_OPERATIONS_MODULE_NAME, 'bdm_operations_activation_hook');

function bdm_operations_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/* ============================================================
 *  Helper auto-load
 * ============================================================ */
$CI = &get_instance();
$CI->load->helper(BDM_OPERATIONS_MODULE_NAME . '/bdm_operations');

/* ============================================================
 *  Sidebar menu
 * ============================================================ */
function bdm_operations_init_menu_items()
{
    $CI = &get_instance();

    if (!has_permission('bdm_operations', '', 'view') && !has_permission('bdm_operations', '', 'view_own')) {
        return;
    }

    $CI->app_menu->add_sidebar_menu_item('bdm_operations', [
        'name'     => _l('bdm_operations'),
        'icon'     => 'fa fa-handshake-o',
        'position' => 30,
    ]);

    $children = [
        ['slug' => 'bdm_dashboard',        'name' => _l('bdm_dashboard'),        'href' => admin_url('bdm_operations'),                    'pos' => 1],
        ['slug' => 'bdm_pipeline',         'name' => _l('bdm_pipeline'),         'href' => admin_url('bdm_operations/pipeline'),           'pos' => 2],
        ['slug' => 'bdm_vr_registrations', 'name' => _l('vr_registrations'),     'href' => admin_url('bdm_operations/vr_registrations'),   'pos' => 3],
        ['slug' => 'bdm_brands',           'name' => _l('brands'),               'href' => admin_url('bdm_operations/brands'),             'pos' => 4],
        ['slug' => 'bdm_distributors',     'name' => _l('distributors'),         'href' => admin_url('bdm_operations/distributors'),       'pos' => 5],
        ['slug' => 'bdm_partnerships',     'name' => _l('bdm_partnerships'),     'href' => admin_url('bdm_operations/partnerships'),       'pos' => 6],
        ['slug' => 'bdm_commissions',      'name' => _l('bdm_commissions'),      'href' => admin_url('bdm_operations/commissions'),        'pos' => 7],
        ['slug' => 'bdm_tenders',          'name' => _l('bdm_tenders'),          'href' => admin_url('bdm_operations/tenders'),            'pos' => 8],
        ['slug' => 'bdm_campaigns',        'name' => _l('bdm_campaigns'),        'href' => admin_url('bdm_operations/campaigns'),          'pos' => 9],
        ['slug' => 'bdm_activities',       'name' => _l('bdm_activities'),       'href' => admin_url('bdm_operations/activities'),         'pos' => 10],
        ['slug' => 'bdm_kpi_targets',      'name' => _l('bdm_kpi_targets'),      'href' => admin_url('bdm_operations/kpi_targets'),        'pos' => 11],
        ['slug' => 'bdm_reports',          'name' => _l('bdm_reports'),          'href' => admin_url('bdm_operations/reports'),            'pos' => 12],
    ];

    foreach ($children as $c) {
        $CI->app_menu->add_sidebar_children_item('bdm_operations', [
            'slug'     => $c['slug'],
            'name'     => $c['name'],
            'href'     => $c['href'],
            'position' => $c['pos'],
        ]);
    }
}

/* ============================================================
 *  Permissions
 * ============================================================ */
function bdm_operations_init_permissions()
{
    $capabilities = ['capabilities' => [
        'view_own' => _l('permission_view_own'),
        'view'     => _l('permission_view') . ' (' . _l('permission_global') . ')',
        'create'   => _l('permission_create'),
        'edit'     => _l('permission_edit'),
        'delete'   => _l('permission_delete'),
    ]];
    register_staff_capabilities('bdm_operations', $capabilities, _l('bdm_operations'));
}

/* ============================================================
 *  Lead modal — "Refer to BDM" injection
 * ============================================================ */
function bdm_operations_lead_referral_button($lead_id)
{
    if (empty($lead_id)) return;
    $CI = &get_instance();
    $CI->load->model('leads_model');
    $lead = $CI->leads_model->get($lead_id);
    if (!$lead || empty($lead->company)) return;

    $action_url = admin_url('bdm_operations/refer_lead/' . $lead_id);
    $CI->load->model('staff_model');
    $staff = $CI->staff_model->get('', ['active' => 1]);

    $segments_options = '';
    foreach (bdm_segments() as $sk => $sl) {
        $segments_options .= '<option value="' . $sk . '">' . htmlentities($sl) . '</option>';
    }
    $staff_options = '';
    foreach ($staff as $s) {
        $sel = ($s['staffid'] == get_staff_user_id()) ? ' selected' : '';
        $staff_options .= '<option value="' . $s['staffid'] . '"' . $sel . '>' . $s['firstname'] . ' ' . $s['lastname'] . '</option>';
    }
    ?>
    <script>
        $(function() {
            var btn = $('<a href="#" class="btn btn-info pull-right mleft5 lead-top-btn" onclick="bdm_refer_lead_modal(); return false;"><i class="fa fa-handshake-o"></i> <?php echo _l('refer_to_bdm'); ?></a>');
            if ($('.lead-convert-to-customer').length) {
                $('.lead-convert-to-customer').after(btn);
            } else {
                $('.lead-top-btn').first().after(btn);
            }
        });
        function bdm_refer_lead_modal() { $('#bdm_referral_modal').modal('show'); }
    </script>
    <div class="modal fade" id="bdm_referral_modal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <form action="<?php echo $action_url; ?>" method="post" id="bdm_referral_form" accept-charset="utf-8">
          <input type="hidden" name="<?php echo $CI->security->get_csrf_token_name(); ?>" value="<?php echo $CI->security->get_csrf_hash(); ?>">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><i class="fa fa-handshake-o"></i> <?php echo _l('bdm_referral'); ?></h4>
            </div>
            <div class="modal-body">
              <p class="text-info"><?php echo _l('refer_to_bdm'); ?>: <strong><?php echo htmlentities($lead->company); ?></strong></p>
              <hr/>
              <div class="form-group">
                <label><?php echo _l('company_name'); ?></label>
                <input type="text" class="form-control" name="company" value="<?php echo htmlentities($lead->company); ?>" readonly>
              </div>
              <div class="form-group">
                <label><?php echo _l('bdm_registration_type'); ?></label>
                <select name="registration_type" class="selectpicker form-control" data-width="100%">
                  <option value="Supplier Onboarding"><?php echo _l('bdm_supplier_onboarding'); ?></option>
                  <option value="Client Vendor Approval"><?php echo _l('bdm_client_approval'); ?></option>
                </select>
              </div>
              <div class="form-group">
                <label><?php echo _l('bdm_segment'); ?></label>
                <select name="segment" class="selectpicker form-control" data-width="100%">
                  <option value=""></option>
                  <?php echo $segments_options; ?>
                </select>
              </div>
              <div class="form-group">
                <label><?php echo _l('bdm_priority'); ?></label>
                <select name="priority" class="selectpicker form-control" data-width="100%">
                  <option value="High"><?php echo _l('priority_high'); ?></option>
                  <option value="Medium" selected><?php echo _l('priority_medium'); ?></option>
                  <option value="Low"><?php echo _l('priority_low'); ?></option>
                </select>
              </div>
              <div class="form-group">
                <label><?php echo _l('bdm_expected_value'); ?></label>
                <input type="number" step="0.01" class="form-control" name="expected_value" value="0">
              </div>
              <div class="form-group">
                <label><?php echo _l('assigned_bdm'); ?></label>
                <select name="assigned_bdm" class="selectpicker form-control" data-width="100%" data-live-search="true">
                  <?php echo $staff_options; ?>
                </select>
              </div>
              <div class="form-group">
                <label><?php echo _l('bdm_notes'); ?></label>
                <textarea class="form-control" name="notes" rows="3">Lead referred from Perfex Leads. Name: <?php echo htmlentities($lead->name); ?></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
              <button type="submit" class="btn btn-info"><i class="fa fa-paper-plane"></i> <?php echo _l('submit'); ?></button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <?php
}

/* ============================================================
 *  Auto-accrue partner commission when invoice is paid
 *  (uses metadata: invoice has bdm_partner_id custom field, optional)
 * ============================================================ */
function bdm_operations_invoice_paid($invoice_id)
{
    $CI = &get_instance();
    if (!$CI->db->table_exists(db_prefix() . 'invoices')) return;

    $CI->db->where('id', $invoice_id);
    $invoice = $CI->db->get(db_prefix() . 'invoices')->row();
    if (!$invoice) return;

    // Look for a bdm_partner_id custom field on the invoice (silent if not configured)
    $partner_id = 0;
    if ($CI->db->table_exists(db_prefix() . 'customfieldsvalues')) {
        $CI->db->select('value');
        $CI->db->where('relid', $invoice_id);
        $CI->db->where('fieldto', 'invoice');
        $CI->db->join(
            db_prefix() . 'customfields',
            db_prefix() . 'customfields.id = ' . db_prefix() . 'customfieldsvalues.fieldid'
        );
        $CI->db->where(db_prefix() . 'customfields.slug', 'bdm_partner_id');
        $row = $CI->db->get(db_prefix() . 'customfieldsvalues')->row();
        if ($row && is_numeric($row->value)) {
            $partner_id = (int) $row->value;
        }
    }
    if (!$partner_id) return;

    $CI->load->model('bdm_operations/bdm_operations_model');
    $partner = $CI->bdm_operations_model->get_partners($partner_id);
    if (!$partner) return;

    $CI->bdm_operations_model->add_commission([
        'partner_id'     => $partner_id,
        'invoice_id'     => $invoice_id,
        'deal_reference' => 'INV #' . $invoice_id,
        'deal_value'     => (float) $invoice->total,
        'commission_pct' => (float) $partner->commission_pct,
        'currency'       => $invoice->currency_name ?? 'AED',
        'status'         => 'Accrued',
        'notes'          => 'Auto-accrued on invoice payment.',
    ]);
}

function bdm_operations_after_invoice_added($invoice_id)
{
    // Reserved for future automation hooks (estimate→invoice→pipeline_step=9 etc.)
}

/* ============================================================
 *  Cron — scheduled notifications + cadence
 * ============================================================ */
function bdm_operations_cron()
{
    $CI   = &get_instance();
    $hour = (int) date('H');

    $CI->load->model('bdm_operations/bdm_operations_model');

    // 08:00 — overdue follow-up reminders
    if ($hour === 8) {
        foreach ($CI->bdm_operations_model->get_overdue_followups_list() as $item) {
            if (empty($item['assigned_bdm'])) continue;
            add_notification([
                'description' => _l('bdm_overdue_followups') . ': ' . ($item['name'] ?? ''),
                'tostaffid'   => $item['assigned_bdm'],
                'fromcompany' => true,
                'link'        => 'bdm_operations/vr_registrations',
            ]);
        }
    }

    // 09:00 — 3-day inactivity alerts
    if ($hour === 9) {
        foreach ($CI->bdm_operations_model->get_inactive_registrations(3) as $reg) {
            if (empty($reg['assigned_bdm'])) continue;
            add_notification([
                'description' => _l('last_activity_days_threshold') . ' (' . ($reg['company_name'] ?? '#' . $reg['id']) . ')',
                'tostaffid'   => $reg['assigned_bdm'],
                'fromcompany' => true,
                'link'        => 'bdm_operations/vr_registrations',
            ]);
        }
    }

    // 07:00 — mark scheduled email cadence touchpoints as ready (consumer is n8n / SMTP)
    if ($hour === 7) {
        foreach ($CI->bdm_operations_model->get_due_email_touchpoints() as $tp) {
            $CI->bdm_operations_model->mark_cadence_sent($tp['id']);
        }
    }
}
