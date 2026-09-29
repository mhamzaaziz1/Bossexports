<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <h4 class="no-margin"><i class="fa fa-cubes"></i> Sourcing Locator</h4>
                        <hr class="hr-panel-heading" />

                        <!-- ===== Filter Row ===== -->
                        <div class="row" id="vs_filter_row">

                            <div class="col-md-2">
                                <label class="control-label">Item Name</label>
                                <select id="filter_item_name" class="selectpicker" data-width="100%" data-live-search="true" title="All Items">
                                    <option value=""></option>
                                    <?php foreach ($filter_items as $item): ?>
                                        <option value="<?php echo htmlspecialchars($item['item_name']); ?>">
                                            <?php echo htmlspecialchars($item['item_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="control-label">Item SKU</label>
                                <select id="filter_sku" class="selectpicker" data-width="100%" data-live-search="true" title="All SKUs">
                                    <option value=""></option>
                                    <?php foreach ($filter_skus as $row): ?>
                                        <option value="<?php echo htmlspecialchars($row['sku']); ?>">
                                            <?php echo htmlspecialchars($row['sku']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="control-label">Vendor</label>
                                <select id="filter_vendor_name" class="selectpicker" data-width="100%" data-live-search="true" title="All Vendors">
                                    <option value=""></option>
                                    <?php foreach ($filter_vendors as $vendor): ?>
                                        <option value="<?php echo htmlspecialchars($vendor['company']); ?>">
                                            <?php echo htmlspecialchars($vendor['company']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="control-label">Vendor SKU</label>
                                <select id="filter_vendor_sku" class="selectpicker" data-width="100%" data-live-search="true" title="All Vendor SKUs">
                                    <option value=""></option>
                                    <?php foreach ($filter_vendor_skus as $row): ?>
                                        <option value="<?php echo htmlspecialchars($row['vendor_item_code']); ?>">
                                            <?php echo htmlspecialchars($row['vendor_item_code']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="control-label">Category</label>
                                <select id="filter_category" class="selectpicker" data-width="100%" data-live-search="true" title="All Categories">
                                    <option value=""></option>
                                    <?php foreach ($filter_categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['name']); ?>">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-1">
                                <label class="control-label">Min Price</label>
                                <input type="number" id="filter_price_min" class="form-control input-sm" placeholder="0" step="any" min="0">
                            </div>

                            <div class="col-md-1">
                                <label class="control-label">Max Price</label>
                                <input type="number" id="filter_price_max" class="form-control input-sm" placeholder="∞" step="any" min="0">
                            </div>

                        </div><!-- /.row filters -->

                        <!-- Action buttons -->
                        <div class="row mtop10 mbot15">
                            <div class="col-md-12">
                                <button type="button" id="vs_search_btn" class="btn btn-primary btn-sm">
                                    <i class="fa fa-search"></i> Search
                                </button>
                                <button type="button" id="vs_reset_btn" class="btn btn-default btn-sm mleft5">
                                    <i class="fa fa-times"></i> Clear Filters
                                </button>
                            </div>
                        </div>

                        <!-- ===== DataTable ===== -->
                        <div class="table-responsive">
                            <?php render_datatable([
                                'Item Name',
                                'Item SKU',
                                'Category',
                                'Vendor',
                                'Vendor SKU',
                                'Last Purchase Price',
                                'Preferred',
                                'Inquiry',
                            ], 'sourcing-locator'); ?>
                        </div>

                    </div><!-- /.panel-body -->
                </div><!-- /.panel_s -->
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<!-- Vendor Hierarchy Modal -->
<div class="modal fade" id="vs-hierarchy-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:4px 4px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:0.8;"><span>&times;</span></button>
                <h4 class="modal-title">
                    <i class="fa fa-sitemap mright5"></i>
                    Vendor Hierarchy — <span id="vs-hierarchy-vendor-name"></span>
                </h4>
            </div>
            <div class="modal-body" id="vs-hierarchy-body" style="min-height:200px;">
                <div class="text-center mtop20 mbot20">
                    <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="text-muted mtop10">Loading hierarchy...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Hierarchy modal tree styles */
.vs-h-breadcrumb {
    list-style: none;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    padding: 10px 14px;
    margin: 0 0 15px;
    background: linear-gradient(135deg, #f8f9ff, #f0f4ff);
    border-radius: 8px;
    border: 1px solid #e8ecf5;
}
.vs-h-breadcrumb li {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
}
.vs-h-breadcrumb .bc-sep {
    margin: 0 6px;
    color: #cbd5e1;
    font-size: 10px;
}
.vs-h-tree { list-style: none; padding-left: 22px; margin: 0; border-left: 2px solid #e2e8f0; }
.vs-h-tree-root { border-left: none; padding-left: 0; }
.vs-h-tree-node { position: relative; padding: 3px 0; }
.vs-h-tree-node::before {
    content: ''; position: absolute; left: -22px; top: 16px;
    width: 18px; height: 0; border-top: 2px solid #e2e8f0;
}
.vs-h-tree-root > .vs-h-tree-node::before { display: none; }
.vs-h-tree-item {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 7px 12px; background: #fff; border: 1px solid #e8ecf5;
    border-radius: 8px; transition: all 0.15s; margin: 2px 0;
}
.vs-h-tree-item:hover {
    background: #f5f7ff; border-color: #c7d2fe;
    box-shadow: 0 2px 8px rgba(99,102,241,0.08);
}
.vs-h-tree-item.vs-h-current {
    background: #eef2ff; border-color: #818cf8;
    box-shadow: 0 2px 10px rgba(99,102,241,0.15);
}
.vs-h-stats {
    display: flex; gap: 10px; margin-bottom: 15px;
}
.vs-h-stat-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px; font-size: 13px;
    background: #f1f5f9; border: 1px solid #e2e8f0;
}
.vs-h-stat-pill i { font-size: 14px; }
</style>

<script>
$(function () {
    // Committed filters logic — only updated when Search is clicked
    var activeFilters = {
        filter_item_name:   '',
        filter_sku:         '',
        filter_vendor_name: '',
        filter_vendor_sku:  '',
        filter_category:    '',
        filter_price_min:   '',
        filter_price_max:   ''
    };

    // Initialize using the table class generated by render_datatable
    var locatorTable = $('.table-sourcing-locator').DataTable({
        serverSide: true,
        processing: true,
        retrieve: true,
        ajax: {
            url: admin_url + 'vendor_sourcing/locator_data',
            type: 'POST',
            data: function (d) {
                // Send current committed filter state
                $.extend(d, activeFilters);
                // Standard CSRF handling
                if (typeof(csrfData) !== 'undefined') {
                    d[csrfData.token_name] = csrfData.hash;
                }
            }
        },
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5, orderable: false },
            { data: 6, orderable: false },
            { data: 7, orderable: false }
        ],
        // CRITICAL: Remove the skeleton loader / loading classes once data is drawn
        drawCallback: function(settings) {
            $('.table-sourcing-locator').removeClass('dt-table-loading');
            $('.table-sourcing-locator').parents('.table-loading').removeClass('table-loading');
        },
        pageLength: 25,
        order: [[0, 'asc']]
    });

    // Search button functionality
    $('#vs_search_btn').on('click', function () {
        activeFilters.filter_item_name   = $('#filter_item_name').val() || '';
        activeFilters.filter_sku         = $('#filter_sku').val() || '';
        activeFilters.filter_vendor_name = $('#filter_vendor_name').val() || '';
        activeFilters.filter_vendor_sku  = $('#filter_vendor_sku').val() || '';
        activeFilters.filter_category    = $('#filter_category').val() || '';
        activeFilters.filter_price_min   = $('#filter_price_min').val() || '';
        activeFilters.filter_price_max   = $('#filter_price_max').val() || '';
        locatorTable.ajax.reload();
    });

    // Reset button functionality
    $('#vs_reset_btn').on('click', function () {
        $('#filter_item_name, #filter_sku, #filter_vendor_name, #filter_vendor_sku, #filter_category')
            .val('').trigger('change');
        $('.selectpicker').selectpicker('refresh');
        $('#filter_price_min, #filter_price_max').val('');
        activeFilters = {
            filter_item_name: '', filter_sku: '', filter_vendor_name: '',
            filter_vendor_sku: '', filter_category: '',
            filter_price_min: '', filter_price_max: ''
        };
        locatorTable.ajax.reload();
    });

    // Enter key support for price inputs
    $('#filter_price_min, #filter_price_max').on('keypress', function (e) {
        if (e.which === 13) { $('#vs_search_btn').trigger('click'); }
    });

    // ===== Vendor Hierarchy Button =====
    var badgeClasses = ['primary', 'success', 'warning', 'info'];

    $(document).on('click', '.vs-hierarchy-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var vendorId   = $(this).data('vendor-id');
        var vendorName = $(this).data('vendor-name');

        $('#vs-hierarchy-vendor-name').text(vendorName);
        $('#vs-hierarchy-body').html(
            '<div class="text-center mtop20 mbot20">' +
            '<i class="fa fa-spinner fa-spin fa-2x text-muted"></i>' +
            '<p class="text-muted mtop10">Loading hierarchy...</p></div>'
        );
        $('#vs-hierarchy-modal').modal('show');

        // Fetch breadcrumb + subtree from vendor_hierarchy module
        $.when(
            $.ajax({
                url: admin_url + 'vendor_hierarchy/get_breadcrumb?vendor_id=' + vendorId,
                type: 'GET',
                dataType: 'json'
            }),
            $.ajax({
                url: admin_url + 'vendor_hierarchy/get_subtree?vendor_id=' + vendorId,
                type: 'GET',
                dataType: 'json'
            })
        ).done(function(bcRes, treeRes) {
            var bcData   = bcRes[0];
            var treeData = treeRes[0];

            var html = '';

            // Stats pills
            var childCount = (treeData.tree) ? treeData.tree.length : 0;
            var totalDesc  = countNodes(treeData.tree || []);
            var depth      = bcData.breadcrumb ? bcData.breadcrumb.length : 1;
            var levelLabel = (bcData.breadcrumb && bcData.breadcrumb.length > 0)
                ? bcData.breadcrumb[bcData.breadcrumb.length - 1].level_label
                : 'Level ' + depth;

            html += '<div class="vs-h-stats">';
            html += '<span class="vs-h-stat-pill"><i class="fa fa-layer-group text-primary"></i> <strong>' + escH(levelLabel) + '</strong></span>';
            html += '<span class="vs-h-stat-pill"><i class="fa fa-users text-warning"></i> ' + childCount + ' direct</span>';
            html += '<span class="vs-h-stat-pill"><i class="fa fa-project-diagram text-info"></i> ' + totalDesc + ' total</span>';
            html += '</div>';

            // Breadcrumb
            if (bcData.breadcrumb && bcData.breadcrumb.length > 1) {
                html += '<ol class="vs-h-breadcrumb">';
                $.each(bcData.breadcrumb, function(i, bc) {
                    var bCls = badgeClasses[Math.min(bc.level - 1, 3)];
                    var isLast = (i === bcData.breadcrumb.length - 1);
                    if (i > 0) html += '<li><span class="bc-sep"><i class="fa fa-chevron-right"></i></span></li>';
                    html += '<li>';
                    html += '<span class="label label-' + bCls + '" style="font-size:10px;">' + escH(bc.level_label) + '</span> ';
                    if (!isLast) {
                        html += '<a href="' + admin_url + 'purchase/vendor/' + bc.userid + '">' + escH(bc.company) + '</a>';
                    } else {
                        html += '<strong>' + escH(bc.company) + '</strong>';
                    }
                    html += '</li>';
                });
                html += '</ol>';
            }

            // Tree
            if (treeData.tree && treeData.tree.length > 0) {
                html += '<h5 class="bold mbot10"><i class="fa fa-sitemap text-success mright5"></i>Sub-Vendors</h5>';
                html += buildTree(treeData.tree, vendorId);
            } else {
                html += '<div class="text-center text-muted mtop15 mbot15">';
                html += '<i class="fa fa-info-circle fa-2x mbot10" style="opacity:0.3;display:block;"></i>';
                html += '<p>No sub-vendors under this vendor.</p>';
                html += '</div>';
            }

            $('#vs-hierarchy-body').html(html);

        }).fail(function() {
            $('#vs-hierarchy-body').html(
                '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle mright5"></i>Failed to load hierarchy data. Ensure the Vendor Hierarchy module is active.</div>'
            );
        });
    });

    function buildTree(nodes, highlightId) {
        var html = '<ul class="vs-h-tree' + (!highlightId ? '' : ' vs-h-tree-root') + '">';
        $.each(nodes, function(i, node) {
            var bCls = badgeClasses[Math.min(node.level - 1, 3)];
            var hasKids = node.children && node.children.length > 0;
            var statusIcon = node.active == 1
                ? '<i class="fa fa-check-circle text-success" style="font-size:12px;"></i>'
                : '<i class="fa fa-times-circle text-danger" style="font-size:12px;"></i>';

            html += '<li class="vs-h-tree-node">';
            html += '<div class="vs-h-tree-item">';
            html += '<span class="label label-' + bCls + '" style="font-size:10px;">' + escH(node.level_label) + '</span>';
            html += '<a href="' + admin_url + 'purchase/vendor/' + node.userid + '" style="font-weight:600;font-size:13px;color:#1e293b;text-decoration:none;">' + escH(node.company) + '</a>';
            html += statusIcon;
            if (node.phonenumber) {
                html += '<small class="text-muted"><i class="fa fa-phone"></i> ' + escH(node.phonenumber) + '</small>';
            }
            html += '</div>';
            if (hasKids) {
                html += buildTree(node.children, false);
            }
            html += '</li>';
        });
        html += '</ul>';
        return html;
    }

    function countNodes(nodes) {
        var c = 0;
        $.each(nodes, function(i, n) {
            c++;
            if (n.children) c += countNodes(n.children);
        });
        return c;
    }

    function escH(str) {
        if (!str) return '';
        return $('<div>').text(str).html();
    }

    // ===== INQUIRY MODAL =====
    $(document).on('click', '.vs-inquiry-btn', function(e){
        e.preventDefault();
        e.stopPropagation();

        var vendorId   = $(this).data('vendor-id');
        var vendorName = $(this).data('vendor-name');
        var itemName   = $(this).data('item-name');
        var itemId     = $(this).data('item-id');

        // Set hidden fields
        $('#inq_vendor_id').val(vendorId);
        $('#inq_item_id').val(itemId);

        // Reset form
        $('#inq_send_email').prop('checked', true);
        $('#inq_send_sms').prop('checked', false);
        $('#inq_send_hierarchy').prop('checked', false);
        $('#inq-email-section').show();
        $('#inq-sms-section').hide();
        $('#inq-result').hide();
        $('#inq-send-btn').prop('disabled', false);

        // Set title
        $('#inq-modal-vendor-name').text(vendorName);
        $('#inq-modal-item-name').text(itemName);

        // Set default templates
        $('#inq_email_subject').val(<?php echo json_encode(get_option('vs_inquiry_email_subject')); ?>);
        $('#inq_email_body').val(<?php echo json_encode(get_option('vs_inquiry_email_template')); ?>);
        $('#inq_sms_body').val(<?php echo json_encode(get_option('vs_inquiry_sms_template')); ?>);

        // Fetch vendor contact info
        $('#inq-vendor-info').html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        $.ajax({
            url: admin_url + 'vendor_sourcing/get_vendor_info',
            type: 'POST',
            dataType: 'json',
            data: {
                vendor_id: vendorId,
                item_id: itemId,
                [csrfData.token_name]: csrfData.hash
            },
            success: function(res){
                if(res.success){
                    var infoHtml = '<div class="row">';
                    infoHtml += '<div class="col-xs-6"><strong>Contact:</strong> ' + escH(res.contact_name) + '</div>';
                    infoHtml += '<div class="col-xs-6"><strong>Email:</strong> ' + escH(res.vendor_email || 'N/A') + '</div>';
                    infoHtml += '<div class="col-xs-6"><strong>Phone:</strong> ' + escH(res.vendor_phone || 'N/A') + '</div>';
                    infoHtml += '<div class="col-xs-6"><strong>Hierarchy:</strong> ' + res.hierarchy_total + ' vendors (' + res.children_count + ' direct children)</div>';
                    infoHtml += '</div>';
                    $('#inq-vendor-info').html(infoHtml);

                    // Update hierarchy toggle label
                    if(res.children_count > 0){
                        $('#inq-hierarchy-label').html('Send to entire hierarchy <span class="label label-warning">' + res.hierarchy_total + ' vendors</span>');
                    } else {
                        $('#inq-hierarchy-label').html('Send to entire hierarchy <small class="text-muted">(no children)</small>');
                    }
                } else {
                    $('#inq-vendor-info').html('<span class="text-danger">Could not load vendor info</span>');
                }
            },
            error: function(){
                $('#inq-vendor-info').html('<span class="text-danger">Failed to load vendor info</span>');
            }
        });

        $('#vs-inquiry-modal').modal('show');
    });

    // Toggle email/sms sections
    $('#inq_send_email').on('change', function(){
        $('#inq-email-section').toggle(this.checked);
    });
    $('#inq_send_sms').on('change', function(){
        $('#inq-sms-section').toggle(this.checked);
    });

    // Send inquiry
    $('#inq-send-btn').on('click', function(){
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mright5"></i>Sending...');
        $('#inq-result').hide();

        $.ajax({
            url: admin_url + 'vendor_sourcing/send_inquiry',
            type: 'POST',
            dataType: 'json',
            data: {
                vendor_id: $('#inq_vendor_id').val(),
                item_id: $('#inq_item_id').val(),
                send_email: $('#inq_send_email').is(':checked') ? 1 : 0,
                send_sms: $('#inq_send_sms').is(':checked') ? 1 : 0,
                send_to_hierarchy: $('#inq_send_hierarchy').is(':checked') ? 1 : 0,
                email_subject: $('#inq_email_subject').val(),
                email_body: $('#inq_email_body').val(),
                sms_body: $('#inq_sms_body').val(),
                [csrfData.token_name]: csrfData.hash
            },
            success: function(res){
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane mright5"></i>Send Inquiry');

                var html = '';
                if(res.success){
                    html = '<div class="alert alert-success"><i class="fa fa-check-circle mright5"></i>';
                    html += '<strong>' + res.sent_count + '</strong> message(s) sent successfully.';
                    if(res.failed_count > 0){
                        html += ' <span class="text-danger">(' + res.failed_count + ' failed)</span>';
                    }
                    html += '</div>';

                    // Show details
                    if(res.details && res.details.length > 0){
                        html += '<table class="table table-condensed table-bordered">';
                        html += '<thead><tr><th>Vendor</th><th>Channel</th><th>Status</th></tr></thead><tbody>';
                        $.each(res.details, function(i, d){
                            var badge = d.status === 'sent'
                                ? '<span class="label label-success">Sent</span>'
                                : '<span class="label label-danger">Failed</span>';
                            var chBadge = d.channel === 'email'
                                ? '<span class="label label-info">Email</span>'
                                : '<span class="label label-success">SMS</span>';
                            html += '<tr><td>' + escH(d.vendor) + '</td><td>' + chBadge + '</td><td>' + badge + '</td></tr>';
                        });
                        html += '</tbody></table>';
                    }
                } else {
                    html = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle mright5"></i>';
                    html += 'No messages were sent. Please check that the vendor has contact information.';
                    html += '</div>';
                }

                $('#inq-result').html(html).show();
            },
            error: function(){
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane mright5"></i>Send Inquiry');
                $('#inq-result').html('<div class="alert alert-danger">Error sending inquiry. Please try again.</div>').show();
            }
        });
    });
});
</script>

<!-- ===== INQUIRY MODAL ===== -->
<div class="modal fade" id="vs-inquiry-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#11998e,#38ef7d);color:#fff;border-radius:4px 4px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:0.8;"><span>&times;</span></button>
                <h4 class="modal-title">
                    <i class="fa fa-paper-plane mright5"></i>
                    Inquiry — <span id="inq-modal-item-name"></span>
                    <small style="opacity:0.8;">to <span id="inq-modal-vendor-name"></span></small>
                </h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="inq_vendor_id">
                <input type="hidden" id="inq_item_id">

                <!-- Vendor Info Card -->
                <div class="well well-sm" id="inq-vendor-info" style="background:#f8f9ff;border:1px solid #e8ecf5;border-radius:8px;">
                    <i class="fa fa-spinner fa-spin"></i> Loading vendor info...
                </div>

                <!-- Channel & Hierarchy Toggle -->
                <div class="row mbot15">
                    <div class="col-md-4">
                        <div class="checkbox">
                            <label><input type="checkbox" id="inq_send_email" checked> <i class="fa fa-envelope text-info"></i> Send Email</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="checkbox">
                            <label><input type="checkbox" id="inq_send_sms"> <i class="fa fa-comment text-success"></i> Send SMS</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="checkbox">
                            <label><input type="checkbox" id="inq_send_hierarchy"> <i class="fa fa-sitemap text-warning"></i> <span id="inq-hierarchy-label">Send to hierarchy</span></label>
                        </div>
                    </div>
                </div>

                <!-- Email Section -->
                <div id="inq-email-section">
                    <div class="form-group">
                        <label class="control-label"><i class="fa fa-tag mright5"></i>Subject</label>
                        <input type="text" id="inq_email_subject" class="form-control" placeholder="Product Inquiry: ...">
                    </div>
                    <div class="form-group">
                        <label class="control-label"><i class="fa fa-file-text mright5"></i>Email Body</label>
                        <textarea id="inq_email_body" class="form-control" rows="8"></textarea>
                    </div>
                </div>

                <!-- SMS Section -->
                <div id="inq-sms-section" style="display:none;">
                    <div class="form-group">
                        <label class="control-label"><i class="fa fa-comment mright5"></i>SMS Message</label>
                        <textarea id="inq_sms_body" class="form-control" rows="4" maxlength="500"></textarea>
                        <small class="text-muted"><span id="inq-sms-count">0</span>/500 characters</small>
                    </div>
                </div>

                <!-- Result -->
                <div id="inq-result" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" id="inq-send-btn" class="btn btn-success">
                    <i class="fa fa-paper-plane mright5"></i>Send Inquiry
                </button>
            </div>
        </div>
    </div>
</div>

