<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open(admin_url('daily_sales_report/api_settings')); ?>
                        
                        <?php echo render_input('optimetriks_client_id', 'Client ID', get_option('optimetriks_client_id')); ?>
                        <?php echo render_input('optimetriks_email', 'Email', get_option('optimetriks_email'), 'email'); ?>
                        <?php echo render_input('optimetriks_password', 'Password', get_option('optimetriks_password'), 'password'); ?>

                        <?php echo render_select('optimetriks_stock_warehouse_id', $warehouses, ['warehouse_id', 'warehouse_name'], 'Stock Level Warehouse', get_option('optimetriks_stock_warehouse_id')); ?>
                        <p class="text-muted">Only this warehouse's stock is sent in the Stock Level file. Products with zero stock are skipped.</p>

                        <hr />
                        <button type="button" class="btn btn-default" onclick="testOptimetriksConnection();">Check Connection</button>
                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                        
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
function testOptimetriksConnection() {
    var clientId = $('input[name="optimetriks_client_id"]').val();
    var email = $('input[name="optimetriks_email"]').val();
    var password = $('input[name="optimetriks_password"]').val();
    
    if(!clientId || !email || !password) {
        alert_float('warning', 'Please fill in all fields before checking connection.');
        return;
    }
    
    $.post(admin_url + 'daily_sales_report/test_optimetriks_connection', {
        client_id: clientId,
        email: email,
        password: password
    }).done(function(response) {
        response = JSON.parse(response);
        if(response.success) {
            alert_float('success', response.message);
        } else {
            alert_float('danger', response.message);
        }
    }).fail(function(data) {
        alert_float('danger', 'An error occurred while testing the connection.');
    });
}
</script>
</body>
</html>
