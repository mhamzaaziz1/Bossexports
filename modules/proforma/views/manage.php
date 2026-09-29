<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12" id="small-table">
                <div class="panel_s">
                    <div class="panel-body">
                         <div class="_buttons">
                            <a href="<?php echo admin_url('proforma/invoice'); ?>" class="btn btn-info pull-left display-block">
                                <?php echo _l('new_proforma_invoice'); ?>
                            </a>
                            <a href="#" class="btn btn-default btn-with-tooltip toggle-small-view hidden-xs pull-right" onclick="toggle_small_view('.table-proformainvoices','#proforma_invoice'); return false;" data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i class="fa fa-angle-double-left"></i></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        
                        <!-- Simple Table for now, typically DataTables -->
                        <table class="table dt-table table-proformainvoices" data-order-col="1" data-order-type="desc">
                            <thead>
                                <tr>
                                    <th><?php echo _l('proforma_invoice_number'); ?></th>
                                    <th><?php echo _l('date'); ?></th>
                                    <th><?php echo _l('client'); ?></th>
                                    <th><?php echo _l('tags'); ?></th>
                                    <th><?php echo _l('duedate'); ?></th>
                                    <th><?php echo _l('status'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $CI = &get_instance();
                                $CI->load->model('proforma_model');
                                $proformas = $CI->proforma_model->get();
                                foreach($proformas as $p) { ?>
                                <tr class="has-row-options" data-proforma-id="<?php echo $p['id']; ?>" style="cursor: pointer;">
                                    <td><a href="#" onclick="init_proforma(<?php echo $p['id']; ?>); return false;"><?php echo format_proforma_number($p['id']); ?></a></td>
                                    <td><?php echo _d($p['date']); ?></td>
                                    <td><?php echo $p['clientid']; // Need to fetch name ?></td>
                                    <td></td>
                                    <td><?php echo _d($p['duedate']); ?></td>
                                    <td><?php echo format_invoice_status($p['status']); ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-7 small-table-right-col">
                <div id="proforma_invoice" class="hide">
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    var hidden_columns = [2,3,4,5];
    $(function() {
        $('body').on('click', '.table-proformainvoices tbody tr', function(e) {
            // Do not trigger if clicking on an A tag or a button or input inside the td
            if ($(e.target).closest('a').length || $(e.target).closest('button').length || $(e.target).closest('input').length) {
                return;
            }
            var id = $(this).data('proforma-id');
            if (id) {
                init_proforma(id);
            }
        });
    });

    function init_proforma(id) {
        var _invoiceid = id;
        var _url = admin_url + 'proforma/get_proforma_data_ajax/' + _invoiceid;
        
        // This toggles the view if it's currently full width
        if ($('#small-table').hasClass('col-md-12')) {
            toggle_small_view('.table-proformainvoices', '#proforma_invoice');
        }
        
        // Load the content
        $('#proforma_invoice').html('');
        $.get(_url, function(response) {
            $('#proforma_invoice').html(response);
        });
    }

    function proforma_email_send(id) {
        var requestUrl = admin_url + 'proforma/send_mail_modal/' + id;
        requestGet(requestUrl).done(function(response) {
            $('#proforma_send_to_client_modal').remove();
            $('body').append(response);
            $('#proforma_send_to_client_modal').modal('show');
            init_editor('.tinymce-'+id);
            init_selectpicker();
        });
    }
</script>
