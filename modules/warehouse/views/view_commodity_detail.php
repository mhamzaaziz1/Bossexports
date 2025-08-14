<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix"></div>
                        <h4>
                            <?php echo html_entity_decode($commodity_item->description); ?>
                        </h4>


                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <div class="col-md-12">

                            <div class="row col-md-12">

                                <h4 class="h4-color"><?php echo _l('general_infor'); ?></h4>
                                <hr class="hr-color">



                                <div class="col-md-7 panel-padding">
                                    <table class="table border table-striped table-margintop">
                                        <tbody>

                                        <tr class="project-overview">
                                            <td class="bold" width="30%"><?php echo _l('commodity_code'); ?></td>
                                            <td><?php echo html_entity_decode($commodity_item->commodity_code) ; ?></td>
                                        </tr>
                                        <tr class="project-overview">
                                            <td class="bold"><?php echo _l('commodity_name'); ?></td>
                                            <td><?php echo html_entity_decode($commodity_item->description) ; ?></td>
                                        </tr>
                                        <tr class="project-overview">
                                            <td class="bold"><?php echo _l('commodity_group'); ?></td>
                                            <td><?php echo get_wh_group_name(html_entity_decode($commodity_item->group_id)) != null ? get_wh_group_name(html_entity_decode($commodity_item->group_id))->name : '' ; ?></td>
                                        </tr>
                                        <tr class="project-overview">
                                            <td class="bold"><?php echo _l('commodity_barcode'); ?></td>
                                            <td><?php echo html_entity_decode($commodity_item->commodity_barcode) ; ?></td>
                                        </tr>
                                        <tr class="project-overview">
                                            <td class="bold"><?php echo _l('sku_code'); ?></td>
                                            <td><?php echo html_entity_decode($commodity_item->sku_code) ; ?></td>
                                        </tr>
                                        <tr class="project-overview">
                                            <td class="bold"><?php echo _l('sku_name'); ?></td>
                                            <td><?php echo html_entity_decode($commodity_item->sku_name) ; ?></td>
                                        </tr>

                                        <tr class="project-overview">
                                            <td class="bold"><?php echo _l('tags'); ?></td>
                                            <td>
                                                <div class="form-group">
                                                    <div id="inputTagsWrapper">
                                                        <input type="text" class="tagsinput" id="tags" name="tags" value="<?php echo (isset($commodity_item) ? prep_tags_input(get_tags_in($commodity_item->id,'item_tags')) : ''); ?>" data-role="tagsinput">
                                                    </div>
                                                </div>

                                            </td>
                                        </tr>



                                        </tbody>
                                    </table>
                                </div>

                                <div class="gallery">
                                    <div class="wrapper-masonry">
                                        <div id="masonry" class="masonry-layout columns-3">
                                            <?php if(isset($commodity_file) && count($commodity_file) > 0){ ?>
                                                <?php foreach ($commodity_file as $key => $value) { ?>

                                                    <?php if(file_exists(WAREHOUSE_ITEM_UPLOAD .$value["rel_id"].'/'.$value["file_name"])){ ?>
                                                        <a  class="images_w_table" href="<?php echo site_url('modules/warehouse/uploads/item_img/'.$value["rel_id"].'/'.$value["file_name"]); ?>"><img style="width:300px;height:300px;" src="<?php echo site_url('modules/warehouse/uploads/item_img/'.$value["rel_id"].'/'.$value["file_name"]); ?>" alt="<?php echo html_entity_decode($value['file_name']) ?>"/></a>

                                                    <?php }elseif(file_exists('modules/purchase/uploads/item_img/' . $value["rel_id"] . '/' . $value["file_name"])) { ?>
                                                        <a  class="images_w_table" href="<?php echo site_url('modules/purchase/uploads/item_img/'.$value["rel_id"].'/'.$value["file_name"]); ?>"><img  src="<?php echo site_url('modules/purchase/uploads/item_img/'.$value["rel_id"].'/'.$value["file_name"]); ?>" alt="<?php echo html_entity_decode($value['file_name']) ?>"/></a>


                                                    <?php } ?>


                                                <?php } ?>
                                            <?php }else{ ?>

                                                <a  href="<?php echo site_url('modules/warehouse/uploads/nul_image.jpg'); ?>"><img class="images_w_table" src="<?php echo site_url('modules/warehouse/uploads/nul_image.jpg'); ?>" alt="nul_image.jpg"/></a>

                                            <?php } ?>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                            </div>


                            <h4 class="h4-color"><?php echo _l('infor_detail'); ?></h4>
                            <hr class="hr-color">
                            <div class="col-md-6 panel-padding" >
                                <table class="table border table-striped table-margintop" >
                                    <tbody>
                                    <tr class="project-overview">
                                        <td class="bold td-width"><?php echo _l('origin'); ?></td>
                                        <td><?php echo html_entity_decode($commodity_item->origin) ; ?></td>
                                    </tr>
                                    <tr class="project-overview">
                                        <td class="bold"><?php echo _l('colors'); ?></td>
                                        <?php
                                        $color_value ='';
                                        if($commodity_item->color){
                                            $color = get_color_type($commodity_item->color);
                                            if($color){
                                                $color_value .= $color->color_code.'_'.$color->color_name;
                                            }
                                        }
                                        ?>
                                        <td><?php echo html_entity_decode($color_value) ; ?></td>
                                    </tr>
                                    <tr class="project-overview">
                                        <td class="bold"><?php echo _l('styles'); ?></td>
                                        <td><?php  if($commodity_item->style_id != null){ echo get_style_name(html_entity_decode($commodity_item->style_id)) != null ? get_style_name(html_entity_decode($commodity_item->style_id))->style_name : '';}else{echo '';} ?></td>
                                    </tr>

                                    <tr class="project-overview">
                                        <td class="bold"><?php echo _l('rate'); ?></td>
                                        <td><?php echo app_format_money((float)$commodity_item->rate,'') ; ?></td>
                                    </tr>

                                    <tr class="project-overview">
                                        <td class="bold"><?php echo _l('_profit_rate_p'); ?></td>
                                        <td><?php echo html_entity_decode($commodity_item->profif_ratio) ; ?></td>
                                    </tr>

                                    <tr class="project-overview">
                                        <td class="bold"><?php echo "ECOMM "._l('rate'); ?></td>
                                        <td><?php echo app_format_money((float)$commodity_item->ECOMM,'') ; ?></td>
                                    </tr>
                                    <tr class="project-overview">
                                        <td class="bold"><?php echo "SELLER "._l('rate'); ?></td>
                                        <td><?php echo app_format_money((float)$commodity_item->SELLER,'') ; ?></td>
                                    </tr>
                                    <tr class="project-overview">
                                        <td class="bold"><?php echo "RETAILER "._l('rate'); ?></td>
                                        <td><?php echo app_format_money((float)$commodity_item->RETAILER,'') ; ?></td>
                                    </tr>
                                    <tr class="project-overview">
                                        <td class="bold"><?php echo "WHOLESALER "._l('rate'); ?></td>
                                        <td><?php echo app_format_money((float)$commodity_item->WHOLESALER,'') ; ?></td>
                                    </tr>
                                    <tr class="project-overview">
                                        <td class="bold"><?php echo _l('status'); ?></td>
                                        <td>
                                            <?php if ($commodity_item->isactive){
                                                echo "Active";
                                            }else{
                                                echo "In-Active";
                                            } ?>
                                        </td>
                                    </tr>


                                    </tbody>
                                </table>
                            </div>

                            <div class="col-md-6 panel-padding" >
                                <table class="table table-striped table-margintop">
                                    <tbody>
                                    <tr class="project-overview">
                                        <td class="bold" width="40%"><?php echo _l('model_id'); ?></td>
                                        <td><?php if($commodity_item->style_id != null){ echo get_model_name(html_entity_decode($commodity_item->model_id)) != null ? get_model_name(html_entity_decode($commodity_item->model_id))->body_name : ''; }else{echo '';}?></td>
                                    </tr>
                                    <tr class="project-overview">
                                        <td class="bold"><?php echo _l('size_id'); ?></td>

                                        <td><?php if($commodity_item->style_id != null){ echo get_size_name(html_entity_decode($commodity_item->size_id)) != null ? get_size_name(html_entity_decode($commodity_item->size_id))->size_name : ''; }else{ echo '';}?></td>
                                    </tr>

                                    <tr class="project-overview">
                                        <td class="bold"><?php echo _l('unit_id'); ?></td>
                                        <td><?php echo  $commodity_item->unit_id != '' && get_unit_type($commodity_item->unit_id) != null ? get_unit_type($commodity_item->unit_id)->unit_name : ''; ?></td>
                                    </tr>

                                    <tr class="project-overview">
                                        <td class="bold"><?php echo _l('purchase_price'); ?></td>
                                        <td><?php echo app_format_money((float)$commodity_item->purchase_price,'') ; ?></td>
                                    </tr>

                                    <tr class="project-overview">
                                        <td class="bold"><?php echo _l('guarantee'); ?></td>
                                        <td><?php echo html_entity_decode($commodity_item->guarantee) ._l('month_label'); ?></td>
                                    </tr>



                                    </tbody>
                                </table>
                            </div>
                            <div class=" row ">
                                <div class="col-md-12">
                                    <h4 class="h4-color"><?php echo _l('description'); ?></h4>
                                    <hr class="hr-color">
                                    <h5><?php echo html_entity_decode($commodity_item->long_description) ; ?></h5>

                                </div>

                            </div>

                            <div class=" row ">
                                <div class="col-md-12">
                                    <h4 class="h4-color"><?php echo _l('long_description'); ?></h4>
                                    <hr class="hr-color">
                                    <h5><?php echo html_entity_decode($commodity_item->long_descriptions) ; ?></h5>

                                </div>
                            </div>

                            <div class=" row ">
                                <div class="col-md-12">
                                    <h4 class="h4-color"><?php echo _l('sales_and_purchases'); ?></h4>
                                    <hr class="hr-color">

                                    <div class="row">
                                        <!-- This month's sale table -->
                                        <div class="col-md-6">
                                            <div class="panel_s">
                                                <div class="panel-body">
                                                    <h4><?php echo _l('this_month_sales'); ?></h4>
                                                    <hr />
                                                    <table class="table table-bordered table-striped">
                                                        <thead>
                                                        <tr>
                                                            <th><?php echo _l('customer'); ?></th>
                                                            <th><?php echo _l('quantity'); ?></th>
                                                            <th><?php echo _l('amount'); ?></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php
                                                        $current_month = date('Y-m-01');
                                                        $next_month = date('Y-m-01', strtotime('+1 month'));
                                                        $date_filter = "i.date >= '$current_month' AND i.date < '$next_month'";
                                                        $sales_data = $this->reports_model->get_buyers_by_item($commodity_item->id, 'sales', 'top', 10, 'amount', $date_filter);

                                                        if (empty($sales_data)) {
                                                            echo '<tr><td colspan="3" class="text-center">' . _l('no_data_available') . '</td></tr>';
                                                        } else {
                                                            foreach ($sales_data as $sale) {
                                                                echo '<tr>';
                                                                echo '<td>' . $sale['name'] . '</td>';
                                                                echo '<td>' . number_format($sale['total_quantity'], 2) . '</td>';
                                                                echo '<td>' . app_format_money($sale['total_amount'], '') . '</td>';
                                                                echo '</tr>';
                                                            }
                                                        }
                                                        ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- This month's purchase table -->
                                        <div class="col-md-6">
                                            <div class="panel_s">
                                                <div class="panel-body">
                                                    <h4><?php echo _l('this_month_purchases'); ?></h4>
                                                    <hr />
                                                    <table class="table table-bordered table-striped">
                                                        <thead>
                                                        <tr>
                                                            <th><?php echo _l('vendor'); ?></th>
                                                            <th><?php echo _l('quantity'); ?></th>
                                                            <th><?php echo _l('amount'); ?></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php
                                                        $current_month = date('Y-m-01');
                                                        $next_month = date('Y-m-01', strtotime('+1 month'));
                                                        $date_filter = "po.order_date >= '$current_month' AND po.order_date < '$next_month'";
                                                        $purchase_data = $this->reports_model->get_buyers_by_item($commodity_item->id, 'purchases', 'top', 10, 'amount', $date_filter);

                                                        if (empty($purchase_data)) {
                                                            echo '<tr><td colspan="3" class="text-center">' . _l('no_data_available') . '</td></tr>';
                                                        } else {
                                                            foreach ($purchase_data as $purchase) {
                                                                echo '<tr>';
                                                                echo '<td>' . $purchase['name'] . '</td>';
                                                                echo '<td>' . number_format($purchase['total_quantity'], 2) . '</td>';
                                                                echo '<td>' . app_format_money($purchase['total_amount'], '') . '</td>';
                                                                echo '</tr>';
                                                            }
                                                        }
                                                        ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mtop20">
                                        <!-- Average sale chart with trend line -->
                                        <div class="col-md-6">
                                            <div class="panel_s">
                                                <div class="panel-body">
                                                    <h4><?php echo _l('avg_sales_by_month'); ?></h4>
                                                    <hr />
                                                    <div class="relative" style="height:350px">
                                                        <canvas id="sales_chart" height="350"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Average purchase chart with trend line -->
                                        <div class="col-md-6">
                                            <div class="panel_s">
                                                <div class="panel-body">
                                                    <h4><?php echo _l('avg_purchases_by_month'); ?></h4>
                                                    <hr />
                                                    <div class="relative" style="height:350px">
                                                        <canvas id="purchases_chart" height="350"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>



                            <table class="table border table-striped ">
                                <tbody>
                                <tr class="project-overview">
                                    <td colspan="2">
                                        <div class="horizontal-scrollable-tabs preview-tabs-top">
                                            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                                            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                                            <div class="horizontal-tabs">
                                                <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">

                                                    <li role="presentation" class="active">
                                                        <a href="#out_of_stock" aria-controls="out_of_stock" role="tab" id="tab_out_of_stock" data-toggle="tab">
                                                            <?php echo _l('inventory_stock') ?>
                                                        </a>
                                                    </li>

                                                    <li role="presentation" >
                                                        <a href="#expiry_date" aria-controls="expiry_date" role="tab" id="tab_expiry_date" data-toggle="tab">
                                                            <?php echo _l('expiry_date') ?>
                                                        </a>
                                                    </li>

                                                    <li role="presentation">
                                                        <a href="#history" aria-controls="history" role="tab" id="tab_history" data-toggle="tab">
                                                            <?php echo _l('transaction_history') ?>
                                                        </a>
                                                    </li>
                                                    <li role="presentation">
                                                        <a href="#so" aria-controls="so" role="tab" id="tab_so" data-toggle="tab">
                                                            <?php echo _l('PO') ?>
                                                        </a>
                                                    </li>
                                                    <li role="presentation">
                                                        <a href="#po" aria-controls="po" role="tab" id="tab_po" data-toggle="tab">
                                                            <?php echo _l('SO') ?>
                                                        </a>
                                                    </li>

                                                    <li role="presentation">
                                                        <a href="#top_buyers" aria-controls="top_buyers" role="tab" id="tab_top_buyers" data-toggle="tab">
                                                            <?php echo _l('top_buyers_per_item') ?>
                                                        </a>
                                                    </li>

                                                    <li role="presentation">
                                                        <a href="#custom_fields" aria-controls="custom_fields" role="tab" id="tab_custom_fields" data-toggle="tab">
                                                            <?php echo _l('custom_fields') ?>
                                                        </a>
                                                    </li>

                                                </ul>
                                            </div>
                                        </div>

                                        <div class="tab-content col-md-12">

                                            <div role="tabpanel" class="tab-pane active row" id="out_of_stock">
                                                <?php render_datatable(array(
                                                    _l('id'),
                                                    _l('commodity_name'),
                                                    _l('expiry_date'),
                                                    _l('lot_number'),
                                                    _l('warehouse_name'),

                                                    _l('inventory_number'),
                                                    _l('unit_name'),
                                                    _l('rate'),
                                                    _l('purchase_price'),
                                                    _l('tax'),
                                                    _l('status_label'),

                                                ),'table_inventory_stock'); ?>
                                            </div>

                                            <div role="tabpanel" class="tab-pane  row" id="expiry_date">
                                                <?php render_datatable(array(
                                                    _l('commodity_name'),
                                                    _l('expiry_date'),
                                                    _l('lot_number'),
                                                    _l('warehouse_name'),

                                                    _l('inventory_number'),
                                                    _l('unit_name'),
                                                    _l('rate'),
                                                    _l('purchase_price'),
                                                    _l('tax'),
                                                    _l('status_label'),

                                                ),'table_view_commodity_detail',['proposal_sm' => 'proposal_sm']); ?>
                                            </div>

                                            <div role="tabpanel" class="tab-pane row" id="history">
                                                <?php render_datatable(array(
                                                    _l('id'),
                                                    _l('form_code'),
                                                    _l('Vendor'),
                                                    _l('commodity_code'),
                                                    _l('description'),
                                                    _l('warehouse_code'),
                                                    _l('warehouse_name'),
                                                    _l('day_vouchers'),
                                                    _l('old_quantity'),
                                                    _l('new_quantity'),
                                                    _l('expiry_date'),
                                                    _l('Unit Bought / Sold'),
                                                    _l('status_label'),
                                                ),'table_warehouse_history'); ?>
                                            </div>

                                            <div role="tabpanel" class="tab-pane row" id="po">
                                                <?php render_datatable(array(
                                                    _l('id'),
                                                    _l('#'),
                                                    _l('Date'),
                                                    _l('Customer'),
                                                    _l('Qty'),
                                                ),'table_warehouse_PO'); ?>
                                            </div>

                                            <div role="tabpanel" class="tab-pane row" id="so">
                                                <?php render_datatable(array(
                                                    _l('id'),
                                                    _l('#'),
                                                    _l('Date'),
                                                    _l('Vendor'),
                                                    _l('Qty'),
                                                ),'table_warehouse_so'); ?>
                                            </div>

                                            <div role="tabpanel" class="tab-pane row" id="top_buyers">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="panel_s">
                                                            <div class="panel-body">
                                                                <h4><?php echo _l('top_buyers_per_item'); ?></h4>
                                                                <hr class="hr-panel-heading" />

                                                                <?php echo form_open(admin_url('reports/top_buyers_per_item'), ['method' => 'post', 'id' => 'top-buyers-per-item-form', 'target' => 'top_buyers_iframe']); ?>
                                                                <input type="hidden" name="item_id" value="<?php echo $commodity_item->id; ?>">
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="transaction_type"><?php echo _l('transaction_type'); ?></label>
                                                                            <select name="transaction_type" id="transaction_type" class="form-control selectpicker">
                                                                                <option value="both"><?php echo _l('both_sales_and_purchases'); ?></option>
                                                                                <option value="sales"><?php echo _l('sales'); ?></option>
                                                                                <option value="purchases"><?php echo _l('purchases'); ?></option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="ranking"><?php echo _l('ranking'); ?></label>
                                                                            <select name="ranking" id="ranking" class="form-control selectpicker">
                                                                                <option value="top"><?php echo _l('top_buyers'); ?></option>
                                                                                <option value="least"><?php echo _l('least_buyers'); ?></option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="limit"><?php echo _l('limit'); ?></label>
                                                                            <input type="number" name="limit" id="limit" class="form-control" min="1" max="100" value="10">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="metric"><?php echo _l('metric'); ?></label>
                                                                            <select name="metric" id="metric" class="form-control selectpicker">
                                                                                <option value="quantity"><?php echo _l('quantity'); ?></option>
                                                                                <option value="amount"><?php echo _l('amount'); ?></option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-8">
                                                                        <div class="form-group mtop25">
                                                                            <button type="submit" class="btn btn-info"><?php echo _l('generate_report'); ?></button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php echo form_close(); ?>

                                                                <div class="row mtop20">
                                                                    <div class="col-md-12">
                                                                        <iframe name="top_buyers_iframe" id="top_buyers_iframe" style="width: 100%; min-height: 400px; border: 0;"></iframe>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div role="tabpanel" class="tab-pane row" id="custom_fields">
                                                <?php echo render_custom_fields('items',$commodity_item->id,[],['items_pr' => true]); ?>
                                            </div>

                                        </div>
                                    </td>
                                </tr>



                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo form_hidden('commodity_id'); ?>

<?php init_tail(); ?>
<?php require 'modules/warehouse/assets/js/view_commodity_detail_js.php';?>
<?php require 'modules/warehouse/assets/js/commodity_detail_js.php';?>
<script>
    // Function to get monthly data for the current year
    function getMonthlyData(transaction_type) {
        var result = [];
        var commodity_id = <?php echo $commodity_item->id; ?>;
        var current_year = <?php echo date('Y'); ?>;

        // Make an AJAX request to get the data
        $.ajax({
            url: admin_url + 'warehouse/get_commodity_monthly_data',
            type: 'POST',
            data: {
                commodity_id: commodity_id,
                transaction_type: transaction_type,
                year: current_year
            },
            dataType: 'json',
            async: false,
            success: function(response) {
                result = response;
            }
        });

        return result;
    }

    // Initialize charts when document is ready
    $(function() {
        // Sales Chart
        var salesCtx = document.getElementById('sales_chart').getContext('2d');
        var salesData = getMonthlyData('sales');

        var salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    {
                        label: '<?php echo _l("average_sales"); ?>',
                        data: salesData.amounts,
                        backgroundColor: 'rgba(66, 133, 244, 0.2)',
                        borderColor: 'rgba(66, 133, 244, 1)',
                        borderWidth: 2,
                        fill: true
                    },
                    {
                        label: '<?php echo _l("trend_line"); ?>',
                        data: salesData.trend,
                        backgroundColor: 'transparent',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return app.format_money(value);
                            }
                        }
                    }
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return data.datasets[tooltipItem.datasetIndex].label + ': ' + app.format_money(tooltipItem.yLabel);
                        }
                    }
                }
            }
        });

        // Purchases Chart
        var purchasesCtx = document.getElementById('purchases_chart').getContext('2d');
        var purchasesData = getMonthlyData('purchases');

        var purchasesChart = new Chart(purchasesCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    {
                        label: '<?php echo _l("average_purchases"); ?>',
                        data: purchasesData.amounts,
                        backgroundColor: 'rgba(67, 160, 71, 0.2)',
                        borderColor: 'rgba(67, 160, 71, 1)',
                        borderWidth: 2,
                        fill: true
                    },
                    {
                        label: '<?php echo _l("trend_line"); ?>',
                        data: purchasesData.trend,
                        backgroundColor: 'transparent',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return app.format_money(value);
                            }
                        }
                    }
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return data.datasets[tooltipItem.datasetIndex].label + ': ' + app.format_money(tooltipItem.yLabel);
                        }
                    }
                }
            }
        });
    });
</script>
</body>
</html>
