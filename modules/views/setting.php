<?php defined('BASEPATH') or exit('No direct script access allowed');?>

<?php init_head();?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
              <div class="panel-body">
                <?php if($organizations && $check_old_data){ ?>
                <div class="border-right">
                  <h4 class="no-margin font-bold"><?php echo _l('migrate_legacy_data'); ?></h4>
                  <hr />
                </div>
                <label class="text-danger"><?php echo _l('migrate_legacy_data_tooltip'); ?></label>
                <br>
                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#migrate-legacy-modal"> <?php echo _l('zoho_migrate'); ?> </button>
                <hr>
                <?php } ?>

                <?php echo form_open(admin_url('zoho_integration/update_setting')); ?>

                <?php if($organizations){ ?>
                <div class="border-right">
                  <h4 class="no-margin font-bold"><?php echo _l('automatic_sync_config'); ?></h4>
                  <hr />
                </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php render_yes_no_option('acc_integration_zoho_sync_from_system', 'acc_sync_from_system_to_zoho'); ?>
                      </div>
                      <div class="col-md-6">
                      <?php 


                      $sync_from_system_organizations = explode(',', get_option('acc_integration_zoho_sync_from_system_organizations')); ?>
                      <?php echo render_select('acc_integration_zoho_sync_from_system_organizations[]',$organizations,array('organization_id', 'name'),'organizations',$sync_from_system_organizations,array('multiple'=>true,'data-actions-box'=>true),array(),'','',false);
                      ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php render_yes_no_option('acc_integration_zoho_sync_to_system', 'acc_sync_from_zoho_to_system'); ?>
                      </div>
                      <div class="col-md-6">
                        <?php $sync_to_system_organizations = explode(',', get_option('acc_integration_zoho_sync_to_system_organizations')); ?>
                      <?php echo render_select('acc_integration_zoho_sync_to_system_organizations[]',$organizations,array('organization_id', 'name'),'organizations',$sync_to_system_organizations,array('multiple'=>true,'data-actions-box'=>true),array(),'','',false); ?>

                      </div>
                    </div>
                <?php } ?>

                <div class="border-right mtop20">
                  <h4 class="no-margin font-bold"><?php echo _l('connection'); ?></h4>
                  <hr />
                </div>
                <?php
                    $value = get_option('acc_integration_zoho_client_id');
                    $client_id = $this->encryption->decrypt($value);
                    echo render_input('settings[acc_integration_zoho_client_id]', 'acc_client_id', $client_id);

                    $value = get_option('acc_integration_zoho_client_secret');
                    $client_secret = $this->encryption->decrypt($value);
                    echo render_input('settings[acc_integration_zoho_client_secret]', 'acc_client_secret', $client_secret);
                ?>
                  <?php if(get_option('acc_integration_zoho_connected') == 1){ ?>
                    <span class="label label-success mbot10"><?php echo _l('connected'); ?></span><br>
                  <?php }else{ ?>
                    <span class="label label-warning mbot10"><?php echo _l('not_connected_yet'); ?></span><br>
                  <?php } ?>
                  <a href="<?php echo admin_url('zoho_integration/connect'); ?>" class="btn btn-primary"><?php echo _l('connect'); ?></a>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                </div>
                <?php echo form_close(); ?>
              </div>
            </div>
         </div>
      </div>
   </div>
</div>

<div class="modal fade" id="migrate-legacy-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <?php echo form_open(admin_url('zoho_integration/migrate_legacy_data'), array('id' => 'send-example-email-form')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <?php echo _l('zoho_migrate'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                      <?php echo render_select('organization_id',$organizations,array('organization_id','name'),'organization', '', array(), array(), '', '', false); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" class="btn btn-primary"><?php echo _l('zoho_migrate'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php init_tail();?>
</body>
</html>
