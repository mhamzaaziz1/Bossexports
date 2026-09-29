<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix().'n8n_webhooks.name as webhook_name',
    'event_type',
    'status',
    'response_code',
    'triggered_at',
    '1',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'n8n_webhook_logs';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, ['LEFT JOIN '.db_prefix().'n8n_webhooks ON '.db_prefix().'n8n_webhook_logs.webhook_id = '.db_prefix().'n8n_webhooks.id'], [], [db_prefix() . 'n8n_webhook_logs.id', 'resource_type', db_prefix().'n8n_webhook_logs.webhook_id as webhook_id']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Webhook name
    $CI = &get_instance();
    $row[] = $aRow['webhook_name'] ? '<a href="'.admin_url(N8N_CONNECTOR_MODULE . '/webhook/' . $aRow['webhook_id']).'">'.htmlspecialchars($aRow['webhook_name']).'</a>' : _l('n8n_webhook_not_found');

    // Event type
    $event = n8n_get_event($aRow['event_type']);
    $event_label = $event ? $event['label'] : $aRow['event_type'];
    $row[] = '<span class="badge badge-default">' . htmlspecialchars($event_label) . '</span>';

    // Status badge
    if ($aRow['status'] == 'success') {
        $status_badge = '<span class="label label-success">' . _l('n8n_status_success') . '</span>';
    } elseif ($aRow['status'] == 'pending') {
        $status_badge = '<span class="label label-info">Pending</span>';
    } elseif ($aRow['status'] == 'retry') {
        $status_badge = '<span class="label label-warning">Retrying</span>';
    } else {
        $status_badge = '<span class="label label-danger">' . _l('n8n_status_failed') . '</span>';
    }
    $row[] = $status_badge;

    // Response code with color
    $response_code = $aRow['response_code'];
    $color = 'label-default';
    if ($response_code >= 200 && $response_code <= 299) {
        $color = 'label-success';
    } elseif ($response_code >= 300 && $response_code <= 399) {
        $color = 'label-info';
    } elseif ($response_code >= 400 && $response_code <= 499) {
        $color = 'label-warning';
    } elseif ($response_code >= 500 && $response_code <= 599) {
        $color = 'label-danger';
    }

    $row[] = ($response_code > 0) ? '<span class="label ' . $color . '">' . $response_code . '</span>' : '-';

    // Triggered date
    $row[] = _dt($aRow['triggered_at']);

    // Actions
    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';  
    $options .= '<a href="' . admin_url(N8N_CONNECTOR_MODULE . '/log/' . $aRow['id']) . '" data-type="text" data-toggle="tooltip" data-title="' . _l('n8n_view_log') . '" class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa-regular fa-eye fa-lg"></i></a>';
    $options .= staff_can('delete', 'n8n_webhook_log') ?  '<a href="' . N8N_CONNECTOR_MODULE . '/delete_log/' . $aRow['id'] . '" data-type="text" data-toggle="tooltip" data-title="' . _l('delete') . '" class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete"><i class="fa-regular fa-trash-can fa-lg"></i></a>': '';
    $options .= "</div>";
    $row[] = $options;

    $output['aaData'][] = $row;
}
