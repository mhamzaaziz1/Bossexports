<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?= prm_safe($title ?? 'Partner Portal') ?> · <?= prm_safe(get_option('companyname')) ?></title>
<link rel="stylesheet" href="<?= base_url('assets/plugins/bootstrap/css/bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/font-awesome/css/font-awesome.min.css') ?>">
<link rel="stylesheet" href="<?= module_dir_url('partner_reseller_management', 'assets/css/portal.css') ?>?v=<?= PRM_MODULE_VERSION ?>">
<style>
  :root { --prm-primary: <?= prm_safe(get_prm_option('prm_portal_brand_color','#2C3E50')) ?>; }
</style>
</head>
<body class="prm-portal">
<?php $partner = is_partner_authenticated() ? prm_current_partner() : null; ?>
<nav class="prm-nav">
  <div class="prm-nav-inner">
    <a href="<?= site_url('partner-portal/dashboard') ?>" class="prm-brand">
      <?php if ($logo = get_prm_option('prm_portal_branding_logo','')): ?>
        <img src="<?= prm_safe($logo) ?>" alt="logo">
      <?php else: ?>
        <i class="fa fa-handshake-o"></i> <?= prm_safe(get_option('companyname')) ?> Partner Portal
      <?php endif; ?>
    </a>
    <?php if ($partner): ?>
    <ul class="prm-nav-links">
      <li><a href="<?= site_url('partner-portal/dashboard') ?>"><i class="fa fa-tachometer"></i> Dashboard</a></li>
      <li><a href="<?= site_url('partner-portal/deals') ?>"><i class="fa fa-briefcase"></i> Deals</a></li>
      <li><a href="<?= site_url('partner-portal/commissions') ?>"><i class="fa fa-money"></i> Commissions</a></li>
      <li><a href="<?= site_url('partner-portal/wallet') ?>"><i class="fa fa-credit-card"></i> Wallet</a></li>
      <li><a href="<?= site_url('partner-portal/training') ?>"><i class="fa fa-graduation-cap"></i> Training</a></li>
      <li><a href="<?= site_url('partner-portal/profile') ?>"><i class="fa fa-user"></i> Profile</a></li>
      <li><a href="<?= site_url('partner-portal/logout') ?>"><i class="fa fa-sign-out"></i> Logout</a></li>
    </ul>
    <?php endif; ?>
  </div>
</nav>
<div class="prm-shell">
  <?php if ($msg = $this->session->flashdata('success')): ?>
    <div class="prm-alert success"><?= prm_safe($msg) ?></div>
  <?php endif; ?>
  <?php if ($msg = $this->session->flashdata('error')): ?>
    <div class="prm-alert error"><?= prm_safe($msg) ?></div>
  <?php endif; ?>
  <?= $content ?? '' ?>
</div>
<script src="<?= base_url('assets/plugins/jquery/jquery.min.js') ?>"></script>
<script src="<?= module_dir_url('partner_reseller_management', 'assets/js/portal.js') ?>?v=<?= PRM_MODULE_VERSION ?>"></script>
</body>
</html>
