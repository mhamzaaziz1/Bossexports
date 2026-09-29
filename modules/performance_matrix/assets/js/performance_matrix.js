$(function() {
    // Customer Performance Modal
    $(body).on('click', '.view-customer-matrix-stats', function(e) {
        e.preventDefault();
        var clientid = $(this).data('clientid');
        if (!clientid) return;

        // Create modal if it doesn't exist
        if ($('#customer_matrix_modal').length === 0) {
            $('body').append('<div class="modal fade" id="customer_matrix_modal" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">Customer Performance Matrix</h4></div><div class="modal-body" id="customer_matrix_content"></div></div></div></div>');
        }

        $('#customer_matrix_content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
        $('#customer_matrix_modal').modal('show');

        $.post(admin_url + 'performance_matrix/customer', {
            client_id: clientid
        }).done(function(response) {
            $('#customer_matrix_content').html(response);
            init_selectpicker();
        });
    });

    // Injects 'View Stats' button in Invoice/Estimate forms
    $('body').on('change', '#clientid, #rel_id', function() {
        var client_id = $(this).val();
        var rel_type = $('#rel_type').val() || 'customer';
        if (rel_type === 'customer' && client_id) {
            $('.customer-matrix-btn').remove();
            var btn = '<a href="#" class="customer-matrix-btn pull-right text-info small" style="margin-top:2px;" data-clientid="' + client_id + '"><i class="fa fa-bar-chart"></i> Matrix Stats</a>';
            var label = $(this).closest('.form-group').find('label.control-label').first();
            if (label.length > 0) label.append(btn);
        } else {
             $('.customer-matrix-btn').remove();
        }
    });

    $(body).on('click', '.customer-matrix-btn', function(e) {
        e.preventDefault();
        var clientid = $(this).data('clientid');
        $('.view-customer-matrix-stats[data-clientid="' + clientid + '"]').first().click() || 
        $(body).append('<a href="#" class="view-customer-matrix-stats hidden" data-clientid="' + clientid + '"></a>').find('.view-customer-matrix-stats').last().click();
    });

    // Item Price History in Customer Forms
    $(body).on('change', 'select[name="item_select"]', function() {
        var item_id = $(this).val();
        var client_id = $('#clientid').val() || ($('#rel_type').val() === 'customer' ? $('#rel_id').val() : '');
        
        if (item_id && client_id) {
            $('.item-matrix-prices').remove();
            var target = $('input[name="rate"]').closest('.form-group');
            if (target.length === 0) target = $('input[name="rate"]').closest('td');
            
            target.append('<div class="item-matrix-prices mtop10 text-muted small"><i class="fa fa-spinner fa-spin"></i> Checking history...</div>');
            
            $.post(admin_url + 'performance_matrix/get_customer_item_prices', {
                item_id: item_id,
                client_id: client_id
            }).done(function(response) {
                $('.item-matrix-prices').remove();
                target.append('<div class="item-matrix-prices mtop10">' + response + '</div>');
            });
        }
    });

    // Vendor Performance Modal
    $(body).on('click', '.view-vendor-matrix-stats', function(e) {
        e.preventDefault();
        var vendorid = $(this).data('vendorid');
        if (!vendorid) return;

        if ($('#vendor_matrix_modal').length === 0) {
            $('body').append('<div class="modal fade" id="vendor_matrix_modal" tabindex="-1" role="dialog"><div class="modal-dialog modal-xl" role="document" style="width:90%"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">Vendor Performance Matrix</h4></div><div class="modal-body" id="vendor_matrix_content"></div></div></div></div>');
        }

        $('#vendor_matrix_content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
        $('#vendor_matrix_modal').modal('show');

        load_vendor_stats(vendorid, 'all_time');
    });

    // Item Performance Modal
    $(body).on('click', '.view-item-matrix-stats', function(e) {
        e.preventDefault();
        var itemid = $(this).data('itemid');
        if (!itemid) return;

        if ($('#item_matrix_modal').length === 0) {
            $('body').append('<div class="modal fade" id="item_matrix_modal" tabindex="-1" role="dialog"><div class="modal-dialog modal-xl" role="document" style="width:90%"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">Item Performance Matrix</h4></div><div class="modal-body" id="item_matrix_content"></div></div></div></div>');
        }

        $('#item_matrix_content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
        $('#item_matrix_modal').modal('show');

        $.post(admin_url + 'performance_matrix/item', {
            item_id: itemid
        }).done(function(response) {
            $('#item_matrix_content').html(response);
        });
    });
});

function load_vendor_stats(vendorid, period) {
    $.post(admin_url + 'performance_matrix/vendor', {
        vendor_id: vendorid,
        period: period
    }).done(function(response) {
        $('#vendor_matrix_content').html(response);
        init_selectpicker();
    });
}

