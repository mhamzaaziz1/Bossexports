<div class="panel_s">
    <div class="panel-body text-center">
        <?php if(isset($widget_meta['drill_down_url'])): ?>
            <a href="<?php echo admin_url($widget_meta['drill_down_url']); ?>" class="no-margin"><h4 class="bold no-margin"><?php echo app_format_money($widget_data['velocity'], get_base_currency()); ?></h4></a>
        <?php else: ?>
            <h4 class="bold no-margin"><?php echo app_format_money($widget_data['velocity'], get_base_currency()); ?></h4>
        <?php endif; ?>
        <span class="text-muted">Sales Velocity (Last 30 Days)</span>
    </div>
</div>
