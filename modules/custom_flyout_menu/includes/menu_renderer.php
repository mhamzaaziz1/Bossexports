<?php

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('admin_init', 'cfm_register_dynamic_admin_menu');

/**
 * ADMIN MENU REGISTRATION
 * Example of how to add custom admin menu items via admin_init hook
 */
function cfm_register_dynamic_admin_menu()
{
    if (!cfm_should_apply_menu()) {
        return;
    }

    $CI = &get_instance();

    if (!isset($CI->custom_flyout_menu_model)) {
        $CI->load->model('custom_flyout_menu/custom_flyout_menu_model');
    }

    $area  = 'admin';
    $items = $CI->custom_flyout_menu_model->get_items($area);
    $groups = $CI->custom_flyout_menu_model->get_groups($area);
    $group_map = [];
    foreach ($groups as $group) {
        $group_map[$group['id']] = $group['name'];
    }

    if (empty($items) && empty($groups)) {
        return;
    }

    /*
    =====================================================
    STEP 1 — ADD GROUPS AS REAL COLLAPSIBLE PARENTS
    =====================================================
    */

    foreach ($groups as $group) {

        $CI->app_menu->add_sidebar_menu_item('cfm-group-' . $group['id'], [
            'slug'     => 'cfm-group-' . $group['id'],
            'name'     => $group['name'],
            'href'     => '#',
            'position' => (int) $group['sort_order'],
            'icon'     => !empty($group['icon']) ? 'ph ' . strtolower($group['icon']) : 'fa fa-folder',
            'collapse' => true,
        ]);
    }

    /*
    =====================================================
    STEP 2 — ADD TOP LEVEL ITEMS (NO GROUP, NO PARENT)
    =====================================================
    */

    foreach ($items as $item) {

        if ($item['group_id'] === null && $item['parent_id'] === null) {

            if (!empty($item['permission']) && !staff_can($item['permission'])) {
                continue;
            }

            // Check if this item has STEP 4 children with grouping
            $hasGroupedChildren = false;
            foreach ($items as $child) {
                if ($child['parent_id'] == $item['id']) {
                    if (!empty($child['group_id'])) {
                        $hasGroupedChildren = true;
                        break;
                    }
                }
            }

            $li_attributes = [];
            // Mark items that have ANY grouped children
            if ($hasGroupedChildren) {
                $li_attributes['data-hide-title-if-grouped'] = '1';
            }

            $CI->app_menu->add_sidebar_menu_item('cfm-item-' . $item['id'], [
                'slug'     => 'cfm-item-' . $item['id'],
                'name'     => $item['label'],
                'href'     => !empty($item['url']) ? admin_url($item['url']) : '#',
                'position' => (int) $item['sort_order'],
                'icon'     => !empty($item['icon']) ? 'ph ' . strtolower($item['icon']) : 'fa fa-circle',
                'li_attributes' => !empty($li_attributes) ? $li_attributes : [],
            ]);
        }
    }

    /*
    =====================================================
    STEP 3 — ADD ITEMS INSIDE GROUPS
    =====================================================
    */

    foreach ($items as $item) {

        if ($item['group_id'] !== null && $item['parent_id'] === null) {

            if (!empty($item['permission']) && !staff_can($item['permission'])) {
                continue;
            }

            $li_attributes = [];
            if (!empty($item['group_id']) && isset($group_map[$item['group_id']])) {
                $li_attributes['data-group-id'] = $item['group_id'];
                $li_attributes['data-group-name'] = $group_map[$item['group_id']];
                $li_attributes['data-is-group-parent'] = '1';
            }

            $CI->app_menu->add_sidebar_children_item('cfm-group-' . $item['group_id'], [
                'slug'     => 'cfm-item-' . $item['id'],
                'name'     => $item['label'],
                'href'     => !empty($item['url']) ? admin_url($item['url']) : '#',
                'position' => (int) $item['sort_order'],
                'icon'     => '',
                'li_attributes' => $li_attributes,
            ]);
        }
    }

    /*
    =====================================================
    STEP 4 — ADD NESTED CHILD ITEMS (parent_id)
    =====================================================
    */

    foreach ($items as $item) {

        if ($item['parent_id'] !== null) {

            if (!empty($item['permission']) && !staff_can($item['permission'])) {
                continue;
            }

            $li_attributes = [];
            if (!empty($item['group_id']) && isset($group_map[$item['group_id']])) {
                $li_attributes['data-group-id'] = $item['group_id'];
                $li_attributes['data-group-name'] = $group_map[$item['group_id']];
                $li_attributes['data-is-nested-grouped'] = '1';
            }

            $CI->app_menu->add_sidebar_children_item('cfm-item-' . $item['parent_id'], [
                'slug'     => 'cfm-item-' . $item['id'],
                'name'     => $item['label'],
                'href'     => !empty($item['url']) ? admin_url($item['url']) : '#',
                'position' => (int) $item['sort_order'],
                'icon'     => '',
                'li_attributes' => $li_attributes,
            ]);
        }
    }
}

/**
 * CLIENT MENU REGISTRATION EXAMPLE
 * 
 * How to add custom client-side menu items via clients_init hook:
 * 
 * Example 1: Simple menu item
 * ============================
 * hooks()->add_action('clients_init', 'my_module_clients_menu_items');
 * 
 * function my_module_clients_menu_items()
 * {
 *     // Show menu item only if client is logged in
 *     if (is_client_logged_in()) {
 *         add_theme_menu_item('my-page', [
 *             'name'     => _l('my_page_title'),
 *             'href'     => site_url('my_module/my_page'),
 *             'position' => 10,
 *             'icon'     => 'ph-star',  // Phosphor icon (optional)
 *         ]);
 *     }
 * }
 * 
 * Example 2: Multiple menu items with different permissions
 * ===========================================================
 * hooks()->add_action('clients_init', 'my_module_menu_items');
 * 
 * function my_module_menu_items()
 * {
 *     if (!is_client_logged_in()) {
 *         return;
 *     }
 *     
 *     // Item 1
 *     add_theme_menu_item('my-store', [
 *         'name'     => _l('my_store'),
 *         'href'     => site_url('my_module/store'),
 *         'position' => 10,
 *         'icon'     => 'ph-shopping-bag',
 *     ]);
 *     
 *     // Item 2 - with permission check
 *     if (has_contact_permission('my_feature')) {
 *         add_theme_menu_item('my-points', [
 *             'name'     => _l('my_points'),
 *             'href'     => site_url('my_module/points'),
 *             'position' => 11,
 *             'icon'     => 'ph-coins',
 *         ]);
 *     }
 * }
 * 
 * Example 3: Cashback Module (Real-world example)
 * ================================================
 * hooks()->add_action('clients_init', 'cashback_clients_area_menu_items');
 * 
 * function cashback_clients_area_menu_items()
 * {
 *     $is_admin_disallowed_group_points = cashback_disallow_points_to_client();
 *     
 *     // Show menu item only if client is logged in
 *     if (is_client_logged_in()) {
 *         add_theme_menu_item('store', [
 *             'name'     => _l('cashback_store'),
 *             'href'     => site_url('cashback/store'),
 *             'position' => 10,
 *             'icon'     => 'ph-shopping-cart',
 *         ]);
 *         
 *         if (!$is_admin_disallowed_group_points) {
 *             add_theme_menu_item('points', [
 *                 'name'     => _l('cashback_points_only'),
 *                 'href'     => site_url('cashback/store/points_history'),
 *                 'position' => 9,
 *                 'icon'     => 'ph-heart',
 *             ]);
 *         }
 *     }
 * }
 * 
 * AVAILABLE PHOSPHOR ICONS
 * ========================
 * Common icons to use: ph-book, ph-user-plus, ph-user, ph-folder-open, ph-receipt,
 * ph-file-text, ph-currency-dollar, ph-handshake, ph-calendar, ph-headset, ph-circle
 * 
 * See: https://phosphoricons.com for full list of available icons
 * 
 */


hooks()->add_action('clients_init', 'cfm_register_dynamic_client_menu');

function cfm_register_dynamic_client_menu()
{
    if (!is_client_logged_in()) {
        return;
    }

    if (!cfm_get_setting('client_menu_enabled', '1')) {
        return;
    }

    $CI = &get_instance();

    if (!isset($CI->custom_flyout_menu_model)) {
        $CI->load->model('custom_flyout_menu/custom_flyout_menu_model');
    }

    $area  = 'client';
    $items = $CI->custom_flyout_menu_model->get_items($area);
    $groups = $CI->custom_flyout_menu_model->get_groups($area);
    $group_map = [];
    foreach ($groups as $group) {
        $group_map[$group['id']] = $group['name'];
    }

    if (empty($items) && empty($groups)) {
        return;
    }

    // Build children arrays first (theme items need children embedded)
    $children_map = [];
    
    // Collect group children (STEP 3)
    foreach ($items as $item) {
        if ($item['group_id'] !== null && $item['parent_id'] === null) {
            $li_attributes = [];
            if (!empty($item['group_id']) && isset($group_map[$item['group_id']])) {
                $li_attributes['data-group-id'] = $item['group_id'];
                $li_attributes['data-group-name'] = $group_map[$item['group_id']];
                $li_attributes['data-is-group-parent'] = '1';
            }

            $children_map['cfm-client-group-' . $item['group_id']][] = [
                'slug'     => 'cfm-client-item-' . $item['id'],
                'name'     => $item['label'],
                'href'     => !empty($item['url']) ? site_url($item['url']) : '#',
                'position' => (int) $item['sort_order'],
                'icon'     => '',
                'li_attributes' => $li_attributes,
            ];
        }
    }

    // Collect nested children (STEP 4)
    foreach ($items as $item) {
        if ($item['parent_id'] !== null) {
            $li_attributes = [];
            if (!empty($item['group_id']) && isset($group_map[$item['group_id']])) {
                $li_attributes['data-group-id'] = $item['group_id'];
                $li_attributes['data-group-name'] = $group_map[$item['group_id']];
                $li_attributes['data-is-nested-grouped'] = '1';
            }

            $children_map['cfm-client-item-' . $item['parent_id']][] = [
                'slug'     => 'cfm-client-item-' . $item['id'],
                'name'     => $item['label'],
                'href'     => !empty($item['url']) ? site_url($item['url']) : '#',
                'position' => (int) $item['sort_order'],
                'icon'     => '',
                'li_attributes' => $li_attributes,
            ];
        }
    }

    /*
    =====================================================
    STEP 1 — ADD GROUPS AS COLLAPSIBLE PARENTS
    =====================================================
    */

    foreach ($groups as $group) {
        $slug = 'cfm-client-group-' . $group['id'];
        
        $CI->app_menu->add_theme_item($slug, [
            'slug'     => $slug,
            'name'     => $group['name'],
            'href'     => '#',
            'position' => (int) $group['sort_order'],
            'icon'     => !empty($group['icon']) ? 'ph ph-' . strtolower($group['icon']) : 'ph ph-folder',
            'collapse' => true,
        ]);

        if (isset($children_map[$slug])) {
            foreach ($children_map[$slug] as $child) {
                $CI->app_menu->add_child($slug, $child, 'theme');
            }
        }
    }

    /*
    =====================================================
    STEP 2 — ADD TOP LEVEL ITEMS (NO GROUP, NO PARENT)
    =====================================================
    */

    foreach ($items as $item) {
        if ($item['group_id'] === null && $item['parent_id'] === null) {
            $slug = 'cfm-client-item-' . $item['id'];
            
            // Check if this item has STEP 4 children with grouping
            $hasGroupedChildren = false;
            foreach ($items as $child) {
                if ($child['parent_id'] == $item['id']) {
                    if (!empty($child['group_id'])) {
                        $hasGroupedChildren = true;
                        break;
                    }
                }
            }

            $li_attributes = [];
            if ($hasGroupedChildren) {
                $li_attributes['data-hide-title-if-grouped'] = '1';
            }

            $CI->app_menu->add_theme_item($slug, [
                'slug'     => $slug,
                'name'     => $item['label'],
                'href'     => !empty($item['url']) ? site_url($item['url']) : '#',
                'position' => (int) $item['sort_order'],
                'icon'     => !empty($item['icon']) ? 'ph ph-' . strtolower($item['icon']) : 'ph ph-circle',
                'li_attributes' => !empty($li_attributes) ? $li_attributes : [],
            ]);

            if (isset($children_map[$slug])) {
                foreach ($children_map[$slug] as $child) {
                    $CI->app_menu->add_child($slug, $child, 'theme');
                }
            }
        }
    }
}

