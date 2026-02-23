<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin font-medium"><i class="fa fa-file-text-o" aria-hidden="true"></i> <?php echo _l('sales_report_heading'); ?> - Item Details</h4>
            <hr/>

            <div class="row mtop10 mleft5 mright5">
              
              <div class="col-md-4">
                 <div class="form-group">
                    <label for="report_type"><?php echo _l('kb_report_type'); // Or use a custom label ?></label>
                    <select class="selectpicker" name="report_type" data-width="100%">
                       <option value="proposals" selected><?php echo _l('proposals'); ?></option>
                       <option value="estimates"><?php echo _l('estimates'); ?></option>
                       <option value="invoices"><?php echo _l('invoices'); ?></option>
                       <option value="credit_notes"><?php echo _l('credit_notes'); ?></option>
                    </select>
                 </div>
              </div>

              <div class="col-md-4">
                <div class="form-group" id="report-time">
                  <label for="months-report"><?php echo _l('period_datepicker'); ?></label><br />
                  <select class="selectpicker" name="months-report" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                    <option value=""><?php echo _l('report_sales_months_all_time'); ?></option>
                    <option value="this_month"><?php echo _l('this_month'); ?></option>
                    <option value="1"><?php echo _l('last_month'); ?></option>
                    <option value="this_year"><?php echo _l('this_year'); ?></option>
                    <option value="last_year"><?php echo _l('last_year'); ?></option>
                    <option value="3" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-2 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_three_months'); ?></option>
                    <option value="6" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-5 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_six_months'); ?></option>
                    <option value="12" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-11 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_twelve_months'); ?></option>
                    <option value="custom"><?php echo _l('period_datepicker'); ?></option>
                  </select>
                </div>
                <div id="date-range" class="hide mbot15">
                  <div class="row">
                    <div class="col-md-6">
                      <label for="report-from" class="control-label"><?php echo _l('report_sales_from_date'); ?></label>
                      <div class="input-group date">
                        <input type="text" class="form-control datepicker" id="report-from" name="report-from">
                        <div class="input-group-addon">
                          <i class="fa fa-calendar calendar-icon"></i>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <label for="report-to" class="control-label"><?php echo _l('report_sales_to_date'); ?></label>
                      <div class="input-group date">
                        <input type="text" class="form-control datepicker" disabled="disabled" id="report-to" name="report-to">
                        <div class="input-group-addon">
                          <i class="fa fa-calendar calendar-icon"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
               <div class="col-md-4">
                  <button id="apply-filters" class="btn btn-primary mtop25"><?php echo _l('apply'); ?></button>
               </div>
            </div>

            <div class="table-responsive mtop20">
              <table class="table table-striped table-bordered dt-table table-sales-report">
                <thead>
                  <tr>
                    <th id="th-ref-no">Ref #</th> <th>Date</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th class="text-right">Quantity</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script>
  $(function(){
    var tableSelector = '.table-sales-report';
    var baseUrl = admin_url + 'reports/sales_quotes_report';

    // 1. Initialize DataTable
    initDataTable(tableSelector, baseUrl, false, false, {}, [0, 'desc']);

    // 2. Handle Dropdown Change for Custom Date
    $('select[name="months-report"]').on('changed.bs.select', function(){
      var val = $(this).val();
      if(val === 'custom'){
        $('#date-range').removeClass('hide');
      } else {
        $('#date-range').addClass('hide');
        $('input[name="report-from"]').val('');
        $('input[name="report-to"]').val('');
      }
    });

    // 3. Handle Datepicker Logic
    $('input[name="report-from"]').on('change', function(){
      var date = $(this).val();
      $('input[name="report-to"]').prop('disabled', false);
      $('input[name="report-to"]').datepicker('destroy').datepicker({
        format: app_date_format,
        autoclose: true,
        todayHighlight: true,
        startDate: date
      });
    });

    // 4. APPLY FILTERS
    $('#apply-filters').on('click', function(){
        // Get values
        var report_type   = $('select[name="report_type"]').val();
        var report_months = $('select[name="months-report"]').val();
        var report_from   = $('input[name="report-from"]').val();
        var report_to     = $('input[name="report-to"]').val();

        // Update Header Text based on selection
        var typeLabel = $('select[name="report_type"] option:selected').text();
        $('#th-ref-no').text(typeLabel + ' #');

        // Build URL
        var newUrl = baseUrl + '?report_type=' + encodeURIComponent(report_type)
                           + '&report_months=' + encodeURIComponent(report_months) 
                           + '&report_from=' + encodeURIComponent(report_from) 
                           + '&report_to=' + encodeURIComponent(report_to);

        if ($.fn.DataTable.isDataTable(tableSelector)) {
            $(tableSelector).DataTable().ajax.url(newUrl).load();
        }
    });
  });
</script>
</body>
</html>