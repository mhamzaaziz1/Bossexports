<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">

        <!-- Page Header -->
        <div class="row">
            <div class="col-md-12">
                <h4 class="font-bold mbot15">
                    <i class="fa fa-sitemap text-info mright5"></i>
                    <?php echo _l('vendor_hierarchy_search'); ?>
                </h4>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <!-- Keyword Search -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label"><i class="fa fa-search mright5"></i>Keyword</label>
                                    <input type="text" id="vh-search-keyword" class="form-control"
                                        placeholder="<?php echo _l('vendor_hierarchy_search_placeholder'); ?>"
                                        value="">
                                </div>
                            </div>

                            <!-- Level Filter -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label"><i class="fa fa-layer-group mright5"></i><?php echo _l('vendor_hierarchy_filter_by_level'); ?></label>
                                    <select id="vh-search-level" class="form-control selectpicker" data-width="100%">
                                        <option value="0"><?php echo _l('vendor_hierarchy_all_levels'); ?></option>
                                        <?php for ($i = 1; $i <= 4; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo ($selected_level == $i) ? 'selected' : ''; ?>>
                                            <?php echo $labels['level_' . $i . '_label']; ?> (Level <?php echo $i; ?>)
                                        </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Parent Filter -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label"><i class="fa fa-arrow-up mright5"></i><?php echo _l('vendor_hierarchy_filter_parent'); ?></label>
                                    <select id="vh-search-parent" class="form-control selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="All Parents">
                                        <option value="">All Parents</option>
                                        <?php foreach ($all_vendors as $v): ?>
                                        <option value="<?php echo $v['userid']; ?>"><?php echo $v['company']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label"><i class="fa fa-toggle-on mright5"></i><?php echo _l('vendor_hierarchy_filter_status'); ?></label>
                                    <select id="vh-search-status" class="form-control selectpicker" data-width="100%">
                                        <option value=""><?php echo _l('vendor_hierarchy_status_all'); ?></option>
                                        <option value="active"><?php echo _l('vendor_hierarchy_status_active'); ?></option>
                                        <option value="inactive"><?php echo _l('vendor_hierarchy_status_inactive'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label">&nbsp;</label>
                                    <div>
                                        <button id="vh-search-btn" class="btn btn-info">
                                            <i class="fa fa-search mright5"></i><?php echo _l('vendor_hierarchy_search_btn'); ?>
                                        </button>
                                        <button id="vh-clear-btn" class="btn btn-default mleft5">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Level Quick-Nav Cards -->
        <div class="row mbot15">
            <?php for ($i = 1; $i <= 4; $i++):
                $level_icons = ['fa-building', 'fa-industry', 'fa-map-marker', 'fa-home'];
                $level_gradients = [
                    'linear-gradient(135deg, #667eea, #764ba2)',
                    'linear-gradient(135deg, #11998e, #38ef7d)',
                    'linear-gradient(135deg, #f5576c, #ff6b6b)',
                    'linear-gradient(135deg, #4facfe, #00f2fe)',
                ];
            ?>
            <div class="col-md-3 col-sm-6">
                <div class="panel_s vh-level-nav-card <?php echo ($selected_level == $i) ? 'vh-level-nav-active' : ''; ?>"
                     data-level="<?php echo $i; ?>" style="cursor:pointer;">
                    <div class="panel-body">
                        <div class="pull-left">
                            <div class="vh-level-nav-icon" style="background: <?php echo $level_gradients[$i - 1]; ?>;">
                                <i class="fa <?php echo $level_icons[$i - 1]; ?>"></i>
                            </div>
                        </div>
                        <div class="pull-right text-right">
                            <h4 class="bold no-margin"><?php echo $labels['level_' . $i . '_label']; ?></h4>
                            <small class="text-muted">Level <?php echo $i; ?></small>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Results Area -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Results header -->
                        <div class="clearfix mbot10">
                            <div class="pull-left" id="vh-results-count">
                                <span class="text-muted">Click "Search" or select a level to begin.</span>
                            </div>
                            <div class="pull-right">
                                <a href="<?php echo admin_url('vendor_hierarchy/export_csv'); ?>" class="btn btn-default btn-xs">
                                    <i class="fa fa-download mright5"></i><?php echo _l('vendor_hierarchy_export_csv'); ?>
                                </a>
                            </div>
                        </div>

                        <!-- Loading indicator -->
                        <div id="vh-loading" class="text-center mbot20 mtop20" style="display:none;">
                            <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                            <p class="text-muted mtop10">Searching...</p>
                        </div>

                        <!-- Results table -->
                        <div id="vh-results-container" style="display:none;">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="vh-results-table">
                                    <thead>
                                        <tr>
                                            <th width="30">#</th>
                                            <th>Company</th>
                                            <th>Level</th>
                                            <th>Parent Vendor</th>
                                            <th><?php echo _l('vendor_hierarchy_hierarchy_path'); ?></th>
                                            <th>Children</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="vh-results-body">
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- No results -->
                        <div id="vh-no-results" class="text-center mtop20 mbot20" style="display:none;">
                            <i class="fa fa-search fa-3x mbot10" style="opacity:0.2;display:block;"></i>
                            <p class="text-muted"><?php echo _l('vendor_hierarchy_no_results'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inline Hierarchy Detail Modal -->
        <div class="modal fade" id="vh-hierarchy-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title"><i class="fa fa-sitemap mright5"></i>
                            <span id="vh-modal-title">Vendor Hierarchy</span>
                        </h4>
                    </div>
                    <div class="modal-body" id="vh-modal-body">
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php init_tail(); ?>
<style>
/* Level Nav Cards */
.vh-level-nav-card {
    border: 2px solid transparent;
    border-radius: 10px;
    transition: all 0.2s ease;
    margin-bottom: 10px;
}
.vh-level-nav-card:hover {
    border-color: #667eea;
    box-shadow: 0 4px 15px rgba(102,126,234,0.15);
    transform: translateY(-2px);
}
.vh-level-nav-active {
    border-color: #667eea !important;
    box-shadow: 0 4px 15px rgba(102,126,234,0.2) !important;
    background: #f8f9ff;
}
.vh-level-nav-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    color: #fff;
    font-size: 20px;
}
/* Results table enhancements */
#vh-results-table .vh-breadcrumb-mini {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 3px;
    font-size: 12px;
    padding: 0;
    margin: 0;
    list-style: none;
}
#vh-results-table .vh-breadcrumb-mini li {
    display: inline-flex;
    align-items: center;
}
#vh-results-table .vh-breadcrumb-mini .vh-bc-sep {
    color: #cbd5e1;
    font-size: 10px;
    margin: 0 2px;
}
#vh-results-table .vh-breadcrumb-mini .label {
    font-size: 9px;
    padding: 1px 5px;
}
/* Children expand row */
.vh-children-row td {
    padding: 0 !important;
    border-top: none !important;
}
.vh-children-panel {
    background: #f8f9ff;
    padding: 15px 20px;
    border-left: 3px solid #667eea;
    margin: 0;
}
.vh-children-panel .table {
    margin: 0;
    background: transparent;
}
.vh-children-panel .table td,
.vh-children-panel .table th {
    padding: 6px 10px;
    font-size: 13px;
}
</style>

<script>
$(function(){
    var adminUrl = '<?php echo admin_url(); ?>';
    var badgeClasses = ['primary', 'success', 'warning', 'info'];

    // Auto-search if level is pre-selected from menu
    <?php if ($selected_level > 0): ?>
    doSearch();
    <?php endif; ?>

    // Level card clicks
    $('.vh-level-nav-card').on('click', function(){
        var level = $(this).data('level');
        $('.vh-level-nav-card').removeClass('vh-level-nav-active');
        $(this).addClass('vh-level-nav-active');
        $('#vh-search-level').val(level).selectpicker('refresh');
        doSearch();
    });

    // Search button
    $('#vh-search-btn').on('click', function(){ doSearch(); });

    // Enter key on keyword
    $('#vh-search-keyword').on('keypress', function(e){
        if (e.which === 13) { doSearch(); }
    });

    // Clear filters
    $('#vh-clear-btn').on('click', function(){
        $('#vh-search-keyword').val('');
        $('#vh-search-level').val('0').selectpicker('refresh');
        $('#vh-search-parent').val('').selectpicker('refresh');
        $('#vh-search-status').val('').selectpicker('refresh');
        $('.vh-level-nav-card').removeClass('vh-level-nav-active');
        $('#vh-results-container, #vh-no-results').hide();
        $('#vh-results-count').html('<span class="text-muted">Filters cleared.</span>');
    });

    function doSearch() {
        var keyword  = $('#vh-search-keyword').val();
        var level    = $('#vh-search-level').val();
        var parentId = $('#vh-search-parent').val();
        var status   = $('#vh-search-status').val();

        $('#vh-loading').show();
        $('#vh-results-container, #vh-no-results').hide();

        $.ajax({
            url: adminUrl + 'vendor_hierarchy/search_ajax',
            type: 'POST',
            data: {
                keyword: keyword,
                level: level,
                parent_id: parentId,
                status: status,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                $('#vh-loading').hide();

                if (response.success && response.results.length > 0) {
                    renderResults(response.results);
                    $('#vh-results-container').show();
                    $('#vh-results-count').html(
                        '<strong>' + response.results.length + '</strong> vendor(s) found'
                    );
                } else {
                    $('#vh-no-results').show();
                    $('#vh-results-count').html('<span class="text-muted">No results.</span>');
                }
            },
            error: function() {
                $('#vh-loading').hide();
                alert_float('danger', 'Search request failed.');
            }
        });
    }

    function renderResults(results) {
        var $tbody = $('#vh-results-body');
        $tbody.empty();

        $.each(results, function(i, v) {
            var badgeClass = badgeClasses[Math.min(v.depth - 1, 3)];

            // Build mini breadcrumb
            var bcHtml = '<ul class="vh-breadcrumb-mini">';
            if (v.breadcrumb && v.breadcrumb.length > 0) {
                $.each(v.breadcrumb, function(bi, bc) {
                    var bcBadge = badgeClasses[Math.min(bc.level - 1, 3)];
                    if (bi > 0) {
                        bcHtml += '<li><span class="vh-bc-sep"><i class="fa fa-chevron-right"></i></span></li>';
                    }
                    var isLast = (bi === v.breadcrumb.length - 1);
                    bcHtml += '<li>';
                    bcHtml += '<span class="label label-' + bcBadge + '">' + bc.level_label + '</span> ';
                    if (!isLast) {
                        bcHtml += '<a href="' + adminUrl + 'purchase/vendor/' + bc.userid + '" style="font-size:12px;">' + escHtml(bc.company) + '</a>';
                    } else {
                        bcHtml += '<strong style="font-size:12px;">' + escHtml(bc.company) + '</strong>';
                    }
                    bcHtml += '</li>';
                });
            }
            bcHtml += '</ul>';

            // Status
            var statusHtml = v.active == 1
                ? '<span class="label label-success">Active</span>'
                : '<span class="label label-danger">Inactive</span>';

            // Parent
            var parentHtml = v.parent_company
                ? '<a href="' + adminUrl + 'purchase/vendor/' + v.parent_id + '">' + escHtml(v.parent_company) + '</a>'
                : '<span class="text-muted">—</span>';

            // Children badge
            var childrenHtml = v.children_count > 0
                ? '<a href="javascript:void(0);" class="vh-toggle-children badge" style="background:#667eea;color:#fff;" data-vendor-id="' + v.userid + '" data-company="' + escAttr(v.company) + '" title="Click to expand children">' + v.children_count + ' <i class="fa fa-chevron-down" style="font-size:10px;"></i></a>'
                : '<span class="badge">0</span>';

            var $tr = $('<tr data-vendor-id="' + v.userid + '">' +
                '<td>' + v.userid + '</td>' +
                '<td><a href="' + adminUrl + 'purchase/vendor/' + v.userid + '"><strong>' + escHtml(v.company) + '</strong></a></td>' +
                '<td><span class="label label-' + badgeClass + '">' + escHtml(v.level_label) + '</span></td>' +
                '<td>' + parentHtml + '</td>' +
                '<td>' + bcHtml + '</td>' +
                '<td class="text-center">' + childrenHtml + '</td>' +
                '<td>' + (v.phonenumber || '') + '</td>' +
                '<td>' + statusHtml + '</td>' +
                '<td>' +
                    '<a href="' + adminUrl + 'purchase/vendor/' + v.userid + '" class="btn btn-default btn-xs" title="View"><i class="fa fa-eye"></i></a>' +
                    ' <a href="' + adminUrl + 'purchase/vendor/' + v.userid + '?group=hierarchy" class="btn btn-info btn-xs" title="Hierarchy"><i class="fa fa-sitemap"></i></a>' +
                '</td>' +
            '</tr>');

            $tbody.append($tr);
        });
    }

    // Toggle children inline
    $(document).on('click', '.vh-toggle-children', function(e) {
        e.preventDefault();
        var $badge = $(this);
        var vendorId = $badge.data('vendor-id');
        var company = $badge.data('company');
        var $tr = $badge.closest('tr');
        var $existingRow = $tr.next('.vh-children-row');

        // Toggle off
        if ($existingRow.length) {
            $existingRow.remove();
            $badge.find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            return;
        }

        $badge.find('i').removeClass('fa-chevron-down').addClass('fa-spinner fa-spin');

        $.ajax({
            url: adminUrl + 'vendor_hierarchy/get_children_ajax',
            type: 'POST',
            data: {
                vendor_id: vendorId,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                $badge.find('i').removeClass('fa-spinner fa-spin').addClass('fa-chevron-up');

                if (response.success && response.children.length > 0) {
                    var colspan = $tr.find('td').length;
                    var childHtml = '<tr class="vh-children-row"><td colspan="' + colspan + '"><div class="vh-children-panel">';
                    childHtml += '<strong class="mbot10 display-block"><i class="fa fa-level-down-alt mright5"></i>Children of ' + escHtml(company) + '</strong>';
                    childHtml += '<table class="table table-condensed"><thead><tr>';
                    childHtml += '<th>#</th><th>Company</th><th>Level</th><th>Path</th><th>Children</th><th>Status</th><th>Actions</th>';
                    childHtml += '</tr></thead><tbody>';

                    $.each(response.children, function(ci, child) {
                        var cBadge = badgeClasses[Math.min(child.depth - 1, 3)];
                        var cStatus = child.active == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';

                        // Mini breadcrumb for child
                        var cBcHtml = '';
                        if (child.breadcrumb && child.breadcrumb.length > 0) {
                            cBcHtml = '<ul class="vh-breadcrumb-mini">';
                            $.each(child.breadcrumb, function(bi, bc) {
                                var bcB = badgeClasses[Math.min(bc.level - 1, 3)];
                                if (bi > 0) cBcHtml += '<li><span class="vh-bc-sep"><i class="fa fa-chevron-right"></i></span></li>';
                                cBcHtml += '<li><span class="label label-' + bcB + '">' + bc.level_label + '</span> <span style="font-size:11px;">' + escHtml(bc.company) + '</span></li>';
                            });
                            cBcHtml += '</ul>';
                        }

                        var cChildBadge = child.children_count > 0
                            ? '<span class="badge" style="background:#667eea;color:#fff;">' + child.children_count + '</span>'
                            : '<span class="badge">0</span>';

                        childHtml += '<tr>';
                        childHtml += '<td>' + child.userid + '</td>';
                        childHtml += '<td><a href="' + adminUrl + 'purchase/vendor/' + child.userid + '">' + escHtml(child.company) + '</a></td>';
                        childHtml += '<td><span class="label label-' + cBadge + '">' + escHtml(child.level_label) + '</span></td>';
                        childHtml += '<td>' + cBcHtml + '</td>';
                        childHtml += '<td class="text-center">' + cChildBadge + '</td>';
                        childHtml += '<td>' + cStatus + '</td>';
                        childHtml += '<td>';
                        childHtml += '<a href="' + adminUrl + 'purchase/vendor/' + child.userid + '" class="btn btn-default btn-xs"><i class="fa fa-eye"></i></a>';
                        childHtml += ' <a href="' + adminUrl + 'purchase/vendor/' + child.userid + '?group=hierarchy" class="btn btn-info btn-xs"><i class="fa fa-sitemap"></i></a>';
                        childHtml += '</td>';
                        childHtml += '</tr>';
                    });

                    childHtml += '</tbody></table></div></td></tr>';
                    $tr.after(childHtml);
                }
            }
        });
    });

    // Utility
    function escHtml(str) {
        if (!str) return '';
        return $('<div>').text(str).html();
    }
    function escAttr(str) {
        if (!str) return '';
        return str.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
});
</script>
</body>
</html>
