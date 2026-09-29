<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?=module_dir_url('telecalling_ld','assets/css/tc_style.css')?>">

<div id="wrapper">
  <div class="content tc-content-wrapper">
    <div class="tc-wrap animate-fadeIn">
      
      <div class="tc-header">
        <div class="tc-title-area">
          <div class="tc-icon-box"><i class="fa fa-bullseye"></i></div>
          <div>
            <h1 class="tc-h-main">Performance Targets</h1>
            <p class="tc-h-sub">Set daily and weekly goals for agents and groups</p>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-5">
          <div class="tc-card">
            <div class="tc-card-title">Set New Target <small>Define goals</small></div>
            <form method="post" action="<?=admin_url('telecalling_ld/save_target')?>">
               <?=form_hidden($this->security->get_csrf_token_name(),$this->security->get_csrf_hash())?>
               
               <div class="form-group">
                 <label>Target For</label>
                 <select name="rel_type" id="tc-target-type" class="form-control selectpicker" onchange="tcToggleTargetRel()">
                    <option value="staff">Individual Agent</option>
                    <option value="group">Sales Group</option>
                 </select>
               </div>

               <div id="target-rel-staff" class="tc-target-rel">
                 <div class="form-group">
                   <label>Search Agent</label>
                   <select name="rel_id_staff" class="form-control selectpicker" data-live-search="true">
                      <?php foreach($staff as $s): ?><option value="<?=$s['staffid']?>"><?=htmlspecialchars($s['firstname'].' '.$s['lastname'])?></option><?php endforeach; ?>
                   </select>
                 </div>
               </div>

               <div id="target-rel-group" class="tc-target-rel" style="display:none;">
                 <div class="form-group">
                   <label>Select Group</label>
                   <select name="rel_id_group" class="form-control selectpicker">
                      <?php foreach($groups as $g): ?><option value="<?=$g->id?>"><?=htmlspecialchars($g->name)?></option><?php endforeach; ?>
                   </select>
                 </div>
               </div>

               <div class="form-group">
                 <label>Performance metric</label>
                 <select name="metric" class="form-control selectpicker">
                    <option value="calls">Number of Calls</option>
                    <option value="appointments">Appointments Scheduled</option>
                    <option value="conversions">Final Conversions</option>
                 </select>
               </div>

               <div class="row">
                 <div class="col-md-6">
                   <div class="form-group"><label>Target Value</label><input type="number" name="target_value" class="form-control" value="50"></div>
                 </div>
                 <div class="col-md-6">
                   <div class="form-group">
                     <label>Period</label>
                     <select name="period" class="form-control selectpicker">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                     </select>
                   </div>
                 </div>
               </div>

               <div class="text-right" style="margin-top:20px;">
                 <button type="submit" class="tc-btn tc-btn-primary btn-block">Create Target Goal</button>
               </div>
            </form>
          </div>
        </div>

        <div class="col-md-7">
          <div class="tc-card">
            <div class="tc-card-title">Active Targets <small>Current monitoring</small></div>
            <div class="tc-table-responsive">
              <table class="tc-table">
                <thead><tr><th>Target For</th><th>Metric</th><th>Goal</th><th>Period</th><th>Action</th></tr></thead>
                <tbody>
                  <?php foreach($targets as $t): ?>
                  <tr>
                    <td style="font-weight:700;"><?=htmlspecialchars($t->target_name)?></td>
                    <td><span class="tc-pill tc-pill-blue"><?=ucwords($t->metric)?></span></td>
                    <td style="font-weight:800; color:var(--tc-primary);"><?=$t->target_value?></td>
                    <td><span class="tc-pill tc-pill-outline"><?=ucwords($t->period)?></span></td>
                    <td>
                      <a href="<?=admin_url('telecalling_ld/delete_target/'.$t->id)?>" class="text-danger" onclick="return confirm('Remove target?')"><i class="fa fa-trash"></i></a>
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
</div>

<?php init_tail(); ?>

<script>
function tcToggleTargetRel(){
  var v = document.getElementById('tc-target-type').value;
  document.querySelectorAll('.tc-target-rel').forEach(el=>el.style.display='none');
  document.getElementById('target-rel-'+v).style.display='block';
}
</script>
