<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">               
                        <div class="clearfix"></div>
                           <div class="pull-right">
                               <button class="btn btn-info" data-toggle="modal" data-target="#performance_matrix_modal">
                                  <i class="fa fa-line-chart"></i> <?php echo _l('Performance Matrix'); ?>
                               </button>
                           </div>
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
                                                <div class="onoffswitch">
                                                    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="commodity_status_switch" data-id="<?php echo $commodity_item->id; ?>" <?php if($commodity_item->active == 1){echo 'checked';} ?> onchange="change_commodity_status(<?php echo $commodity_item->id; ?>, this);">
                                                    <label class="onoffswitch-label" for="commodity_status_switch"></label>
                                                </div>
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
                                        <td class="bold"><?php echo _l('weight'); ?></td>
                                        <td><?php echo html_entity_decode($commodity_item->weight) ; ?></td>
                                     </tr>

                                     <tr class="project-overview">
                                        <td class="bold"><?php echo _l('volume'); ?></td>
                                        <td><?php echo html_entity_decode($commodity_item->volume) ; ?></td>
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
                                                <div class="col-md-6">
                                                    <h4><?php echo _l('customers'); ?></h4>
                                                    <?php render_datatable(array(
                                                        _l('customer'),
                                                        _l('quantity'),
                                                        _l('amount'),
                                                        ),'table_commodity_buyers'); ?>
                                                </div>
                                                <div class="col-md-6">
                                                     <h4><?php echo _l('vendors'); ?></h4>
                                                     <?php render_datatable(array(
                                                        _l('vendor'),
                                                        _l('quantity'),
                                                        _l('amount'),
                                                        ),'table_commodity_sellers'); ?>
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

  // Performance Matrix Modal Charts Initialization
  $('#performance_matrix_modal').on('shown.bs.modal', function () {
      // 1. Sales vs Target
      new Chart(document.getElementById('sales_vs_target_chart'), {
          type: 'bar',
          data: {
              labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
              datasets: [{
                  label: 'Actual Sales',
                  data: [12000, 19000, 3000, 5000, 2000, 3000],
                  backgroundColor: 'rgba(54, 162, 235, 0.5)',
                  borderColor: 'rgba(54, 162, 235, 1)',
                  borderWidth: 1
              }, {
                  label: 'Target',
                  data: [10000, 15000, 5000, 7000, 3000, 4000],
                  type: 'line',
                  fill: false,
                  borderColor: 'rgba(255, 99, 132, 1)',
                  borderWidth: 2
              }]
          },
          options: { responsive: true, maintainAspectRatio: false }
      });

      // 2. Inventory Aging
      new Chart(document.getElementById('inventory_aging_chart'), {
          type: 'doughnut',
          data: {
              labels: ['< 30 Days', '30-60 Days', '60-90 Days', '> 90 Days'],
              datasets: [{
                  data: [45, 25, 20, 10],
                  backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545']
              }]
          },
          options: { responsive: true, maintainAspectRatio: false }
      });

      // 3. Customer Demographics
      new Chart(document.getElementById('customer_demographics_chart'), {
          type: 'pie',
          data: {
              labels: ['18-24', '25-34', '35-44', '45-54', '55+'],
              datasets: [{
                  data: [15, 35, 25, 15, 10],
                  backgroundColor: ['#36a2eb', '#ff6384', '#cc65fe', '#ffce56', '#4bc0c0']
              }]
          },
          options: { responsive: true, maintainAspectRatio: false }
      });

      // 4. Peak Shopping Hours
      new Chart(document.getElementById('peak_hours_chart'), {
          type: 'line',
          data: {
              labels: ['8AM', '10AM', '12PM', '2PM', '4PM', '6PM', '8PM', '10PM'],
              datasets: [{
                  label: 'Avg. Traffic',
                  data: [20, 45, 80, 70, 65, 90, 50, 30],
                  borderColor: '#8e5ea2',
                  fill: true,
                  backgroundColor: 'rgba(142, 94, 162, 0.2)'
              }]
          },
          options: { responsive: true, maintainAspectRatio: false }
      });
  });
});
</script>
</body>
<!-- Performance Matrix Modal -->
<div class="modal fade" id="performance_matrix_modal" tabindex="-1" role="dialog" aria-labelledby="performanceMatrixLabel">
  <div class="modal-dialog modal-xl" role="document" style="width: 90%;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="performanceMatrixLabel"><?php echo _l('Performance Matrix'); ?> - <?php echo html_entity_decode($commodity_item->description); ?></h4>
      </div>
      <div class="modal-body">
         <div class="row">
            <!-- 1. Sales Overview -->
            <div class="col-md-12">
               <h4 class="text-primary"><i class="fa fa-money"></i> 1. Sales Overview</h4>
               <hr>
               <div class="row">
                  <div class="col-md-3">
                     <div class="panel_s">
                        <div class="panel-body text-center">
                           <h3>$125,000</h3>
                           <span class="text-muted">Total Gross Revenue (YTD)</span>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="panel_s">
                        <div class="panel-body text-center">
                           <h3>$45,000</h3>
                           <span class="text-muted">Net Profit (YTD)</span>
                        </div>
                     </div>
                  </div>
                   <div class="col-md-3">
                     <div class="panel_s">
                        <div class="panel-body text-center">
                           <h3>1,250</h3>
                           <span class="text-muted">Total Units Sold (YTD)</span>
                        </div>
                     </div>
                  </div>
                   <div class="col-md-3">
                     <div class="panel_s">
                        <div class="panel-body text-center">
                           <h3 class="text-success">+15%</h3>
                           <span class="text-muted">Growth vs. Last Year</span>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="row">
                   <div class="col-md-12">
                       <p><strong>Recent Trend Analysis:</strong> Sales have shown a steady increase over the last quarter, driven primarily by seasonal demand and recent marketing campaigns. The peak sales occurred in August, correlating with the 'Back to School' promotion.</p>
                   </div>
               </div>
            </div>

            <!-- 2. Inventory Health -->
            <div class="col-md-12 mtop20">
               <h4 class="text-warning"><i class="fa fa-cubes"></i> 2. Inventory Health</h4>
               <hr>
               <div class="row">
                   <div class="col-md-4">
                       <ul class="list-group">
                           <li class="list-group-item d-flex justify-content-between align-items-center">
                               Current Stock Level
                               <span class="badge badge-primary badge-pill pull-right">3,400 Units</span>
                           </li>
                           <li class="list-group-item d-flex justify-content-between align-items-center">
                               Reorder Point
                               <span class="badge badge-warning badge-pill pull-right">500 Units</span>
                           </li>
                           <li class="list-group-item d-flex justify-content-between align-items-center">
                               Safety Stock
                               <span class="badge badge-success badge-pill pull-right">200 Units</span>
                           </li>
                       </ul>
                   </div>
                   <div class="col-md-8">
                       <table class="table table-bordered">
                           <thead>
                               <tr>
                                   <th>Metric</th>
                                   <th>Value</th>
                                   <th>Status</th>
                                   <th>Action</th>
                               </tr>
                           </thead>
                           <tbody>
                               <tr>
                                   <td>Inventory Turnover Ratio</td>
                                   <td>4.5</td>
                                   <td><span class="label label-success">Healthy</span></td>
                                   <td>Maintain current ordering schedule.</td>
                               </tr>
                               <tr>
                                   <td>Average Days to Sell</td>
                                   <td>45 Days</td>
                                   <td><span class="label label-warning">Monitor</span></td>
                                   <td>Consider a small discount to speed up velocity.</td>
                               </tr>
                               <tr>
                                   <td>Stockout Risk</td>
                                   <td>Low</td>
                                   <td><span class="label label-success">Good</span></td>
                                   <td>N/A</td>
                               </tr>
                           </tbody>
                       </table>
                   </div>
               </div>
            </div>

            <!-- 3. Customer Metrics -->
            <div class="col-md-12 mtop20">
               <h4 class="text-info"><i class="fa fa-users"></i> 3. Customer Metrics</h4>
               <hr>
               <div class="row">
                   <div class="col-md-6">
                       <h5>Return Rate Analysis</h5>
                       <div class="progress">
                          <div class="progress-bar progress-bar-success" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100">
                            Kept (95%)
                          </div>
                          <div class="progress-bar progress-bar-danger" role="progressbar" style="width: 5%" aria-valuenow="5" aria-valuemin="0" aria-valuemax="100">
                            Returned (5%)
                          </div>
                        </div>
                        <p class="text-muted small">The industry average return rate for this category is 7%. This product is performing better than average.</p>
                   </div>
                   <div class="col-md-6">
                       <h5>Demographic Appeal</h5>
                       <p><strong>Primary Audience:</strong> Adults aged 25-45, primarily located in urban areas.</p>
                       <p><strong>Feedback Sentiment:</strong> 92% Positive. Common accolades include "Durability" and "Value for Money". Common complaints focus on "Packaging".</p>
                   </div>
               </div>
            </div>

            <!-- 4. Supply Chain -->
            <div class="col-md-12 mtop20">
               <h4 class="text-danger"><i class="fa fa-truck"></i> 4. Supply Chain & Logistics</h4>
               <hr>
               <div class="row">
                   <div class="col-md-4">
                       <div class="panel_s">
                           <div class="panel-body">
                               <h5 class="bold">Lead Time</h5>
                               <p>Average: 14 Days</p>
                               <p class="text-muted">Standard deviation of +/- 2 days.</p>
                           </div>
                       </div>
                   </div>
                    <div class="col-md-4">
                       <div class="panel_s">
                           <div class="panel-body">
                               <h5 class="bold">Vendor Performance</h5>
                               <p>Score: 4.8 / 5.0</p>
                               <p class="text-muted">Consistently on time with minimal defects.</p>
                           </div>
                       </div>
                   </div>
                    <div class="col-md-4">
                       <div class="panel_s">
                           <div class="panel-body">
                               <h5 class="bold">Next Shipment</h5>
                               <p>Date: Oct 15, 2026</p>
                               <p class="text-muted">Quantity: 1,000 Units (In Transit)</p>
                           </div>
                       </div>
                   </div>
               </div>
            </div>

             <!-- 5. Forecasting & Seasonality -->
            <div class="col-md-12 mtop20">
               <h4 class="text-secondary"><i class="fa fa-calendar"></i> 5. Forecasting & Seasonality</h4>
               <hr>
               <div class="alert alert-info">
                   <strong>Peak Season Alert:</strong> This product historically sees a 40% spike in sales during November/December.
               </div>
               <p><strong>Suggested Action:</strong> Increase safety stock by 20% starting October 1st to mitigate holiday stockout risks.</p>
               <table class="table table-striped">
                   <thead>
                       <tr>
                           <th>Month</th>
                           <th>Predicted Sales</th>
                           <th>Confidence Level</th>
                       </tr>
                   </thead>
                   <tbody>
                       <tr>
                           <td>October</td>
                           <td>1,500 Units</td>
                           <td>High</td>
                       </tr>
                       <tr>
                           <td>November</td>
                           <td>2,100 Units</td>
                           <td>High</td>
                       </tr>
                       <tr>
                           <td>December</td>
                           <td>2,800 Units</td>
                           <td>Medium</td>
                       </tr>
                   </tbody>
               </table>
            </div>

            <!-- 6. Retail Specifics -->
            <div class="col-md-12 mtop20">
               <h4 class="text-success"><i class="fa fa-shopping-cart"></i> 6. Retail Optimization</h4>
               <hr>
               <div class="row">
                   <div class="col-md-6">
                       <h5>Shelf Placement Logic</h5>
                       <p>Optimized for eye-level placement. Best performance observed when placed adjacent to "Complementary Product A". Avoid bottom shelf due to lower visibility for premium packaging.</p>
                   </div>
                    <div class="col-md-6">
                       <h5>Bundle Opportunities</h5>
                       <p>Frequently bought with: <strong>Product B (SKU-123)</strong> and <strong>Product C (SKU-456)</strong>. Consider creating a holiday bundle with these items for a 10% discount to drive volume.</p>
                   </div>
               </div>
               <div class="well mtop15">
                   <h5>Discount Elasticity</h5>
                   <p>A 5% discount typically yields a 12% increase in sales volume. A 10% discount yields a 30% increase. The optimal discount for clearing excess inventory is 15%.</p>
               </div>
            </div>

             <!-- 7. Advanced Analytics (New Graphs) -->
             <div class="col-md-12 mtop20">
                 <h4 class="text-primary"><i class="fa fa-bar-chart"></i> 7. Advanced Analytics</h4>
                 <hr>
                 <div class="row">
                     <!-- Sales vs Target -->
                     <div class="col-md-6">
                         <div class="panel_s">
                             <div class="panel-body">
                                 <h5 class="bold"><?php echo _l('Sales vs Target Details'); ?></h5>
                                 <div class="relative" style="height:300px">
                                     <canvas id="sales_vs_target_chart"></canvas>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <!-- Inventory Aging -->
                     <div class="col-md-6">
                         <div class="panel_s">
                             <div class="panel-body">
                                 <h5 class="bold"><?php echo _l('Inventory Aging Analysis'); ?></h5>
                                 <div class="relative" style="height:300px">
                                     <canvas id="inventory_aging_chart"></canvas>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
                 <div class="row mtop20">
                     <!-- Customer Demographics -->
                     <div class="col-md-6">
                         <div class="panel_s">
                             <div class="panel-body">
                                 <h5 class="bold"><?php echo _l('Customer Demographics'); ?></h5>
                                 <div class="relative" style="height:300px">
                                     <canvas id="customer_demographics_chart"></canvas>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <!-- Peak Shopping Hours -->
                     <div class="col-md-6">
                         <div class="panel_s">
                             <div class="panel-body">
                                 <h5 class="bold"><?php echo _l('Peak Shopping Hours'); ?></h5>
                                 <div class="relative" style="height:300px">
                                     <canvas id="peak_hours_chart"></canvas>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>

         </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fa fa-print"></i> <?php echo _l('print'); ?></button>
      </div>
    </div>
  </div>
</div>
</html>
