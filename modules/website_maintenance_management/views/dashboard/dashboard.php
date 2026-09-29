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
                        <div class="_buttons">
                            <a href="<?php
							echo admin_url('website_maintenance_management/maintenance_logs'); ?>" class="btn btn-primary pull-left display-block">
                                <i class="fa-regular fa-plus tw-mr-1"></i>
								<?php
								echo _l('wmm_log_maintenance'); ?>
                            </a>
                            <div class="clearfix"></div>
                        </div>
                        <hr class="hr-panel-heading"/>
                        <h4 class="no-margin">
                            <i class="fa fa-tachometer-alt tw-mr-2"></i>
							<?php
							echo _l('wmm_dashboard'); ?>
                        </h4>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="col-md-3 col-sm-6">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h1 class="bold text-primary mtop5 mbot5">
							<?php
							echo $summary['my_tasks']; ?>
                        </h1>
                        <p class="text-muted mbot5"><?php
							echo _l('wmm_my_active_tasks'); ?></p>
                        <a href="<?php
						echo admin_url('website_maintenance_management/maintenance_tasks'); ?>" class="btn btn-sm btn-default">
							<?php
							echo _l('view_all'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h1 class="bold text-info mtop5 mbot5">
							<?php
							echo $summary['my_logs_this_month']; ?>
                        </h1>
                        <p class="text-muted mbot5"><?php
							echo _l('wmm_my_logs_this_month'); ?></p>
                        <a href="<?php
						echo admin_url('website_maintenance_management/maintenance_logs'); ?>" class="btn btn-sm btn-default">
							<?php
							echo _l('view'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h1 class="bold text-warning mtop5 mbot5">
							<?php
							echo $summary['my_in_progress']; ?>
                        </h1>
                        <p class="text-muted mbot5"><?php
							echo _l('wmm_my_in_progress'); ?></p>
                        <a href="<?php
						echo admin_url('website_maintenance_management/maintenance_logs'); ?>" class="btn btn-sm btn-default">
							<?php
							echo _l('view'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h1 class="bold text-success mtop5 mbot5">
							<?php
							echo number_format($summary['hours_this_week'], 1); ?>h
                        </h1>
                        <p class="text-muted mbot5"><?php
							echo _l('wmm_hours_this_week'); ?></p>
                        <a href="<?php
						echo admin_url('website_maintenance_management/reports'); ?>" class="btn btn-sm btn-default">
							<?php
							echo _l('wmm_view_report'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold"><?php
							echo _l('wmm_quick_links'); ?></h4>
                        <hr class="hr-panel-heading"/>
                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <a href="<?php
								echo admin_url('website_maintenance_management/maintenance_tasks'); ?>" class="btn btn-default btn-block mbot10">
                                    <i class="fa fa-tasks fa-lg"></i><br>
									<?php
									echo _l('wmm_maintenance_tasks'); ?>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="<?php
								echo admin_url('website_maintenance_management/websites'); ?>" class="btn btn-default btn-block mbot10">
                                    <i class="fa fa-globe fa-lg"></i><br>
									<?php
									echo _l('wmm_manage_websites'); ?>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="<?php
								echo admin_url('website_maintenance_management/calendar'); ?>" class="btn btn-default btn-block mbot10">
                                    <i class="fa fa-calendar fa-lg"></i><br>
									<?php
									echo _l('wmm_calendar'); ?>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="<?php
								echo admin_url('website_maintenance_management/maintenance_logs'); ?>" class="btn btn-default btn-block mbot10">
                                    <i class="fa fa-history fa-lg"></i><br>
									<?php
									echo _l('wmm_maintenance_logs'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Tasks -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold">
							<?php
							echo _l('wmm_my_recent_tasks'); ?>
                            <a href="<?php
							echo admin_url('website_maintenance_management/maintenance_tasks'); ?>" class="pull-right">
                                <small><?php
									echo _l('view_all'); ?></small>
                            </a>
                        </h4>
                        <hr class="hr-panel-heading"/>

						<?php
						$CI = &get_instance();
						$CI->db->select('t.*, ta.staffid, c.name as category_name');
						$CI->db->from(db_prefix().'wmm_maintenance_tasks t');
						$CI->db->join(db_prefix().'wmm_task_assigned ta', 'ta.task_id = t.id');
						$CI->db->join(db_prefix().'wmm_categories c', 'c.id = t.category', 'left');
						$CI->db->where('ta.staffid', get_staff_user_id());
						$CI->db->where('t.is_active', 1);
						$CI->db->order_by('t.created_at', 'DESC');
						$CI->db->limit(5);
						$recent_tasks = $CI->db->get()->result_array();
						?>

						<?php
						if (empty($recent_tasks)) { ?>
                            <p class="text-muted text-center mtop15 mbot15">
								<?php
								echo _l('wmm_no_tasks_assigned'); ?>
                            </p>
						<?php
						} else { ?>
                            <div class="list-group">
								<?php
								foreach ($recent_tasks as $task)
								{
									$priority_colors = [
										'low'    => 'default',
										'medium' => 'info',
										'high'   => 'warning',
										'urgent' => 'danger',
									];
									$priority_badge  = isset($priority_colors[$task['priority']]) ? $priority_colors[$task['priority']] : 'default';
									?>
                                    <a href="<?php
									echo admin_url('website_maintenance_management/maintenance_tasks/view/'.$task['id']); ?>" class="list-group-item">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <strong><?php
													echo html_escape($task['name']); ?></strong>
                                                <br>
                                                <small class="text-muted">
													<?php
													echo html_escape($task['category_name'] ?: _l('wmm_uncategorized')); ?>
                                                </small>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                <span class="label label-<?php
                                                echo $priority_badge; ?>">
                                                    <?php
                                                    echo ucfirst($task['priority']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </a>
								<?php
								} ?>
                            </div>
						<?php
						} ?>
                    </div>
                </div>
            </div>

            <!-- Recent Logs -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold">
							<?php
							echo _l('wmm_recent_maintenance_logs'); ?>
                            <a href="<?php
							echo admin_url('website_maintenance_management/maintenance_logs'); ?>" class="pull-right">
                                <small><?php
									echo _l('view_all'); ?></small>
                            </a>
                        </h4>
                        <hr class="hr-panel-heading"/>

						<?php
						$CI->db->select('l.*, w.website_url, p.name as project_name, c.company as client_name');
						$CI->db->from(db_prefix().'wmm_maintenance_logs l');
						$CI->db->join(db_prefix().'wmm_websites w', 'w.id = l.website_id');
						$CI->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
						$CI->db->join(db_prefix().'clients c', 'c.userid = w.client_id', 'left');
						$CI->db->where('l.performed_by', get_staff_user_id());
						$CI->db->order_by('l.performed_at', 'DESC');
						$CI->db->limit(5);
						$recent_logs = $CI->db->get()->result_array();
						?>

						<?php
						if (empty($recent_logs)) { ?>
                            <p class="text-muted text-center mtop15 mbot15">
								<?php
								echo _l('wmm_no_recent_logs'); ?>
                            </p>
						<?php
						} else { ?>
                            <div class="list-group">
								<?php
								foreach ($recent_logs as $log) { ?>
                                    <a href="<?php
									echo admin_url('website_maintenance_management/maintenance_logs/view/'.$log['id']); ?>" class="list-group-item">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <strong><?php
													echo html_escape($log['project_name'] ?: $log['website_url']); ?></strong>
												<?php
												if ($log['client_name']) { ?>
                                                    <br>
                                                    <small class="text-muted"><?php
														echo html_escape($log['client_name']); ?></small>
												<?php
												} ?>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                <small class="text-muted">
                                                    <i class="fa fa-clock"></i>
													<?php
													echo _dt($log['performed_at']); ?>
                                                </small>
                                                <br>
												<?php
												if ($log['is_completed']) { ?>
                                                    <span class="label label-success">
                                                        <i class="fa fa-check"></i> <?php
														echo _l('wmm_completed'); ?>
                                                    </span>
												<?php
												} else { ?>
                                                    <span class="label label-warning">
                                                        <i class="fa fa-clock"></i> <?php
														echo _l('wmm_in_progress'); ?>
                                                    </span>
												<?php
												} ?>
                                            </div>
                                        </div>
                                    </a>
								<?php
								} ?>
                            </div>
						<?php
						} ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
init_tail(); ?>
