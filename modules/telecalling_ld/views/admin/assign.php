<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?=module_dir_url('telecalling_ld','assets/css/tc_style.css')?>">

<div id="wrapper">
  <div class="content tc-content-wrapper">
    <div class="tc-wrap animate-fadeIn">
      
      <div class="tc-header">
        <div class="tc-title-area">
          <div class="tc-icon-box"><i class="fa fa-users"></i></div>
          <div>
            <h1 class="tc-h-main">Assign Leads</h1>
            <p class="tc-h-sub">Distribute leads to agents via rotation, groups, or manual selection</p>
          </div>
        </div>
        <div class="tc-actions">
           <a href="<?=admin_url('telecalling_ld')?>" class="tc-btn tc-btn-outline"><i class="fa fa-arrow-left"></i> Dashboard</a>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4">
          <div class="tc-kpi teal">
            <div class="tc-kpi-accent"></div>
            <div class="tc-kpi-top">
               <div class="tc-kpi-icon"><i class="fa fa-cloud-download"></i></div>
            </div>
            <div class="tc-kpi-lbl">Unassigned Leads</div>
            <div class="tc-kpi-val"><?=number_format($unassigned_count)?></div>
            <div class="tc-kpi-sub">Available Leads</div>
          </div>
          
          <div class="tc-kpi purple" style="margin-top:16px;">
            <div class="tc-kpi-accent"></div>
            <div class="tc-kpi-top">
               <div class="tc-kpi-icon"><i class="fa fa-refresh"></i></div>
            </div>
            <div class="tc-kpi-lbl">Rotation Queue</div>
            <div class="tc-kpi-val"><?=number_format($queue_count)?></div>
            <div class="tc-kpi-sub">Total in Queue</div>
          </div>
        </div>

        <div class="col-md-8">
          <div class="tc-card">
            <div class="tc-card-title">Distribution Settings <small>Choose your method</small></div>
            <form method="post" action="<?=admin_url('telecalling_ld/assign')?>">
              <?=form_hidden($this->security->get_csrf_token_name(),$this->security->get_csrf_hash())?>
              
              <div class="form-group">
                <label class="control-label">Distribution Method</label>
                <select name="method" id="tc-method" class="form-control selectpicker" onchange="tcToggleMethod()">
                  <option value="rotation">Auto-Rotation (Fair Share)</option>
                  <option value="group">Assign to Group</option>
                  <option value="agent">Manual Agent Selection</option>
                  <option value="bulk">Bulk Distribution</option>
                </select>
              </div>

              <div id="method-group" class="tc-method-fields" style="display:none;">
                <div class="form-group">
                  <label class="control-label">Target Group</label>
                  <select name="group_id" class="form-control selectpicker">
                    <?php foreach($groups as $g): ?><option value="<?=$g->id?>"><?=htmlspecialchars($g->name)?></option><?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div id="method-agent" class="tc-method-fields" style="display:none;">
                <div class="form-group">
                  <label class="control-label">Select Agent</label>
                  <select name="agent_id" class="form-control selectpicker" data-live-search="true">
                    <?php foreach($staff as $s): ?><option value="<?=$s->staffid?>"><?=htmlspecialchars($s->firstname.' '.$s->lastname)?></option><?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label">Number of Leads to Distribute</label>
                <input type="number" name="count" class="form-control" value="10" min="1" max="<?=$unassigned_count?>">
              </div>

              <div class="text-right" style="margin-top:20px;">
                <button type="submit" class="tc-btn tc-btn-primary" style="padding: 12px 30px;"><i class="fa fa-check-circle"></i> Run Distribution</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php init_tail(); ?>

<script>
function tcToggleMethod(){
  var m = document.getElementById('tc-method').value;
  document.querySelectorAll('.tc-method-fields').forEach(el=>el.style.display='none');
  if(m==='group') document.getElementById('method-group').style.display='block';
  if(m==='agent') document.getElementById('method-agent').style.display='block';
}
</script>
