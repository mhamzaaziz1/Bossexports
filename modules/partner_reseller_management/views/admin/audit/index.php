<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
  <h4 class="no-margin"><?= _l('prm_audit_log') ?></h4>
  <p class="text-muted"><?= _l('prm_audit_subtitle') ?></p><hr/>
  <div class="panel panel-default"><div class="panel-body">
    <?php render_datatable([
        _l('prm_when'),
        _l('prm_actor'),
        _l('prm_action'),
        _l('prm_entity'),
        _l('prm_severity'),
        _l('prm_ip'),
    ], 'audit'); ?>
  </div></div>
</div></div>
<?php init_tail(); ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
      initDataTable('.table-audit', window.location.href.replace('audit', 'table/audit'), [], []);
  });
</script>
