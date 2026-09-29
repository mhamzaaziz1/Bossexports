<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?=module_dir_url('telecalling_ld','assets/css/tc_style.css')?>">

<div id="wrapper">
  <div class="content tc-content-wrapper">
    <div class="tc-wrap animate-fadeIn">
      
      <div class="tc-header">
        <div class="tc-title-area">
          <div class="tc-icon-box"><i class="fa fa-hand-paper-o"></i></div>
          <div>
            <h1 class="tc-h-main">Lead Requests</h1>
            <p class="tc-h-sub">Agents requesting more leads for their pipeline</p>
          </div>
        </div>
      </div>

      <div class="tc-card">
        <div class="tc-card-title">Pending Requests <small>Awaiting approval</small></div>
        <div class="tc-table-responsive">
          <table class="tc-table">
            <thead><tr><th>Agent</th><th>Requested Count</th><th>Agent Message</th><th>Date</th><th width="150">Action</th></tr></thead>
            <tbody>
              <?php foreach($requests as $r): ?>
              <tr>
                <td>
                  <div style="display:flex; align-items:center; gap:10px;">
                    <div class="tc-avatar-circle" style="background:<?=tc_avatar_color($r->staffid)?>; width:34px; height:34px; font-size:12px;">
                       <?=strtoupper(substr($r->firstname,0,1).substr($r->lastname,0,1))?>
                    </div>
                    <div style="font-weight:700; color:var(--tc-text-main);"><?=htmlspecialchars($r->firstname.' '.$r->lastname)?></div>
                  </div>
                </td>
                <td style="font-weight:800; color:var(--tc-primary); font-size:18px;"><?=$r->count?> leads</td>
                <td style="font-style:italic; color:var(--tc-text-muted); font-size:12px; max-width:200px;"><?=htmlspecialchars($r->message ?: 'No message')?></td>
                <td style="font-size:11px; color:var(--tc-text-muted);"><?=date('d M Y H:i', strtotime($r->created_at))?></td>
                <td>
                   <?php if($r->status === 'pending'): ?>
                     <div style="display:flex; gap:8px;">
                        <a href="<?=admin_url('telecalling_ld/approve_request/'.$r->id)?>" class="tc-btn tc-btn-primary btn-xs" style="padding:6px 12px;"><i class="fa fa-check"></i> Approve</a>
                        <a href="<?=admin_url('telecalling_ld/reject_request/'.$r->id)?>" class="tc-btn tc-btn-outline btn-xs" style="padding:6px 12px; color:var(--tc-danger);"><i class="fa fa-times"></i> Reject</a>
                     </div>
                   <?php else: ?>
                     <span class="tc-pill tc-pill-<?=($r->status==='approved'?'success':'danger')?>"><?=ucwords($r->status)?></span>
                   <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($requests)): ?><tr><td colspan="5" style="text-align:center; padding:50px; color:var(--tc-text-muted);">No lead requests found.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>

<?php init_tail(); ?>
