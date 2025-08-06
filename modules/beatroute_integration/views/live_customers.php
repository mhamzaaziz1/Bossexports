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
                                <h4 class="no-margin"><?php echo _l('beatroute_live_customers'); ?></h4>
                                <p class="text-muted"><?php echo _l('live_data_from_beatroute_v2'); ?></p>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?php echo admin_url('beatroute_integration/customers'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('back_to_customers'); ?>
                                </a>
                            </div>
                        </div>
                        <hr class="hr-panel-heading" />

                        <!-- Search Form -->
                        <div class="row">
                            <div class="col-md-12">
                                <form method="get" action="<?php echo admin_url('beatroute_integration/live_customers'); ?>" id="search-form">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <input type="text" name="search" id="search" class="form-control" placeholder="<?php echo _l('search_placeholder'); ?>" value="<?php echo isset($search) ? $search : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <select name="limit" id="limit" class="form-control selectpicker">
                                                    <option value="10" <?php if (isset($limit) && $limit == 10) echo 'selected'; ?>>10 <?php echo _l('per_page'); ?></option>
                                                    <option value="25" <?php if (!isset($limit) || $limit == 25) echo 'selected'; ?>>25 <?php echo _l('per_page'); ?></option>
                                                    <option value="50" <?php if (isset($limit) && $limit == 50) echo 'selected'; ?>>50 <?php echo _l('per_page'); ?></option>
                                                    <option value="100" <?php if (isset($limit) && $limit == 100) echo 'selected'; ?>>100 <?php echo _l('per_page'); ?></option>
                                                    <option value="250" <?php if (isset($limit) && $limit == 250) echo 'selected'; ?>>250 <?php echo _l('per_page'); ?></option>
                                                    <option value="500" <?php if (isset($limit) && $limit == 500) echo 'selected'; ?>>500 <?php echo _l('per_page'); ?></option>
                                                    <option value="1000" <?php if (isset($limit) && $limit == 1000) echo 'selected'; ?>>1000 <?php echo _l('per_page'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-info btn-block"><?php echo _l('search'); ?></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Results -->
                        <div class="row mtop20">
                            <div class="col-md-12">
                                <?php 
                                // Check if live_customers is set and has a data key
                                if (!isset($live_customers) || !isset($live_customers['data']) || empty($live_customers['data'])) { 
                                ?>
                                    <div class="alert alert-info">
                                        <?php echo _l('no_live_customers_found'); ?>
                                    </div>
                                <?php } else { ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th><?php echo _l('id'); ?></th>
                                                    <th><?php echo _l('first_name'); ?></th>
                                                    <th><?php echo _l('last_name'); ?></th>
                                                    <th><?php echo _l('email'); ?></th>
                                                    <th><?php echo _l('phone'); ?></th>
                                                    <th><?php echo _l('address'); ?></th>
                                                    <th><?php echo _l('city'); ?></th>
                                                    <th><?php echo _l('state'); ?></th>
                                                    <th><?php echo _l('zip'); ?></th>
                                                    <th><?php echo _l('country'); ?></th>
                                                    <th><?php echo _l('status'); ?></th>
                                                    <th><?php echo _l('actions'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                // Ensure data is an array before iterating
                                                if (is_array($live_customers['data'])) {
                                                    foreach ($live_customers['data'] as $customer) { 
                                                        // Ensure customer is an array before displaying
                                                        if (!is_array($customer)) continue;
                                                ?>
                                                    <tr>
                                                        <td><?php echo isset($customer['external_id']) ? $customer['external_id'] : '-'; ?></td>
                                                        <td><?php echo isset($customer['name']) ? $customer['name'] : '-'; ?></td>
                                                        <td><?php echo isset($customer['last_name']) ? $customer['last_name'] : '-'; ?></td>
                                                        <td><?php echo isset($customer['email']) ? $customer['email'] : '-'; ?></td>
                                                        <td><?php echo isset($customer['phone']) ? $customer['phone'] : '-'; ?></td>
                                                        <td><?php echo isset($customer['address']) ? $customer['address'] : '-'; ?></td>
                                                        <td><?php echo isset($customer['city']) ? $customer['city'] : '-'; ?></td>
                                                        <td><?php echo isset($customer['state']) ? $customer['state'] : '-'; ?></td>
                                                        <td><?php echo isset($customer['zip']) ? $customer['zip'] : '-'; ?></td>
                                                        <td><?php echo isset($customer['country']) ? $customer['country'] : '-'; ?></td>
                                                        <td>
                                                            <?php
                                                            $status_badge = 'default';
                                                            $status_text = 'Unknown';

                                                            if (isset($customer['status'])) {
                                                                if ($customer['status'] == 'active') {
                                                                    $status_badge = 'success';
                                                                    $status_text = 'Active';
                                                                } else if ($customer['status'] == 'inactive') {
                                                                    $status_badge = 'warning';
                                                                    $status_text = 'Inactive';
                                                                } else {
                                                                    $status_text = $customer['status'];
                                                                }
                                                            }
                                                            ?>
                                                            <span class="label label-<?php echo $status_badge; ?>"><?php echo $status_text; ?></span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <?php echo _l('actions'); ?> <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-right">
                                                                    <li>
                                                                        <a href="<?php echo admin_url('beatroute_integration/sync?type=customers&id=' . $customer['id']); ?>">
                                                                            <i class="fa fa-refresh"></i> <?php echo _l('sync_to_local'); ?>
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php 
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <?php if (isset($live_customers['pagination']) && isset($live_customers['pagination']['pageCount']) && $live_customers['pagination']['pageCount'] > 1) { ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination">
                                                    <?php 
                                                    $current_page = isset($live_customers['pagination']['currentPage']) ? $live_customers['pagination']['currentPage'] : 1;
                                                    $total_pages = isset($live_customers['pagination']['pageCount']) ? $live_customers['pagination']['pageCount'] : 1;
                                                    
                                                    // Previous page link
                                                    if ($current_page > 1) { ?>
                                                    <li>
                                                        <a href="<?php echo admin_url('beatroute_integration/live_customers?page=' . ($current_page - 1) . '&limit=' . $limit . (isset($search) && !empty($search) ? '&search=' . urlencode($search) : '')); ?>" aria-label="Previous">
                                                            <span aria-hidden="true">&laquo;</span>
                                                        </a>
                                                    </li>
                                                    <?php } 
                                                    
                                                    // Calculate range of page numbers to show
                                                    $start_page = max(1, $current_page - 2);
                                                    $end_page = min($total_pages, $current_page + 2);
                                                    
                                                    // Show first page if not in range
                                                    if ($start_page > 1) { ?>
                                                    <li>
                                                        <a href="<?php echo admin_url('beatroute_integration/live_customers?page=1&limit=' . $limit . (isset($search) && !empty($search) ? '&search=' . urlencode($search) : '')); ?>">1</a>
                                                    </li>
                                                    <?php if ($start_page > 2) { ?>
                                                    <li class="disabled">
                                                        <a href="#">...</a>
                                                    </li>
                                                    <?php } 
                                                    } 
                                                    
                                                    // Page numbers
                                                    for ($i = $start_page; $i <= $end_page; $i++) { ?>
                                                    <li <?php if ($i == $current_page) echo 'class="active"'; ?>>
                                                        <a href="<?php echo admin_url('beatroute_integration/live_customers?page=' . $i . '&limit=' . $limit . (isset($search) && !empty($search) ? '&search=' . urlencode($search) : '')); ?>"><?php echo $i; ?></a>
                                                    </li>
                                                    <?php } 
                                                    
                                                    // Show last page if not in range
                                                    if ($end_page < $total_pages) { 
                                                    if ($end_page < $total_pages - 1) { ?>
                                                    <li class="disabled">
                                                        <a href="#">...</a>
                                                    </li>
                                                    <?php } ?>
                                                    <li>
                                                        <a href="<?php echo admin_url('beatroute_integration/live_customers?page=' . $total_pages . '&limit=' . $limit . (isset($search) && !empty($search) ? '&search=' . urlencode($search) : '')); ?>"><?php echo $total_pages; ?></a>
                                                    </li>
                                                    <?php } 
                                                    
                                                    // Next page link
                                                    if ($current_page < $total_pages) { ?>
                                                    <li>
                                                        <a href="<?php echo admin_url('beatroute_integration/live_customers?page=' . ($current_page + 1) . '&limit=' . $limit . (isset($search) && !empty($search) ? '&search=' . urlencode($search) : '')); ?>" aria-label="Next">
                                                            <span aria-hidden="true">&raquo;</span>
                                                        </a>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_footer(); ?>