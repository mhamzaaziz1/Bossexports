<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * Hierarchy tree-view tab for vendor profile.
 * Displays the breadcrumb path AND sub-vendor tree.
 */
$CI =& get_instance();
$CI->load->model('vendor_hierarchy/vendor_hierarchy_model');

$vendor_id = $client->userid;
$labels = $CI->vendor_hierarchy_model->get_level_labels();
$breadcrumb = $CI->vendor_hierarchy_model->get_breadcrumb($vendor_id);
$subtree = $CI->vendor_hierarchy_model->get_subtree($vendor_id);
$current_depth = $CI->vendor_hierarchy_model->get_vendor_depth($vendor_id);
$current_level_label = $CI->vendor_hierarchy_model->get_level_label($current_depth);
$parent_vendor = $CI->vendor_hierarchy_model->get_parent_vendor($vendor_id);
$children_count = count($CI->vendor_hierarchy_model->get_direct_children($vendor_id));
$total_descendants = $CI->vendor_hierarchy_model->count_descendants($vendor_id);
?>

<div class="vh-hierarchy-tab">
    <!-- Quick Stats -->
    <div class="row mbot20">
        <div class="col-md-3">
            <div class="panel_s vh-stat-card">
                <div class="panel-body text-center">
                    <div class="vh-stat-icon"><i class="fa fa-layer-group text-primary"></i></div>
                    <h3 class="no-margin bold"><?php echo $current_level_label; ?></h3>
                    <small class="text-muted"><?php echo _l('vendor_hierarchy_current_level'); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel_s vh-stat-card">
                <div class="panel-body text-center">
                    <div class="vh-stat-icon"><i class="fa fa-arrow-up text-success"></i></div>
                    <h3 class="no-margin bold"><?php echo $parent_vendor ? $parent_vendor->company : '—'; ?></h3>
                    <small class="text-muted"><?php echo _l('vendor_hierarchy_parent_vendor'); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel_s vh-stat-card">
                <div class="panel-body text-center">
                    <div class="vh-stat-icon"><i class="fa fa-users text-warning"></i></div>
                    <h3 class="no-margin bold"><?php echo $children_count; ?></h3>
                    <small class="text-muted"><?php echo _l('vendor_hierarchy_children_count'); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel_s vh-stat-card">
                <div class="panel-body text-center">
                    <div class="vh-stat-icon"><i class="fa fa-project-diagram text-info"></i></div>
                    <h3 class="no-margin bold"><?php echo $total_descendants; ?></h3>
                    <small class="text-muted"><?php echo _l('vendor_hierarchy_total_descendants'); ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Breadcrumb Path -->
    <?php if (!empty($breadcrumb) && count($breadcrumb) > 1): ?>
    <div class="panel_s">
        <div class="panel-body">
            <h5 class="no-margin bold mbot10">
                <i class="fa fa-route text-info mright5"></i>
                <?php echo _l('vendor_hierarchy_breadcrumb'); ?>
            </h5>
            <ol class="vh-breadcrumb">
                <?php foreach ($breadcrumb as $idx => $crumb): ?>
                <li class="vh-breadcrumb-item <?php echo ($idx == count($breadcrumb) - 1) ? 'active' : ''; ?>">
                    <span class="label label-<?php
                        $badge_classes = ['primary', 'success', 'warning', 'info'];
                        echo $badge_classes[min($crumb['level'] - 1, 3)];
                    ?> vh-breadcrumb-badge"><?php echo $crumb['level_label']; ?></span>
                    <?php if ($idx < count($breadcrumb) - 1): ?>
                        <a href="<?php echo admin_url('purchase/vendor/' . $crumb['userid']); ?>" class="vh-breadcrumb-link">
                            <?php echo $crumb['company']; ?>
                        </a>
                        <i class="fa fa-chevron-right vh-breadcrumb-sep"></i>
                    <?php else: ?>
                        <strong><?php echo $crumb['company']; ?></strong>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
    <?php endif; ?>

    <!-- Sub-Vendor Tree -->
    <div class="panel_s">
        <div class="panel-body">
            <div class="clearfix mbot10">
                <h5 class="no-margin bold pull-left">
                    <i class="fa fa-sitemap text-success mright5"></i>
                    <?php echo _l('vendor_hierarchy_sub_vendors'); ?>
                </h5>
                <?php if (!empty($subtree)): ?>
                <div class="pull-right">
                    <button class="btn btn-default btn-xs" id="vh-expand-all">
                        <i class="fa fa-expand mright5"></i><?php echo _l('vendor_hierarchy_expand_all'); ?>
                    </button>
                    <button class="btn btn-default btn-xs" id="vh-collapse-all">
                        <i class="fa fa-compress mright5"></i><?php echo _l('vendor_hierarchy_collapse_all'); ?>
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($subtree)): ?>
            <div class="vh-tree-container">
                <?php echo vh_render_tree($subtree); ?>
            </div>
            <?php else: ?>
            <div class="text-center text-muted mtop20 mbot20">
                <i class="fa fa-info-circle fa-3x mbot10" style="opacity:0.3;display:block;"></i>
                <p><?php echo _l('vendor_hierarchy_no_children'); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
/**
 * Helper function to render the tree recursively
 */
function vh_render_tree($nodes) {
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
        $html .= '<i class="fa ' . $status_icon . ' ' . $status_class . ' mright5 mleft5" title="' . ($node['active'] ? 'Active' : 'Inactive') . '"></i>';

        if ($node['phonenumber']) {
            $html .= '<small class="text-muted mleft5"><i class="fa fa-phone"></i> ' . htmlspecialchars($node['phonenumber']) . '</small>';
        }

        $html .= '</div>';

        if ($has_children) {
            $html .= vh_render_tree($node['children']);
        }

        $html .= '</li>';
    }
    $html .= '</ul>';

    return $html;
}
?>
