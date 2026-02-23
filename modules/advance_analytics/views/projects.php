<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
          <div class="row">
            <div class="col-md-12">
                <h4 class="no-margin"><?php echo _l('analytics_projects'); ?></h4>
                <hr class="hr-panel-heading" />
            </div>

            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('project_status'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="project_status_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('task_total_logged_time'); ?> (Top 10 Staff)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="logged_hours_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function(){
        // Project Status
        new Chart($('#project_status_chart'), {
            type: 'doughnut',
            data: <?php echo $project_status_stats; ?>,
            options: { maintainAspectRatio: false }
        });

        // Logged Hours
        new Chart($('#logged_hours_chart'), {
            type: 'bar',
            data: <?php echo $logged_hours_stats; ?>,
            options: { maintainAspectRatio: false, scales: { yAxes: [{ ticks: { beginAtZero: true } }] } }
        });
    });
</script>
