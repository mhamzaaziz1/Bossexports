<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();?>
<div id="wrapper">
    <div class="content">
<div class="row">
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="no-margin"><?php echo _l('beatroute_skus'); ?></h4>
                    </div>
                    <div class="col-md-4 text-right">
                        <?php if (has_permission('beatroute_integration', '', 'edit')) { ?>
                            <a href="<?php echo admin_url('beatroute_integration/sync?type=skus'); ?>" class="btn btn-info">
                                <i class="fa fa-refresh"></i> <?php echo _l('sync_skus'); ?>
                            </a>
                        <?php } ?>
                    </div>
                </div>
                <hr class="hr-panel-heading" />

                <?php if (empty($skus)) { ?>
                    <div class="alert alert-info">
                        <?php echo _l('no_skus_found'); ?>
                    </div>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table dt-table" data-order-col="0" data-order-type="asc">
                            <thead>
                                <tr>
                                    <th><?php echo _l('id'); ?></th>
                                    <th><?php echo _l('sku_code'); ?></th>
                                    <th><?php echo _l('name'); ?></th>
                                    <th><?php echo _l('price'); ?></th>
                                    <th><?php echo _l('status'); ?></th>
                                    <th><?php echo _l('last_sync'); ?></th>
                                    <th><?php echo _l('sync_status'); ?></th>
                                    <th><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($skus as $sku) { ?>
                                    <tr>
                                        <td><?php echo $sku['id']; ?></td>
                                        <td><?php echo $sku['sku_code']; ?></td>
                                        <td><?php echo $sku['name']; ?></td>
                                        <td><?php echo app_format_money($sku['price'], ''); ?></td>
                                        <td>
                                            <?php
                                            $status_badge = 'default';
                                            if ($sku['status'] == 'active') {
                                                $status_badge = 'success';
                                            } elseif ($sku['status'] == 'inactive') {
                                                $status_badge = 'warning';
                                            }
                                            ?>
                                            <span class="label label-<?php echo $status_badge; ?>"><?php echo $sku['status']; ?></span>
                                        </td>
                                        <td><?php echo _dt($sku['last_sync']); ?></td>
                                        <td>
                                            <?php
                                            $sync_badge = 'default';
                                            if ($sku['sync_status'] == 'synced') {
                                                $sync_badge = 'success';
                                            } elseif ($sku['sync_status'] == 'pending') {
                                                $sync_badge = 'warning';
                                            } elseif ($sku['sync_status'] == 'failed') {
                                                $sync_badge = 'danger';
                                            }
                                            ?>
                                            <span class="label label-<?php echo $sync_badge; ?>"><?php echo $sku['sync_status']; ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-cog"></i> <?php echo _l('options'); ?> <span class="caret"></span>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <li>
                                                        <a href="#" onclick="viewSKUDetails(<?php echo $sku['id']; ?>); return false;">
                                                            <i class="fa fa-eye"></i> <?php echo _l('view'); ?>
                                                        </a>
                                                    </li>
                                                    <?php if (has_permission('beatroute_integration', '', 'edit')) { ?>
                                                        <li>
                                                            <a href="<?php echo admin_url('beatroute_integration/push?type=sku&id=' . $sku['beatroute_id']); ?>">
                                                                <i class="fa fa-upload"></i> <?php echo _l('push_to_beatroute'); ?>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if ($sku['item_id']) { ?>
                                                        <li>
                                                            <a href="<?php echo admin_url('invoice_items/item/' . $sku['item_id']); ?>" target="_blank">
                                                                <i class="fa fa-link"></i> <?php echo _l('view_in_perfex'); ?>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Live Beatroute SKUs Section -->
<div class="row">
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="no-margin"><?php echo _l('live_beatroute_skus'); ?></h4>
                        <p class="text-muted"><?php echo _l('live_data_from_beatroute'); ?></p>
                    </div>
                </div>
                <hr class="hr-panel-heading" />

                <?php if (empty($live_skus)) { ?>
                    <div class="alert alert-info">
                        <?php echo _l('no_live_skus_found'); ?>
                    </div>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table dt-table" data-order-col="0" data-order-type="asc">
                            <thead>
                                <tr>
                                    <th><?php echo _l('id'); ?></th>
                                    <th><?php echo _l('sku_external_id'); ?></th>
                                    <th><?php echo _l('description'); ?></th>
                                    <th><?php echo _l('price'); ?></th>
                                    <th><?php echo _l('status'); ?></th>
                                    <th><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Make sure we're accessing the data array inside live_skus
                                $skus_data = isset($live_skus['data']) ? $live_skus['data'] : $live_skus;
                                foreach ($skus_data as $sku) { 
                                ?>
                                    <tr>
                                        <td><?php echo isset($sku['sku_br_id']) ? $sku['sku_br_id'] : '-'; ?></td>
                                        <td><?php echo isset($sku['sku_external_id']) ? $sku['sku_external_id'] : '-'; ?></td>
                                        <td><?php echo isset($sku['description']) ? $sku['description'] : '-'; ?></td>
                                        <td><?php echo isset($sku['price']) ? app_format_money($sku['price'], '') : '-'; ?></td>
                                        <td>
                                            <?php
                                            $status_badge = 'default';
                                            $status_text = 'Unknown';

                                            if (isset($sku['is_available'])) {
                                                if ($sku['is_available'] == 1) {
                                                    $status_badge = 'success';
                                                    $status_text = 'Available';
                                                } else {
                                                    $status_badge = 'warning';
                                                    $status_text = 'Not Available';
                                                }
                                            }
                                            ?>
                                            <span class="label label-<?php echo $status_badge; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-cog"></i> <?php echo _l('options'); ?> <span class="caret"></span>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <?php if (has_permission('beatroute_integration', '', 'edit')) { ?>
                                                        <li>
                                                            <a href="<?php echo admin_url('beatroute_integration/sync?type=skus&id=' . (isset($sku['sku_external_id']) ? $sku['sku_external_id'] : $sku['id'])); ?>">
                                                                <i class="fa fa-refresh"></i> <?php echo _l('sync_to_local'); ?>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <?php 
                    // Extract pagination information from live_skus
                    $current_page = isset($live_skus['pagination']['currentPage']) ? $live_skus['pagination']['currentPage'] : 1;
                    $items_per_page = isset($live_skus['pagination']['perPage']) ? $live_skus['pagination']['perPage'] : 25;
                    $total_pages = isset($live_skus['pagination']['pageCount']) ? $live_skus['pagination']['pageCount'] : 1;
                    $total_items = isset($live_skus['pagination']['totalCount']) ? $live_skus['pagination']['totalCount'] : 0;

                    if ($total_pages > 1) { 
                    ?>
                    <!-- Pagination Controls -->
                    <div class="row">
                        <div class="col-md-12">
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <?php if ($current_page > 1) { ?>
                                    <li>
                                        <a href="<?php echo admin_url('beatroute_integration/skus?page='.($current_page-1).'&limit='.$items_per_page); ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <?php } ?>

                                    <?php
                                    // Calculate range of page numbers to show
                                    $start_page = max(1, $current_page - 2);
                                    $end_page = min($total_pages, $current_page + 2);

                                    // Show first page if not in range
                                    if ($start_page > 1) {
                                        echo '<li><a href="'.admin_url('beatroute_integration/skus?page=1&limit='.$items_per_page).'">1</a></li>';
                                        if ($start_page > 2) {
                                            echo '<li class="disabled"><a href="#">...</a></li>';
                                        }
                                    }

                                    // Show page numbers
                                    for ($i = $start_page; $i <= $end_page; $i++) {
                                        $active = $i == $current_page ? 'class="active"' : '';
                                        echo '<li '.$active.'><a href="'.admin_url('beatroute_integration/skus?page='.$i.'&limit='.$items_per_page).'">'.$i.'</a></li>';
                                    }

                                    // Show last page if not in range
                                    if ($end_page < $total_pages) {
                                        if ($end_page < $total_pages - 1) {
                                            echo '<li class="disabled"><a href="#">...</a></li>';
                                        }
                                        echo '<li><a href="'.admin_url('beatroute_integration/skus?page='.$total_pages.'&limit='.$items_per_page).'">'.$total_pages.'</a></li>';
                                    }
                                    ?>

                                    <?php if ($current_page < $total_pages) { ?>
                                    <li>
                                        <a href="<?php echo admin_url('beatroute_integration/skus?page='.($current_page+1).'&limit='.$items_per_page); ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </nav>
                            <p class="text-muted">
                                <?php echo _l('showing'); ?> <?php echo ($current_page-1)*$items_per_page+1; ?>-<?php echo min($current_page*$items_per_page, $total_items); ?> <?php echo _l('of'); ?> <?php echo $total_items; ?> <?php echo _l('items'); ?>
                            </p>
                        </div>
                    </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- SKU Details Modal -->
<div class="modal fade" id="sku_details_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo _l('sku_details'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td><strong><?php echo _l('id'); ?></strong></td>
                                    <td id="sku_id"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('beatroute_id'); ?></strong></td>
                                    <td id="sku_beatroute_id"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('sku_code'); ?></strong></td>
                                    <td id="sku_code"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('name'); ?></strong></td>
                                    <td id="sku_name"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('description'); ?></strong></td>
                                    <td id="sku_description"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('price'); ?></strong></td>
                                    <td id="sku_price"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('status'); ?></strong></td>
                                    <td id="sku_status"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('last_sync'); ?></strong></td>
                                    <td id="sku_last_sync"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('sync_status'); ?></strong></td>
                                    <td id="sku_sync_status"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('created_at'); ?></strong></td>
                                    <td id="sku_created_at"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('updated_at'); ?></strong></td>
                                    <td id="sku_updated_at"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
        </div>
    </div>
</div>

</div>
</div>

<script src="<?php echo base_url('modules/beatroute_integration/assets/js/beatroute.js'); ?>"></script>
<script>
    function viewSKUDetails(id) {
        $.get(admin_url + 'beatroute_integration/get_sku_details/' + id, function(response) {
            var sku = JSON.parse(response);

            $('#sku_id').text(sku.id);
            $('#sku_beatroute_id').text(sku.beatroute_id);
            $('#sku_code').text(sku.sku_code);
            $('#sku_name').text(sku.name);
            $('#sku_description').text(sku.description || '-');
            $('#sku_price').text(format_money(sku.price));

            var status_badge = 'default';
            if (sku.status == 'active') {
                status_badge = 'success';
            } else if (sku.status == 'inactive') {
                status_badge = 'warning';
            }
            $('#sku_status').html('<span class="label label-' + status_badge + '">' + sku.status + '</span>');

            // Use formatDateTime function if moment is not available
            if (typeof moment !== 'undefined') {
                $('#sku_last_sync').text(moment(sku.last_sync).format('YYYY-MM-DD HH:mm:ss'));
                $('#sku_created_at').text(moment(sku.created_at).format('YYYY-MM-DD HH:mm:ss'));
                $('#sku_updated_at').text(moment(sku.updated_at).format('YYYY-MM-DD HH:mm:ss'));
            } else {
                $('#sku_last_sync').text(formatDateTime(sku.last_sync));
                $('#sku_created_at').text(formatDateTime(sku.created_at));
                $('#sku_updated_at').text(formatDateTime(sku.updated_at));
            }

            var sync_badge = 'default';
            if (sku.sync_status == 'synced') {
                sync_badge = 'success';
            } else if (sku.sync_status == 'pending') {
                sync_badge = 'warning';
            } else if (sku.sync_status == 'failed') {
                sync_badge = 'danger';
            }
            $('#sku_sync_status').html('<span class="label label-' + sync_badge + '">' + sku.sync_status + '</span>');

            $('#sku_details_modal').modal('show');
        });
    }
</script>
<?php init_footer(); ?>
