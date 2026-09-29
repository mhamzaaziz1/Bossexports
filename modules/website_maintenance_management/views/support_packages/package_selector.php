<?php
defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- Support Hour Package Selection -->
<div id="package-section" style="display:none;">
    <hr/>
    <h4 class="tw-flex tw-items-center tw-gap-2">
        <i class="fa fa-box"></i>
		<?php
		echo _l('wmm_support_packages'); ?>
        <span class="label label-info" id="package-balance-label" style="display:none;">
            <span id="package-balance-hours">0</span> <?php
			echo _l('wmm_hours_remaining'); ?>
        </span>
    </h4>
    <p class="text-muted"><?php
		echo _l('wmm_deduct_from_package'); ?></p>

    <div id="package-loading" style="display:none;">
        <p class="text-muted">
            <i class="fa fa-spinner fa-spin"></i> <?php
			echo _l('loading'); ?>...
        </p>
    </div>

    <div id="package-content">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group" id="package-select-group">
                    <label for="package_id"><?php
						echo _l('wmm_select_package'); ?></label>
                    <select name="package_id" id="package_id" class="selectpicker form-control" data-width="100%" data-live-search="true">
                        <option value=""><?php
							echo _l('dropdown_non_selected_tex'); ?></option>
                    </select>
                    <small class="text-muted" id="package-info" style="display:none;"></small>
                </div>

                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="deduct_from_package" id="deduct_from_package" value="1">
                    <label for="deduct_from_package">
						<?php
						echo _l('wmm_deduct_hours'); ?>
                    </label>
                </div>

                <div class="alert alert-warning" id="package-warning" style="display:none;">
                    <i class="fa fa-exclamation-triangle"></i>
                    <span id="package-warning-text"></span>
                </div>

                <div class="alert alert-info" id="no-packages-alert" style="display:none;">
                    <i class="fa fa-info-circle"></i>
					<?php
					echo _l('wmm_no_active_packages'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

</script>
