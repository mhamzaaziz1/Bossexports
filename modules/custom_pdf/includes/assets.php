<?php

hooks()->add_action('app_admin_head', 'custom_pdf_add_head_components');
function custom_pdf_add_head_components()
{
    custom_pdf_items_table_custom_style_render();
}

hooks()->add_action('app_admin_footer', function () {
    // Check if the 'custom_pdf' module is active
    if (get_instance()->app_modules->is_active('custom_pdf')) {
        // Generate the URL for the 'custom_pdf.js' script file
        $script_url = module_dir_url('custom_pdf', 'assets/js/custom_pdf.js');

        // Get the core version from the application's scripts
        $core_version = get_instance()->app_scripts->core_version();

        // Echo the script tag to include 'custom_pdf.js' with a version parameter
        echo '<script src="'.$script_url.'?v='.$core_version.'"></script>';
    }
});
