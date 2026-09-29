<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="_buttons">
              <a href="#" class="btn btn-info pull-left display-block" onclick="new_activity(); return false;">
                <i class="fa fa-plus"></i> <?php echo _l('bdm_new_activity'); ?>
              </a>
            </div>
            <div class="clearfix"></div>
            <hr class="hr-panel-heading" />
            <table class="table dt-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th><?php echo _l('bdm_activity_type'); ?></th>
                  <th><?php echo _l('bdm_customer_name'); ?></th>
                  <th><?php echo _l('contact_name'); ?></th>
                  <th><?php echo _l('phone'); ?></th>
                  <th><?php echo _l('bdm_outcome'); ?></th>
                  <th><?php echo _l('description'); ?></th>
                  <th><?php echo _l('bdm_next_action'); ?></th>
                  <th><?php echo _l('follow_up_date'); ?></th>
                  <th><?php echo _l('date'); ?></th>
                  <th>BDM</th>
                  <th><?php echo _l('options'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php
                $type_colors = [
                    'Visit'    => 'primary',
                    'Call'     => 'success',
                    'Email'    => 'info',
                    'WhatsApp' => 'warning',
                    'LinkedIn' => 'default',
                    'Meeting'  => 'danger',
                ];
                foreach ($activities as $activity):
                    $tc = $type_colors[$activity['type']] ?? 'default';
                ?>
                <tr>
                  <td><?php echo $activity['id']; ?></td>
                  <td><span class="label label-<?php echo $tc; ?>"><?php echo htmlentities($activity['type']); ?></span></td>
                  <td><strong><?php echo htmlentities($activity['customer_name']); ?></strong></td>
                  <td><?php echo htmlentities($activity['contact_person'] ?? ''); ?></td>
                  <td><?php echo htmlentities($activity['phone'] ?? ''); ?></td>
                  <td><?php echo htmlentities($activity['outcome'] ?? '—'); ?></td>
                  <td><small><?php echo htmlentities($activity['description'] ?? ''); ?></small></td>
                  <td><?php echo htmlentities($activity['next_action'] ?? ''); ?></td>
                  <td><?php echo $activity['follow_up_date'] ? _d($activity['follow_up_date']) : '—'; ?></td>
                  <td><?php echo _dt($activity['date_created']); ?></td>
                  <td><?php echo get_staff_full_name($activity['staff_id']); ?></td>
                  <td>
                    <a href="<?php echo admin_url('bdm_operations/delete_activity/' . $activity['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="activity_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <?php echo form_open(admin_url('bdm_operations/activities'), ['id' => 'activity_form']); ?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo _l('bdm_new_activity'); ?></h4>
      </div>
      <div class="modal-body">
        <?php
          $types_arr = [];
          foreach (bdm_activity_types() as $k => $v) $types_arr[] = ['id' => $k, 'name' => $v];
          echo render_select('type', $types_arr, ['id', 'name'], 'bdm_activity_type', 'Call');
        ?>
        <?php
          $channels_arr = [];
          foreach (bdm_marketing_channels() as $k => $v) $channels_arr[] = ['id' => $k, 'name' => $v];
          echo render_select('channel', $channels_arr, ['id', 'name'], 'bdm_campaign_channel');
        ?>
        <?php echo render_input('customer_name', 'bdm_customer_name'); ?>
        <?php echo render_input('contact_person', 'contact_name'); ?>
        <?php echo render_input('phone', 'phone'); ?>
        <?php echo render_input('email', 'email'); ?>
        <?php echo render_select('outcome', [
            ['id' => '',                    'name' => '—'],
            ['id' => 'Meeting Booked',      'name' => _l('outcome_meeting_booked')],
            ['id' => 'No Answer',           'name' => _l('outcome_no_answer')],
            ['id' => 'Not Interested',      'name' => _l('outcome_not_interested')],
            ['id' => 'Qualified',           'name' => _l('outcome_qualified')],
            ['id' => 'Proposal Requested',  'name' => _l('outcome_proposal_requested')],
            ['id' => 'Follow Up Required',  'name' => _l('outcome_follow_up')],
        ], ['id', 'name'], 'bdm_outcome'); ?>
        <?php echo render_textarea('description', 'description'); ?>
        <?php echo render_input('next_action', 'bdm_next_action'); ?>
        <?php echo render_date_input('follow_up_date', 'follow_up_date'); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
      </div>
    </div>
    <?php echo form_close(); ?>
  </div>
</div>

<?php init_tail(); ?>
<script>
function new_activity() {
    $('#activity_form')[0].reset();
    $('#activity_form select').selectpicker('refresh');
    $('#activity_modal').modal('show');
}
</script>
