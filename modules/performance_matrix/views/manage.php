<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin font-bold uppercase"><i class="fa fa-bar-chart"></i> <?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        
                        <div class="horizontal-scrollable-tabs">
                          <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                            <li role="presentation" class="<?php echo ($tab == 'item' ? 'active' : ''); ?>">
                              <a href="#item_matrix" aria-controls="item_matrix" role="tab" data-toggle="tab">Item Performance Matrix</a>
                            </li>
                            <li role="presentation" class="<?php echo ($tab == 'customer' ? 'active' : ''); ?>">
                              <a href="#customer_matrix" aria-controls="customer_matrix" role="tab" data-toggle="tab">Customer Performance Matrix</a>
                            </li>
                            <li role="presentation" class="<?php echo ($tab == 'vendor' ? 'active' : ''); ?>">
                              <a href="#vendor_matrix" aria-controls="vendor_matrix" role="tab" data-toggle="tab">Vendor Performance Matrix</a>
                            </li>
                          </ul>
                        </div>

                        <div class="tab-content mtop20">
                          <!-- Item Matrix -->
                          <div role="tabpanel" class="tab-pane <?php echo ($tab == 'item' ? 'active' : ''); ?>" id="item_matrix">
                            <div class="row">
                                <div class="col-md-4">
                                    <?php echo render_select('item_id', $items ?? [], ['id', 'description'], 'Select Item to View Performance', '', ['data-live-search' => 'true', 'data-none-selected-text' => 'Choose an Item...'], [], ''); ?>
                                </div>
                                <div class="col-md-12" id="item_matrix_dynamic_content">
                                    <div class="alert alert-info">Please select an item above to view its performance matrix.</div>
                                </div>
                            </div>
                          </div>

                          <!-- Customer Matrix -->
                          <div role="tabpanel" class="tab-pane <?php echo ($tab == 'customer' ? 'active' : ''); ?>" id="customer_matrix">
                            <div class="row">
                                <div class="col-md-4">
                                    <?php echo render_select('client_id_matrix', $customers ?? [], ['userid', 'company'], 'Select Customer to View Performance', '', ['data-live-search' => 'true', 'data-none-selected-text' => 'Choose a Customer...'], [], ''); ?>
                                </div>
                                <div class="col-md-12" id="customer_matrix_dynamic_content">
                                    <div class="alert alert-info">Please select a customer above to view their performance matrix.</div>
                                </div>
                            </div>
                          </div>

                          <!-- Vendor Matrix -->
                          <div role="tabpanel" class="tab-pane <?php echo ($tab == 'vendor' ? 'active' : ''); ?>" id="vendor_matrix">
                            <div class="row">
                                <div class="col-md-4">
                                    <?php echo render_select('vendor_id_matrix', $vendors ?? [], ['userid', 'company'], 'Select Vendor to View Performance', '', ['data-live-search' => 'true', 'data-none-selected-text' => 'Choose a Vendor...'], [], ''); ?>
                                </div>
                                <div class="col-md-12" id="vendor_matrix_dynamic_content">
                                    <div class="alert alert-info">Please select a vendor above to view their performance matrix.</div>
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
    // Dynamic loading in manage page
    $('#item_id').on('change', function() {
        var id = $(this).val();
        if (id) {
            $('#item_matrix_dynamic_content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
            $.post(admin_url + 'performance_matrix/item', { item_id: id }).done(function(response) {
                $('#item_matrix_dynamic_content').html(response);
            });
        }
    });

    $('#client_id_matrix').on('change', function() {
        var id = $(this).val();
        if (id) {
            $('#customer_matrix_dynamic_content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
            $.post(admin_url + 'performance_matrix/customer', { client_id: id }).done(function(response) {
                $('#customer_matrix_dynamic_content').html(response);
            });
        }
    });

    $('#vendor_id_matrix').on('change', function() {
        var id = $(this).val();
        if (id) {
            $('#vendor_matrix_dynamic_content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
            $.post(admin_url + 'performance_matrix/vendor', { vendor_id: id }).done(function(response) {
                $('#vendor_matrix_dynamic_content').html(response);
            });
        }
    });
});

// For vendor Period Select in this page
function load_vendor_stats(vendorid, period) {
     if ($('#vendor_matrix_dynamic_content').length > 0) {
        $.post(admin_url + 'performance_matrix/vendor', { vendor_id: vendorid, period: period }).done(function(response) {
            $('#vendor_matrix_dynamic_content').html(response);
            init_selectpicker();
        });
     } else {
         // Handle modal fallback
         $('#vendor_matrix_content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
         $.post(admin_url + 'performance_matrix/vendor', { vendor_id: vendorid, period: period }).done(function(response) {
            $('#vendor_matrix_content').html(response);
            init_selectpicker();
        });
     }
}
</script>
