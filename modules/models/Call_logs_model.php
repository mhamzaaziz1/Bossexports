<?php
/* Model for Call Log Module, it has all database related functions/calls. */
defined('BASEPATH') or exit('No direct script access allowed');

use Carbon\Carbon;

class Call_logs_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    /* Get the count for staff. */
    public function get_staff_counts($staffid){
        $count = 0;

        $sql = "SELECT count(`staffid`) as total_count
                from ".db_prefix()."call_logs where staffid= '".$staffid."' " ;
        $query = $this->db->query($sql);
        $row = $query->row();
        if (isset($row)){
            $count = $row->total_count;
        }

        return $count;
    }

    /**
     * Get category
     * @param  mixed $id category id (Optional)
     * @return mixed     object or array
     */
    public function get_category($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'call_log_types')->row();
        }
        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'call_log_types')->result_array();
    }

    /**
     * Add new category
     * @param mixed $data All $_POST data
     * @return boolean
     */
    public function add_category($data)
    {
        $data['description'] = nl2br($data['description']);
        $this->db->insert(db_prefix() . 'call_log_types', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Call Type Added [ID: ' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }


    /**
     * @param  integer (optional)
     * @return object
     * Get single
     */
    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'call_logs')->row();
        }

        return $this->db->get(db_prefix() . 'call_logs')->result_array();
    }
    /* Get all call logs by staff members. */
    public function get_staff_call_logs($staff_id, $exclude_notified = true)
    {
        $this->db->where('staff_id', $staff_id);
        if ($exclude_notified) {
            $this->db->where('notified', 0);
        }

        $this->db->order_by('end_date', 'asc');
    }

    /**
     * Add new
     * @param mixed $data All $_POST dat
     * @return mixed
     */
    public function add($data)
    {
        $data['staffid']      = $data['staffid'] == '' ? 0 : $data['staffid'];
        $data['call_start_time']    = to_sql_date($data['call_start_time'], true);
        $data['call_end_time']      = to_sql_date($data['call_end_time'], true);
        if($data['has_follow_up'] == 1) {
            $data['follow_up_schedule'] = to_sql_date($data['follow_up_schedule'], true);
        }else{
            $data['follow_up_schedule'] = 'NULL';
        }

        $this->db->insert(db_prefix() . 'call_logs', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Call Log Added [ID:' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update
     * @param  mixed $data All $_POST data
     * @param  mixed $id    id
     * @return boolean
     */
    public function update($data, $id)
    {
        $data['staffid']      = $data['staffid'] == '' ? 0 : $data['staffid'];
        $data['call_start_time']    = to_sql_date($data['call_start_time'], true);
        $data['call_end_time']      = to_sql_date($data['call_end_time'], true);
        if($data['has_follow_up'] == 1) {
            $data['follow_up_schedule'] = to_sql_date($data['follow_up_schedule'], true);
        }else{
            $data['follow_up_schedule'] = 'NULL';
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'call_logs', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Call Log Updated [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete
     * @param  mixed $id id
     * @return boolean
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'call_logs');
        if ($this->db->affected_rows() > 0) {
            log_activity('Call log Deleted [ID:' . $id . ']');

            return true;
        }

        return false;
    }
    /* Display all logs, which has notification as true. Notification Queue. */
    public function get_notifiable_logs(){
        $this->db->where('is_completed', 0);
        $this->db->where('has_follow_up', 1);
        $this->db->where('notified', 0);
        $this->db->where('TIMESTAMPDIFF(MINUTE, CURRENT_TIMESTAMP(), follow_up_schedule) <=', '30');
        return $this->db->get(db_prefix() . 'call_logs')->result_array();
    }

    /**
     * Notify staff members
     * @param  mixed $id           id
     * @return boolean
     */
    public function notify_staff_members($id)
    {
        $callLog = $this->get($id);
        $callLog_desc = 'cl_follow_up_notification';

        if ($callLog->call_with_staffid > 0) {
            $this->load->model('staff_model');
            $staff = $this->staff_model->get('', ['active' => 1, 'staffid' => $callLog->call_with_staffid]);
        } else if ($callLog->staffid > 0) {
            $this->db->where('active', 1)
            ->where('staffid', $callLog->staffid);
            $staff = $this->db->get(db_prefix() . 'staff')->result_array();
        }else {
            $this->load->model('staff_model');
            $staff = $this->staff_model->get('', ['active' => 1]);
        }

        $oClient = $this->clients_model->get($callLog->clientid);
        $oCustomer = $this->clients_model->get_contacts($oClient->userid, ['is_primary' => true]);
        $contactName = '';
        if(isset($oCustomer[0])){
            $contactName = $oCustomer[0]['firstname']. ' '.  $oCustomer[0]['lastname'].'<br>';
        }
        $contactName = $contactName. ' ' .$oClient->company;
        $notifiedUsers = [];
        foreach ($staff as $member) {
            if (is_staff_member($member['staffid'])) {
                $notified = add_notification([
                    'fromcompany'     => 1,
                    'touserid'        => $member['staffid'],
                    'description'     => $callLog_desc,
                    'additional_data' => serialize([
                        $contactName,
                        _d($callLog->follow_up_schedule),
                    ]),
                ]);
                if ($notified) {
                    array_push($notifiedUsers, $member['staffid']);
                }
            }
        }

        pusher_trigger_notification($notifiedUsers);
        $this->db->where('id', $callLog->id);
        $this->db->update(db_prefix() . 'call_logs', [
            'notified' => 1,
        ]);

        if (count($staff) > 0 && $this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }
    /* Count the total inbound and outbound calls. */
    public function count_inbound_outbound_calls($start_date, $end_date){
        $result['inbound'] = 0;
        $result['outbound'] = 0;

        $sql = "SELECT count(`call_direction`) as inbound
                from ".db_prefix()."call_logs where call_direction = 1 AND DATE_FORMAT(`call_start_time`,'%Y-%m-%d') between '".$start_date."' AND '".$end_date."' " ;
        $query = $this->db->query($sql);
        $row = $query->row();
        if (isset($row)){
            $result['inbound'] = $row->inbound;
        }

        $sql = "SELECT count(`call_direction`) as outbound
                from `tblcall_logs` where call_direction = 2 AND DATE_FORMAT(`call_start_time`,'%Y-%m-%d') between '".$start_date."' AND '".$end_date."' " ;
        $query = $this->db->query($sql);
        $row = $query->row();
        if (isset($row)){
            $result['outbound'] = $row->outbound;
        }

        return $result;
    }
    /* Get the inbound report for the overview tab. */
    public function get_inbound_outbound_report($start_date, $end_date){
        $date_labels  = [];
        $total_inbound = [];
        $total_outbound   = [];
        $i              = 0;

        $daysDiff = Carbon::parse($end_date)->diffInDays(Carbon::parse($start_date));

        for ($d = 0; $d <= $daysDiff; $d++) {
            $filterDate =  Carbon::parse($start_date)->addDays($d)->format("Y-m-d");
            array_push($date_labels, _l(Carbon::parse($filterDate)->format('d M Y')));

            $inbound = 0;
            $outbound = 0;

            $sql = "SELECT count(`call_direction`) as inbound
                from ".db_prefix()."call_logs where call_direction = 1 AND DATE_FORMAT(`call_start_time`,'%Y-%m-%d') between '".$filterDate."' AND '".$filterDate."' " ;
            $query = $this->db->query($sql);
            $row = $query->row();
            if (isset($row)){
                $inbound = $row->inbound;
            }

            if (!isset($total_inbound[$i])) {
                $total_inbound[$i] = [];
            }
            $total_inbound[$i] = $inbound;

            $sql = "SELECT count(`call_direction`) as outbound
                from `tblcall_logs` where call_direction = 2 AND DATE_FORMAT(`call_start_time`,'%Y-%m-%d') between '".$filterDate."' AND '".$filterDate."' " ;
            $query = $this->db->query($sql);
            $row = $query->row();
            if (isset($row)){
                $outbound = $row->outbound;
            }

            if (!isset($total_outbound[$i])) {
                $total_outbound[$i] = [];
            }
            $total_outbound[$i] = $outbound;

            $i++;
        }

        $chart = [
            'labels'   => $date_labels,
            'datasets' => [
                [
                    'label'           => _l('cl_report_inbound_calls'),
                    'backgroundColor' => 'rgba(60, 118, 61,0.8)',
                    'borderColor'     => '#3c763d',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => $total_inbound,
                ],
                [
                    'label'           => _l('cl_report_outbound_calls'),
                    'backgroundColor' => 'rgba(51, 122, 183,0.8)',
                    'borderColor'     => '#337ab7',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => $total_outbound,
                ],
            ],
        ];

        return $chart;
    }
}
