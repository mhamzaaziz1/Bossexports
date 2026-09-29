<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Vendor PO Pricing
Description: Standalone module to allow vendors to input their prices directly on Purchase Orders.
Version: 1.0.0
Requires at least: 2.3.*
*/

define('VENDOR_PRICING_MODULE_NAME', 'vendor_pricing');

hooks()->add_action('admin_init', 'vendor_pricing_permissions');
hooks()->add_action('admin_init', 'vendor_pricing_module_init_menu_items');

/**
* Register activation module hook
*/
register_activation_hook(VENDOR_PRICING_MODULE_NAME, 'vendor_pricing_module_activation_hook');

function vendor_pricing_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

register_merge_fields('vendor_pricing/merge_fields/vendor_pricing_merge_fields');


/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(VENDOR_PRICING_MODULE_NAME, [VENDOR_PRICING_MODULE_NAME]);

/**
 * Init vendor_pricing module menu items in setup in admin_init hook
 * @return null
 */
function vendor_pricing_module_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('vendor_pricing', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('vendor_pricing', [
            'name'     => _l('vendor_pricing'), // The name if the item to display
            'href'     => admin_url('vendor_pricing'), // URL of the item
            'position' => 31, // The menu position
            'icon'     => 'fa fa-money', // Font awesome icon
        ]);
    }
}

function vendor_pricing_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('vendor_pricing', $capabilities, _l('vendor_pricing'));
}

hooks()->add_action('app_admin_footer', 'vendor_pricing_app_admin_footer');

function vendor_pricing_app_admin_footer()
{
    echo '<script>
    function inject_vendor_pricing_link() {
        if ($("input[name=\"link_public\"]").length > 0 && $("#vendor_pricing_link").length === 0) {
            var public_link = $("input[name=\"link_public\"]").val();
            if(public_link && public_link.indexOf("vendors_portal/pur_order/") !== -1) {
                var parts = public_link.split("vendors_portal/pur_order/");
                var id_hash = parts[1];
                var new_url = site_url + "vendor_pricing/vendor_po/view/" + id_hash;
                
                var title = "Copy Vendor Pricing Link";
                var email_title = "Send Vendor Pricing Request Email";
                
                var email_btn_html = \'<a href="\'+admin_url+\'vendor_pricing/send_email/\'+id_hash+\'" class="btn btn-info btn-with-tooltip mleft5" data-toggle="tooltip" title="\'+email_title+\'"><i class="fa fa-envelope"></i></a>\';
                
                var html = \'<div class="col-md-12 padr_div_0 mtop10" style="padding-right:0px;"><br><div class="pull-right _buttons mright5"><a href="javascript:void(0)" onclick="copy_vendor_pricing_link(); return false;" class="btn btn-warning btn-with-tooltip" data-toggle="tooltip" title="\'+title+\'"><i class="fa fa-clone"></i></a>\'+email_btn_html+\'</div><div class="pull-right col-md-6"><input type="text" id="vendor_pricing_link" readonly class="form-control" title="Vendor Pricing Link" value="\'+new_url+\'"></div><div class="clearfix"></div></div>\';
                
                $("input[name=\"link_public\"]").closest(".col-md-12.padr_div_0").after(html);
                $("[data-toggle=\"tooltip\"]").tooltip();
            }
        }
    }

    $(function(){
        inject_vendor_pricing_link();
    });

    $(document).ajaxComplete(function(event, xhr, settings) {
        inject_vendor_pricing_link();
    });

    function copy_vendor_pricing_link() {
        var copyText = document.getElementById("vendor_pricing_link");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        alert_float("success", "Vendor Pricing link copied to clipboard!");
    }
    </script>';
}
