<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="pull-left">
                            <h4 class="no-margin"><?php echo $title; ?> (<?php echo $report['name']; ?>)</h4>
                        </div>
                        <div class="pull-right">
                            <?php if(isset($is_opti_only) && $is_opti_only == 1){ ?>
                                <?php echo form_open(admin_url('daily_sales_report/send_optimetriks_only')); ?>
                            <?php } else { ?>
                                <?php echo form_open(admin_url('daily_sales_report/send_manual_custom')); ?>
                            <?php } ?>
                            <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                            <input type="hidden" name="report_start_date" value="<?php echo $start_date; ?>">
                            <input type="hidden" name="report_end_date" value="<?php echo $end_date; ?>">
                            
                            <?php if(isset($is_opti_only) && $is_opti_only == 1){ ?>
                                <button type="submit" class="btn btn-info"><i class="fa fa-cloud-upload"></i> Send to Optimetriks Only</button>
                            <?php } else { ?>
                                <button type="submit" class="btn btn-success"><i class="fa fa-envelope"></i> <?php echo _l('daily_sales_report_send_now'); ?></button>
                            <?php } ?>
                            
                            <a href="<?php echo admin_url('daily_sales_report'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>
                            <?php echo form_close(); ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        
                        <div class="horizontal-scrollable-tabs">
                            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                            <div class="horizontal-tabs">
                                <?php $opti_only = isset($is_opti_only) && $is_opti_only == 1; ?>
                                <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                                    <?php if (!$opti_only) { ?>
                                    <li role="presentation" class="active">
                                        <a href="#tab_sales" aria-controls="tab_sales" role="tab" data-toggle="tab">Sales Report CSV</a>
                                    </li>
                                    <?php } ?>
                                    <li role="presentation" class="<?php echo $opti_only ? 'active' : ''; ?>">
                                        <a href="#tab_stock" aria-controls="tab_stock" role="tab" data-toggle="tab">Stock Level CSV</a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#tab_sell_out" aria-controls="tab_sell_out" role="tab" data-toggle="tab">Sell Out CSV</a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#tab_sell_out_lines" aria-controls="tab_sell_out_lines" role="tab" data-toggle="tab">Sell Out Lines CSV</a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#tab_customers" aria-controls="tab_customers" role="tab" data-toggle="tab">Customers CSV</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="tab-content mtop15">
                            <?php if (!$opti_only) { ?>
                            <div role="tabpanel" class="tab-pane active" id="tab_sales">
                                <div class="table-responsive">
                                    <table class="table dt-table">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('daily_sales_report_date'); ?></th>
                                                <th><?php echo _l('daily_sales_report_sku'); ?></th>
                                                <th><?php echo _l('daily_sales_report_product_name'); ?></th>
                                                <th><?php echo _l('daily_sales_report_format'); ?></th>
                                                <th><?php echo _l('daily_sales_report_quantity_sold'); ?></th>
                                                <th><?php echo _l('daily_sales_report_unit_price'); ?></th>
                                                <th><?php echo _l('daily_sales_report_total_amount'); ?></th>
                                                <th><?php echo _l('daily_sales_report_visit_id'); ?></th>
                                                <th><?php echo _l('daily_sales_report_customer_id'); ?></th>
                                                <th><?php echo _l('daily_sales_report_customer'); ?></th>
                                                <th><?php echo _l('daily_sales_report_phone'); ?></th>
                                                <th><?php echo _l('daily_sales_report_channel'); ?></th>
                                                <th><?php echo _l('daily_sales_report_shop_class'); ?></th>
                                                <th><?php echo _l('daily_sales_report_region'); ?></th>
                                                <th><?php echo _l('daily_sales_report_town'); ?></th>
                                                <th><?php echo _l('daily_sales_report_creator'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($sales as $sale){ ?>
                                            <tr>
                                                <td><?php echo $sale['date']; ?></td>
                                                <td><?php echo $sale['sku']; ?></td>
                                                <td><?php echo $sale['name']; ?></td>
                                                <td><?php echo $sale['format']; ?></td>
                                                <td><?php echo $sale['qty']; ?></td>
                                                <td><?php echo app_format_money($sale['unit_price'], get_base_currency()); ?></td>
                                                <td><?php echo app_format_money($sale['total'], get_base_currency()); ?></td>
                                                <td><?php echo $sale['invoice_no']; ?></td>
                                                <td><?php echo $sale['customer_id']; ?></td>
                                                <td><?php echo $sale['customer']; ?></td>
                                                <td><?php echo $sale['phone']; ?></td>
                                                <td><?php echo $sale['channel']; ?></td>
                                                <td><?php echo $sale['shop_class']; ?></td>
                                                <td><?php echo $sale['region']; ?></td>
                                                <td><?php echo $sale['town']; ?></td>
                                                <td><?php echo $sale['creator']; ?></td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php } ?>

                            <div role="tabpanel" class="tab-pane<?php echo $opti_only ? ' active' : ''; ?>" id="tab_stock">
                                <div class="table-responsive">
                                    <table class="table dt-table">
                                        <thead>
                                            <tr>
                                                <th>sku_id</th>
                                                <th>uom</th>
                                                <th>quantity</th>
                                                <th>id</th>
                                                <th>warehouse_attached</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($opti_stock as $row){ ?>
                                            <tr>
                                                <td><?php echo $row['sku_id']; ?></td>
                                                <td><?php echo $row['uom']; ?></td>
                                                <td><?php echo (float)$row['quantity']; ?></td>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo $row['warehouse_attached']; ?></td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="tab_sell_out">
                                <div class="table-responsive">
                                    <table class="table dt-table">
                                        <thead>
                                            <tr>
                                                <th>visit_id</th>
                                                <th>date</th>
                                                <th>cust_id</th>
                                                <th>sales_rep_id</th>
                                                <th>amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($opti_sell_out as $row){ ?>
                                            <tr>
                                                <td><?php echo $row['visit_id']; ?></td>
                                                <td><?php echo $row['date']; ?></td>
                                                <td><?php echo $row['cust_id']; ?></td>
                                                <td><?php echo $row['sales_rep_id']; ?></td>
                                                <td><?php echo (float)$row['amount']; ?></td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="tab_sell_out_lines">
                                <div class="table-responsive">
                                    <table class="table dt-table">
                                        <thead>
                                            <tr>
                                                <th>id</th>
                                                <th>visit_id</th>
                                                <th>sku_id</th>
                                                <th>format</th>
                                                <th>quantity</th>
                                                <th>format_price</th>
                                                <th>total_price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($opti_sell_out_lines as $row){ ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo $row['visit_id']; ?></td>
                                                <td><?php echo $row['sku_id']; ?></td>
                                                <td><?php echo $row['format']; ?></td>
                                                <td><?php echo (float)$row['quantity']; ?></td>
                                                <td><?php echo (float)$row['format_price']; ?></td>
                                                <td><?php echo (float)$row['total_price']; ?></td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="tab_customers">
                                <div class="table-responsive">
                                    <table class="table dt-table">
                                        <thead>
                                            <tr>
                                                <th>cust_id</th>
                                                <th>customer_name</th>
                                                <th>phone</th>
                                                <th>channel</th>
                                                <th>customer_type</th>
                                                <th>region</th>
                                                <th>town</th>
                                                <th>latitude</th>
                                                <th>longitude</th>
                                                <th>warehouse_attached</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($opti_customers as $row){ ?>
                                            <tr>
                                                <td><?php echo $row['cust_id']; ?></td>
                                                <td><?php echo $row['customer_name']; ?></td>
                                                <td><?php echo $row['phone']; ?></td>
                                                <td><?php echo $row['channel']; ?></td>
                                                <td><?php echo $row['customer_type']; ?></td>
                                                <td><?php echo $row['region']; ?></td>
                                                <td><?php echo $row['town']; ?></td>
                                                <td><?php echo (float)$row['latitude']; ?></td>
                                                <td><?php echo (float)$row['longitude']; ?></td>
                                                <td><?php echo $row['warehouse_attached']; ?></td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
