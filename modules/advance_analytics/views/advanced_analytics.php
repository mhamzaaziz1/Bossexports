<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (!empty($client) && !empty($client->userid)) : ?>
<div class="row">
    <div class="col-md-12">
        <h4 class="customer-profile-group-heading"><?php echo _l('advanced_analytics'); ?></h4>
        <hr class="hr-panel-heading" />

        <?php
            // Load the model
            $CI = &get_instance();
            $CI->load->model('advance_analytics/advance_analytics_model');
            
            // Get data
            $analytics_data = $CI->advance_analytics_model->get_client_analytics_data($client->userid);
            
            if ($analytics_data) {
                // Pass data to the view
                $this->load->view('advance_analytics/client_advanced_analytics', $analytics_data);
            } else {
                echo '<p class="text-danger">' . _l('no_data_available') . '</p>';
            }
        ?>

    </div>
</div>
<?php else : ?>
    <p class="text-danger"><?php echo _l('client_not_found'); ?></p>
<?php endif; ?>
