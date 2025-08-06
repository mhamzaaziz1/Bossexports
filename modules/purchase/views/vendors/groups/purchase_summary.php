<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if(isset($client)){ ?>
<div class="row">
    <div class="col-md-12">
        <h4 class="customer-profile-group-heading"><?php echo _l('purchase_summary'); ?></h4>
        <hr class="hr-panel-heading" />
        
        <?php 
        // Get the vendor ID from the URL
        $vendor_id = $client->userid;
        
        // Load the report in an iframe
        redirect(admin_url('reports/contact_items_report/vendor/' . $vendor_id));
        ?>
        
        </div>
</div>
<?php } ?>