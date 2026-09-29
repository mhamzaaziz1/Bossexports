<?php

/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Booking
Description: This is Booking Module.
Version: 1.0.0
Author: Ivan Brunner
Author URI: https://ivan.com
Requires at least: 2.6.*
*/

define('BOOKING_MODULE_NAME', 'booking');
define('SMS_TRIGGER_QUOTE_EXP_REMINDER', 'quote_expiration_reminder');
define('SMS_TRIGGER_QUOTE_NEW_COMMENT_TO_CUSTOMER', 'quote_new_comment_to_customer');
define('SMS_TRIGGER_QUOTE_NEW_COMMENT_TO_STAFF', 'quote_new_comment_to_staff');
define('LEADS_TYPE_OF_TOUR_ID', 2);
define('LEADS_TOUR_DATE_TIME_ID', 1);
define('LEADS_PICKUP_ADDRESS_ID', 51);
define('LEADS_DROPOFF_ADDRESS_ID', 59);
define('LEADS_HOW_MANY_GUESTS_ID', 58);
define('BOOKINGS_PICKUP_ADDRESS_ID', 50);
define('BOOKINGS_DROPOFF_ADDRESS_ID', 49);
define('BOOKINGS_NUMBER_OF_GUESTS_ID', 48);
define('BOOKINGS_AMOUNT_TO_COLLECT_ID', 47);
define('LEADS_WINE_BOOKING_ID', 54);
define('WINE_TOURS', 3);
define('COASTAL_TOURS', 7);
define('BONDS', 'BND2');
define('WINE_TOUR_BOOKING_FEE', 'WTBOOK');
define('COASTAL_TOUR_BOOKING_FEE', 'COASTBOOK');
define('TOUR_COMPLETED', 'TOUR COMPLETED');
define('DO_NOT_RETURN_BOND', 'DO NOT RETURN BOND');

register_merge_fields('booking/merge_fields/quotes_merge_fields');
register_merge_fields('booking/merge_fields/bookings_merge_fields');

/**
 * Quote attachments
 */
define('QUOTE_ATTACHMENTS_FOLDER', FCPATH . 'uploads/quotes' . '/');
if(!is_dir(QUOTE_ATTACHMENTS_FOLDER)){
    mkdir(QUOTE_ATTACHMENTS_FOLDER);
}

/**
 * Booking discussions attachments
 */
define('BOOKING_DISCUSSION_ATTACHMENT_FOLDER', FCPATH . 'uploads/bookings_discussions' . '/');
if(!is_dir(BOOKING_DISCUSSION_ATTACHMENT_FOLDER)){
    mkdir(BOOKING_DISCUSSION_ATTACHMENT_FOLDER);
}

/**
 * Booking files attachments
 */
define('BOOKING_ATTACHMENTS_FOLDER', FCPATH . 'uploads/bookings' . '/');
if(!is_dir(BOOKING_ATTACHMENTS_FOLDER)){
    mkdir(BOOKING_ATTACHMENTS_FOLDER);
}

$CI = &get_instance();

hooks()->add_filter('sidebar_menu_items', 'app_admin_sidebar_custom_options_booking', 999);

function app_admin_sidebar_custom_options_booking($items)
{
    return _apply_menu_items_options_booking($items, json_decode(get_option('aside_menu_active')));
}
function _apply_menu_items_options_booking($items, $options)
{
    foreach ($items as $key => $item) {
        if (isset($options->{$item['slug']})) {
            if (isset($options->{$item['slug']}->disabled)
                && $options->{$item['slug']}->disabled === 'true') {
                // Main item is disabled
                unset($items[$key]);
            } else {
                // Main item has custom icon
                if (isset($options->{$item['slug']}->icon) && $options->{$item['slug']}->icon === false) {
                    // False is when user set the icon empty from the builder
                    $items[$key]['icon'] = '';
                } elseif (!empty($options->{$item['slug']}->icon)) {
                    $items[$key]['icon'] = $options->{$item['slug']}->icon;
                }
            }

            foreach ($item['children'] as $childKey => $child) {
                if (isset($options->{$item['slug']}->children->{$child['slug']})) {
                    if (isset($options->{$item['slug']}->children->{$child['slug']}->disabled)
                        && $options->{$item['slug']}->children->{$child['slug']}->disabled === 'true') {
                        // Is disabled
                        unset($items[$key]['children'][$childKey]);
                    } else {
                        // Has custom icon
                        if ($options->{$item['slug']}->children->{$child['slug']}->icon === false) {
                            $items[$key]['children'][$childKey]['icon'] = '';
                        } elseif (!empty($options->{$item['slug']}->children->{$child['slug']}->icon)) {
                            $items[$key]['children'][$childKey]['icon'] = $options->{$item['slug']}->children->{$child['slug']}->icon;
                        }
                    }
                }
            }
        }
    }

    return $items;
}

hooks()->add_filter('sms_gateway_available_triggers', 'booking_register_sms_triggers');

/**
 * Register SMS Triggers for booking
 *
 * @param [array] $triggers
 * @return array
 */
function booking_register_sms_triggers($triggers)
{
    $quote_merge_fields = [
        '{quote_number}',
        '{quote_id}',
        '{quote_subject}',
        '{quote_open_till}',
        '{quote_subtotal}',
        '{quote_total}',
        '{quote_quote_to}',
        '{quote_link}',
    ];

    $triggers[SMS_TRIGGER_QUOTE_EXP_REMINDER] = [
        'merge_fields' => $quote_merge_fields,
        'label'        => 'Quote Expiration Reminder',
        'info'         => 'Trigger when expiration reminder should be send to quote.',
    ];

    $triggers[SMS_TRIGGER_QUOTE_NEW_COMMENT_TO_CUSTOMER] = [
        'merge_fields' => $quote_merge_fields,
        'label'        => 'New Comment on Quote (to customer)',
        'info'         => 'Trigger when staff member comments on quote, SMS will be sent to quote number (customer/lead).',
    ];

    $triggers[SMS_TRIGGER_QUOTE_NEW_COMMENT_TO_STAFF] = [
        'merge_fields' => $quote_merge_fields,
        'label'        => 'New Comment on Quote (to staff)',
        'info'         => 'Trigger when customer/lead comments on quote, SMS will be sent to quote creator and assigned staff member.',
    ];

    return $triggers;
}

hooks()->add_action('admin_init', 'booking_register_menu_items');

/**
 * Register new menu item in sidebar menu
 */
function booking_register_menu_items()
{
    $CI = &get_instance();

    if (staff_can('view', 'bookings') || staff_can('view_own', 'bookings')) {
        $CI->app_menu->add_sidebar_menu_item(BOOKING_MODULE_NAME, [
            'name'     => 'Booking',
            'href'     => admin_url('booking/bookings'),
            'icon'     => 'fa fa-bars',
            'position' => 12,
        ]);
        $CI->app_menu->add_sidebar_children_item(BOOKING_MODULE_NAME, [
            'slug'     => 'booking_customers',
            'name'     => 'Customers',
            'href'     => admin_url('booking/clients'),
            'icon'     => 'fa fa-user-o',
            'position' => 4,
        ]);
        $CI->app_menu->add_sidebar_children_item(BOOKING_MODULE_NAME, [
            'slug'     => 'booking_leads',
            'name'     => 'Leads',
            'href'     => admin_url('booking/leads'),
            'icon'     => 'fa fa-tty',
            'position' => 1,
        ]);
        $CI->app_menu->add_sidebar_children_item(BOOKING_MODULE_NAME, [
            'slug'     => 'booking_quotes',
            'name'     => 'Quotes',
            'href'     => admin_url('booking/quotes'),
            'icon'     => 'fa fa-bookmark-o',
            'position' => 2,
        ]);
        $CI->app_menu->add_sidebar_children_item(BOOKING_MODULE_NAME, [
            'slug'     => 'booking_bookings',
            'name'     => 'Bookings',
            'href'     => admin_url('booking/bookings'),
            'icon'     => 'fa fa-bars',
            'position' => 3,
        ]);
        $CI->app_menu->add_sidebar_children_item(BOOKING_MODULE_NAME, [
            'slug'     => 'booking_invoices',
            'name'     => 'Invoices',
            'href'     => admin_url('booking/invoices'),
            'icon'     => 'fa fa-newspaper-o',
            'position' => 5,
        ]);
        $CI->app_menu->add_sidebar_children_item(BOOKING_MODULE_NAME, [
            'slug'     => 'booking_bond_returns',
            'name'     => 'Bond Returns',
            'href'     => admin_url('booking/bond_returns'),
            'icon'     => 'fa fa-area-chart',
            'position' => 6,
        ]);
    }
    update_option('aside_menu_active', '{"dashboard":{"id":"dashboard","icon":"","disabled":"false","position":1},"customers":{"id":"customers","icon":"","disabled":"true","position":"10"},"sales":{"id":"sales","icon":"","disabled":"false","position":"15","children":{"proposals":{"id":"proposals","disabled":"true","icon":"","position":"5"},"estimates":{"id":"estimates","disabled":"true","icon":"","position":"10"},"invoices":{"id":"invoices","disabled":"true","icon":"","position":"15"},"payments":{"disabled":"false","id":"payments","icon":"","position":"20"},"credit_notes":{"disabled":"false","id":"credit_notes","icon":"","position":"25"},"items":{"disabled":"false","id":"items","icon":"","position":"30"}}},"appointly":{"id":"appointly","icon":"","disabled":"false","position":"20","children":{"appointly_sidemenu appointly-user-dashboard":{"disabled":"false","id":"appointly_sidemenu appointly-user-dashboard","position":"5","icon":false},"appointly_sidemenu appointly-callbacks":{"disabled":"false","id":"appointly_sidemenu appointly-callbacks","position":"10","icon":false},"appointly_sidemenu appointly-user-settings":{"disabled":"false","id":"appointly_sidemenu appointly-user-settings","position":"15","icon":false}}},"booking":{"id":"booking","icon":"","disabled":"false","position":"25","children":{"booking_leads":{"disabled":"false","id":"booking_leads","icon":"","position":"5"},"booking_quotes":{"disabled":"false","id":"booking_quotes","icon":"","position":"10"},"booking_bookings":{"disabled":"false","id":"booking_bookings","icon":"","position":"15"},"booking_customers":{"disabled":"false","id":"booking_customers","icon":"","position":"20"},"booking_invoices":{"disabled":"false","id":"booking_invoices","icon":"","position":"25"},"booking_bond_returns":{"disabled":"false","id":"booking_bond_returns","icon":"","position":"30"}}},"subscriptions":{"id":"subscriptions","icon":"","disabled":"false","position":"30"},"expenses":{"id":"expenses","icon":"","disabled":"false","position":"35"},"contracts":{"id":"contracts","icon":"","disabled":"false","position":"40"},"projects":{"id":"projects","icon":"","disabled":"true","position":"45"},"tasks":{"id":"tasks","icon":"","disabled":"false","position":"50"},"support":{"id":"support","icon":"","disabled":"false","position":"55"},"leads":{"id":"leads","icon":"","disabled":"true","position":"60"},"knowledge-base":{"id":"knowledge-base","icon":"","disabled":"false","position":"65"},"utilities":{"id":"utilities","icon":"","disabled":"false","position":"70","children":{"media":{"disabled":"false","id":"media","icon":"","position":"5"},"bulk-pdf-exporter":{"disabled":"false","id":"bulk-pdf-exporter","icon":"","position":"10"},"calendar":{"disabled":"false","id":"calendar","icon":"","position":"15"},"announcements":{"disabled":"false","id":"announcements","icon":"","position":"20"},"activity-log":{"disabled":"false","id":"activity-log","icon":"","position":"25"},"utility_backup":{"disabled":"false","id":"utility_backup","icon":"","position":"30"},"ticket-pipe-log":{"disabled":"false","id":"ticket-pipe-log","icon":"","position":"35"}}},"reports":{"id":"reports","icon":"","disabled":"false","position":"75","children":{"sales-reports":{"disabled":"false","id":"sales-reports","icon":"","position":"5"},"expenses-reports":{"disabled":"false","id":"expenses-reports","icon":"","position":"10"},"expenses-vs-income-reports":{"disabled":"false","id":"expenses-vs-income-reports","icon":"","position":"15"},"leads-reports":{"disabled":"false","id":"leads-reports","icon":"","position":"20"},"timesheets-reports":{"disabled":"false","id":"timesheets-reports","icon":"","position":"25"},"knowledge-base-reports":{"disabled":"false","id":"knowledge-base-reports","icon":"","position":"30"}}}}');
}

hooks()->add_action('app_init', 'booking_regitster_client_menu_items');

/**
 * Register new menu item in theme menu
 */
function booking_regitster_client_menu_items()
{
    $CI = &get_instance();

    $CI->app_menu->add_theme_item('quotes', [
        'name'     => _l('clients_nav_quotes'),
        'href'     => site_url('booking/quotes_public'),
        'position' => 30,
    ]);
    $CI->app_menu->add_theme_item('Bookings', [
        'name'     => _l('clients_nav_bookings'),
        'href'     => site_url('booking/bookings_public'),
        'position' => 30,
    ]);
    $CI->app_menu->add_theme_item('Invoices', [
        'name'     => _l('clients_nav_invoices'),
        'href'     => site_url('booking/invoices_public'),
        'position' => 30,
    ]);
    $handle = fopen(APPPATH . "/config/my_routes.php", "w");
    $routes_file_path = APP_MODULES_PATH . BOOKING_MODULE_NAME . "/routes.php";
    $handle1 = fopen($routes_file_path, "r");
    $content = fread($handle1, filesize($routes_file_path));
    fclose($handle1);
    fwrite($handle, $content);
    fclose($handle);
    
}

/**
 * Register new custom fields for quote
 */
hooks()->add_action('after_custom_fields_select_options', 'quote_custom_fields');
function quote_custom_fields($custom_field)
{
    $selected = (isset($custom_field) && $custom_field->fieldto == 'quotes') ? 'selected' : '';
    echo '<option value="quotes"  ' . ($selected) . '>' . _l('quotes') . '</option>';
}

/**
 * Register new custom fields for booking
 */
hooks()->add_action('after_custom_fields_select_options', 'booking_custom_fields');
function booking_custom_fields($custom_field)
{
    $selected = (isset($custom_field) && $custom_field->fieldto == 'bookings') ? 'selected' : '';
    echo '<option value="bookings"  ' . ($selected) . '>' . _l('bookings') . '</option>';
}

/**
 * Register new custom fields for tour
 */
hooks()->add_action('after_custom_fields_select_options', 'tour_custom_fields');
function tour_custom_fields($custom_field)
{
    $selected = (isset($custom_field) && $custom_field->fieldto == 'tours') ? 'selected' : '';
    echo '<option value="tours"  ' . ($selected) . '>' . _l('tours') . '</option>';
}

hooks()->add_action('admin_init', 'app_init_customer_profile_quotes_tab');

function app_init_customer_profile_quotes_tab()
{
    $CI = &get_instance();

    $CI->app_tabs->add_customer_profile_tab('quotes', [
        'name'     => _l('quotes'),
        'icon'     => 'fa fa-file-powerpoint-o',
        'view'     => 'admin/clients/groups/quotes',
        'visible'  => (has_permission('quotes', '', 'view') || has_permission('quotes', '', 'view_own') || (get_option('allow_staff_view_quotes_assigned') == 1 && staff_has_assigned_quotes())),
        'position' => 35,
    ]);
    
    $CI->app_tabs->add_customer_profile_tab('bookings', [
        'name'     => _l('bookings'),
        'icon'     => 'fa fa-bars',
        'view'     => 'admin/clients/groups/bookings',
        'visible'  => (has_permission('bookings', '', 'view') || has_permission('bookings', '', 'view_own') || (get_option('allow_staff_view_bookings_assigned') == 1 && staff_has_assigned_bookings())),
        'position' => 35,
    ]);
}

hooks()->add_action('admin_init', 'app_init_booking_tabs');

/**
 * Init the default booking tabs
 * @return null
 */
function app_init_booking_tabs()
{
    $CI = &get_instance();

    $CI->app_tabs->add('booking_overview', [
        'name'     => _l('booking_overview'),
        'icon'     => 'fa fa-th',
        'view'     => 'admin/bookings/booking_overview',
        'position' => 5,
    ], 'booking');

    $CI->app_tabs->add('booking_tours', [
        'name'                      => _l('tours'),
        'icon'                      => 'fa fa-check-circle',
        'view'                      => 'admin/bookings/booking_tours',
        'position'                  => 10,
        'linked_to_customer_option' => ['view_tours'],
    ], 'booking');

    $CI->app_tabs->add('booking_timesheets', [
        'name'                      => _l('booking_timesheets'),
        'icon'                      => 'fa fa-clock-o',
        'view'                      => 'admin/bookings/booking_timesheets',
        'position'                  => 15,
        'linked_to_customer_option' => ['view_timesheets'],
    ], 'booking');

    $CI->app_tabs->add('booking_milestones', [
        'name'                      => _l('booking_milestones'),
        'icon'                      => 'fa fa-rocket',
        'view'                      => 'admin/bookings/booking_milestones',
        'position'                  => 20,
        'linked_to_customer_option' => ['view_milestones'],
    ], 'booking');

    $CI->app_tabs->add('booking_files', [
        'name'                      => _l('booking_files'),
        'icon'                      => 'fa fa-files-o',
        'view'                      => 'admin/bookings/booking_files',
        'position'                  => 25,
        'linked_to_customer_option' => ['upload_files'],
    ], 'booking');

    $CI->app_tabs->add('booking_discussions', [
        'name'                      => _l('booking_discussions'),
        'icon'                      => 'fa fa-commenting',
        'view'                      => 'admin/bookings/booking_discussions',
        'position'                  => 30,
        'linked_to_customer_option' => ['open_discussions'],
    ], 'booking');

    $CI->app_tabs->add('booking_gantt', [
        'name'                      => _l('booking_gant'),
        'icon'                      => 'fa fa-align-left',
        'view'                      => 'admin/bookings/booking_gantt',
        'position'                  => 35,
        'linked_to_customer_option' => ['view_gantt'],
    ], 'booking');

    $CI->app_tabs->add('booking_tickets', [
        'name'     => _l('booking_tickets'),
        'icon'     => 'fa fa-life-ring',
        'view'     => 'admin/bookings/booking_tickets',
        'position' => 40,
        'visible'  => (get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member(),
    ], 'booking');

    $CI->app_tabs->add('booking_contracts', [
        'name'     => _l('contracts'),
        'icon'     => 'fa fa-file',
        'view'     => 'admin/bookings/booking_contracts',
        'position' => 45,
        'visible'  => has_permission('contracts', '', 'view') || has_permission('contracts', '', 'view_own'),
    ], 'booking');

    $CI->app_tabs->add('sales', [
        'name'     => _l('sales_string'),
        'icon'     => 'fa fa-balance-scale',
        'position' => 50,
        'collapse' => true,
        'visible'  => (has_permission('estimates', '', 'view') || has_permission('estimates', '', 'view_own') || (get_option('allow_staff_view_estimates_assigned') == 1 && staff_has_assigned_estimates()))
            || (has_permission('invoices', '', 'view_own') || (get_option('allow_staff_view_invoices_assigned') == 1 && staff_has_assigned_invoices()))
            || (has_permission('expenses', '', 'view') || has_permission('expenses', '', 'view_own')),
    ], 'booking');

    $CI->app_tabs->add_child('sales', [
        'slug'     => 'booking_invoices',
        'name'     => _l('booking_invoices'),
        'view'     => 'admin/bookings/booking_invoices',
        'position' => 5,
        'visible'  => (has_permission('invoices', '', 'view_own') || (get_option('allow_staff_view_invoices_assigned') == 1 && staff_has_assigned_invoices())),
    ], 'booking');

    $CI->app_tabs->add_child('sales', [
        'slug'     => 'booking_estimates',
        'name'     => _l('estimates'),
        'view'     => 'admin/bookings/booking_estimates',
        'position' => 10,
        'visible'  => (has_permission('estimates', '', 'view') || has_permission('estimates', '', 'view_own') || (get_option('allow_staff_view_estimates_assigned') == 1 && staff_has_assigned_estimates())),
    ], 'booking');

    $CI->app_tabs->add_child('sales', [
        'slug'     => 'booking_expenses',
        'name'     => _l('booking_expenses'),
        'view'     => 'admin/bookings/booking_expenses',
        'position' => 15,
       'visible'   => has_permission('expenses', '', 'view') || has_permission('expenses', '', 'view_own'),
    ], 'booking');

    $CI->app_tabs->add_child('sales', [
        'slug'     => 'booking_credit_notes',
        'name'     => _l('credit_notes'),
        'view'     => 'admin/bookings/booking_credit_notes',
        'position' => 20,
        'visible'  => has_permission('credit_notes', '', 'view') || has_permission('credit_notes', '', 'view_own'),
    ], 'booking');

    $CI->app_tabs->add_child('sales', [
        'slug'     => 'booking_subscriptions',
        'name'     => _l('subscriptions'),
        'view'     => 'admin/bookings/booking_subscriptions',
        'position' => 25,
        'visible'  => has_permission('subscriptions', '', 'view') || has_permission('subscriptions', '', 'view_own'),
    ], 'booking');

    $CI->app_tabs->add('booking_notes', [
        'name'     => _l('booking_notes'),
        'icon'     => 'fa fa-file-o',
        'view'     => 'admin/bookings/booking_notes',
        'position' => 55,
    ], 'booking');

    $CI->app_tabs->add('booking_activity', [
        'name'                      => _l('booking_activity'),
        'icon'                      => 'fa fa-exclamation',
        'view'                      => 'admin/bookings/booking_activity',
        'position'                  => 60,
        'linked_to_customer_option' => ['view_activity_log'],
    ], 'booking');
}

hooks()->add_action('lead_converted_to_customer', 'create_draft_quote_when_converted_to_customer');

/**
 * Create a draft quote from lead when tour staff convert lead to customer
 * @return null
 */
function create_draft_quote_when_converted_to_customer($data){
    $CI = &get_instance();
    $CI->load->model('leads_model');
    $CI->load->model('clients_model');
    $lead = $CI->leads_model->get($data['lead_id']);
    $customer = $CI->clients_model->get($data['customer_id']);
    $default_quote_data = array();
    $default_quote_data['rel_type'] = 'lead';
    $default_quote_data['rel_id'] = $data['lead_id'];
    $default_quote_data['date'] = date("d/m/Y", strtotime($lead->dateassigned));
    $open_till = date("d/m/Y",mktime(0,0,0,date("n",strtotime($lead->dateassigned)),date("j",strtotime($lead->dateassigned))+3,date("Y",strtotime($lead->dateassigned))));
    $default_quote_data['open_till'] = $open_till;
    // $default_quote_data['currency'] = 1;
    $default_quote_data['discount_type'] = '';
    $default_quote_data['tags'] = '';
    $default_quote_data['allow_comments'] = 'on';
    $default_quote_data['status'] = '6';
    $default_quote_data['assigned'] = $lead->assigned;
    $default_quote_data['quote_to'] = $lead->name;
    $default_quote_data['address'] = $customer->address;
    $default_quote_data['city'] = $customer->city;
    $default_quote_data['state'] = $customer->state;
    $default_quote_data['country'] = $customer->country;
    $default_quote_data['zip'] = $customer->zip;
    $default_quote_data['email'] = $lead->email;
    $default_quote_data['phone'] = $lead->phonenumber;
    $default_quote_data['item_select'] = '';
    $default_quote_data['show_quantity_as'] = '1';
    $default_quote_data['description'] = '';
    $default_quote_data['long_description'] = '';
    $default_quote_data['quantity'] = '1';
    $default_quote_data['unit'] = '';
    $default_quote_data['rate'] = '';
    $default_quote_data['newitems'] = array();
    
    $CI->db->where('description', BONDS);
    $bond = $CI->db->get(db_prefix() . 'items')->row();
    
    $default_quote_data['newitems'][] = array(
        'order' => '1',
        'description' => $bond->description,
        'long_description' => $bond->long_description,
        'qty' => '1',
        'unit' => $bond->unit,
        'rate' => $bond->rate
    );
    
    $CI->db->select(db_prefix() . 'customfieldsvalues.value');
    $CI->db->where('relid', $data['lead_id']);
    $CI->db->where('fieldid', LEADS_TOUR_DATE_TIME_ID);
    $CI->db->where('fieldto', 'leads');
    $tour_date = $CI->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
    $tour_date1 = date("d/m/Y H:i", strtotime($tour_date));
    
    $CI->db->select(db_prefix() . 'customfieldsvalues.value');
    $CI->db->where('relid', $data['lead_id']);
    $CI->db->where('fieldid', LEADS_TYPE_OF_TOUR_ID);
    $CI->db->where('fieldto', 'leads');
    $value = $CI->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
    
    $CI->db->where('long_description', $value);
    $item_selected = $CI->db->get(db_prefix() . 'items')->row();
    
    $subject = $item_selected->description . ' ' . $lead->name . ' ' . $item_selected->long_description . ' ' . $tour_date1 . ' ' . $lead->phonenumber;
    $default_quote_data['subject'] = $subject;
    
    $total_rate = $bond->rate;
    if($item_selected){
        $default_quote_data['newitems'][] = array(
            'order' => '2',
            'description' => $item_selected->description,
            'long_description' => $item_selected->long_description,
            'qty' => '1',
            'unit' => $item_selected->unit,
            'rate' => $item_selected->rate
        );
        $total_rate += $item_selected->rate;
    }
    $default_quote_data['subtotal'] = $total_rate ;
    $default_quote_data['discount_percent'] = '0' ;
    $default_quote_data['discount_total'] = '0.00' ;
    $default_quote_data['adjustment'] = '0' ;
    $default_quote_data['total'] = $total_rate ;
    
    $CI->db->select(db_prefix() . 'customfieldsvalues.value');
    $CI->db->where('relid', $data['lead_id']);
    $CI->db->where('fieldid', LEADS_WINE_BOOKING_ID);
    $CI->db->where('fieldto', 'leads');
    $value1 = $CI->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
    if($value1 == 'Yes (Booking Fees & Charges Apply)'){
        $booking_fee = false;
        if($item_selected->group_id == WINE_TOURS){
            $CI->db->where('description', WINE_TOUR_BOOKING_FEE);
            $booking_fee = $CI->db->get(db_prefix() . 'items')->row();
        }
        if($item_selected->group_id == COASTAL_TOURS){
            $CI->db->where('description', COASTAL_TOUR_BOOKING_FEE);
            $booking_fee = $CI->db->get(db_prefix() . 'items')->row();
        }
        
        if($booking_fee){
            $default_quote_data['newitems'][] = array(
                'order' => '3',
                'description' => $booking_fee->description,
                'long_description' => $booking_fee->long_description,
                'qty' => '1',
                'unit' => $booking_fee->unit,
                'rate' => $booking_fee->rate,
            );
        
            $default_quote_data['subtotal'] = $total_rate + $booking_fee->rate;
            $default_quote_data['discount_percent'] = '0' ;
            $default_quote_data['discount_total'] = '0.00' ;
            $default_quote_data['adjustment'] = '0' ;
            $default_quote_data['total'] = $total_rate + $booking_fee->rate;
        }
    }
    $CI->load->model('booking/quotes_model');
    $id = $CI->quotes_model->add($default_quote_data);
}

hooks()->add_action('web_to_lead_form_submitted', 'create_draft_quote_with_dupicated_lead');

function create_draft_quote_with_dupicated_lead($data)
{
    $CI = &get_instance();
    $CI->load->model('leads_model');
    $CI->load->model('clients_model');
    $lead = $CI->leads_model->get($data['lead_id']);
    if($lead){
        $CI->db->select(db_prefix() . 'contacts.id');
        $CI->db->where('email', $lead->email);
        $query = $CI->db->get(db_prefix() . 'contacts');
        if($query->num_rows() > 0){
            $id = $query->row()->id;
            $customer = $CI->clients_model->get($id);
            $default_quote_data = array();
            $default_quote_data['rel_type'] = 'lead';
            $default_quote_data['rel_id'] = $data['lead_id'];
            $default_quote_data['date'] = date("d/m/Y", strtotime($lead->dateassigned));
            $open_till = date("d/m/Y",mktime(0,0,0,date("n",strtotime($lead->dateassigned)),date("j",strtotime($lead->dateassigned))+3,date("Y",strtotime($lead->dateassigned))));
            $default_quote_data['open_till'] = $open_till;
            // $default_quote_data['currency'] = 1;
            $default_quote_data['discount_type'] = '';
            $default_quote_data['tags'] = '';
            $default_quote_data['allow_comments'] = 'on';
            $default_quote_data['status'] = '6';
            $default_quote_data['assigned'] = $lead->assigned;
            $default_quote_data['quote_to'] = $lead->name;
            $default_quote_data['address'] = $customer->address;
            $default_quote_data['city'] = $customer->city;
            $default_quote_data['state'] = $customer->state;
            $default_quote_data['country'] = $customer->country;
            $default_quote_data['zip'] = $customer->zip;
            $default_quote_data['email'] = $lead->email;
            $default_quote_data['phone'] = $lead->phonenumber;
            $default_quote_data['item_select'] = '';
            $default_quote_data['show_quantity_as'] = '1';
            $default_quote_data['description'] = '';
            $default_quote_data['long_description'] = '';
            $default_quote_data['quantity'] = '1';
            $default_quote_data['unit'] = '';
            $default_quote_data['rate'] = '';
            $default_quote_data['newitems'] = array();
            
            $CI->db->where('description', BONDS);
            $bond = $CI->db->get(db_prefix() . 'items')->row();
            
            $default_quote_data['newitems'][] = array(
                'order' => '1',
                'description' => $bond->description,
                'long_description' => $bond->long_description,
                'qty' => '1',
                'unit' => $bond->unit,
                'rate' => $bond->rate
            );
            
            $CI->db->select(db_prefix() . 'customfieldsvalues.value');
            $CI->db->where('relid', $data['lead_id']);
            $CI->db->where('fieldid', LEADS_TOUR_DATE_TIME_ID);
            $CI->db->where('fieldto', 'leads');
            $tour_date = $CI->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
            $tour_date1 = date("d/m/Y H:i", strtotime($tour_date));
            
            $CI->db->select(db_prefix() . 'customfieldsvalues.value');
            $CI->db->where('relid', $data['lead_id']);
            $CI->db->where('fieldid', LEADS_TYPE_OF_TOUR_ID);
            $CI->db->where('fieldto', 'leads');
            $value = $CI->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
            
            $CI->db->where('long_description', $value);
            $item_selected = $CI->db->get(db_prefix() . 'items')->row();
            
            $subject = $item_selected->description . ' ' . $lead->name . ' ' . $item_selected->long_description . ' ' . $tour_date1 . ' ' . $lead->phonenumber;
            $default_quote_data['subject'] = $subject;
            
            $total_rate = $bond->rate;
            if($item_selected){
                $default_quote_data['newitems'][] = array(
                    'order' => '2',
                    'description' => $item_selected->description,
                    'long_description' => $item_selected->long_description,
                    'qty' => '1',
                    'unit' => $item_selected->unit,
                    'rate' => $item_selected->rate
                );
                $total_rate += $item_selected->rate;
            }
            $default_quote_data['subtotal'] = $total_rate ;
            $default_quote_data['discount_percent'] = '0' ;
            $default_quote_data['discount_total'] = '0.00' ;
            $default_quote_data['adjustment'] = '0' ;
            $default_quote_data['total'] = $total_rate ;
            
            $CI->db->select(db_prefix() . 'customfieldsvalues.value');
            $CI->db->where('relid', $data['lead_id']);
            $CI->db->where('fieldid', LEADS_WINE_BOOKING_ID);
            $CI->db->where('fieldto', 'leads');
            $value1 = $CI->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
            if($value1 == 'Yes (Booking Fees & Charges Apply)'){
                $booking_fee = false;
                if($item_selected->group_id == WINE_TOURS){
                    $CI->db->where('description', WINE_TOUR_BOOKING_FEE);
                    $booking_fee = $CI->db->get(db_prefix() . 'items')->row();
                }
                if($item_selected->group_id == COASTAL_TOURS){
                    $CI->db->where('description', COASTAL_TOUR_BOOKING_FEE);
                    $booking_fee = $CI->db->get(db_prefix() . 'items')->row();
                }
                
                if($booking_fee){
                    $default_quote_data['newitems'][] = array(
                        'order' => '3',
                        'description' => $booking_fee->description,
                        'long_description' => $booking_fee->long_description,
                        'qty' => '1',
                        'unit' => $booking_fee->unit,
                        'rate' => $booking_fee->rate,
                    );
                
                    $default_quote_data['subtotal'] = $total_rate + $booking_fee->rate;
                    $default_quote_data['discount_percent'] = '0' ;
                    $default_quote_data['discount_total'] = '0.00' ;
                    $default_quote_data['adjustment'] = '0' ;
                    $default_quote_data['total'] = $total_rate + $booking_fee->rate;
                }
            }
            $CI->load->model('booking/quotes_model');
            $id = $CI->quotes_model->add($default_quote_data);
        }
    }
}

hooks()->add_action('after_payment_added', 'add_tag_when_payment_is_made_for_invoice');
/**
 * Add tags to booking when payments made
 */
function add_tag_when_payment_is_made_for_invoice($invoicepaymentrecords_id)
{
    $CI = &get_instance();
    $CI->load->model('booking/bookings_model');
    $CI->load->model('invoices_model');
    $CI->db->where('id', $invoicepaymentrecords_id);
    $invoicepaymentrecord = $CI->db->get(db_prefix() . 'invoicepaymentrecords')->row();
    $invoice_id = $invoicepaymentrecord->invoiceid;
    $invoice = $CI->invoices_model->get($invoice_id);
    if($invoice->total == $invoicepaymentrecord->amount){
        if($invoice->items[0]['description'] == BONDS){
            $booking_id = $invoice->booking_id;
            if($booking_id){
                $tags = get_tags_in($booking_id, 'booking');
                if(count($tags) > 0){
                    $tags = implode(",", $tags);
                    $tags = $tags . ',BOND RECEIVED';
                }
                else $tags = 'BOND RECEIVED';
                if(isset($invoice->items[1])){
                    $tags .= ',PAID BOOKING FEE';
                }
                $data = array();
                $data['booking_id'] = $booking_id;
                $data['status_id'] = 2;
                $CI->bookings_model->mark_as($data);
                handle_tags_save($tags, $booking_id, 'booking');
            }
        }
        else{
            $booking_id = $invoice->booking_id;
            if($booking_id){
                $booking = $CI->bookings_model->get($booking_id);
                $data = (array)$booking;
                $tags = get_tags_in($booking_id, 'booking');
                if(count($tags) > 0){
                    $tags = implode(",", $tags);
                    $tags = $tags . ',PAID IN FULL';
                    $data['booking_id'] = $booking_id;
                    $data['status_id'] = 4;
                    $CI->bookings_model->mark_as($data);
                }
                else $tags = 'PAID FOR TOUR';
                handle_tags_save($tags, $booking_id, 'booking');
            }
        }
    }
}

hooks()->add_action('after_update_booking', 'process_when_booking_is_completed');
/**
 * Handle process when booking is completed(tour is completed)
 */
function process_when_booking_is_completed($booking_id)
{
    $CI = &get_instance();
    $CI->load->model('invoices_model');
    $CI->load->model('clients_model');
    $CI->load->model('bookings_model');
    $booking = $CI->bookings_model->get($booking_id);
}

hooks()->add_filter('before_create_quote', '_format_data_quote');
hooks()->add_filter('before_quote_updated', '_format_data_quote');

/**
 * Remove and format some common used data for the sales feature eq invoice,estimates etc..
 * @param  array $data $_POST data
 * @return array
 */
function _format_data_quote($data)
{
    foreach (_get_quote_unused_names() as $u) {
        if (isset($data['data'][$u])) {
            unset($data['data'][$u]);
        }
    }

    if (isset($data['data']['date']) && $data['data']['date'] != "" ) {
        $data['data']['date'] = to_sql_date($data['data']['date']);
    }

    if (isset($data['data']['open_till']) && $data['data']['open_till'] != "") {
        $data['data']['open_till'] = to_sql_date($data['data']['open_till']);
    }

    if (isset($data['data']['expirydate'])) {
        $data['data']['expirydate'] = to_sql_date($data['data']['expirydate']);
    }

    if (isset($data['data']['duedate'])) {
        $data['data']['duedate'] = to_sql_date($data['data']['duedate']);
    }

    if (isset($data['data']['clientnote'])) {
        $data['data']['clientnote'] = nl2br_save_html($data['data']['clientnote']);
    }

    if (isset($data['data']['terms'])) {
        $data['data']['terms'] = nl2br_save_html($data['data']['terms']);
    }

    if (isset($data['data']['adminnote'])) {
        $data['data']['adminnote'] = nl2br($data['data']['adminnote']);
    }

    if ((isset($data['data']['adjustment']) && !is_numeric($data['data']['adjustment'])) || !isset($data['data']['adjustment'])) {
        $data['data']['adjustment'] = 0;
    } elseif (isset($data['data']['adjustment']) && is_numeric($data['data']['adjustment'])) {
        $data['data']['adjustment'] = number_format($data['data']['adjustment'], get_decimal_places(), '.', '');
    }

    if (isset($data['data']['discount_total']) && $data['data']['discount_total'] == 0) {
        $data['data']['discount_type'] = '';
    }

    foreach (['country', 'billing_country', 'shipping_country', 'booking_id', 'sale_agent'] as $should_be_zero) {
        if (isset($data['data'][$should_be_zero]) && $data['data'][$should_be_zero] == '') {
            $data['data'][$should_be_zero] = 0;
        }
    }

    return $data;
}

/**
 * Unsed $_POST request names, mostly they are used as helper inputs in the form
 * The top function will check all of them and unset from the $data
 * @return array
 */
function _get_quote_unused_names()
{
    return [
        'taxname', 'description',
        'currency_symbol', 'price',
        'isedit', 'taxid',
        'long_description', 'unit',
        'rate', 'quantity',
        'item_select', 'tax',
        'billed_tours', 'billed_expenses',
        'tour_select', 'tour_id',
        'expense_id', 'repeat_every_custom',
        'repeat_type_custom', 'bill_expenses',
        'save_and_send', 'merge_current_invoice',
        'cancel_merged_invoices', 'invoices_to_merge',
        'tags', 's_prefix', 'save_and_record_payment',
    ];
}

/**
 * Register activation hook
 */
register_activation_hook(BOOKING_MODULE_NAME, 'booking_activation_hook');


/**
 * The activation function
 */
function booking_activation_hook()
{
    require(__DIR__ . '/install.php');
}

/**
 * Register module language files
 */
register_language_files(BOOKING_MODULE_NAME, ['booking','quote','tour']);

/**
 * Loads the module function helper
 */
$CI->load->helper(BOOKING_MODULE_NAME . '/booking');
$CI->load->helper(BOOKING_MODULE_NAME . '/quotes');
$CI->load->helper(BOOKING_MODULE_NAME . '/bookings');
$CI->load->helper(BOOKING_MODULE_NAME . '/tours');