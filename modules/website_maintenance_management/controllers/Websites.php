<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Maintenance_websites_model $maintenance_websites_model
 */
class Websites extends AdminController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('maintenance_websites_model');
	}

	/**
	 * List all websites under maintenance
	 */
	public function index()
	{
		if (staff_cant('view', 'website_maintenance_websites'))
		{
			access_denied('website_maintenance_websites');
		}

		if ($this->input->is_ajax_request())
		{
			$this->app->get_table_data(module_views_path('website_maintenance_management', 'tables/websites_table'));
		}

		$this->load->model('clients_model');
		$data['clients'] = $this->clients_model->get();
		$data['title']   = _l('wmm_manage_websites');
		$this->load->view('websites/manage', $data);
	}

	/**
	 * Get projects by client (AJAX)
	 */
	public function get_projects_by_client($client_id)
	{
		if (staff_cant('view', 'website_maintenance_websites'))
		{
			ajax_access_denied();
		}

		$this->load->model('projects_model');
		$projects = $this->projects_model->get('', ['clientid' => $client_id]);

		echo json_encode($projects);
	}

	/**
	 * Add website to maintenance
	 */
	public function add()
	{
		if (staff_cant('create', 'website_maintenance_websites'))
		{
			access_denied('website_maintenance_websites');
		}

		if ($this->input->post())
		{
			$result = $this->maintenance_websites_model->add($this->input->post());

			if (is_array($result) && isset($result['error']))
			{
				set_alert('warning', $result['error']);
			} elseif ($result)
			{
				set_alert('success', _l('wmm_website_added_successfully'));
			} else
			{
				set_alert('danger', _l('wmm_website_add_failed'));
			}
		}

		redirect(admin_url('website_maintenance_management/websites'));
	}

	/**
	 * Delete website from maintenance
	 */
	public function delete($id)
	{
		if (staff_cant('delete', 'website_maintenance_websites'))
		{
			ajax_access_denied();
		}

		$response = $this->maintenance_websites_model->delete($id);

		if (is_array($response) && isset($response['error']))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => $response['error'],
			]);
		} elseif ($response)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('deleted', _l('wmm_website')),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('problem_deleting', _l('wmm_website')),
			]);
		}
	}

	/**
	 * Toggle website active status
	 */
	public function toggle_status($id, $status)
	{
		if (staff_cant('edit', 'website_maintenance_websites'))
		{
			ajax_access_denied();
		}

		$success = $this->maintenance_websites_model->toggle_active($id, $status);

		echo json_encode([
			'message' => $success ? _l('updated_successfully', _l('wmm_website')) : _l('wmm_problem_updating', _l('wmm_website')),
			'success' => $success,
		]);
	}

	/**
	 * Get website information (AJAX)
	 */
	public function get_website($id)
	{
		if (staff_cant('view', 'website_maintenance_websites'))
		{
			ajax_access_denied();
		}

		$website = $this->maintenance_websites_model->get($id);

		if ($website)
		{
			echo json_encode([
				'success' => TRUE,
				'website' => $website,
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('not_found', _l('wmm_website')),
			]);
		}
	}

}
