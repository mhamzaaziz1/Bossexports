<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="row">
                    <div class="col-md-12">
                        <div class="panel_s">
                            <div class="panel-body">

                                <div class="tw-flex tw-justify-between tw-items-center no-margin">
                                    <p class="tw-font-semibold tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse">
                                        <?php if (isset($webhook)): ?>
                                            <a class="text-dark" href="<?php echo admin_url(N8N_CONNECTOR_MODULE . '/webhook/' . $webhook->id); ?>">
                                                <?php echo htmlspecialchars($webhook->name); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted"><?php echo _l('n8n_webhook_deleted'); ?></span>
                                        <?php endif; ?>
                                    </p>
                                    <div>
                                        <?php if ($log->status == 'success'): ?>
                                            <span class="label label-success"><?php echo _l('n8n_status_success'); ?></span>
                                        <?php elseif ($log->status == 'pending'): ?>
                                            <span class="label label-info"><?php echo _l('n8n_status_pending'); ?></span>
                                        <?php elseif ($log->status == 'retry'): ?>
                                            <span class="label label-warning"><?php echo _l('n8n_status_retry'); ?></span>
                                        <?php else: ?>
                                            <span class="label label-danger"><?php echo _l('n8n_status_failed'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <hr class="hr-panel-heading">

                                <!-- Statistics Cards -->
                                <div class="row">
                                    <!-- Total Webhooks -->
                                    <div class="col-md-3 col-xs-6 border-right">
                                        <p class="bold tw-flex tw-flex-col"><?php echo _l('n8n_event_type'); ?><span class="text-muted font-size-14"><?php
                                                                                                                                                                                $event = n8n_get_event($log->event_type);
                                                                                                                                                                                echo $event ? htmlspecialchars($event['label']) : htmlspecialchars($log->event_type);
                                                                                                                                                                                ?></span></p>

                                    </div>

                                    <!-- Active Webhooks -->
                                    <div class="col-md-3 col-xs-6 border-right">
                                        <p class="bold tw-flex tw-flex-col"><?php echo _l('n8n_duration'); ?><span class="text-muted font-size-14"><?php echo $log->response_time_ms ?? 0; ?> ms</span></p>

                                    </div>

                                    <!-- Total Logs -->
                                    <div class="col-md-3 col-xs-6 border-right">
                                        <p class="bold tw-flex tw-flex-col"><?php echo _l('n8n_attempts'); ?><span class="text-muted font-size-14"><?php if ($log->attempt_number > 1): ?>
                                                    <span class="text-muted font-size-14"><?php echo $log->attempt_number; ?></span>
                                                <?php else: ?>
                                                    <?php echo $log->attempt_number; ?>
                                                <?php endif; ?></span></p>
                                    </div>

                                    <!-- Success Rate -->
                                    <div class="col-md-3 col-xs-6">
                                        <p class="bold tw-flex tw-flex-col"><?php echo _l('created_at'); ?><span class="text-muted font-size-14"><?php echo _dt($log->triggered_at); ?></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Response card -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel_s">
                            <div class="panel-body">
                                <div class="tw-flex tw-justify-between tw-items-center">
                                    <p class="tw-font-semibold tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse">
                                        <span class="tw-text-neutral-700">
                                            <?php echo _l('webhook_response'); ?>
                                        </span>
                                    </p>
                                </div>
                                <hr class="hr-panel-heading">
                                <div class="row">
                                    <?php if (!empty($log->error_message)): ?>
                                        <div class="col-md-12">
                                            <div class="alert alert-danger">
                                                <h4><i class="fa fa-exclamation-triangle"></i> <?php echo _l('n8n_error_message'); ?></h4>
                                                <?php echo htmlspecialchars($log->error_message); ?>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Payload -->
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4><?php echo _l('n8n_payload'); ?></h4>
                                                <pre class="code-block"><code class="language-json"><?php
                                                                                                    echo htmlspecialchars(json_encode($log->payload, JSON_PRETTY_PRINT));
                                                                                                    ?></code></pre>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Response -->
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4><?php echo _l('n8n_response'); ?></h4>
                                                <pre class="code-block"><?php echo is_array($log->response_body) ? htmlspecialchars(json_encode($log->response_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $log->response_body; ?></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php init_tail(); ?>

        <style>
            .code-block {
                background: #f5f5f5;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 15px;
                max-height: 400px;
                overflow-y: auto;
                font-size: 12px;
                line-height: 1.5;
            }
        </style>

        </body>

        </html>