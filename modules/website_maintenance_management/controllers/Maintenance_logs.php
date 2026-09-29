<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Maintenance_tasks_model $maintenance_tasks_model
 * @property Maintenance_logs_model $maintenance_logs_model
 * @property Maintenance_attachments_model $maintenance_attachments_model
 * @property Maintenance_websites_model $maintenance_websites_model
 */
class Maintenance_logs extends AdminController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('maintenance_tasks_model');
		$this->load->model('maintenance_logs_model');
		$this->load->model('maintenance_attachments_model');
		$this->load->model('maintenance_websites_model');
	}

	/**
	 * View maintenance logs
	 */
	public function index()
	{
		if (staff_cant('view', 'website_maintenance_logs'))
		{
			access_denied('website_maintenance_logs');
		}

		$websites        = $this->maintenance_websites_model->get();
		$website_options = [];
		foreach ($websites as $website)
		{
			$website_options[] = [
				'id'          => $website['id'],
				'name'        => $website['website_url'],
				'client_id'   => $website['client_id'],
				'client_name' => $website['client_name'],
			];
		}
		$active_tasks = $this->maintenance_tasks_model->get_active_tasks();

		$data['bodyclass']       = 'maintenance-logs-page';
		$data['title']           = _l('wmm_maintenance_logs');
		$data['website_options'] = $website_options;
		$data['active_tasks']    = $active_tasks;

		$this->load->view('maintenance_logs/manage', $data);
	}

	public function table()
	{
		if (staff_cant('view', 'website_maintenance_logs'))
		{
			if ($this->input->is_ajax_request())
			{
				ajax_access_denied();
			} else
			{
				access_denied('website_maintenance_logs');
			}
		}

		$this->app->get_table_data(module_views_path('website_maintenance_management', 'tables/logs_table'));
	}

	/**
	 * Log maintenance
	 */
	public function log()
	{
		if (staff_cant('create', 'website_maintenance_logs'))
		{
			ajax_access_denied();
		}

		if ($this->input->post())
		{
			$result = $this->maintenance_logs_model->add($this->input->post());

			if ($result)
			{
				echo json_encode([
					'success' => TRUE,
					'message' => _l('wmm_maintenance_logged_successfully'),
					'log_id'  => $result,
				]);
			} else
			{
				echo json_encode([
					'success' => FALSE,
					'message' => _l('wmm_maintenance_log_failed'),
				]);
			}
		}
	}

	/**
	 * View single log details
	 */
	public function view($id)
	{
		if (staff_cant('view', 'website_maintenance_logs'))
		{
			access_denied('website_maintenance_logs');
		}

		$log = $this->maintenance_logs_model->get($id);

		if ( ! $log)
		{
			show_404();
		}

		// Get attachments
		$attachments = $this->maintenance_attachments_model->get($id);

		// If AJAX request, load as modal content
		if ($this->input->is_ajax_request())
		{
			$data['log']         = $log;
			$data['tasks']       = $this->maintenance_logs_model->get_log_tasks($id);
			$data['attachments'] = $attachments;
			$this->load->view('maintenance_logs/log_modal', $data);
		} else
		{
			// Regular page view
			$data['log']         = $log;
			$data['tasks']       = $this->maintenance_logs_model->get_log_tasks($id);
			$data['attachments'] = $attachments;
			$data['title']       = _l('wmm_maintenance_log_details');
			$this->load->view('maintenance_logs/log_details', $data);
		}
	}

	/**
	 * Delete maintenance log
	 */
	public function delete($id)
	{
		if (staff_cant('delete', 'website_maintenance_logs'))
		{
			ajax_access_denied();
		}

		// Delete attachments first
		$this->maintenance_attachments_model->delete_all($id);

		$response = $this->maintenance_logs_model->delete($id);

		// Return JSON response
		if ($response)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('deleted', _l('wmm_maintenance_log')),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('problem_deleting', _l('wmm_maintenance_log')),
			]);
		}
	}

	/**
	 * Resend notification email
	 */
	public function resend_notification($log_id)
	{
		if (staff_cant('edit', 'website_maintenance_logs'))
		{
			ajax_access_denied();
		}

		// Get log to check if completed
		$log = $this->maintenance_logs_model->get($log_id);

		if ( ! $log)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('wmm_log_not_found'),
			]);

			return;
		}

		// Send appropriate email based on completion status
		if ($log->is_completed)
		{
			$success = $this->maintenance_logs_model->send_maintenance_completed_notification($log_id);
		} else
		{
			$success = $this->maintenance_logs_model->send_maintenance_started_notification($log_id);
		}

		echo json_encode([
			'success' => $success,
			'message' => $success ? _l('wmm_email_sent_successfully') : _l('wmm_email_send_failed'),
		]);
	}

	/**
	 * Stop timer for maintenance log
	 */
	public function stop_timer($log_id)
	{
		if (staff_cant('edit', 'website_maintenance_logs'))
		{
			ajax_access_denied();
		}

		$success = $this->maintenance_logs_model->stop_timer($log_id);

		echo json_encode([
			'success' => $success,
			'message' => $success ? _l('wmm_timer_stopped_successfully') : _l('wmm_timer_stop_failed'),
		]);
	}

	/* ==================== ATTACHMENT METHODS ==================== */

	/**
	 * Upload file
	 */
	public function upload_file()
	{
		// Check permission - can be either create or edit
		if (staff_cant('create', 'website_maintenance_logs') && staff_cant('edit', 'website_maintenance_logs'))
		{
			ajax_access_denied();
		}

		$log_id = $this->input->post('log_id');

		if ( ! $log_id)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => 'Log ID is required',
			]);

			return;
		}

		$path = $this->maintenance_attachments_model->get_upload_path();
		_maybe_create_upload_path($path);

		$uploaded_files = $this->handle_maintenance_log_attachments_array($log_id);
		if ( ! empty($uploaded_files))
		{
			foreach ($uploaded_files as $file)
			{
				$this->maintenance_attachments_model->add($log_id, [
					'file_name' => $file['file_name'],
					'filetype'  => $file['filetype'],
				]);
			}

			echo json_encode([
				'success' => TRUE,
				'log_id'  => $log_id,
				'files'   => $uploaded_files,
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => 'No files uploaded',
			]);
		}
	}

	public function handle_maintenance_log_attachments_array($log_id)
	{
		$uploaded_files = [];
		$path           = $this->maintenance_attachments_model->get_upload_path().$log_id.'/';

		if (isset($_FILES['file']['name'])
		    && ($_FILES['file']['name'] != '' || is_array($_FILES['file']['name']) && count($_FILES['file']['name']) > 0)
		)
		{
			if ( ! is_array($_FILES['file']['name']))
			{
				$_FILES['file']['name']     = [$_FILES['file']['name']];
				$_FILES['file']['type']     = [$_FILES['file']['type']];
				$_FILES['file']['tmp_name'] = [$_FILES['file']['tmp_name']];
				$_FILES['file']['error']    = [$_FILES['file']['error']];
				$_FILES['file']['size']     = [$_FILES['file']['size']];
			}

			_file_attachments_index_fix('file');
			for ($i = 0; $i < count($_FILES['file']['name']); $i++)
			{
				// Get the temp file path
				$tmpFilePath = $_FILES['file']['tmp_name'][$i];

				// Make sure we have a filepath
				if ( ! empty($tmpFilePath) && $tmpFilePath != '')
				{
					if (_perfex_upload_error($_FILES['file']['error'][$i])
					    || ! _upload_extension_allowed($_FILES['file']['name'][$i])
					)
					{
						continue;
					}

					_maybe_create_upload_path($path);
					$filename    = time().'_'.md5($log_id).'_'.$_FILES['file']['name'][$i];
					$newFilePath = $path.$filename;

					// Upload the file into the temp dir
					if (move_uploaded_file($tmpFilePath, $newFilePath))
					{
						array_push($uploaded_files, [
							'file_name' => $filename,
							'filetype'  => $_FILES['file']['type'][$i],
						]);

						if (is_image($newFilePath))
						{
							create_img_thumb($path, $filename);
						}
					}
				}
			}
		}

		if (count($uploaded_files) > 0)
		{
			return $uploaded_files;
		}

		return FALSE;
	}

	/**
	 * Delete attachment
	 */
	public function delete_attachment($id, $log_id)
	{
		if (staff_cant('delete', 'website_maintenance_logs'))
		{
			ajax_access_denied();
		}

		$success = $this->maintenance_attachments_model->delete($id, $log_id);

		echo json_encode(['success' => $success]);
	}

	/**
	 * Download attachment
	 */
	public function download_attachment($id, $log_id)
	{
		$attachment = $this->maintenance_attachments_model->get($log_id, $id);

		if ( ! $attachment)
		{
			show_404();
		}

		$file_path = $this->maintenance_attachments_model->get_upload_path().$log_id.'/'.$attachment->file_name;

		if ( ! file_exists($file_path))
		{
			show_404();
		}

		$this->load->helper('download');
		force_download($file_path, NULL);
	}

	/**
	 * Download all attachments
	 */
	public function download_all_attachments($log_id)
	{
		$attachments = $this->maintenance_attachments_model->get($log_id);

		if (empty($attachments))
		{
			show_404();
		}

		$this->load->library('zip');
		$path = $this->maintenance_attachments_model->get_upload_path().$log_id.'/';

		foreach ($attachments as $attachment)
		{
			$file_path = $path.$attachment['file_name'];
			if (file_exists($file_path))
			{
				$this->zip->read_file($file_path);
			}
		}

		$log      = $this->maintenance_logs_model->get($log_id);
		$filename = 'maintenance_log_'.$log_id.'_attachments.zip';

		$this->zip->download($filename);
	}

	/**
	 * Create invoice from maintenance log
	 */
	public function create_invoice($log_id)
	{
		if (staff_cant('create', 'invoices'))
		{
			ajax_access_denied();
		}

		$log = $this->maintenance_logs_model->get($log_id);

		if ( ! $log || ! $log->is_completed)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('wmm_cannot_create_invoice'),
			]);

			return;
		}

		if ($log->invoice_created && $log->invoice_id)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('wmm_invoice_already_created'),
			]);

			return;
		}

		$invoice_id = $this->maintenance_logs_model->create_invoice($log_id);

		if ($invoice_id)
		{
			echo json_encode([
				'success'     => TRUE,
				'message'     => _l('wmm_invoice_created_successfully'),
				'invoice_id'  => $invoice_id,
				'invoice_url' => admin_url('invoices/invoice/'.$invoice_id),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('wmm_invoice_creation_failed'),
			]);
		}
	}

	/**
	 * Unlink invoice from maintenance log
	 */
	public function unlink_invoice($log_id)
	{
		if (staff_cant('edit', 'website_maintenance_logs'))
		{
			ajax_access_denied();
		}

		$log = $this->maintenance_logs_model->get($log_id);

		if ( ! $log)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('wmm_log_not_found'),
			]);

			return;
		}

		if ( ! $log->invoice_id || ! $log->invoice_created)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('wmm_no_invoice_to_unlink'),
			]);

			return;
		}

		$success = $this->maintenance_logs_model->unlink_invoice($log_id);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('wmm_invoice_unlinked_successfully'),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('wmm_invoice_unlink_failed'),
			]);
		}
	}

}
