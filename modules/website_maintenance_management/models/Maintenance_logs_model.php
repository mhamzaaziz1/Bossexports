<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Maintenance_logs_model extends App_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('website_maintenance_management/support_packages_model');
	}

	/**
	 * Get maintenance log(s)
	 *
	 * @param  mixed  $id  Log ID or empty for all
	 *
	 * @return mixed
	 */
	public function get($id = '')
	{
		if (is_numeric($id))
		{
			$this->db->select(
				'l.*, w.website_url, w.project_id, w.client_id,
                              p.name as project_name, c.company as client_name,
                              s.firstname, s.lastname,
                              sp.id as package_id, sp.package_name, sp.hours_remaining as package_hours_remaining,
                              pu.hours_consumed',
			);
			$this->db->from(db_prefix().'wmm_maintenance_logs l');
			$this->db->join(db_prefix().'wmm_websites w', 'w.id = l.website_id', 'left');
			$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
			$this->db->join(db_prefix().'clients c', 'c.userid = w.client_id', 'left');
			$this->db->join(db_prefix().'staff s', 's.staffid = l.performed_by', 'left');
			$this->db->join(db_prefix().'wmm_package_usage pu', 'pu.log_id = l.id', 'left');
			$this->db->join(db_prefix().'wmm_support_packages sp', 'sp.id = l.package_id', 'left');
			$this->db->where('l.id', $id);

			return $this->db->get()->row();
		}

		$this->db->select(
			'l.*, w.website_url, w.project_id, w.client_id,
                          p.name as project_name, c.company as client_name,
                          s.firstname, s.lastname',
		);
		$this->db->from(db_prefix().'wmm_maintenance_logs l');
		$this->db->join(db_prefix().'wmm_websites w', 'w.id = l.website_id', 'left');
		$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
		$this->db->join(db_prefix().'clients c', 'c.userid = w.client_id', 'left');
		$this->db->join(db_prefix().'staff s', 's.staffid = l.performed_by', 'left');
		$this->db->order_by('l.performed_at', 'DESC');

		return $this->db->get()->result_array();
	}

	/**
	 * Get logs for specific website
	 *
	 * @param  int  $website_id  Website ID
	 *
	 * @return array
	 */
	public function get_by_website($website_id)
	{
		$this->db->select('l.*, s.firstname, s.lastname');
		$this->db->from(db_prefix().'wmm_maintenance_logs l');
		$this->db->join(db_prefix().'staff s', 's.staffid = l.performed_by', 'left');
		$this->db->where('l.website_id', $website_id);
		$this->db->order_by('l.performed_at', 'DESC');

		return $this->db->get()->result_array();
	}

	/**
	 * Get tasks for a maintenance log
	 *
	 * @param  int  $log_id  Log ID
	 *
	 * @return array
	 */
	public function get_log_tasks($log_id)
	{
		$this->db->select('lt.*, t.name, t.description, c.name as category_name, c.icon as category_icon, c.color as category_color');
		$this->db->from(db_prefix().'wmm_maintenance_log_tasks lt');
		$this->db->join(db_prefix().'wmm_maintenance_tasks t', 't.id = lt.task_id', 'left');
		$this->db->join(db_prefix().'wmm_categories c', 'c.id = t.category', 'left');

		$this->db->where('lt.log_id', $log_id);
		$this->db->order_by('t.category', 'ASC');
		$this->db->order_by('t.name', 'ASC');

		return $this->db->get()->result_array();
	}

	/**
	 * Add maintenance log
	 *
	 * @param  array  $data  Log data
	 *
	 * @return mixed
	 */
	public function add($data)
	{
		$tasks               = isset($data['task_ids']) ? $data['task_ids'] : [];
		$send_email          = isset($data['send_email']) ? (bool)$data['send_email'] : FALSE;
		$create_invoice      = isset($data['create_invoice']) ? (bool)$data['create_invoice'] : FALSE;
		$is_completed        = isset($data['is_completed']) ? (bool)$data['is_completed'] : FALSE;
		$package_id          = isset($data['package_id']) && ! empty($data['package_id']) ? $data['package_id'] : NULL;
		$deduct_from_package = isset($data['deduct_from_package']) ? (bool)$data['deduct_from_package'] : FALSE;

		unset($data['task_ids']);
		unset($data['send_email']);
		unset($data['create_invoice']);

		$data['performed_by'] = get_staff_user_id();
		$data['performed_at'] = date('Y-m-d H:i:s');

		// Handle billable fields
		$data['is_billable'] = isset($data['is_billable']) ? 1 : 0;
		$data['hourly_rate'] = isset($data['hourly_rate']) && $data['is_billable'] ? $data['hourly_rate'] : NULL;

		// Handle timer logic
		if ($is_completed)
		{
			// Maintenance completed - has start and end time
			$data['is_completed'] = 1;

			// Calculate time_spent if both start and end time provided
			if ( ! empty($data['start_time']) && ! empty($data['end_time']))
			{
				$start              = strtotime($data['start_time']);
				$end                = strtotime($data['end_time']);
				$data['time_spent'] = $end - $start; // in seconds
			}
		} else
		{
			// Maintenance in progress - only has start time
			$data['is_completed'] = 0;
			$data['start_time']   = date('Y-m-d H:i:s');
			$data['end_time']     = NULL;
			$data['time_spent']   = NULL;
		}

		$this->db->insert(db_prefix().'wmm_maintenance_logs', $data);
		$log_id = $this->db->insert_id();

		if ($log_id)
		{
			// Insert tasks
			if ( ! empty($tasks))
			{
				foreach ($tasks as $task_id)
				{
					$this->db->insert(db_prefix().'wmm_maintenance_log_tasks', [
						'log_id'       => $log_id,
						'task_id'      => $task_id,
						'is_completed' => 1,
					]);
				}
			}

			// Send email notifications
			if ($send_email)
			{
				if ($is_completed)
				{
					// Send maintenance completed email
					$this->send_maintenance_completed_notification($log_id);
				} else
				{
					// Send maintenance started email
					$this->send_maintenance_started_notification($log_id);
				}
			}

			// Create invoice if requested and maintenance is completed
			if ($create_invoice && $is_completed)
			{
				$this->create_invoice($log_id);
			}

			// Deduct hours from package if requested and maintenance is completed
			if ($package_id && $deduct_from_package && $is_completed && $data['time_spent'])
			{
				$hours            = round($data['time_spent'] / 3600, 2);
				$CI = &get_instance();
				$CI->load->model('support_packages_model');
				$deduction_result = $this->support_packages_model->deduct_hours($package_id, $log_id, $hours);

				if (is_array($deduction_result) && isset($deduction_result['error']))
				{
					// Log error but don't fail the maintenance log creation
					log_activity('Failed to deduct hours from package: '.$deduction_result['error']);
				}
			}

			log_activity('Maintenance Logged [ID:'.$log_id.', Website ID: '.$data['website_id'].']');

			return $log_id;
		}

		return FALSE;
	}

	/**
	 * Update maintenance log
	 *
	 * @param  array  $data  Log data
	 * @param  int  $id  Log ID
	 *
	 * @return bool
	 */
	public function update($data, $id)
	{
		$tasks = isset($data['tasks']) ? $data['tasks'] : [];
		unset($data['tasks']);

		$this->db->where('id', $id);
		$this->db->update(db_prefix().'wmm_maintenance_logs', $data);

		if ( ! empty($tasks))
		{
			// Delete existing tasks
			$this->db->where('log_id', $id);
			$this->db->delete(db_prefix().'wmm_maintenance_log_tasks');

			// Insert new tasks
			foreach ($tasks as $task_id)
			{
				$this->db->insert(db_prefix().'wmm_maintenance_log_tasks', [
					'log_id'       => $id,
					'task_id'      => $task_id,
					'is_completed' => 1,
				]);
			}
		}

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Maintenance Log Updated [ID:'.$id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Delete maintenance log
	 *
	 * @param  int  $id  Log ID
	 *
	 * @return bool
	 */
	public function delete($id)
	{
		// Delete log tasks first
		$this->db->where('log_id', $id);
		$this->db->delete(db_prefix().'wmm_maintenance_log_tasks');

		// Delete log
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'wmm_maintenance_logs');

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Maintenance Log Deleted [ID:'.$id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Stop timer for maintenance log
	 *
	 * @param  int  $log_id  Log ID
	 *
	 * @return bool
	 */
	public function stop_timer($log_id)
	{
		$log = $this->get($log_id);

		if ( ! $log || $log->is_completed)
		{
			return FALSE;
		}

		$end_time   = date('Y-m-d H:i:s');
		$time_spent = NULL;

		// Calculate time spent
		if ($log->start_time)
		{
			$start      = strtotime($log->start_time);
			$end        = strtotime($end_time);
			$time_spent = $end - $start;
		}

		$this->db->where('id', $log_id);
		$this->db->update(db_prefix().'wmm_maintenance_logs', [
			'end_time'     => $end_time,
			'time_spent'   => $time_spent,
			'is_completed' => 1,
		]);

		if ($this->db->affected_rows() > 0)
		{
			// Deduct hours from package if requested and package_id exists
			if ($log->deduct_from_package && $log->package_id && $time_spent)
			{
				$hours = round($time_spent / 3600, 2);

				$CI =& get_instance();
				$CI->load->model('support_packages_model');
				$deduction_result = $this->support_packages_model->deduct_hours($log->package_id, $log_id, $hours);

				if (is_array($deduction_result) && isset($deduction_result['error']))
				{
					// Log error but don't fail the timer stop
					log_activity('Failed to deduct hours from package: '.$deduction_result['error']);
				}
			}

			log_activity('Maintenance Timer Stopped [Log ID:'.$log_id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Send maintenance started notification email
	 *
	 * @param  int  $log_id  Log ID
	 *
	 * @return bool
	 */
	public function send_maintenance_started_notification($log_id)
	{
		$this->load->model('website_maintenance_management/maintenance_websites_model');

		$log = $this->get($log_id);

		if ( ! $log)
		{
			return FALSE;
		}

		$contact = $this->maintenance_websites_model->get_primary_contact($log->website_id);

		if ( ! $contact)
		{
			return FALSE;
		}

		// Prepare merge fields
		$merge_fields = [
			'{client_name}'            => $contact->firstname.' '.$contact->lastname,
			'{project_name}'           => $log->project_name,
			'{website_url}'            => $log->website_url ?: $log->project_name,
			'{maintenance_start_time}' => _dt($log->start_time),
			'{staff_name}'             => $log->firstname.' '.$log->lastname,
			'{company_name}'           => get_option('companyname'),
		];

		// Get template slug
		$template_slug = 'wmm-maintenance-started';

		// Use PerfexCRM email template system
		$this->load->model('emails_model');

		try
		{
			$sent = $this->emails_model->send_email_template($template_slug, $contact->email, $merge_fields);

			if ($sent)
			{
				log_activity('Maintenance started notification sent [Log ID:'.$log_id.']');

				return TRUE;
			}
		} catch (Exception $e)
		{
			log_activity('Failed to send maintenance started notification: '.$e->getMessage());
		}

		return FALSE;
	}

	/**
	 * Send maintenance completed notification email
	 *
	 * @param  int  $log_id  Log ID
	 *
	 * @return bool
	 */
	public function send_maintenance_completed_notification($log_id)
	{
		$this->load->model('website_maintenance_management/maintenance_websites_model');

		$log = $this->get($log_id);

		if ( ! $log)
		{
			return FALSE;
		}

		$contact = $this->maintenance_websites_model->get_primary_contact($log->website_id);

		if ( ! $contact)
		{
			return FALSE;
		}

		// Get completed tasks
		$tasks      = $this->get_log_tasks($log_id);
		$tasks_html = '<ul>';
		foreach ($tasks as $task)
		{
			$tasks_html .= '<li>'.$task['name'];
			if ( ! empty($task['description']))
			{
				$tasks_html .= ' - '.$task['description'];
			}
			$tasks_html .= '</li>';
		}
		$tasks_html .= '</ul>';

		// Format time spent
		$time_spent = '';
		if ($log->time_spent)
		{
			$hours   = floor($log->time_spent / 3600);
			$minutes = floor(($log->time_spent % 3600) / 60);

			if ($hours > 0)
			{
				$time_spent .= _l('wmm_time_h', $hours);
			}
			if ($minutes > 0)
			{
				if ($hours > 0)
				{
					$time_spent .= ' '._l('wmm_time_m', $minutes);
				} else
				{
					$time_spent .= _l('wmm_time_m', $minutes);
				}
			}
			if (empty($time_spent))
			{
				$time_spent = '< 1 '._l('wmm_time_m', 1);
			}
		} else
		{
			$time_spent = _l('wmm_no_time_logged');
		}

		// Prepare notes
		$notes = $log->notes ? '<p><strong>'._l('wmm_notes').':</strong><br>'.nl2br($log->notes).'</p>' : '';

		// Prepare merge fields
		$merge_fields = [
			'{client_name}'      => $contact->firstname.' '.$contact->lastname,
			'{project_name}'     => $log->project_name,
			'{website_url}'      => $log->website_url ?: $log->project_name,
			'{maintenance_date}' => _dt($log->performed_at),
			'{time_spent}'       => $time_spent,
			'{tasks_completed}'  => $tasks_html,
			'{notes}'            => $notes,
			'{staff_name}'       => $log->firstname.' '.$log->lastname,
			'{company_name}'     => get_option('companyname'),
		];

		// Get template slug
		$template_slug = 'wmm-maintenance-completed';

		// Use PerfexCRM email template system
		$this->load->model('emails_model');

		try
		{
			$sent = $this->emails_model->send_email_template($template_slug, $contact->email, $merge_fields);

			if ($sent)
			{
				$this->db->where('id', $log_id);
				$this->db->update(db_prefix().'wmm_maintenance_logs', [
					'email_sent'    => 1,
					'email_sent_at' => date('Y-m-d H:i:s'),
				]);
				log_activity('Maintenance completed notification sent [Log ID:'.$log_id.']');

				return TRUE;
			}
		} catch (Exception $e)
		{
			log_activity('Failed to send maintenance completed notification: '.$e->getMessage());
		}

		return FALSE;
	}

	/**
	 * Create invoice from maintenance log
	 *
	 * @param  int  $log_id  Log ID
	 *
	 * @return mixed Invoice ID or false
	 */
	public function create_invoice($log_id)
	{
		$this->load->model('invoices_model');
		$this->load->model('website_maintenance_management/maintenance_websites_model');

		$log = $this->get($log_id);

		if ( ! $log || ! $log->is_completed)
		{
			return FALSE;
		}

		// Get website info
		$website = $this->maintenance_websites_model->get($log->website_id);
		if ( ! $website)
		{
			return FALSE;
		}

		// Get client info
		$this->load->model('clients_model');
		$client = $this->clients_model->get($log->client_id);
		if ( ! $client)
		{
			return FALSE;
		}

		// Prepare invoice data
		$invoice_data = [
			'clientid'         => $log->client_id,
			'number'           => get_option('next_invoice_number'),
			'date'             => date('Y-m-d'),
			'duedate'          => date('Y-m-d', strtotime('+'.get_option('invoice_due_after').' days')),
			'currency'         => empty($client->default_currency) ? get_base_currency()->id : $client->default_currency,
			'project_id'       => $log->project_id,
			'adminnote'        => '',
			'billing_street'   => $client->billing_street,
			'billing_city'     => $client->billing_city,
			'billing_state'    => $client->billing_state,
			'billing_zip'      => $client->billing_zip,
			'billing_country'  => $client->billing_country,
			'include_shipping' => 0,
			'status'           => Invoices_model::STATUS_UNPAID,
		];

		// Prepare invoice items
		$items = [];
		$tasks = $this->get_log_tasks($log_id);

		if ($log->is_billable && $log->hourly_rate > 0 && $log->time_spent > 0)
		{
			// Invoice based on hourly rate
			$hours       = round($log->time_spent / 3600, 2);
			$description = _l('wmm_maintenance_service').' - '.$log->project_name;

			$long_description = _l('wmm_maintenance_date').': '._dt($log->performed_at)."\n";
			$long_description .= _l('wmm_time_spent').': '.$this->format_seconds_to_time($log->time_spent)."\n\n";
			$long_description .= _l('wmm_tasks_completed').":\n";

			foreach ($tasks as $task)
			{
				$long_description .= '- '.$task['name']."\n";
			}

			if ( ! empty($log->notes))
			{
				$long_description .= "\n"._l('wmm_notes').":\n".$log->notes;
			}

			$items[] = [
				'order'            => 1,
				'description'      => $description,
				'long_description' => $long_description,
				'qty'              => $hours,
				'rate'             => $log->hourly_rate,
				'unit'             => _l('wmm_hours'),
			];
		} else
		{
			// Invoice with tasks as separate items or custom amount
			$order = 1;
			foreach ($tasks as $task)
			{
				$description      = $task['name'];
				$long_description = ! empty($task['description']) ? $task['description'] : '';

				$items[] = [
					'order'            => $order++,
					'description'      => $description,
					'long_description' => $long_description,
					'qty'              => 1,
					'rate'             => 0, // Will be filled manually or by user
					'unit'             => '',
				];
			}

			// Add a general maintenance item if no tasks
			if (empty($items))
			{
				$description      = _l('wmm_maintenance_service').' - '.$log->project_name;
				$long_description = _l('wmm_maintenance_date').': '._dt($log->performed_at)."\n";

				if ( ! empty($log->notes))
				{
					$long_description .= "\n"._l('wmm_notes').":\n".$log->notes;
				}

				$items[] = [
					'order'            => 1,
					'description'      => $description,
					'long_description' => $long_description,
					'qty'              => 1,
					'rate'             => 0,
					'unit'             => '',
				];
			}
		}

		$total_amount             = array_sum(array_map(function ($item) {
			return floatval($item['qty']) * floatval($item['rate']);
		}, $items));
		$invoice_data['subtotal'] = round($total_amount, 2);
		$invoice_data['total']    = round($total_amount, 2);
		$invoice_data['newitems'] = $items;

		// Create invoice
		$invoice_id = $this->invoices_model->add($invoice_data);

		if ($invoice_id)
		{
			// Update log with invoice reference
			$this->db->where('id', $log_id);
			$this->db->update(db_prefix().'wmm_maintenance_logs', [
				'invoice_id'      => $invoice_id,
				'invoice_created' => 1,
			]);

			// Create related item link
			$this->db->insert(db_prefix().'related_items', [
				'item_id'  => $invoice_id,
				'rel_id'   => $log_id,
				'rel_type' => 'maintenance_log',
			]);

			log_activity('Invoice created from maintenance log [Log ID: '.$log_id.', Invoice ID: '.$invoice_id.']');

			return $invoice_id;
		}

		return FALSE;
	}

	/**
	 * Format seconds to readable time
	 *
	 * @param  int  $seconds
	 *
	 * @return string
	 */
	private function format_seconds_to_time($seconds)
	{
		$hours   = floor($seconds / 3600);
		$minutes = floor(($seconds % 3600) / 60);

		$time_parts = [];
		if ($hours > 0)
		{
			$time_parts[] = $hours.' '.($hours == 1 ? _l('hour') : _l('hours'));
		}
		if ($minutes > 0)
		{
			$time_parts[] = $minutes.' '.($minutes == 1 ? _l('minute') : _l('minutes'));
		}

		return ! empty($time_parts) ? implode(' ', $time_parts) : '< 1 '._l('minute');
	}

	/**
	 * Unlink invoice from maintenance log
	 *
	 * @param  int  $log_id  Log ID
	 *
	 * @return bool
	 */
	public function unlink_invoice($log_id)
	{
		$log = $this->get($log_id);

		if ( ! $log || ! $log->invoice_id)
		{
			return FALSE;
		}

		// Remove invoice reference from log
		$this->db->where('id', $log_id);
		$this->db->update(db_prefix().'wmm_maintenance_logs', [
			'invoice_id'      => NULL,
			'invoice_created' => 0,
		]);

		if ($this->db->affected_rows() > 0)
		{
			// Remove related items link
			$this->db->where('rel_id', $log_id);
			$this->db->where('rel_type', 'maintenance_log');
			$this->db->delete(db_prefix().'related_items');

			log_activity('Invoice unlinked from maintenance log [Log ID: '.$log_id.', Invoice ID: '.$log->invoice_id.']');

			return TRUE;
		}

		return FALSE;
	}

}
