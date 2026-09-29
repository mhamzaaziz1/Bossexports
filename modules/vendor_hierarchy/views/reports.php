<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <!-- Page Header -->
        <div class="row">
            <div class="col-md-12">
                <div class="page-heading">
                    <h4 class="font-bold">
                        <i class="fa fa-sitemap text-info mright5"></i>
                        <?php echo _l('vendor_hierarchy_reports'); ?>
                    </h4>
                    <div class="pull-right mtop5">
                        <a href="<?php echo admin_url('vendor_hierarchy/export_csv'); ?>" class="btn btn-default btn-sm">
                            <i class="fa fa-download mright5"></i><?php echo _l('vendor_hierarchy_export_csv'); ?>
                        </a>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>

        <!-- Summary KPI Cards -->
        <div class="row mbot20">
            <div class="col-md-2 col-sm-4">
                <div class="panel_s vh-kpi-card">
                    <div class="panel-body text-center">
                        <div class="vh-kpi-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                            <i class="fa fa-building"></i>
                        </div>
                        <h2 class="bold no-margin mtop10"><?php echo $summary['total_vendors']; ?></h2>
                        <small class="text-muted"><?php echo _l('vendor_hierarchy_total_vendors'); ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="panel_s vh-kpi-card">
                    <div class="panel-body text-center">
                        <div class="vh-kpi-icon" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                            <i class="fa fa-crown"></i>
                        </div>
                        <h2 class="bold no-margin mtop10"><?php echo $summary['top_level']; ?></h2>
                        <small class="text-muted"><?php echo _l('vendor_hierarchy_top_level_vendors'); ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="panel_s vh-kpi-card">
                    <div class="panel-body text-center">
                        <div class="vh-kpi-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                            <i class="fa fa-users"></i>
                        </div>
                        <h2 class="bold no-margin mtop10"><?php echo $summary['with_children']; ?></h2>
                        <small class="text-muted">Parents (with children)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="panel_s vh-kpi-card">
                    <div class="panel-body text-center">
                        <div class="vh-kpi-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                            <i class="fa fa-arrow-down"></i>
                        </div>
                        <h2 class="bold no-margin mtop10"><?php echo $summary['deepest_chain']; ?></h2>
                        <small class="text-muted"><?php echo _l('vendor_hierarchy_deepest_chain'); ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="panel_s vh-kpi-card">
                    <div class="panel-body text-center">
                        <div class="vh-kpi-icon" style="background: linear-gradient(135deg, #fa709a, #fee140);">
                            <i class="fa fa-chart-bar"></i>
                        </div>
                        <h2 class="bold no-margin mtop10"><?php echo $summary['avg_children']; ?></h2>
                        <small class="text-muted"><?php echo _l('vendor_hierarchy_avg_children'); ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="panel_s vh-kpi-card">
                    <div class="panel-body text-center">
                        <div class="vh-kpi-icon" style="background: linear-gradient(135deg, #a18cd1, #fbc2eb);">
                            <i class="fa fa-exclamation-triangle"></i>
                        </div>
                        <h2 class="bold no-margin mtop10"><?php echo count($orphans); ?></h2>
                        <small class="text-muted"><?php echo _l('vendor_hierarchy_report_orphans'); ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mbot20">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h5 class="font-bold no-margin mbot15">
                            <i class="fa fa-chart-pie text-primary mright5"></i>
                            Level Distribution
                        </h5>
                        <div style="position:relative; height:280px; width:100%;">
                            <canvas id="vh-level-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h5 class="font-bold no-margin mbot15">
                            <i class="fa fa-chart-bar text-success mright5"></i>
                            Vendors Per Level
                        </h5>
                        <div style="position:relative; height:280px; width:100%;">
                            <canvas id="vh-bar-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Level Tabs -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="horizontal-scrollable-tabs">
                            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                            <div class="horizontal-tabs">
                                <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                                    <?php for ($i = 1; $i <= 4; $i++): ?>
                                    <li role="presentation" <?php echo ($i == 1) ? 'class="active"' : ''; ?>>
                                        <a href="#level_<?php echo $i; ?>_tab" role="tab" data-toggle="tab">
                                            <span class="label label-<?php
                                                $badge_classes = ['primary', 'success', 'warning', 'info'];
                                                echo $badge_classes[$i - 1];
                                            ?> mright5"><?php echo $summary['level_counts'][$i]; ?></span>
                                            <?php echo $labels['level_' . $i . '_label']; ?>
                                        </a>
                                    </li>
                                    <?php endfor; ?>
                                    <li role="presentation">
                                        <a href="#tree_tab" role="tab" data-toggle="tab">
                                            <i class="fa fa-sitemap mright5"></i>
                                            <?php echo _l('vendor_hierarchy_report_tree'); ?>
                                        </a>
                                    </li>
                                    <?php if (!empty($orphans)): ?>
                                    <li role="presentation">
                                        <a href="#orphan_tab" role="tab" data-toggle="tab">
                                            <i class="fa fa-exclamation-triangle text-danger mright5"></i>
                                            <?php echo _l('vendor_hierarchy_report_orphans'); ?>
                                            <span class="badge"><?php echo count($orphans); ?></span>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                        <div class="tab-content mtop15">
                            <!-- Level Tabs Content -->
                            <?php for ($i = 1; $i <= 4; $i++):
                                $level_vendors = $by_level['levels'][$i];
                            ?>
                            <div role="tabpanel" class="tab-pane <?php echo ($i == 1) ? 'active' : ''; ?>" id="level_<?php echo $i; ?>_tab">
                                <?php if (!empty($level_vendors)): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover dt-table" data-order='[[1,"asc"]]'>
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th><?php echo _l('clients_list_company'); ?></th>
                                                <th><?php echo _l('vendor_hierarchy_parent_vendor'); ?></th>
                                                <th><?php echo _l('vendor_hierarchy_children_count'); ?></th>
                                                <th><?php echo _l('vendor_hierarchy_total_descendants'); ?></th>
                                                <th><?php echo _l('clients_list_phone'); ?></th>
                                                <th><?php echo _l('customer_active'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($level_vendors as $v): ?>
                                            <tr>
                                                <td><?php echo $v['userid']; ?></td>
                                                <td>
                                                    <a href="<?php echo admin_url('purchase/vendor/' . $v['userid']); ?>">
                                                        <?php echo $v['company']; ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php
                                                    $CI =& get_instance();
                                                    $pv = $CI->vendor_hierarchy_model->get_parent_vendor($v['userid']);
                                                    if ($pv) {
                                                        echo '<a href="' . admin_url('purchase/vendor/' . $pv->userid) . '">' . $pv->company . '</a>';
                                                    } else {
                                                        echo '<span class="text-muted">—</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td><span class="badge"><?php echo $v['children_count']; ?></span></td>
                                                <td><span class="badge"><?php echo $v['total_descendants']; ?></span></td>
                                                <td><?php echo $v['phonenumber']; ?></td>
                                                <td>
                                                    <?php if ($v['active']): ?>
                                                        <span class="label label-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="label label-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="text-center text-muted mtop20 mbot20">
                                    <i class="fa fa-info-circle fa-2x mbot10" style="opacity:0.3;display:block;"></i>
                                    <p>No vendors at this level.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endfor; ?>

                            <!-- Full Tree Tab -->
                            <div role="tabpanel" class="tab-pane" id="tree_tab">
                                <div class="mbot10">
                                    <button class="btn btn-default btn-xs" id="vh-report-expand-all">
                                        <i class="fa fa-expand mright5"></i><?php echo _l('vendor_hierarchy_expand_all'); ?>
                                    </button>
                                    <button class="btn btn-default btn-xs" id="vh-report-collapse-all">
                                        <i class="fa fa-compress mright5"></i><?php echo _l('vendor_hierarchy_collapse_all'); ?>
                                    </button>
                                </div>
                                <?php if (!empty($full_tree)): ?>
                                <div class="vh-tree-container">
                                    <?php echo vh_render_report_tree($full_tree); ?>
                                </div>
                                <?php else: ?>
                                <div class="text-center text-muted mtop20 mbot20">
                                    <p>No vendors found.</p>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Orphan Tab -->
                            <?php if (!empty($orphans)): ?>
                            <div role="tabpanel" class="tab-pane" id="orphan_tab">
                                <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-triangle mright5"></i>
                                    These vendors reference a parent that does not exist in the system. Consider reassigning or removing their parent.
                                </div>
                                <table class="table table-striped dt-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Company</th>
                                            <th>Missing Parent ID</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orphans as $o): ?>
                                        <tr>
                                            <td><?php echo $o['userid']; ?></td>
                                            <td>
                                                <a href="<?php echo admin_url('purchase/vendor/' . $o['userid']); ?>">
                                                    <?php echo $o['company']; ?>
                                                </a>
                                            </td>
                                            <td><span class="text-danger"><?php echo $o['parent_vendor_id']; ?></span></td>
                                            <td>
                                                <a href="<?php echo admin_url('purchase/vendor/' . $o['userid']); ?>" class="btn btn-info btn-xs">
                                                    <i class="fa fa-pencil mright5"></i>Edit Vendor
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(function(){
    // Chart data
    var labels = <?php
        $chart_labels = [];
        $chart_data = [];
        for ($i = 1; $i <= 4; $i++) {
            $chart_labels[] = $labels['level_' . $i . '_label'];
            $chart_data[] = $summary['level_counts'][$i];
        }
        echo json_encode($chart_labels);
    ?>;
    var data = <?php echo json_encode($chart_data); ?>;
    var colors = ['#667eea', '#38ef7d', '#f5576c', '#4facfe'];
    var bgColors = ['rgba(102,126,234,0.15)', 'rgba(56,239,125,0.15)', 'rgba(245,87,108,0.15)', 'rgba(79,172,254,0.15)'];

    // Doughnut Chart
    if (document.getElementById('vh-level-chart')) {
        new Chart(document.getElementById('vh-level-chart'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
                },
                cutout: '60%'
            }
        });
    }

    // Bar Chart
    if (document.getElementById('vh-bar-chart')) {
        new Chart(document.getElementById('vh-bar-chart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Vendors',
                    data: data,
                    backgroundColor: colors,
                    borderColor: colors,
                    borderWidth: 1,
                    borderRadius: 6,
                    maxBarThickness: 60
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // Tree expand/collapse for report
    $(document).on('click', '.vh-tree-toggle', function(){
        var $li = $(this).closest('.vh-tree-node');
        $li.find('> .vh-tree').toggleClass('vh-tree-collapsed');
        $(this).find('i').toggleClass('fa-caret-down fa-caret-right');
    });

    $('#vh-report-expand-all').on('click', function(){
        $('.vh-tree').removeClass('vh-tree-collapsed');
        $('.vh-tree-toggle i').removeClass('fa-caret-right').addClass('fa-caret-down');
    });

    $('#vh-report-collapse-all').on('click', function(){
        $('.vh-tree .vh-tree').addClass('vh-tree-collapsed');
        $('.vh-tree .vh-tree').prev('.vh-tree-node-content').find('.vh-tree-toggle i').removeClass('fa-caret-down').addClass('fa-caret-right');
    });
});
</script>
</body>
</html>

<?php
function vh_render_report_tree($nodes) {
    if (empty($nodes)) return '';
    $html = '<ul class="vh-tree">';
    foreach ($nodes as $node) {
        $badge_classes = ['primary', 'success', 'warning', 'info'];
        $badge_class = $badge_classes[min($node['level'] - 1, 3)];
        $has_children = !empty($node['children']);
        $status_class = $node['active'] ? 'text-success' : 'text-danger';
        $status_icon = $node['active'] ? 'fa-check-circle' : 'fa-times-circle';

        $html .= '<li class="vh-tree-node">';
        $html .= '<div class="vh-tree-node-content">';
        if ($has_children) {
            $html .= '<span class="vh-tree-toggle"><i class="fa fa-caret-down"></i></span>';
        } else {
            $html .= '<span class="vh-tree-toggle-placeholder"></span>';
        }
        $html .= '<span class="label label-' . $badge_class . ' vh-tree-badge">' . $node['level_label'] . '</span>';
        $html .= '<a href="' . admin_url('purchase/vendor/' . $node['userid']) . '" class="vh-tree-link">';
        $html .= htmlspecialchars($node['company']);
        $html .= '</a>';
        $html .= '<i class="fa ' . $status_icon . ' ' . $status_class . ' mleft5" title="' . ($node['active'] ? 'Active' : 'Inactive') . '"></i>';
        if ($node['phonenumber']) {
            $html .= '<small class="text-muted mleft10"><i class="fa fa-phone"></i> ' . htmlspecialchars($node['phonenumber']) . '</small>';
        }
        $html .= '</div>';
        if ($has_children) {
            $html .= vh_render_report_tree($node['children']);
        }
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}
?>
