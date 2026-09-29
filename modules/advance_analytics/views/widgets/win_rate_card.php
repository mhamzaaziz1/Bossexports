<div class="panel_s">
    <div class="panel-body text-center">
        <?php if(isset($widget_meta['drill_down_url'])): ?>
            <a href="<?php echo admin_url($widget_meta['drill_down_url']); ?>" class="no-margin"><h4 class="bold no-margin"><?php echo round($widget_data['win_rate'], 1); ?>%</h4></a>
        <?php else: ?>
            <h4 class="bold no-margin"><?php echo round($widget_data['win_rate'], 1); ?>%</h4>
        <?php endif; ?>
        <span class="text-muted">Win Rate (Leads -> Customers)</span>
    </div>
</div>
