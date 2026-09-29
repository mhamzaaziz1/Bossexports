<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?=module_dir_url('telecalling_ld','assets/css/tc_style.css')?>">

<div id="wrapper">
  <div class="content tc-content-wrapper">
    <div class="tc-wrap animate-fadeIn">
      
      <div class="tc-header">
        <div class="tc-title-area">
          <div class="tc-icon-box"><i class="fa fa-envelope"></i></div>
          <div>
            <h1 class="tc-h-main">Email Campaigns</h1>
            <p class="tc-h-sub">Manage automated sequences and email templates</p>
          </div>
        </div>
        <div class="tc-actions">
           <a href="<?=admin_url('telecalling_ld/settings')?>" class="tc-btn tc-btn-outline"><i class="fa fa-cog"></i> Email Settings</a>
           <a href="#" class="tc-btn tc-btn-primary" data-toggle="modal" data-target="#new-campaign"><i class="fa fa-plus"></i> New Campaign</a>
        </div>
      </div>

      <div class="row">
        <!-- Campaign List -->
        <div class="col-md-5">
          <div class="tc-card">
            <div class="tc-card-title">Active Campaigns <small>Automation sequences</small></div>
            <div class="tc-list">
              <?php foreach($campaigns as $c): ?>
              <div class="tc-list-item" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <div style="font-weight:700; color:var(--tc-text-main);"><?=htmlspecialchars($c->name)?></div>
                  <div style="font-size:11px; color:var(--tc-text-muted);"><?=count($this->tc->get_templates($c->id))?> templates in sequence</div>
                </div>
                <div style="display:flex; gap:5px;">
                   <a href="<?=admin_url('telecalling_ld/campaigns/'.$c->id)?>" class="tc-btn-action tc-btn-primary btn-xs"><i class="fa fa-edit"></i></a>
                   <a href="<?=admin_url('telecalling_ld/delete_campaign/'.$c->id)?>" class="tc-btn-action tc-btn-outline btn-xs text-danger" onclick="return confirm('Delete campaign?')"><i class="fa fa-trash"></i></a>
                </div>
              </div>
              <?php endforeach; ?>
              <?php if(empty($campaigns)): ?><p class="text-center text-muted" style="padding:20px;">No campaigns created yet.</p><?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Templates for selected campaign -->
        <div class="col-md-7">
          <div class="tc-card">
             <div class="tc-card-title">Sequence Templates <small>Step-by-step emails</small></div>
             <?php if(isset($active_campaign)): ?>
                <div class="text-right" style="margin-bottom:15px;">
                   <button class="tc-btn tc-btn-primary btn-sm" data-toggle="modal" data-target="#new-template"><i class="fa fa-plus"></i> Add Template</button>
                </div>
                <div class="tc-table-responsive">
                  <table class="tc-table">
                    <thead><tr><th>Order</th><th>Subject</th><th>Condition</th><th>Action</th></tr></thead>
                    <tbody>
                      <?php foreach($templates as $t): ?>
                      <tr>
                        <td>Day <?=$t->send_after_days?></td>
                        <td style="font-weight:600;"><?=htmlspecialchars($t->subject)?></td>
                        <td><span class="tc-pill tc-pill-blue">After <?=$t->send_after_days?> days</span></td>
                        <td>
                          <a href="<?=admin_url('telecalling_ld/edit_template/'.$t->id)?>" class="tc-btn-action tc-btn-outline btn-xs"><i class="fa fa-pencil"></i></a>
                        </td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
             <?php else: ?>
                <div class="text-center" style="padding:80px; color:var(--tc-text-muted);">
                   <i class="fa fa-mouse-pointer" style="font-size:40px; opacity:0.1; display:block; margin-bottom:15px;"></i>
                   Select a campaign to manage its templates
                </div>
             <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php init_tail(); ?>
