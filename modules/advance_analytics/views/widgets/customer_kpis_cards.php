<?php $kpis = $widget_data['retention_kpis']; ?>
 <div class="col-md-3"><div class="panel_s"><div class="panel-body text-center">
     <h4 class="bold no-margin"><?php echo $kpis['total_customers']; ?></h4><span class="text-muted">Total Customers</span>
 </div></div></div>
 <div class="col-md-3"><div class="panel_s"><div class="panel-body text-center">
     <h4 class="bold no-margin"><?php echo round($kpis['retention_rate'], 1); ?>%</h4><span class="text-muted">Retention Rate</span>
 </div></div></div>
 <div class="col-md-3"><div class="panel_s"><div class="panel-body text-center">
     <h4 class="bold no-margin text-danger"><?php echo round($kpis['churn_rate'], 1); ?>%</h4><span class="text-muted">Churn Rate</span>
 </div></div></div>
 <div class="col-md-3"><div class="panel_s"><div class="panel-body text-center">
     <h4 class="bold no-margin text-success"><?php echo app_format_money($widget_data['clv_stats']['avg_clv'], get_base_currency()); ?></h4><span class="text-muted">Avg Lifetime Value (CLV)</span>
 </div></div></div>
