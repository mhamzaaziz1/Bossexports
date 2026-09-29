<?php
defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <i class="fa fa-chart-line tw-mr-2"></i>
							<?php
							echo _l('wmm_reports_analytics'); ?>
                        </h4>
                        <hr class="hr-panel-heading"/>

                        <!-- Filters -->
                        <div class="row">
                            <div class="col-md-4">
								<?php
								echo render_date_input('date_from', 'wmm_date_from', $filters['date_from']); ?>
                            </div>
                            <div class="col-md-4">
								<?php
								echo render_date_input('date_to', 'wmm_date_to', $filters['date_to']); ?>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mtop25">
                                    <button type="button" class="btn btn-primary" id="apply-filters">
                                        <i class="fa fa-filter"></i> <?php
										echo _l('apply_filter'); ?>
                                    </button>
                                    <a href="<?php
									echo admin_url('website_maintenance_management/reports/export_time_tracking'); ?>" class="btn btn-success mleft10">
                                        <i class="fa fa-download"></i> <?php
										echo _l('export_excel'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-muted"><?php
							echo _l('wmm_total_tasks'); ?></h3>
                        <h1 class="bold"><?php
							echo $task_stats['total_tasks']; ?></h1>
                        <p class="text-muted">
                            <span class="text-success"><?php
								echo $task_stats['active_tasks']; ?><?php
								echo _l('wmm_active'); ?></span> |
                            <span class="text-muted"><?php
								echo $task_stats['inactive_tasks']; ?><?php
								echo _l('wmm_inactive'); ?></span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-muted"><?php
							echo _l('wmm_total_time'); ?></h3>
                        <h1 class="bold"><?php
							echo number_format($time_stats['total_hours'], 2); ?>h</h1>
                        <p class="text-muted"><?php
							echo $time_stats['total_entries']; ?><?php
							echo _l('wmm_completed_logs'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-muted"><?php
							echo _l('wmm_maintenance_logs'); ?></h3>
                        <h1 class="bold"><?php
							echo $maintenance_stats['total_logs']; ?></h1>
                        <p class="text-muted">
							<?php
							echo $maintenance_stats['websites_maintained']; ?><?php
							echo _l('wmm_websites'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <!-- Task Completion Trend -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4><?php
							echo _l('wmm_completion_trend'); ?></h4>
                        <canvas id="completionTrendChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Time Logged Trend -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4><?php
							echo _l('wmm_time_logged_trend'); ?></h4>
                        <canvas id="timeLoggedChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Tasks by Category -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4><?php
							echo _l('wmm_tasks_by_category'); ?></h4>
                        <canvas id="categoryChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tasks by Priority -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4><?php
							echo _l('wmm_tasks_by_priority'); ?></h4>
                        <canvas id="priorityChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Productivity Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4><?php
							echo _l('wmm_staff_productivity'); ?></h4>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th><?php
									echo _l('staff_member'); ?></th>
                                <th><?php
									echo _l('wmm_maintenance_count'); ?></th>
                                <th><?php
									echo _l('wmm_total_hours'); ?></th>
                                <th><?php
									echo _l('wmm_avg_time_per_log'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							foreach ($staff_productivity as $staff) { ?>
                                <tr>
                                    <td><?php
										echo html_escape($staff['full_name']); ?></td>
                                    <td><?php
										echo $staff['maintenance_count']; ?></td>
                                    <td><?php
										echo number_format($staff['total_hours'] ?? 0, 2); ?>h
                                    </td>
                                    <td><?php
										echo number_format($staff['avg_time_per_log'] ?? 0, 2); ?>h
                                    </td>
                                </tr>
							<?php
							} ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Staff & Most Maintained Websites -->
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4><?php
							echo _l('wmm_top_performers'); ?></h4>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th><?php
									echo _l('staff_member'); ?></th>
                                <th><?php
									echo _l('wmm_maintenance_count'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							foreach ($top_staff as $staff) { ?>
                                <tr>
                                    <td><?php
										echo html_escape($staff['full_name']); ?></td>
                                    <td><span class="badge"><?php
											echo $staff['maintenance_count']; ?></span></td>
                                </tr>
							<?php
							} ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4><?php
							echo _l('wmm_most_maintained_websites'); ?></h4>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th><?php
									echo _l('wmm_website'); ?></th>
                                <th><?php
									echo _l('wmm_maintenance_count'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							foreach ($most_maintained as $website) { ?>
                                <tr>
                                    <td>
                                        <strong><?php
											echo html_escape($website['client_name']); ?></strong><br>
                                        <small><?php
											echo html_escape($website['project_name']); ?></small>
                                    </td>
                                    <td><span class="badge"><?php
											echo $website['maintenance_count']; ?></span></td>
                                </tr>
							<?php
							} ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
init_tail(); ?>

<script src="<?php
echo base_url('assets/plugins/chart.js/chart.min.js'); ?>"></script>
<script>
    $(function () {
        // Apply filters
        $('#apply-filters').on('click', function () {
            var dateFrom = $('input[name="date_from"]').val();
            var dateTo = $('input[name="date_to"]').val();
            window.location.href = admin_url + 'website_maintenance_management/reports?date_from=' + dateFrom + '&date_to=' + dateTo;
        });

        // Completion Trend Chart
        var completionCtx = document.getElementById('completionTrendChart').getContext('2d');
        var completionData = <?php echo json_encode($completion_trend); ?>;
        new Chart(completionCtx, {
            type: 'line',
            data: {
                labels: completionData.map(d => d.month_short),
                datasets: [{
                    label: '<?php echo _l('wmm_tasks_completed'); ?>',
                    data: completionData.map(d => d.count),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Time Logged Chart
        var timeCtx = document.getElementById('timeLoggedChart').getContext('2d');
        var timeData = <?php echo json_encode($time_trend); ?>;
        new Chart(timeCtx, {
            type: 'bar',
            data: {
                labels: timeData.map(d => d.week),
                datasets: [{
                    label: '<?php echo _l('wmm_hours'); ?>',
                    data: timeData.map(d => d.hours),
                    backgroundColor: '#22c55e'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Category Chart
        var categoryCtx = document.getElementById('categoryChart').getContext('2d');
        var categoryData = <?php echo json_encode($tasks_by_category); ?>;
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryData.map(d => d.name),
                datasets: [{
                    data: categoryData.map(d => d.count),
                    backgroundColor: ['#3b82f6', '#f59e0b', '#22c55e', '#64748b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });

        // Priority Chart
        var priorityCtx = document.getElementById('priorityChart').getContext('2d');
        var priorityData = <?php echo json_encode($tasks_by_priority); ?>;
        new Chart(priorityCtx, {
            type: 'pie',
            data: {
                labels: priorityData.map(d => d.priority),
                datasets: [{
                    data: priorityData.map(d => d.count),
                    backgroundColor: ['#64748b', '#3b82f6', '#f59e0b', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });
    });
</script>
