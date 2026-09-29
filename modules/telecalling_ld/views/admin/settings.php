<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?=module_dir_url('telecalling_ld','assets/css/tc_style.css')?>">

<div id="wrapper">
  <div class="content tc-content-wrapper">
    <div class="tc-wrap animate-fadeIn">
      
      <div class="tc-header">
        <div class="tc-title-area">
          <div class="tc-icon-box"><i class="fa fa-cog"></i></div>
          <div>
            <h1 class="tc-h-main">Module Settings</h1>
            <p class="tc-h-sub">Configure email, WhatsApp, AI, and lead distribution logic</p>
          </div>
        </div>
      </div>

      <form method="post" action="<?=admin_url('telecalling_ld/save_settings')?>">
        <?=form_hidden($this->security->get_csrf_token_name(),$this->security->get_csrf_hash())?>
        
        <div class="tc-card">
          <div class="tc-tabs" style="display:flex; border-bottom:1px solid var(--tc-border); margin-bottom:20px;">
             <div class="tc-tab active" onclick="tcShowTab('st-email', this)" style="padding:15px 25px; cursor:pointer; font-weight:700; color:var(--tc-primary); border-bottom:3px solid var(--tc-primary);">Email</div>
             <div class="tc-tab" onclick="tcShowTab('st-wa', this)" style="padding:15px 25px; cursor:pointer; font-weight:600; color:var(--tc-text-muted);">WhatsApp</div>
             <div class="tc-tab" onclick="tcShowTab('st-ai', this)" style="padding:15px 25px; cursor:pointer; font-weight:600; color:var(--tc-text-muted);">AI & Scoring</div>
             <div class="tc-tab" onclick="tcShowTab('st-gen', this)" style="padding:15px 25px; cursor:pointer; font-weight:600; color:var(--tc-text-muted);">General</div>
          </div>

          <!-- Email Settings -->
          <div id="st-email" class="tc-tab-content">
             <div class="row">
               <div class="col-md-6">
                 <div class="form-group"><label>SMTP Host</label><input type="text" name="tc_smtp_host" class="form-control" value="<?=htmlspecialchars($settings['tc_smtp_host']??'')?>"></div>
                 <div class="form-group"><label>SMTP User</label><input type="text" name="tc_smtp_user" class="form-control" value="<?=htmlspecialchars($settings['tc_smtp_user']??'')?>"></div>
               </div>
               <div class="col-md-6">
                 <div class="form-group"><label>SMTP Password</label><input type="password" name="tc_smtp_pass" class="form-control" value="<?=htmlspecialchars($settings['tc_smtp_pass']??'')?>"></div>
                 <div class="form-group"><label>From Name</label><input type="text" name="tc_from_name" class="form-control" value="<?=htmlspecialchars($settings['tc_from_name']??'')?>"></div>
               </div>
             </div>
          </div>

          <!-- WhatsApp Settings -->
          <div id="st-wa" class="tc-tab-content" style="display:none;">
             <div class="form-group"><label>WhatsApp API Key</label><input type="text" name="tc_wa_api_key" class="form-control" value="<?=htmlspecialchars($settings['tc_wa_api_key']??'')?>"></div>
             <div class="form-group"><label>Instance ID</label><input type="text" name="tc_wa_instance_id" class="form-control" value="<?=htmlspecialchars($settings['tc_wa_instance_id']??'')?>"></div>
          </div>

          <!-- General Settings -->
          <div id="st-gen" class="tc-tab-content" style="display:none;">
             <div class="form-group"><label>Default leads per request</label><input type="number" name="tc_default_leads" class="form-control" value="<?=$settings['tc_default_leads']??10?>"></div>
             <div class="checkbox checkbox-primary"><input type="checkbox" name="tc_leader_can_assign" value="1" <?=($settings['tc_leader_can_assign']??'')==='1'?'checked':''?>><label>Allow group leaders to assign leads</label></div>
             <div class="checkbox checkbox-primary"><input type="checkbox" name="tc_agent_can_request" value="1" <?=($settings['tc_agent_can_request']??'')==='1'?'checked':''?>><label>Allow agents to request more leads</label></div>
          </div>

          <div id="st-ai" class="tc-tab-content" style="display:none;">
             <p class="text-muted" style="padding:20px; font-style:italic;">AI Scoring and Sentiment Analysis features require an valid API key. Coming soon.</p>
          </div>

          <div style="margin-top:40px; border-top:1px solid var(--tc-border); padding-top:20px;">
             <button type="submit" class="tc-btn tc-btn-primary btn-lg"><i class="fa fa-save"></i> Save All Settings</button>
          </div>
        </div>
      </form>

    </div>
  </div>
</div>

<?php init_tail(); ?>

<script>
function tcShowTab(id, el){
  document.querySelectorAll('.tc-tab-content').forEach(c=>c.style.display='none');
  document.getElementById(id).style.display='block';
  document.querySelectorAll('.tc-tab').forEach(t=>{
    t.classList.remove('active');
    t.style.color='var(--tc-text-muted)';
    t.style.borderBottom='none';
  });
  el.classList.add('active');
  el.style.color='var(--tc-primary)';
  el.style.borderBottom='3px solid var(--tc-primary)';
}
</script>
