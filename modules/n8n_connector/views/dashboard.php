<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="tw-flex tw-justify-between tw-items-center no-margin">
                            <p class="tw-font-semibold tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse">
                                <i class="fa fa-plug fa-lg tw-text-neutral-500"></i>
                                <span class="tw-text-neutral-700">
                                    <?php echo _l('n8n_connector_dashboard'); ?>
                                </span>
                            </p>
                            <div>
                                <?php if (staff_can('create', 'n8n_connector')): ?>
                                    <a href="<?php echo admin_url(N8N_CONNECTOR_MODULE . '/webhook'); ?>"
                                        class="btn btn-primary">
                                        <i class="fa fa-plus"></i> <?php echo _l('n8n_add_webhook'); ?>
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo 'https://help.corbitaltech.dev/hc/categories/12/perfex-crm-to-n8n'; ?>" target="_blank"
                                    class="btn btn-info">
                                    <i class="fa fa-book"></i> <?php echo _l('n8n_documentation'); ?>
                                </a>
                                <a href="<?php echo admin_url(N8N_CONNECTOR_MODULE . '/settings'); ?>"
                                    class="btn btn-default">
                                    <i class="fa fa-cog"></i> <?php echo _l('settings'); ?>
                                </a>
                            </div>
                        </div>
                        <hr class="hr-panel-heading">

                        <!-- Statistics Cards -->
                        <div class="row">
                            <!-- Total Webhooks -->
                            <div class="col-md-3 col-xs-6 border-right">
                                <h3 class="bold tw-flex tw-items-center"><?php echo $total_webhooks; ?><span class="text-muted font-size-14 text-success tw-ml-2"><?php echo _l('n8n_total_webhooks'); ?></span></h3>

                            </div>

                            <!-- Active Webhooks -->
                            <div class="col-md-3 col-xs-6 border-right">
                                <h3 class="bold tw-flex tw-items-center"><?php echo $active_webhooks; ?><span class="text-muted font-size-14 text-info tw-ml-2"><?php echo _l('n8n_active_webhooks'); ?></span></h3>

                            </div>

                            <!-- Total Logs -->
                            <div class="col-md-3 col-xs-6 border-right">
                                <h3 class="bold tw-flex tw-items-center"><?php echo $total_logs; ?><span class="text-muted font-size-14 text-success tw-ml-2"><?php echo _l('n8n_total_logs'); ?></span></h3>
                            </div>

                            <!-- Success Rate -->
                            <div class="col-md-3 col-xs-6">
                                <h3 class="bold tw-flex tw-items-center"><?php echo number_format($success_rate, 1); ?>%<span class="text-muted font-size-14 text-success tw-ml-2"><?php echo _l('n8n_success_rate'); ?></span></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Webhooks -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <p class="tw-font-semibold tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse">
                                <i class="fa-regular fa-clock fa-lg tw-text-neutral-500"></i>
                                <span class="tw-text-neutral-700">
                                    <?php echo _l('n8n_recent_webhooks'); ?>
                                </span>
                            </p>
                            <div>
                                <a href="<?php echo admin_url(N8N_CONNECTOR_MODULE . '/webhooks'); ?>"
                                    class="pull-right">
                                    <?php echo _l('view_all'); ?>
                                </a>
                            </div>
                        </div>
                        <hr class="hr-panel-heading">
                        <div class="panel-table-full">
                            <?php if (!empty($recent_webhooks)): ?>
                                <table class="table dt-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo _l('name'); ?></th>
                                            <th><?php echo _l('n8n_status'); ?></th>
                                            <th><?php echo _l('n8n_events'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_webhooks as $webhook): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?php echo admin_url(N8N_CONNECTOR_MODULE . '/webhook/' . $webhook['id']); ?>">
                                                        <?php echo htmlspecialchars($webhook['name']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php if ($webhook['is_active']): ?>
                                                        <span class="label label-success"><?php echo _l('n8n_active'); ?></span>
                                                    <?php else: ?>
                                                        <span class="label label-danger"><?php echo _l('n8n_inactive'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $events = !empty($webhook['enabled_events']) ? json_decode($webhook['enabled_events'], true) : [];
                                                    echo is_array($events) ? count($events) : 0;
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-muted text-center mtop20">
                                    <?php echo _l('n8n_no_webhooks_yet'); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Logs -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <p class="tw-font-semibold tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse">
                                <i class="fa-regular fa-clock fa-lg tw-text-neutral-500"></i>
                                <span class="tw-text-neutral-700">
                                    <?php echo _l('n8n_recent_logs'); ?>
                                </span>
                            </p>
                            <div>
                                <a href="<?php echo admin_url(N8N_CONNECTOR_MODULE . '/logs'); ?>"
                                    class="pull-right">
                                    <?php echo _l('view_all'); ?>
                                </a>
                            </div>
                        </div>
                        <hr class="hr-panel-heading">
                        <div class="panel-table-full">
                            <?php if (!empty($recent_logs)): ?>
                                <table class="table dt-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo _l('n8n_event'); ?></th>
                                            <th><?php echo _l('n8n_status'); ?></th>
                                            <th><?php echo _l('n8n_time'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_logs as $log): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?php echo admin_url(N8N_CONNECTOR_MODULE . '/log/' . $log['id']); ?>">
                                                        <?php
                                                        $event = n8n_get_event($log['event_type']);
                                                        echo $event ? htmlspecialchars($event['label']) : $log['event_type'];
                                                        ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($log['status'] == 'success') {
                                                        $status_badge = '<span class="label label-success">' . _l('n8n_status_success') . '</span>';
                                                    } elseif ($log['status'] == 'pending') {
                                                        $status_badge = '<span class="label label-info">' . _l('n8n_status_pending') . '</span>';
                                                    } elseif ($log['status'] == 'retry') {
                                                        $status_badge = '<span class="label label-warning">' . _l('n8n_status_retry') . '</span>';
                                                    } else {
                                                        $status_badge = '<span class="label label-danger">' . _l('n8n_status_failed') . '</span>';
                                                    }
                                                    echo $status_badge;
                                                    ?>
                                                </td>

                                                <td>
                                                    <span class="text-muted"><?php echo time_ago($log['triggered_at']); ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-muted text-center mtop20">
                                    <?php echo _l('n8n_no_logs_yet'); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>