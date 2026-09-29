<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Maintenance_categories_model $maintenance_categories_model
 */
class Categories extends AdminController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('maintenance_categories_model');
	}

	/**
	 * List all categories
	 */
	public function index()
	{
		if (staff_cant('view', 'website_maintenance_categories'))
		{
			access_denied('website_maintenance_categories');
		}

		$data['title'] = _l('wmm_categories');
		$this->load->view('categories/manage', $data);
	}

	public function table()
	{
		if (staff_cant('view', 'website_maintenance_categories'))
		{
			if ($this->input->is_ajax_request())
			{
				ajax_access_denied();
			} else
			{
				access_denied('website_maintenance_categories');
			}
		}

		$this->app->get_table_data(module_views_path('website_maintenance_management', 'tables/categories_table'));
	}

	/**
	 * Add or edit category
	 */
	public function save($id = '')
	{
		if ($this->input->post())
		{
			if ($id == '')
			{
				if (staff_cant('create', 'website_maintenance_categories'))
				{
					access_denied('website_maintenance_categories');
				}
				$result = $this->maintenance_categories_model->add($this->input->post());
				if (is_array($result) && isset($result['error']))
				{
					set_alert('warning', $result['error']);
				} elseif ($result)
				{
					set_alert('success', _l('added_successfully', _l('wmm_category')));
				}
			} else
			{
				if (staff_cant('edit', 'website_maintenance_categories'))
				{
					access_denied('website_maintenance_categories');
				}
				$result = $this->maintenance_categories_model->update($this->input->post(), $id);
				if (is_array($result) && isset($result['error']))
				{
					set_alert('warning', $result['error']);
				} elseif ($result)
				{
					set_alert('success', _l('updated_successfully', _l('wmm_category')));
				}
			}
			redirect(admin_url('website_maintenance_management/categories'));
		}
	}

	/**
	 * Get category for editing
	 */
	public function get($id)
	{
		if (staff_cant('view', 'website_maintenance_categories'))
		{
			ajax_access_denied();
		}

		$category = $this->maintenance_categories_model->get($id);
		echo json_encode($category);
	}

	/**
	 * Delete category
	 */
	public function delete($id)
	{
		if (staff_cant('delete', 'website_maintenance_categories'))
		{
			ajax_access_denied();
		}

		$response = $this->maintenance_categories_model->delete($id);

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
				'message' => _l('deleted', _l('wmm_category')),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('problem_deleting', _l('wmm_category')),
			]);
		}
	}

	/**
	 * Toggle category active status
	 */
	public function toggle_status($id, $status)
	{
		if (staff_cant('edit', 'website_maintenance_categories'))
		{
			ajax_access_denied();
		}

		$success = $this->maintenance_categories_model->toggle_active($id, $status);
		echo json_encode([
			'success' => $success,
			'message' => _l('updated_successfully', _l('wmm_category')),
		]);
	}

}
