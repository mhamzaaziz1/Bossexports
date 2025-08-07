<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(function() {
        // Initialize the sales aging report when the page loads
        if (typeof(init_report) !== 'undefined') {
            // Add sales aging report to the init_report function
            var originalInitReport = init_report;
            init_report = function(e, type) {
                if (type == 'sales-aging-report') {
                    // Hide all reports
                    $('div[id$="-report"]').addClass('hide');
                    // Show the sales aging report
                    $('#sales-aging-report').removeClass('hide');
                    // Initialize the report
                    sales_aging_report();
                    // Set the active link
                    $('a[onclick*="init_report"]').removeClass('active');
                    $(e).addClass('active');
                    return;
                }
                // Call the original init_report function for other report types
                originalInitReport(e, type);
            };
        }

        // Add sales aging report to the gen_reports function
        if (typeof(gen_reports) !== 'undefined') {
            var originalGenReports = gen_reports;
            gen_reports = function() {
                if (!$('#sales-aging-report').hasClass('hide')) {
                    sales_aging_report();
                    return;
                }
                // Call the original gen_reports function for other report types
                originalGenReports();
            };
        }

        // Sales Aging Report function
        function sales_aging_report() {
            if ($.fn.DataTable.isDataTable('.table-sales-aging-report')) {
                $('.table-sales-aging-report').DataTable().destroy();
            }
            
            var params = {};
            var report_from = $('input[name="report-from"]').val();
            var report_to = $('input[name="report-to"]').val();
            var report_currency = $('select[name="currency"]').val();
            var report_months = $('select[name="months-report"]').val();
            var sale_agent_items = $('select[name="sale_agent_items"]').val();
            var sale_product_items = $('select[name="sale_product_items"]').val();
            
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
            
            if (sale_agent_items) {
                params.sale_agent_items = sale_agent_items;
            }
            
            if (sale_product_items) {
                params.sale_product_items = sale_product_items;
            }
            
            var table = $('.table-sales-aging-report').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": admin_url + 'reports/sales_aging',
                    "type": "POST",
                    "data": params
                },
                "columns": [
                    { "data": 0 }, // Item
                    { "data": 1 }, // Invoice
                    { "data": 2 }, // Customer
                    { "data": 3 }, // Invoice Date
                    { "data": 4 }, // Due Date
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
                        $('.table-sales-aging-report tfoot .total_qty').html(sums.total_qty);
                        $('.table-sales-aging-report tfoot .total_amount').html(sums.total_amount);
                        $('.table-sales-aging-report tfoot .aging_30').html(sums.aging_30);
                        $('.table-sales-aging-report tfoot .aging_60').html(sums.aging_60);
                        $('.table-sales-aging-report tfoot .aging_90').html(sums.aging_90);
                        $('.table-sales-aging-report tfoot .aging_120').html(sums.aging_120);
                        $('.table-sales-aging-report tfoot .aging_older').html(sums.aging_older);
                    }
                }
            });
            
            return table;
        }
    });
</script>