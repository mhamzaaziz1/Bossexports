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
                        <h4 class="no-margin"><?php echo _l('beatroute_customers'); ?></h4>
                    </div>
                    <div class="col-md-4 text-right">
                        <?php if (has_permission('beatroute_integration', '', 'edit')) { ?>
                            <a href="<?php echo admin_url('beatroute_integration/sync?type=customers'); ?>" class="btn btn-info">
                                <i class="fa fa-refresh"></i> <?php echo _l('sync_customers'); ?>
                            </a>
                        <?php } ?>
                    </div>
                </div>
                <hr class="hr-panel-heading" />

                <?php if (empty($customers)) { ?>
                    <div class="alert alert-info">
                        <?php echo _l('no_customers_found'); ?>
                    </div>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table dt-table" data-order-col="0" data-order-type="asc">
                            <thead>
                                <tr>
                                    <th><?php echo _l('id'); ?></th>
                                    <th><?php echo _l('name'); ?></th>
                                    <th><?php echo _l('email'); ?></th>
                                    <th><?php echo _l('phone'); ?></th>
                                    <th><?php echo _l('status'); ?></th>
                                    <th><?php echo _l('last_sync'); ?></th>
                                    <th><?php echo _l('sync_status'); ?></th>
                                    <th><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer) { ?>
                                    <tr>
                                        <td><?php echo $customer['id']; ?></td>
                                        <td><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></td>
                                        <td><?php echo $customer['email']; ?></td>
                                        <td><?php echo $customer['phone']; ?></td>
                                        <td>
                                            <?php
                                            $status_badge = 'default';
                                            if ($customer['status'] == 'active') {
                                                $status_badge = 'success';
                                            } elseif ($customer['status'] == 'inactive') {
                                                $status_badge = 'warning';
                                            }
                                            ?>
                                            <span class="label label-<?php echo $status_badge; ?>"><?php echo $customer['status']; ?></span>
                                        </td>
                                        <td><?php echo _dt($customer['last_sync']); ?></td>
                                        <td>
                                            <?php
                                            $sync_badge = 'default';
                                            if ($customer['sync_status'] == 'synced') {
                                                $sync_badge = 'success';
                                            } elseif ($customer['sync_status'] == 'pending') {
                                                $sync_badge = 'warning';
                                            } elseif ($customer['sync_status'] == 'failed') {
                                                $sync_badge = 'danger';
                                            }
                                            ?>
                                            <span class="label label-<?php echo $sync_badge; ?>"><?php echo $customer['sync_status']; ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-cog"></i> <?php echo _l('options'); ?> <span class="caret"></span>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <li>
                                                        <a href="#" onclick="viewCustomerDetails(<?php echo $customer['id']; ?>); return false;">
                                                            <i class="fa fa-eye"></i> <?php echo _l('view'); ?>
                                                        </a>
                                                    </li>
                                                    <?php if (has_permission('beatroute_integration', '', 'edit')) { ?>
                                                        <li>
                                                            <a href="<?php echo admin_url('beatroute_integration/push?type=customer&id=' . $customer['beatroute_id']); ?>">
                                                                <i class="fa fa-upload"></i> <?php echo _l('push_to_beatroute'); ?>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if ($customer['client_id']) { ?>
                                                        <li>
                                                            <a href="<?php echo admin_url('clients/client/' . $customer['client_id']); ?>" target="_blank">
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

<!-- Live Beatroute Customers Section -->
<div class="row">
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="no-margin"><?php echo _l('live_beatroute_customers'); ?></h4>
                        <p class="text-muted"><?php echo _l('live_data_from_beatroute'); ?></p>
                    </div>
                </div>
                <hr class="hr-panel-heading" />

                <?php if (empty($live_customers)) { ?>
                    <div class="alert alert-info">
                        <?php echo _l('no_live_customers_found'); ?>
                    </div>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table dt-table" data-order-col="0" data-order-type="asc">
                            <thead>
                                <tr>
                                    <th><?php echo _l('id'); ?></th>
                                    <th><?php echo _l('name'); ?></th>
                                    <th><?php echo _l('email'); ?></th>
                                    <th><?php echo _l('phone'); ?></th>
                                    <th><?php echo _l('status'); ?></th>
                                    <th><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Make sure we're accessing the data array inside live_customers
                                $customers_data = isset($live_customers['data']) ? $live_customers['data'] : $live_customers;
                                foreach ($customers_data as $customer) { 
                                ?>
                                    <tr>
                                        <td><?php echo $customer['external_id']; ?></td>
                                        <td><?php echo $customer['name'] . ' ' . $customer['last_name']; ?></td>
                                        <td><?php echo $customer['email']; ?></td>
                                        <td><?php echo isset($customer['mobile']) ? $customer['mobile'] : ''; ?></td>
                                        <td>
                                            <?php
                                            $status_badge = 'default';
                                            if ($customer['status'] == 'active' || $customer['status'] == '1') {
                                                $status_badge = 'success';
                                                $customer['status'] = 'active';
                                            } elseif ($customer['status'] == 'inactive' || $customer['status'] == '0') {
                                                $status_badge = 'warning';
                                                $customer['status'] = 'inactive';
                                            }
                                            ?>
                                            <span class="label label-<?php echo $status_badge; ?>"><?php echo $customer['status']; ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-cog"></i> <?php echo _l('options'); ?> <span class="caret"></span>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <?php if (has_permission('beatroute_integration', '', 'edit')) { ?>
                                                        <li>
                                                            <a href="<?php echo admin_url('beatroute_integration/sync?type=customers&id=' . $customer['id']); ?>">
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
                    // Extract pagination information from live_customers
                    $current_page = isset($live_customers['pagination']['currentPage']) ? $live_customers['pagination']['currentPage'] : 1;
                    $items_per_page = isset($live_customers['pagination']['perPage']) ? $live_customers['pagination']['perPage'] : 25;
                    $total_pages = isset($live_customers['pagination']['pageCount']) ? $live_customers['pagination']['pageCount'] : 1;
                    $total_items = isset($live_customers['pagination']['totalCount']) ? $live_customers['pagination']['totalCount'] : 0;

                    if ($total_pages > 1) { 
                    ?>
                    <!-- Pagination Controls -->
                    <div class="row">
                        <div class="col-md-12">
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <?php if ($current_page > 1) { ?>
                                    <li>
                                        <a href="<?php echo admin_url('beatroute_integration/customers?page='.($current_page-1).'&limit='.$items_per_page); ?>" aria-label="Previous">
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
                                        echo '<li><a href="'.admin_url('beatroute_integration/customers?page=1&limit='.$items_per_page).'">1</a></li>';
                                        if ($start_page > 2) {
                                            echo '<li class="disabled"><a href="#">...</a></li>';
                                        }
                                    }

                                    // Show page numbers
                                    for ($i = $start_page; $i <= $end_page; $i++) {
                                        $active = $i == $current_page ? 'class="active"' : '';
                                        echo '<li '.$active.'><a href="'.admin_url('beatroute_integration/customers?page='.$i.'&limit='.$items_per_page).'">'.$i.'</a></li>';
                                    }

                                    // Show last page if not in range
                                    if ($end_page < $total_pages) {
                                        if ($end_page < $total_pages - 1) {
                                            echo '<li class="disabled"><a href="#">...</a></li>';
                                        }
                                        echo '<li><a href="'.admin_url('beatroute_integration/customers?page='.$total_pages.'&limit='.$items_per_page).'">'.$total_pages.'</a></li>';
                                    }
                                    ?>

                                    <?php if ($current_page < $total_pages) { ?>
                                    <li>
                                        <a href="<?php echo admin_url('beatroute_integration/customers?page='.($current_page+1).'&limit='.$items_per_page); ?>" aria-label="Next">
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

<!-- Customer Details Modal -->
<div class="modal fade" id="customer_details_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo _l('customer_details'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td><strong><?php echo _l('id'); ?></strong></td>
                                    <td id="customer_id"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('beatroute_id'); ?></strong></td>
                                    <td id="customer_beatroute_id"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('first_name'); ?></strong></td>
                                    <td id="customer_first_name"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('last_name'); ?></strong></td>
                                    <td id="customer_last_name"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('email'); ?></strong></td>
                                    <td id="customer_email"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('phone'); ?></strong></td>
                                    <td id="customer_phone"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('address'); ?></strong></td>
                                    <td id="customer_address"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('city'); ?></strong></td>
                                    <td id="customer_city"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('state'); ?></strong></td>
                                    <td id="customer_state"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('zip'); ?></strong></td>
                                    <td id="customer_zip"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('country'); ?></strong></td>
                                    <td id="customer_country"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('status'); ?></strong></td>
                                    <td id="customer_status"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('last_sync'); ?></strong></td>
                                    <td id="customer_last_sync"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('sync_status'); ?></strong></td>
                                    <td id="customer_sync_status"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('created_at'); ?></strong></td>
                                    <td id="customer_created_at"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('updated_at'); ?></strong></td>
                                    <td id="customer_updated_at"></td>
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

<script src="<?php echo base_url('modules/beatroute_integration/assets/js/beatroute.js'); ?>"></script>
<script>
    function viewCustomerDetails(id) {
        $.get(admin_url + 'beatroute_integration/get_customer_details/' + id, function(response) {
            var customer = JSON.parse(response);

            $('#customer_id').text(customer.id);
            $('#customer_beatroute_id').text(customer.beatroute_id);
            $('#customer_first_name').text(customer.first_name);
            $('#customer_last_name').text(customer.last_name);
            $('#customer_email').text(customer.email);
            $('#customer_phone').text(customer.phone || '-');
            $('#customer_address').text(customer.address || '-');
            $('#customer_city').text(customer.city || '-');
            $('#customer_state').text(customer.state || '-');
            $('#customer_zip').text(customer.zip || '-');
            $('#customer_country').text(customer.country || '-');

            var status_badge = 'default';
            if (customer.status == 'active') {
                status_badge = 'success';
            } else if (customer.status == 'inactive') {
                status_badge = 'warning';
            }
            $('#customer_status').html('<span class="label label-' + status_badge + '">' + customer.status + '</span>');

            // Use formatDateTime function if moment is not available
            if (typeof moment !== 'undefined') {
                $('#customer_last_sync').text(moment(customer.last_sync).format('YYYY-MM-DD HH:mm:ss'));
                $('#customer_created_at').text(moment(customer.created_at).format('YYYY-MM-DD HH:mm:ss'));
                $('#customer_updated_at').text(moment(customer.updated_at).format('YYYY-MM-DD HH:mm:ss'));
            } else {
                $('#customer_last_sync').text(formatDateTime(customer.last_sync));
                $('#customer_created_at').text(formatDateTime(customer.created_at));
                $('#customer_updated_at').text(formatDateTime(customer.updated_at));
            }

            var sync_badge = 'default';
            if (customer.sync_status == 'synced') {
                sync_badge = 'success';
            } else if (customer.sync_status == 'pending') {
                sync_badge = 'warning';
            } else if (customer.sync_status == 'failed') {
                sync_badge = 'danger';
            }
            $('#customer_sync_status').html('<span class="label label-' + sync_badge + '">' + customer.sync_status + '</span>');

            $('#customer_details_modal').modal('show');
        });
    }
</script>
</div>
</div>
<?php init_footer(); ?>
