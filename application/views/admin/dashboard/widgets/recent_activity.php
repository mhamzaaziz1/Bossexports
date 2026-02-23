<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('recent_activity'); ?>">
  <div class="panel_s recent-activity">
      <div class="panel-body padding-10">
        <div class="widget-dragger"></div>
        <div class="row">
           <div class="col-md-8">
              <h4 class="no-margin font-bold"><?php echo _l('recent_activity'); ?></h4>
           </div>
           <div class="col-md-4 text-right">
              <select class="selectpicker" id="recent_activity_type" onchange="load_recent_activity_widget_data();" data-width="100%">
                 <option value="proposals"><?php echo _l('proposals'); ?></option>
                 <option value="estimates"><?php echo _l('estimates'); ?></option>
                 <option value="invoices"><?php echo _l('invoices'); ?></option>
                 <option value="credit_notes"><?php echo _l('credit_notes'); ?></option>
              </select>
           </div>
        </div>
        <hr class="hr-panel-heading-dashboard">
        
        <div class="relative" style="max-height: 400px;overflow-y:auto;">
             <div id="recent_activity_widget_content" class="activity-feed">
             </div>
        </div>

     </div>
  </div>
</div>
<script>
   document.addEventListener('DOMContentLoaded', function() {
       load_recent_activity_widget_data();
   });

   function load_recent_activity_widget_data() {
      var type = $('#recent_activity_type').val();
      var $content = $('#recent_activity_widget_content');
      
      $content.html('<div class="text-center ptop20"><i class="fa fa-refresh fa-spin fa-2x"></i></div>');

      var data = {type: type};
      if (typeof(csrfData) !== 'undefined') {
          data[csrfData['token_name']] = csrfData['hash'];
      }

      $.post(admin_url + 'dashboard/get_recent_activity_data', data)
      .done(function(response) {
         $content.html(response);
      }).fail(function(data) {
         $content.html('<p class="text-danger text-center">Error loading data</p>');
         if(data.responseText){
             alert_float('danger', data.responseText);
         }
      });
   }
</script>
