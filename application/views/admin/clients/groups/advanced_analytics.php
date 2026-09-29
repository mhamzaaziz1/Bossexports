<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (!empty($client) && !empty($client->userid)) : ?>
<div class="row">
    <div class="col-md-12">
        <h4 class="customer-profile-group-heading"><?php echo _l('advanced_analytics'); ?></h4>
        <hr class="hr-panel-heading" />

        <?php
            // Data you want available inside the view
            $data = [
                'client_id' => (int) $client->userid,
                'client'    => $client,
            ];

            // Load the view file (no admin_url here)
            // Make sure the file exists at: application/views/admin/reports/client_advanced_analytics.php
            $this->load->view('admin/reports/client_advanced_analytics', $data);
        ?>

    </div>
</div>
<?php else : ?>
    <p class="text-danger"><?php echo _l('client_not_found'); ?></p>
<?php endif; ?>
