<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-mtop"><i class="fa fa-handshake-o"></i> <?php echo _l('bdm_referral'); ?></h4>
            <p class="text-muted">Refer this lead into the BDM pipeline.</p>
            <hr class="hr-panel-heading" />

            <?php echo form_open(admin_url('bdm_operations/refer_lead/' . $lead->id), ['id' => 'bdm_referral_form']); ?>
              <?php echo render_input('company', 'company_name', $lead->company, 'text', ['readonly' => true]); ?>

              <?php echo render_select('registration_type', [
                  ['id' => 'Supplier Onboarding',     'name' => _l('bdm_supplier_onboarding')],
                  ['id' => 'Client Vendor Approval', 'name' => _l('bdm_client_approval')],
              ], ['id', 'name'], 'bdm_registration_type'); ?>

              <?php
                $segs = [];
                foreach (bdm_segments() as $k => $v) $segs[] = ['id' => $k, 'name' => $v];
                echo render_select('segment', $segs, ['id', 'name'], 'bdm_segment');
              ?>

              <?php
                $prios = [];
                foreach (bdm_priorities() as $k => $v) $prios[] = ['id' => $k, 'name' => $v];
                echo render_select('priority', $prios, ['id', 'name'], 'bdm_priority', 'Medium');
              ?>

              <?php echo render_input('expected_value', 'bdm_expected_value', 0, 'number', ['step' => '0.01']); ?>

              <?php echo render_select('assigned_bdm', $staff, ['staffid', ['firstname', 'lastname']], 'assigned_bdm', get_staff_user_id()); ?>

              <?php echo render_textarea('notes', 'bdm_notes', 'Lead referred from Perfex Leads. Name: ' . $lead->name); ?>

              <div class="text-right">
                <a href="<?php echo admin_url('leads/index/' . $lead->id); ?>" class="btn btn-default"><?php echo _l('close'); ?></a>
                <button type="submit" class="btn btn-info"><i class="fa fa-paper-plane"></i> <?php echo _l('submit'); ?></button>
              </div>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
