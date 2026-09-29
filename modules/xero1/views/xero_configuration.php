<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6 left-column">
			
                <div class="panel_s" >
				  <div class="panel-body">
                        <?php echo form_open(admin_url('xero/save_configuration'),array('id'=>'meeting-submit-form')); ?>
                    <div class="row">

                        <div class="col-md-12">
                            <h2>Xero Configuration Details</h2>
                        </div>

                    <div class="row">

                    <div class="col-md-12">
                          <div class="form-group open-ticket-subject-group">
                              <?php echo render_input('client_id','xero_client_id', $client_id); ?>
                              <?php echo form_error('client_id'); ?>
                              <p class="text-muted">Note: Get the value of <a target="_blank" href="https://developer.xero.com/myapps/">client id</a> from xero app.</p>
                          </div>
                     </div>

                        <div class="col-md-12">
                            <div class="form-group open-ticket-subject-group">
                                <?php echo render_input('client_secret','xero_client_secret', $client_secret); ?>
                                <?php echo form_error('client_secret'); ?>
                                <p class="text-muted">Note: Get the value of <a target="_blank" href="https://developer.xero.com/myapps/">client secret</a> from xero app.</p>
                            </div>
                        </div>


                        <div class="col-md-12">
                            <div class="form-group open-ticket-subject-group">
                                <?php echo render_input('redirect_uri','oAuth_redirect_uri', $redirect_uri); ?>
                                <?php echo form_error('redirect_uri'); ?>
                                <p class="text-muted">Note: Get the value of <a target="_blank" href="https://developer.xero.com/myapps/">OAuth 2.0 redirect URI</a> from xero app.</p>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group open-ticket-subject-group">
                                <button type="button" class="btn btn-warning" onclick="window.location.href='<?php echo admin_url('xero/xeroAuthorization')?>'"><?php echo _l('xero_refresh_token'); ?></button>
                            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
							</div>
							
                        </div>

                  
<?php echo form_close(); ?>
                                     </div>

   </div>

</div>







  

</div>

</div>

</div>

</div>



</div>

</div>

</div>
<?php init_tail(); ?>



</body>

</html>

