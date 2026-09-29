<?php
defined('BASEPATH') or exit('No direct script access allowed');
ob_start(); ?>
<h1>Welcome back, <?= prm_safe($partner->company_name) ?> 👋</h1>
<p class="prm-muted">Tier: <?= prm_safe(($partner->tier_level_id ? 'Active' : 'Bronze')) ?> · Code: <?= prm_safe($partner->partner_code) ?></p>

<div class="prm-grid">
  <div class="prm-card">
    <div class="prm-card-label">Wallet balance</div>
    <div class="prm-card-value"><?= prm_format_money($stats['wallet']['balance'] ?? 0, $partner->currency) ?></div>
    <div class="prm-card-sub">+<?= prm_format_money($stats['wallet']['pending'] ?? 0, $partner->currency) ?> pending</div>
  </div>
  <div class="prm-card">
    <div class="prm-card-label">Total commission earned</div>
    <div class="prm-card-value"><?= prm_format_money($stats['commission_total'], $partner->currency) ?></div>
  </div>
  <div class="prm-card">
    <div class="prm-card-label">Lifetime score</div>
    <div class="prm-card-value"><?= (int)$partner->lifetime_score ?> pts</div>
    <div class="prm-card-sub">SLA <?= (float)$partner->sla_score ?>%</div>
  </div>
</div>

<div class="prm-row">
  <div class="prm-col-7">
    <h3>Pipeline</h3>
    <div class="prm-pipeline">
      <?php foreach ($stats['pipeline'] as $stage => $r): ?>
        <div class="prm-stage-card">
          <div class="prm-stage-name"><?= prm_safe(ucfirst(str_replace('_',' ',$stage))) ?></div>
          <div class="prm-stage-num"><?= (int)$r['count'] ?></div>
          <div class="prm-stage-val"><?= prm_format_money($r['value']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <a href="<?= site_url('partner-portal/deals/new') ?>" class="prm-btn primary"><i class="fa fa-plus"></i> Register a deal</a>
  </div>

  <div class="prm-col-5">
    <h3>Recent deals</h3>
    <ul class="prm-list">
      <?php foreach (array_slice($recent_deals, 0, 8) as $d): ?>
        <li>
          <a href="<?= site_url('partner-portal/deals/'.$d->id) ?>">
            <strong><?= prm_safe($d->deal_number) ?></strong> — <?= prm_safe($d->client_name) ?>
          </a>
          <span class="prm-pill"><?= prm_safe($d->pipeline_stage) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
