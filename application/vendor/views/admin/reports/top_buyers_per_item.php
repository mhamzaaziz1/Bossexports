<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('top_buyers_per_item'); ?></h4>
                        <hr class="hr-panel-heading" />

                        <?php echo form_open(admin_url('reports/top_buyers_per_item'), ['method' => 'post', 'id' => 'top-buyers-per-item-form']); ?>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="item_id"><?php echo _l('item'); ?></label>
                                    <select name="item_id" id="item_id" class="form-control selectpicker" data-live-search="true" required>
                                        <option value=""><?php echo _l('select_item'); ?></option>
                                        <?php foreach($items as $item): ?>
                                            <option value="<?php echo $item['id']; ?>" <?php if(isset($selected_item) && $selected_item == $item['id']){echo 'selected';} ?>><?php echo $item['description']; ?> (<?php echo $item['long_description']; ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="transaction_type"><?php echo _l('transaction_type'); ?></label>
                                    <select name="transaction_type" id="transaction_type" class="form-control selectpicker">
                                        <option value="both" <?php if(isset($selected_transaction_type) && $selected_transaction_type == 'both'){echo 'selected';} ?>><?php echo _l('both_sales_and_purchases'); ?></option>
                                        <option value="sales" <?php if(isset($selected_transaction_type) && $selected_transaction_type == 'sales'){echo 'selected';} ?>><?php echo _l('sales'); ?></option>
                                        <option value="purchases" <?php if(isset($selected_transaction_type) && $selected_transaction_type == 'purchases'){echo 'selected';} ?>><?php echo _l('purchases'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ranking"><?php echo _l('ranking'); ?></label>
                                    <select name="ranking" id="ranking" class="form-control selectpicker">
                                        <option value="top" <?php if(isset($selected_ranking) && $selected_ranking == 'top'){echo 'selected';} ?>><?php echo _l('top_buyers'); ?></option>
                                        <option value="least" <?php if(isset($selected_ranking) && $selected_ranking == 'least'){echo 'selected';} ?>><?php echo _l('least_buyers'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="limit"><?php echo _l('limit'); ?></label>
                                    <input type="number" name="limit" id="limit" class="form-control" min="1" max="100" value="<?php echo isset($selected_limit) ? $selected_limit : 10; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="metric"><?php echo _l('metric'); ?></label>
                                    <select name="metric" id="metric" class="form-control selectpicker">
                                        <option value="quantity" <?php if(isset($selected_metric) && $selected_metric == 'quantity'){echo 'selected';} ?>><?php echo _l('quantity'); ?></option>
                                        <option value="amount" <?php if(isset($selected_metric) && $selected_metric == 'amount'){echo 'selected';} ?>><?php echo _l('amount'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="report_months"><?php echo _l('period_datepicker'); ?></label>
                                    <select name="report_months" id="report_months" class="form-control selectpicker">
                                        <option value="" <?php if(isset($report_months) && $report_months == ''){echo 'selected';} ?>><?php echo _l('report_sales_months_all_time'); ?></option>
                                        <option value="this_month" <?php if(isset($report_months) && $report_months == 'this_month'){echo 'selected';} ?>><?php echo _l('this_month'); ?></option>
                                        <option value="1" <?php if(isset($report_months) && $report_months == '1'){echo 'selected';} ?>><?php echo _l('last_month'); ?></option>
                                        <option value="this_year" <?php if(isset($report_months) && $report_months == 'this_year'){echo 'selected';} ?>><?php echo _l('this_year'); ?></option>
                                        <option value="last_year" <?php if(isset($report_months) && $report_months == 'last_year'){echo 'selected';} ?>><?php echo _l('last_year'); ?></option>
                                        <option value="3" <?php if(isset($report_months) && $report_months == '3'){echo 'selected';} ?> data-subtext="<?php echo _d(date('Y-m-01', strtotime("-2 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_three_months'); ?></option>
                                        <option value="6" <?php if(isset($report_months) && $report_months == '6'){echo 'selected';} ?> data-subtext="<?php echo _d(date('Y-m-01', strtotime("-5 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_six_months'); ?></option>
                                        <option value="12" <?php if(isset($report_months) && $report_months == '12'){echo 'selected';} ?> data-subtext="<?php echo _d(date('Y-m-01', strtotime("-11 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_twelve_months'); ?></option>
                                        <option value="custom" <?php if(isset($report_months) && $report_months == 'custom'){echo 'selected';} ?>><?php echo _l('period_datepicker'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mtop25">
                                    <button type="submit" class="btn btn-info"><?php echo _l('generate_report'); ?></button>
                                </div>
                            </div>
                        </div>
                        <div class="row date-range" <?php if(!isset($report_months) || $report_months != 'custom'){echo 'style="display:none;"';} ?>>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_from"><?php echo _l('report_sales_from_date'); ?></label>
                                    <div class="input-group date">
                                        <input type="text" class="form-control datepicker" id="report_from" name="report_from" value="<?php echo isset($report_from) ? $report_from : ''; ?>">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar calendar-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_to"><?php echo _l('report_sales_to_date'); ?></label>
                                    <div class="input-group date">
                                        <input type="text" class="form-control datepicker" id="report_to" name="report_to" value="<?php echo isset($report_to) ? $report_to : ''; ?>">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar calendar-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row date-range" <?php if(!isset($report_months) || $report_months != 'custom'){echo 'style="display:none;"';} ?>>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_from"><?php echo _l('report_sales_from_date'); ?></label>
                                    <div class="input-group date">
                                        <input type="text" class="form-control datepicker" id="report_from" name="report_from" value="<?php echo isset($report_from) ? $report_from : ''; ?>">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar calendar-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_to"><?php echo _l('report_sales_to_date'); ?></label>
                                    <div class="input-group date">
                                        <input type="text" class="form-control datepicker" id="report_to" name="report_to" value="<?php echo isset($report_to) ? $report_to : ''; ?>">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar calendar-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php echo form_close(); ?>

                        <hr class="hr-panel-heading" />

                        <?php if(isset($report_data) && !empty($report_data)): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('contact_name'); ?></th>
                                                <th><?php echo _l('type'); ?></th>
                                                <th><?php echo _l('total_quantity'); ?></th>
                                                <th><?php echo _l('total_amount'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($report_data as $row): ?>
                                            <tr>
                                                <td>
                                                    <?php if($row['type'] == 'customer'): ?>
                                                        <a href="<?php echo admin_url('clients/client/'.$row['contact_id']); ?>" target="_blank"><?php echo $row['name']; ?></a>
                                                    <?php else: ?>
                                                        <a href="<?php echo admin_url('purchase/vendor/'.$row['contact_id']); ?>" target="_blank"><?php echo $row['name']; ?></a>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo _l($row['type']); ?></td>
                                                <td><?php echo $row['total_quantity']; ?></td>
                                                <td><?php echo app_format_money($row['total_amount'], get_base_currency()->name); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                            <?php if(isset($report_data)): ?>
                                <p class="no-margin"><?php echo _l('no_data_found'); ?></p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(function() {
    // Initialize form validation
    var validationRules = {
        item_id: 'required',
        limit: 'required',
        transaction_type: 'required',
        ranking: 'required',
        metric: 'required'
    };

    // Show/hide date range inputs based on report_months selection
    $('#report_months').on('change', function() {
        if ($(this).val() === 'custom') {
            $('.date-range').show();
            // Add validation for custom date range
            validationRules.report_from = 'required';
            validationRules.report_to = 'required';
        } else {
            $('.date-range').hide();
            // Remove validation for custom date range
            delete validationRules.report_from;
            delete validationRules.report_to;
        }

        // Reinitialize form validation with updated rules
        appValidateForm($('#top-buyers-per-item-form'), validationRules);
    });

    // Initial form validation
    appValidateForm($('#top-buyers-per-item-form'), validationRules);

    // Trigger change event to set initial state
    $('#report_months').trigger('change');
});
</script>
