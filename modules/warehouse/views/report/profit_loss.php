<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="no-margin"><?php echo _l('profit_and_loss_report'); ?></h4>
                            </div>
                            <div class="col-md-6">
                                <?php echo form_open(admin_url('warehouse/profit_loss'), array('id' => 'profit_loss_filter')); ?>
                                <div class="row">
                                    <div class="col-md-5">
                                        <?php echo render_date_input('from_date', 'from_date', _d($from_date)); ?>
                                    </div>
                                    <div class="col-md-5">
                                        <?php echo render_date_input('to_date', 'to_date', _d($to_date)); ?>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-info btn-block mtop25"><?php echo _l('filter'); ?></button>
                                    </div>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                        <hr />

                        <div class="horizontal-scrollable-tabs preview-tabs-top">
                             <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                             <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                             <div class="horizontal-tabs">
                                 <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                                     <li role="presentation" class="active">
                                         <a href="#summary" aria-controls="summary" role="tab" data-toggle="tab">
                                             Detailed Summary
                                         </a>
                                     </li>
                                     <li role="presentation">
                                         <a href="#by_product" aria-controls="by_product" role="tab" data-toggle="tab">
                                             Profit by Product
                                         </a>
                                     </li>
                                     <li role="presentation">
                                         <a href="#by_category" aria-controls="by_category" role="tab" data-toggle="tab">
                                             Profit by Category
                                         </a>
                                     </li>
                                      <li role="presentation">
                                         <a href="#by_day" aria-controls="by_day" role="tab" data-toggle="tab">
                                             Profit by Day
                                         </a>
                                     </li>
                                 </ul>
                             </div>
                        </div>

                        <div class="tab-content">
                            <!-- SUMMARY TAB -->
                            <div role="tabpanel" class="tab-pane active" id="summary">
                                <div class="row">
                                    <!-- Stock Section -->
                                    <div class="col-md-4">
                                        <h4 class="text-info">Stock Valuation</h4>
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <td>Opening Stock (By purchase price)</td>
                                                    <td class="text-right"><?php echo app_format_money($opening_stock_purchase, ''); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Opening Stock (By sale price)</td>
                                                    <td class="text-right"><?php echo app_format_money($opening_stock_sale, ''); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Closing Stock (By purchase price)</td>
                                                    <td class="text-right"><?php echo app_format_money($closing_stock_purchase, ''); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Closing Stock (By sale price)</td>
                                                    <td class="text-right"><?php echo app_format_money($closing_stock_sale, ''); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Total Stock Adjustment</td>
                                                    <td class="text-right">$ 0.00</td> <!-- Example Placeholder -->
                                                </tr>
                                                 <tr>
                                                    <td>Total Stock Recovered</td>
                                                    <td class="text-right">$ 0.00</td> <!-- Example Placeholder -->
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Financials Section -->
                                    <div class="col-md-4">
                                        <h4 class="text-info">Financials</h4>
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <td>Total Purchase (Exc. tax, Discount)</td>
                                                    <td class="text-right"><?php echo app_format_money($total_purchase, ''); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Total Purchase Shipping Charge</td>
                                                    <td class="text-right"><?php echo app_format_money($purchase_shipping_charge, ''); ?></td>
                                                </tr>
                                                 <tr>
                                                    <td>Purchase Additional Expenses</td>
                                                    <td class="text-right">$ 0.00</td> <!-- Example Placeholder -->
                                                </tr>
                                                <tr>
                                                    <td>Total Purchase Discount</td>
                                                    <td class="text-right"><?php echo app_format_money($total_purchase_discount, ''); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Total Sales (Exc. tax, Discount)</td>
                                                    <td class="text-right"><?php echo app_format_money($total_sales_exc_tax, ''); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Total Sell Discount</td>
                                                    <td class="text-right"><?php echo app_format_money($total_sales_discount, ''); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Total Expense</td>
                                                    <td class="text-right"><?php echo app_format_money($total_expenses, ''); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Summary Section -->
                                    <div class="col-md-4">
                                        <h4 class="text-info">Summary</h4>
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <td class="text-bold">COGS</td>
                                                    <td class="text-right text-bold"><?php echo app_format_money($cogs, ''); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-bold">Gross Profit</td>
                                                    <td class="text-right text-bold"><?php echo app_format_money($gross_profit, ''); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-bold">Net Profit</td>
                                                    <td class="text-right text-bold" style="color: <?php echo $net_profit >= 0 ? 'green' : 'red'; ?>;">
                                                        <?php echo app_format_money($net_profit, ''); ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <p class="text-muted small mtop15">
                                            COGS = Opening Stock + Purchases - Closing Stock<br>
                                            Gross Profit = Sales - COGS<br>
                                            Net Profit = Gross Profit - Expenses
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- BY PRODUCT TAB -->
                            <div role="tabpanel" class="tab-pane" id="by_product">
                                <table class="table dt-table">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Qty Sold</th>
                                            <th>Total Sales</th>
                                            <th>Total Cost</th>
                                            <th>Gross Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(isset($profit_by_product)){ foreach($profit_by_product as $prod): ?>
                                        <tr>
                                            <td><?php echo $prod['name']; ?></td>
                                            <td><?php echo $prod['qty']; ?></td>
                                            <td><?php echo app_format_money($prod['sales'], ''); ?></td>
                                            <td><?php echo app_format_money($prod['cost'], ''); ?></td>
                                            <td><?php echo app_format_money($prod['profit'], ''); ?></td>
                                        </tr>
                                        <?php endforeach; }?>
                                    </tbody>
                                </table>
                            </div>

                             <!-- BY CATEGORY TAB -->
                            <div role="tabpanel" class="tab-pane" id="by_category">
                                <table class="table dt-table">
                                    <thead>
                                        <tr>
                                            <th>Category Name</th>
                                            <th>Total Sales</th>
                                            <th>Total Cost</th>
                                            <th>Gross Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(isset($profit_by_category)){ foreach($profit_by_category as $cat): ?>
                                        <tr>
                                            <td><?php echo $cat['name']; ?></td>
                                            <td><?php echo app_format_money($cat['sales'], ''); ?></td>
                                            <td><?php echo app_format_money($cat['cost'], ''); ?></td>
                                            <td><?php echo app_format_money($cat['profit'], ''); ?></td>
                                        </tr>
                                        <?php endforeach; }?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- BY DAY TAB -->
                            <div role="tabpanel" class="tab-pane" id="by_day">
                                <table class="table dt-table">
                                    <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>Date</th>
                                            <th>Gross Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(isset($profit_by_day)){ foreach($profit_by_day as $day): ?>
                                        <tr>
                                            <td><?php echo $day['day']; ?></td>
                                            <td><?php echo _d($day['date']); ?></td>
                                            <td><?php echo app_format_money($day['profit'], ''); ?></td>
                                        </tr>
                                        <?php endforeach; }?>
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
<?php init_tail(); ?>
</body>
</html>
