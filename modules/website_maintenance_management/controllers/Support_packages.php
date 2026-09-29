<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Support_packages_model $support_packages_model
 * @property Maintenance_websites_model $maintenance_websites_model
 * @property Maintenance_tasks_model $maintenance_tasks_model
 * @property Clients_model $clients_model
 */
class Support_packages extends AdminController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('support_packages_model');
		$this->load->model('maintenance_websites_model');
		$this->load->model('maintenance_tasks_model');
		$this->load->model('clients_model');
	}

	/**
	 * List all support packages
	 */
	public function index()
	{
		if ( ! staff_can('view', 'website_maintenance_packages'))
		{
			access_denied('Support Packages');
		}

		$data['title'] = _l('wmm_support_packages');
		$this->load->view('support_packages/manage', $data);
	}

	/**
	 * Get packages data for datatable
	 */
	public function table()
	{
		if ( ! staff_can('view', 'website_maintenance_packages'))
		{
			ajax_access_denied();
		}

		$this->app->get_table_data(module_views_path(WEBSITE_MAINTENANCE_MODULE_NAME, 'tables/packages_table'));
	}

	/**
	 * View package details
	 *
	 * @param  int  $id  Package ID
	 */
	public function view($id)
	{
		if ( ! staff_can('view', 'website_maintenance_packages'))
		{
			access_denied('Support Packages');
		}

		$package = $this->support_packages_model->get($id);

		if ( ! $package)
		{
			show_404();
		}

		$websites        = $this->maintenance_websites_model->get_by_client($package->client_id);
		$website_options = [];
		foreach ($websites as $website)
		{
			if ($package->website_id && $website['id'] !== $package->website_id)
			{
				continue;
			}
			$website_options[] = [
				'id'          => $website['id'],
				'name'        => $website['website_url'],
				'client_id'   => $website['client_id'],
				'client_name' => $website['client_name'],
			];
		}
		$active_tasks = $this->maintenance_tasks_model->get_active_tasks();

		$data['package']         = $package;
		$data['usage_history']   = $this->support_packages_model->get_usage_history($id);
		$data['statistics']      = $this->support_packages_model->get_statistics($id);
		$data['title']           = _l('wmm_package_details').' #'.$id;
		$data['website_options'] = $website_options;
		$data['active_tasks']    = $active_tasks;

		$this->load->view('support_packages/view', $data);
	}

	/**
	 * Add new package (AJAX)
	 */
	public function add()
	{
		if ( ! staff_can('create', 'website_maintenance_packages'))
		{
			ajax_access_denied();
		}

		if ($this->input->post())
		{
			$data = $this->input->post();

			// Handle website_id null for client-wide packages
			if (isset($data['package_scope']) && $data['package_scope'] === 'client')
			{
				$data['website_id'] = NULL;
			}

			unset($data['package_scope']);

			$id = $this->support_packages_model->add($data);

			if ($id)
			{
				set_alert('success', _l('wmm_package_added_successfully'));
				echo json_encode([
					'success' => TRUE,
					'id'      => $id,
					'message' => _l('wmm_package_added_successfully'),
				]);
			} else
			{
				echo json_encode([
					'success' => FALSE,
					'message' => _l('wmm_package_add_failed'),
				]);
			}
		}
	}

	/**
	 * Edit package (AJAX)
	 *
	 * @param  int  $id  Package ID
	 */
	public function edit($id)
	{
		if ( ! staff_can('edit', 'website_maintenance_packages'))
		{
			ajax_access_denied();
		}

		if ($this->input->post())
		{
			$data = $this->input->post();

			// Handle website_id null for client-wide packages
			if (isset($data['package_scope']) && $data['package_scope'] === 'client')
			{
				$data['website_id'] = NULL;
			}

			unset($data['package_scope']);

			$success = $this->support_packages_model->update($data, $id);

			if ($success)
			{
				set_alert('success', _l('wmm_package_updated_successfully'));
				echo json_encode([
					'success' => TRUE,
					'message' => _l('wmm_package_updated_successfully'),
				]);
			} else
			{
				echo json_encode([
					'success' => FALSE,
					'message' => _l('wmm_package_update_failed'),
				]);
			}
		}
	}

	/**
	 * Delete package
	 *
	 * @param  int  $id  Package ID
	 */
	public function delete($id)
	{
		if ( ! staff_can('delete', 'website_maintenance_packages'))
		{
			ajax_access_denied();
		}

		$response = $this->support_packages_model->delete($id);

		if ($response === TRUE)
		{
			set_alert('success', _l('wmm_package_deleted_successfully'));
			echo json_encode(['success' => TRUE]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => is_array($response) ? $response['error'] : _l('wmm_package_delete_failed'),
			]);
		}
	}

	/**
	 * Get package data (AJAX)
	 *
	 * @param  int  $id  Package ID
	 */
	public function get_package($id)
	{
		if ( ! staff_can('view', 'website_maintenance_packages'))
		{
			ajax_access_denied();
		}

		$package = $this->support_packages_model->get($id);

		if ($package)
		{
			echo json_encode([
				'success' => TRUE,
				'package' => $package,
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('wmm_package_not_found'),
			]);
		}
	}

	/**
	 * Get active packages for a client/website (AJAX)
	 */
	public function get_active_packages()
	{
		if ( ! staff_can('view', 'website_maintenance_packages'))
		{
			ajax_access_denied();
		}

		$client_id  = $this->input->post('client_id');
		$website_id = $this->input->post('website_id');

		if ( ! $client_id)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('wmm_client_required'),
			]);

			return;
		}

		$packages = $this->support_packages_model->get_active_packages($client_id, $website_id);

		echo json_encode([
			'success'  => TRUE,
			'packages' => $packages,
		]);
	}

	/**
	 * Get projects by client (AJAX)
	 */
	public function get_client_projects()
	{
		if ( ! staff_can('view', 'website_maintenance_packages'))
		{
			ajax_access_denied();
		}

		$client_id = $this->input->post('client_id');

		if ( ! $client_id)
		{
			echo json_encode([]);

			return;
		}

		$this->db->where('clientid', $client_id);
		$this->db->where('status', 2); // Active projects
		$projects = $this->db->get(db_prefix().'projects')->result_array();

		echo json_encode($projects);
	}

	/**
	 * Get websites by client (AJAX)
	 */
	public function get_client_websites()
	{
		if ( ! staff_can('view', 'website_maintenance_packages'))
		{
			ajax_access_denied();
		}

		$client_id = $this->input->post('client_id');

		if ( ! $client_id)
		{
			echo json_encode([]);

			return;
		}

		$websites = $this->maintenance_websites_model->get_by_client($client_id);

		echo json_encode($websites);
	}

	/**
	 * Update package status (AJAX)
	 *
	 * @param  int  $id  Package ID
	 */
	public function update_status($id)
	{
		if ( ! staff_can('edit', 'website_maintenance_packages'))
		{
			ajax_access_denied();
		}

		$status = $this->input->post('status');

		if ( ! in_array($status, ['active', 'exhausted', 'expired', 'cancelled']))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('wmm_invalid_status'),
			]);

			return;
		}

		$success = $this->support_packages_model->update(['status' => $status], $id);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('wmm_package_status_updated'),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('wmm_package_update_failed'),
			]);
		}
	}

	/**
	 * Get package balance widget data (AJAX)
	 */
	public function get_balance_widget()
	{
		if ( ! staff_can('view', 'website_maintenance_packages'))
		{
			ajax_access_denied();
		}

		$client_id  = $this->input->get('client_id');
		$website_id = $this->input->get('website_id');

		if ( ! $client_id)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('wmm_client_required'),
			]);

			return;
		}

		$packages = $this->support_packages_model->get_active_packages($client_id, $website_id);

		$total_hours_remaining = 0;
		foreach ($packages as $package)
		{
			$total_hours_remaining += $package['hours_remaining'];
		}

		echo json_encode([
			'success'               => TRUE,
			'packages'              => $packages,
			'total_hours_remaining' => round($total_hours_remaining, 2),
		]);
	}

	/**
	 * Get summary statistics (AJAX)
	 */
	public function get_summary()
	{
		if ( ! staff_can('view', 'website_maintenance_packages'))
		{
			ajax_access_denied();
		}

		$summary = $this->support_packages_model->get_summary();

		echo json_encode([
			'success' => TRUE,
			'summary' => $summary,
		]);
	}

	/**
	 * Export packages to CSV
	 */
	public function export_csv()
	{
		if ( ! staff_can('view', 'website_maintenance_packages'))
		{
			access_denied('Support Packages');
		}

		$packages = $this->support_packages_model->get();

		// Set headers for CSV download
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=support-packages-'.date('Y-m-d').'.csv');

		$output = fopen('php://output', 'w');

		// Add BOM for UTF-8
		fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

		// CSV headers
		fputcsv($output, [
			_l('id'),
			_l('wmm_package_name'),
			_l('wmm_customer'),
			_l('wmm_website'),
			_l('wmm_total_hours'),
			_l('wmm_hours_used'),
			_l('wmm_hours_remaining'),
			_l('wmm_status'),
			_l('wmm_start_date'),
			_l('wmm_expiry_date'),
			_l('wmm_created_at'),
		]);

		// CSV data
		foreach ($packages as $package)
		{
			fputcsv($output, [
				$package['id'],
				$package['package_name'],
				$package['client_name'],
				$package['website_url'] ?: $package['project_name'] ?: _l('wmm_all_client_websites'),
				$package['total_hours'],
				$package['hours_used'],
				$package['hours_remaining'],
				ucfirst($package['status']),
				$package['start_date'],
				$package['expiry_date'],
				$package['created_at'],
			]);
		}

		fclose($output);
		exit();
	}

}
