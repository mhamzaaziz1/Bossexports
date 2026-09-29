<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Package Usage Controller - View Only
 * Displays package usage history across all packages
 * @property Support_packages_model $support_packages_model
 */
class Package_usage extends AdminController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('support_packages_model');
	}

	/**
	 * View all package usage records
	 */
	public function index()
	{
		if (staff_cant('view', 'website_maintenance_packages'))
		{
			access_denied('website_maintenance_packages');
		}

		$data['title'] = _l('wmm_package_usage_history');
		$this->load->view('package_usage/manage', $data);
	}

	/**
	 * DataTable for package usage
	 */
	public function table()
	{
		if (staff_cant('view', 'website_maintenance_packages'))
		{
			if ($this->input->is_ajax_request())
			{
				ajax_access_denied();
			} else
			{
				access_denied('website_maintenance_packages');
			}
		}

		$this->app->get_table_data(module_views_path('website_maintenance_management', 'tables/package_usage_table'));
	}

	/**
	 * Get usage summary statistics (AJAX)
	 */
	public function get_summary()
	{
		if (staff_cant('view', 'website_maintenance_packages'))
		{
			ajax_access_denied();
		}

		// Get total usage records
		$this->db->select('COUNT(*) as total_records, SUM(hours_consumed) as total_hours_consumed');
		$this->db->from(db_prefix().'wmm_package_usage');
		$query = $this->db->get();
		$stats = $query->row();

		// Get usage this month
		$this->db->select('COUNT(*) as monthly_records, SUM(hours_consumed) as monthly_hours');
		$this->db->from(db_prefix().'wmm_package_usage');
		$this->db->where('MONTH(consumed_at)', date('m'));
		$this->db->where('YEAR(consumed_at)', date('Y'));
		$query_month = $this->db->get();
		$monthly    = $query_month->row();

		// Get unique packages with usage
		$this->db->select('COUNT(DISTINCT package_id) as active_packages_count');
		$this->db->from(db_prefix().'wmm_package_usage');
		$query_packages = $this->db->get();
		$packages_stats = $query_packages->row();

		echo json_encode([
			'success'                => TRUE,
			'summary'                => [
				'total_records'          => $stats->total_records ?? 0,
				'total_hours_consumed'   => number_format($stats->total_hours_consumed ?? 0, 2),
				'monthly_records'        => $monthly->monthly_records ?? 0,
				'monthly_hours'          => number_format($monthly->monthly_hours ?? 0, 2),
				'active_packages_count'  => $packages_stats->active_packages_count ?? 0,
			],
		]);
	}

	/**
	 * Export package usage to CSV
	 */
	public function export_csv()
	{
		if (staff_cant('view', 'website_maintenance_packages'))
		{
			access_denied('website_maintenance_packages');
		}

		$this->db->select(
			'pu.id, pu.package_id, sp.package_name,
			c.company as client_name,
			COALESCE(w.website_url, p.name) as website,
			pu.log_id, pu.hours_consumed, pu.consumed_at,
			st.firstname, st.lastname',
		);
		$this->db->from(db_prefix().'wmm_package_usage pu');
		$this->db->join(db_prefix().'wmm_support_packages sp', 'sp.id = pu.package_id', 'left');
		$this->db->join(db_prefix().'clients c', 'c.userid = sp.client_id', 'left');
		$this->db->join(db_prefix().'wmm_websites w', 'w.id = sp.website_id', 'left');
		$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
		$this->db->join(db_prefix().'wmm_maintenance_logs ml', 'ml.id = pu.log_id', 'left');
		$this->db->join(db_prefix().'staff st', 'st.staffid = ml.performed_by', 'left');
		$this->db->order_by('pu.consumed_at', 'DESC');

		$usage_records = $this->db->get()->result_array();

		// Generate CSV
		$filename = 'package_usage_'.date('Y-m-d').'.csv';

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="'.$filename.'"');

		$output = fopen('php://output', 'w');

		// CSV Headers
		fputcsv($output, [
			_l('id'),
			_l('wmm_package_name'),
			_l('wmm_customer'),
			_l('wmm_website'),
			_l('wmm_log_id'),
			_l('wmm_hours_consumed'),
			_l('wmm_consumed_at'),
			_l('wmm_consumed_by'),
		]);

		// CSV Data
		foreach ($usage_records as $record)
		{
			fputcsv($output, [
				$record['id'],
				$record['package_name'],
				$record['client_name'],
				$record['website'] ?: _l('wmm_all_client_websites'),
				$record['log_id'],
				$record['hours_consumed'],
				_dt($record['consumed_at']),
				$record['firstname'].' '.$record['lastname'],
			]);
		}

		fclose($output);
		exit;
	}
}
