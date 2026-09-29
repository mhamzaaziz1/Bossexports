<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'name',
    'webhook_url',
    'enabled_events',
    'is_active',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'n8n_webhooks';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Name
    $row[] = htmlspecialchars($aRow['name']);

    // Webhook URL (truncated)
    $fullUrl = htmlspecialchars($aRow['webhook_url']);
    if (strlen($fullUrl) > 50) {
        $url = substr($fullUrl, 0, 47) . '...';
    }
    $row[] = '<span class="copy-webhook-url text-muted" data-url="' . $fullUrl . '" data-toggle="tooltip" data-title="' . _l('copy_url') . '"><small>' . $url . '</small></span>';

    // Event types
    $events = !empty($aRow['enabled_events']) ? json_decode($aRow['enabled_events'], true) : [];
    if (is_array($events) && !empty($events)) {
        $event_badges = '';
        $count = 0;
        foreach ($events as $event_key) {
            $count++;
            if ($count > 3) {
                $remaining = count($events) - 3;
                $event_badges .= '<span class="badge badge-secondary">+' . $remaining . ' more</span>';
                break;
            }
            $event = n8n_get_event($event_key);
            $label = $event ? $event['label'] : $event_key;
            $event_badges .= '<span class="badge badge-info">' . htmlspecialchars($label) . '</span> ';
        }
        $row[] = $event_badges;
    } else {
        $row[] = '-';
    }

    // Status toggle
    $is_active = '';
    if ($aRow['is_active'] == 1) {
        $is_active = 'checked';
    }
    
    $disabled = staff_can('edit', 'n8n_connector') ? '' : 'disabled';
    $row[] = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="' . admin_url() . N8N_CONNECTOR_MODULE . '/toggle_webhook" 
                       name="onoffswitch" class="onoffswitch-checkbox" 
                       id="webhook_' . $aRow['id'] . '" 
                       data-id="' . $aRow['id'] . '" ' . $is_active . ' '.$disabled.'>
                <label class="onoffswitch-label" for="webhook_' . $aRow['id'] . '"></label>
               </div>';

    // Actions
    if (staff_can('edit', 'n8n_connector') || staff_can('delete', 'n8n_connector')) {
        $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
        $options .= staff_can('edit', 'n8n_connector') ? '<a href="' . N8N_CONNECTOR_MODULE . '/webhook/' . $aRow['id'] . '" data-type="text" data-toggle="tooltip" data-title="' . _l('edit') . '" class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa-regular fa-pen-to-square fa-lg"></i></a>':'';
        $options .= staff_can('delete', 'n8n_connector') ?  '<a href="' . N8N_CONNECTOR_MODULE . '/delete_webhook/' . $aRow['id'] . '" data-type="text" data-toggle="tooltip" data-title="' . _l('delete') . '" class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete"><i class="fa-regular fa-trash-can fa-lg"></i></a>': '';
        $options .= "</div>";
        $row[] = $options;
    } else {
        $row[] = "-";
    }
    $output['aaData'][] = $row;
}
