<div class="panel_s">
    <div class="panel-body text-center">
        <?php if(isset($widget_meta['drill_down_url'])): ?>
            <a href="<?php echo admin_url($widget_meta['drill_down_url']); ?>" class="no-margin"><h4 class="bold no-margin"><?php echo app_format_money($widget_data, get_base_currency()); ?></h4></a>
        <?php else: ?>
            <h4 class="bold no-margin"><?php echo app_format_money($widget_data, get_base_currency()); ?></h4>
        <?php endif; ?>
        <span class="text-muted">Avg Revenue Per Acc (ARPA)</span>
    </div>
</div>
