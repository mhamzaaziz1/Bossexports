<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Si_sms_model extends App_Model
{
	public function __construct()
	{
		parent::__construct();
	}
	/**
	* @param  integer (optional)
	* @return object
	* Get single template filter
	*/
	public function get($id = '')
	{
		$this->db->where('staff_id',get_staff_user_id());
		if (is_numeric($id)) {
			$this->db->where('id', $id);
			return $this->db->get(db_prefix() . 'si_sms_templates')->row();
		}
		return $this->db->get(db_prefix() . 'si_sms_templates')->result_array();
	}
	/**
	* Add new sms template
	* @param mixed $data All $_POST data
	* @return mixed
	*/
	public function add($data)
	{
		$this->db->insert(db_prefix() . 'si_sms_templates', $data);
		$insert_id = $this->db->insert_id();
		if ($insert_id) {
			log_activity('New Add-on SMS Template Added [Name:' . $data['template_name'] . ']');
			return $insert_id;
		}
		return false;
	}
	/**
	* Update sms templates
	* @param mixed $data All $_POST data
	* @return mixed
	*/
	public function update($template_id,$data)
	{
		$this->db->where('id',$template_id);
		$this->db->where('staff_id',get_staff_user_id());
		$update = $this->db->update(db_prefix() . 'si_sms_templates', $data);
		if ($update) {
			log_activity('Add-on SMS Template Updated [Name:' . $data['template_name'] . ']');
			return true;
		}
		return false;
	}
	/**
	* Delete sms template
	* @param  mixed $id template id
	* @return boolean
	*/
	public function delete($id,$staff_id)
	{
		$this->db->where('id', $id);
		$this->db->where('staff_id', $staff_id);
		$this->db->delete(db_prefix() . 'si_sms_templates');
		if ($this->db->affected_rows() > 0) {
			log_activity('Add-on SMS Template Deleted [ID:' . $id . ']');
			return true;
		}
		return false;
	}
	public function get_clients()
	{
		$permission_view_own = (!has_permission('customers', '', 'view'));
		if($permission_view_own){
			$this->db->join(db_prefix() . 'customer_admins',db_prefix() . 'customer_admins.customer_id='.db_prefix() . 'clients.userid');
			$this->db->where(db_prefix() . 'customer_admins.staff_id',get_staff_user_id());
		}
		$this->db->select('userid as id,company as name',false);	
		$this->db->where('active',1);
		$this->db->from(db_prefix() . 'clients');
		return $this->db->get()->result_array();
	}
	public function get_leads()
	{
		$has_permission_view = has_permission('leads', '', 'view');
		if (!$has_permission_view) {
			$this->db->where('(assigned = ' . get_staff_user_id() . ' OR addedfrom=' . get_staff_user_id() . ' OR is_public=1)');
		}
		$this->db->select('id,name');	
		$this->db->from(db_prefix() . 'leads');
		return $this->db->get()->result_array();
	}
	public function get_client_contacts($client_ids, $where = ['active' => 1])
	{
		if ($client_ids != '' && (is_numeric($client_ids) || is_array($client_ids))){
			$send_type = get_option(SI_SMS_MODULE_NAME.'_send_to_customer');
			if($send_type=='all' || $send_type=='primary'){
				$this->db->select('id,CONCAT(firstname," ",lastname) as name,phonenumber,email',false);
				$this->db->where($where);
				if (is_numeric($client_ids)) {
					$this->db->where('userid', $client_ids);
				}
				if (is_array($client_ids)) {
					$this->db->where_in('userid', $client_ids);
				}
				if($send_type=='primary')
					$this->db->where('is_primary', 1);
				$this->db->where('phonenumber IS NOT NULL');
				$this->db->where('phonenumber<>', '');
				return $this->db->get(db_prefix() . 'contacts')->result_array();
			}
			if($send_type=='client'){
				$this->db->select('userid,company as name,phonenumber,"" as email',false);
				$this->db->where($where);
				if (is_numeric($client_ids)) {
					$this->db->where('userid', $client_ids);
				}
				if (is_array($client_ids)) {
					$this->db->where_in('userid', $client_ids);
				}
				$this->db->where('phonenumber IS NOT NULL');
				$this->db->where('phonenumber<>', '');
				return $this->db->get(db_prefix() . 'clients')->result_array();
			}
		}
		return array();
	}
	public function get_leads_contacts($lead_ids, $where = [])
	{
		if ($lead_ids != '' && (is_numeric($lead_ids) || is_array($lead_ids))){
			$this->db->select('id,name,phonenumber,email');
			$this->db->where($where);
			if (is_numeric($lead_ids)) {
				$this->db->where('id', $lead_ids);
			}
			if (is_array($lead_ids)) {
				$this->db->where_in('id', $lead_ids);
			}
			$this->db->where('phonenumber IS NOT NULL');
			$this->db->where('phonenumber<>', '');
			return $this->db->get(db_prefix() . 'leads')->result_array();
		}
		return array();
	}
	public function get_staffs_contacts($staff_ids, $where = ['active' => 1])
	{
		if ($staff_ids != '' && (is_numeric($staff_ids) || is_array($staff_ids))){
			$this->db->select('staffid,CONCAT(firstname," ",lastname) as name,phonenumber,email',false);
			$this->db->where($where);
			if (is_numeric($staff_ids)) {
				$this->db->where('staffid', $staff_ids);
			}
			if (is_array($staff_ids)) {
				$this->db->where_in('staffid', $staff_ids);
			}
			$this->db->where('phonenumber IS NOT NULL');
			$this->db->where('phonenumber<>', '');
			return $this->db->get(db_prefix() . 'staff')->result_array();
		}
		return array();
	}
}
