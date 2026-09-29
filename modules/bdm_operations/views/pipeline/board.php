<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .kanban-wrap { display:flex; gap:12px; overflow-x:auto; padding-bottom:10px; }
    .kanban-col  { flex: 0 0 240px; background:#f5f6fa; border-radius:8px; padding:10px; min-height:560px; }
    .kanban-col h5 { margin:0 0 10px; font-weight:700; color:#333; font-size:13px; text-transform:uppercase; letter-spacing:.4px; }
    .kanban-col .badge { background:#fff; color:#333; }
    .kanban-card { background:#fff; border-radius:6px; padding:10px 12px; margin-bottom:8px; box-shadow:0 1px 2px rgba(0,0,0,.07); border-left:4px solid #1565c0; cursor:grab; }
    .kanban-card:active { cursor:grabbing; }
    .kanban-card .company { font-weight:600; font-size:13px; color:#222; }
    .kanban-card .meta    { font-size:11px; color:#777; margin-top:4px; }
    .kanban-card .value   { font-size:12px; color:#1b5e20; font-weight:600; margin-top:4px; }
    .ks-1 { border-left-color:#90caf9 !important; }
    .ks-2 { border-left-color:#42a5f5 !important; }
    .ks-3 { border-left-color:#1e88e5 !important; }
    .ks-4 { border-left-color:#7e57c2 !important; }
    .ks-5 { border-left-color:#5e35b2 !important; }
    .ks-6 { border-left-color:#fb8c00 !important; }
    .ks-7 { border-left-color:#43a047 !important; }
    .ks-8 { border-left-color:#2e7d32 !important; }
    .ks-9 { border-left-color:#00897b !important; }
    .ks-10{ border-left-color:#ad1457 !important; }
</style>
<div id="wrapper">
  <div class="content">
    <h4 class="no-mtop"><i class="fa fa-columns"></i> <?php echo _l('bdm_pipeline'); ?> — 10-Step BDM Workflow</h4>
    <p class="text-muted small">Drag cards across columns to move a registration along the pipeline. Updates persist instantly.</p>
    <hr class="hr-panel-heading" />

    <div class="kanban-wrap" id="kanban">
      <?php foreach ($pipeline_step_map as $step => $label):
          $cards = array_filter($registrations, function($r) use ($step) { return (int) $r['pipeline_step'] === (int) $step; });
      ?>
      <div class="kanban-col" data-step="<?php echo $step; ?>">
        <h5>
          Step <?php echo $step; ?>
          <span class="badge pull-right"><?php echo count($cards); ?></span>
          <br><small class="text-muted" style="font-weight:500; text-transform:none; letter-spacing:0;"><?php echo $label; ?></small>
        </h5>
        <div class="kanban-list">
          <?php foreach ($cards as $r):
              $c       = $companies_index[$r['company_id']] ?? null;
              $company = $c['company_name'] ?? 'N/A';
          ?>
          <div class="kanban-card ks-<?php echo $step; ?>" data-id="<?php echo $r['id']; ?>" draggable="true">
            <div class="company"><?php echo htmlentities($company); ?></div>
            <div class="meta">
              <i class="fa fa-user-o"></i> <?php echo get_staff_full_name($r['assigned_bdm']); ?>
              <?php if (!empty($r['segment'])): ?> · <?php echo htmlentities($r['segment']); ?><?php endif; ?>
              <?php if (!empty($r['emirate'] ?? null)): ?> · <?php echo htmlentities($r['emirate']); ?><?php endif; ?>
            </div>
            <div class="meta">
              <i class="fa fa-tag"></i> <?php echo htmlentities($r['registration_type']); ?>
              <?php if (!empty($r['priority'])): ?>
                · <span class="label label-<?php echo strtolower($r['priority']) === 'high' ? 'danger' : (strtolower($r['priority']) === 'medium' ? 'warning' : 'default'); ?>"
                       style="font-size:9px;"><?php echo $r['priority']; ?></span>
              <?php endif; ?>
            </div>
            <?php if ((float) ($r['expected_value'] ?? 0) > 0): ?>
              <div class="value">AED <?php echo number_format((float) $r['expected_value'], 0); ?></div>
            <?php endif; ?>
            <div class="meta text-right">
              <a href="<?php echo admin_url('bdm_operations/vr_registrations'); ?>" class="text-muted"><i class="fa fa-pencil"></i></a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<?php init_tail(); ?>
<script>
$(function () {
    var dragged = null;
    var endpoint = '<?php echo admin_url('bdm_operations/update_vr_step'); ?>';
    var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';

    document.querySelectorAll('.kanban-card').forEach(function(card){
        card.addEventListener('dragstart', function(e){ dragged = card; e.dataTransfer.effectAllowed = 'move'; });
        card.addEventListener('dragend',   function(){ dragged = null; });
    });

    document.querySelectorAll('.kanban-col').forEach(function(col){
        col.addEventListener('dragover', function(e){ e.preventDefault(); });
        col.addEventListener('drop', function(e){
            e.preventDefault();
            if (!dragged) return;
            var targetStep = col.getAttribute('data-step');
            var id         = dragged.getAttribute('data-id');
            col.querySelector('.kanban-list').appendChild(dragged);

            // Adjust card colour-class to new step
            dragged.className = dragged.className.replace(/ks-\d+/, 'ks-' + targetStep);

            $.post(endpoint, { id: id, step: targetStep, [csrfName]: csrfHash }, function(resp){
                try { resp = (typeof resp === 'string') ? JSON.parse(resp) : resp; } catch(e){}
                if (resp && resp.success) {
                    alert_float('success', 'Pipeline updated → Step ' + targetStep);
                } else {
                    alert_float('warning', 'Could not update — refresh and try again.');
                }
            });
        });
    });
});
</script>
