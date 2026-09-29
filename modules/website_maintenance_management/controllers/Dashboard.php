<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends AdminController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('maintenance_analytics_model');
	}

	/**
	 * Module dashboard
	 */
	public function index()
	{
		if (staff_cant('view', 'website_maintenance_reports'))
		{
			access_denied('website_maintenance_reports');
		}

		$data['title']   = _l('wmm_dashboard');
		$data['summary'] = $this->maintenance_analytics_model->get_dashboard_summary();

		$this->load->view('dashboard/dashboard', $data);
	}

}
