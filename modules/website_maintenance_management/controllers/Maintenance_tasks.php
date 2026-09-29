<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Maintenance_tasks_model $maintenance_tasks_model
 * @property Maintenance_analytics_model $maintenance_analytics_model
 * @property Maintenance_categories_model $maintenance_categories_model
 * @property Staff_model $staff_model
 */
class Maintenance_tasks extends AdminController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('maintenance_tasks_model');
		$this->load->model('maintenance_analytics_model');
		$this->load->model('maintenance_categories_model');
		$this->load->model('staff_model');
	}

	/**
	 * List all maintenance tasks
	 */
	public function index()
	{
		if (staff_cant('view', 'website_maintenance_tasks'))
		{
			access_denied('website_maintenance_tasks');
		}

		$data['title'] = _l('wmm_maintenance_tasks');
		$this->load->view('maintenance_tasks/manage', $data);
	}

	public function table()
	{
		if (staff_cant('view', 'website_maintenance_tasks'))
		{
			ajax_access_denied();
		}

		if ($this->input->is_ajax_request())
		{
			$this->app->get_table_data(module_views_path('website_maintenance_management', 'tables/tasks_table'));
		} else
		{
			redirect(admin_url(WEBSITE_MAINTENANCE_MODULE_NAME.'/maintenance_tasks'));
			exit();
		}
	}

	/**
	 * Add or edit maintenance task
	 */
	public function save($id = '')
	{
		if ($this->input->post())
		{
			if ($id == '')
			{
				if (staff_cant('create', 'website_maintenance_tasks'))
				{
					access_denied('website_maintenance_tasks');
				}
				$id = $this->maintenance_tasks_model->add($this->input->post());
				if ($id)
				{
					set_alert('success', _l('added_successfully', _l('wmm_maintenance_task')));
				}
			} else
			{
				if (staff_cant('edit', 'website_maintenance_tasks'))
				{
					access_denied('website_maintenance_tasks');
				}
				$success = $this->maintenance_tasks_model->update($this->input->post(), $id);
				if ($success)
				{
					set_alert('success', _l('updated_successfully', _l('wmm_maintenance_task')));
				}
			}
			redirect(admin_url('website_maintenance_management/maintenance_tasks'));
		}
	}

	/**
	 * Get task for editing
	 */
	public function get($id)
	{
		if (staff_cant('view', 'website_maintenance_tasks'))
		{
			ajax_access_denied();
		}

		$task = $this->maintenance_tasks_model->get($id);
		echo json_encode($task);
	}

	/**
	 * Delete maintenance task
	 */
	public function delete($id)
	{
		if (staff_cant('delete', 'website_maintenance_tasks'))
		{
			ajax_access_denied();
		}

		$response = $this->maintenance_tasks_model->delete($id);

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
				'message' => _l('deleted', _l('wmm_maintenance_task')),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('problem_deleting', _l('wmm_maintenance_task')),
			]);
		}
	}

	/**
	 * View task details
	 */
	public function view($id)
	{
		if (staff_cant('view', 'website_maintenance_tasks'))
		{
			access_denied('website_maintenance_tasks');
		}

		$task = $this->maintenance_tasks_model->get($id);

		if ( ! $task)
		{
			show_404();
		}

		$data['task']       = $task;
		$data['title']      = $task->name;
		$data['categories'] = $this->maintenance_categories_model->get_active();

		$staff_members         = $this->staff_model->get('', ['active' => 1]);
		$data['staff_members'] = $staff_members;

		$staff_array = [];
		foreach ($staff_members as $staff)
		{
			$staff_array[] = [
				'id'   => $staff['staffid'],
				'name' => $staff['firstname'].' '.$staff['lastname'],
			];
		}
		$data['staff_array'] = $staff_array;
		$data['assignees']   = array_map(function ($staff) {
			return $staff['staffid'];
		}, $task->assignees);
		$this->load->view('maintenance_tasks/task_view', $data);
	}

}
