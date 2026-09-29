<script>

var quotesTemplates = <?php echo json_encode(get_quote_templates()); ?>;

/** Create New Quote **/
add_hotkey('Ctrl+Shift+Q', function () {
    window.location.href = admin_url + 'booking/quotes/quote';
});

// In lead modal issue with reminder modal height
$("body").on("shown.bs.modal", '#sync_data_quote_data', function () {
    if ($('#sync_data_quote_data').data('rel-type') == 'lead') {
        $('#lead-modal .data').eq(0).css('height', ($('#sync_data_quote_data .modal-content').height() + 80) + 'px').css('overflow-x', 'hidden');
    }
});

// Remove on lead modal reminder inline style
$("body").on("hidden.bs.modal", '#sync_data_quote_data', function () {
    if ($('#sync_data_quote_data').data('rel-type') == 'lead') {
        $('#lead-modal .data').prop('style', '');
    }
});

// Init are you sure on forms
$('form').not('#single-ticket-form,#calendar-event-form,#quote-form').areYouSure();

if ($("body").hasClass('quotes-pipeline')) {
    var quote_id = $('input[name="quoteid"]').val();
    quote_pipeline_open(quote_id);
}

// Fetches lead modal data, can be edit/add/view
function init_lead_modal_data(id, url, isEdit) {

    var requestURL = (typeof (url) != 'undefined' ? url : 'booking/leads/lead/') + (typeof (id) != 'undefined' ? id : '');

    if (isEdit === true) {
    var concat = '?';
    if (requestURL.indexOf('?') > -1) {
        concat += '&';
    }
    requestURL += concat + 'edit=true';
    }

    requestGetJSON(requestURL).done(function (response) {
    _lead_init_data(response, id);
    }).fail(function (data) {
    alert_float('danger', data.responseText);
    });
}

// Init lead for add/edit/view or refresh data
function init_lead(id, isEdit) {
    if ($('#task-modal').is(':visible')) {
        $('#task-modal').modal('hide');
    }
    // In case header error
    if (init_lead_modal_data(id, undefined, isEdit)) {
        $('#lead-modal').modal('show');
    }
}

// Updates all quotes emails linked to lead, this wil be executed when eq lead email is changed
function update_all_quote_emails_linked_to_lead(id) {
    $.post(admin_url + 'leads/update_all_quote_emails_linked_to_lead/' + id, {
        update: true
    }).done(function (response) {
        response = JSON.parse(response);
        if (response.success) {
            alert_float('success', response.message);
        }
        init_lead_modal_data(id);
    });
}

function init_quote_editor() {

    tinymce.remove('div.editable');

    var _templates = [];

    $.each(quotesTemplates, function (i, template) {
        _templates.push({
            url: admin_url + 'booking/quotes/get_template?name=' + template,
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
                save_quote_content(true);
            });

            editor.addShortcut('Meta+S', '', 'mceSave');

            editor.on('MouseLeave blur', function () {
                if (tinymce.activeEditor.isDirty()) {
                    save_quote_content();
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
                save_quote_content();
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

function add_quote_comment() {
    var comment = $('#comment').val();
    if (comment == '') {
        return;
    }
    var data = {};
    data.content = comment;
    data.quoteid = quote_id;
    $('body').append('<div class="dt-loader"></div>');
    $.post(admin_url + 'booking/quotes/add_quote_comment', data).done(function (response) {
        response = JSON.parse(response);
        $('body').find('.dt-loader').remove();
        if (response.success == true) {
            $('#comment').val('');
            get_quote_comments();
        }
    });
}

function get_quote_comments() {
    if (typeof (quote_id) == 'undefined') {
        return;
    }
    requestGet('booking/quotes/get_quote_comments/' + quote_id).done(function (response) {
        $('body').find('#quote-comments').html(response);
    });
}

function remove_quote_comment(commentid) {
    if (confirm_delete()) {
        requestGetJSON('booking/quotes/remove_comment/' + commentid).done(function (response) {
            if (response.success == true) {
                $('[data-commentid="' + commentid + '"]').remove();
            }
        });
    }
}

function edit_quote_comment(id) {
    var content = $('body').find('[data-quote-comment-edit-textarea="' + id + '"] textarea').val();
    if (content != '') {
        $.post(admin_url + 'booking/quotes/edit_comment/' + id, {
            content: content
        }).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
                $('body').find('[data-quote-comment="' + id + '"]').html(nl2br(content));
            }
        });
        toggle_quote_comment_edit(id);
    }
}

function toggle_quote_comment_edit(id) {
    $('body').find('[data-quote-comment="' + id + '"]').toggleClass('hide');
    $('body').find('[data-quote-comment-edit-textarea="' + id + '"]').toggleClass('hide');
}

function quote_convert_template(invoker) {
    var template = $(invoker).data('template');
    var html_helper_selector;
    if (template == 'estimate') {
        html_helper_selector = 'estimate';
    } else if (template == 'invoice') {
        html_helper_selector = 'invoice';
    } else {
        return false;
    }

    requestGet('booking/quotes/get_' + html_helper_selector + '_convert_data/' + quote_id).done(function (data) {
        if ($('.quote-pipeline-modal').is(':visible')) {
            $('.quote-pipeline-modal').modal('hide');
        }
        $('#convert_helper').html(data);
        $('#convert_to_' + html_helper_selector).modal({
            show: true,
            backdrop: 'static'
        });
        reorder_items();
    });

}

function save_quote_content(manual) {
    var editor = tinyMCE.activeEditor;
    var data = {};
    data.quote_id = quote_id;
    data.content = editor.getContent();
    $.post(admin_url + 'booking/quotes/save_quote_data', data).done(function (response) {
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

// Quote sync data in case eq mail is changed, shown for lead and customers.
function sync_quotes_data(rel_id, rel_type) {
    var data = {};
    var modal_sync = $('#sync_data_quote_data');
    data.country = modal_sync.find('select[name="country"]').val();
    data.zip = modal_sync.find('input[name="zip"]').val();
    data.state = modal_sync.find('input[name="state"]').val();
    data.city = modal_sync.find('input[name="city"]').val();
    data.address = modal_sync.find('textarea[name="address"]').val();
    data.phone = modal_sync.find('input[name="phone"]').val();
    data.rel_id = rel_id;
    data.rel_type = rel_type;
    $.post(admin_url + 'booking/quotes/sync_data', data).done(function (response) {
        response = JSON.parse(response);
        alert_float('success', response.message);
        modal_sync.modal('hide');
    });
}

// Init single quote
function init_quote(id) {
    load_small_table_item(id, '#quote', 'quote_id', 'booking/quotes/get_quote_data_ajax', '.table-quotes');
}

// Delete quote attachment
function delete_quote_attachment(id) {
    if (confirm_delete()) {
        requestGet('booking/quotes/delete_attachment/' + id).done(function (success) {
            if (success == 1) {
                var rel_id = $("body").find('input[name="_attachment_sale_id"]').val();
                $("body").find('[data-attachment-id="' + id + '"]').remove();
                $("body").hasClass('quotes-pipeline') ? quote_pipeline_open(rel_id) : init_quote(rel_id);
            }
        }).fail(function (error) {
            alert_float('danger', error.responseText);
        });
    }
}

// Sort quotes in the pipeline view / switching sort type by click
function quote_pipeline_sort(type) {
    kan_ban_sort(type, quotes_pipeline);
}

// Used when quote is updated from pipeline. eq changed order or moved to another status
function quotes_pipeline_update(ui, object) {
    if (object === ui.item.parent()[0]) {
        var data = {};
        data.order = [];
        data.quoteid = $(ui.item).data('quote-id');
        data.status = $(ui.item.parent()[0]).data('status-id');
        var status = $(ui.item).parents('.pipeline-status').find('li');

        var i = 1;
        $.each(status, function () {
            data.order.push([$(this).data('quote-id'), i]);
            i++;
        });

        check_kanban_empty_col('[data-quote-id]');
        $.post(admin_url + 'booking/quotes/update_pipeline', data);
    }
}

// Init quotes pipeline
function quotes_pipeline() {
    init_kanban('booking/quotes/get_pipeline', quotes_pipeline_update, '.pipeline-status', 347, 360);
}

// Open single quote in pipeline
function quote_pipeline_open(id) {
    if (id === '') {
        return;
    }
    requestGet('booking/quotes/pipeline_open/' + id).done(function (response) {
        var visible = $('.quote-pipeline-modal:visible').length > 0;
        $('#quote').html(response);
        if (!visible) {
            $('.quote-pipeline-modal').modal({
                show: true,
                backdrop: 'static',
                keyboard: false
            });
        } else {
            $('#quote').find('.modal.quote-pipeline-modal')
                .removeClass('fade')
                .addClass('in')
                .css('display', 'block');
        }
    });
}

// Quote merge field into the editor
function insert_quote_merge_field(field) {
    tinymce.activeEditor.execCommand('mceInsertContent', false, $(field).text());
}

</script>