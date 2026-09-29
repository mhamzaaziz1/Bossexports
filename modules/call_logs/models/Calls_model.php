<?php 
/**
* Call LOgs Model
*/
class Calls_model extends App_Model {
	
	function __construct() {
		parent::__construct();
		$this->tbl = db_prefix().'_call_logs';
	}
	public function get($id = '', $where = []) {
        $subQ = "(case 
                    when caller_type = 'caller' then caller_name
                    when caller_type = 'customer' then tblclients.company
                    when caller_type = 'driver' then (
                        SELECT CONCAT(tblstaff.firstname,' ',tblstaff.lastname) 
                        FROM tblstaff 
                        WHERE tblstaff.staffid = tbl_call_logs.driver_id
                    )
                END) as customer";
        $this->db->where($where);
        $this->db->join('tblclients', 'tblclients.userid = '.$this->tbl.'.clientid','left');
        $this->db->join('tblstaff', 'tblstaff.staffid = '.$this->tbl.'.driver_id','left');
        $this->db->select($this->tbl.'.*, tblclients.company, '.$subQ);
        $this->db->select('TIMEDIFF('.$this->tbl.'.end_time, '.$this->tbl.'.start_time) as call_duration',false);
		if (is_numeric($id)) {
            $this->db->where($this->tbl.'.id', $id);
            $call = $this->db->get($this->tbl)->row();
            // exit($this->db->last_query());
            $call->call_for = get_call_log_for($call->call_for);

            return do_action('get_call', $call);
        }

        return $this->db->get(db_prefix().$this->tbl)->result_array();
	}
	public function add($data) {
		$insert_data = [];
        foreach ($data as $key => $value) {
            $insert_data[$key] = $value;
        }

        $dt = DateTime::createFromFormat('Y-d-m H:i:s', $insert_data['start_time']);
        $insert_data['start_time'] = $dt->format('Y-m-d H:i:s');
        $dt = DateTime::createFromFormat('Y-d-m H:i:s', $insert_data['end_time']);
        $insert_data['end_time'] = $dt->format('Y-m-d H:i:s');

        if (isset($insert_data['followup_time'])) {
            $dt = DateTime::createFromFormat('Y-d-m H:i:s', $insert_data['followup_time']);
            $insert_data['followup_time'] = $dt->format('Y-m-d H:i:s');
        };
        $insert_data['datecreated'] = date('Y-m-d H:i:s');
        $insert_data['created_by'] = $insert_data['call_owner'];

        if (isset($data['caller_type'])) {
            if ($data['caller_type'] == 'caller_name') {
                $insert_data['caller_type'] = 'caller';
            }
            if ($data['caller_type'] == 'driver_id') {
                $insert_data['caller_type'] = 'driver';
            }
            if ($data['caller_type'] == 'clientid') {
                $insert_data['caller_type'] = 'customer';
            }
        }
        // unset($insert_data['parent_id']);
        if (!empty($data['parent_id'])) {
            $insert_data['parent'] = 0;
        }
		$this->db->insert($this->tbl, $insert_data);
		$insert_id = $this->db->insert_id();
		if ($insert_id) {
            // $this->update($data['parent_id'],['child_id' => $insert_id, 'caller_type' => $data['caller_type']]);
			return $insert_id;
		}
		return false;
	}
	public function update($id, $data) {
        foreach ($data as $key => $value) {
            $update[$key] = $value;
        }
		if (isset($data['caller_type'])) {
            switch ($data['caller_type']) {
                case 'caller_name':
                    $caller_type = 'caller';
                    break;
                case 'driver_id':
                    $caller_type = 'driver';
                    break;
                case 'clientid':
                    $caller_type = 'customer';
                    break;
            }
            $update['caller_type'] = $caller_type;
        }
        // $update = [
        //     'clientid' =>  isset($data['clientid']) ? $data['clientid'] : '',
        //     'driver_id' =>  isset($data['driver_id']) ? $data['driver_id'] : null,
        //     'child_id' =>  isset($data['child_id']) ? $data['child_id'] : 0,
        //     'caller_name' =>  isset($data['caller_name']) ? $data['caller_name'] : '',
        //     'caller_type' =>  $caller_type,
        //     'purpose_of_call' => $data['purpose_of_call'],
        //     'invoice_id' => $data['invoice_id'],
        //     'call_summary' => $data['call_summary'],
        //     'call_owner' => $data['call_owner'],
        //     'call_for' => $data['call_for'],
        //     'followup' => $data['followup'],
        // ];
        if (isset($data['followup_notes'])) {
            $update['followup_notes'] = $data['followup_notes'];
        }
        if (isset($data['followup_time'])) {
            $dt = DateTime::createFromFormat('Y-d-m H:i:s', $data['followup_time']);
            $update['followup_time'] = $dt->format('Y-m-d H:i:s');
        } else {
            // $update['followup_time'] = '0000-00-00 00:00:00';
        }
        $this->db->where('id', $id);
        $this->db->update($this->tbl, $update);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
	}
	public function delete($id) {
		$this->db->where('id', $id);
        $this->db->delete($this->tbl);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
	}

    public function import($data) {
        if($this->db->insert_batch('tblstaff', $data) !== false) {
            return true;
        }
        return false;
    }
}