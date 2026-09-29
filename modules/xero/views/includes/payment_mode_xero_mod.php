<div class="inc_acc">
	<div class="form-group">
	    <label class="control-label" for="parent_id"><small class="req text-danger">* </small><?php echo _l('xero_hasSortCode'); ?></label>
	     <select id="xero_hasSortCode" name="xero_hasSortCode" class="selectpicker form-control">
	     	<option value="0" <?php if (!$selected) {
	echo 'selected';
}
?> >No</option>
	     	<option value="1" <?php if ($selected) {
	echo 'selected';
}
?> >Yes</option>
	     </select>
	</div>
    <div class="form-group" app-field-wrapper="xero_accountNumber" id="herez">
    	<label for="xero_accountNumber" class="control-label no_sort">
    		<small class="req text-danger">* </small><?php echo _l('bank_acc_no'); ?>
    	</label>

    	<label for="xero_accountNumber" class="control-label hide has_sort">
    		<i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('bank_acc_no_with_sort_help'); ?>"></i>
    		<small class="req text-danger">* </small><?php echo _l('bank_acc_no_with_sort'); ?>
    	</label>

    	<input type="text" id="xero_accountNumber" name="xero_accountNumber" class="form-control" value="<?php echo $accountNumber; ?>" aria-invalid="false">
    </div>
</div>