<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if(isset($client)){ ?>
<div class="row">
    <div class="col-md-12">
        <h4 class="customer-profile-group-heading"><?php echo _l('advanced_analytics'); ?></h4>
        <hr class="hr-panel-heading" />

        <?php 
        // Get the client ID from the URL
        $client_id = $client->userid;

        // Load the report in an iframe
        redirect(admin_url('reports/client_advanced_analytics/' . $client_id));
        ?>

    </div>
</div>
<?php } ?>