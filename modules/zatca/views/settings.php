<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo _l('zatca_settings'); ?></h4>
                  <hr class="hr-panel-heading" />
                  
                  <?php echo form_open(admin_url('zatca/settings')); ?>
                  
                  <div class="row">
                     <div class="col-md-6">
                        <div class="form-group">
                            <label for="zatca_mode"><?php echo _l('zatca_mode'); ?></label>
                            <select name="zatca_mode" id="zatca_mode" class="form-control">
                                <option value="sandbox" <?php if(get_option('zatca_mode') == 'sandbox'){echo 'selected';} ?>>Sandbox / Developer</option>
                                <option value="simulation" <?php if(get_option('zatca_mode') == 'simulation'){echo 'selected';} ?>>Simulation</option>
                                <option value="production" <?php if(get_option('zatca_mode') == 'production'){echo 'selected';} ?>>Production</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="zatca_otp"><?php echo _l('zatca_otp'); ?></label>
                            <input type="text" name="zatca_otp" id="zatca_otp" class="form-control" value="<?php echo get_option('zatca_otp'); ?>">
                            <p class="text-muted">Enter the OTP from Fatoora Portal to Onboard.</p>
                        </div>

                        <hr />
                        <h5><?php echo _l('zatca_csr_config'); ?></h5>

                        <div class="form-group">
                            <label for="zatca_csr_common_name"><?php echo _l('zatca_common_name'); ?></label>
                            <input type="text" name="zatca_csr_common_name" id="zatca_csr_common_name" class="form-control" value="<?php echo get_option('zatca_csr_common_name'); ?>">
                            <p class="text-muted">E.g., "TST-886431145-311158864300003". Unique for every device/solution.</p>
                        </div>

                     </div>
                     
                     <div class="col-md-6">
                        <div class="alert alert-info">
                            <h5>Current Status</h5>
                            <p><strong>Compliance CSID:</strong> <?php echo get_option('zatca_compliance_csid') ? '✅ Generated' : '❌ Not Generated'; ?></p>
                            <p><strong>Production CSID:</strong> <?php echo get_option('zatca_production_csid') ? '✅ Generated' : '❌ Not Generated'; ?></p>
                            
                            <hr />
                            <a href="<?php echo admin_url('zatca/generate_keys'); ?>" class="btn btn-primary btn-block">
                                <i class="fa fa-key"></i> Generate Keys & CSR
                            </a>
                            <br />

                             <?php if(!get_option('zatca_compliance_csid')): ?>
                                <a href="<?php echo admin_url('zatca/onboard'); ?>" class="btn btn-warning btn-block">
                                    <?php echo _l('zatca_onboard'); ?> (Step 2)
                                </a>
                             <?php endif; ?>
                        </div>
                     </div>
                  </div>

                  <div class="text-right">
                     <button type="submit" class="btn btn-info"><?php echo _l('save'); ?></button>
                  </div>
                  <?php echo form_close(); ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
