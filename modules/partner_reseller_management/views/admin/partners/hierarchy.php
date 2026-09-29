<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
  <h4 class="no-margin"><?= _l('prm_hierarchy') ?></h4><hr/>
  <div class="panel panel-default"><div class="panel-body">
    <?php
    $byParent = [];
    foreach ($partners as $p) { $byParent[(int)$p->parent_partner_id][] = $p; }
    function render_node($p, $byParent) {
        echo '<li><a href="'.admin_url('partner_reseller_management/partners/manage/'.$p->id).'">'.htmlspecialchars($p->company_name).' <small class="text-muted">'.htmlspecialchars($p->partner_code).'</small></a>';
        if (!empty($byParent[$p->id])) {
            echo '<ul>';
            foreach ($byParent[$p->id] as $child) render_node($child, $byParent);
            echo '</ul>';
        }
        echo '</li>';
    }
    ?>
    <ul class="prm-tree">
      <?php if (!empty($byParent[0])) foreach ($byParent[0] as $root) render_node($root, $byParent); ?>
    </ul>
  </div></div>
</div></div>
<?php init_tail(); ?>
