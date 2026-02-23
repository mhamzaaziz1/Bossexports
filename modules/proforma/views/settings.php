<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('settings[proforma_settings_active_tab]','proforma_settings'); ?>
<div class="row">
    <div class="col-md-12">
        <h4><?php echo _l('proforma_invoice_settings'); ?></h4>
        <hr />
        <div class="form-group">
            <label for="proforma_number_prefix"><?php echo _l('proforma_invoice_prefix'); ?></label>
            <input type="text" name="settings[proforma_number_prefix]" id="proforma_number_prefix" class="form-control" value="<?php echo get_option('proforma_number_prefix'); ?>">
        </div>
        <div class="form-group">
            <label for="next_proforma_invoice_number" class="control-label">
                <?php echo _l('next_proforma_invoice_number'); ?>
            </label>
            <input type="number" name="settings[next_proforma_invoice_number]" id="next_proforma_invoice_number" class="form-control" value="<?php echo get_option('next_proforma_invoice_number'); ?>">
        </div>
        <hr />
        <?php echo render_yes_no_option('show_proforma_signature', 'show_pdf_signature_proforma'); ?>
    </div>
</div>
