"use strict";

var shipment_id = $('input[name="shipment_id"]').val();

$(function () {
    // Item Search
    init_ajax_search('items', '#item_select', admin_url + 'shipments/search_items');

    // Purchase Order Search - Standard Dropdown
    $('#add_from_po_modal').on('show.bs.modal', function () {
        var $select = $('#po_select');

        // Only load if empty (or force reload if you prefer)
        if ($select.children('option').length <= 1) {
            $select.html('<option value="">Loading...</option>');

            $.get(admin_url + 'shipments/get_purchase_orders', function (response) {
                var data = JSON.parse(response);
                var options = '<option value=""></option>';

                $.each(data, function (i, item) {
                    options += '<option value="' + item.id + '">' + item.text + '</option>';
                });

                $select.html(options);
            });
        }
    });

    // Handle change event for standard dropdown
    $('#po_select').on('change', function () {
        load_po_items($(this).val());
    });

    // Selectpicker
    $('.selectpicker').selectpicker();
});

function add_manual_item() {
    var sid = $('#shipment_id').val();
    if (!sid) { alert('Please save shipment first'); return; }

    var item_id = $('#item_select').val();
    var qty = $('input[name="new_qty"]').val();
    var fob = $('input[name="new_fob"]').val();
    var weight = $('input[name="new_weight"]').val();
    var volume = $('input[name="new_volume"]').val();
    var duty = $('input[name="new_duty"]').val();

    if (!item_id) { alert('Please select an item'); return; }

    var data = {
        shipment_id: sid,
        item_id: item_id,
        qty_shipped: qty,
        unit_fob_price: fob,
        net_weight_kg: weight,
        volume_cbm: volume,
        duty_percent: duty
    };

    $.post(admin_url + 'shipments/add_line', data).done(function (response) {
        response = JSON.parse(response);
        if (response.success) {
            location.reload();
        } else {
            alert('Failed to add item');
        }
    });
}

function delete_shipment_line(id) {
    if (confirm('Are you sure?')) {
        $.get(admin_url + 'shipments/delete_line/' + id).done(function (response) {
            location.reload();
        });
    }
}

function load_po_items(po_id) {
    if (!po_id) return;

    $.get(admin_url + 'shipments/get_po_items/' + po_id).done(function (response) {
        var items = JSON.parse(response);
        var html = '<div class="form-group"><input type="text" id="po_items_search" class="form-control" placeholder="Search items in this quotation..."></div>';

        html += '<div style="max-height: 300px; overflow-y: auto;">';
        html += '<table class="table" id="po_items_table"><thead><tr><th style="width:50px; text-align:center;"><input type="checkbox" id="check_all_po_items"></th><th>Item</th><th>Qty</th><th>Rate</th></tr></thead><tbody>';

        $.each(items, function (i, item) {
            var itemName = (item.item_name || item.description || '');
            var itemCode = (item.commodity_code || '');
            var fullText = (itemName + ' ' + itemCode).toLowerCase().replace(/"/g, '&quot;');

            html += '<tr data-search-text="' + fullText + '">';
            html += '<td text-align="center"><input type="checkbox" name="po_item_check" value="' + i + '" data-item-id="' + item.item_code + '" data-qty="' + item.quantity + '" data-rate="' + item.unit_price + '"></td>';
            html += '<td>' + itemName + (itemCode ? ' <small class="text-muted">(' + itemCode + ')</small>' : '') + '</td>';
            html += '<td>' + item.quantity + '</td>';
            html += '<td>' + item.unit_price + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table></div>';

        $('#po_items_container').html(html);

        // Filter Logic
        $('#po_items_search').on('keyup', function () {
            var value = $(this).val().toLowerCase();
            $('#po_items_table tbody tr').filter(function () {
                $(this).toggle($(this).data('search-text').indexOf(value) > -1)
            });
        });

        // Check All Logic
        $('#check_all_po_items').on('change', function () {
            var isChecked = $(this).prop('checked');
            // Only check visible rows if searching, or all if not
            $('#po_items_table tbody tr:visible input[name="po_item_check"]').prop('checked', isChecked);
        });
    });
}


function add_po_items() {
    var sid = $('#shipment_id').val();

    // Validate Shipment ID check
    if (!sid) {
        alert('Shipment ID is missing. Please save the shipment first.');
        return;
    }

    var selected = $('input[name="po_item_check"]:checked');
    if (selected.length == 0) {
        alert('Please select at least one item to add.');
        return;
    }

    var promises = [];
    var po_id = $('#po_select').val();

    selected.each(function () {
        var me = $(this);
        var data = {
            shipment_id: sid,
            item_id: me.data('item-id'),
            po_ref_id: po_id,
            qty_shipped: me.data('qty'),
            unit_fob_price: me.data('rate'),
            net_weight_kg: 0,
            volume_cbm: 0,
            duty_percent: 0
        };
        promises.push($.post(admin_url + 'shipments/add_line', data));
    });

    $.when.apply($, promises).then(function () {
        // Reload after all adds are complete
        location.reload();
    }, function () {
        alert('Error adding items.');
    });
}

// COST FUNCTIONS

function add_cost() {
    var cost_def_id = $('#cost_def_select').val();
    var amount = $('input[name="cost_amount"]').val();
    var currency = $('input[name="cost_currency"]').val();
    var rate = $('input[name="cost_rate"]').val();
    var method = $('#cost_method').val();

    if (!cost_def_id || !amount) { alert('Please fill required fields'); return; }

    var data = {
        shipment_id: shipment_id,
        cost_def_id: cost_def_id,
        total_amount: amount,
        currency: currency,
        exchange_rate: rate,
        allocation_method: method
    };

    $.post(admin_url + 'shipments/add_cost', data).done(function (response) {
        var res = JSON.parse(response);
        if (res.success) {
            location.reload();
        } else {
            alert('Failed to add cost');
        }
    });
}

function delete_cost(id) {
    if (confirm('Are you sure?')) {
        $.get(admin_url + 'shipments/delete_cost/' + id).done(function (response) {
            location.reload();
        });
    }
}

function recalculate_costs() {
    var sid = $('#shipment_id').val();
    if (!sid) { alert('Please save shipment first'); return; }

    $('#calculation_result_modal').modal('show');
    $('#calculation_modal_body').html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i> Calculating...</p>');

    $.get(admin_url + 'shipments/calculate/' + sid).done(function (response) {
        // Handle potential error responses
        try {
            var data = JSON.parse(response);
        } catch (e) {
            $('#calculation_modal_body').html('<div class="alert alert-danger">Server Error: ' + response + '</div>');
            return;
        }

        if (data.error) {
            $('#calculation_modal_body').html('<div class="alert alert-danger">' + data.error + '</div>');
            return;
        }

        var html = '<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Item ID</th><th>Item Name</th><th>Qty</th><th>Base FOB</th><th>Allocated Costs</th><th>Duty</th><th>Post-Duty</th><th>Total Landed</th><th>Unit Cost</th></tr></thead><tbody>';

        var total_qty = 0;
        var total_fob = 0;
        var total_allocated = 0;
        var total_duty = 0;
        var total_post_duty = 0;
        var total_landed = 0;

        $.each(data, function (id, val) {
            html += '<tr>';
            html += '<td>' + val.id + '</td>';
            html += '<td>' + val.item_name + '</td>';
            html += '<td>' + val.qty_shipped + '</td>';
            html += '<td>' + val.base_fob + '</td>';
            html += '<td>' + val.allocated_costs + '</td>';
            html += '<td>' + val.duty_amount + '</td>';
            html += '<td>' + (val.post_duty_cost || 0) + '</td>';
            html += '<td>' + val.total_landed + '</td>';
            html += '<td><strong>' + val.unit_landed + '</strong></td>';
            html += '</tr>';

            total_qty += parseFloat(val.qty_shipped || 0);
            total_fob += parseFloat(val.base_fob || 0);
            total_allocated += parseFloat(val.allocated_costs || 0);
            total_duty += parseFloat(val.duty_amount || 0);
            total_post_duty += parseFloat(val.post_duty_cost || 0);
            total_landed += parseFloat(val.total_landed || 0);
        });

        html += '</tbody><tfoot><tr style="font-weight:bold; background-color:#f0f0f0;">';
        html += '<td colspan="2" class="text-right">Total:</td>';
        html += '<td>' + total_qty + '</td>';
        html += '<td>' + total_fob.toFixed(4) + '</td>';
        html += '<td>' + total_allocated.toFixed(4) + '</td>';
        html += '<td>' + total_duty.toFixed(4) + '</td>';
        html += '<td>' + total_post_duty.toFixed(4) + '</td>';
        html += '<td>' + total_landed.toFixed(4) + '</td>';
        html += '<td></td>';
        html += '</tr></tfoot></table></div>';
        $('#calculation_modal_body').html(html);
    }).fail(function (xhr, status, error) {
        $('#calculation_modal_body').html('<div class="alert alert-danger">Request Failed: ' + error + '</div>');
    });
}

function edit_shipment_line(line, itemName) {
    $('#edit_line_id').val(line.id);
    $('#edit_item_name').val(itemName);
    $('input[name="edit_qty"]').val(line.qty_shipped);
    $('input[name="edit_fob"]').val(line.unit_fob_price);
    $('input[name="edit_weight"]').val(line.net_weight_kg);
    $('input[name="edit_volume"]').val(line.volume_cbm);
    $('input[name="edit_duty"]').val(line.duty_percent);

    $('#edit_line_modal').modal('show');
}

function save_line_edit() {
    var id = $('#edit_line_id').val();
    if (!id) return;

    var data = {
        qty_shipped: $('input[name="edit_qty"]').val(),
        unit_fob_price: $('input[name="edit_fob"]').val(),
        net_weight_kg: $('input[name="edit_weight"]').val(),
        volume_cbm: $('input[name="edit_volume"]').val(),
        duty_percent: $('input[name="edit_duty"]').val()
    };

    $.post(admin_url + 'shipments/update_line/' + id, data).done(function (response) {
        var res = JSON.parse(response);
        if (res.success) {
            location.reload();
        } else {
            alert('Failed to update line');
        }
    });
}
