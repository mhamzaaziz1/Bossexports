<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Maintenance_tasks_model extends App_Model {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get maintenance task(s)
	 *
	 * @param  mixed  $id  Task ID or empty for all
	 *
	 * @return mixed
	 */
	public function get($id = '')
	{
		if (is_numeric($id))
		{
			$this->db->where('id', $id);
			$task = $this->db->get(db_prefix().'wmm_maintenance_tasks')->row();

			if ($task)
			{
				// Get related data
				$task->assignees        = $this->get_task_assignees($id);
				$task->assignees_ids    = array_column($task->assignees, 'staffid');
				$task->maintenance_logs = $this->get_task_maintenance_logs($id);

				// Get creator info
				if ($task->created_by)
				{
					$this->db->select('staffid, firstname, lastname, email');
					$this->db->where('staffid', $task->created_by);
					$task->creator = $this->db->get(db_prefix().'staff')->row();
				}

				if ($task->category)
				{
					$this->db->where('id', $task->category);
					$task->category = $this->db->get(db_prefix().'wmm_categories')->row();
				}

				// Check if current user is assigned
				if (is_staff_logged_in())
				{
					$task->current_user_is_assigned = in_array(get_staff_user_id(), $task->assignees_ids);
					$task->current_user_is_creator  = ($task->created_by == get_staff_user_id());
				}
			}

			return $task;
		}

		$this->db->order_by('category', 'ASC');
		$this->db->order_by('name', 'ASC');

		return $this->db->get(db_prefix().'wmm_maintenance_tasks')->result_array();
	}

	/**
	 * Get active tasks only
	 *
	 * @return array
	 */
	public function get_active_tasks()
	{
		$this->db->where('is_active', 1);
		$this->db->order_by('category', 'ASC');
		$this->db->order_by('name', 'ASC');

		return $this->db->get(db_prefix().'wmm_maintenance_tasks')->result_array();
	}

	/**
	 * Add new maintenance task
	 *
	 * @param  array  $data  Task data
	 *
	 * @return mixed
	 */
	public function add($data)
	{
		$assignees = [];
		if (isset($data['assignees']))
		{
			$assignees = $data['assignees'];
			unset($data['assignees']);
		}

		$data['is_active']  = isset($data['is_active']) ? 1 : 0;
		$data['created_by'] = get_staff_user_id();

		$this->db->insert(db_prefix().'wmm_maintenance_tasks', $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			// Add assignees
			if ( ! empty($assignees))
			{
				$this->add_task_assignees($assignees, $insert_id);
			}

			log_activity('New Maintenance Task Added [ID:'.$insert_id.', Name: '.$data['name'].']');

			return $insert_id;
		}

		return FALSE;
	}

	/**
	 * Update maintenance task
	 *
	 * @param  array  $data  Task data
	 * @param  int  $id  Task ID
	 *
	 * @return bool
	 */
	public function update($data, $id)
	{
		$assignees = [];
		if (isset($data['assignees']))
		{
			$assignees = $data['assignees'];
			unset($data['assignees']);
		}

		$data['is_active'] = isset($data['is_active']) ? 1 : 0;

		// Handle assignees sync
		if (isset($assignees) && is_array($assignees))
		{
			$this->sync_task_assignees($assignees, $id);
		}

		$this->db->where('id', $id);
		$this->db->update(db_prefix().'wmm_maintenance_tasks', $data);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Maintenance Task Updated [ID:'.$id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Delete maintenance task
	 *
	 * @param  int  $id  Task ID
	 *
	 * @return bool
	 */
	public function delete($id)
	{
		// Check if task is used in any logs
		$this->db->where('task_id', $id);
		$count = $this->db->count_all_results(db_prefix().'wmm_maintenance_log_tasks');

		if ($count > 0)
		{
			return ['error' => _l('wmm_task_has_logs')];
		}

		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'wmm_maintenance_tasks');

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Maintenance Task Deleted [ID:'.$id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Get tasks by category
	 *
	 * @param  string  $category  Category name
	 *
	 * @return array
	 */
	public function get_by_category($category)
	{
		$this->db->where('category', $category);
		$this->db->where('is_active', 1);
		$this->db->order_by('name', 'ASC');

		return $this->db->get(db_prefix().'wmm_maintenance_tasks')->result_array();
	}

	/* ==================== ASSIGNEES METHODS ==================== */

	/**
	 * Get task assignees
	 *
	 * @param  int  $task_id  Task ID
	 *
	 * @return array
	 */
	public function get_task_assignees($task_id)
	{
		$this->db->select('a.*, s.firstname, s.lastname, s.email, CONCAT(s.firstname, " ", s.lastname) as full_name');
		$this->db->from(db_prefix().'wmm_task_assigned a');
		$this->db->join(db_prefix().'staff s', 's.staffid = a.staffid');
		$this->db->where('a.task_id', $task_id);
		$this->db->order_by('s.firstname', 'ASC');

		return $this->db->get()->result_array();
	}

	/**
	 * Add task assignees
	 *
	 * @param  array  $assignees  Array of staff IDs
	 * @param  int  $task_id  Task ID
	 *
	 * @return bool
	 */
	public function add_task_assignees($assignees, $task_id)
	{
		$assigned_from = get_staff_user_id();

		foreach ($assignees as $staff_id)
		{
			if ( ! is_numeric($staff_id))
			{
				continue;
			}

			// Check if already assigned
			$this->db->where('task_id', $task_id);
			$this->db->where('staffid', $staff_id);
			$exists = $this->db->get(db_prefix().'wmm_task_assigned')->row();

			if ( ! $exists)
			{
				$this->db->insert(db_prefix().'wmm_task_assigned', [
					'task_id'       => $task_id,
					'staffid'       => $staff_id,
					'assigned_from' => $assigned_from,
				]);
			}
		}

		return TRUE;
	}

	/**
	 * Sync task assignees (delete removed, add new)
	 *
	 * @param  array  $assignees  Array of staff IDs
	 * @param  int  $task_id  Task ID
	 *
	 * @return bool
	 */
	public function sync_task_assignees($assignees, $task_id)
	{
		$current_assignees = $this->get_task_assignees($task_id);
		$current_ids       = array_column($current_assignees, 'staffid');

		// Remove assignees not in new list
		foreach ($current_ids as $staff_id)
		{
			if ( ! in_array($staff_id, $assignees))
			{
				$this->db->where('task_id', $task_id);
				$this->db->where('staffid', $staff_id);
				$this->db->delete(db_prefix().'wmm_task_assigned');
			}
		}

		// Add new assignees
		$this->add_task_assignees($assignees, $task_id);

		return TRUE;
	}

	/**
	 * Remove task assignee
	 *
	 * @param  int  $task_id  Task ID
	 * @param  int  $staff_id  Staff ID
	 *
	 * @return bool
	 */
	public function remove_task_assignee($task_id, $staff_id)
	{
		$this->db->where('task_id', $task_id);
		$this->db->where('staffid', $staff_id);
		$this->db->delete(db_prefix().'wmm_task_assigned');

		return $this->db->affected_rows() > 0;
	}

	/* ==================== TIMESHEETS METHODS ==================== */

	/**
	 * Get task timesheets
	 *
	 * @param  int  $task_id  Task ID
	 *
	 * @return array
	 */
	public function get_task_timesheets($task_id)
	{
		$this->db->select('t.*, s.firstname, s.lastname, CONCAT(s.firstname, " ", s.lastname) as full_name');
		$this->db->from(db_prefix().'wmm_task_timers t');
		$this->db->join(db_prefix().'staff s', 's.staffid = t.staff_id');
		$this->db->where('t.task_id', $task_id);
		$this->db->order_by('t.start_time', 'DESC');
		$timesheets = $this->db->get()->result_array();

		// Calculate time spent for each
		foreach ($timesheets as &$timesheet)
		{
			if ($timesheet['end_time'])
			{
				$timesheet['time_spent']         = $timesheet['end_time'] - $timesheet['start_time'];
				$timesheet['time_spent_decimal'] = $this->get_decimal_time($timesheet['time_spent']);
			} else
			{
				$timesheet['time_spent']         = time() - $timesheet['start_time'];
				$timesheet['time_spent_decimal'] = $this->get_decimal_time($timesheet['time_spent']);
			}
		}

		return $timesheets;
	}

	/**
	 * Get active timer for staff member
	 *
	 * @param  int  $task_id  Task ID
	 * @param  int  $staff_id  Staff ID
	 *
	 * @return object|null
	 */
	public function get_active_timer($task_id, $staff_id = NULL)
	{
		if ( ! $staff_id)
		{
			$staff_id = get_staff_user_id();
		}

		$this->db->where('task_id', $task_id);
		$this->db->where('staff_id', $staff_id);
		$this->db->where('end_time IS NULL');

		return $this->db->get(db_prefix().'wmm_task_timers')->row();
	}

	/**
	 * Start task timer
	 *
	 * @param  int  $task_id  Task ID
	 *
	 * @return mixed
	 */
	public function start_timer($task_id)
	{
		$staff_id = get_staff_user_id();

		// Check if already has active timer
		$active_timer = $this->get_active_timer($task_id, $staff_id);
		if ($active_timer)
		{
			return ['error' => _l('wmm_timer_already_running')];
		}

		$this->db->insert(db_prefix().'wmm_task_timers', [
			'task_id'    => $task_id,
			'staff_id'   => $staff_id,
			'start_time' => time(),
		]);

		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			log_activity('Task Timer Started [Task ID: '.$task_id.']');

			return $insert_id;
		}

		return FALSE;
	}

	/**
	 * Stop task timer
	 *
	 * @param  int  $task_id  Task ID
	 * @param  array  $data  Timer data (note, hourly_rate)
	 *
	 * @return bool
	 */
	public function stop_timer($task_id, $data = [])
	{
		$staff_id = get_staff_user_id();

		$timer = $this->get_active_timer($task_id, $staff_id);
		if ( ! $timer)
		{
			return ['error' => _l('wmm_no_active_timer')];
		}

		$update_data = [
			'end_time' => time(),
		];

		if (isset($data['note']))
		{
			$update_data['note'] = $data['note'];
		}

		if (isset($data['hourly_rate']))
		{
			$update_data['hourly_rate'] = $data['hourly_rate'];
		}

		$this->db->where('id', $timer->id);
		$this->db->update(db_prefix().'wmm_task_timers', $update_data);

		log_activity('Task Timer Stopped [Task ID: '.$task_id.']');

		return TRUE;
	}

	/**
	 * Delete timesheet
	 *
	 * @param  int  $id  Timesheet ID
	 * @param  int  $task_id  Task ID
	 *
	 * @return bool
	 */
	public function delete_timesheet($id, $task_id)
	{
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'wmm_task_timers');

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Task Timesheet Deleted [ID: '.$id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Convert seconds to decimal hours
	 *
	 * @param  int  $seconds  Seconds
	 *
	 * @return float
	 */
	private function get_decimal_time($seconds)
	{
		return round($seconds / 3600, 2);
	}

	/* ==================== MAINTENANCE LOGS METHODS ==================== */

	/**
	 * Get maintenance logs where this task was completed
	 *
	 * @param  int  $task_id  Task ID
	 *
	 * @return array
	 */
	public function get_task_maintenance_logs($task_id)
	{
		$this->db->select('l.*, lt.is_completed, w.website_url, p.name as project_name, c.company as client_name, s.firstname, s.lastname');
		$this->db->from(db_prefix().'wmm_maintenance_log_tasks lt');
		$this->db->join(db_prefix().'wmm_maintenance_logs l', 'l.id = lt.log_id');
		$this->db->join(db_prefix().'wmm_websites w', 'w.id = l.website_id', 'left');
		$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
		$this->db->join(db_prefix().'clients c', 'c.userid = w.client_id', 'left');
		$this->db->join(db_prefix().'staff s', 's.staffid = l.performed_by', 'left');
		$this->db->where('lt.task_id', $task_id);
		$this->db->order_by('l.performed_at', 'DESC');

		return $this->db->get()->result_array();
	}

	/* ==================== UTILITY METHODS ==================== */

	/**
	 * Get priority name
	 *
	 * @param  string  $priority  Priority
	 *
	 * @return string
	 */
	public function get_priority_name($priority)
	{
		$priorities = [
			'low'    => _l('wmm_priority_low'),
			'medium' => _l('wmm_priority_medium'),
			'high'   => _l('wmm_priority_high'),
			'urgent' => _l('wmm_priority_urgent'),
		];

		return isset($priorities[$priority]) ? $priorities[$priority] : '';
	}

	/**
	 * Get category name by ID
	 *
	 * @param  int  $category_id  Category ID
	 *
	 * @return string
	 */
	public function get_category_name($category_id)
	{
		if ( ! $category_id)
		{
			return '';
		}

		$this->db->select('name');
		$this->db->where('id', $category_id);
		$category = $this->db->get(db_prefix().'wmm_categories')->row();

		return $category ? $category->name : '';
	}

}
