<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Approvify_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addRequest($data)
    {

        $data['created_at']      = date('Y-m-d H:i:s');
        $data['requester_id'] = get_staff_user_id();
        $data['request_title']   = trim($data['request_title']);
        $data['request_content']   = nl2br_save_html($data['request_content']);

        $categoryData = $this->getType($data['category_id']);

        $this->db->insert(db_prefix() . 'approvify_requests', $data);
        $requestId = $this->db->insert_id();
        if ($requestId) {

            if (!empty($categoryData->approve_list)) {

                $this->load->model('emails_model');
                $decodeApproveList = json_decode($categoryData->approve_list);

                foreach ($decodeApproveList as $staff) {

                    $staffData = get_staff($staff);

                    $notified = add_notification([
                        'description'     => 'approvify_new_request_from_staff',
                        'touserid'        => $staff,
                        'fromcompany'     => 1,
                        'fromuserid'      => 0,
                        'link'            => 'approvify/view_request/' . $requestId,
                        'additional_data' => serialize([
                            get_staff_full_name($staff),
                            $data['request_title'],
                        ]),
                    ]);

                    if ($notified) {
                        pusher_trigger_notification([$staff]);
                    }

                    $this->emails_model->send_simple_email(
                        $staffData->email,
                        get_option('companyname').' - New Staff Request',
                        '
            Hello,
<br>
There is a new request from staff with title <strong>'.$data['request_title'] .'</strong> request by <strong>'.get_staff_full_name($data['requester_id'] ).'</strong>.
<br>
<a href="'.admin_url('approvify/view_request/' . $requestId).'/?review=true">Check Request Here</a>
<br>
Best regards,<br>
'.get_option('companyname').'
            '
                    );

                }

            }

            $attachments = approvify_handle_request_attachments($requestId);
            if ($attachments) {
                $this->insertRequestFilesToDatabase($attachments, $requestId);
            }

            $_attachments = $this->getRequestAttachments($requestId);

            return $requestId;
        }

        return false;
    }

    public function getRequest($requestId)
    {
        $this->db->select(db_prefix() . 'approvify_requests.*, '. db_prefix() . 'approvify_approval_categories.category_name, '. db_prefix() . 'approvify_approval_categories.approve_list');
        $this->db->from(db_prefix() . 'approvify_requests');
        $this->db->join(db_prefix() . 'approvify_approval_categories', db_prefix() . 'approvify_approval_categories.id = ' . db_prefix() . 'approvify_requests.category_id', 'left');

        $this->db->where(db_prefix() . 'approvify_requests.id', $requestId);

        $request = $this->db->get()->row();
        if ($request) {
            $request->attachments = $this->getRequestAttachments($request->id);
        }

        return $request;
    }

    public function getRequestAttachments($id)
    {
        $this->db->where('request_id', $id);
        return $this->db->get('approvify_request_files')->result_array();
    }

    public function insertRequestFilesToDatabase($attachments, $requestId)
    {
        foreach ($attachments as $attachment) {
            $attachment['request_id']  = $requestId;
            $attachment['created_at'] = date('Y-m-d H:i:s');
            $attachment['filename'] = $attachment['file_name'];
            unset($attachment['file_name'], $attachment['filetype']);

            $this->db->insert(db_prefix() . 'approvify_request_files', $attachment);
        }
    }

    public function updateRequest($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'approvify_requests', $data);

        return $this->db->affected_rows() > 0;
    }

    public function addActivity($data)
    {
        $this->db->insert(db_prefix() . 'approvify_request_activity', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function getActivities($requestId)
    {
        $this->db->where('request_id', $requestId);
        return $this->db->get(db_prefix() . 'approvify_request_activity')->result_array();
    }

    public function addType($data)
    {
        $this->db->insert(db_prefix() . 'approvify_approval_categories', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function getType($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'approvify_approval_categories')->row();
    }

    public function getTypes()
    {
        $this->db->where('is_active', '1');
        return $this->db->get(db_prefix() . 'approvify_approval_categories')->result_array();
    }

    public function updateType($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'approvify_approval_categories', $data);

        return $this->db->affected_rows() > 0;
    }

    public function deleteType($id)
    {

        if (is_reference_in_table('category_id', db_prefix() . 'approvify_requests', $id)) {
            return [
                'referenced' => true,
            ];
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'approvify_approval_categories');

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function changeTypeStatus($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'approvify_approval_categories', [
            'is_active' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

}
