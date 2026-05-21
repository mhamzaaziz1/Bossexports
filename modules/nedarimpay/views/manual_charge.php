<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">

      <!-- Page Header -->
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="row">
                     <div class="col-md-8">
                        <h4 class="no-margin"><?php echo _l('nedarimpay_manual_charge'); ?></h4>
                     </div>
                     <div class="col-md-4 text-right">
                        <a href="<?php echo admin_url('nedarimpay/dashboard'); ?>" class="btn btn-default btn-sm">
                           <i class="fa fa-arrow-left"></i> <?php echo _l('nedarimpay_dashboard'); ?>
                        </a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <div class="row">

         <!-- Charge Form -->
         <div class="col-md-7">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo _l('nedarimpay_charge_form_title'); ?></h4>
                  <hr class="hr-panel-heading" />

                  <div class="alert alert-info">
                     <i class="fa fa-info-circle"></i>
                     <?php echo _l('nedarimpay_charge_info'); ?>
                  </div>

                  <?php echo form_open(admin_url('nedarimpay/manual_charge')); ?>

                  <div class="row">
                     <div class="col-md-6">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_perfex_client'); ?></label>
                           <select name="perfex_client_id" class="form-control"
                                   id="perfex_client_select"
                                   onchange="fillNedarimId(this)">
                              <option value=""><?php echo _l('nedarimpay_select_client'); ?></option>
                              <?php foreach ($clients as $c) : ?>
                              <option value="<?php echo $c['userid']; ?>"
                                      data-nedarim="">
                                 <?php echo htmlspecialchars($c['company']); ?>
                              </option>
                              <?php endforeach; ?>
                           </select>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>
                              <?php echo _l('nedarimpay_nedarim_client_id'); ?>
                              <span class="text-danger">*</span>
                           </label>
                           <input type="text" name="client_id_nedarim" class="form-control"
                                  id="nedarim_client_id_input"
                                  placeholder="<?php echo _l('nedarimpay_nedarim_client_id_placeholder'); ?>"
                                  required>
                           <small class="help-block"><?php echo _l('nedarimpay_nedarim_client_id_help'); ?></small>
                        </div>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-4">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_amount'); ?> <span class="text-danger">*</span></label>
                           <div class="input-group">
                              <input type="number" name="amount" class="form-control"
                                     step="0.01" min="0.01" placeholder="0.00" required>
                              <span class="input-group-addon" id="currency_symbol">₪</span>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_currency'); ?></label>
                           <select name="currency" class="form-control" id="currency_select"
                                   onchange="updateCurrencySymbol(this)">
                              <option value="1">₪ ILS</option>
                              <option value="2">$ USD</option>
                           </select>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_tashlumim'); ?></label>
                           <input type="number" name="tashlumim" class="form-control"
                                  min="1" value="1">
                           <small class="help-block"><?php echo _l('nedarimpay_tashlumim_help'); ?></small>
                        </div>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_charge_type'); ?></label>
                           <select name="charge_type" class="form-control"
                                   onchange="updateReceiptType(this)">
                              <option value="tuition"  data-receipt="student">
                                 <?php echo _l('nedarimpay_charge_tuition'); ?>
                              </option>
                              <option value="travel"   data-receipt="student">
                                 <?php echo _l('nedarimpay_charge_travel'); ?>
                              </option>
                              <option value="other"    data-receipt="student">
                                 <?php echo _l('nedarimpay_charge_other'); ?>
                              </option>
                              <option value="donation" data-receipt="donation">
                                 <?php echo _l('nedarimpay_charge_donation'); ?>
                              </option>
                           </select>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_receipt_type'); ?></label>
                           <select name="receipt_type" class="form-control" id="receipt_type_select">
                              <option value="student"><?php echo _l('nedarimpay_type_student'); ?></option>
                              <option value="donation"><?php echo _l('nedarimpay_type_donation'); ?></option>
                           </select>
                           <small class="help-block"><?php echo _l('nedarimpay_receipt_type_auto_help'); ?></small>
                        </div>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_groupe'); ?></label>
                           <input type="text" name="groupe" class="form-control"
                                  placeholder="<?php echo _l('nedarimpay_groupe_placeholder'); ?>"
                                  maxlength="100">
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_description'); ?></label>
                           <input type="text" name="description" class="form-control"
                                  placeholder="<?php echo _l('nedarimpay_description_placeholder'); ?>"
                                  maxlength="300">
                        </div>
                     </div>
                  </div>

                  <hr />

                  <div class="row">
                     <div class="col-md-12">
                        <button type="submit" class="btn btn-success"
                                onclick="return confirm('<?php echo _l('nedarimpay_confirm_charge'); ?>')">
                           <i class="fa fa-paper-plane"></i>
                           <?php echo _l('nedarimpay_send_charge'); ?>
                        </button>
                        <a href="<?php echo admin_url('nedarimpay/dashboard'); ?>" class="btn btn-default">
                           <?php echo _l('cancel'); ?>
                        </a>
                     </div>
                  </div>

                  <?php echo form_close(); ?>
               </div>
            </div>
         </div>
         <!-- /Charge Form -->

         <!-- RIGHT: Info Panel -->
         <div class="col-md-5">

            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo _l('nedarimpay_charge_howto_title'); ?></h4>
                  <hr class="hr-panel-heading" />
                  <ol>
                     <li><?php echo _l('nedarimpay_charge_step1'); ?></li>
                     <li><?php echo _l('nedarimpay_charge_step2'); ?></li>
                     <li><?php echo _l('nedarimpay_charge_step3'); ?></li>
                     <li><?php echo _l('nedarimpay_charge_step4'); ?></li>
                  </ol>
               </div>
            </div>

            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo _l('nedarimpay_receipt_series_reminder'); ?></h4>
                  <hr class="hr-panel-heading" />
                  <p>
                     <span class="label label-info"><?php echo _l('nedarimpay_type_student'); ?></span>
                     &nbsp;<?php echo _l('nedarimpay_series_student_desc'); ?>
                  </p>
                  <p>
                     <span class="label label-primary"><?php echo _l('nedarimpay_type_donation'); ?></span>
                     &nbsp;<?php echo _l('nedarimpay_series_donation_desc'); ?>
                  </p>
               </div>
            </div>

         </div>
         <!-- /RIGHT -->

      </div>

   </div>
</div>
<?php init_tail(); ?>
<script>
function updateReceiptType(sel) {
   var receiptType = sel.options[sel.selectedIndex].getAttribute('data-receipt');
   document.getElementById('receipt_type_select').value = receiptType || 'student';
}
function fillNedarimId(sel) {
   var nedarimId = sel.options[sel.selectedIndex].getAttribute('data-nedarim') || '';
   if (nedarimId) {
      document.getElementById('nedarim_client_id_input').value = nedarimId;
   }
}
function updateCurrencySymbol(sel) {
   document.getElementById('currency_symbol').textContent = sel.value == '2' ? '$' : '₪';
}
</script>
