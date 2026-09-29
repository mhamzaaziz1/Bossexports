<script>

/** Create New Booking **/
add_hotkey('Ctrl+Shift+B', function () {
    window.location.href = admin_url + 'booking/bookings/booking';
});

// In lead modal issue with reminder modal height
$("body").on("shown.bs.modal", '#sync_data_booking_data', function () {
    if ($('#sync_data_booking_data').data('rel-type') == 'lead') {
        $('#lead-modal .data').eq(0).css('height', ($('#sync_data_booking_data .modal-content').height() + 80) + 'px').css('overflow-x', 'hidden');
    }
});

// Remove on lead modal reminder inline style
$("body").on("hidden.bs.modal", '#sync_data_booking_data', function () {
    if ($('#sync_data_booking_data').data('rel-type') == 'lead') {
        $('#lead-modal .data').prop('style', '');
    }
});

// Init are you sure on forms
$('form').not('#single-ticket-form,#calendar-event-form,#booking-form').areYouSure();

if ($("body").hasClass('bookings-pipeline')) {
    var booking_id = $('input[name="bookingid"]').val();
    booking_pipeline_open(booking_id);
}

// Updates all bookings emails linked to lead, this wil be executed when eq lead email is changed
function update_all_booking_emails_linked_to_lead(id) {
    $.post(admin_url + 'leads/update_all_booking_emails_linked_to_lead/' + id, {
        update: true
    }).done(function (response) {
        response = JSON.parse(response);
        if (response.success) {
            alert_float('success', response.message);
        }
        init_lead_modal_data(id);
    });
}

function init_booking_editor() {

    tinymce.remove('div.editable');

    var _templates = [];

    $.each(bookingsTemplates, function (i, template) {
        _templates.push({
            url: admin_url + 'booking/bookings/get_template?name=' + template,
            title: template
        });
    });

    var settings = {
        selector: 'div.editable',
        inline: true,
        theme: 'inlite',
        // skin: 'perfex',
        relative_urls: false,
        remove_script_host: false,
        inline_styles: true,
        verify_html: false,
        cleanup: false,
        apply_source_formatting: false,
        valid_elements: '+*[*]',
        valid_children: "+body[style], +style[type]",
        file_browser_callback: elFinderBrowser,
        table_default_styles: {
            width: '100%'
        },
        fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
        pagebreak_separator: '<p pagebreak="true"></p>',
        plugins: [
            'advlist pagebreak autolink autoresize lists link image charmap hr',
            'searchreplace visualblocks visualchars code',
            'media nonbreaking table contextmenu',
            'paste textcolor colorpicker'
        ],
        autoresize_bottom_margin: 50,
        insert_toolbar: 'image media quicktable | bullist numlist | h2 h3 | hr',
        selection_toolbar: 'save_button bold italic underline superscript | forecolor backcolor link | alignleft aligncenter alignright alignjustify | fontselect fontsizeselect h2 h3',
        contextmenu: "image media inserttable | cell row column deletetable | paste pastetext searchreplace | visualblocks pagebreak charmap | code",
        setup: function (editor) {

            editor.addCommand('mceSave', function () {
                save_booking_content(true);
            });

            editor.addShortcut('Meta+S', '', 'mceSave');

            editor.on('MouseLeave blur', function () {
                if (tinymce.activeEditor.isDirty()) {
                    save_booking_content();
                }
            });

            editor.on('MouseDown ContextMenu', function () {
                if (!is_mobile() && !$('#small-table').hasClass('hide')) {
                    small_table_full_view();
                }
            });

            editor.on('blur', function () {
                $.Shortcuts.start();
            });

            editor.on('focus', function () {
                $.Shortcuts.stop();
            });
        },
    };

    if (is_mobile()) {

        settings.theme = 'modern';
        settings.mobile = {};
        settings.mobile.theme = 'mobile';
        settings.mobile.toolbar = _tinymce_mobile_toolbar();

        settings.inline = false;

        window.addEventListener("beforeunload", function (event) {
            if (tinymce.activeEditor.isDirty()) {
                save_booking_content();
            }
        });
    }

    if (_templates.length > 0) {
        settings.templates = _templates;
        settings.plugins[3] = 'template ' + settings.plugins[3];
        settings.contextmenu = settings.contextmenu.replace('inserttable', 'inserttable template')
    }

    tinymce.init(settings);
}

function add_booking_comment() {
    var comment = $('#comment').val();
    if (comment == '') {
        return;
    }
    var data = {};
    data.content = comment;
    data.bookingid = booking_id;
    $('body').append('<div class="dt-loader"></div>');
    $.post(admin_url + 'booking/bookings/add_booking_comment', data).done(function (response) {
        response = JSON.parse(response);
        $('body').find('.dt-loader').remove();
        if (response.success == true) {
            $('#comment').val('');
            get_booking_comments();
        }
    });
}

function get_booking_comments() {
    if (typeof (booking_id) == 'undefined') {
        return;
    }
    requestGet('bookings/get_booking_comments/' + booking_id).done(function (response) {
        $('body').find('#booking-comments').html(response);
    });
}

function remove_booking_comment(commentid) {
    if (confirm_delete()) {
        requestGetJSON('bookings/remove_comment/' + commentid).done(function (response) {
            if (response.success == true) {
                $('[data-commentid="' + commentid + '"]').remove();
            }
        });
    }
}

function edit_booking_comment(id) {
    var content = $('body').find('[data-booking-comment-edit-textarea="' + id + '"] textarea').val();
    if (content != '') {
        $.post(admin_url + 'booking/bookings/edit_comment/' + id, {
            content: content
        }).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
                $('body').find('[data-booking-comment="' + id + '"]').html(nl2br(content));
            }
        });
        toggle_booking_comment_edit(id);
    }
}

function toggle_booking_comment_edit(id) {
    $('body').find('[data-booking-comment="' + id + '"]').toggleClass('hide');
    $('body').find('[data-booking-comment-edit-textarea="' + id + '"]').toggleClass('hide');
}

function booking_convert_template(invoker) {
    var template = $(invoker).data('template');
    var html_helper_selector;
    if (template == 'estimate') {
        html_helper_selector = 'estimate';
    } else if (template == 'invoice') {
        html_helper_selector = 'invoice';
    } else {
        return false;
    }

    requestGet('bookings/get_' + html_helper_selector + '_convert_data/' + booking_id).done(function (data) {
        if ($('.booking-pipeline-modal').is(':visible')) {
            $('.booking-pipeline-modal').modal('hide');
        }
        $('#convert_helper').html(data);
        $('#convert_to_' + html_helper_selector).modal({
            show: true,
            backdrop: 'static'
        });
        reorder_items();
    });

}

function save_booking_content(manual) {
    var editor = tinyMCE.activeEditor;
    var data = {};
    data.booking_id = booking_id;
    data.content = editor.getContent();
    $.post(admin_url + 'booking/bookings/save_booking_data', data).done(function (response) {
        response = JSON.parse(response);
        if (typeof (manual) != 'undefined') {
            // Show some message to the user if saved via CTRL + S
            alert_float('success', response.message);
        }
        // Invokes to set dirty to false
        editor.save();
    }).fail(function (error) {
        var response = JSON.parse(error.responseText);
        alert_float('danger', response.message);
    });
}

// Booking sync data in case eq mail is changed, shown for lead and customers.
function sync_bookings_data(rel_id, rel_type) {
    var data = {};
    var modal_sync = $('#sync_data_booking_data');
    data.country = modal_sync.find('select[name="country"]').val();
    data.zip = modal_sync.find('input[name="zip"]').val();
    data.state = modal_sync.find('input[name="state"]').val();
    data.city = modal_sync.find('input[name="city"]').val();
    data.address = modal_sync.find('textarea[name="address"]').val();
    data.phone = modal_sync.find('input[name="phone"]').val();
    data.rel_id = rel_id;
    data.rel_type = rel_type;
    $.post(admin_url + 'booking/bookings/sync_data', data).done(function (response) {
        response = JSON.parse(response);
        alert_float('success', response.message);
        modal_sync.modal('hide');
    });
}

// Init single booking
function init_booking(id) {
    load_small_table_item(id, '#booking', 'booking_id', 'bookings/get_booking_data_ajax', '.table-bookings');
}

// Delete booking attachment
function delete_booking_attachment(id) {
    if (confirm_delete()) {
        requestGet('bookings/delete_attachment/' + id).done(function (success) {
            if (success == 1) {
                var rel_id = $("body").find('input[name="_attachment_sale_id"]').val();
                $("body").find('[data-attachment-id="' + id + '"]').remove();
                $("body").hasClass('bookings-pipeline') ? booking_pipeline_open(rel_id) : init_booking(rel_id);
            }
        }).fail(function (error) {
            alert_float('danger', error.responseText);
        });
    }
}

// Sort bookings in the pipeline view / switching sort type by click
function booking_pipeline_sort(type) {
    kan_ban_sort(type, bookings_pipeline);
}

// Used when booking is updated from pipeline. eq changed order or moved to another status
function bookings_pipeline_update(ui, object) {
    if (object === ui.item.parent()[0]) {
        var data = {};
        data.order = [];
        data.bookingid = $(ui.item).data('booking-id');
        data.status = $(ui.item.parent()[0]).data('status-id');
        var status = $(ui.item).parents('.pipeline-status').find('li');

        var i = 1;
        $.each(status, function () {
            data.order.push([$(this).data('booking-id'), i]);
            i++;
        });

        check_kanban_empty_col('[data-booking-id]');
        $.post(admin_url + 'booking/bookings/update_pipeline', data);
    }
}

// Init bookings pipeline
function bookings_pipeline() {
    init_kanban('bookings/get_pipeline', bookings_pipeline_update, '.pipeline-status', 347, 360);
}

// Open single booking in pipeline
function booking_pipeline_open(id) {
    if (id === '') {
        return;
    }
    requestGet('bookings/pipeline_open/' + id).done(function (response) {
        var visible = $('.booking-pipeline-modal:visible').length > 0;
        $('#booking').html(response);
        if (!visible) {
            $('.booking-pipeline-modal').modal({
                show: true,
                backdrop: 'static',
                keyboard: false
            });
        } else {
            $('#booking').find('.modal.booking-pipeline-modal')
                .removeClass('fade')
                .addClass('in')
                .css('display', 'block');
        }
    });
}

// Booking merge field into the editor
function insert_booking_merge_field(field) {
    tinymce.activeEditor.execCommand('mceInsertContent', false, $(field).text());
}

</script>