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
                                <h4 class="no-margin"><?php echo _l('beatroute_live_skus'); ?></h4>
                                <p class="text-muted"><?php echo _l('live_data_from_beatroute_v2'); ?></p>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?php echo admin_url('beatroute_integration/skus'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('back_to_skus'); ?>
                                </a>
                            </div>
                        </div>
                        <hr class="hr-panel-heading" />

                        <!-- Search Form -->
                        <div class="row">
                            <div class="col-md-12">
                                <form method="get" action="<?php echo admin_url('beatroute_integration/live_skus'); ?>" id="search-form">
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
                                // Check if live_skus is set and has a data key
                                if (!isset($live_skus) || !isset($live_skus['data']) || empty($live_skus['data'])) { 
                                ?>
                                    <div class="alert alert-info">
                                        <?php echo _l('no_live_skus_found'); ?>
                                    </div>
                                <?php } else { ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th><?php echo _l('id'); ?></th>
                                                    <th><?php echo _l('sku_external_id'); ?></th>
                                                    <th><?php echo _l('description'); ?></th>
                                                    <th><?php echo _l('detail_description'); ?></th>
                                                    <th><?php echo _l('uom'); ?></th>
                                                    <th><?php echo _l('brand'); ?></th>
                                                    <th><?php echo _l('category'); ?></th>
                                                    <th><?php echo _l('status'); ?></th>
                                                    <th><?php echo _l('created_at'); ?></th>
                                                    <th><?php echo _l('updated_at'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                // Ensure data is an array before iterating
                                                if (is_array($live_skus['data'])) {
                                                    foreach ($live_skus['data'] as $sku) { 
                                                        // Ensure sku is an array before displaying
                                                        if (!is_array($sku)) continue;
                                                ?>
                                                    <tr>
                                                        <td><?php echo isset($sku['sku_br_id']) ? $sku['sku_br_id'] : '-'; ?></td>
                                                        <td><?php echo isset($sku['sku_external_id']) ? $sku['sku_external_id'] : '-'; ?></td>
                                                        <td><?php echo isset($sku['description']) ? $sku['description'] : '-'; ?></td>
                                                        <td><?php echo isset($sku['detail_description']) ? $sku['detail_description'] : '-'; ?></td>
                                                        <td><?php echo isset($sku['uom']) ? $sku['uom'] : '-'; ?></td>
                                                        <td><?php echo isset($sku['brand_name']) ? $sku['brand_name'] : '-'; ?></td>
                                                        <td><?php echo isset($sku['category_name']) ? $sku['category_name'] : '-'; ?></td>
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
                                                            <?php 
                                                            if (isset($sku['created_date'])) {
                                                                // Check if the date is valid before formatting
                                                                try {
                                                                    echo _dt($sku['created_date']);
                                                                } catch (Exception $e) {
                                                                    echo $sku['created_date']; // Display as is if formatting fails
                                                                }
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            if (isset($sku['updated_date'])) {
                                                                // Check if the date is valid before formatting
                                                                try {
                                                                    echo _dt($sku['updated_date']);
                                                                } catch (Exception $e) {
                                                                    echo $sku['updated_date']; // Display as is if formatting fails
                                                                }
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php 
                                                    } // End foreach
                                                } // End if is_array
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } // End if !empty ?>
                            </div>
                        </div>

                        <!-- Pagination with Previous and Next buttons -->
                        <?php 
                        $limit = isset($limit) ? $limit : 25;
                        $page = isset($page) ? $page : 1;
                        $total_pages = isset($total_pages) ? $total_pages : 1;

                        if (!empty($live_skus['data']) && $total_pages > 1) { ?>
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <div class="btn-group">
                                        <?php if ($page > 1) { ?>
                                            <a href="<?php echo admin_url('beatroute_integration/live_skus?page=' . ($page - 1) . '&limit=' . $limit . '&search=' . (isset($search) ? $search : '')); ?>" class="btn btn-default">
                                                <i class="fa fa-arrow-left"></i> <?php echo _l('previous'); ?>
                                            </a>
                                        <?php } else { ?>
                                            <button class="btn btn-default" disabled>
                                                <i class="fa fa-arrow-left"></i> <?php echo _l('previous'); ?>
                                            </button>
                                        <?php } ?>

                                        <?php if ($page < $total_pages) { ?>
                                            <a href="<?php echo admin_url('beatroute_integration/live_skus?page=' . ($page + 1) . '&limit=' . $limit . '&search=' . (isset($search) ? $search : '')); ?>" class="btn btn-default">
                                                <?php echo _l('next'); ?> <i class="fa fa-arrow-right"></i>
                                            </a>
                                        <?php } else { ?>
                                            <button class="btn btn-default" disabled>
                                                <?php echo _l('next'); ?> <i class="fa fa-arrow-right"></i>
                                            </button>
                                        <?php } ?>
                                    </div>
                                    <p class="text-muted mtop10">
                                        <?php echo _l('page'); ?> <?php echo $page; ?> <?php echo _l('of'); ?> <?php echo $total_pages; ?>
                                        (<?php echo _l('showing'); ?> <?php echo count($live_skus['data']); ?> <?php echo _l('of'); ?> <?php echo isset($total_items) ? $total_items : 0; ?> <?php echo _l('total_items'); ?>)
                                    </p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url('modules/beatroute_integration/assets/js/beatroute.js'); ?>"></script>
<script>
    $(function() {
        // Auto-submit form when limit changes
        $('#limit').on('change', function() {
            $('#search-form').submit();
        });

        // We're using server-side pagination with custom buttons
        // No DataTables initialization needed

        // Add search functionality through form submission
        $('#search').on('keyup', function(e) {
            if (e.keyCode === 13) {
                $('#search-form').submit();
            }
        });
    });
</script>
<?php init_footer(); ?>
