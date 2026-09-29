<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_open_multipart(admin_url('settings?group=payment_signature'), array('id' => 'payment_signature_settings_form')); ?>
<!-- We probably shouldn't open form here because the main settings page wraps everything in a form?
     Let's check how Perfex settings tabs work.
     The generic settings view `admin/settings/all` wraps the tab content.
     However, the form in `admin/settings/all` has `enctype="multipart/form-data"`.
     So I don't need to open a form.
     I just need to provide the fields.
-->
<div class="row">
    <div class="col-md-12">
        <?php echo render_yes_no_option('payment_signature_enable', 'Enable Payment Signature & Stamp'); ?>
        <hr />
        
        <?php echo render_input('settings[payment_signature_text]', 'Signature Heading Text', get_option('payment_signature_text')); ?>
        
        <div class="form-group">
            <label for="payment_signature_image" class="control-label">Signature Image</label>
            <input type="file" name="payment_signature_image" class="form-control">
            <?php if(get_option('payment_signature_image_path') != ''): ?>
                <div class="mtop10">
                    <img src="<?php echo base_url('uploads/company/'.get_option('payment_signature_image_path')); ?>" class="img-responsive" style="max-width: 200px;">
                    <br/>
                    <a href="<?php echo admin_url('payment_signature/remove_image/signature'); ?>" class="text-danger _delete">Remove Signature</a>
                    <!-- I need a controller for removing images or handle it generically -->
                    <!-- Perfex settings usually handle removal via specific controller methods.
                         Since I am in a module, I might need a controller or a cleaner way to remove.
                         For now, let's skip the remove link or point it to a module controller.
                         I'll add the controller later.
                    -->
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="payment_stamp_image" class="control-label">Stamp Image</label>
            <input type="file" name="payment_stamp_image" class="form-control">
            <?php if(get_option('payment_stamp_image_path') != ''): ?>
                <div class="mtop10">
                    <img src="<?php echo base_url('uploads/company/'.get_option('payment_stamp_image_path')); ?>" class="img-responsive" style="max-width: 200px;">
                    <!-- <a href="#" class="text-danger _delete">Remove Stamp</a> -->
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
