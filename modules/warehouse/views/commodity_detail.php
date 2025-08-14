<div class="col-md-12">
<div class="panel_s">
  <div class="panel-body">

      <div class="row col-md-12">

        <h4 class="h4-color"><?php echo _l('general_infor'); ?></h4>
        <hr class="hr-color">



        <div class="col-md-7 panel-padding">
          <table class="table border table-striped table-margintop">
              <tbody>

                  <tr class="project-overview">
                    <td class="bold" width="30%"><?php echo _l('commodity_code'); ?></td>
                    <td><?php echo html_entity_decode($commodites->commodity_code) ; ?></td>
                 </tr>
                 <tr class="project-overview">
                    <td class="bold"><?php echo _l('commodity_name'); ?></td>
                    <td><?php echo html_entity_decode($commodites->description) ; ?></td>
                 </tr>
                 <tr class="project-overview">
                    <td class="bold"><?php echo _l('commodity_group'); ?></td>
                    <td><?php echo get_wh_group_name(html_entity_decode($commodites->group_id)) != null ? get_wh_group_name(html_entity_decode($commodites->group_id))->name : '' ; ?></td>
                 </tr>
                 <tr class="project-overview">
                    <td class="bold"><?php echo _l('commodity_barcode'); ?></td>
                    <td><?php echo html_entity_decode($commodites->commodity_barcode) ; ?></td>
                 </tr>
                 <tr class="project-overview">
                    <td class="bold"><?php echo _l('sku_code'); ?></td>
                    <td><?php echo html_entity_decode($commodites->sku_code) ; ?></td>
                 </tr>
                 <tr class="project-overview">
                    <td class="bold"><?php echo _l('sku_name'); ?></td>
                    <td><?php echo html_entity_decode($commodites->sku_name) ; ?></td>
                 </tr>

                </tbody>
          </table>
      </div>

        <div class="gallery">
            <div class="wrapper-masonry">
              <div id="masonry" class="masonry-layout columns-2">
            <?php if(isset($commodity_file) && count($commodity_file) > 0){ ?>
              <?php foreach ($commodity_file as $key => $value) { ?>

                  <?php if(file_exists(WAREHOUSE_ITEM_UPLOAD .$value["rel_id"].'/'.$value["file_name"])){ ?>
                      <a  class="images_w_table" href="<?php echo site_url('modules/warehouse/uploads/item_img/'.$value["rel_id"].'/'.$value["file_name"]); ?>"><img class="images_w_table" src="<?php echo site_url('modules/warehouse/uploads/item_img/'.$value["rel_id"].'/'.$value["file_name"]); ?>" alt="<?php echo html_entity_decode($value['file_name']) ?>"/></a>

                    <?php }elseif(file_exists('modules/purchase/uploads/item_img/'. $value["rel_id"] . '/' . $value["file_name"])) { ?>
                      <a  class="images_w_table" href="<?php echo site_url('modules/purchase/uploads/item_img/'.$value["rel_id"].'/'.$value["file_name"]); ?>"><img class="images_w_table" src="<?php echo site_url('modules/purchase/uploads/item_img/'.$value["rel_id"].'/'.$value["file_name"]); ?>" alt="<?php echo html_entity_decode($value['file_name']) ?>"/></a>


                    <?php } ?>


            <?php } ?>
          <?php }else{ ?>

                <a href="<?php echo site_url('modules/warehouse/uploads/nul_image.jpg'); ?>"><img src="<?php echo site_url('modules/warehouse/uploads/nul_image.jpg'); ?>" alt="nul_image.jpg"/></a>

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
                    <td><?php echo html_entity_decode($commodites->origin) ; ?></td>
               </tr>
               <tr class="project-overview">
                  <td class="bold"><?php echo _l('colors'); ?></td>
                  <?php
                  $color_value ='';
                  if($commodites->color){
                    $color = get_color_type($commodites->color);
                    if($color){
                      $color_value .= $color->color_code.'_'.$color->color_name;
                    }
                  }
                   ?>
                    <td><?php echo html_entity_decode($color_value) ; ?></td>
               </tr>
               <tr class="project-overview">
                  <td class="bold"><?php echo _l('style_id'); ?></td>
                <td><?php  if($commodites->style_id != null){ echo get_style_name(html_entity_decode($commodites->style_id)) != null ? get_style_name(html_entity_decode($commodites->style_id))->style_name : '';}else{echo '';} ?></td>
               </tr>

                <tr class="project-overview">
                  <td class="bold"><?php echo _l('rate'); ?></td>
                  <td><?php echo app_format_money((float)$commodites->rate,'') ; ?></td>
               </tr>
               <tr class="project-overview">
                  <td class="bold"><?php echo "ECOMM "._l('rate'); ?></td>
                  <td><?php echo app_format_money((float)$commodites->ECOMM,'') ; ?></td>
               </tr>
               <tr class="project-overview">
                  <td class="bold"><?php echo "SELLER "._l('rate'); ?></td>
                  <td><?php echo app_format_money((float)$commodites->SELLER,'') ; ?></td>
               </tr>
               <tr class="project-overview">
                  <td class="bold"><?php echo "RETAILER"._l('rate'); ?></td>
                  <td><?php echo app_format_money((float)$commodites->RETAILER,'') ; ?></td>
               </tr>
               <tr class="project-overview">
                  <td class="bold"><?php echo "WHOLESALER"._l('rate'); ?></td>
                  <td><?php echo app_format_money((float)$commodites->WHOLESALER,'') ; ?></td>
               </tr>
               <tr class="project-overview">
                  <td class="bold"><?php echo _l('status'); ?></td>
                  <td>
                        <select name='isactive'>
                          <option value="1">active</option>
                          <option value="0">Inactive</option>
                        </select>
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
                   <td><?php if($commodites->style_id != null){ echo get_model_name(html_entity_decode($commodites->model_id)) != null ? get_model_name(html_entity_decode($commodites->model_id))->body_name : ''; }else{echo '';}?></td>
               </tr>
               <tr class="project-overview">
                  <td class="bold"><?php echo _l('size_id'); ?></td>

                  <td><?php if($commodites->style_id != null){ echo get_size_name(html_entity_decode($commodites->size_id)) != null ? get_size_name(html_entity_decode($commodites->size_id))->size_name : ''; }else{ echo '';}?></td>
               </tr>

                 <tr class="project-overview">
                    <td class="bold"><?php echo _l('unit_id'); ?></td>
                    <td><?php echo  $commodites->unit_id != '' && get_unit_type($commodites->unit_id) != null ? get_unit_type($commodites->unit_id)->unit_name : ''; ?></td>
                 </tr> 

                 <tr class="project-overview">
                    <td class="bold"><?php echo _l('purchase_price'); ?></td>
                    <td><?php echo app_format_money((float)$commodites->purchase_price,'') ; ?></td>
                 </tr>



              </tbody>
            </table>
      </div>

       <h4 class="h4-color"><?php echo _l('description'); ?></h4>
      <hr class="hr-color">
      <p><?php echo html_entity_decode($commodites->long_description) ; ?></p>

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
                  $this->load->model('reports_model');
                  $current_month = date('Y-m-01');
                  $next_month = date('Y-m-01', strtotime('+1 month'));
                  $date_filter = "i.date >= '$current_month' AND i.date < '$next_month'";
                  $sales_data = $this->reports_model->get_buyers_by_item($commodites->id, 'sales', 'top', 10, 'amount', $date_filter);

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
                  $purchase_data = $this->reports_model->get_buyers_by_item($commodites->id, 'purchases', 'top', 10, 'amount', $date_filter);

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

  </div>

<?php require 'modules/warehouse/assets/js/commodity_detail_js.php';?>

<script>
// Function to get monthly data for the current year
function getMonthlyData(transaction_type) {
  var result = [];
  var commodity_id = <?php echo $commodites->id; ?>;
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
