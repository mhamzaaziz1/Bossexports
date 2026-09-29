<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$CI->load->model('performance_matrix/performance_matrix_model');
$client_id = $client->userid;
$stats = $CI->performance_matrix_model->get_detailed_customer_stats($client_id);
$last_transactions = $CI->performance_matrix_model->get_customer_last_transactions($client_id);
?>
<div class="row mtop15">
    <div class="col-md-12">
        <h4 class="customer-profile-group-heading font-medium-size text-primary"><i class="fa fa-bar-chart mright5"></i> <?php echo _l('Performance Matrix'); ?></h4>
        <hr class="mtop10" />
    </div>
</div>

<!-- All Detailed Stats -->
<?php $overview = $stats; include_once('customer_stats_modal_content.php'); ?>

<div class="row mtop30">
    <div class="col-md-12">
        <h4 class="font-bold mbot20">Item Price History (Quick Search)</h4>
        <div class="alert alert-info borderShadow">
            <p><i class="fa fa-info-circle"></i> Quickly check the last 5 prices offered to this customer for any specific product.</p>
        </div>
        <div class="col-md-6 no-padding">
             <?php 
                $CI->db->select('id, description');
                $items = $CI->db->get(db_prefix() . 'items')->result_array();
                echo render_select('item_matrix_history_select', $items, ['id', 'description'], 'Search Item Pricing History', '', ['data-live-search' => 'true']); 
             ?>
        </div>
        <div class="col-md-12 mtop15 no-padding" id="item_matrix_history_result">
             <p class="text-muted small italic">Select a product above to view comparative pricing history.</p>
        </div>
    </div>
</div>

<script>
$(function() {
    $('body').on('change', '#item_matrix_history_select', function() {
        var item_id = $(this).val();
        var client_id = "<?php echo $client->userid; ?>";
        if (item_id && client_id) {
            $('#item_matrix_history_result').html('<div class="text-center ptop20"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
            $.post(admin_url + 'performance_matrix/get_customer_item_prices', {
                item_id: item_id,
                client_id: client_id
            }).done(function(response) {
                $('#item_matrix_history_result').html(response);
            });
        }
    });
});
</script>
