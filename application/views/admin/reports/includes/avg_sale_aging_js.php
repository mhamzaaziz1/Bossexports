<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    // Variable to reference the report element
    var report_avg_sale_aging = $('#avg-sale-aging-report');
    
    // Function to initialize the AVG Sale aging report
    function avg_sale_aging_report() {
        if ($.fn.DataTable.isDataTable('.table-avg-sale-aging-report')) {
            $('.table-avg-sale-aging-report').DataTable().destroy();
        }
        initDataTable('.table-avg-sale-aging-report', admin_url + 'reports/avg_sale_aging', false, false, fnServerParams, [0, 'asc']);
    }
    
    // Add event handler for the table
    $('.table-avg-sale-aging-report').on('draw.dt', function() {
        var avgSaleAgingTable = $(this).DataTable();
        var sums = avgSaleAgingTable.ajax.json().sums;
        $(this).find('tfoot').addClass('bold');
        $(this).find('tfoot td.total_amount').html(sums.total_amount);
        $(this).find('tfoot td.total_qty').html(sums.total_qty);
        $(this).find('tfoot td.avg_days').html(sums.avg_days);
        $(this).find('tfoot td.total_items').html(sums.total_items);
    });
    
    // Extend the init_report function to handle the AVG Sale aging report
    var originalInitReport = init_report;
    init_report = function(e, type) {
        // Hide the AVG Sale aging report
        report_avg_sale_aging.addClass('hide');
        
        // Call the original init_report function
        originalInitReport(e, type);
        
        // If the type is 'avg-sale-aging-report', show it
        if (type == 'avg-sale-aging-report') {
            report_avg_sale_aging.removeClass('hide');
        }
    };
    
    // Extend the gen_reports function to handle the AVG Sale aging report
    var originalGenReports = gen_reports;
    gen_reports = function() {
        // Call the original gen_reports function
        originalGenReports();
        
        // If the AVG Sale aging report is visible, initialize it
        if (!report_avg_sale_aging.hasClass('hide')) {
            avg_sale_aging_report();
        }
    };
</script>