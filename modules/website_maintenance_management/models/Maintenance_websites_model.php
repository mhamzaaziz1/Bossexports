<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Maintenance_websites_model extends App_Model {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get website(s)
	 *
	 * @param  mixed  $id  Website ID or empty for all
	 *
	 * @return mixed
	 */
	public function get($id = '')
	{
		if (is_numeric($id))
		{
			$this->db->where('w.id', $id);
			$this->db->select('w.*, p.name as project_name, c.company as client_name');
			$this->db->from(db_prefix().'wmm_websites w');
			$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
			$this->db->join(db_prefix().'clients c', 'c.userid = w.client_id', 'left');

			return $this->db->get()->row();
		}

		$this->db->select('w.*, p.name as project_name, c.company as client_name');
		$this->db->from(db_prefix().'wmm_websites w');
		$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
		$this->db->join(db_prefix().'clients c', 'c.userid = w.client_id', 'left');
		$this->db->where('w.is_active', 1);
		$this->db->order_by('c.company', 'ASC');

		return $this->db->get()->result_array();
	}

	/**
	 * Get all websites including inactive
	 *
	 * @return array
	 */
	public function get_all()
	{
		$this->db->select('w.*, p.name as project_name, c.company as client_name');
		$this->db->from(db_prefix().'wmm_websites w');
		$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
		$this->db->join(db_prefix().'clients c', 'c.userid = w.client_id', 'left');
		$this->db->order_by('c.company', 'ASC');

		return $this->db->get()->result_array();
	}

	/**
	 * Add website to maintenance
	 *
	 * @param  array  $data  Website data
	 *
	 * @return mixed
	 */
	public function add($data)
	{
		// Check if project already exists
		$this->db->where('project_id', $data['project_id']);
		$exists = $this->db->get(db_prefix().'wmm_websites')->row();

		if ($exists)
		{
			return ['error' => _l('wmm_project_already_added')];
		}

		$data['added_by']  = get_staff_user_id();
		$data['is_active'] = 1;

		$this->db->insert(db_prefix().'wmm_websites', $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			log_activity('Website Added to Maintenance [ID:'.$insert_id.', Project ID: '.$data['project_id'].']');

			return $insert_id;
		}

		return FALSE;
	}

	/**
	 * Update website
	 *
	 * @param  array  $data  Website data
	 * @param  int  $id  Website ID
	 *
	 * @return bool
	 */
	public function update($data, $id)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix().'wmm_websites', $data);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Maintenance Website Updated [ID:'.$id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Delete website
	 *
	 * @param  int  $id  Website ID
	 *
	 * @return bool
	 */
	public function delete($id)
	{
		// Check if website has logs
		$this->db->where('website_id', $id);
		$count = $this->db->count_all_results(db_prefix().'wmm_maintenance_logs');

		if ($count > 0)
		{
			return ['error' => _l('wmm_website_has_logs')];
		}

		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'wmm_websites');

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Maintenance Website Deleted [ID:'.$id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Toggle website active status
	 *
	 * @param  int  $id  Website ID
	 * @param  int  $status  Active status
	 *
	 * @return bool
	 */
	public function toggle_active($id, $status)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix().'wmm_websites', ['is_active' => $status]);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Maintenance Website Status Changed [ID:'.$id.', Active: '.$status.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Get websites by client
	 *
	 * @param  int  $client_id  Client ID
	 *
	 * @return array
	 */
	public function get_by_client($client_id)
	{
		$this->db->select('w.*, p.name as project_name, c.company as client_name');
		$this->db->from(db_prefix().'wmm_websites w');
		$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
		$this->db->join(db_prefix().'clients c', 'c.userid = w.client_id', 'left');
		$this->db->where('w.client_id', $client_id);
		$this->db->where('w.is_active', 1);

		return $this->db->get()->result_array();
	}

	/**
	 * Get primary contact for website
	 *
	 * @param  int  $website_id  Website ID
	 *
	 * @return object|null
	 */
	public function get_primary_contact($website_id)
	{
		$website = $this->get($website_id);

		if ( ! $website)
		{
			return NULL;
		}

		$this->db->where('userid', $website->client_id);
		$this->db->where('is_primary', 1);
		$contact = $this->db->get(db_prefix().'contacts')->row();

		// If no primary contact, get first active contact
		if ( ! $contact)
		{
			$this->db->where('userid', $website->client_id);
			$this->db->where('active', 1);
			$this->db->limit(1);
			$contact = $this->db->get(db_prefix().'contacts')->row();
		}

		return $contact;
	}

}
