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
            <h1 class="tc-h-main">Sales Groups</h1>
            <p class="tc-h-sub">Organize agents into teams for better reporting and distribution</p>
          </div>
        </div>
        <div class="tc-actions">
           <a href="#" class="tc-btn tc-btn-primary" data-toggle="modal" data-target="#new-group"><i class="fa fa-plus"></i> Create Group</a>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4">
          <div class="tc-card">
            <div class="tc-card-title">Existing Groups <small>Team list</small></div>
            <div class="tc-list">
              <?php foreach($groups as $g): ?>
              <a href="<?=admin_url('telecalling_ld/groups/'.$g->id)?>" class="tc-list-item <?=isset($active_group)&&$active_group->id==$g->id?'active':''?>" style="display:flex; align-items:center; gap:12px; text-decoration:none;">
                <div style="width:12px; height:12px; border-radius:50%; background:<?=$g->color?>"></div>
                <div style="flex:1;">
                   <div style="font-weight:700; color:var(--tc-text-main);"><?=htmlspecialchars($g->name)?></div>
                   <div style="font-size:11px; color:var(--tc-text-muted);"><?=$g->member_count?> members</div>
                </div>
                <i class="fa fa-chevron-right" style="font-size:10px; opacity:0.3;"></i>
              </a>
              <?php endforeach; ?>
            </div>
            <style>
               .tc-list-item.active { background: var(--tc-primary-dim); border-color: var(--tc-primary); }
            </style>
          </div>
        </div>

        <div class="col-md-8">
          <?php if(isset($active_group)): ?>
          <div class="tc-card">
             <div class="tc-card-title">Manage Group: <?=htmlspecialchars($active_group->name)?> <small>Group members & settings</small></div>
             
             <form method="post" action="<?=admin_url('telecalling_ld/update_group/'.$active_group->id)?>" style="margin-bottom:30px;">
                <?=form_hidden($this->security->get_csrf_token_name(),$this->security->get_csrf_hash())?>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group"><label>Group Name</label><input type="text" name="name" class="form-control" value="<?=htmlspecialchars($active_group->name)?>"></div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group"><label>Theme Color</label><input type="color" name="color" class="form-control" value="<?=$active_group->color?>" style="height:45px; padding:5px;"></div>
                  </div>
                </div>
                <div class="text-right">
                  <button type="submit" class="tc-btn tc-btn-primary btn-sm"><i class="fa fa-save"></i> Update Settings</button>
                </div>
             </form>

             <hr style="opacity:0.05;">

             <div class="tc-card-title">Group Members <small>Assigned agents</small></div>
             <div class="tc-table-responsive">
                <table class="tc-table">
                  <thead><tr><th>Agent</th><th>Role</th><th>Added</th><th>Action</th></tr></thead>
                  <tbody>
                    <?php foreach($members as $m): ?>
                    <tr>
                      <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                          <div class="tc-avatar-circle" style="background:<?=tc_avatar_color($m->staffid)?>; width:30px; height:30px; font-size:11px;">
                            <?=strtoupper(substr($m->firstname,0,1).substr($m->lastname,0,1))?>
                          </div>
                          <span style="font-weight:600;"><?=htmlspecialchars($m->firstname.' '.$m->lastname)?></span>
                        </div>
                      </td>
                      <td><span class="tc-pill tc-pill-blue"><?=$m->is_leader?'Leader':'Agent'?></span></td>
                      <td style="font-size:11px; color:var(--tc-text-muted);"><?=date('d M Y', strtotime($m->added_at))?></td>
                      <td>
                        <a href="<?=admin_url('telecalling_ld/remove_from_group/'.$active_group->id.'/'.$m->staffid)?>" class="text-danger" onclick="return confirm('Remove agent?')"><i class="fa fa-times"></i></a>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
             </div>
          </div>
          <?php else: ?>
          <div class="tc-card" style="padding:100px; text-align:center; color:var(--tc-text-muted);">
             <i class="fa fa-users" style="font-size:50px; opacity:0.1; display:block; margin-bottom:20px;"></i>
             Select a group from the left to manage members and settings
          </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<?php init_tail(); ?>
