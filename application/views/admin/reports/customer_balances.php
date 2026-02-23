<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('customer_balance_report') ? _l('customer_balance_report') : 'Customer Balance Report'; ?></h4>
                        <hr class="hr-panel-heading" />
                        <div class="row">
                            <div class="col-md-4">
                                <?php echo render_date_input('report_date', 'report_date'); ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <?php render_datatable([
                            _l('id'),
                            _l('client_company'),
                            _l('client_phonenumber'),
                            _l('customer_groups'),
                            _l('balance'),
                        ], 'customer-balances'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        var report_date = $('input[name="report_date"]');
        var serverParams = {};

        // Initial params
        serverParams['report_date'] = '[name="report_date"]';

        initDataTable('.table-customer-balances', admin_url + 'reports/customer_balances', undefined, undefined, serverParams, [4, 'desc']);

        report_date.on('change', function() {
            $('.table-customer-balances').DataTable().ajax.reload();
        });
    });
</script>
</body>
</html>
