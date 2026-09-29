<?php defined('BASEPATH') or exit('No direct script access allowed');

$_ndp_mosad    = get_option('nedarimpay_mosad_number');
$_ndp_valid    = get_option('nedarimpay_api_valid');
$_ndp_mode_id  = (int)get_option('nedarimpay_payment_mode_id');
$_ndp_ready    = !empty($_ndp_mosad) && !empty($_ndp_valid) && $_ndp_mode_id > 0;
?>

<!-- NedarimPay Invoice Payment Modal -->
<div class="modal fade" id="nedarimpayPaymentModal" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
      <div class="modal-content">

         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            <h4 class="modal-title">
               <i class="fa fa-credit-card"></i>
               <?php echo _l('nedarimpay_record_payment_title'); ?>
               <small id="ndp_invoice_label" class="text-muted" style="font-size:13px;margin-left:8px;"></small>
            </h4>
         </div>

         <div class="modal-body">

            <?php if (!$_ndp_ready): ?>
            <!-- ── NOT CONFIGURED state ──────────────────────────────────────── -->
            <div class="text-center" style="padding:20px 10px;">
               <i class="fa fa-exclamation-circle text-danger" style="font-size:48px;"></i>
               <h4 style="margin-top:15px;"><?php echo _l('nedarimpay_not_configured_title'); ?></h4>
               <p class="text-muted" style="max-width:380px;margin:10px auto;">
                  <?php echo _l('nedarimpay_not_configured_body'); ?>
               </p>
               <a href="<?php echo admin_url('nedarimpay/settings'); ?>" class="btn btn-warning mtop10">
                  <i class="fa fa-cog"></i> <?php echo _l('nedarimpay_not_configured_link'); ?>
               </a>
            </div>

            <?php else: ?>
            <!-- ── READY state: payment form ────────────────────────────────── -->
            <div class="row">
               <div class="col-md-6">
                  <div class="form-group">
                     <label><?php echo _l('nedarimpay_record_payment_amount'); ?> <span class="text-danger">*</span></label>
                     <input type="number" id="ndp_amount" class="form-control" step="0.01" min="0.01" required>
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="form-group">
                     <label><?php echo _l('nedarimpay_record_payment_date'); ?> <span class="text-danger">*</span></label>
                     <input type="text" id="ndp_date" class="form-control datepicker" autocomplete="off"
                            value="<?php echo _d(date('Y-m-d')); ?>" required>
                  </div>
               </div>
            </div>

            <div class="row">
               <div class="col-md-6">
                  <div class="form-group">
                     <label><?php echo _l('nedarimpay_record_payment_receipt_type'); ?></label>
                     <select id="ndp_receipt_type" class="form-control">
                        <option value="student"><?php echo _l('nedarimpay_type_student'); ?></option>
                        <option value="donation"><?php echo _l('nedarimpay_type_donation'); ?></option>
                     </select>
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="form-group">
                     <label><?php echo _l('nedarimpay_record_payment_transaction_id'); ?></label>
                     <input type="text" id="ndp_transaction_id" class="form-control"
                            placeholder="<?php echo _l('nedarimpay_record_payment_transaction_id'); ?>">
                     <small class="help-block"><?php echo _l('nedarimpay_record_payment_transaction_id_help'); ?></small>
                  </div>
               </div>
            </div>

            <div class="form-group">
               <label><?php echo _l('nedarimpay_record_payment_note'); ?></label>
               <textarea id="ndp_note" class="form-control" rows="3"></textarea>
            </div>

            <div id="ndp_alert" class="alert" style="display:none;"></div>
            <?php endif; ?>

         </div>

         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">
               <?php echo _l('close'); ?>
            </button>
            <?php if ($_ndp_ready): ?>
            <button type="button" id="ndp_submit_btn" class="btn btn-primary"
                    onclick="nedarimpaySubmitPayment()">
               <i class="fa fa-check"></i> <?php echo _l('nedarimpay_record_payment_submit'); ?>
            </button>
            <?php endif; ?>
         </div>

      </div>
   </div>
</div>

<script>
var _ndpInvoiceId = 0;

function nedarimpayOpenPaymentModal(invoiceId, remainingAmount) {
   _ndpInvoiceId = invoiceId;
   document.getElementById('ndp_invoice_label').textContent = '#' + invoiceId;
   <?php if ($_ndp_ready): ?>
   if (remainingAmount !== undefined && remainingAmount > 0) {
      document.getElementById('ndp_amount').value = remainingAmount;
   }
   var ndpAlert = document.getElementById('ndp_alert');
   ndpAlert.style.display = 'none';
   ndpAlert.className     = 'alert';
   <?php endif; ?>
   $('#nedarimpayPaymentModal').modal('show');
   <?php if ($_ndp_ready): ?>
   init_datepicker();
   <?php endif; ?>
}

<?php if ($_ndp_ready): ?>
function nedarimpaySubmitPayment() {
   var amount         = document.getElementById('ndp_amount').value;
   var date           = document.getElementById('ndp_date').value;
   var receiptType    = document.getElementById('ndp_receipt_type').value;
   var transactionId  = document.getElementById('ndp_transaction_id').value;
   var note           = document.getElementById('ndp_note').value;
   var ndpAlert       = document.getElementById('ndp_alert');
   var submitBtn      = document.getElementById('ndp_submit_btn');

   if (!amount || parseFloat(amount) <= 0 || !date) {
      ndpAlert.className   = 'alert alert-danger';
      ndpAlert.textContent = '<?php echo _l("nedarimpay_charge_validation_error"); ?>';
      ndpAlert.style.display = '';
      return;
   }

   submitBtn.disabled    = true;
   submitBtn.innerHTML   = '<i class="fa fa-spinner fa-spin"></i> <?php echo _l("wait_text"); ?>';
   ndpAlert.style.display = 'none';

   $.post('<?php echo admin_url("nedarimpay/record_invoice_payment"); ?>', {
      invoice_id:     _ndpInvoiceId,
      amount:         amount,
      date:           date,
      receipt_type:   receiptType,
      transaction_id: transactionId,
      note:           note,
      <?php echo csrf_token_name(); ?>: Cookies.get('<?php echo csrf_cookie_name(); ?>')
   }, function(resp) {
      submitBtn.disabled  = false;
      submitBtn.innerHTML = '<i class="fa fa-check"></i> <?php echo _l("nedarimpay_record_payment_submit"); ?>';

      if (resp.success) {
         ndpAlert.className   = 'alert alert-success';
         ndpAlert.textContent = resp.message;
         ndpAlert.style.display = '';
         setTimeout(function() {
            $('#nedarimpayPaymentModal').modal('hide');
            init_invoice(_ndpInvoiceId);
         }, 1500);
      } else {
         ndpAlert.className   = 'alert alert-danger';
         ndpAlert.textContent = resp.message;
         ndpAlert.style.display = '';
      }
   }, 'json').fail(function() {
      submitBtn.disabled  = false;
      submitBtn.innerHTML = '<i class="fa fa-check"></i> <?php echo _l("nedarimpay_record_payment_submit"); ?>';
      ndpAlert.className   = 'alert alert-danger';
      ndpAlert.textContent = '<?php echo _l("nedarimpay_record_payment_fail"); ?>';
      ndpAlert.style.display = '';
   });
}
<?php endif; ?>
</script>
