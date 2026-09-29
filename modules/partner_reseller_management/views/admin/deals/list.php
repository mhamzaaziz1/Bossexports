<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
  <h4 class="no-margin"><?= _l('prm_deals') ?></h4><hr/>
  <div class="panel panel-default"><div class="panel-body">
    <?php render_datatable([
        _l('prm_deal_number'),
        _l('prm_partner'),
        _l('prm_client'),
        _l('prm_value'),
        _l('prm_stage'),
        _l('prm_score'),
        _l('prm_submitted'),
        '',
    ], 'deals'); ?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
          initDataTable('.table-deals', window.location.href.replace('deals', 'table/deals'), [7], [7]);
      });
    </script>
  </div></div>
</div></div>
<?php init_tail(); ?>
