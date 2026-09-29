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
    
    // AI Carrier Suggestion
    $('input[name="port_of_discharge"]').on('blur', function() {
        var pol = $('input[name="port_of_loading"]').val();
        var pod = $(this).val();
        var carrierInput = $('input[name="carrier"]');
        
        if (pol && pod && carrierInput.val() === '') {
            $.post(admin_url + 'shipments/suggest_carrier', {
                port_of_loading: pol,
                port_of_discharge: pod
            }).done(function(response) {
                var res = JSON.parse(response);
                if (res.carrier) {
                    carrierInput.val(res.carrier);
                    alert_float('info', 'AI Suggested Carrier applied based on historical routes.');
                }
            });
        }
    });
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
        duty_percent: duty,
        vat_percent: $('input[name="new_vat"]').val(),
        hs_code: $('input[name="new_hs_code"]').val(),
        customs_value: $('input[name="new_customs_value"]').val(),
        specific_duty_rate: $('input[name="new_specific_duty"]').val(),
        country_of_origin: $('input[name="new_origin"]').val()
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
            duty_percent: 0,
            vat_percent: 0,
            hs_code: '',
            customs_value: 0,
            specific_duty_rate: 0,
            country_of_origin: ''
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

        // Gather all unique cost names
        var cost_names = [];
        $.each(data, function (id, val) {
            if (val.individual_costs) {
                Object.keys(val.individual_costs).forEach(function (name) {
                    if (cost_names.indexOf(name) === -1) cost_names.push(name);
                });
            }
        });

        var html = '<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Item Name</th><th>Qty</th><th>Base FOB</th>';
        
        // Add dynamic cost headers
        $.each(cost_names, function (i, name) {
            html += '<th>' + name + '</th>';
        });

        html += '<th>Emen. (2%)</th><th>CIF</th><th>Customs</th><th>VAT</th><th>Total Landed</th><th>Unit Cost</th></tr></thead><tbody>';

        var totals = {
            qty: 0,
            fob: 0,
            emen: 0,
            cif: 0,
            customs: 0,
            vat: 0,
            landed: 0,
            costs: {} // Track sums for each dynamic cost
        };
        $.each(cost_names, function (i, name) { totals.costs[name] = 0; });

        $.each(data, function (id, val) {
            html += '<tr>';
            html += '<td>' + val.item_name + '</td>';
            html += '<td>' + val.qty_shipped + '</td>';
            html += '<td>' + val.base_fob + '</td>';

            // Dynamic cost cells
            $.each(cost_names, function (i, name) {
                var amt = parseFloat(val.individual_costs[name] || 0);
                html += '<td>' + amt.toFixed(4) + '</td>';
                totals.costs[name] += amt;
            });

            html += '<td>' + (val.emenumentus_cost || 0) + '</td>';
            html += '<td>' + (val.cif_cost || 0) + '</td>';
            html += '<td>' + (val.duty_amount || 0) + '</td>';
            html += '<td>' + (val.vat_amount || 0) + '</td>';
            html += '<td>' + val.total_landed + '</td>';
            html += '<td><strong>' + val.unit_landed + '</strong></td>';
            html += '</tr>';

            totals.qty += parseFloat(val.qty_shipped || 0);
            totals.fob += parseFloat(val.base_fob || 0);
            totals.emen += parseFloat(val.emenumentus_cost || 0);
            totals.cif += parseFloat(val.cif_cost || 0);
            totals.customs += parseFloat(val.duty_amount || 0);
            totals.vat += parseFloat(val.vat_amount || 0);
            totals.landed += parseFloat(val.total_landed || 0);
        });

        html += '</tbody><tfoot><tr style="font-weight:bold; background-color:#f0f0f0;">';
        html += '<td text-align="right">Total:</td>';
        html += '<td>' + totals.qty + '</td>';
        html += '<td>' + totals.fob.toFixed(4) + '</td>';

        // Dynamic cost sums in footer
        $.each(cost_names, function (i, name) {
            html += '<td>' + totals.costs[name].toFixed(4) + '</td>';
        });

        html += '<td>' + totals.emen.toFixed(4) + '</td>';
        html += '<td>' + totals.cif.toFixed(4) + '</td>';
        html += '<td>' + totals.customs.toFixed(4) + '</td>';
        html += '<td>' + totals.vat.toFixed(4) + '</td>';
        html += '<td>' + totals.landed.toFixed(4) + '</td>';
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
    $('input[name="edit_vat"]').val(line.vat_percent);
    $('input[name="edit_hs_code"]').val(line.hs_code);
    $('input[name="edit_customs_value"]').val(line.customs_value);
    $('input[name="edit_specific_duty"]').val(line.specific_duty_rate);
    $('input[name="edit_origin"]').val(line.country_of_origin);

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
        duty_percent: $('input[name="edit_duty"]').val(),
        vat_percent: $('input[name="edit_vat"]').val(),
        hs_code: $('input[name="edit_hs_code"]').val(),
        customs_value: $('input[name="edit_customs_value"]').val(),
        specific_duty_rate: $('input[name="edit_specific_duty"]').val(),
        country_of_origin: $('input[name="edit_origin"]').val()
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
