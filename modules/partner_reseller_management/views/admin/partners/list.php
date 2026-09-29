<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
  <div class="row">
    <div class="col-md-12">
      <a href="<?= admin_url('partner_reseller_management/partners/manage') ?>" class="btn btn-info pull-right">
        <i class="fa fa-plus"></i> <?= _l('prm_new_partner'); ?>
      </a>
      <h4 class="no-margin"><?= _l('prm_partners'); ?></h4><hr/>
      <div class="panel panel-default">
        <div class="panel-body">
          <?php render_datatable([
              _l('prm_code'),
              _l('prm_company'),
              _l('prm_type'),
              _l('prm_email'),
              _l('prm_revenue'),
              _l('prm_status'),
              '',
          ], 'partners'); ?>
          <script>
            document.addEventListener('DOMContentLoaded', function() {
                initDataTable('.table-partners', window.location.href.replace('partners', 'table/partners'), [6], [6]);
            });
          </script>
        </div>
      </div>
    </div>
  </div>
</div></div>
<?php init_tail(); ?>
