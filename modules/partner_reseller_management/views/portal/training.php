<?php
defined('BASEPATH') or exit('No direct script access allowed');
ob_start(); ?>
<h1>Training & Certifications</h1>
<div class="prm-row">
  <div class="prm-col-7">
    <h3>Available courses</h3>
    <div class="prm-cards">
      <?php foreach ($courses as $c): ?>
        <div class="prm-course">
          <div class="prm-course-head">
            <strong><?= prm_safe($c->title) ?></strong>
            <span class="prm-pill"><?= prm_safe($c->difficulty) ?></span>
          </div>
          <p class="prm-muted"><?= prm_safe($c->description) ?></p>
          <small><?= (int)$c->duration_min ?> min · pass <?= (int)$c->pass_score ?>%</small>
          <?php if ($c->mandatory): ?> <span class="prm-pill warn">Mandatory</span><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="prm-col-5">
    <h3>Your certifications</h3>
    <ul class="prm-list">
      <?php foreach ($certifications as $cert): ?>
        <li><strong><?= prm_safe($cert->cert_name) ?></strong>
          <small class="prm-muted"><?= prm_safe($cert->cert_code) ?></small><br/>
          Issued <?= prm_safe($cert->issued_at) ?>
          <?php if ($cert->expires_at): ?> · expires <?= prm_safe($cert->expires_at) ?><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
