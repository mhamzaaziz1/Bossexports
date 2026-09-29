<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI =& get_instance();
$CI->load->model('telecalling_ld/Telecalling_ld_model','tc_model');
$email_logs = $CI->tc_model->get_email_log($lead_id);
?>
<li class="nav-item"><a href="#tc-email-tab" data-toggle="tab"><i class="fa fa-envelope-o"></i> Email History (<?=count($email_logs)?>)</a></li>
<div id="tc-email-tab" class="tab-pane">
  <div class="tc-wrap" style="padding:0">
    <?php if($email_logs): ?>
    <table class="tc-table">
      <thead><tr><th>Sent</th><th>Campaign</th><th>Subject</th><th>Opened</th><th>Clicked</th><th>Bounced</th><th>Unsub</th></tr></thead>
      <tbody>
        <?php foreach($email_logs as $e): ?>
        <tr>
          <td><?=$e->sent_at?></td>
          <td><?=htmlspecialchars($e->campaign_name??'Manual')?></td>
          <td><?=htmlspecialchars($e->subject)?></td>
          <td><?=$e->opened_at?'<i class="fa fa-check text-success"></i> '.date('d M H:i',strtotime($e->opened_at)):'<i class="fa fa-times text-muted"></i>'?></td>
          <td><?=$e->clicked_at?'<i class="fa fa-check text-success"></i>':'<i class="fa fa-times text-muted"></i>'?></td>
          <td><?=$e->bounced?'<i class="fa fa-exclamation-triangle text-danger"></i>':'—'?></td>
          <td><?=$e->unsubscribed?'<i class="fa fa-ban text-danger"></i>':'—'?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?><p class="text-muted">No emails sent yet.</p><?php endif; ?>
  </div>
</div>
