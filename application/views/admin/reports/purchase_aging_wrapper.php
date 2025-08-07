<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('purchase_aging'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo form_open(admin_url('reports/purchase_aging'), ['id' => 'purchase-aging-form', 'method' => 'post']); ?>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="report_months"><?php echo _l('period_datepicker'); ?></label>
                                                    <select class="selectpicker" name="months-report" id="report_months" data-width="100%">
                                                        <option value=""><?php echo _l('report_sales_months_all_time'); ?></option>
                                                        <option value="this_month"><?php echo _l('this_month'); ?></option>
                                                        <option value="1"><?php echo _l('last_month'); ?></option>
                                                        <option value="this_year"><?php echo _l('this_year'); ?></option>
                                                        <option value="last_year"><?php echo _l('last_year'); ?></option>
                                                        <option value="3" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-2 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_three_months'); ?></option>
                                                        <option value="6" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-5 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_six_months'); ?></option>
                                                        <option value="12" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-11 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_twelve_months'); ?></option>
                                                        <option value="custom"><?php echo _l('period_datepicker_custom'); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="currency"><?php echo _l('currency'); ?></label>
                                                    <select class="selectpicker" name="currency" data-width="100%">
                                                        <?php if(isset($currencies)): foreach($currencies as $currency){ ?>
                                                            <option value="<?php echo $currency['id']; ?>" <?php if($currency['isdefault'] == 1){echo 'selected';} ?>><?php echo $currency['name']; ?></option>
                                                        <?php } endif; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div id="date-range" class="hide mbot15">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="report-from"><?php echo _l('report_sales_from_date'); ?></label>
                                                                <div class="input-group date">
                                                                    <input type="text" class="form-control datepicker" id="report-from" name="report-from">
                                                                    <div class="input-group-addon">
                                                                        <i class="fa fa-calendar calendar-icon"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="report-to"><?php echo _l('report_sales_to_date'); ?></label>
                                                                <div class="input-group date">
                                                                    <input type="text" class="form-control datepicker" id="report-to" name="report-to">
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
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="vendor_items"><?php echo _l('vendors'); ?></label>
                                                    <select name="vendor_items" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
                                                        <?php if(isset($vendors)): foreach($vendors as $vendor){ ?>
                                                            <option value="<?php echo $vendor['userid']; ?>"><?php echo $vendor['company']; ?></option>
                                                        <?php } endif; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="purchase_product_items"><?php echo _l('products'); ?></label>
                                                    <select name="purchase_product_items" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
                                                        <?php if(isset($items)): foreach($items as $item){ ?>
                                                            <option value="<?php echo $item['id']; ?>"><?php echo isset($item['commodity_code']) && $item['commodity_code'] ? $item['commodity_code'].'-'.$item['description'] : $item['description']; ?></option>
                                                        <?php } endif; ?>
                                                    </select>
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
                                        <table class="table table-purchase-aging-report scroll-responsive">
                                            <thead>
                                                <tr>
                                                    <th><?php echo _l('reports_item'); ?></th>
                                                    <th><?php echo _l('purchase_order_number'); ?></th>
                                                    <th><?php echo _l('vendor'); ?></th>
                                                    <th><?php echo _l('purchase_date'); ?></th>
                                                    <th><?php echo _l('delivery_date'); ?></th>
                                                    <th><?php echo _l('days_overdue'); ?></th>
                                                    <th><?php echo _l('aging_category'); ?></th>
                                                    <th><?php echo _l('quantity_purchased'); ?></th>
                                                    <th><?php echo _l('rate'); ?></th>
                                                    <th><?php echo _l('total_amount'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="7" class="text-right"><strong><?php echo _l('total'); ?></strong></td>
                                                    <td class="total_qty"></td>
                                                    <td></td>
                                                    <td class="total_amount"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="text-right"><strong><?php echo _l('aging_summary'); ?></strong></td>
                                                    <td class="text-right"><strong>1-30 <?php echo _l('days'); ?></strong></td>
                                                    <td colspan="2"></td>
                                                    <td class="aging_30"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td class="text-right"><strong>31-60 <?php echo _l('days'); ?></strong></td>
                                                    <td colspan="2"></td>
                                                    <td class="aging_60"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td class="text-right"><strong>61-90 <?php echo _l('days'); ?></strong></td>
                                                    <td colspan="2"></td>
                                                    <td class="aging_90"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td class="text-right"><strong>91-120 <?php echo _l('days'); ?></strong></td>
                                                    <td colspan="2"></td>
                                                    <td class="aging_120"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td class="text-right"><strong>120+ <?php echo _l('days'); ?></strong></td>
                                                    <td colspan="2"></td>
                                                    <td class="aging_older"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
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
    $('select[name="months-report"]').on('change', function() {
        var value = $(this).val();
        if (value == 'custom') {
            $('#date-range').removeClass('hide');
        } else {
            $('#date-range').addClass('hide');
        }
    });
    
    // Purchase Aging Report function
    function purchase_aging_report() {
        if ($.fn.DataTable.isDataTable('.table-purchase-aging-report')) {
            $('.table-purchase-aging-report').DataTable().destroy();
        }
        
        var params = {};
        var report_from = $('input[name="report-from"]').val();
        var report_to = $('input[name="report-to"]').val();
        var report_currency = $('select[name="currency"]').val();
        var report_months = $('select[name="months-report"]').val();
        var vendor_items = $('select[name="vendor_items"]').val();
        var purchase_product_items = $('select[name="purchase_product_items"]').val();
        
        if (report_from) {
            params.report_from = report_from;
        }
        
        if (report_to) {
            params.report_to = report_to;
        }
        
        if (report_currency) {
            params.report_currency = report_currency;
        }
        
        if (report_months) {
            params.report_months = report_months;
        }
        
        if (vendor_items) {
            params.vendor_items = vendor_items;
        }
        
        if (purchase_product_items) {
            params.purchase_product_items = purchase_product_items;
        }
        
        var table = $('.table-purchase-aging-report').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": admin_url + 'reports/purchase_aging',
                "type": "POST",
                "data": params
            },
            "columns": [
                { "data": 0 }, // Item
                { "data": 1 }, // Purchase Order
                { "data": 2 }, // Vendor
                { "data": 3 }, // Purchase Date
                { "data": 4 }, // Delivery Date
                { "data": 5 }, // Days Overdue
                { "data": 6 }, // Aging Category
                { "data": 7 }, // Quantity
                { "data": 8 }, // Rate
                { "data": 9 }  // Total Amount
            ],
            "order": [
                [6, 'desc'] // Sort by aging category by default
            ],
            "fnDrawCallback": function(oSettings) {
                var sums = oSettings.json.sums;
                if (sums) {
                    $('.table-purchase-aging-report tfoot .total_qty').html(sums.total_qty);
                    $('.table-purchase-aging-report tfoot .total_amount').html(sums.total_amount);
                    $('.table-purchase-aging-report tfoot .aging_30').html(sums.aging_30);
                    $('.table-purchase-aging-report tfoot .aging_60').html(sums.aging_60);
                    $('.table-purchase-aging-report tfoot .aging_90').html(sums.aging_90);
                    $('.table-purchase-aging-report tfoot .aging_120').html(sums.aging_120);
                    $('.table-purchase-aging-report tfoot .aging_older').html(sums.aging_older);
                }
            }
        });
        
        return table;
    }
    
    // Handle form submission
    $('#purchase-aging-form').on('submit', function(e) {
        e.preventDefault();
        purchase_aging_report();
    });
    
    // Initialize the report on page load
    purchase_aging_report();
});
</script>