<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Maintenance_analytics_model $maintenance_analytics_model
 */
class Reports extends AdminController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('maintenance_analytics_model');
	}

	/**
	 * Reports dashboard
	 */
	public function index()
	{
		if (staff_cant('view', 'website_maintenance_reports'))
		{
			access_denied('website_maintenance_reports');
		}

		$data['title']         = _l('wmm_reports_analytics');
		$data['staff_members'] = $this->staff_model->get('', ['active' => 1]);

		// Default filters
		$date_from = $this->input->get('date_from') ?: date('Y-m-01');
		$date_to   = $this->input->get('date_to') ?: date('Y-m-t');

		$filters = [
			'date_from' => $date_from,
			'date_to'   => $date_to,
		];

		// Get analytics data
		$data['task_stats']         = $this->maintenance_analytics_model->get_task_stats($filters);
		$data['time_stats']         = $this->maintenance_analytics_model->get_time_stats($filters);
		$data['maintenance_stats']  = $this->maintenance_analytics_model->get_maintenance_stats($filters);
		$data['staff_productivity'] = $this->maintenance_analytics_model->get_staff_productivity($filters);
		$data['tasks_by_category']  = $this->maintenance_analytics_model->get_tasks_by_category();
		$data['tasks_by_priority']  = $this->maintenance_analytics_model->get_tasks_by_priority();
		$data['completion_trend']   = $this->maintenance_analytics_model->get_completion_trend(6);
		$data['time_trend']         = $this->maintenance_analytics_model->get_time_logged_trend();
		$data['top_staff']          = $this->maintenance_analytics_model->get_top_staff(5, $filters);
		$data['most_maintained']    = $this->maintenance_analytics_model->get_most_maintained_websites(10, $filters);

		$data['filters'] = $filters;

		$this->load->view('reports/reports', $data);
	}

	/**
	 * Time tracking report
	 */
	public function time_tracking()
	{
		if (staff_cant('view', 'website_maintenance_reports'))
		{
			access_denied('website_maintenance_reports');
		}

		$date_from = $this->input->get('date_from') ?: date('Y-m-01');
		$date_to   = $this->input->get('date_to') ?: date('Y-m-t');
		$staff_id  = $this->input->get('staff_id');

		$filters = [
			'date_from' => $date_from,
			'date_to'   => $date_to,
			'staff_id'  => $staff_id,
		];

		$data['title']              = _l('wmm_time_tracking_report');
		$data['time_stats']         = $this->maintenance_analytics_model->get_time_stats($filters);
		$data['staff_productivity'] = $this->maintenance_analytics_model->get_staff_productivity($filters);
		$data['filters']            = $filters;
		$data['staff_members']      = $this->staff_model->get('', ['active' => 1]);

		$this->load->view('reports/time_tracking', $data);
	}

	/**
	 * Export time tracking report
	 */
	public function export_time_tracking()
	{
		if (staff_cant('view', 'website_maintenance_reports'))
		{
			access_denied('website_maintenance_reports');
		}

		$date_from = $this->input->get('date_from') ?: date('Y-m-01');
		$date_to   = $this->input->get('date_to') ?: date('Y-m-t');

		$filters = [
			'date_from' => $date_from,
			'date_to'   => $date_to,
		];

		$csv = $this->maintenance_analytics_model->export_time_tracking_csv($filters);

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="time_tracking_'.date('Y-m-d').'.csv"');
		echo $csv;
		exit;
	}

}
