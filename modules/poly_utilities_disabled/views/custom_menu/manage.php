<?php defined('BASEPATH') or exit('No direct script access allowed');

init_head();

?>
<div id="wrapper">
    <div class="content">
        <div class="row poly_utilities_settings">
            <div class="col-md-12">
                <div class="tw-mb-2 sm:tw-mb-4">
                    <div class="panel_s">
                        <div class="panel-body">
<pre>The features are being integrated in version 1.0.8
- Include custom link in primary or client-specific menus.
- Display client-exclusive link upon authentication.
- Target link visibility to individual clients.
- Attach badges to administrative menu options.
- Embed internal/external links in menus.
- Launch links in new tabs/iFrames.
- Restrict custom link visibility by role.
- Limit custom link access to designated staff.
- Control module access through staff permissions.</pre>
                            <?php
                            if (has_permission('poly_utilities_shortcut_menu_extend', '', 'create')) {
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<?php
echo '<script src="' . base_url('modules/poly_utilities/assets/js/admin/custom_menu.js') . '"></script>';
?>