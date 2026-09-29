<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix().'n8n_webhook_queue.id as queue_id',
    db_prefix().'n8n_webhooks.name as webhook_name',
    db_prefix().'n8n_webhook_queue.event_type as event_type',
    db_prefix().'n8n_webhook_queue.status',
    'retry_count',
    'next_retry_at',
    db_prefix().'n8n_webhook_queue.created_at',
    '1',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'n8n_webhook_queue';

$join = [
    'LEFT JOIN '.db_prefix().'n8n_webhooks ON '.db_prefix().'n8n_webhook_queue.webhook_id = '.db_prefix().'n8n_webhooks.id',
    'LEFT JOIN '.db_prefix().'n8n_webhook_logs ON '.db_prefix().'n8n_webhook_queue.log_id = '.db_prefix().'n8n_webhook_logs.id',
];

$additionalSelect = [
    db_prefix() . 'n8n_webhook_queue.id as queue_id',
    db_prefix() . 'n8n_webhook_queue.webhook_id as webhook_id',
    db_prefix() . 'n8n_webhook_queue.log_id as log_id',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], $additionalSelect);

$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];

    // Queue ID
    $row[] = '#' . $aRow['queue_id'];

    // Webhook name
    $row[] = $aRow['webhook_name'] ? '<a href="'.admin_url(N8N_CONNECTOR_MODULE . '/webhook/' . $aRow['webhook_id']).'">'.htmlspecialchars($aRow['webhook_name']).'</a>' : '<span class="text-muted">' . _l('n8n_webhook_not_found') . '</span>';

    // Event type
    $event = n8n_get_event($aRow['event_type']);
    $event_label = $event ? $event['label'] : $aRow['event_type'];
    $row[] = '<span class="badge badge-default">' . htmlspecialchars($event_label) . '</span>';

    // Status badge
    $status = $aRow[db_prefix().'n8n_webhook_queue.status'];
    if ($status == 'completed') {
        $status_badge = '<span class="label label-success">' . _l('n8n_queue_status_completed') . '</span>';
    } elseif ($status == 'pending') {
        if ($aRow['retry_count'] > 0) {
            $status_badge = '<span class="label label-warning">' . _l('n8n_queue_status_retrying') . '</span>';
        } else {
            $status_badge = '<span class="label label-info">' . _l('n8n_queue_status_pending') . '</span>';
        }
    } elseif ($status == 'processing') {
        $status_badge = '<span class="label label-primary">' . _l('n8n_queue_status_processing') . '</span>';
    } else {
        $status_badge = '<span class="label label-danger">' . _l('n8n_queue_status_failed') . '</span>';
    }
    $row[] = $status_badge;

    // Retry count
    if ($aRow['retry_count'] > 0) {
        $row[] = '<span class="badge badge-warning">' . $aRow['retry_count'] . '</span>';
    } else {
        $row[] = '-';
    }

    // Next retry time
    if ($aRow['next_retry_at']) {
        $row[] = _dt($aRow['next_retry_at']);
    } else {
        $row[] = '-';
    }

    // Created at
    $row[] = _dt($aRow[db_prefix().'n8n_webhook_queue.created_at']);

    // Actions  
    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';  
    $options .= !empty($aRow['log_id']) ?  '<a href="' . admin_url(N8N_CONNECTOR_MODULE . '/log/' . $aRow['log_id']) . '" data-type="text" data-toggle="tooltip" data-title="' . _l('n8n_view_log') . '" class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa-regular fa-eye fa-lg"></i></a>': '';
    $options .= staff_can('delete', 'n8n_queue') ?  '<a href="' . N8N_CONNECTOR_MODULE . '/delete_queue/' . $aRow['queue_id'] . '" data-type="text" data-toggle="tooltip" data-title="' . _l('delete') . '" class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete"><i class="fa-regular fa-trash-can fa-lg"></i></a>': '';
    $options .= "</div>";

    $row[] = $options;

    $output['aaData'][] = $row;
}
