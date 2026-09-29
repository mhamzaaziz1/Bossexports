<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI =& get_instance();
$logs = $CI->db->select('c.*,CONCAT(s.firstname," ",s.lastname) AS agent_name')
    ->from($CI->db->dbprefix.'tc_call_log c')
    ->join('tblstaff s','s.staffid=c.agent_id','left')
    ->where('c.lead_id',$lead_id)->order_by('c.called_at','DESC')->get()->result();
?>
<li class="nav-item"><a href="#tc-call-log-tab" data-toggle="tab"><i class="fa fa-phone"></i> Call Log (<?=count($logs)?>)</a></li>
<!-- Tab content added to tab-content div via JS or separately -->
<div id="tc-call-log-tab" class="tab-pane">
  <div class="tc-wrap" style="padding:0">
    <?php if(is_admin()||tc_is_manager()): ?>
    <div class="tc-card" style="margin-bottom:12px">
      <form method="post" action="<?=admin_url('telecalling_ld/log_call')?>">
        <?=form_hidden($CI->security->get_csrf_token_name(),$CI->security->get_csrf_hash())?>
        <input type="hidden" name="lead_id" value="<?=$lead_id?>">
        <div class="row">
          <div class="col-md-3"><div class="form-group"><label>Call Status</label>
            <select name="tc_call_status" class="form-control">
              <option value="not_answered">Not Answered</option><option value="callback_later">Callback Later</option>
              <option value="interested">Interested</option><option value="not_interested">Not Interested</option>
              <option value="appointment_scheduled">Appointment Scheduled</option><option value="converted">Converted</option>
            </select></div></div>
          <div class="col-md-7"><div class="form-group"><label>Notes</label><textarea name="tc_notes" class="form-control" rows="2"></textarea></div></div>
          <div class="col-md-2" style="display:flex;align-items:flex-end;padding-bottom:16px"><button type="submit" class="btn btn-primary btn-block">Log Call</button></div>
        </div>
      </form>
    </div>
    <?php endif; ?>
    <?php if($logs): ?>
    <table class="tc-table">
      <thead><tr><th>Date</th><th>Agent</th><th>Status</th><th>Notes</th><th>Score</th><th>AI Score</th><th>AI Suggestion</th></tr></thead>
      <tbody>
        <?php foreach($logs as $l): ?>
        <tr>
          <td><?=$l->called_at?></td>
          <td><?=htmlspecialchars($l->agent_name??'—')?></td>
          <td><span class="tc-status-<?=$l->call_status?>"><?=ucwords(str_replace('_',' ',$l->call_status))?></span></td>
          <td><?=htmlspecialchars($l->notes??'')?></td>
          <td><?=$l->lead_score?></td>
          <td><?=$l->ai_score>0?$l->ai_score:'—'?></td>
          <td><?=htmlspecialchars($l->ai_suggestion??'')?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?><p class="text-muted">No calls logged yet.</p><?php endif; ?>
  </div>
</div>
