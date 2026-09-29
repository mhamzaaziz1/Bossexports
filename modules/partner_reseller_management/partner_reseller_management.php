<?php
/**
 * ============================================================================
 *  Partner & Reseller Management — Enterprise Module for Perfex CRM
 * ============================================================================
 *  Module Slug : partner_reseller_management
 *  Version     : 2.0.0
 *  Author      : Senior Engineering (L09) — Hamza
 *  License     : Commercial / Proprietary
 *  Requires    : Perfex CRM 2.3.0+, PHP 7.4+
 * ----------------------------------------------------------------------------
 *  Module Name: Partner and Reseller Management
 *  Description: World-class B2B Partner Program — multi-tier hierarchy, deal
 *               registration with collision protection, AI lead scoring,
 *               recurring & clawback commissions, MDF, training & certification,
 *               partner wallet, gamification, audit trail, multi-currency.
 *  Version: 2.0.0
 *  Requires at least: 2.3.0
 *  Author: Hamza Aziz
 *  Author URI: https://example.com
 * ============================================================================
 */

defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 *  CONSTANTS
 * --------------------------------------------------------------------------*/
define('PRM_MODULE_NAME',    'partner_reseller_management');
define('PRM_MODULE_VERSION', '2.0.0');
define('PRM_MODULE_PATH',    module_dir_path(PRM_MODULE_NAME) . '/');
define('PRM_MODULE_URL',     module_dir_url(PRM_MODULE_NAME, ''));

/* ----------------------------------------------------------------------------
 *  HOOKS — Activation / Deactivation / Uninstall
 * --------------------------------------------------------------------------*/
register_activation_hook(PRM_MODULE_NAME, 'prm_activation_hook');
register_deactivation_hook(PRM_MODULE_NAME, 'prm_deactivation_hook');
register_uninstall_hook(PRM_MODULE_NAME, 'prm_uninstall_hook');

function prm_activation_hook()
{
    $CI = &get_instance();
    require_once(PRM_MODULE_PATH . 'install/install.php');
    $installer = new Partner_Reseller_Management_Install();
    $installer->run();
}

function prm_deactivation_hook()
{
    // Soft deactivation — keep data, only remove cron flags.
    $CI = &get_instance();
    log_activity('Partner & Reseller Management Module deactivated');
}

function prm_uninstall_hook()
{
    $CI = &get_instance();
    require_once(PRM_MODULE_PATH . 'install/uninstall.php');
    $uninstaller = new Partner_Reseller_Management_Uninstall();
    $uninstaller->run();
}

/* ----------------------------------------------------------------------------
 *  LANGUAGE FILES
 * --------------------------------------------------------------------------*/
register_language_files(PRM_MODULE_NAME, [PRM_MODULE_NAME]);

/* ----------------------------------------------------------------------------
 *  GLOBAL HELPER LOADER  (auto-load helper file)
 * --------------------------------------------------------------------------*/
hooks()->add_action('app_init', 'prm_load_helpers');
function prm_load_helpers()
{
    $CI = &get_instance();
    if (file_exists(PRM_MODULE_PATH . 'helpers/partner_reseller_management_helper.php')) {
        require_once(PRM_MODULE_PATH . 'helpers/partner_reseller_management_helper.php');
    }
}

/* ----------------------------------------------------------------------------
 *  ADMIN MENU
 * --------------------------------------------------------------------------*/
hooks()->add_action('admin_init', 'prm_register_menu');
function prm_register_menu()
{
    $CI = &get_instance();

    if (!staff_can('view', 'partner_reseller_management') && !is_admin()) {
        return;
    }

    $CI->app_menu->add_sidebar_menu_item('prm-root', [
        'collapse' => true,
        'name'     => _l('prm_module_name'),
        'icon'     => 'fa fa-handshake-o',
        'position' => 16,
    ]);

    $children = [
        ['dashboard',   _l('prm_dashboard'),    'fa fa-tachometer',     'partner_reseller_management/dashboard',   1],
        ['partners',    _l('prm_partners'),     'fa fa-users',          'partner_reseller_management/partners',    2],
        ['hierarchy',   _l('prm_hierarchy'),    'fa fa-sitemap',        'partner_reseller_management/hierarchy',   3],
        ['deals',       _l('prm_deals'),        'fa fa-briefcase',      'partner_reseller_management/deals',       4],
        ['commissions', _l('prm_commissions'),  'fa fa-money',          'partner_reseller_management/commissions', 5],
        ['payouts',     _l('prm_payouts'),      'fa fa-credit-card',    'partner_reseller_management/payouts',     6],
        ['mdf',         _l('prm_mdf'),          'fa fa-bullhorn',       'partner_reseller_management/mdf',         7],
        ['training',    _l('prm_training'),     'fa fa-graduation-cap', 'partner_reseller_management/training',    8],
        ['gamification',_l('prm_gamification'), 'fa fa-trophy',         'partner_reseller_management/gamification',9],
        ['audit',       _l('prm_audit_log'),    'fa fa-shield',         'partner_reseller_management/audit',       11],
    ];

    foreach ($children as $row) {
        $CI->app_menu->add_sidebar_children_item('prm-root', [
            'slug'     => 'prm-' . $row[0],
            'name'     => $row[1],
            'icon'     => $row[2],
            'href'     => admin_url($row[3]),
            'position' => $row[4],
        ]);
    }

    $CI->app_menu->add_sidebar_children_item('reports', [
        'slug'     => 'prm-reports',
        'name'     => _l('prm_module_name'),
        'href'     => admin_url('partner_reseller_management/reports'),
        'position' => 25,
    ]);

    if (is_admin()) {
        $CI->app_menu->add_setup_menu_item('prm-settings', [
            'name'     => _l('prm_module_name'),
            'href'     => admin_url('partner_reseller_management/settings'),
            'position' => 25,
        ]);
    }
}

/* ----------------------------------------------------------------------------
 *  STAFF PERMISSIONS
 * --------------------------------------------------------------------------*/
hooks()->add_filter('staff_permissions', 'prm_register_permissions');
function prm_register_permissions($permissions)
{
    $permissions[] = [
        'name'         => _l('prm_module_name'),
        'short_name'   => 'partner_reseller_management',
        'capabilities' => [
            'view'              => _l('permission_view'),
            'view_own'          => _l('permission_view_own'),
            'create'            => _l('permission_create'),
            'edit'              => _l('permission_edit'),
            'delete'            => _l('permission_delete'),
            'approve_deals'     => _l('prm_perm_approve_deals'),
            'manage_commission' => _l('prm_perm_manage_commission'),
            'process_payouts'   => _l('prm_perm_process_payouts'),
            'manage_mdf'        => _l('prm_perm_manage_mdf'),
            'view_audit'        => _l('prm_perm_view_audit'),
        ],
    ];
    return $permissions;
}

/* ----------------------------------------------------------------------------
 *  ADMIN HEAD — assets injection
 * --------------------------------------------------------------------------*/
hooks()->add_action('app_admin_head', 'prm_admin_head_assets');
function prm_admin_head_assets()
{
    if (strpos($_SERVER['REQUEST_URI'] ?? '', 'partner_reseller_management') === false) {
        return;
    }
    echo '<link rel="stylesheet" href="' . PRM_MODULE_URL . 'assets/css/admin.css?v=' . PRM_MODULE_VERSION . '">';
}
hooks()->add_action('app_admin_footer', 'prm_admin_footer_assets');
function prm_admin_footer_assets()
{
    if (strpos($_SERVER['REQUEST_URI'] ?? '', 'partner_reseller_management') === false) {
        return;
    }
    echo '<script src="' . PRM_MODULE_URL . 'assets/js/admin.js?v=' . PRM_MODULE_VERSION . '"></script>';
}

/* ----------------------------------------------------------------------------
 *  DEAL → CRM LEAD lifecycle hook
 *  Whenever a deal in our module is approved, automatically create a Perfex
 *  CRM Lead and link it back via lead_id.  Powered by Lead_intelligence
 *  library (loaded on demand).
 * --------------------------------------------------------------------------*/
hooks()->add_action('prm_deal_approved', 'prm_handle_deal_approved');
function prm_handle_deal_approved($deal_id)
{
    $CI = &get_instance();
    $CI->load->model('partner_reseller_management/deals_model');
    $CI->load->library('partner_reseller_management/Commission_engine', null, 'commission_engine');
    $CI->load->library('partner_reseller_management/Notification_hub',   null, 'notification_hub');

    $deal = $CI->deals_model->get($deal_id);
    if (!$deal) return;

    if ((int) get_option('prm_auto_create_lead') === 1) {
        $CI->load->model('leads_model');
        $lead_data = [
            'name'         => $deal->client_name,
            'company'      => $deal->company_name,
            'email'        => $deal->email,
            'phonenumber'  => $deal->phone,
            'description'  => $deal->description,
            'source'       => (int) get_option('prm_lead_source_id', 0),
            'status'       => (int) get_option('prm_lead_default_status', 1),
            'assigned'     => (int) ($deal->assigned_to ?: 0),
            'is_public'    => 0,
            'addedfrom'    => 0,
            'lastcontact'  => null,
            'dateadded'    => date('Y-m-d H:i:s'),
        ];
        $lead_id = $CI->leads_model->add($lead_data);
        if ($lead_id) {
            $CI->deals_model->update($deal_id, ['lead_id' => $lead_id]);
            log_activity("PRM: Deal #$deal_id auto-created Lead #$lead_id");
        }
    }

    // notify partner
    $CI->notification_hub->send_to_partner($deal->partner_id, 'deal_approved', [
        'deal_number' => $deal->deal_number,
        'client_name' => $deal->client_name,
    ]);
}

/* ----------------------------------------------------------------------------
 *  RECURRING COMMISSION CRON  (daily)
 *  Run on app_cron_daily — generates recurring commissions for AMC / SaaS
 *  type partners.  Idempotent.
 * --------------------------------------------------------------------------*/
hooks()->add_action('app_cron', 'prm_cron_recurring_commissions');
function prm_cron_recurring_commissions()
{
    $CI = &get_instance();
    $CI->load->library('partner_reseller_management/Commission_engine', null, 'commission_engine');
    $CI->commission_engine->run_recurring_cron();
}

/* ----------------------------------------------------------------------------
 *  PORTAL ROUTES — exposed at  /partner-portal/*
 *  These are wired through application/config/routes.php OR
 *  the module's own controller routing.  We append routes via the
 *  CodeIgniter route hook below.
 * --------------------------------------------------------------------------*/
hooks()->add_filter('app_routes', 'prm_register_routes');
function prm_register_routes($routes)
{
    $routes['partner-portal']                 = 'partner_reseller_management/Partner_portal/index';
    $routes['partner-portal/login']           = 'partner_reseller_management/Partner_portal/login';
    $routes['partner-portal/logout']          = 'partner_reseller_management/Partner_portal/logout';
    $routes['partner-portal/register']        = 'partner_reseller_management/Partner_portal/register';
    $routes['partner-portal/dashboard']       = 'partner_reseller_management/Partner_portal/dashboard';
    $routes['partner-portal/deals']           = 'partner_reseller_management/Partner_portal/deals';
    $routes['partner-portal/deals/new']       = 'partner_reseller_management/Partner_portal/deal_new';
    $routes['partner-portal/deals/(:num)']    = 'partner_reseller_management/Partner_portal/deal_view/$1';
    $routes['partner-portal/commissions']     = 'partner_reseller_management/Partner_portal/commissions';
    $routes['partner-portal/wallet']          = 'partner_reseller_management/Partner_portal/wallet';
    $routes['partner-portal/training']        = 'partner_reseller_management/Partner_portal/training';
    $routes['partner-portal/profile']         = 'partner_reseller_management/Partner_portal/profile';
    $routes['partner-portal/api/(:any)']      = 'partner_reseller_management/Partner_portal_api/$1';
    return $routes;
}
