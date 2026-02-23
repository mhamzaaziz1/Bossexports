<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo _l('zatca_history'); ?></h4>
                  <hr class="hr-panel-heading" />
                  
                  <table class="table dt-table">
                      <thead>
                          <tr>
                              <th><?php echo _l('zatca_log_id'); ?></th>
                              <th><?php echo _l('invoice'); ?></th>
                              <th>Status</th>
                              <th>Date</th>
                              <th>Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          <!-- Rows to come from DB -->
                      </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
