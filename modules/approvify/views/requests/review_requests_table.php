<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'requester_id',
    'category_id',
    'request_title',
    'status',
    db_prefix() . 'approvify_requests.created_at as request_created',
    db_prefix() . 'approvify_approval_categories.category_name as category_name',
    db_prefix() . 'approvify_approval_categories.approve_list as approve_list',
    db_prefix() . 'approvify_approval_categories.category_icon as category_icon'
 ];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'approvify_requests';

$join = [
    'LEFT JOIN ' . db_prefix() . 'approvify_approval_categories ON ' . db_prefix() . 'approvify_approval_categories.id = ' . db_prefix() . 'approvify_requests.category_id',
];
$where = [];

array_push($where, "AND FIND_IN_SET('".get_staff_user_id()."', REPLACE(REPLACE(REPLACE(".db_prefix()."approvify_approval_categories.approve_list, '[',''), ']', ''), '\"', ''))");

if (isset($postData['approvify_request_status']) && $postData['approvify_request_status']) {
    $statuses = $postData['approvify_request_status'];
    $statuses = array_filter($statuses);
    $statuses = array_map(function($status) {
        if ($status === 'sub')
        {
            $status = 0;
        }
        return "'" . $status . "'";
    }, $statuses);
    array_push($where, 'AND '.db_prefix().'approvify_requests.status IN (' . implode(',', $statuses) . ')');
}

if (isset($postData['approvify_request_staff']) && $postData['approvify_request_staff']) {
    $statuses = $postData['approvify_request_staff'];
    $statuses = array_filter($statuses);
    $statuses = array_map(function($status) {
        if ($status === 'sub')
        {
            $status = 0;
        }
        return "'" . $status . "'";
    }, $statuses);
    array_push($where, 'AND '.db_prefix().'approvify_requests.requester_id IN (' . implode(',', $statuses) . ')');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix().'approvify_requests.id'
]);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {

        $row[] = $aRow['id'];
        $row[] = $aRow['request_title'];
        $row[] = '<i class="'.$aRow['category_icon'].'"></i>  '.$aRow['category_name'];

        $row[] = get_staff_full_name($aRow['requester_id']);

        $row[] = approvify_return_request_status_html($aRow['status']);

        $approveList = '';
        if (!empty($aRow['approve_list'])) {
            $decodeApproveList = json_decode($aRow['approve_list']);
            foreach ($decodeApproveList as $staff) {
                $approveList .= '<a href="' . admin_url('staff/profile/' . $staff) . '">' . staff_profile_image($staff, [
                        'staff-profile-image-small',
                    ]) . '</a>';
            }
        }

        $row[] = $approveList;

        $row[] = $aRow['request_created'];

        $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
        $options .= '<a href="' . admin_url('approvify/view_request/' . $aRow['id'] .'/?review=true') . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
        <i class="fa-regular fa-eye fa-lg"></i>
    </a>';

        $options .= '</div>';

        $row[] = $options;
    }

    $output['aaData'][] = $row;
}
