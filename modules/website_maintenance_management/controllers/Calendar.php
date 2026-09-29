<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Calendar extends AdminController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('maintenance_tasks_model');
	}

	/**
	 * Calendar view for tasks
	 */
	public function index()
	{
		if (staff_cant('view', 'website_maintenance_logs'))
		{
			access_denied('website_maintenance_logs');
		}

		$data['title']         = _l('wmm_calendar');
		$data['staff_members'] = $this->staff_model->get('', ['active' => 1]);
		$this->load->view('calendar/calendar', $data);
	}

	/**
	 * Get calendar events (AJAX)
	 */
	public function events()
	{
		if (staff_cant('view', 'website_maintenance_logs'))
		{
			ajax_access_denied();
		}

		$start    = $this->input->get('start');
		$end      = $this->input->get('end');
		$staff_id = $this->input->get('staff_id');

		$this->db->select(
			'
            l.id,
            CONCAT("Maintenance: ", w.website_url) as title,
            l.performed_at as start,
            l.performed_by,
            w.website_url
        ',
		);
		$this->db->from(db_prefix().'wmm_maintenance_logs l');
		$this->db->join(db_prefix().'wmm_websites w', 'w.id = l.website_id');

		// Filter by date range
		if ($start && $end)
		{
			$this->db->where('l.performed_at >=', $start);
			$this->db->where('l.performed_at <=', $end);
		}

		// Filter by staff if specified
		if ($staff_id)
		{
			$this->db->where('l.performed_by', $staff_id);
		}

		$logs = $this->db->get()->result_array();

		$events = [];
		foreach ($logs as $log)
		{
			$events[] = [
				'id'              => $log['id'],
				'title'           => $log['title'],
				'start'           => $log['start'],
				'backgroundColor' => '#10b981',
				'borderColor'     => '#10b981',
				'url'             => admin_url('website_maintenance_management/maintenance_logs/view/'.$log['id']),
			];
		}

		echo json_encode($events);
	}

	/**
	 * Get priority color for calendar
	 */
	private function get_priority_color($priority)
	{
		$colors = [
			'low'    => '#64748b',
			'medium' => '#3b82f6',
			'high'   => '#f59e0b',
			'urgent' => '#ef4444',
		];

		return isset($colors[$priority]) ? $colors[$priority] : '#64748b';
	}

}
