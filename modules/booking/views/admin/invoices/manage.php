<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<?php
			include_once(APPPATH.'views/admin/invoices/filter_params.php');
			$this->load->view('admin/invoices/list_template');
			?>
		</div>
	</div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>var hidden_columns = [2,6,7,8];</script>
<?php init_tail(); ?>
<script>
	load_small_table_item('', '#invoice', 'invoiceid', 'booking/invoices/get_invoice_data_ajax', '.table-invoices');
	table_invoices = $('table.table-invoices');
    table_estimates = $('table.table-estimates');

    if (table_invoices.length > 0 || table_estimates.length > 0) {

        // Invoices additional server params
        var Invoices_Estimates_ServerParams = {};
        var Invoices_Estimates_Filter = $('._hidden_inputs._filters input');

        $.each(Invoices_Estimates_Filter, function () {
            Invoices_Estimates_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
        });

        if (table_invoices.length) {
            // Invoices tables
            initDataTable(table_invoices, (admin_url + 'booking/invoices/table' + ($('body').hasClass('recurring') ? '?recurring=1' : '')), 'undefined', 'undefined', Invoices_Estimates_ServerParams, !$('body').hasClass('recurring') ? [
                [3, 'desc'],
                [0, 'desc']
            ] : [table_invoices.find('th.next-recurring-date').index(), 'asc']);
        }

        if (table_estimates.length) {
            // Estimates table
            initDataTable(table_estimates, admin_url + 'estimates/table', 'undefined', 'undefined', Invoices_Estimates_ServerParams, [
                [3, 'desc'],
                [0, 'desc']
            ]);
        }
    }
</script>
</body>
</html>
