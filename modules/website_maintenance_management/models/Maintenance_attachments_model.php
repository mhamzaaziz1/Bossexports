<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Maintenance_attachments_model extends App_Model {

	private $upload_path;

	public function __construct()
	{
		parent::__construct();
		$this->upload_path = get_upload_path_by_type('maintenance_logs');
	}

	/**
	 * Get attachment(s) for a log
	 *
	 * @param  int  $log_id  Log ID
	 * @param  int  $id  Attachment ID (optional)
	 *
	 * @return mixed
	 */
	public function get($log_id, $id = '')
	{
		$this->db->where('log_id', $log_id);

		if (is_numeric($id))
		{
			$this->db->where('id', $id);

			return $this->db->get(db_prefix().'wmm_maintenance_attachments')->row();
		}

		$this->db->order_by('dateadded', 'ASC');

		return $this->db->get(db_prefix().'wmm_maintenance_attachments')->result_array();
	}

	/**
	 * Add attachment
	 *
	 * @param  int  $log_id  Log ID
	 * @param  array  $data  Attachment data
	 *
	 * @return mixed
	 */
	public function add($log_id, $data)
	{
		$data['log_id']    = $log_id;
		$data['staffid']   = get_staff_user_id();
		$data['dateadded'] = date('Y-m-d H:i:s');

		if ( ! isset($data['attachment_key']))
		{
			$data['attachment_key'] = app_generate_hash();
		}

		$this->db->insert(db_prefix().'wmm_maintenance_attachments', $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			log_activity('Maintenance Log Attachment Added [Log ID: '.$log_id.', File: '.$data['file_name'].']');

			return $insert_id;
		}

		return FALSE;
	}

	/**
	 * Delete attachment
	 *
	 * @param  int  $id  Attachment ID
	 * @param  int  $log_id  Log ID
	 *
	 * @return bool
	 */
	public function delete($id, $log_id)
	{
		$this->db->where('id', $id);
		$this->db->where('log_id', $log_id);
		$attachment = $this->db->get(db_prefix().'wmm_maintenance_attachments')->row();

		if ( ! $attachment)
		{
			return FALSE;
		}

		// Check permission
		if ($attachment->staffid != get_staff_user_id() && staff_cant('delete', 'website_maintenance_logs'))
		{
			return FALSE;
		}

		// Delete physical file if not external
		if (empty($attachment->external))
		{
			$file_path = $this->upload_path.$log_id.'/'.$attachment->file_name;
			if (file_exists($file_path))
			{
				unlink($file_path);
			}
		}

		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'wmm_maintenance_attachments');

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Maintenance Log Attachment Deleted [ID: '.$id.']');

			// Check if folder is empty and delete it
			if (empty($attachment->external))
			{
				$dir = $this->upload_path.$log_id;
				if (is_dir($dir) && count(scandir($dir)) == 2)
				{
					rmdir($dir);
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Delete all attachments for a log
	 *
	 * @param  int  $log_id  Log ID
	 *
	 * @return bool
	 */
	public function delete_all($log_id)
	{
		$attachments = $this->get($log_id);

		foreach ($attachments as $attachment)
		{
			$this->delete($attachment['id'], $log_id);
		}

		return TRUE;
	}

	/**
	 * Get upload path
	 *
	 * @return string
	 */
	public function get_upload_path()
	{
		return $this->upload_path;
	}

}
