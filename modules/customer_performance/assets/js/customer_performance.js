function view_customer_performance(client_id) {
    if(!client_id) return;
    
    // Show modal directly with a loading state before AJAX finishes
    $('#customer_performance_modal_content').html('<div class="modal-body text-center"><div class="spinner-border text-primary" role="status"></div><p>Wait, loading stats...</p></div>');
    $('#customer_performance_modal').modal('show');
    
    $.get(admin_url + 'customer_performance/get_stats/' + client_id, function(response) {
        $('#customer_performance_modal_content').html(response);
    }).fail(function(err) {
        $('#customer_performance_modal').modal('hide');
        alert_float('danger', 'Failed to fetch customer performance stats.');
    });
}

// Logic to inject the 'View Stats' button in the add/edit forms (Invoices, Estimates, Proposals)
$(function() {
    // For Invoices and Estimates
    $('body').on('change', '#clientid', function() {
        var client_id = $(this).val();
        handle_customer_performance_button(client_id, $(this));
    });

    // For Proposals
    $('body').on('change', '#rel_id', function() {
        var rel_type = $('#rel_type').val();
        if (rel_type === 'customer') {
            var client_id = $(this).val();
            handle_customer_performance_button(client_id, $(this));
        } else {
            remove_customer_performance_button();
        }
    });

    $('body').on('change', '#rel_type', function() {
        var rel_type = $(this).val();
        if (rel_type !== 'customer') {
            remove_customer_performance_button();
        } else {
            var client_id = $('#rel_id').val();
            if (client_id) {
                handle_customer_performance_button(client_id, $('#rel_id'));
            }
        }
    });

    // Check on load (if form is opened for editing or pre-filled)
    setTimeout(function() {
        if ($('#clientid').length > 0 && $('#clientid').val()) {
            $('#clientid').trigger('change');
        }
        if ($('#rel_id').length > 0 && $('#rel_type').val() === 'customer' && $('#rel_id').val()) {
            $('#rel_id').trigger('change');
        }
    }, 500);

    function handle_customer_performance_button(client_id, element) {
        remove_customer_performance_button(); // ensure no duplicates
        if (client_id && client_id !== '') {
            var btn = '<a href="#" class="customer-performance-btn pull-right text-info" style="font-weight: normal; font-size: 13px; margin-top:2px;" onclick="view_customer_performance(' + client_id + '); return false;"><i class="fa fa-bar-chart"></i> View Stats</a>';
            
            var wrapper = element.closest('.form-group');
            if (wrapper.find('label.control-label').length > 0) {
                wrapper.find('label.control-label').first().append(btn);
            } else {
                element.parent().append('<div class="customer-performance-btn mtop10">' + btn + '</div>');
            }
        }
    }

    function remove_customer_performance_button() {
        $('.customer-performance-btn').remove();
    }

    // New logic for showing the last 5 prices when an item is selected
    $('body').on('change', 'select[name="item_select"]', function() {
        var item_id = $(this).val();
        var client_id = '';

        // Determine the client ID based on form context
        if ($('#clientid').length > 0 && $('#clientid').val()) {
            client_id = $('#clientid').val();
        } else if ($('#rel_id').length > 0 && $('#rel_type').val() === 'customer' && $('#rel_id').val()) {
            client_id = $('#rel_id').val();
        }

        $('.item-last-prices').remove(); // Clear previous history

        if (client_id && item_id) {
            var target_wrapper = $('input[name="rate"]').closest('.form-group');
            if (target_wrapper.length === 0) {
                target_wrapper = $('input[name="rate"]').closest('td');
            }
            
            // Append a loading text
            target_wrapper.append('<div class="item-last-prices mtop10 text-muted"><small><i class="fa fa-spinner fa-spin"></i> Checking previous prices...</small></div>');

            $.post(admin_url + 'customer_performance/get_item_prices', {
                client_id: client_id,
                item_id: item_id
            }, function(response) {
                $('.item-last-prices').remove();
                if(response !== '') {
                    target_wrapper.append('<div class="item-last-prices">' + response + '</div>');
                }
            });
        }
    });
});
