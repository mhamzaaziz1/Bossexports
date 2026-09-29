<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Maintenance_analytics_model extends App_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('website_maintenance_management/maintenance_tasks_model');
		$this->load->model('website_maintenance_management/maintenance_logs_model');
	}

	/**
	 * Get task statistics
	 *
	 * @param  array  $filters  Date range, staff, etc.
	 *
	 * @return array
	 */
	public function get_task_stats($filters = [])
	{
		$date_from = isset($filters['date_from']) ? $filters['date_from'] : date('Y-m-01');
		$date_to   = isset($filters['date_to']) ? $filters['date_to'] : date('Y-m-t');

		$stats = [];

		// Total tasks
		$this->db->where('created_at >=', $date_from.' 00:00:00');
		$this->db->where('created_at <=', $date_to.' 23:59:59');
		$stats['total_tasks'] = $this->db->count_all_results(db_prefix().'wmm_maintenance_tasks');

		// Active tasks
		$this->db->where('is_active', 1);
		$this->db->where('created_at >=', $date_from.' 00:00:00');
		$this->db->where('created_at <=', $date_to.' 23:59:59');
		$stats['active_tasks'] = $this->db->count_all_results(db_prefix().'wmm_maintenance_tasks');

		// Inactive tasks
		$this->db->where('is_active', 0);
		$this->db->where('created_at >=', $date_from.' 00:00:00');
		$this->db->where('created_at <=', $date_to.' 23:59:59');
		$stats['inactive_tasks'] = $this->db->count_all_results(db_prefix().'wmm_maintenance_tasks');

		return $stats;
	}

	/**
	 * Get time tracking statistics - based on maintenance logs
	 *
	 * @param  array  $filters
	 *
	 * @return array
	 */
	public function get_time_stats($filters = [])
	{
		$date_from = isset($filters['date_from']) ? $filters['date_from'] : date('Y-m-01');
		$date_to   = isset($filters['date_to']) ? $filters['date_to'] : date('Y-m-t');
		$staff_id  = isset($filters['staff_id']) ? $filters['staff_id'] : NULL;

		$stats = [];

		// Build base query from maintenance logs
		$this->db->select(
			'
            COUNT(id) as total_entries,
            SUM(time_spent) as total_seconds,
            SUM(time_spent) / 3600 as total_hours
        ',
		);
		$this->db->from(db_prefix().'wmm_maintenance_logs');
		$this->db->where('is_completed', 1);

		if ($date_from)
		{
			$this->db->where('performed_at >=', $date_from.' 00:00:00');
		}
		if ($date_to)
		{
			$this->db->where('performed_at <=', $date_to.' 23:59:59');
		}
		if ($staff_id)
		{
			$this->db->where('performed_by', $staff_id);
		}

		$result = $this->db->get()->row_array();

		return [
			'total_entries' => $result['total_entries'] ?: 0,
			'total_hours'   => $result['total_hours'] ?: 0,
			'total_seconds' => $result['total_seconds'] ?: 0,
		];
	}

	/**
	 * Get staff productivity - based on maintenance logs
	 *
	 * @param  array  $filters
	 *
	 * @return array
	 */
	public function get_staff_productivity($filters = [])
	{
		$date_from = isset($filters['date_from']) ? $filters['date_from'] : date('Y-m-01');
		$date_to   = isset($filters['date_to']) ? $filters['date_to'] : date('Y-m-t');

		$this->db->select(
			'
            s.staffid,
            s.firstname,
            s.lastname,
            CONCAT(s.firstname, " ", s.lastname) as full_name,
            COUNT(l.id) as maintenance_count,
            SUM(l.time_spent) / 3600 as total_hours,
            AVG(l.time_spent) / 3600 as avg_time_per_log
        ',
		);
		$this->db->from(db_prefix().'staff s');
		$this->db->join(db_prefix().'wmm_maintenance_logs l', 'l.performed_by = s.staffid', 'left');

		$this->db->where('(l.performed_at BETWEEN "'.$date_from.' 00:00:00" AND "'.$date_to.' 23:59:59" OR l.performed_at IS NULL)');
		$this->db->where('(l.is_completed = 1 OR l.is_completed IS NULL)');
		$this->db->group_by('s.staffid');
		$this->db->order_by('total_hours', 'DESC');

		return $this->db->get()->result_array();
	}

	/**
	 * Get maintenance logs statistics
	 *
	 * @param  array  $filters
	 *
	 * @return array
	 */
	public function get_maintenance_stats($filters = [])
	{
		$date_from = isset($filters['date_from']) ? $filters['date_from'] : date('Y-m-01');
		$date_to   = isset($filters['date_to']) ? $filters['date_to'] : date('Y-m-t');

		$stats = [];

		// Total logs
		if ($date_from && $date_to)
		{
			$this->db->where('performed_at >=', $date_from.' 00:00:00');
			$this->db->where('performed_at <=', $date_to.' 23:59:59');
		}
		$stats['total_logs'] = $this->db->count_all_results(db_prefix().'wmm_maintenance_logs');

		// Email sent vs not sent
		$this->db->where('email_sent', 1);
		if ($date_from && $date_to)
		{
			$this->db->where('performed_at >=', $date_from.' 00:00:00');
			$this->db->where('performed_at <=', $date_to.' 23:59:59');
		}
		$stats['emails_sent'] = $this->db->count_all_results(db_prefix().'wmm_maintenance_logs');

		// Websites maintained
		$this->db->select('COUNT(DISTINCT website_id) as count');
		$this->db->from(db_prefix().'wmm_maintenance_logs');
		if ($date_from && $date_to)
		{
			$this->db->where('performed_at >=', $date_from.' 00:00:00');
			$this->db->where('performed_at <=', $date_to.' 23:59:59');
		}
		$result                       = $this->db->get()->row();
		$stats['websites_maintained'] = $result ? $result->count : 0;

		return $stats;
	}

	/**
	 * Get tasks by category breakdown
	 *
	 * @return array
	 */
	public function get_tasks_by_category()
	{
		$this->db->select(db_prefix().'wmm_categories.name as name, COUNT('.db_prefix().'wmm_maintenance_tasks.id) as count');
		$this->db->from(db_prefix().'wmm_maintenance_tasks');
		$this->db->join(db_prefix().'wmm_categories', db_prefix().'wmm_categories.id = wmm_maintenance_tasks.category', 'left');
		$this->db->where(db_prefix().'wmm_maintenance_tasks.is_active', 1);
		$this->db->group_by('category');

		return $this->db->get()->result_array();
	}

	/**
	 * Get tasks by priority breakdown
	 *
	 * @return array
	 */
	public function get_tasks_by_priority()
	{
		$this->db->select('priority, COUNT(id) as count');
		$this->db->from(db_prefix().'wmm_maintenance_tasks');
		$this->db->where('is_active', 1);
		$this->db->group_by('priority');

		return $this->db->get()->result_array();
	}

	/**
	 * Get maintenance completion trend (monthly) - based on logs
	 *
	 * @param  int  $months  Number of months back
	 *
	 * @return array
	 */
	public function get_completion_trend($months = 6)
	{
		$data = [];

		for ($i = $months - 1; $i >= 0; $i--)
		{
			$month_start = date('Y-m-01', strtotime("-$i months"));
			$month_end   = date('Y-m-t', strtotime("-$i months"));

			// Count maintenance logs (completed maintenances) instead
			$this->db->where('performed_at >=', $month_start.' 00:00:00');
			$this->db->where('performed_at <=', $month_end.' 23:59:59');
			$this->db->where('is_completed', 1);
			$count = $this->db->count_all_results(db_prefix().'wmm_maintenance_logs');

			$data[] = [
				'month'       => date('M Y', strtotime($month_start)),
				'month_short' => date('M', strtotime($month_start)),
				'count'       => $count,
			];
		}

		return $data;
	}

	/**
	 * Get time logged trend (weekly for last 4 weeks) - based on maintenance logs
	 *
	 * @return array
	 */
	public function get_time_logged_trend()
	{
		$data = [];

		for ($i = 3; $i >= 0; $i--)
		{
			$week_start = date('Y-m-d', strtotime("-$i weeks monday"));
			$week_end   = date('Y-m-d', strtotime("-$i weeks sunday"));

			$this->db->select('SUM(time_spent) / 3600 as total_hours');
			$this->db->from(db_prefix().'wmm_maintenance_logs');
			$this->db->where('performed_at >=', $week_start.' 00:00:00');
			$this->db->where('performed_at <=', $week_end.' 23:59:59');
			$this->db->where('is_completed', 1);
			$result = $this->db->get()->row();

			$data[] = [
				'week'  => date('M d', strtotime($week_start)).' - '.date('M d', strtotime($week_end)),
				'hours' => $result ? ($result->total_hours ?: 0) : 0,
			];
		}

		return $data;
	}

	/**
	 * Get top performing staff members - based on maintenance logs
	 *
	 * @param  int  $limit
	 * @param  array  $filters
	 *
	 * @return array
	 */
	public function get_top_staff($limit = 5, $filters = [])
	{
		$date_from = isset($filters['date_from']) ? $filters['date_from'] : date('Y-m-01');
		$date_to   = isset($filters['date_to']) ? $filters['date_to'] : date('Y-m-t');

		$this->db->select(
			'
            s.staffid,
            s.firstname,
            s.lastname,
            CONCAT(s.firstname, " ", s.lastname) as full_name,
            COUNT(l.id) as maintenance_count,
            SUM(l.time_spent) / 3600 as hours_logged
        ',
		);
		$this->db->from(db_prefix().'staff s');
		$this->db->join(db_prefix().'wmm_maintenance_logs l', 'l.performed_by = s.staffid', 'left');

		if ($date_from && $date_to)
		{
			$this->db->where('(l.performed_at BETWEEN "'.$date_from.' 00:00:00" AND "'.$date_to.' 23:59:59" OR l.performed_at IS NULL)');
		}

		$this->db->where('(l.is_completed = 1 OR l.is_completed IS NULL)');
		$this->db->group_by('s.staffid');
		$this->db->order_by('maintenance_count', 'DESC');
		$this->db->limit($limit);

		return $this->db->get()->result_array();
	}

	/**
	 * Get most maintained websites
	 *
	 * @param  int  $limit
	 * @param  array  $filters
	 *
	 * @return array
	 */
	public function get_most_maintained_websites($limit = 10, $filters = [])
	{
		$date_from = isset($filters['date_from']) ? $filters['date_from'] : date('Y-m-01');
		$date_to   = isset($filters['date_to']) ? $filters['date_to'] : date('Y-m-t');

		$this->db->select(
			'
            w.id,
            w.website_url,
            p.name as project_name,
            c.company as client_name,
            COUNT(l.id) as maintenance_count,
            MAX(l.performed_at) as last_maintenance
        ',
		);
		$this->db->from(db_prefix().'wmm_websites w');
		$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
		$this->db->join(db_prefix().'clients c', 'c.userid = w.client_id', 'left');
		$this->db->join(db_prefix().'wmm_maintenance_logs l', 'l.website_id = w.id', 'left');

		if ($date_from && $date_to)
		{
			$this->db->where('(l.performed_at BETWEEN "'.$date_from.' 00:00:00" AND "'.$date_to.' 23:59:59" OR l.performed_at IS NULL)');
		}

		$this->db->group_by('w.id');
		$this->db->order_by('maintenance_count', 'DESC');
		$this->db->limit($limit);

		return $this->db->get()->result_array();
	}

	/**
	 * Get dashboard summary
	 *
	 * @return array
	 */
	public function get_dashboard_summary()
	{
		$summary = [];

		// My assigned tasks
		$this->db->where('staffid', get_staff_user_id());
		$assigned_task_ids = $this->db->get(db_prefix().'wmm_task_assigned')->result_array();
		$task_ids          = array_column($assigned_task_ids, 'task_id');

		if ( ! empty($task_ids))
		{
			$this->db->where_in('id', $task_ids);
			$this->db->where('is_active', 1);
			$summary['my_tasks'] = $this->db->count_all_results(db_prefix().'wmm_maintenance_tasks');
		} else
		{
			$summary['my_tasks'] = 0;
		}

		// My maintenance logs this month
		$month_start = date('Y-m-01');
		$month_end   = date('Y-m-t');

		$this->db->where('performed_by', get_staff_user_id());
		$this->db->where('performed_at >=', $month_start.' 00:00:00');
		$this->db->where('performed_at <=', $month_end.' 23:59:59');
		$summary['my_logs_this_month'] = $this->db->count_all_results(db_prefix().'wmm_maintenance_logs');

		// My in-progress maintenance logs
		$this->db->where('performed_by', get_staff_user_id());
		$this->db->where('is_completed', 0);
		$summary['my_in_progress'] = $this->db->count_all_results(db_prefix().'wmm_maintenance_logs');

		// Time logged this week (from maintenance logs)
		$week_start = date('Y-m-d', strtotime('monday this week'));
		$week_end   = date('Y-m-d', strtotime('sunday this week'));

		$this->db->select('SUM(time_spent) / 3600 as hours');
		$this->db->from(db_prefix().'wmm_maintenance_logs');
		$this->db->where('performed_by', get_staff_user_id());
		$this->db->where('performed_at >=', $week_start.' 00:00:00');
		$this->db->where('performed_at <=', $week_end.' 23:59:59');
		$this->db->where('is_completed', 1);
		$result                     = $this->db->get()->row();
		$summary['hours_this_week'] = $result ? ($result->hours ?: 0) : 0;

		// Active maintenance (in progress)
		$this->db->where('performed_by', get_staff_user_id());
		$this->db->where('is_completed', 0);
		$summary['has_active_maintenance'] = $this->db->count_all_results(db_prefix().'wmm_maintenance_logs') > 0;

		return $summary;
	}

	/**
	 * Export maintenance logs report to CSV
	 *
	 * @param  array  $filters
	 *
	 * @return string CSV content
	 */
	public function export_time_tracking_csv($filters = [])
	{
		$date_from = isset($filters['date_from']) ? $filters['date_from'] : date('Y-m-01');
		$date_to   = isset($filters['date_to']) ? $filters['date_to'] : date('Y-m-t');

		$this->db->select(
			'
            l.id,
            w.website_url,
            p.name as project_name,
            c.company as client_name,
            CONCAT(s.firstname, " ", s.lastname) as staff_name,
            l.start_time,
            l.end_time,
            ROUND(l.time_spent / 3600, 2) as hours,
            l.performed_at,
            l.notes
        ',
		);
		$this->db->from(db_prefix().'wmm_maintenance_logs l');
		$this->db->join(db_prefix().'wmm_websites w', 'w.id = l.website_id', 'left');
		$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
		$this->db->join(db_prefix().'clients c', 'c.userid = w.client_id', 'left');
		$this->db->join(db_prefix().'staff s', 's.staffid = l.performed_by', 'left');
		$this->db->where('l.is_completed', 1);

		if ($date_from)
		{
			$this->db->where('l.performed_at >=', $date_from.' 00:00:00');
		}
		if ($date_to)
		{
			$this->db->where('l.performed_at <=', $date_to.' 23:59:59');
		}

		$this->db->order_by('l.performed_at', 'DESC');
		$logs = $this->db->get()->result_array();

		// Generate CSV
		$csv = "ID,Website,Project,Client,Tasks,Staff,Performed At,Start Time,End Time,Hours,Notes\n";

		foreach ($logs as $row)
		{
			$this->db->select('name');
			$this->db->where('log_id', $row['id']);
			$this->db->join(db_prefix().'wmm_maintenance_tasks', db_prefix().'wmm_maintenance_log_tasks.task_id='.db_prefix().'wmm_maintenance_tasks.id');
			$tasks      = $this->db->get(db_prefix().'wmm_maintenance_log_tasks')->result_array();
			$tasks      = array_values($tasks);
			$tasks_name = implode(', ', array_column($tasks, 'name'));

			$csv .= implode(',', [
					$row['id'],
					'"'.str_replace('"', '""', $row['website_url'] ?: '').'"',
					'"'.str_replace('"', '""', $row['project_name'] ?: '').'"',
					'"'.str_replace('"', '""', $row['client_name'] ?: '').'"',
					'"'.str_replace('"', '""', $tasks_name).'"',
					'"'.str_replace('"', '""', $row['staff_name']).'"',
					$row['performed_at'],
					$row['start_time'] ?: '',
					$row['end_time'] ?: '',
					$row['hours'] ?: 0,
					'"'.str_replace('"', '""', $row['notes'] ?: '').'"',
				])."\n";
		}

		return $csv;
	}

}
