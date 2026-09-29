<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI =& get_instance();
$CI->load->model('telecalling_ld/Telecalling_ld_model','tc_model');
$wa_logs = $CI->tc_model->get_whatsapp_log($lead_id);
?>
<li class="nav-item"><a href="#tc-wa-tab" data-toggle="tab"><i class="fa fa-whatsapp"></i> WhatsApp (<?=count($wa_logs)?>)</a></li>
<div id="tc-wa-tab" class="tab-pane">
  <div class="tc-wrap" style="padding:0">
    <?php if($wa_logs): ?>
    <table class="tc-table">
      <thead><tr><th>Date</th><th>Direction</th><th>Message</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach($wa_logs as $w): ?>
        <tr>
          <td><?=$w->sent_at?></td>
          <td><?=ucfirst($w->direction)?></td>
          <td><?=htmlspecialchars($w->message)?></td>
          <td><span class="tc-pill <?=$w->status=='sent'?'tc-pill-green':'tc-pill-red'?>"><?=$w->status?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?><p class="text-muted">No WhatsApp messages yet.</p><?php endif; ?>
  </div>
</div>
