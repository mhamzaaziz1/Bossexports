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
                        <h4 class="no-margin"><?php echo _l('nedarimpay_settings'); ?></h4>
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

      <?php echo form_open(admin_url('nedarimpay/settings')); ?>

      <div class="row">

         <!-- LEFT Column: Credentials + Receipt Series -->
         <div class="col-md-8">

            <!-- Webhook URL -->
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin">
                     <i class="fa fa-link"></i>
                     <?php echo _l('nedarimpay_webhook_url_title'); ?>
                  </h4>
                  <hr class="hr-panel-heading" />

                  <div class="alert alert-info">
                     <i class="fa fa-info-circle"></i>
                     <strong><?php echo _l('nedarimpay_webhook_note'); ?></strong>
                     <?php echo _l('nedarimpay_webhook_instruction'); ?>
                  </div>

                  <div class="input-group">
                     <input type="text" class="form-control" id="webhook_url_display"
                            value="<?php echo $webhook_url; ?>" readonly>
                     <span class="input-group-btn">
                        <button class="btn btn-default" type="button" onclick="copyWebhookUrl()">
                           <i class="fa fa-copy"></i> <?php echo _l('nedarimpay_copy'); ?>
                        </button>
                     </span>
                  </div>
                  <small class="help-block">
                     <i class="fa fa-info-circle"></i>
                     <?php echo _l('nedarimpay_webhook_both_types'); ?>
                  </small>
               </div>
            </div>

            <!-- Nedarim Plus Credentials -->
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin">
                     <i class="fa fa-key"></i>
                     <?php echo _l('nedarimpay_credentials'); ?>
                  </h4>
                  <hr class="hr-panel-heading" />

                  <div class="row">
                     <div class="col-md-4">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_mosad_number'); ?> <span class="text-danger">*</span></label>
                           <input type="text" name="nedarimpay_mosad_number" class="form-control"
                                  value="<?php echo get_option('nedarimpay_mosad_number'); ?>"
                                  placeholder="1234567" maxlength="7">
                           <small class="help-block"><?php echo _l('nedarimpay_mosad_help'); ?></small>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_api_valid'); ?> <span class="text-danger">*</span></label>
                           <input type="text" name="nedarimpay_api_valid" class="form-control"
                                  value="<?php echo get_option('nedarimpay_api_valid'); ?>"
                                  placeholder="ApiValid token">
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_api_key'); ?></label>
                           <input type="text" name="nedarimpay_api_key" class="form-control"
                                  value="<?php echo get_option('nedarimpay_api_key'); ?>"
                                  placeholder="https://...">
                           <small class="help-block"><?php echo _l('nedarimpay_api_key_help'); ?></small>
                        </div>
                     </div>
                  </div>

                  <!-- Charge API URL -->
                  <div class="row">
                     <div class="col-md-12">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_charge_api_url_label'); ?></label>
                           <input type="text" name="nedarimpay_charge_api_url" class="form-control"
                                  value="<?php echo get_option('nedarimpay_charge_api_url'); ?>"
                                  placeholder="https://www.matara.pro/nedarimplus/online/">
                           <small class="help-block"><?php echo _l('nedarimpay_charge_api_url_help'); ?></small>
                        </div>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-4">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_payment_mode'); ?></label>
                           <select name="nedarimpay_payment_mode_id" class="form-control">
                              <option value=""><?php echo _l('nedarimpay_select_payment_mode'); ?></option>
                              <?php foreach ($payment_modes as $pm) : ?>
                              <option value="<?php echo $pm['id']; ?>"
                                 <?php echo get_option('nedarimpay_payment_mode_id') == $pm['id'] ? 'selected' : ''; ?>>
                                 <?php echo htmlspecialchars($pm['name']); ?>
                              </option>
                              <?php endforeach; ?>
                           </select>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_client_match_field'); ?></label>
                           <select name="nedarimpay_match_field" class="form-control">
                              <?php foreach (['email', 'phone', 'zeout', 'nedarim_client_id'] as $opt) : ?>
                              <option value="<?php echo $opt; ?>"
                                 <?php echo get_option('nedarimpay_match_field') === $opt ? 'selected' : ''; ?>>
                                 <?php echo strtoupper(str_replace('_', ' ', $opt)); ?>
                              </option>
                              <?php endforeach; ?>
                           </select>
                           <small class="help-block"><?php echo _l('nedarimpay_match_field_help'); ?></small>
                        </div>
                     </div>
                  </div>

               </div>
            </div>

            <!-- Receipt Number Series -->
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin">
                     <i class="fa fa-file-text-o"></i>
                     <?php echo _l('nedarimpay_receipt_series'); ?>
                  </h4>
                  <hr class="hr-panel-heading" />

                  <div class="alert alert-warning">
                     <i class="fa fa-exclamation-triangle"></i>
                     <?php echo _l('nedarimpay_series_warning'); ?>
                  </div>

                  <div class="row">

                     <!-- Student Series -->
                     <div class="col-md-6">
                        <div class="panel_s" style="border:1px solid #dce4ec;">
                           <div class="panel-body">
                              <h5 class="no-margin">
                                 <span class="label label-info"><?php echo _l('nedarimpay_type_student'); ?></span>
                                 &nbsp;<?php echo _l('nedarimpay_series_student_title'); ?>
                              </h5>
                              <p class="text-muted" style="font-size:12px;margin-top:5px;">
                                 <?php echo _l('nedarimpay_series_student_desc'); ?>
                              </p>
                              <div class="form-group">
                                 <label><?php echo _l('nedarimpay_prefix'); ?></label>
                                 <input type="text" name="nedarimpay_student_prefix" class="form-control"
                                        value="<?php echo get_option('nedarimpay_student_prefix'); ?>"
                                        placeholder="T" maxlength="5">
                                 <small class="help-block">
                                    <?php echo _l('nedarimpay_preview'); ?>:
                                    <code id="student_preview">
                                       <?php echo get_option('nedarimpay_student_prefix'); ?>-00001
                                    </code>
                                 </small>
                              </div>
                              <div class="form-group">
                                 <label><?php echo _l('nedarimpay_groupe_filter'); ?></label>
                                 <input type="text" name="nedarimpay_student_groupe" class="form-control"
                                        value="<?php echo get_option('nedarimpay_student_groupe'); ?>"
                                        placeholder="e.g. tuition">
                                 <small class="help-block"><?php echo _l('nedarimpay_groupe_filter_help'); ?></small>
                              </div>
                           </div>
                        </div>
                     </div>

                     <!-- Donation Series -->
                     <div class="col-md-6">
                        <div class="panel_s" style="border:1px solid #dce4ec;">
                           <div class="panel-body">
                              <h5 class="no-margin">
                                 <span class="label label-primary"><?php echo _l('nedarimpay_type_donation'); ?></span>
                                 &nbsp;<?php echo _l('nedarimpay_series_donation_title'); ?>
                              </h5>
                              <p class="text-muted" style="font-size:12px;margin-top:5px;">
                                 <?php echo _l('nedarimpay_series_donation_desc'); ?>
                              </p>
                              <div class="form-group">
                                 <label><?php echo _l('nedarimpay_prefix'); ?></label>
                                 <input type="text" name="nedarimpay_donation_prefix" class="form-control"
                                        value="<?php echo get_option('nedarimpay_donation_prefix'); ?>"
                                        placeholder="D" maxlength="5">
                                 <small class="help-block">
                                    <?php echo _l('nedarimpay_preview'); ?>:
                                    <code id="donation_preview">
                                       <?php echo get_option('nedarimpay_donation_prefix'); ?>-00001
                                    </code>
                                 </small>
                              </div>
                              <div class="form-group">
                                 <label><?php echo _l('nedarimpay_groupe_filter'); ?></label>
                                 <input type="text" name="nedarimpay_donation_groupe" class="form-control"
                                        value="<?php echo get_option('nedarimpay_donation_groupe'); ?>"
                                        placeholder="e.g. donation">
                                 <small class="help-block"><?php echo _l('nedarimpay_groupe_filter_help'); ?></small>
                              </div>
                           </div>
                        </div>
                     </div>

                  </div>
               </div>
            </div>

            <!-- Email Templates -->
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin">
                     <i class="fa fa-envelope-o"></i>
                     <?php echo _l('nedarimpay_email_templates'); ?>
                  </h4>
                  <hr class="hr-panel-heading" />

                  <p class="text-muted">
                     <?php echo _l('nedarimpay_email_placeholders'); ?>:
                     <code>{client_name}</code>
                     <code>{amount}</code>
                     <code>{receipt_number}</code>
                     <code>{date}</code>
                     <code>{transaction_id}</code>
                  </p>

                  <ul class="nav nav-tabs" role="tablist">
                     <li class="active">
                        <a href="#tab-student-email" data-toggle="tab">
                           <i class="fa fa-graduation-cap"></i>
                           <?php echo _l('nedarimpay_type_student'); ?>
                        </a>
                     </li>
                     <li>
                        <a href="#tab-donation-email" data-toggle="tab">
                           <i class="fa fa-heart"></i>
                           <?php echo _l('nedarimpay_type_donation'); ?>
                        </a>
                     </li>
                  </ul>

                  <div class="tab-content" style="padding-top:15px;">

                     <div class="tab-pane active" id="tab-student-email">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_email_subject'); ?></label>
                           <input type="text" name="nedarimpay_student_email_subject" class="form-control"
                                  value="<?php echo htmlspecialchars(get_option('nedarimpay_student_email_subject')); ?>">
                        </div>
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_email_body'); ?></label>
                           <textarea name="nedarimpay_student_email_body" class="form-control" rows="5"><?php echo htmlspecialchars(get_option('nedarimpay_student_email_body')); ?></textarea>
                        </div>
                     </div>

                     <div class="tab-pane" id="tab-donation-email">
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_email_subject'); ?></label>
                           <input type="text" name="nedarimpay_donation_email_subject" class="form-control"
                                  value="<?php echo htmlspecialchars(get_option('nedarimpay_donation_email_subject')); ?>">
                        </div>
                        <div class="form-group">
                           <label><?php echo _l('nedarimpay_email_body'); ?></label>
                           <textarea name="nedarimpay_donation_email_body" class="form-control" rows="5"><?php echo htmlspecialchars(get_option('nedarimpay_donation_email_body')); ?></textarea>
                        </div>
                     </div>

                  </div>
               </div>
            </div>

         </div>
         <!-- /LEFT Column -->

         <!-- RIGHT Column: Status + Save -->
         <div class="col-md-4">

            <!-- Save Box -->
            <div class="panel_s">
               <div class="panel-body">
                  <button type="submit" class="btn btn-info btn-block">
                     <i class="fa fa-save"></i> <?php echo _l('save'); ?>
                  </button>
               </div>
            </div>

            <!-- Current Config Status -->
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo _l('nedarimpay_config_status'); ?></h4>
                  <hr class="hr-panel-heading" />
                  <table class="table no-margin">
                     <tr>
                        <td class="text-muted"><?php echo _l('nedarimpay_mosad_number'); ?></td>
                        <td>
                           <?php if (get_option('nedarimpay_mosad_number')) : ?>
                              <i class="fa fa-check-circle text-success"></i>
                           <?php else : ?>
                              <i class="fa fa-times-circle text-danger"></i>
                           <?php endif; ?>
                        </td>
                     </tr>
                     <tr>
                        <td class="text-muted"><?php echo _l('nedarimpay_api_valid'); ?></td>
                        <td>
                           <?php if (get_option('nedarimpay_api_valid')) : ?>
                              <i class="fa fa-check-circle text-success"></i>
                           <?php else : ?>
                              <i class="fa fa-times-circle text-danger"></i>
                           <?php endif; ?>
                        </td>
                     </tr>
                     <tr>
                        <td class="text-muted"><?php echo _l('nedarimpay_payment_mode'); ?></td>
                        <td>
                           <?php if (get_option('nedarimpay_payment_mode_id')) : ?>
                              <i class="fa fa-check-circle text-success"></i>
                           <?php else : ?>
                              <i class="fa fa-times-circle text-warning"></i>
                           <?php endif; ?>
                        </td>
                     </tr>
                     <tr>
                        <td class="text-muted"><?php echo _l('nedarimpay_type_student'); ?> prefix</td>
                        <td>
                           <code><?php echo get_option('nedarimpay_student_prefix') ?: 'T'; ?>-XXXXX</code>
                        </td>
                     </tr>
                     <tr>
                        <td class="text-muted"><?php echo _l('nedarimpay_type_donation'); ?> prefix</td>
                        <td>
                           <code><?php echo get_option('nedarimpay_donation_prefix') ?: 'D'; ?>-XXXXX</code>
                        </td>
                     </tr>
                  </table>
               </div>
            </div>

            <!-- Setup Guide -->
            <div class="panel_s" style="border-left:4px solid #5cb85c;">
               <div class="panel-body">
                  <h4 class="no-margin" style="color:#3c763d;">
                     <i class="fa fa-map-signs"></i>
                     <?php echo _l('nedarimpay_setup_guide_title'); ?>
                  </h4>
                  <hr class="hr-panel-heading" />
                  <p class="text-muted" style="font-size:12px;margin-bottom:12px;">
                     <?php echo _l('nedarimpay_setup_guide_intro'); ?>
                  </p>

                  <!-- Step 1 -->
                  <div style="display:flex;gap:10px;margin-bottom:12px;">
                     <div style="flex-shrink:0;">
                        <span class="label label-success" style="font-size:13px;padding:5px 8px;">1</span>
                     </div>
                     <div>
                        <strong><?php echo _l('nedarimpay_setup_step1_title'); ?></strong>
                        <p style="font-size:12px;margin:4px 0 0;color:#555;">
                           <?php echo _l('nedarimpay_setup_step1_body'); ?>
                        </p>
                     </div>
                  </div>

                  <!-- Step 2 -->
                  <div style="display:flex;gap:10px;margin-bottom:12px;">
                     <div style="flex-shrink:0;">
                        <span class="label label-success" style="font-size:13px;padding:5px 8px;">2</span>
                     </div>
                     <div>
                        <strong><?php echo _l('nedarimpay_setup_step2_title'); ?></strong>
                        <p style="font-size:12px;margin:4px 0 0;color:#555;">
                           <?php echo _l('nedarimpay_setup_step2_body'); ?>
                        </p>
                     </div>
                  </div>

                  <!-- Step 3 -->
                  <div style="display:flex;gap:10px;margin-bottom:12px;">
                     <div style="flex-shrink:0;">
                        <span class="label label-warning" style="font-size:13px;padding:5px 8px;">3</span>
                     </div>
                     <div>
                        <strong><?php echo _l('nedarimpay_setup_step3_title'); ?></strong>
                        <p style="font-size:12px;margin:4px 0 0;color:#555;">
                           <?php echo _l('nedarimpay_setup_step3_body'); ?>
                        </p>
                     </div>
                  </div>

                  <!-- Step 4 -->
                  <div style="display:flex;gap:10px;margin-bottom:12px;">
                     <div style="flex-shrink:0;">
                        <span class="label label-info" style="font-size:13px;padding:5px 8px;">4</span>
                     </div>
                     <div>
                        <strong><?php echo _l('nedarimpay_setup_step4_title'); ?></strong>
                        <p style="font-size:12px;margin:4px 0 0;color:#555;">
                           <?php echo _l('nedarimpay_setup_step4_body'); ?>
                        </p>
                     </div>
                  </div>

                  <!-- Step 5 -->
                  <div style="display:flex;gap:10px;margin-bottom:12px;">
                     <div style="flex-shrink:0;">
                        <span class="label label-info" style="font-size:13px;padding:5px 8px;">5</span>
                     </div>
                     <div>
                        <strong><?php echo _l('nedarimpay_setup_step5_title'); ?></strong>
                        <p style="font-size:12px;margin:4px 0 0;color:#555;">
                           <?php echo _l('nedarimpay_setup_step5_body'); ?>
                        </p>
                     </div>
                  </div>

                  <!-- Step 6 -->
                  <div style="display:flex;gap:10px;margin-bottom:12px;">
                     <div style="flex-shrink:0;">
                        <span class="label label-primary" style="font-size:13px;padding:5px 8px;">6</span>
                     </div>
                     <div>
                        <strong><?php echo _l('nedarimpay_setup_step6_title'); ?></strong>
                        <p style="font-size:12px;margin:4px 0 0;color:#555;">
                           <?php echo _l('nedarimpay_setup_step6_body'); ?>
                        </p>
                     </div>
                  </div>

                  <!-- Tips -->
                  <div class="alert alert-info" style="margin-top:10px;font-size:12px;">
                     <strong><i class="fa fa-lightbulb-o"></i> <?php echo _l('nedarimpay_setup_tip_title'); ?></strong>
                     <ul style="margin:6px 0 0;padding-left:18px;">
                        <li style="margin-bottom:4px;"><?php echo _l('nedarimpay_setup_tip_email'); ?></li>
                        <li style="margin-bottom:4px;"><?php echo _l('nedarimpay_setup_tip_groupe'); ?></li>
                        <li><?php echo _l('nedarimpay_setup_tip_match'); ?></li>
                     </ul>
                  </div>

                  <p class="text-muted" style="font-size:11px;margin-bottom:0;">
                     <i class="fa fa-question-circle"></i>
                     <?php echo _l('nedarimpay_setup_need_help'); ?>
                  </p>
               </div>
            </div>

            <!-- API Docs Links -->
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo _l('nedarimpay_api_docs'); ?></h4>
                  <hr class="hr-panel-heading" />
                  <ul class="list-unstyled">
                     <li>
                        <i class="fa fa-external-link"></i>
                        <a href="https://matara.pro/nedarimplus/ApiDocumentation.html" target="_blank">
                           Nedarim Plus API Docs
                        </a>
                     </li>
                     <li>
                        <i class="fa fa-external-link"></i>
                        <a href="https://invoice4uapi.docs.apiary.io/" target="_blank">
                           Invoice4U API Docs
                        </a>
                     </li>
                  </ul>
               </div>
            </div>

         </div>
         <!-- /RIGHT Column -->

      </div>

      <?php echo form_close(); ?>

   </div>
</div>
<?php init_tail(); ?>
<script>
function copyWebhookUrl() {
   var el = document.getElementById('webhook_url_display');
   el.select();
   el.setSelectionRange(0, 99999);
   document.execCommand('copy');
   alert_float('success', '<?php echo _l('nedarimpay_copied'); ?>');
}

// Live prefix preview
document.querySelector('input[name="nedarimpay_student_prefix"]').addEventListener('input', function () {
   document.getElementById('student_preview').textContent = (this.value || 'T') + '-00001';
});
document.querySelector('input[name="nedarimpay_donation_prefix"]').addEventListener('input', function () {
   document.getElementById('donation_preview').textContent = (this.value || 'D') + '-00001';
});
</script>
