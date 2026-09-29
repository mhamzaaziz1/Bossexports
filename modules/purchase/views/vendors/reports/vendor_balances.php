<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo 'Vendor Balance Report'; ?></h4>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php render_datatable([
                            _l('id'),
                            _l('client_company'),
                            _l('client_phonenumber'),
                            _l('balance'),
                        ], 'vendor-balances'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-vendor-balances', admin_url + 'purchase/vendor_balances', undefined, undefined, 'undefined', [3, 'desc']);
    });
</script>
</body>
</html>
