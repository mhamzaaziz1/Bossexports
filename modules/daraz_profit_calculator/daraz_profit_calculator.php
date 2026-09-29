<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Daraz Profit Calculator
Description: Calculate product profit based on Daraz payouts, commissions, and fees.
Version: 1.0.0
Requires at least: 2.3.2
*/

define('DARAZ_PROFIT_CALCULATOR_MODULE_NAME', 'daraz_profit_calculator');

hooks()->add_action('admin_init', 'daraz_profit_calculator_module_init_menu_items');
hooks()->add_action('admin_init', 'daraz_profit_calculator_permissions');

/**
* Load the module helper
*/
$CI = &get_instance();
$CI->load->helper(DARAZ_PROFIT_CALCULATOR_MODULE_NAME . '/daraz_profit_calculator');

/**
* Register activation hook
*/
register_activation_hook(DARAZ_PROFIT_CALCULATOR_MODULE_NAME, 'daraz_profit_calculator_activation_hook');

function daraz_profit_calculator_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files
*/
register_language_files(DARAZ_PROFIT_CALCULATOR_MODULE_NAME, [DARAZ_PROFIT_CALCULATOR_MODULE_NAME]);

/**
 * Init daraz_profit_calculator module menu items in setup in admin_init hook
 * @return null
 */
function daraz_profit_calculator_module_init_menu_items()
{
    /**
     * If the current user is not admin, don't show the menu
     */
    if (!is_admin()) {
        return;
    }

    $CI = &get_instance();

    $CI->app_menu->add_sidebar_menu_item('daraz-profit', [
        'name'     => 'Daraz Profit',
        'icon'     => 'fa fa-calculator',
        'position' => 10,
    ]);

    $CI->app_menu->add_sidebar_children_item('daraz-profit', [
        'slug'     => 'daraz-calculator',
        'name'     => 'Calculator',
        'href'     => admin_url('daraz_profit_calculator'),
        'position' => 1,
    ]);

    $CI->app_menu->add_sidebar_children_item('daraz-profit', [
        'slug'     => 'daraz-categories',
        'name'     => 'Categories',
        'href'     => admin_url('daraz_profit_calculator/categories'),
        'position' => 2,
    ]);

    $CI->app_menu->add_sidebar_children_item('daraz-profit', [
        'slug'     => 'daraz-settings',
        'name'     => 'Settings',
        'href'     => admin_url('daraz_profit_calculator/settings'),
        'position' => 3,
    ]);
}

function daraz_profit_calculator_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('daraz_profit_calculator', $capabilities, _l('daraz_profit_calculator'));
}

hooks()->add_action('admin_head', 'daraz_profit_calculator_inject_js');

function daraz_profit_calculator_inject_js()
{
    $CI = &get_instance();
    $categories = $CI->daraz_model->get_categories();
    $options = '<option value=""></option>';
    foreach($categories as $cat) {
        $options .= '<option value="'.$cat['id'].'">'.$cat['name'].' ('.$cat['commission_percentage'].'%)</option>';
    }
    ?>
    <script>
    $(function() {
        // Inject field into Items modal when it's about to be shown
        $(document).on('show.bs.modal', '#sales_item_modal', function() {
            var $modal = $(this);
            var $form = $modal.find('form');
            var item_id = $modal.find('input[name="itemid"]').val();
            
            // Check if field already exists, if not, add it
            if ($modal.find('#daraz_category_id').length === 0) {
                var html = '<div class="row mtop15"><div class="col-md-12"><div class="form-group">';
                html += '<label class="control-label">Daraz Category</label>';
                html += '<select name="daraz_category_id" id="daraz_category_id" class="selectpicker" data-width="100%" data-none-selected-text="No Category Selected">';
                html += '<?php echo $options; ?>';
                html += '</select>';
                html += '</div></div></div>';
                
                // Append after Item Group select
                $modal.find('select[name="group_id"]').parent().parent().after(html);
                $modal.find('#daraz_category_id').selectpicker('refresh');
            }

            // If editing, fetch the current category
            if (item_id) {
                $.get(admin_url + 'daraz_profit_calculator/get_item_category/' + item_id, function(response) {
                    var data = JSON.parse(response);
                    $modal.find('#daraz_category_id').selectpicker('val', data.daraz_category_id).selectpicker('refresh');
                });
            } else {
                $modal.find('#daraz_category_id').selectpicker('val', '').selectpicker('refresh');
            }
        });
    });
    </script>
    <?php
}

/**
 * Hook to save Daraz Category when item is created/updated
 */
hooks()->add_filter('before_update_item', 'daraz_save_item_category');
hooks()->add_filter('before_add_item', 'daraz_save_item_category');

function daraz_save_item_category($data, $id = null)
{
    if (isset($_POST['daraz_category_id'])) {
        $data['daraz_category_id'] = $_POST['daraz_category_id'];
    }
    return $data;
}
