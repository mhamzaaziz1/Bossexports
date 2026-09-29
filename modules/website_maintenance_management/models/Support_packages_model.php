<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Support_packages_model extends App_Model {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get support package(s)
	 *
	 * @param  mixed  $id  Package ID or empty for all
	 *
	 * @return mixed
	 */
	public function get($id = '')
	{
		if (is_numeric($id))
		{
			$this->db->select(
				'sp.*,
                c.company as client_name,
                w.website_url,
                p.name as project_name,
                s.firstname as creator_firstname,
                s.lastname as creator_lastname
            ',
			);
			$this->db->from(db_prefix().'wmm_support_packages sp');
			$this->db->join(db_prefix().'clients c', 'c.userid = sp.client_id', 'left');
			$this->db->join(db_prefix().'wmm_websites w', 'w.id = sp.website_id', 'left');
			$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
			$this->db->join(db_prefix().'staff s', 's.staffid = sp.created_by', 'left');
			$this->db->where('sp.id', $id);

			return $this->db->get()->row();
		}

		$this->db->select(
			'
            sp.*,
            c.company as client_name,
            w.website_url,
            p.name as project_name,
            s.firstname as creator_firstname,
            s.lastname as creator_lastname
        ',
		);
		$this->db->from(db_prefix().'wmm_support_packages sp');
		$this->db->join(db_prefix().'clients c', 'c.userid = sp.client_id', 'left');
		$this->db->join(db_prefix().'wmm_websites w', 'w.id = sp.website_id', 'left');
		$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
		$this->db->join(db_prefix().'staff s', 's.staffid = sp.created_by', 'left');
		$this->db->order_by('sp.created_at', 'DESC');

		return $this->db->get()->result_array();
	}

	/**
	 * Get active packages for a client
	 *
	 * @param  int  $client_id  Client ID
	 * @param  int  $website_id  Optional website ID to filter
	 *
	 * @return array
	 */
	public function get_active_packages($client_id, $website_id = NULL)
	{
		$this->db->select('sp.*, w.website_url, p.name as project_name');
		$this->db->from(db_prefix().'wmm_support_packages sp');
		$this->db->join(db_prefix().'wmm_websites w', 'w.id = sp.website_id', 'left');
		$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
		$this->db->where('sp.client_id', $client_id);
		$this->db->where('sp.status', 'active');
		$this->db->where('sp.hours_remaining >', 0);

		if ($website_id)
		{
			$this->db->group_start();
			$this->db->where('sp.website_id', $website_id);
			$this->db->or_where('sp.website_id IS NULL'); // Include client-wide packages
			$this->db->group_end();
		}

		$this->db->order_by('sp.expiry_date', 'ASC');
		$this->db->order_by('sp.hours_remaining', 'ASC');

		return $this->db->get()->result_array();
	}

	/**
	 * Get package by website
	 *
	 * @param  int  $website_id  Website ID
	 *
	 * @return array
	 */
	public function get_by_website($website_id)
	{
		$this->db->select('sp.*');
		$this->db->from(db_prefix().'wmm_support_packages sp');
		$this->db->join(db_prefix().'wmm_websites w', 'w.id = sp.website_id', 'left');
		$this->db->where('sp.website_id', $website_id);
		$this->db->or_where('(sp.website_id IS NULL AND sp.client_id = (SELECT client_id FROM '.db_prefix().'wmm_websites WHERE id = '.$website_id.'))');
		$this->db->order_by('sp.status', 'ASC');
		$this->db->order_by('sp.created_at', 'DESC');

		return $this->db->get()->result_array();
	}

	/**
	 * Add support package
	 *
	 * @param  array  $data  Package data
	 *
	 * @return mixed
	 */
	public function add($data)
	{
		$data['created_by']      = get_staff_user_id();
		$data['hours_remaining'] = $data['total_hours'];
		$data['hours_used']      = 0;
		$data['status']          = 'active';

		// Auto-update status based on expiry date
		if (isset($data['expiry_date']) && ! empty($data['expiry_date']))
		{
			if (strtotime($data['expiry_date']) < strtotime(date('Y-m-d')))
			{
				$data['status'] = 'expired';
			}
		}

		$this->db->insert(db_prefix().'wmm_support_packages', $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			log_activity('Support Package Created [ID:'.$insert_id.', Client ID: '.$data['client_id'].']');

			return $insert_id;
		}

		return FALSE;
	}

	/**
	 * Update support package
	 *
	 * @param  array  $data  Package data
	 * @param  int  $id  Package ID
	 *
	 * @return bool
	 */
	public function update($data, $id)
	{
		// Recalculate hours_remaining if total_hours changed
		if (isset($data['total_hours']))
		{
			$package = $this->get($id);
			if ($package)
			{
				$data['hours_remaining'] = $data['total_hours'] - $package->hours_used;

				// Update status if hours exhausted
				if ($data['hours_remaining'] <= 0)
				{
					$data['status']          = 'exhausted';
					$data['hours_remaining'] = 0;
				}
			}
		}

		// Auto-update status based on expiry date
		if (isset($data['expiry_date']) && ! empty($data['expiry_date']))
		{
			if (strtotime($data['expiry_date']) < strtotime(date('Y-m-d')))
			{
				$data['status'] = 'expired';
			}
		}

		$this->db->where('id', $id);
		$this->db->update(db_prefix().'wmm_support_packages', $data);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Support Package Updated [ID:'.$id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Delete support package
	 *
	 * @param  int  $id  Package ID
	 *
	 * @return bool
	 */
	public function delete($id)
	{
		// Check if package has been used
		$this->db->where('package_id', $id);
		$count = $this->db->count_all_results(db_prefix().'wmm_package_usage');

		if ($count > 0)
		{
			return ['error' => _l('wmm_package_has_usage')];
		}

		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'wmm_support_packages');

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Support Package Deleted [ID:'.$id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Deduct hours from package
	 *
	 * @param  int  $package_id  Package ID
	 * @param  int  $log_id  Maintenance log ID
	 * @param  float  $hours  Hours to deduct
	 *
	 * @return bool|array
	 */
	public function deduct_hours($package_id, $log_id, $hours)
	{
		$package = $this->get($package_id);

		if ( ! $package)
		{
			return FALSE;
		}

		// Check if package has enough hours
		if ($package->hours_remaining < $hours)
		{
			return [
				'error' => _l('wmm_insufficient_package_hours'),
			];
		}

		// Check if already deducted
		$this->db->where('package_id', $package_id);
		$this->db->where('log_id', $log_id);
		$exists = $this->db->get(db_prefix().'wmm_package_usage')->row();

		if ($exists)
		{
			return [
				'error' => _l('wmm_hours_already_deducted'),
			];
		}

		// Start transaction
		$this->db->trans_start();

		// Update package hours
		$new_hours_used      = $package->hours_used + $hours;
		$new_hours_remaining = $package->total_hours - $new_hours_used;

		$update_data = [
			'hours_used'      => $new_hours_used,
			'hours_remaining' => max($new_hours_remaining, 0),
		];

		// Update status if exhausted
		if ($new_hours_remaining <= 0)
		{
			$update_data['status'] = 'exhausted';
		}

		$this->db->where('id', $package_id);
		$this->db->update(db_prefix().'wmm_support_packages', $update_data);

		// Record usage
		$this->db->insert(db_prefix().'wmm_package_usage', [
			'package_id'     => $package_id,
			'log_id'         => $log_id,
			'hours_consumed' => $hours,
		]);

		// Update maintenance log
		$this->db->where('id', $log_id);
		$this->db->update(db_prefix().'wmm_maintenance_logs', [
			'package_id'            => $package_id,
			'deducted_from_package' => 1,
		]);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
			return FALSE;
		}

		// Check for low balance notification
		if (
			$new_hours_remaining > 0
		    && $new_hours_remaining <= $package->low_balance_threshold
		    && $package->low_balance_notify
		)
		{
			$this->send_low_balance_notification($package_id);
		}

		log_activity('Hours deducted from package [Package ID: '.$package_id.', Log ID: '.$log_id.', Hours: '.$hours.']');

		return TRUE;
	}

	/**
	 * Get package usage history
	 *
	 * @param  int  $package_id  Package ID
	 *
	 * @return array
	 */
	public function get_usage_history($package_id)
	{
		$this->db->select(
			'
            pu.*,
            ml.performed_at,
            ml.time_spent,
            w.website_url,
            p.name as project_name,
            s.firstname,
            s.lastname
        ',
		);
		$this->db->from(db_prefix().'wmm_package_usage pu');
		$this->db->join(db_prefix().'wmm_maintenance_logs ml', 'ml.id = pu.log_id', 'left');
		$this->db->join(db_prefix().'wmm_websites w', 'w.id = ml.website_id', 'left');
		$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
		$this->db->join(db_prefix().'staff s', 's.staffid = ml.performed_by', 'left');
		$this->db->where('pu.package_id', $package_id);
		$this->db->order_by('pu.consumed_at', 'DESC');

		return $this->db->get()->result_array();
	}

	/**
	 * Get package statistics
	 *
	 * @param  int  $package_id  Package ID
	 *
	 * @return object
	 */
	public function get_statistics($package_id)
	{
		$package = $this->get($package_id);

		if ( ! $package)
		{
			return NULL;
		}

		// Get usage count
		$this->db->where('package_id', $package_id);
		$usage_count = $this->db->count_all_results(db_prefix().'wmm_package_usage');

		// Calculate usage percentage
		$usage_percentage = $package->total_hours > 0
			? round(($package->hours_used / $package->total_hours) * 100, 2)
			: 0;

		// Calculate average hours per usage
		$avg_hours_per_usage = $usage_count > 0
			? round($package->hours_used / $usage_count, 2)
			: 0;

		// Days until expiry
		$days_until_expiry = NULL;
		if ($package->expiry_date)
		{
			$expiry            = strtotime($package->expiry_date);
			$now               = strtotime(date('Y-m-d'));
			$days_until_expiry = floor(($expiry - $now) / (60 * 60 * 24));
		}

		return (object)[
			'usage_count'         => $usage_count,
			'usage_percentage'    => $usage_percentage,
			'avg_hours_per_usage' => $avg_hours_per_usage,
			'days_until_expiry'   => $days_until_expiry,
		];
	}

	/**
	 * Get packages summary for dashboard
	 *
	 * @return array
	 */
	public function get_summary()
	{
		// Total active packages
		$this->db->where('status', 'active');
		$total_active = $this->db->count_all_results(db_prefix().'wmm_support_packages');

		// Total exhausted packages
		$this->db->where('status', 'exhausted');
		$total_exhausted = $this->db->count_all_results(db_prefix().'wmm_support_packages');

		// Total hours remaining across all active packages
		$this->db->select_sum('hours_remaining');
		$this->db->where('status', 'active');
		$query                 = $this->db->get(db_prefix().'wmm_support_packages');
		$total_hours_remaining = $query->row()->hours_remaining ?? 0;

		// Packages with low balance
		$this->db->where('status', 'active');
		$this->db->where('hours_remaining <=', 'low_balance_threshold', FALSE);
		$this->db->where('hours_remaining >', 0);
		$low_balance_count = $this->db->count_all_results(db_prefix().'wmm_support_packages');

		return [
			'total_active'          => $total_active,
			'total_exhausted'       => $total_exhausted,
			'total_hours_remaining' => round($total_hours_remaining, 2),
			'low_balance_count'     => $low_balance_count,
		];
	}

	/**
	 * Get packages with low balance
	 *
	 * @return array
	 */
	public function get_low_balance_packages()
	{
		$this->db->select('sp.*, c.company as client_name, w.website_url, p.name as project_name');
		$this->db->from(db_prefix().'wmm_support_packages sp');
		$this->db->join(db_prefix().'clients c', 'c.userid = sp.client_id', 'left');
		$this->db->join(db_prefix().'wmm_websites w', 'w.id = sp.website_id', 'left');
		$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
		$this->db->where('sp.status', 'active');
		$this->db->where('sp.hours_remaining <=', 'sp.low_balance_threshold', FALSE);
		$this->db->where('sp.hours_remaining >', 0);
		$this->db->order_by('sp.hours_remaining', 'ASC');

		return $this->db->get()->result_array();
	}

	/**
	 * Send low balance notification
	 *
	 * @param  int  $package_id  Package ID
	 *
	 * @return bool
	 */
	public function send_low_balance_notification($package_id)
	{
		$package = $this->get($package_id);

		if ( ! $package)
		{
			return FALSE;
		}

		// Get client primary contact
		$this->db->where('userid', $package->client_id);
		$this->db->where('is_primary', 1);
		$contact = $this->db->get(db_prefix().'contacts')->row();

		if ( ! $contact)
		{
			$this->db->where('userid', $package->client_id);
			$this->db->where('active', 1);
			$this->db->limit(1);
			$contact = $this->db->get(db_prefix().'contacts')->row();
		}

		if ( ! $contact)
		{
			return FALSE;
		}

		// Get website info if package is website-specific
		$website_info = _l('wmm_all_client_websites');
		if ($package->website_id)
		{
			$this->db->select('w.website_url, p.name as project_name');
			$this->db->from(db_prefix().'wmm_websites w');
			$this->db->join(db_prefix().'projects p', 'p.id = w.project_id', 'left');
			$this->db->where('w.id', $package->website_id);
			$website = $this->db->get()->row();

			if ($website)
			{
				$website_info = $website->website_url ?: $website->project_name;
			}
		}

		// Prepare merge fields
		$merge_fields = [
			'{client_name}'        => $contact->firstname.' '.$contact->lastname,
			'{package_name}'       => $package->package_name,
			'{hours_remaining}'    => number_format($package->hours_remaining, 2),
			'{total_hours}'        => number_format($package->total_hours, 2),
			'{hours_used}'         => number_format($package->hours_used, 2),
			'{website_info}'       => $website_info,
			'{threshold_hours}'    => number_format($package->low_balance_threshold, 2),
			'{expiry_date}'        => $package->expiry_date ? _d($package->expiry_date) : _l('wmm_no_expiry'),
			'{company_name}'       => get_option('companyname'),
			'{package_view_link}'  => admin_url('website_maintenance_management/support_packages/view/'.$package_id),
		];

		// Get template slug
		$template_slug = 'wmm-package-low-balance';

		// Use PerfexCRM email template system
		$this->load->model('emails_model');

		try
		{
			$sent = $this->emails_model->send_email_template($template_slug, $contact->email, $merge_fields);

			if ($sent)
			{
				// Mark package as notified
				$this->db->where('id', $package_id);
				$this->db->update(db_prefix().'wmm_support_packages', [
					'low_balance_notified' => 1,
				]);

				log_activity('Low balance notification sent for package [Package ID: '.$package_id.']');

				return TRUE;
			}
		} catch (Exception $e)
		{
			log_activity('Failed to send low balance notification: '.$e->getMessage());
		}

		return FALSE;
	}

}
