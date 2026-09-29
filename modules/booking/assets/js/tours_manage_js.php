<script>

var table_tours;

/** Create New Customer **/
add_hotkey('Shift+C', function () {
    var $leadModal = $('#lead-modal');
    var $tourModal = $('#tour-modal');
    if ($leadModal.is(':visible')) {
        convert_lead_to_customer($leadModal.find('input[name="leadid"]').val());
    } else if ($tourModal.is(':visible')) {
        var $tourCommentsWrapper = $tourModal.find('.tours-comments');
        if (!$tourCommentsWrapper.is(':visible')) {
            $tourCommentsWrapper.css('display', 'block');
        }
        init_new_tour_comment();
    } else {
        window.location.href = admin_url + 'clients/client';
    }
});

/** Create New Estimate **/
add_hotkey('Shift+E', function () {
    var $leadModal = $('#lead-modal');
    var $tourModal = $('#tour-modal');
    if (!$leadModal.is(':visible') && !$tourModal.is(':visible')) {
        window.location.href = admin_url + 'estimates/estimate';
    } else {
        if ($leadModal.is(':visible')) {
            $('a[lead-edit]').click();
        } else if ($tourModal.is(':visible')) {
            edit_tour($tourModal.find('[data-tour-single-id]').attr('data-tour-single-id'));
        }
    }
});

/** Marks tour as finished when modal is opened **/
add_hotkey('Shift+F', function () {
    var $tourModal = $('#tour-modal');
    if ($tourModal.is(':visible')) {
        var $tourSingleBody = $tourModal.find('[data-tour-single-id]');
        if ($tourSingleBody.attr('data-status') != 5) {
            mark_complete($tourSingleBody.attr('data-tour-single-id'));
        }
    }
});

/** Create New Tour **/
add_hotkey('Shift+T', function () {
    var $newTourRelationBtn = $('.new-tour-relation');
    if ($newTourRelationBtn.length > 0) {
        new_tour(admin_url + 'booking/tours/tour?rel_id=' + $newTourRelationBtn.attr('data-rel-id') + '&rel_type=' + $newTourRelationBtn.attr('data-rel-type'));
    } else if ($("body").hasClass('booking')) {
        new_tour(admin_url + 'booking/tours/tour?rel_id=' + booking_id + '&rel_type=booking');
    } else {
        new_tour();
    }
});

/** List Tours **/
add_hotkey('Alt+T', function () {
    window.location.href = admin_url + 'booking/tours/list_tours';
});

// On delete reminder reload the tables
$("body").on('click', '.delete-reminder', function () {
    if (confirm_delete()) {
        requestGetJSON($(this).attr('href')).done(function (response) {
            alert_float(response.alert_type, response.message);
            if ($('#tour-modal').is(':visible')) {
                _tour_append_html(response.tourHtml);
            }
            reload_reminders_tables();
        });
    }
    return false;
});

/* Insert new checklist items on enter press */
$("body").on('keypress', 'textarea[name="checklist-description"]', function (event) {
    if (event.which == '13') {
        var that = $(this);
        update_tour_checklist_item(that).done(function () {
            add_tour_checklist_item(that.attr('data-tourid'));
        });
        return false;
    }
});

/* Update tours checklist items when focusing out */
$("body").on('blur paste', 'textarea[name="checklist-description"]', function () {
    update_tour_checklist_item($(this));
});

$("body").on('show.bs.select', 'select.checklist-items-template-select', _make_tour_checklist_items_deletable);
$("body").on('refreshed.bs.select', 'select.checklist-items-template-select', _make_tour_checklist_items_deletable);


// Tour single modal inline changes, eq start date, due date...
$('body').on('change', '.tour-single-inline-field', function () {
    var singleDateInputs = $('body').find('.tour-single-inline-field');
    var data = {};
    $.each(singleDateInputs, function () {
        var name = $(this).attr('name');
        var val = $(this).val();
        var $parentwrap = $(this).parents('.tour-single-inline-wrap');
        if (name == 'startdate' && val === '') {
            $parentwrap.addClass('text-danger');
        } else if (name == 'startdate' && val !== '') {
            $parentwrap.removeClass('text-danger');
        }
        if ((name == 'startdate' && val !== '') || name != 'startdate') {
            data[$(this).attr('name')] = val;
            // Name is required
            if (name != 'startdate' && val === '') {
                $parentwrap.css('opacity', 0.5);
            } else {
                $parentwrap.css('opacity', 1);
            }
        }
    });
    var $tourModal = $('#tour-modal');
    var dTourID = $tourModal.find('[data-tour-single-id]').attr('data-tour-single-id');
    $.post(admin_url + 'booking/tours/tour_single_inline_update/' + dTourID, data);
});

// When user select from checklist items template add this template
$("body").on('change', '#tour-modal #checklist_items_templates', function () {
    var val = $(this).val();
    var valTemplate = $(this).find('option[value="' + val + '"]').html().trim();
    if (valTemplate !== '') {
        var $tourModal = $('#tour-modal');
        add_tour_checklist_item(
            $tourModal.find('[data-tour-single-id]').attr('data-tour-single-id'),
            valTemplate);
        $(this).selectpicker('val', '');
    }
});

// Used on tour comment date href, can be used directly to add the link in browser for scroll.
$("body").on('click', '.tour-date-as-comment-id', function (e) {
    e.preventDefault();
    var tour_comment_temp = $(this).attr('href').split('#');
    var comment_position = $('#' + tour_comment_temp[tour_comment_temp.length - 1]).position();
    $("#tour-modal").scrollTop(comment_position.top);
});

// Init single tour data
if (typeof (tourid) !== 'undefined' && tourid !== '') {
    init_tour_modal(tourid);
}

// Tour checklist mark as complete/incomplete
$("body").on('change', 'input[name="checklist-box"]', function () {
    requestGet(admin_url + 'booking/tours/checkbox_action/' + ($(this).parents('.checklist').data('checklist-id')) + '/' + ($(this).prop('checked') === true ? 1 : 0));
    recalculate_checklist_items_progress();
});

// Fix tour checklist content textarea height
$("body").on('keyup paste click', "textarea[name='checklist-description']", function (e) {
    do_tour_checklist_items_height($(this));
});

// On click on tour comment textarea make it tinymce, by default is plain textarea
$("body").on('click focus', '#tour_comment', function (e) {
    init_new_tour_comment();
});

// Delete tour timesheet from the tour single modal
$("body").on('click', '.tour-single-delete-timesheet', function (e) {
    e.preventDefault();
    if (confirm_delete()) {
        var _delete_timesheet_tour_id = $(this).data('tour-id');
        requestGet($(this).attr('href')).done(function (response) {
            init_tour_modal(_delete_timesheet_tour_id);
            setTimeout(function () {
                reload_tours_tables();
                init_timers();
            }, 20);
        });
    }
});

// New timesheet add manually from tour single modal
$("body").on('click', '.tour-single-add-timesheet', function (e) {
    e.preventDefault();
    var start_time = $("body").find('#tour-modal input[name="timesheet_start_time"]').val();
    var end_time = $("body").find('#tour-modal input[name="timesheet_end_time"]').val();
    var duration = $("body").find('#tour-modal input[name="timesheet_duration"]').val();
    if ((start_time !== '' && end_time !== '') || duration !== '') {
        var data = {};
        data.timesheet_duration = duration;
        data.start_time = start_time;
        data.end_time = end_time;
        data.timesheet_tour_id = $(this).data('tour-id');
        data.note = $("body").find('#tour_single_timesheet_note').val();
        data.timesheet_staff_id = $("body").find('#tour-modal select[name="single_timesheet_staff_id"]').val();
        $.post(admin_url + 'booking/tours/log_time', data).done(function (response) {
            response = JSON.parse(response);
            if (response.success === true || response.success == 'true') {
                init_tour_modal(data.timesheet_tour_id);
                alert_float('success', response.message);
                setTimeout(function () {
                    reload_tours_tables();
                }, 20);
            } else {
                alert_float('warning', response.message);
            }
        });
    }
});

// Copy tour href/button event.
$("body").on('click', '.copy_tour_action', function () {
    var data = {};
    $(this).prop('disabled', true);
    data.copy_from = $(this).data('tour-copy-from');
    data.copy_tour_assignees = $("body").find('#copy_tour_assignees').prop('checked');
    data.copy_tour_followers = $("body").find('#copy_tour_followers').prop('checked');
    data.copy_tour_checklist_items = $("body").find('#copy_tour_checklist_items').prop('checked');
    data.copy_tour_attachments = $("body").find('#copy_tour_attachments').prop('checked');
    data.copy_tour_status = $("body").find('input[name="copy_tour_status"]:checked').val();
    $.post(admin_url + 'booking/tours/copy', data).done(function (response) {
        response = JSON.parse(response);
        if (response.success === true || response.success == 'true') {
            var $tourModal = $('#_tour_modal');
            if ($tourModal.is(':visible')) {
                $tourModal.modal('hide');
            }
            init_tour_modal(response.new_tour_id);
            reload_tours_tables();

        }
        alert_float(response.alert_type, response.message);
    });
    return false;
});

// Creates new tour in specific milestones, the milestone is auto selected on the new tour modal
$("body").on('click', '.new-tour-to-milestone', function (e) {
    e.preventDefault();
    var milestone_id = $(this).parents('.milestone-column').data('col-status-id');
    new_tour(admin_url + 'booking/tours/tour?rel_type=booking&rel_id=' + booking_id + '&milestone_id=' + milestone_id);
    $('body [data-toggle="popover"]').popover('hide');
});

// On shown tour add/edit modal
$("body").on("shown.bs.modal", '#_tour_modal', function (e) {
    if (!$(e.currentTarget).hasClass('edit')) {
        $("body").find('#_tour_modal #name').focus();
    } else {
        if ($(this).find('.tinymce-tour').val().trim() !== '') {
            init_editor('.tinymce-tour', {
                height: 200
            });
        }
    }
    init_tags_inputs();
});

// Remove the tinymce description tour editor
$("body").on("hidden.bs.modal", '#_tour_modal', function () {

    tinyMCE.remove('.tinymce-tour');
    // Clear _ticket_message from single tickets in case user tried to convert ticket to tour to prevent populating the fields again with the last ticket message click
    if (typeof (_ticket_message) != 'undefined') {
        _ticket_message = undefined;
    }

    if ($(this).attr('data-lead-id') != undefined && !$(this).attr('data-tour-created')) {
        init_lead($(this).attr('data-lead-id'));
    }

    destroy_dynamic_scripts_in_element($('body #_tour_modal'));

    $('#_tour').empty();

});

// Don't allow the tour modal to close if lightbox is visible in for the tour attachments
// Used when user hit the ESC button
// Empty tour data
$("body").on('hide.bs.modal', '#tour-modal', function () {
    if ($('#lightbox').is(':visible') == true) {
        return false;
    }
    if (typeof (tourAttachmentDropzone) != 'undefined') {
        tourAttachmentDropzone.destroy();
    }
    tinyMCE.remove('#tour_view_description');
});

// On tour single modal hidden remove all html data
$("body").on("hidden.bs.modal", '#tour-modal', function () {
    // Clear memory leak
    destroy_dynamic_scripts_in_element($(this));

    $(this).find('.data').empty();
});

// On tour single modal shown perform few actions
$("body").on("shown.bs.modal", '#tour-modal', function () {
    do_tour_checklist_items_height();
    init_tags_inputs();
    fix_tour_modal_left_col_height();
    $(document).off('focusin.modal');
    var current_url = window.location.href;
    if (current_url.indexOf('#comment_') > -1) {
        var tour_comment_id = current_url.split('#comment_');
        tour_comment_id = tour_comment_id[tour_comment_id.length - 1];
        $('[data-tour-comment-href-id="' + tour_comment_id + '"]').click();
    }
});

// On focus out on the taks modal single update the tags in case changes are found
$("body").on('blur', '#tour-modal ul.tagit li.tagit-new input', function () {
    setTimeout(function () {
        tour_single_update_tags();
    }, 100);
});

// Assign tour to staff member
$("body").on('change', 'select[name="select-assignees"]', function () {
    $("body").append('<div class="dt-loader"></div>');
    var data = {};
    data.assignee = $('select[name="select-assignees"]').val();
    if (data.assignee !== '') {
        data.tourid = $(this).attr('data-tour-id');
        $.post(admin_url + 'booking/tours/add_tour_assignees', data).done(function (response) {
            $("body").find('.dt-loader').remove();
            response = JSON.parse(response);
            reload_tours_tables();
            _tour_append_html(response.tourHtml);
        });
    }
});

// Add follower to tour
$("body").on('change', 'select[name="select-followers"]', function () {
    var data = {};
    data.follower = $('select[name="select-followers"]').val();
    if (data.follower !== '') {
        data.tourid = $(this).attr('data-tour-id');
        $("body").append('<div class="dt-loader"></div>');
        $.post(admin_url + 'booking/tours/add_tour_followers', data).done(function (response) {
            response = JSON.parse(response);
            $("body").find('.dt-loader').remove();
            _tour_append_html(response.tourHtml);
        });
    }
});

// Tour single moda tracking stats close
$("body").on('click', '.close-tour-stats', function () {
    $('#tour-tracking-stats-modal').modal('hide');
});

// Remove tracking status div because its appended automatically to the DOM on each click
$("body").on("hidden.bs.modal", '#tour-tracking-stats-modal', function () {
    $('#tracking-stats').remove();
});

// Tour modal single chart for logged time by assigned users
$("body").on('show.bs.modal', '#tour-tracking-stats-modal', function () {
    var tracking_chart_selector = $("body").find('#tour-tracking-stats-chart');
    setTimeout(function () {
        if (typeof (tourTrackingChart) != 'undefined') {
            tourTrackingChart.destroy();
        }
        tourTrackingChart = new Chart(tracking_chart_selector, {
            type: 'line',
            data: tourTrackingStatsData,
            options: {
                legend: {
                    display: false,
                },
                responsive: true,
                maintainAspectRatio: false,
                tooltips: {
                    enabled: true,
                    mode: 'single',
                    callbacks: {
                        label: function (tooltipItems, data) {
                            return decimalToHM(tooltipItems.yLabel);
                        }
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            min: 0,
                            userCallback: function (label, index, labels) {
                                return decimalToHM(label);
                            },
                        }
                    }]
                },
            }
        });
    }, 800);
});

// On shown for all modals
$("body").on("shown.bs.modal", '.modal', function () {
    // Fix for all modals scroll..
    $("body").addClass('modal-open');
    // Close the top timers dropdown in case user click on some tour
    $("body").find('#started-timers-top').parents('li').removeClass('open');
});

/* Custom notifications links, NOTE: touchstart listener is for iOS davices */
$("body").on('click' + ('ontouchstart' in window ? ' touchstart' : ''),
    '.notifications a.notification-top, .notification_link',
    function (e) {
        e.preventDefault();
        var $notLink = $(this);
        var not_href_id;

        var not_href = $notLink.hasClass('notification_link') ? $notLink.data('link') : e.currentTarget.href;

        var not_href_array = not_href.split('#');
        var notRedirect = true;
        if (not_href_array[1] && not_href_array[1].indexOf('=') > -1) {
            notRedirect = false;
            not_href_id = not_href_array[1].split('=')[1];
            if (not_href_array[1].indexOf('postid') > -1) {
                postid = not_href_id;
                if ($(window).width() > 769) {
                    $('.open_newsfeed.desktop').click();
                } else {
                    $('.open_newsfeed.mobile').click();
                }
            } else if (not_href_array[1].indexOf('tourid') > -1) {

                var comment_id = undefined;
                if (not_href.indexOf('#comment_') > -1) {
                    var tour_comment_id = not_href.split('#comment_');
                    comment_id = tour_comment_id[tour_comment_id.length - 1];
                }
                init_tour_modal(not_href_id, comment_id);
            } else if (not_href_array[1].indexOf('leadid') > -1) {
                init_lead(not_href_id);
            } else if (not_href_array[1].indexOf('eventid') > -1) {
                view_event(not_href_id);
            }
        }
        if (!$notLink.hasClass('desktopClick')) {
            $notLink.parent('li').find('.not-mark-as-read-inline').click();
        }
        if (notRedirect) {
            setTimeout(function () {
                window.location.href = not_href_array;
            }, 50);
        }
    });

table_tours = $('.table-tours');
if (table_tours.length) {
    var ToursServerParams = {},
        Tours_Filters;
    Tours_Filters = $('._hidden_inputs._filters._tours_filters input');
    $.each(Tours_Filters, function () {
        ToursServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
    });

    // Tours not sortable
    var toursTableNotSortable = [0]; // bulk actions
    var toursTableURL = admin_url + 'booking/tours/table';

    if ($("body").hasClass('tours-page')) {
        toursTableURL += '?bulk_actions=true';
    }

    _table_api = initDataTable(table_tours, toursTableURL, toursTableNotSortable, toursTableNotSortable, ToursServerParams, [table_tours.find('th.duedate').index(), 'asc']);

    if (_table_api && $("body").hasClass('dashboard')) {
        _table_api.column(5).visible(false, false)
            .column(6).visible(false, false)
            .columns.adjust();
    }
}

$('body').on('change', '.invoice #booking_id', function () {
    var booking_id = $(this).selectpicker('val');
    if (booking_id !== '') {
        requestGetJSON('booking/tours/get_billable_tours_by_booking/' + booking_id).done(function (tours) {
            _init_tours_billable_select(tours, booking_id);
        });
    } else {
        var client_id = $('#clientid').selectpicker('val');
        if (client_id !== '') {
            requestGetJSON('booking/tours/get_billable_tours_by_customer_id/' + client_id).done(function (tours) {
                _init_tours_billable_select(tours);
            });
        } else {
            // Empty dropdown
            _init_tours_billable_select([], '');
        }
    }
});

// Add tour data to preview from the dropdown for invoiecs
$("body").on('change', 'select[name="tour_select"]', function () {
    ($(this).selectpicker('val') !== '' ? add_tour_to_preview_as_item($(this).selectpicker('val')) : '');
});

$("body").on('change', '.f_client_id #clientid', function () {
    var val = $(this).val();
    var bookingAjax = $('select[name="booking_id"]');
    var clonedBookingsAjaxSearchSelect = bookingAjax.html('').clone();
    var bookingsWrapper = $('.bookings-wrapper');
    bookingAjax.selectpicker('destroy').remove();
    bookingAjax = clonedBookingsAjaxSearchSelect;
    $('#booking_ajax_search_wrapper').append(clonedBookingsAjaxSearchSelect);
    init_ajax_booking_search_by_customer_id();
    clear_billing_and_shipping_details();
    if (!val) {
        $('#merge').empty();
        $('#expenses_to_bill').empty();
        $('#invoice_top_info').addClass('hide');
        bookingsWrapper.addClass('hide');
        return false;
    }

    var currentInvoiceID = $("body").find('input[name="merge_current_invoice"]').val();
    currentInvoiceID = typeof (currentInvoiceID) == 'undefined' ? '' : currentInvoiceID;

    requestGetJSON('invoices/client_change_data/' + val + '/' + currentInvoiceID).done(function (response) {
        $('#merge').html(response.merge_info);
        var $billExpenses = $('#expenses_to_bill');
        // Invoice from booking, in invoice_template this is not shown
        $billExpenses.length === 0 ? response.expenses_bill_info = '' : $billExpenses.html(response.expenses_bill_info);
        ((response.merge_info !== '' || response.expenses_bill_info !== '') ? $('#invoice_top_info').removeClass('hide') : $('#invoice_top_info').addClass('hide'));

        for (var f in billingAndShippingFields) {
            if (billingAndShippingFields[f].indexOf('billing') > -1) {
                if (billingAndShippingFields[f].indexOf('country') > -1) {
                    $('select[name="' + billingAndShippingFields[f] + '"]').selectpicker('val', response['billing_shipping'][0][billingAndShippingFields[f]]);
                } else {
                    if (billingAndShippingFields[f].indexOf('billing_street') > -1) {
                        $('textarea[name="' + billingAndShippingFields[f] + '"]').val(response['billing_shipping'][0][billingAndShippingFields[f]]);
                    } else {
                        $('input[name="' + billingAndShippingFields[f] + '"]').val(response['billing_shipping'][0][billingAndShippingFields[f]]);
                    }
                }
            }
        }

        if (!empty(response['billing_shipping'][0]['shipping_street'])) {
            $('input[name="include_shipping"]').prop("checked", true).change();
        }

        for (var fsd in billingAndShippingFields) {
            if (billingAndShippingFields[fsd].indexOf('shipping') > -1) {
                if (billingAndShippingFields[fsd].indexOf('country') > -1) {
                    $('select[name="' + billingAndShippingFields[fsd] + '"]').selectpicker('val', response['billing_shipping'][0][billingAndShippingFields[fsd]]);
                } else {
                    if (billingAndShippingFields[fsd].indexOf('shipping_street') > -1) {
                        $('textarea[name="' + billingAndShippingFields[fsd] + '"]').val(response['billing_shipping'][0][billingAndShippingFields[fsd]]);
                    } else {
                        $('input[name="' + billingAndShippingFields[fsd] + '"]').val(response['billing_shipping'][0][billingAndShippingFields[fsd]]);
                    }
                }
            }
        }

        init_billing_and_shipping_details();

        var client_currency = response['client_currency'];
        var s_currency = $("body").find('.accounting-template select[name="currency"]');
        client_currency = parseInt(client_currency);
        client_currency != 0 ? s_currency.val(client_currency) : s_currency.val(s_currency.data('base'));
        _init_tours_billable_select(response['billable_tours'], bookingAjax.selectpicker('val'));
        response.customer_has_bookings === true ? bookingsWrapper.removeClass('hide') : bookingsWrapper.addClass('hide');
        s_currency.selectpicker('refresh');
        init_currency();
    });

});

// Invoices to merge
$("body").on('change', 'input[name="invoices_to_merge[]"]', function () {
    var checked = $(this).prop('checked');
    var _id = $(this).val();
    if (checked === true) {
        requestGetJSON('invoices/get_merge_data/' + _id).done(function (response) {
            $.each(response.items, function (i, obj) {
                if (obj.rel_type !== '') {
                    if (obj.rel_type == 'tour') {
                        $('input[name="tour_id"]').val(obj.item_related_formatted_for_input);
                    } else if (obj.rel_type == 'expense') {
                        $('input[name="expense_id"]').val(obj.item_related_formatted_for_input);
                    }
                }
                _set_item_preview_custom_fields_array(obj.custom_fields);
                add_item_to_table(obj, 'undefined', _id);
            });
        });
    } else {
        // Remove the appended invoice to merge
        $("body").find('[data-merge-invoice="' + _id + '"]').remove();
    }
});

function _make_tour_checklist_items_deletable() {
    if (app.options.has_permission_tours_checklist_items_delete == '1') {
        var itemsHtml = $("body").find('.checklist-templates-wrapper ul.dropdown-menu li').not(':first-child');
        var itemsSelect = $("body").find('.checklist-templates-wrapper select option').not(':first-child');
        $.each(itemsSelect, function (i, item) {
            var $item = $(item);
            if ($(itemsHtml[i]).find('.checklist-item-template-remove').length === 0) {
                $(itemsHtml[i]).find('a > span.text').after('<small class="checklist-item-template-remove" onclick="remove_checklist_item_template(' + $item.attr('value') + '); event.stopPropagation();"><i class="fa fa-remove"></i></small>');
            }
        });
    }
}

function _init_tours_billable_select(tours, booking_id) {
    var billable_tours_area = $('#tour_select');
    if (billable_tours_area.length > 0) {
        var option_data;
        billable_tours_area.find('option').filter(function () {
            return this.value || $.trim(this.value).length > 0 || $.trim(this.text).length > 0;
        }).remove();

        $.each(tours, function (i, obj) {
            option_data = ' ';
            if (obj.started_timers === true) {
                option_data += 'disabled class="text-danger important" data-subtext="' + app.lang.invoice_tour_billable_timers_found + '"';
            } else if (obj.started_timers === false && obj.rel_type != 'booking') {
                option_data += 'data-subtext="' + obj.rel_name + '"';
            }
            billable_tours_area.append('<option value="' + obj.id + '"' + option_data + '>' + obj.name + '</option>');
        });

        var tours_help_wrapper = $('.input-group-addon-bill-tours-help');
        tours_help_wrapper.find('.popover-invoker').popover('destroy');
        tours_help_wrapper.empty();

        var help_tooltip = '';

        if (!empty(booking_id)) {
            help_tooltip = app.lang['showing_billable_tours_from_booking'] + ' ' + $('#booking_id option:selected').text().trim();
        } else {
            help_tooltip = app.lang['invoice_tour_item_booking_tours_not_included'];
        }

        tours_help_wrapper.html('<span class="pointer popover-invoker" data-container=".form-group-select-tour_select" data-trigger="click" data-placement="top" data-toggle="popover" data-content="' + help_tooltip + '"><i class="fa fa-question-circle"></i></span>');

        delay(function () {
            if ((tours_help_wrapper.attr('info-shown-count') < 3 || typeof (tours_help_wrapper.attr('info-shown-count')) == 'undefined') &&
                $('.bookings-wrapper').is(':visible') && tours.length > 0) {
                tours_help_wrapper.attr('info-shown-count', typeof (tours_help_wrapper.attr('info-shown-count')) == 'undefined' ? 1 : parseInt(tours_help_wrapper.attr('info-shown-count')) + 1);
                tours_help_wrapper.find('.popover-invoker').click();
            }
        }, 3500);
    }

    billable_tours_area.selectpicker('refresh');
}

// Initing relation tours tables
function init_rel_tours_table(rel_id, rel_type, selector) {
    if (typeof (selector) == 'undefined') {
        selector = '.table-rel-tours';
    }
    var $selector = $("body").find(selector);
    if ($selector.length === 0) {
        return;
    }

    var ToursServerParams = {},
        toursRelationTableNotSortable = [0], // bulk actions
        ToursFilters;

    ToursFilters = $('body').find('._hidden_inputs._filters._tours_filters input');

    $.each(ToursFilters, function () {
        ToursServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
    });

    var url = admin_url + 'booking/tours/init_relation_tours/' + rel_id + '/' + rel_type;

    if ($selector.attr('data-new-rel-type') == 'booking') {
        url += '?bulk_actions=true';
    }

    initDataTable($selector, url, toursRelationTableNotSortable, toursRelationTableNotSortable, ToursServerParams, [$selector.find('th.duedate').index(), 'asc']);
}

// Update tags in tour single modal
function tour_single_update_tags() {
    var tourTags = $("#tourTags");
    $.post(admin_url + 'booking/tours/update_tags', {
        tags: tourTags.tagit('assignedTags'),
        tour_id: tourTags.attr('data-tourid')
    });
}

// By default only 2 attachments for tours are shown the other are hidden and there is button show more, this will show all the attachments
function tour_attachments_toggle() {
    var $tourModal = $('#tour-modal');
    $tourModal.find('.tour_attachments_wrapper .tour-attachments-more').toggleClass('hide');
    $tourModal.find('.tour_attachments_wrapper .tour-attachments-less').toggleClass('hide');
}

// New tour reminder custom function
function new_tour_reminder(id) {
    var $container = $('#newTourReminderToggle');
    if (!$container.is(':visible') || $container.is(':visible') && $container.attr('data-edit') != undefined) {

        $container.slideDown(400, function () {
            fix_tour_modal_left_col_height();
        });

        $('#tourReminderFormSubmit').html(app.lang.create_reminder);
        $container.find('form').attr('action', admin_url + 'booking/tours/add_reminder/' + id);

        $container.find('#description').val('');
        $container.find('#date').val('');
        $container.find('#staff').selectpicker('val', $container.find('#staff').attr('data-current-staff'));
        $container.find('#notify_by_email').prop('checked', false);
        if ($container.attr('data-edit') != undefined) {
            $container.removeAttr('data-edit');
        }
        if (!$container.isInViewport()) {
            $('#tour-modal').animate({
                scrollTop: $container.offset().top + 'px'
            }, 'fast');
        }
    } else {
        $container.slideUp();
    }
}

// Edit reminder function
function edit_reminder(id, e) {
    requestGetJSON('misc/get_reminder/' + id).done(function (response) {
        var $container = $('.reminder-modal-' + response.rel_type + '-' + response.rel_id);
        var actionURL = admin_url + 'misc/edit_reminder/' + id;
        if ($container.length === 0 && $('body').hasClass('all-reminders')) {
            // maybe from view all reminders?
            $container = $('.reminder-modal--');
            $container.find('input[name="rel_type"]').val(response.rel_type);
            $container.find('input[name="rel_id"]').val(response.rel_id);
        } else if ($('#tour-modal').is(':visible')) {

            $container = $('#newTourReminderToggle');

            if ($container.attr('data-edit') && $container.attr('data-edit') == id) {
                $container.slideUp();
                $container.removeAttr('data-edit');
            } else {
                $container.slideDown(400, function () {
                    fix_tour_modal_left_col_height();
                });
                $container.attr('data-edit', id);
                if (!$container.isInViewport()) {
                    $('#tour-modal').animate({
                        scrollTop: $container.offset().top + 'px'
                    }, 'fast');
                }
            }
            actionURL = admin_url + 'booking/tours/edit_reminder/' + id;
            $('#tourReminderFormSubmit').html(app.lang.save);
        }

        $container.find('form').attr('action', actionURL);
        // For focusing the date field
        $container.find('form').attr('data-edit', true);
        $container.find('#description').val(response.description);
        $container.find('#date').val(response.date);
        $container.find('#staff').selectpicker('val', response.staff);
        $container.find('#notify_by_email').prop('checked', response.notify_by_email == 1 ? true : false);
        if ($container.hasClass('modal')) {
            $container.modal('show');
        }
    });
}

// Handles reminder modal form
function reminderFormHandler(form) {
    form = $(form);
    var data = form.serialize();
    $.post(form.attr('action'), data).done(function (data) {
        data = JSON.parse(data);
        if (data.message !== '') {
            alert_float(data.alert_type, data.message);
        }
        form.trigger('reinitialize.areYouSure');
        if ($('#tour-modal').is(':visible')) {
            _tour_append_html(data.tourHtml);
        }
        reload_reminders_tables();
    });

    if ($('body').hasClass('all-reminders')) {
        $('.reminder-modal--').modal('hide');
    } else {
        $('.reminder-modal-' + form.find('[name="rel_type"]').val() + '-' + form.find('[name="rel_id"]').val()).modal('hide');
    }

    return false;
}

// Init lead for add/edit/view or refresh data
function init_lead(id, isEdit) {
    if ($('#tour-modal').is(':visible')) {
        $('#tour-modal').modal('hide');
    }
    // In case header error
    if (init_lead_modal_data(id, undefined, isEdit)) {
        $('#lead-modal').modal('show');
    }
}

// Fix tour checklist content textarea height
function do_tour_checklist_items_height(tour_checklist_items) {
    if (typeof (tour_checklist_items) == 'undefined') {
        tour_checklist_items = $("body").find("textarea[name='checklist-description']");
    }

    $.each(tour_checklist_items, function () {
        var val = $(this).val();
        if ($(this).outerHeight() < this.scrollHeight + parseFloat($(this).css("borderTopWidth")) + parseFloat($(this).css("borderBottomWidth"))) {
            $(this).height(0).height(this.scrollHeight);
        }
        if (val === '') {
            $(this).removeAttr('style');
        }
    });
}

// Recalculate tour checklist items progress, this progress is shown only if there is more then 2 checklist items
function recalculate_checklist_items_progress() {
    var total_finished = $('input[name="checklist-box"]:checked').length;
    var total_checklist_items = $('input[name="checklist-box"]').length;
    var percent = 0,
        tour_progress_bar = $('.tour-progress-bar');
    if (total_checklist_items == 0) {
        // remove the heading for checklist items
        $("body").find('.chk-heading').remove();
        $('#tour-no-checklist-items').removeClass('hide');
    } else {
        $('#tour-no-checklist-items').addClass('hide');
    }
    if (total_checklist_items > 2) {
        tour_progress_bar.parents('.progress').removeClass('hide');
        percent = (total_finished * 100) / total_checklist_items;
    } else {
        tour_progress_bar.parents('.progress').addClass('hide');
        return false;
    }
    tour_progress_bar.css('width', percent.toFixed(2) + '%');
    tour_progress_bar.text(percent.toFixed(2) + '%');
}

// Remove tour checklist items template
function remove_checklist_item_template(id) {
    requestGetJSON('booking/tours/remove_checklist_item_template/' + id).done(function (response) {
        if (response.success === true || response.success == 'true') {
            var itemsTemplateSelect = $("body").find('select.checklist-items-template-select');
            var deletedItemDescription = itemsTemplateSelect.find('option[value="' + id + '"]').html().trim();
            var currentChecklists = $('#tour-modal .checklist');
            $.each(currentChecklists, function (i, area) {
                var checkList = $(area);
                if (checkList.find('textarea[name="checklist-description"]').val().trim() == deletedItemDescription) {
                    checkList.find('.save-checklist-template').removeClass('hide');
                }
            });
            itemsTemplateSelect.find('option[value="' + id + '"]').remove();
            itemsTemplateSelect.selectpicker('refresh');
            if (itemsTemplateSelect.find('option').length === 1) {
                itemsTemplateSelect.selectpicker('destroy');
                $('.checklist-templates-wrapper').addClass('hide');
            }
        }
    });
}

// New tour checklist items template
function save_checklist_item_template(id, field) {
    var description = $('.checklist[data-checklist-id="' + id + '"] textarea').val();
    $.post(admin_url + 'booking/tours/save_checklist_item_template', {
        description: description
    }).done(function (response) {
        response = JSON.parse(response);
        $(field).addClass('hide');
        var singleChecklistTemplate = $('.checklist-templates-wrapper');
        singleChecklistTemplate.find('select option[value=""]').after('<option value="' + response.id + '">' + description.trim() + '</option>');
        singleChecklistTemplate.removeClass('hide');
        singleChecklistTemplate.find('select').selectpicker('refresh');
    });
}

// Updates tour checklist items order
function update_checklist_order() {
    var order = [];
    var items = $("body").find('.checklist');
    if (items.length === 0) {
        return;
    }
    var i = 1;
    $.each(items, function () {
        order.push([$(this).data('checklist-id'), i]);
        i++;
    });
    var data = {};
    data.order = order;
    $.post(admin_url + 'booking/tours/update_checklist_order', data);
}

// New tour checklist item
function add_tour_checklist_item(tour_id, description, e) {
    if (e) {
        $(e).addClass('disabled');
    }

    description = typeof (description) == 'undefined' ? '' : description;

    $.post(admin_url + 'booking/tours/add_checklist_item', {
        tourid: tour_id,
        description: description
    }).done(function () {
        init_tours_checklist_items(true, tour_id);
    }).always(function () {
        if (e) {
            $(e).removeClass('disabled');
        }
    })
}

function update_tour_checklist_item(textArea) {
    var deferred = $.Deferred();
    setTimeout(function () {
        var description = textArea.val();
        description = description.trim();
        var listid = textArea.parents('.checklist').data('checklist-id');

        $.post(admin_url + 'booking/tours/update_checklist_item', {
            description: description,
            listid: listid
        }).done(function (response) {
            deferred.resolve();
            response = JSON.parse(response);
            if (response.can_be_template === true) {
                textArea.parents('.checklist').find('.save-checklist-template').removeClass('hide');
            }
            if (description === '') {
                $('#checklist-items').find('.checklist[data-checklist-id="' + listid + '"]').remove();
            }
        });
    }, 300);
    return deferred.promise();
}

// Remove tour checklist item from the tour
function delete_checklist_item(id, field) {
    requestGetJSON('booking/tours/delete_checklist_item/' + id).done(function (response) {
        if (response.success === true || response.success == 'true') {
            $(field).parents('.checklist').remove();
            recalculate_checklist_items_progress();
        }
    });
}

// Fetches tour checklist items.
function init_tours_checklist_items(is_new, tour_id) {
    $.post(admin_url + 'booking/tours/init_checklist_items', {
        tourid: tour_id
    }).done(function (data) {
        $('#checklist-items').html(data);
        if (typeof (is_new) != 'undefined') {
            var first = $('#checklist-items').find('.checklist textarea').eq(0);
            if (first.val() === '') {
                first.focus();
            }
        }
        recalculate_checklist_items_progress();
        update_checklist_order();
    });
}

function _tour_attachments_more_and_less_checks() {
    var att_wrap = $("body").find('.tour_attachments_wrapper');
    var attachments = att_wrap.find('.tour-attachment-col');
    var tourAttachmentsMore = $("body").find('#show-more-less-tour-attachments-col .tour-attachments-more');
    if (attachments.length === 0) {
        att_wrap.remove();
    } else if (attachments.length == 2 && tourAttachmentsMore.hasClass('hide')) {
        $("body").find('#show-more-less-tour-attachments-col').remove();
    } else if ($('.tour_attachments_wrapper .tour-attachment-col:visible').length === 0 && !tourAttachmentsMore.hasClass('hide')) {
        tourAttachmentsMore.click();
    }

    $.each($('#tour-modal .comment-content'), function () {
        if ($(this).find('.tour-attachment-col').length === 0) {
            $(this).find('.download-all').remove();
        }
    });
}

// Removes tour single attachment
function remove_tour_attachment(link, id) {
    if (confirm_delete()) {
        requestGetJSON('booking/tours/remove_tour_attachment/' + id).done(function (response) {
            if (response.success === true || response.success == 'true') {
                $('[data-tour-attachment-id="' + id + '"]').remove();
            }
            _tour_attachments_more_and_less_checks();
            if (response.comment_removed) {
                $('#comment_' + response.comment_removed).remove();
            }
        });
    }
}

// Add new tour comment from the modal
function add_tour_comment(tour_id) {
    var data = {};

    if (tourCommentAttachmentDropzone.files.length > 0) {
        tourCommentAttachmentDropzone.processQueue(tour_id);
        return;
    }
    if (tinymce.activeEditor) {
        data.content = tinyMCE.activeEditor.getContent();
    } else {
        data.content = $('#tour_comment').val();
        data.no_editor = true;
    }
    data.tourid = tour_id;
    $.post(admin_url + 'booking/tours/add_tour_comment', data).done(function (response) {
        response = JSON.parse(response);
        _tour_append_html(response.tourHtml);
        // Remove tour comment editor instance
        // Causing error because of are you sure you want to leave this page, the plugin still sees as active and dirty.
        tinymce.remove('#tour_comment');
    });
}

// Deletes tour comment from database
function remove_tour_comment(commentid) {
    if (confirm_delete()) {
        requestGetJSON('booking/tours/remove_comment/' + commentid).done(function (response) {
            if (response.success === true || response.success == 'true') {
                $('[data-commentid="' + commentid + '"]').remove();
                $('[data-comment-attachment="' + commentid + '"]').remove();
                _tour_attachments_more_and_less_checks();
            }
        });
    }
}

// Remove tour assignee
function remove_assignee(id, tour_id) {
    if (confirm_delete()) {
        requestGetJSON('booking/tours/remove_assignee/' + id + '/' + tour_id).done(function (response) {
            if (response.success === true || response.success == 'true') {
                alert_float('success', response.message);
                _tour_append_html(response.tourHtml);
            }
        });
    }
}

// Remove tour follower
function remove_follower(id, tour_id) {
    if (confirm_delete()) {
        requestGetJSON('booking/tours/remove_follower/' + id + '/' + tour_id).done(function (response) {
            if (response.success === true || response.success == 'true') {
                alert_float('success', response.message);
                _tour_append_html(response.tourHtml);
            }
        });
    }
}

// Marking tour as complete
function mark_complete(tour_id) {
    tour_mark_as(5, tour_id);
}

// Unmarking tour as complete
function unmark_complete(tour_id) {
    tour_mark_as(4, tour_id, 'booking/tours/unmark_complete/' + tour_id);
}

// Mark tour status
function tour_mark_as(status, tour_id, url) {
    url = typeof (url) == 'undefined' ? 'booking/tours/mark_as/' + status + '/' + tour_id : url;
    var tourModalVisible = $('#tour-modal').is(':visible');
    url += '?single_tour=' + tourModalVisible;
    $("body").append('<div class="dt-loader"></div>');
    requestGetJSON(url).done(function (response) {
        $("body").find('.dt-loader').remove();
        if (response.success === true || response.success == 'true') {
            reload_tours_tables();
            if (tourModalVisible) {
                _tour_append_html(response.tourHtml);
            }
            if (status == 5 && typeof (_maybe_remove_tour_from_booking_milestone) == 'function') {
                _maybe_remove_tour_from_booking_milestone(tour_id);
            }
            if ($('.tours-kanban').length === 0) {
                alert_float('success', response.message);
            }
        }
    });
}

// Change tour priority from sigle modal
function tour_change_priority(priority_id, tour_id) {
    url = 'booking/tours/change_priority/' + priority_id + '/' + tour_id;
    var tourModalVisible = $('#tour-modal').is(':visible');
    url += '?single_tour=' + tourModalVisible;
    requestGetJSON(url).done(function (response) {
        if (response.success === true || response.success == 'true') {
            reload_tours_tables();
            if (tourModalVisible) {
                _tour_append_html(response.tourHtml);
            }
        }
    });
}

// Change tour milestone from single modal
function tour_change_milestone(milestone_id, tour_id) {
    url = 'booking/tours/change_milestone/' + milestone_id + '/' + tour_id;
    var tourModalVisible = $('#tour-modal').is(':visible');
    url += '?single_tour=' + tourModalVisible;
    requestGetJSON(url).done(function (response) {
        if (response.success === true || response.success == 'true') {
            reload_tours_tables();
            if (tourModalVisible) {
                _tour_append_html(response.tourHtml);
            }
        }
    });
}

// Non finished timesheet delete, this is available for all staff
function delete_user_unfinished_timesheet(id) {
    if (confirm_delete()) {
        requestGetJSON('booking/tours/delete_user_unfinished_timesheet/' + id).done(function (response) {
            _init_timers_top_html(JSON.parse(response.timers));
            reload_tours_tables();
        });
    }
}

// Reload all tours possible table where the table data needs to be refreshed after an action is performed on tour.
function reload_tours_tables() {
    var av_tours_tables = ['.table-tours', '.table-rel-tours', '.table-rel-tours-leads', '.table-timesheets', '.table-timesheets-report'];
    $.each(av_tours_tables, function (i, selector) {
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().ajax.reload(null, false);
        }
    });
}

// Makes tour public with AJAX request
function make_tour_public(tour_id) {
    requestGetJSON('booking/tours/make_public/' + tour_id).done(function (response) {
        if (response.success === true || response.success == 'true') {
            reload_tours_tables();
            _tour_append_html(response.tourHtml);
        }
    });
}

// New tour function, various actions performed
function new_tour(url, timer_id) {
    url = typeof (url) != 'undefined' ? url : admin_url + 'booking/tours/tour';

    var $leadModal = $('#lead-modal');
    if ($leadModal.is(':visible')) {
        url += '&opened_from_lead_id=' + $leadModal.find('input[name="leadid"]').val();
        if (url.indexOf('?') === -1) {
            url = url.replace('&', '?');
        }
        $leadModal.modal('hide');
    }

    var $tourSingleModal = $('#tour-modal');
    if ($tourSingleModal.is(':visible')) {
        $tourSingleModal.modal('hide');
    }

    var $tourEditModal = $('#_tour_modal');
    if ($tourEditModal.is(':visible')) {
        $tourEditModal.modal('hide');
    }

    requestGet(url).done(function (response) {
        $('#_tour').html(response);
        $("body").find('#_tour_modal').modal({
            show: true,
            backdrop: 'static'
        });

        var stopTimerPopover = $('#timer-select-tour');
        if (stopTimerPopover.is(':visible')) {
            $('.system-popup-close').click();
            window._timer_id = timer_id;
        }

    }).fail(function (error) {
        alert_float('danger', error.responseText);
    })
}

// Create new tour directly from relation, related options selected after modal is shown
function new_tour_from_relation(table, rel_type, rel_id) {
    if (typeof (rel_type) == 'undefined' && typeof (rel_id) == 'undefined') {
        rel_id = $(table).data('new-rel-id');
        rel_type = $(table).data('new-rel-type');
    }
    var url = admin_url + 'booking/tours/tour?rel_id=' + rel_id + '&rel_type=' + rel_type;
    new_tour(url);
}

// Go to edit view
function edit_tour(tour_id) {
    requestGet('booking/tours/tour/' + tour_id).done(function (response) {
        $('#_tour').html(response);
        $('#tour-modal').modal('hide');
        $("body").find('#_tour_modal').modal({
            show: true,
            backdrop: 'static'
        });
    });
}

// Handles tour add/edit form modal.
function tour_form_handler(form) {

    tinymce.triggerSave();

    $('#_tour_modal').find('input[name="startdate"]').prop('disabled', false);
    // Disable the save button in cases od duplicate clicks
    $('#_tour_modal').find('button[type="submit"]').prop('disabled', true);

    $("#_tour_modal input[type=file]").each(function () {
        if ($(this).val() === "") {
            $(this).prop('disabled', true);
        }
    });

    var formURL = form.action;
    var formData = new FormData($(form)[0]);

    $.ajax({
        type: $(form).attr('method'),
        data: formData,
        mimeType: $(form).attr('enctype'),
        contentType: false,
        cache: false,
        processData: false,
        url: formURL
    }).done(function (response) {
        response = JSON.parse(response);
        if (response.success === true || response.success == 'true') {
            alert_float('success', response.message);
        }

        if (window._timer_id) {
            requestGet(admin_url + 'booking/tours/get_tour_by_id/' + response.id).done(function (response) {
                $('[data-timer-id="' + window._timer_id + '"').click();
                response = JSON.parse(response);
                var option = '<option value="' + response.id + '" title="' + response.name + '" selected>' + response.name + '</option>';
                $('#timer_add_tour_id').append(option);
                $('#timer_add_tour_id').trigger('change').data('AjaxBootstrapSelect').list.cache = {};
                $('#timer_add_tour_id').selectpicker('refresh')
                delete window._timer_id;
            });
            $('#_tour_modal').modal('hide');
            $('#tour-modal').modal('hide');
            return false;
        }

        if (!$("body").hasClass('booking')) {
            $('#_tour_modal').attr('data-tour-created', true);
            $('#_tour_modal').modal('hide');
            init_tour_modal(response.id);
            reload_tours_tables();
            if ($('body').hasClass('kan-ban-body') && $('body').hasClass('tours')) {
                tours_kanban();
            }
        } else {
            // reload page on booking area
            var location = window.location.href;
            var params = [];
            location = location.split('?');
            var group = get_url_param('group');
            var excludeCompletedTours = get_url_param('exclude_completed');
            if (group) {
                params['group'] = group;
            }
            if (excludeCompletedTours) {
                params['exclude_completed'] = excludeCompletedTours;
            }
            params['tourid'] = response.id;
            window.location.href = buildUrl(location[0], params);
        }
    }).fail(function (error) {
        alert_float('danger', JSON.parse(error.responseText));
    });

    return false;
}

// Action for tour timer start/stop
function timer_action(e, tour_id, timer_id, adminStop) {

    timer_id = typeof (timer_id) == 'undefined' ? '' : timer_id;

    var $timerSelectTour = $('#timer-select-tour');
    if (tour_id === '' && $timerSelectTour.is(':visible')) {
        return;
    }
    if (timer_id !== '' && tour_id == '0') {
        var popupData = {};
        popupData.content = '';
        popupData.content += '<div class="row">';
        popupData.content += '<div class="form-group">';
        if (app.options.has_permission_create_tour == '1') {
            popupData.content += '<div class="input-group" style="margin:0 auto;width:60%;">';
        }
        popupData.content += '<select id="timer_add_tour_id" data-empty-title="' + app.lang.search_tours + '" data-width="60%" class="ajax-search" data-live-search="true">';
        popupData.content += '</select>';
        if (app.options.has_permission_create_tour == '1') {
            popupData.content += '<div class="input-group-addon" style="opacity: 1;">';
            popupData.content += '<a href="#" onclick="new_tour(\'booking/tours/tour\',' + timer_id + '); return false;"><i class="fa fa-plus"></i></a>';
            popupData.content += '</div>';
        }
        popupData.content += '</div></div>';
        popupData.content += '<div class="form-group">';
        popupData.content += '<textarea id="timesheet_note" placeholder="' + app.lang.note + '" style="margin:0 auto;width:60%;" rows="4" class="form-control"></textarea>';
        popupData.content += '</div>';
        popupData.content += '<button type=\'button\' onclick=\'timer_action(this,document.getElementById("timer_add_tour_id").value,' + timer_id + ');return false;\' class=\'btn btn-info\'>' + app.lang.confirm + '</button>';
        popupData.message = app.lang.tour_stop_timer;
        var $popupHTML = system_popup(popupData);
        $popupHTML.attr('id', 'timer-select-tour');
        init_ajax_search('tours', '#timer_add_tour_id', undefined, admin_url + 'booking/tours/ajax_search_assign_tour_to_timer');
        return false;
    }

    $(e).addClass('disabled');

    var data = {};
    data.tour_id = tour_id;
    data.timer_id = timer_id;
    data.note = $("body").find('#timesheet_note').val();
    if (!data.note) {
        data.note = '';
    }
    var tourModalVisible = $('#tour-modal').is(':visible');
    var reqUrl = admin_url + 'booking/tours/timer_tracking?single_tour=' + tourModalVisible;
    if (adminStop) {
        reqUrl += '&admin_stop=' + adminStop;
    }
    $.post(reqUrl, data).done(function (response) {
        response = JSON.parse(response);

        // Timer action, stopping from staff/member/id
        if ($('body').hasClass('member')) {
            window.location.reload();
        }

        if (tourModalVisible) {
            _tour_append_html(response.tourHtml);
        }

        if ($timerSelectTour.is(':visible')) {
            $timerSelectTour.find('.system-popup-close').click();
        }

        _init_timers_top_html(JSON.parse(response.timers));

        $('.popover-top-timer-note').popover('hide');
        reload_tours_tables();
    });
}

// Init tour modal and get data from server
function init_tour_modal(tour_id, comment_id) {

    var queryStr = '';
    var $leadModal = $('#lead-modal');
    var $tourAddEditModal = $('#_tour_modal');
    if ($leadModal.is(':visible')) {
        queryStr += '?opened_from_lead_id=' + $leadModal.find('input[name="leadid"]').val();
        $leadModal.modal('hide');
    } else if ($tourAddEditModal.attr('data-lead-id') != undefined) {
        queryStr += '?opened_from_lead_id=' + $tourAddEditModal.attr('data-lead-id');
    }

    requestGet('booking/tours/get_tour_data/' + tour_id + queryStr).done(function (response) {
        _tour_append_html(response);
        if (typeof (comment_id) != 'undefined') {
            setTimeout(function () {
                $('[data-tour-comment-href-id="' + comment_id + '"]').click();
            }, 1000);
        }
    }).fail(function (data) {
        $('#tour-modal').modal('hide');
        alert_float('danger', data.responseText);
    });
}

// General function to append tour html returned from request
function _tour_append_html(html) {

    var $tourModal = $('#tour-modal');

    $tourModal.find('.data').html(html);
    //init_tours_checklist_items(false, tour_id);
    recalculate_checklist_items_progress();
    do_tour_checklist_items_height();

    setTimeout(function () {
        $tourModal.modal('show');
        // Init_tags_input is trigged too when tour modal is shown
        // This line prevents triggering twice.
        if ($tourModal.is(':visible')) {
            init_tags_inputs();
        }
        init_form_reminder('tour');
        fix_tour_modal_left_col_height();

        // Show the comment area on mobile when tour modal is opened
        // Because the user may want only to upload file, but if the comment textarea is not focused the dropzone won't be shown

        if (is_mobile()) {
            init_new_tour_comment(true);
        }

    }, 150);
}

// Tracking stats modal from tour single
function tour_tracking_stats(id) {
    requestGet('booking/tours/tour_tracking_stats/' + id).done(function (response) {
        $('<div/>', {
            id: 'tracking-stats'
        }).appendTo('body').html(response);
        $('#tour-tracking-stats-modal').modal('toggle');
    });
}

// Fetches all staff timers and append to DOM
function init_timers() {
    requestGetJSON('booking/tours/get_staff_started_timers').done(function (response) {
        _init_timers_top_html(response);
    });
}

// Init tour edit comment
function edit_tour_comment(id) {
    var edit_wrapper = $('[data-edit-comment="' + id + '"]');
    edit_wrapper.next().addClass('hide');
    edit_wrapper.removeClass('hide');

    if (!is_ios()) {
        tinymce.remove('#tour_comment_' + id);
        var editorConfig = _simple_editor_config();
        editorConfig.auto_focus = 'tour_comment_' + id;
        init_editor('#tour_comment_' + id, editorConfig);
        tinymce.triggerSave();
    }
}

// Save tour edited comment
function save_edited_comment(id, tour_id) {
    tinymce.triggerSave();
    var data = {};
    data.id = id;
    data.tour_id = tour_id;
    data.content = $('[data-edit-comment="' + id + '"]').find('textarea').val();
    if (is_ios()) {
        data.no_editor = true;
    }
    $.post(admin_url + 'booking/tours/edit_comment', data).done(function (response) {
        response = JSON.parse(response);
        if (response.success === true || response.success == 'true') {
            alert_float('success', response.message);
            _tour_append_html(response.tourHtml);
        } else {
            cancel_edit_comment(id);
        }
        tinymce.remove('[data-edit-comment="' + id + '"] textarea');
    });
}

// Fix tour single modal height to be on both sides the same
function fix_tour_modal_left_col_height() {
    if (!is_mobile()) {
        $("body").find('.tour-single-col-left').css('min-height', $("body").find('.tour-single-col-right').outerHeight(true) + 'px');
    }
}

// Updates tour when action performed form kan ban area eq status changed.
function tours_kanban_update(ui, object) {
    if (object === ui.item.parent()[0]) {
        var status = $(ui.item.parent()[0]).data('tour-status-id');
        var tours = $(ui.item.parent()[0]).find('[data-tour-id]');

        var data = {};
        data.order = [];
        var i = 0;
        $.each(tours, function () {
            data.order.push([$(this).data('tour-id'), i]);
            i++;
        });

        tour_mark_as(status, $(ui.item).data('tour-id'));
        check_kanban_empty_col('[data-tour-id]');
        setTimeout(function () {
            $.post(admin_url + 'booking/tours/update_order', data);
        }, 200);
    }
}

// Init tours kan ban
function tours_kanban() {
    init_kanban('booking/tours/kanban', tours_kanban_update, '.tours-status', 265, 360);
}

// Tour single edit description with inline editor, used from tour single modal
function edit_tour_inline_description(e, id) {

    tinyMCE.remove('#tour_view_description');

    if ($(e).hasClass('editor-initiated')) {
        $(e).removeClass('editor-initiated');
        return;
    }

    $(e).addClass('editor-initiated');
    $.Shortcuts.stop();
    tinymce.init({
        selector: '#tour_view_description',
        theme: 'inlite',
        skin: 'perfex',
        auto_focus: "tour_view_description",
        plugins: 'table link paste contextmenu textpattern',
        contextmenu: "link table paste pastetext",
        insert_toolbar: 'quicktable',
        selection_toolbar: 'bold italic | quicklink h2 h3 blockquote',
        inline: true,
        table_default_styles: {
            width: '100%'
        },
        setup: function (editor) {
            editor.on('blur', function (e) {
                if (editor.isDirty()) {
                    $.post(admin_url + 'booking/tours/update_tour_description/' + id, {
                        description: editor.getContent()
                    });
                }
                setTimeout(function () {
                    editor.remove();
                    $.Shortcuts.start();
                }, 500);
            });
        }
    });
}

// Tours bulk actions action
function tours_bulk_action(event) {
    if (confirm_delete()) {
        var ids = [],
            data = {},
            mass_delete = $('#mass_delete').prop('checked');
        if (mass_delete == false || typeof (mass_delete) == 'undefined') {
            data.status = $('#move_to_status_tours_bulk_action').val();

            var assignees = $('#tour_bulk_assignees');
            data.assignees = assignees.length ? assignees.selectpicker('val') : '';

            var tags_bulk = $('#tags_bulk');
            data.tags = tags_bulk.length ? tags_bulk.tagit('assignedTags') : '';

            var milestone = $('#tour_bulk_milestone');
            data.milestone = milestone.length ? milestone.selectpicker('val') : '';

            data.priority = $('#tour_bulk_priority').val();
            data.priority = typeof (data.priority) == 'undefined' ? '' : data.priority;

            if (data.status === '' && data.priority === '' && data.tags === '' && data.assignees === '' && data.milestone === '') {
                return;
            }
        } else {
            data.mass_delete = true;
        }
        var rows = $($('#tours_bulk_actions').attr('data-table')).find('tbody tr');
        $.each(rows, function () {
            var checkbox = $($(this).find('td').eq(0)).find('input');
            if (checkbox.prop('checked') === true) {
                ids.push(checkbox.val());
            }
        });
        data.ids = ids;
        $(event).addClass('disabled');
        setTimeout(function () {
            $.post(admin_url + 'booking/tours/bulk_action', data).done(function () {
                window.location.reload();
            });
        }, 200);
    }
}

// Add tour to preview
function add_tour_to_preview_as_item(id) {
    requestGetJSON('booking/tours/get_billable_tour_data/' + id).done(function (response) {
        response.taxname = $('select.main-tax').selectpicker('val');
        var previewArea = $('.main');
        previewArea.find('textarea[name="description"]').val(response.name);
        previewArea.find('textarea[name="long_description"]').val(response.description);
        previewArea.find('input[name="quantity"]').val(response.total_hours);
        previewArea.find('input[name="rate"]').val(response.hourly_rate);
        previewArea.find('input[name="unit"]').val('');
        $('input[name="tour_id"]').val(id);
        $(document).trigger({
            type: "item-added-to-preview",
            item: response,
            item_type: 'tour',
        });
    });
}

// Clear the items added to preview
function clear_item_preview_values(default_taxes) {

    // Get the last taxes applied to be available for the next item
    var last_taxes_applied = $('table.items tbody').find('tr:last-child').find('select').selectpicker('val');
    var previewArea = $('.main');

    previewArea.find('textarea').val(''); // includes cf
    previewArea.find('td.custom_field input[type="checkbox"]').prop('checked', false); // cf
    previewArea.find('td.custom_field input:not(:checkbox):not(:hidden)').val(''); // cf // not hidden for chkbox hidden helpers
    previewArea.find('td.custom_field select').selectpicker('val', ''); // cf
    previewArea.find('input[name="quantity"]').val(1);
    previewArea.find('select.tax').selectpicker('val', last_taxes_applied);
    previewArea.find('input[name="rate"]').val('');
    previewArea.find('input[name="unit"]').val('');

    $('input[name="tour_id"]').val('');
    $('input[name="expense_id"]').val('');
}

// Append the added items to the preview to the table as items
function add_item_to_table(data, itemid, merge_invoice, bill_expense) {

    // If not custom data passed get from the preview
    data = typeof (data) == 'undefined' || data == 'undefined' ? get_item_preview_values() : data;
    if (data.description === "" && data.long_description === "" && data.rate === "") {
        return;
    }
    var table_row = '';
    var item_key = $("body").find('tbody .item').length + 1;

    table_row += '<tr class="sortable item" data-merge-invoice="' + merge_invoice + '" data-bill-expense="' + bill_expense + '">';

    table_row += '<td class="dragger">';

    // Check if quantity is number
    if (isNaN(data.qty)) {
        data.qty = 1;
    }

    // Check if rate is number
    if (data.rate === '' || isNaN(data.rate)) {
        data.rate = 0;
    }

    var amount = data.rate * data.qty;

    var tax_name = 'newitems[' + item_key + '][taxname][]';
    $("body").append('<div class="dt-loader"></div>');
    var regex = /<br[^>]*>/gi;
    get_taxes_dropdown_template(tax_name, data.taxname).done(function (tax_dropdown) {

        // order input
        table_row += '<input type="hidden" class="order" name="newitems[' + item_key + '][order]">';

        table_row += '</td>';

        table_row += '<td class="bold description"><textarea name="newitems[' + item_key + '][description]" class="form-control" rows="5">' + data.description + '</textarea></td>';

        table_row += '<td><textarea name="newitems[' + item_key + '][long_description]" class="form-control item_long_description" rows="5">' + data.long_description.replace(regex, "\n") + '</textarea></td>';

        var custom_fields = $('tr.main td.custom_field');
        var cf_has_required = false;

        if (custom_fields.length > 0) {

            $.each(custom_fields, function () {

                var cf = $(this).clone();
                var cf_html = '';
                var cf_field = $(this).find('[data-fieldid]');
                var cf_name = 'newitems[' + item_key + '][custom_fields][items][' + cf_field.attr('data-fieldid') + ']';

                if (cf_field.is(':checkbox')) {

                    var checked = $(this).find('input[type="checkbox"]:checked');
                    var checkboxes = cf.find('input[type="checkbox"]');

                    $.each(checkboxes, function (i, e) {
                        var random_key = Math.random().toString(20).slice(2);
                        $(this).attr('id', random_key)
                            .attr('name', cf_name)
                            .next('label').attr('for', random_key);
                        if ($(this).attr('data-custom-field-required') == '1') {
                            cf_has_required = true;
                        }
                    });

                    $.each(checked, function (i, e) {
                        cf.find('input[value="' + $(e).val() + '"]')
                            .attr('checked', true);
                    });

                    cf_html = cf.html();

                } else if (cf_field.is('input') || cf_field.is('textarea')) {
                    if (cf_field.is('input')) {
                        cf.find('[data-fieldid]').attr('value', cf_field.val());
                    } else {
                        cf.find('[data-fieldid]').html(cf_field.val());
                    }
                    cf.find('[data-fieldid]').attr('name', cf_name);
                    if (cf.find('[data-fieldid]').attr('data-custom-field-required') == '1') {
                        cf_has_required = true;
                    }
                    cf_html = cf.html();
                } else if (cf_field.is('select')) {

                    if ($(this).attr('data-custom-field-required') == '1') {
                        cf_has_required = true;
                    }

                    var selected = $(this).find('select[data-fieldid]').selectpicker('val');
                    selected = typeof (selected != 'array') ? new Array(selected) : selected;

                    // Check if is multidimensional by multi-select customfield
                    selected = selected[0].constructor === Array ? selected[0] : selected;

                    var selectNow = cf.find('select');
                    var $wrapper = $('<div/>');
                    selectNow.attr('name', cf_name);

                    var $select = selectNow.clone();
                    $wrapper.append($select);
                    $.each(selected, function (i, e) {
                        $wrapper.find('select option[value="' + e + '"]').attr('selected', true);
                    });

                    cf_html = $wrapper.html();
                }
                table_row += '<td class="custom_field">' + cf_html + '</td>';
            });
        }

        table_row += '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="newitems[' + item_key + '][qty]" value="' + data.qty + '" class="form-control">';

        if (!data.unit || typeof (data.unit) == 'undefined') {
            data.unit = '';
        }

        table_row += '<input type="text" placeholder="' + app.lang.unit + '" name="newitems[' + item_key + '][unit]" class="form-control input-transparent text-right" value="' + data.unit + '">';

        table_row += '</td>';

        table_row += '<td class="rate"><input type="number" data-toggle="tooltip" title="' + app.lang.item_field_not_formatted + '" onblur="calculate_total();" onchange="calculate_total();" name="newitems[' + item_key + '][rate]" value="' + data.rate + '" class="form-control"></td>';

        table_row += '<td class="taxrate">' + tax_dropdown + '</td>';

        table_row += '<td class="amount" align="right">' + format_money(amount, true) + '</td>';

        table_row += '<td><a href="#" class="btn btn-danger pull-left" onclick="delete_item(this,' + itemid + '); return false;"><i class="fa fa-trash"></i></a></td>';

        table_row += '</tr>';

        $('table.items tbody').append(table_row);

        $(document).trigger({
            type: "item-added-to-table",
            data: data,
            row: table_row
        });

        setTimeout(function () {
            calculate_total();
        }, 15);

        var billed_tour = $('input[name="tour_id"]').val();
        var billed_expense = $('input[name="expense_id"]').val();

        if (billed_tour !== '' && typeof (billed_tour) != 'undefined') {
            billed_tours = billed_tour.split(',');
            $.each(billed_tours, function (i, obj) {
                $('#billed-tours').append(hidden_input('billed_tours[' + item_key + '][]', obj));
            });
        }

        if (billed_expense !== '' && typeof (billed_expense) != 'undefined') {
            billed_expenses = billed_expense.split(',');
            $.each(billed_expenses, function (i, obj) {
                $('#billed-expenses').append(hidden_input('billed_expenses[' + item_key + '][]', obj));
            });
        }

        if ($('#item_select').hasClass('ajax-search') && $('#item_select').selectpicker('val') !== '') {
            $('#item_select').prepend('<option></option>');
        }

        init_selectpicker();
        init_datepicker();
        init_color_pickers();
        clear_item_preview_values();
        reorder_items();

        $('body').find('#items-warning').remove();
        $("body").find('.dt-loader').remove();
        $('#item_select').selectpicker('val', '');

        if (cf_has_required && $('.invoice-form').length) {
            validate_invoice_form();
        } else if (cf_has_required && $('.estimate-form').length) {
            validate_estimate_form();
        } else if (cf_has_required && $('.quote-form').length) {
            validate_quote_form();
        } else if (cf_has_required && $('.credit-note-form').length) {
            validate_credit_note_form();
        }

        return true;

    });

    return false;
}

function init_new_tour_comment(manual) {

    if (tinymce.editors.tour_comment) {
        tinymce.remove('#tour_comment');
    }

    if (typeof (tourCommentAttachmentDropzone) != 'undefined') {
        tourCommentAttachmentDropzone.destroy();
    }

    $('#dropzoneTourComment').removeClass('hide');
    $('#addTourCommentBtn').removeClass('hide');

    tourCommentAttachmentDropzone = new Dropzone("#tour-comment-form", appCreateDropzoneOptions({
        uploadMultiple: true,
        clickable: '#dropzoneTourComment',
        previewsContainer: '.dropzone-tour-comment-previews',
        autoProcessQueue: false,
        addRemoveLinks: true,
        parallelUploads: 20,
        maxFiles: 20,
        paramName: 'file',
        sending: function (file, xhr, formData) {
            formData.append("tourid", $('#addTourCommentBtn').attr('data-comment-tour-id'));
            if (tinyMCE.activeEditor) {
                formData.append("content", tinyMCE.activeEditor.getContent());
            } else {
                formData.append("content", $('#tour_comment').val());
            }
        },
        success: function (files, response) {
            response = JSON.parse(response);
            if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                _tour_append_html(response.tourHtml);
                tinymce.remove('#tour_comment');
            }
        }
    }));

    var editorConfig = _simple_editor_config();
    if (typeof (manual) == 'undefined' || manual === false) {
        editorConfig.auto_focus = true;
    }
    var iOS = is_ios();
    // Not working fine on iOs
    if (!iOS) {
        init_editor('#tour_comment', editorConfig);
    }
}

function tourExternalFileUpload(files, externalType, tourId) {
    $.post(admin_url + 'booking/tours/add_external_attachment', {
        files: files,
        tour_id: tourId,
        external: externalType
    }).done(function () {
        init_tour_modal(tourId);
    });
}

</script>