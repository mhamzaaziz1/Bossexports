<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo form_open(admin_url('reports/avg_purchase_aging'), ['id' => 'avg-purchase-aging-form', 'method' => 'post']); ?>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="transaction_type"><?php echo _l('transaction_type'); ?></label>
                                                    <select name="transaction_type" id="transaction_type" class="selectpicker" data-width="100%">
                                                        <option value="both" <?php if (isset($selected_transaction_type) && $selected_transaction_type == 'both') { echo 'selected'; } ?>><?php echo _l('both_sales_and_purchases'); ?></option>
                                                        <option value="sales" <?php if (isset($selected_transaction_type) && $selected_transaction_type == 'sales') { echo 'selected'; } ?>><?php echo _l('sales'); ?></option>
                                                        <option value="purchases" <?php if (isset($selected_transaction_type) && $selected_transaction_type == 'purchases') { echo 'selected'; } ?>><?php echo _l('purchases'); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="report_months"><?php echo _l('period_datepicker'); ?></label>
                                                    <select class="selectpicker" name="report_months" id="report_months" data-width="100%">
                                                        <option value="" <?php if (!isset($report_months)) { echo 'selected'; } ?>><?php echo _l('report_sales_months_all_time'); ?></option>
                                                        <option value="this_month" <?php if (isset($report_months) && $report_months == 'this_month') { echo 'selected'; } ?>><?php echo _l('this_month'); ?></option>
                                                        <option value="last_month" <?php if (isset($report_months) && $report_months == 'last_month') { echo 'selected'; } ?>><?php echo _l('last_month'); ?></option>
                                                        <option value="this_year" <?php if (isset($report_months) && $report_months == 'this_year') { echo 'selected'; } ?>><?php echo _l('this_year'); ?></option>
                                                        <option value="last_year" <?php if (isset($report_months) && $report_months == 'last_year') { echo 'selected'; } ?>><?php echo _l('last_year'); ?></option>
                                                        <option value="custom" <?php if (isset($report_months) && $report_months == 'custom') { echo 'selected'; } ?>><?php echo _l('custom'); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div id="date-range" class="<?php if (!isset($report_months) || $report_months != 'custom') { echo 'hide'; } ?>">
                                                    <div class="row">
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
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <button type="submit" class="btn btn-info"><?php echo _l('generate_report'); ?></button>
                                            </div>
                                        </div>
                                        <?php echo form_close(); ?>
                                    </div>
                                </div>
                                <div class="row mtop15">
                                    <div class="col-md-12" id="report-results">
                                        <?php $this->load->view('admin/reports/avg_purchase_aging'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function() {
    // Initialize datepicker
    init_datepicker();
    
    // Show/hide date range based on report_months selection
    $('#report_months').on('change', function() {
        if ($(this).val() == 'custom') {
            $('#date-range').removeClass('hide');
        } else {
            $('#date-range').addClass('hide');
        }
    });
    
    // Handle form submission via AJAX
    $('#avg-purchase-aging-form').on('submit', function(e) {
        e.preventDefault();
        var data = $(this).serialize();
        
        $.post(admin_url + 'reports/avg_purchase_aging', data, function(response) {
            $('#report-results').html(response);
            $('html, body').animate({
                scrollTop: $("#report-results").offset().top
            }, 500);
        });
    });
});
</script>
</body>
</html>