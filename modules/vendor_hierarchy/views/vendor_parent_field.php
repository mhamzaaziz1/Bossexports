<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * This view is injected into the vendor profile form via the
 * after_customer_billing_and_shipping_tab hook.
 * It adds a "Parent Vendor" dropdown and hierarchy breadcrumb.
 */
$CI =& get_instance();
$CI->load->model('vendor_hierarchy/vendor_hierarchy_model');

$labels = $CI->vendor_hierarchy_model->get_level_labels();
$current_parent_id = (isset($client) && isset($client->parent_vendor_id)) ? $client->parent_vendor_id : '';
$vendor_id = (isset($client) && isset($client->userid)) ? $client->userid : 0;
$eligible_parents = $CI->vendor_hierarchy_model->get_eligible_parents($vendor_id);
$current_depth = ($vendor_id > 0) ? $CI->vendor_hierarchy_model->get_vendor_depth($vendor_id) : 1;
$current_level_label = $CI->vendor_hierarchy_model->get_level_label($current_depth);

// Get breadcrumb if editing existing vendor
$breadcrumb = [];
if ($vendor_id > 0) {
    $breadcrumb = $CI->vendor_hierarchy_model->get_breadcrumb($vendor_id);
}
?>

<!-- Hierarchy Tab -->
<li role="presentation">
    <a href="#vendor_hierarchy_tab" aria-controls="vendor_hierarchy_tab" role="tab" data-toggle="tab">
        <i class="fa fa-sitemap mright5"></i><?php echo _l('vendor_hierarchy'); ?>
    </a>
</li>
</ul>
</div>
</div>

<!-- We need to close the existing tabs UL and open the tab-content, then add our pane -->
<!-- The parent vendor tab pane is rendered inside the tab-content that already exists -->

<script>
$(function(){
    // Move the hierarchy tab pane into the existing tab-content
    var $pane = $('#vh_hierarchy_pane');
    if ($pane.length) {
        $pane.appendTo($pane.closest('.tab-content'));
    }
});
</script>
