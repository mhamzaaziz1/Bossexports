<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_open_multipart(admin_url('call_logs/import/'),['id'=>'import-staff-form']); ?>
<div class="row">
	<div class="col-md-12">
		<div class="form-group" id="error"></div>
		<div class="form-group">
			<div class="row">
				<div class="col-md-6">
					<label for="" class="control-label">Choose File</label>
					<input type="file" name="file_csv" id="file_csv" class="form-control" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
				</div>
				<div class="col-md-6">
					<label for="" class="control-lable">Role</label>
					<select name="role" class="form-control">
						<?php foreach (get_roles() as $role) {
							echo '<option value="'.$role->roleid.'">'.$role->name.'</option>';
						} ?>
					</select>
				</div>
			</div>
		</div>
		<div class="form-group">
			<button type="submit" class="btn btn-primary" data-click="import_staff">Import</button>
		</div>
		<div class="progress hidden">
			<div class="progress-bar progress-bar-striped progress-bar-warning active" role="progressbar" style="" aria-valuenow="" aria-valuemin="0" aria-valuemax="100"></div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>