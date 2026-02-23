<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('estimate_stats'); ?>">
    <div class="row">
        <?php $this->load->view('admin/estimates/estimates_top_stats'); ?>
    </div>
</div>
