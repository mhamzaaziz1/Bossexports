<div class="form-group inc_acc">
    <label class="control-label" for="parent_id"><small class="req text-danger">* </small><?php echo _l('income_account'); ?></label>
    <?php echo form_dropdown('xero_incomeAccountRef', $xero_income_accounts, $selected, array('id' => 'xero_incomeAccountRef', 'class' => 'form-control selectpicker', 'data-live-search' => 'true', 'data-width' => '100%', 'required' => 'required')); ?>
</div>