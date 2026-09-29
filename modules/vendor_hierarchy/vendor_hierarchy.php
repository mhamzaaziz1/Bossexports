<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Vendor Hierarchy View
Description: Enables a 4-level deep parent-child hierarchy between vendors. Provides hierarchy labels, breadcrumb navigation, tree views, and comprehensive reporting.
Version: 1.0.0
Requires at least: 2.3.*
Author: Custom Development
*/

define('VENDOR_HIERARCHY_MODULE_NAME', 'vendor_hierarchy');

// =========================================================================
// HOOKS REGISTRATION
// =========================================================================

hooks()->add_action('admin_init', 'vendor_hierarchy_module_init_menu_items');
hooks()->add_action('admin_init', 'vendor_hierarchy_add_settings_tab');
hooks()->add_filter('before_settings_updated', 'vendor_hierarchy_settings_updated');
hooks()->add_action('admin_init', 'vendor_hierarchy_register_permissions');
hooks()->add_action('app_admin_footer', 'vendor_hierarchy_add_footer_components');
hooks()->add_action('app_admin_footer', 'vendor_hierarchy_inject_parent_field');
hooks()->add_filter('before_client_added', 'vendor_hierarchy_before_vendor_added');
hooks()->add_filter('before_client_updated', 'vendor_hierarchy_before_vendor_updated', 10, 2);
hooks()->add_filter('customers_table_row_data', 'vendor_hierarchy_add_table_columns', 10, 2);
hooks()->add_action('before_client_deleted', 'vendor_hierarchy_before_vendor_deleted');

// =========================================================================
// ACTIVATION
// =========================================================================

register_activation_hook(VENDOR_HIERARCHY_MODULE_NAME, 'vendor_hierarchy_module_activation_hook');

function vendor_hierarchy_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

// =========================================================================
// LANGUAGE
// =========================================================================

register_language_files(VENDOR_HIERARCHY_MODULE_NAME, [VENDOR_HIERARCHY_MODULE_NAME]);

// =========================================================================
// MENU ITEMS
// =========================================================================

function vendor_hierarchy_module_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('vendor_hierarchy', '', 'view') || is_admin()) {

        // Load the model to get level labels
        $CI->load->model('vendor_hierarchy/vendor_hierarchy_model');
        $labels = $CI->vendor_hierarchy_model->get_level_labels();

        // Top-level sidebar menu
        $CI->app_menu->add_sidebar_menu_item('vendor-hierarchy', [
            'name'     => _l('vendor_hierarchy'),
            'href'     => admin_url('vendor_hierarchy/search'),
            'icon'     => 'fa fa-sitemap',
            'position' => 7,
        ]);

        // Child: Search / Browse Hierarchy
        $CI->app_menu->add_sidebar_children_item('vendor-hierarchy', [
            'slug'     => 'vh-search',
            'name'     => _l('vendor_hierarchy_search'),
            'href'     => admin_url('vendor_hierarchy/search'),
            'icon'     => 'fa fa-search',
            'position' => 1,
        ]);

        // Children: One per hierarchy level
        $level_icons = ['fa fa-building', 'fa fa-industry', 'fa fa-map-marker', 'fa fa-home'];
        for ($i = 1; $i <= 4; $i++) {
            $label_key = 'level_' . $i . '_label';
            $CI->app_menu->add_sidebar_children_item('vendor-hierarchy', [
                'slug'     => 'vh-level-' . $i,
                'name'     => isset($labels[$label_key]) ? $labels[$label_key] : 'Level ' . $i,
                'href'     => admin_url('vendor_hierarchy/search?level=' . $i),
                'icon'     => $level_icons[$i - 1],
                'position' => $i + 1,
            ]);
        }

        // Child: Reports
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'vh-reports',
            'name'     => _l('vendor_hierarchy_reports'),
            'href'     => admin_url('vendor_hierarchy/reports'),
            'icon'     => 'fa fa-chart-bar',
            'position' => 7,
        ]);

    }
}

function vendor_hierarchy_add_settings_tab()
{
    $CI = &get_instance();
    $CI->app_tabs->add_settings_tab('vendor-hierarchy', [
        'name'     => _l('vendor_hierarchy'),
        'view'     => 'vendor_hierarchy/settings',
        'position' => 60,
    ]);
}

function vendor_hierarchy_settings_updated($data)
{
    if (isset($data['settings']['level_1_label'])) {
        $CI = &get_instance();
        $CI->load->model('vendor_hierarchy/vendor_hierarchy_model');
        
        $vh_settings = [];
        for ($i = 1; $i <= 4; $i++) {
            $key = 'level_' . $i . '_label';
            if (isset($data['settings'][$key])) {
                $vh_settings[$key] = $data['settings'][$key];
                unset($data['settings'][$key]);
            }
        }
        
        if (!empty($vh_settings)) {
            $CI->vendor_hierarchy_model->update_settings($vh_settings);
        }
    }
    return $data;
}

// =========================================================================
// PERMISSIONS
// =========================================================================

function vendor_hierarchy_register_permissions()
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('vendor_hierarchy', $capabilities, _l('vendor_hierarchy'));
}

// =========================================================================
// ASSETS (CSS + JS)
// =========================================================================

function vendor_hierarchy_add_footer_components()
{
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];

    // CSS — Load on all purchase/vendor pages and module pages
    if (strpos($viewuri, '/admin/purchase') !== false || strpos($viewuri, '/admin/vendor_hierarchy') !== false) {
        echo '<link href="' . module_dir_url(VENDOR_HIERARCHY_MODULE_NAME, 'assets/css/vendor_hierarchy.css') . '?v=' . time() . '"  rel="stylesheet" type="text/css" />';
    }

    // JS — Load on vendor profile and module pages
    if (strpos($viewuri, '/admin/purchase/vendor') !== false || strpos($viewuri, '/admin/vendor_hierarchy') !== false) {
        echo '<script src="' . module_dir_url(VENDOR_HIERARCHY_MODULE_NAME, 'assets/js/vendor_hierarchy.js') . '?v=' . time() . '"></script>';
    }
}

// =========================================================================
// INJECT PARENT VENDOR FIELD INTO VENDOR PROFILE
// =========================================================================

function vendor_hierarchy_inject_parent_field()
{
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];

    // Only on vendor profile page
    if (strpos($viewuri, '/admin/purchase/vendor') === false) {
        return;
    }

    // Don't inject on the vendor list page
    if (preg_match('#/admin/purchase/vendors?$#', $viewuri)) {
        return;
    }

    $CI->load->model('vendor_hierarchy/vendor_hierarchy_model');

    // Determine vendor ID from the URI
    $segments = explode('/', trim(parse_url($viewuri, PHP_URL_PATH), '/'));
    $vendor_id = 0;
    foreach ($segments as $idx => $seg) {
        if ($seg === 'vendor' && isset($segments[$idx + 1]) && is_numeric($segments[$idx + 1])) {
            $vendor_id = (int)$segments[$idx + 1];
            break;
        }
    }

    $labels = $CI->vendor_hierarchy_model->get_level_labels();
    $current_parent_id = 0;
    $current_depth = 1;
    $breadcrumb = [];

    if ($vendor_id > 0) {
        $vendor = $CI->db->select('parent_vendor_id')
            ->where('userid', $vendor_id)
            ->get(db_prefix() . 'pur_vendor')
            ->row();
        $current_parent_id = ($vendor && $vendor->parent_vendor_id) ? $vendor->parent_vendor_id : 0;
        $current_depth = $CI->vendor_hierarchy_model->get_vendor_depth($vendor_id);
        $breadcrumb = $CI->vendor_hierarchy_model->get_breadcrumb($vendor_id);
    }

    $eligible_parents = $CI->vendor_hierarchy_model->get_eligible_parents($vendor_id);
    $current_level_label = $CI->vendor_hierarchy_model->get_level_label($current_depth);

    // We'll inject a complete block of HTML using JavaScript into the vendor profile form
    ?>
    <script>
    $(function(){
        // Build the parent vendor dropdown HTML
        var parentFieldHtml = '' +
        '<div class="vh-parent-field" id="vh-parent-field-wrapper">' +
            '<div class="vh-parent-field-header">' +
                '<i class="fa fa-sitemap"></i> ' +
                '<?php echo _l("vendor_hierarchy"); ?> — <?php echo addslashes($current_level_label); ?>' +
            '</div>' +
            <?php if (!empty($breadcrumb) && count($breadcrumb) > 1): ?>
            '<div class="mbot10">' +
                '<ol class="vh-breadcrumb">' +
                <?php foreach ($breadcrumb as $idx => $crumb):
                    $badge_classes = ['primary', 'success', 'warning', 'info'];
                    $badge_class = $badge_classes[min($crumb['level'] - 1, 3)];
                    $is_last = ($idx == count($breadcrumb) - 1);
                ?>
                '<li class="vh-breadcrumb-item <?php echo $is_last ? "active" : ""; ?>">' +
                    '<span class="label label-<?php echo $badge_class; ?> vh-breadcrumb-badge"><?php echo addslashes($crumb['level_label']); ?></span> ' +
                    <?php if (!$is_last): ?>
                    '<a href="<?php echo admin_url('purchase/vendor/' . $crumb['userid']); ?>" class="vh-breadcrumb-link"><?php echo addslashes($crumb['company']); ?></a>' +
                    '<i class="fa fa-chevron-right vh-breadcrumb-sep"></i>' +
                    <?php else: ?>
                    '<strong><?php echo addslashes($crumb['company']); ?></strong>' +
                    <?php endif; ?>
                '</li>' +
                <?php endforeach; ?>
                '</ol>' +
            '</div>' +
            <?php endif; ?>
            '<div class="form-group">' +
                '<label for="parent_vendor_id" class="control-label"><?php echo _l("vendor_hierarchy_parent_vendor"); ?></label>' +
                '<select name="parent_vendor_id" id="parent_vendor_id" class="form-control selectpicker" data-live-search="true" data-none-selected-text="<?php echo _l("vendor_hierarchy_no_parent"); ?>" data-width="100%">' +
                    '<option value=""><?php echo _l("vendor_hierarchy_no_parent"); ?></option>' +
                    <?php foreach ($eligible_parents as $ep): ?>
                    '<option value="<?php echo $ep['userid']; ?>" <?php echo ($ep['userid'] == $current_parent_id) ? 'selected' : ''; ?>><?php echo addslashes($ep['company']); ?> (<?php echo addslashes($ep['level_label']); ?>)</option>' +
                    <?php endforeach; ?>
                '</select>' +
            '</div>' +
            '<div id="vh-validation-msg" class="vh-validation-msg"></div>' +
        '</div>';

        // Inject into the vendor profile form — after the first row of contact info or at end
        var $profileTab = $('#contact_info');
        if ($profileTab.length) {
            $profileTab.find('.row').first().after(parentFieldHtml);
            // Re-init selectpicker for the new element
            $('#parent_vendor_id').selectpicker();
        } else {
            // For NEW vendor page (no tabs)
            var $vendorForm = $('form.vendor-form');
            if ($vendorForm.length) {
                $vendorForm.find('.tab-content .tab-pane').first().find('.row').first().after(parentFieldHtml);
                $('#parent_vendor_id').selectpicker();
            }
        }
    });
    </script>
    <?php
}

// =========================================================================
// FILTER: BEFORE VENDOR ADDED — Ensure parent_vendor_id is saved
// =========================================================================

function vendor_hierarchy_before_vendor_added($data)
{
    if (isset($data['parent_vendor_id'])) {
        if (empty($data['parent_vendor_id'])) {
            $data['parent_vendor_id'] = null;
        } else {
            // Validate
            $CI = &get_instance();
            $CI->load->model('vendor_hierarchy/vendor_hierarchy_model');
            $result = $CI->vendor_hierarchy_model->validate_parent_assignment(0, $data['parent_vendor_id']);
            if (!$result['valid']) {
                // Remove invalid parent
                $data['parent_vendor_id'] = null;
                set_alert('warning', $result['message']);
            }
        }
    }
    return $data;
}

// =========================================================================
// FILTER: BEFORE VENDOR UPDATED — Validate & save parent_vendor_id
// =========================================================================

function vendor_hierarchy_before_vendor_updated($data, $id = null)
{
    // The $id might not come from the standard hook — we need to extract it
    if (isset($data['parent_vendor_id'])) {
        if (empty($data['parent_vendor_id'])) {
            $data['parent_vendor_id'] = null;
        } else {
            $CI = &get_instance();
            $CI->load->model('vendor_hierarchy/vendor_hierarchy_model');

            // Get the vendor ID from the data or URI
            $vendor_id = $id;
            if (!$vendor_id) {
                // Try to get from hidden field
                $vendor_id = isset($data['userid']) ? $data['userid'] : 0;
            }

            $result = $CI->vendor_hierarchy_model->validate_parent_assignment($vendor_id, $data['parent_vendor_id']);
            if (!$result['valid']) {
                unset($data['parent_vendor_id']);
                set_alert('warning', $result['message']);
            }
        }
    }
    return $data;
}

// =========================================================================
// FILTER: ADD COLUMNS TO VENDOR TABLE
// =========================================================================

function vendor_hierarchy_add_table_columns($row, $aRow)
{
    // This function adds the parent vendor info to the vendor list table
    // We modify the company column (index 2) to append parent info
    $CI = &get_instance();
    $CI->load->model('vendor_hierarchy/vendor_hierarchy_model');

    $parent = $CI->vendor_hierarchy_model->get_parent_vendor($aRow['userid']);
    $depth = $CI->vendor_hierarchy_model->get_vendor_depth($aRow['userid']);
    $level_label = $CI->vendor_hierarchy_model->get_level_label($depth);

    // Append level badge + parent info after the company name in column 2
    $badge_classes = ['primary', 'success', 'warning', 'info'];
    $badge_class = $badge_classes[min($depth - 1, 3)];

    $extra = '<br><span class="vh-parent-col">';
    $extra .= '<span class="label label-' . $badge_class . ' vh-level-badge">' . $level_label . '</span>';

    if ($parent) {
        $extra .= ' <small class="text-muted">&larr; ' . htmlspecialchars($parent->company) . '</small>';
    }
    $extra .= '</span>';

    // Append to company column (index 2)
    if (isset($row[2])) {
        // Insert before the row-options div
        $row[2] = str_replace('<div class="row-options">', $extra . '<div class="row-options">', $row[2]);
    }

    return $row;
}

// =========================================================================
// CLEANUP: WHEN VENDOR DELETED — Clear children's parent_vendor_id
// =========================================================================

function vendor_hierarchy_before_vendor_deleted($id)
{
    $CI = &get_instance();
    // Set all children's parent_vendor_id to NULL when their parent is deleted
    $CI->db->where('parent_vendor_id', $id);
    $CI->db->update(db_prefix() . 'pur_vendor', ['parent_vendor_id' => null]);
}

// =========================================================================
// HOOK: ADD HIERARCHY TAB TO VENDOR PROFILE SIDEBAR
// =========================================================================

hooks()->add_action('admin_init', 'vendor_hierarchy_register_vendor_tab');

function vendor_hierarchy_register_vendor_tab()
{
    // We use the footer hook to inject the tab + tab content via JS
    hooks()->add_action('app_admin_footer', 'vendor_hierarchy_inject_tab');
}

function vendor_hierarchy_inject_tab()
{
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];

    // Only on vendor profile page (with an ID)
    if (strpos($viewuri, '/admin/purchase/vendor/') === false) {
        return;
    }

    // Extract vendor ID
    $segments = explode('/', trim(parse_url($viewuri, PHP_URL_PATH), '/'));
    $vendor_id = 0;
    foreach ($segments as $idx => $seg) {
        if ($seg === 'vendor' && isset($segments[$idx + 1]) && is_numeric($segments[$idx + 1])) {
            $vendor_id = (int)$segments[$idx + 1];
            break;
        }
    }

    if ($vendor_id <= 0) return;

    $CI->load->model('vendor_hierarchy/vendor_hierarchy_model');

    $labels = $CI->vendor_hierarchy_model->get_level_labels();
    $breadcrumb = $CI->vendor_hierarchy_model->get_breadcrumb($vendor_id);
    $subtree = $CI->vendor_hierarchy_model->get_subtree($vendor_id);
    $children_count = count($CI->vendor_hierarchy_model->get_direct_children($vendor_id));
    $total_descendants = $CI->vendor_hierarchy_model->count_descendants($vendor_id);
    $parent_vendor = $CI->vendor_hierarchy_model->get_parent_vendor($vendor_id);
    $current_depth = $CI->vendor_hierarchy_model->get_vendor_depth($vendor_id);
    $current_level_label = $CI->vendor_hierarchy_model->get_level_label($current_depth);

    // Check if we're on the hierarchy group tab
    $is_hierarchy_tab = (isset($_GET['group']) && $_GET['group'] === 'hierarchy');

    ?>
    <script>
    $(function(){
        // Add the Hierarchy tab to the sidebar navigation
        var $tabList = $('ul.customer-tabs');
        if ($tabList.length) {
            <?php if ($is_hierarchy_tab): ?>
            // Remove active from all other tabs when hierarchy is selected
            $tabList.find('li').removeClass('active');
            <?php endif; ?>
            $tabList.append(
                '<li <?php echo $is_hierarchy_tab ? "class=\"active\"" : ""; ?>>' +
                '<a href="<?php echo admin_url('purchase/vendor/' . $vendor_id . '?group=hierarchy'); ?>" data-group="hierarchy">' +
                '<i class="fa fa-sitemap menu-icon"></i> <?php echo _l("vendor_hierarchy_tree_view"); ?>' +
                ' <span class="badge" style="background:#6366f1;color:#fff;font-size:10px;"><?php echo $total_descendants; ?></span>' +
                '</a></li>'
            );
        }
    });
    </script>
    <?php

    // If the group is 'hierarchy', we need to render the tab content
    // Since the vendor controller loads `vendors/groups/{group}`, we need to handle this
    if ($is_hierarchy_tab) {
        // Create the view file dynamically if needed — but let's just output via JS
        ?>
        <script>
        $(function(){
            var hierarchyContent = <?php
                // Capture the hierarchy tab view content
                ob_start();
                $client = $CI->db->where('userid', $vendor_id)->get(db_prefix() . 'pur_vendor')->row();
                $CI->load->vars(['client' => $client]);

                // Build inline HTML since we can't easily load a view into a different module's tab
                $badge_classes = ['primary', 'success', 'warning', 'info'];
            ?>
            <?php
                $html = '<div class="vh-hierarchy-tab">';

                // Stats
                $html .= '<div class="row mbot20">';
                $html .= '<div class="col-md-3"><div class="panel_s vh-stat-card"><div class="panel-body text-center">';
                $html .= '<div class="vh-stat-icon"><i class="fa fa-layer-group text-primary"></i></div>';
                $html .= '<h3 class="no-margin bold">' . $current_level_label . '</h3>';
                $html .= '<small class="text-muted">' . _l('vendor_hierarchy_current_level') . '</small>';
                $html .= '</div></div></div>';

                $html .= '<div class="col-md-3"><div class="panel_s vh-stat-card"><div class="panel-body text-center">';
                $html .= '<div class="vh-stat-icon"><i class="fa fa-arrow-up text-success"></i></div>';
                $html .= '<h3 class="no-margin bold">' . ($parent_vendor ? htmlspecialchars($parent_vendor->company) : '—') . '</h3>';
                $html .= '<small class="text-muted">' . _l('vendor_hierarchy_parent_vendor') . '</small>';
                $html .= '</div></div></div>';

                $html .= '<div class="col-md-3"><div class="panel_s vh-stat-card"><div class="panel-body text-center">';
                $html .= '<div class="vh-stat-icon"><i class="fa fa-users text-warning"></i></div>';
                $html .= '<h3 class="no-margin bold">' . $children_count . '</h3>';
                $html .= '<small class="text-muted">' . _l('vendor_hierarchy_children_count') . '</small>';
                $html .= '</div></div></div>';

                $html .= '<div class="col-md-3"><div class="panel_s vh-stat-card"><div class="panel-body text-center">';
                $html .= '<div class="vh-stat-icon"><i class="fa fa-project-diagram text-info"></i></div>';
                $html .= '<h3 class="no-margin bold">' . $total_descendants . '</h3>';
                $html .= '<small class="text-muted">' . _l('vendor_hierarchy_total_descendants') . '</small>';
                $html .= '</div></div></div>';
                $html .= '</div>';

                // Breadcrumb
                if (count($breadcrumb) > 1) {
                    $html .= '<div class="panel_s"><div class="panel-body">';
                    $html .= '<h5 class="no-margin bold mbot10"><i class="fa fa-route text-info mright5"></i>' . _l('vendor_hierarchy_breadcrumb') . '</h5>';
                    $html .= '<ol class="vh-breadcrumb">';
                    foreach ($breadcrumb as $idx => $crumb) {
                        $bc = $badge_classes[min($crumb['level'] - 1, 3)];
                        $is_last = ($idx == count($breadcrumb) - 1);
                        $html .= '<li class="vh-breadcrumb-item ' . ($is_last ? 'active' : '') . '">';
                        $html .= '<span class="label label-' . $bc . ' vh-breadcrumb-badge">' . htmlspecialchars($crumb['level_label']) . '</span> ';
                        if (!$is_last) {
                            $html .= '<a href="' . admin_url('purchase/vendor/' . $crumb['userid']) . '" class="vh-breadcrumb-link">' . htmlspecialchars($crumb['company']) . '</a>';
                            $html .= '<i class="fa fa-chevron-right vh-breadcrumb-sep"></i>';
                        } else {
                            $html .= '<strong>' . htmlspecialchars($crumb['company']) . '</strong>';
                        }
                        $html .= '</li>';
                    }
                    $html .= '</ol>';
                    $html .= '</div></div>';
                }

                // Tree
                $html .= '<div class="panel_s"><div class="panel-body">';
                $html .= '<div class="clearfix mbot10">';
                $html .= '<h5 class="no-margin bold pull-left"><i class="fa fa-sitemap text-success mright5"></i>' . _l('vendor_hierarchy_sub_vendors') . '</h5>';
                if (!empty($subtree)) {
                    $html .= '<div class="pull-right">';
                    $html .= '<button class="btn btn-default btn-xs" id="vh-expand-all"><i class="fa fa-expand mright5"></i>' . _l('vendor_hierarchy_expand_all') . '</button> ';
                    $html .= '<button class="btn btn-default btn-xs" id="vh-collapse-all"><i class="fa fa-compress mright5"></i>' . _l('vendor_hierarchy_collapse_all') . '</button>';
                    $html .= '</div>';
                }
                $html .= '</div>';

                if (!empty($subtree)) {
                    $html .= '<div class="vh-tree-container">';
                    $html .= vh_build_tree_html($subtree);
                    $html .= '</div>';
                } else {
                    $html .= '<div class="text-center text-muted mtop20 mbot20">';
                    $html .= '<i class="fa fa-info-circle fa-3x mbot10" style="opacity:0.3;display:block;"></i>';
                    $html .= '<p>' . _l('vendor_hierarchy_no_children') . '</p>';
                    $html .= '</div>';
                }
                $html .= '</div></div>';
                $html .= '</div>';

                ob_end_clean();

                echo json_encode($html);
            ?>;

            // Replace the panel body content with hierarchy
            var $tabContent = $('.tab-content');
            if ($tabContent.length) {
                $tabContent.html(hierarchyContent);
            }
        });
        </script>
        <?php
    }
}

/**
 * Build tree HTML (used in hook output)
 */
function vh_build_tree_html($nodes) {
    if (empty($nodes)) return '';
    $badge_classes = ['primary', 'success', 'warning', 'info'];

    $html = '<ul class="vh-tree">';
    foreach ($nodes as $node) {
        $badge_class = $badge_classes[min($node['level'] - 1, 3)];
        $has_children = !empty($node['children']);
        $status_icon = $node['active'] ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';

        $html .= '<li class="vh-tree-node">';
        $html .= '<div class="vh-tree-node-content">';
        if ($has_children) {
            $html .= '<span class="vh-tree-toggle"><i class="fa fa-caret-down"></i></span>';
        } else {
            $html .= '<span class="vh-tree-toggle-placeholder"></span>';
        }
        $html .= '<span class="label label-' . $badge_class . ' vh-tree-badge">' . htmlspecialchars($node['level_label']) . '</span>';
        $html .= '<a href="' . admin_url('purchase/vendor/' . $node['userid']) . '" class="vh-tree-link">' . htmlspecialchars($node['company']) . '</a>';
        $html .= '<i class="fa ' . $status_icon . ' mleft5"></i>';
        if (!empty($node['phonenumber'])) {
            $html .= '<small class="text-muted mleft5"><i class="fa fa-phone"></i> ' . htmlspecialchars($node['phonenumber']) . '</small>';
        }
        $html .= '</div>';
        if ($has_children) {
            $html .= vh_build_tree_html($node['children']);
        }
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}
