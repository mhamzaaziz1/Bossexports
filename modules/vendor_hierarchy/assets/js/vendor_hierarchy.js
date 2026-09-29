/**
 * Vendor Hierarchy Module — JavaScript
 */
(function($) {
    'use strict';

    // ---------- Tree Toggle (Profile & Reports) ----------
    $(document).on('click', '.vh-tree-toggle', function(e) {
        e.stopPropagation();
        var $li = $(this).closest('.vh-tree-node');
        $li.find('> .vh-tree').toggleClass('vh-tree-collapsed');
        $(this).find('i').toggleClass('fa-caret-down fa-caret-right');
    });

    // Expand All
    $(document).on('click', '#vh-expand-all, #vh-report-expand-all', function() {
        $('.vh-tree').removeClass('vh-tree-collapsed');
        $('.vh-tree-toggle i').removeClass('fa-caret-right').addClass('fa-caret-down');
    });

    // Collapse All
    $(document).on('click', '#vh-collapse-all, #vh-report-collapse-all', function() {
        $('.vh-tree .vh-tree').addClass('vh-tree-collapsed');
        $('.vh-tree .vh-tree-toggle i').removeClass('fa-caret-down').addClass('fa-caret-right');
    });

    // ---------- Parent Vendor Validation ----------
    $(document).on('change', '#parent_vendor_id', function() {
        var $select = $(this);
        var parentId = $select.val();
        var vendorId = $('input[name="userid"]').val() || 0;
        var $msg = $('#vh-validation-msg');

        if (!parentId || parentId === '' || parentId == '0') {
            $msg.removeClass('vh-error vh-success').hide();
            return;
        }

        // AJAX validate
        $.ajax({
            url: admin_url + 'vendor_hierarchy/validate_parent',
            type: 'POST',
            data: {
                vendor_id: vendorId,
                parent_vendor_id: parentId
            },
            dataType: 'json',
            beforeSend: function() {
                $msg.removeClass('vh-error vh-success')
                    .html('<i class="fa fa-spinner fa-spin mright5"></i>Validating...')
                    .show();
            },
            success: function(response) {
                if (response.valid) {
                    $msg.removeClass('vh-error').addClass('vh-success')
                        .html('<i class="fa fa-check-circle mright5"></i>Valid parent assignment.')
                        .show();
                    // Auto-hide the success message
                    setTimeout(function() { $msg.fadeOut(); }, 3000);
                } else {
                    $msg.removeClass('vh-success').addClass('vh-error')
                        .html('<i class="fa fa-exclamation-triangle mright5"></i>' + response.message)
                        .show();
                    // Reset the select
                    $select.val('').selectpicker('refresh');
                }
            },
            error: function() {
                $msg.removeClass('vh-success').addClass('vh-error')
                    .html('<i class="fa fa-times mright5"></i>Validation request failed.')
                    .show();
            }
        });
    });

    // ---------- Form submission: add parent_vendor_id to vendor form ----------
    $(document).on('submit', '.vendor-form', function(e) {
        var parentVal = $('#parent_vendor_id').val();
        // The hidden field is already in the form, just let it submit normally
    });

})(jQuery);
