<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Xero extends AdminController {

	public function __construct() {
		parent::__construct();
		$this->load->model('clients_model');
		$this->load->model('payments_model');
		$this->load->model('invoices_model');
		$this->load->model('invoice_items_model');
		$this->app_css->add('xero-css', module_dir_url('xero', 'assets/css/xero.css') . '?v=' . $this->app_css->core_version(), 'admin', ['app-css']);
		if (get_option('xero_access_token') && time() > get_option('xero_expiration_date')) {
			set_alert('warning', _l('needs_reconnecting'));
			redirect(admin_url());
		}
	}

	public function index() {
		if (has_permission('xero', '', 'view')) {
			$data['accounts'] = $this->xero_model->get_xero_accounts('*');
			$this->load->view('xero', $data, FALSE);
		}
	}

/*==========================================================
//  Chart of accounts
//==========================================================*/
	public function synchallaccounts() {

		$bg_process = false;
		if ($this->input->get('bg_process', true) !== null) {
			$bg_process = $this->input->get('bg_process', true);
		}

		if ($this->input->is_ajax_request() || $bg_process) {
			$result = $this->xero_model->xero_synch_accounts();

			if ($result) {

				echo json_encode(array(
					'success' => true,
					'message' => _l('chat_sync_success'),
				));
			} else {
				echo json_encode(array(
					'success' => false,
					'message' => _l('chat_sync_fail'),
				));
			}
		}

		return true;
	}

/*==========================================================
//  CUSTOMER
//==========================================================*/

	public function synchallclients() {
		$bg_process = false;
		if ($this->input->get('bg_process', true) !== null) {
			$bg_process = $this->input->get('bg_process', true);
		}

		if ($this->input->is_ajax_request() || $bg_process) {
			$clients = $this->clients_model->get();

			//synch client groups
			$this->xero_model->xero_synch_client_groups();

			if (count($clients) > 0) {
				foreach ($clients as $client) {
					if (total_rows(db_prefix() . 'contacts', ['active' => 1, 'userid' => $client['userid']]) > 0) {
						$success = $this->xero_model->xero_synch_client($client);
						if (!$success) {
							echo json_encode(array(
								'success' => false,
								'message' => _l('customer_sync_fail'),
							));

							return;
						}
					}
				}

				echo json_encode(array(
					'success' => true,
					'message' => _l('customer_sync_success'),
				));
			} else {
				echo json_encode(array(
					'success' => false,
					'message' => _l('customer_sync_404'),
				));
			}
			die;
		}
		return true;
	}

	public function get_xero_clients($select = "") {
		if (!empty($select)) {
			$this->db->select($select);
		}

		$result = $this->db->where('xero_Id !=', XERO_DEFAULT_ID)->get(db_prefix() . 'clients')->result();

		echo json_encode($result);
	}

/*==========================================================
//  CUSTOMER GROUPS
//==========================================================*/

	public function synchallclientgroups() {
		$bg_process = false;
		if ($this->input->get('bg_process', true) !== null) {
			$bg_process = $this->input->get('bg_process', true);
		}

		if ($this->input->is_ajax_request() || $bg_process) {

			//synch client groups
			$success = $this->xero_model->xero_synch_client_groups();

			if (!$success) {
				echo json_encode(array(
					'success' => false,
					'message' => _l('customer_groups_sync_fail'),
				));

				return;
			}

			echo json_encode(array(
				'success' => true,
				'message' => _l('customer_groups_sync_success'),
			));
			die;
		}
		return true;
	}

	public function get_xero_client_groups($select = "") {
		if (!empty($select)) {
			$this->db->select($select);
		}

		$result = $this->db->where(['xero_Id !=' => XERO_DEFAULT_ID])->get(db_prefix() . 'customers_groups')->result();

		echo json_encode($result);
	}

	public function deleteCustomerGroup($id) {
		$this->load->model('client_groups_model');
		$group = $this->client_groups_model->get_groups($id);
		$success = $this->xero_model->delete_customer_group($group->xero_Id);
		echo json_encode(['success' => $success]);
	}

/*==========================================================
//  TAXES
//==========================================================*/
	public function synchalltaxes() {
		$bg_process = false;
		if ($this->input->get('bg_process', true) != null) {
			$bg_process = $this->input->get('bg_process', true);
		}

		if ($this->input->is_ajax_request() || $bg_process) {

			//Synch Down
			if (!$this->xero_model->xero_synch_tax_down()) {
				echo json_encode(array(
					'success' => false,
					'message' => _l('taxes_sync_fail'),
				));

				return;
			}

			//Synch Up
			//Get Taxes
			$taxes = $this->xero_model->getTaxes();
			if (count($taxes) > 0) {
				foreach ($taxes as $tax) {
					$success = $this->xero_model->xero_synch_tax_up($tax);
					if (!$success) {
						log_message('error', $tax['name'] . ' Tax synchronization with ' . _l('xero') . ' unsuccessful. Kindly refresh page and try again.');
					}
				}

				echo json_encode(array(
					'success' => true,
					'message' => _l('taxes_sync_success'),
				));
			} else {
				echo json_encode(array(
					'success' => false,
					'message' => _l('taxes_sync_404'),
				));
			}
			die;
		}

		return true;
	}

	public function get_xero_taxes() {

		$result = $this->xero_model->getTaxes(['sync_xero >' => 0]);

		echo json_encode($result);
	}

/*==========================================================
//  ITEMS
//==========================================================*/

	public function synchallitems() {

		$bg_process = false;
		if ($this->input->get('bg_process', true) !== null) {
			$bg_process = $this->input->get('bg_process', true);
		}

		if ($this->input->is_ajax_request() || $bg_process) {
			$where = [];
			if (get_option('xero_default_income_account') == "") {
				$where = array('xero_incomeAccountRef !=' => null);
			}

			$items = $this->xero_model->get_items('', $where);
			if (count($items) > 0) {
				$items = json_decode(json_encode($items));
				$unsuccessful = '';
				foreach ($items as $key => $item) {
					$success = $this->xero_model->xero_item($item->itemid);
					if ($success == false) {
						$unsuccessful .= $item->description . ',';
					}
				}
				if (empty($unsuccessful)) {

					echo json_encode(array(
						'success' => true,
						'message' => _l('items_sync_success'),
					));
				} else {
					echo json_encode(array(
						'success' => false,
						'message' => _l('items_sync_fail', $unsuccessful),
					));
				}
			} else {
				echo json_encode(array(
					'success' => false,
					'message' => _l('items_sync_404'),
				));
			}
			die;
		}

		return true;
	}
	public function get_xero_items($select = "") {
		if (!empty($select)) {
			$this->db->select($select);
		}
		$result = $this->db->where('xero_Id !=', XERO_DEFAULT_ID)->get(db_prefix() . 'xero_items')->result();

		echo json_encode($result);
	}

	public function get_item_income_acc_select_field($id = null) {
		if ($this->input->is_ajax_request()) {
			if (!is_numeric($id)) {
				$selected = null;
			} else {
				$selected = $this->xero_model->get_items($id)->xero_incomeAccountRef;
			}

			$data = array();
			$data['selected'] = $selected;

			$acc_select = $this->xero_model->get_xero_accounts("*", "REVENUE", '', true);
			if (isset($acc_select->chart->REVENUE) && count($acc_select->chart->REVENUE) > 0) {
				foreach ($acc_select->chart->REVENUE as $key => $value) {
					$data['xero_income_accounts'][$value->Code] = $value->Name;
				}
			}

			$this->load->view('includes/item_income_acc_ref_field', $data);
		}
	}

/*==========================================================
//  INVOICES
//==========================================================*/

	public function synchallinvoices() {

		$bg_process = false;
		if ($this->input->get('bg_process', true) !== null) {
			$bg_process = $this->input->get('bg_process', true);
		}

		if ($this->input->is_ajax_request() || $bg_process) {
			$invoices = $this->invoices_model->get('', array('status <' => 5, 'xero_Id' => XERO_DEFAULT_ID));
			if (count($invoices) > 0) {
				$invoices = json_decode(json_encode($invoices));
				$unsuccessful = "";
				foreach ($invoices as $key => $invoice) {
					$success = $this->xero_model->xero_invoice($invoice->id);
					if ($success == false) {
						$unsuccessful .= format_invoice_number($invoice->id) . ',';
					}
				}
				if (empty($unsuccessful)) {

					echo json_encode(array(
						'success' => true,
						'message' => _l('invoices_sync_success'),
					));
				} else {
					echo json_encode(array(
						'success' => false,
						'message' => _l('invoices_sync_fail', $unsuccessful),
					));
				}
			} else {
				echo json_encode(array(
					'success' => false,
					'message' => _l('invoices_sync_404'),
				));
			}
			die;
		}

		return true;
	}

	public function get_xero_invoice($select = "") {
		if (!empty($select)) {
			$this->db->select($select);
		}
		$result = $this->db->where(['xero_Id !=' => XERO_DEFAULT_ID])->get(db_prefix() . 'invoices')->result();

		echo json_encode($result);
	}

/*==========================================================
//  PAYMENTS
//==========================================================*/

	public function synchallpayments() {

		$bg_process = false;
		if ($this->input->get('bg_process', true) !== null) {
			$bg_process = $this->input->get('bg_process', true);
		}

		if ($this->input->is_ajax_request() || $bg_process) {
			if (get_option('xero_sync_payments_down_enabled') == "0") {
				$invoices = $this->invoices_model->get('', array('status <' => 4, 'status >' => 1));
				if (count($invoices) > 0) {
					$invoices = json_decode(json_encode($invoices));
					foreach ($invoices as $key => $invoice) {
						$payments = $this->payments_model->get_invoice_payments($invoice->id);
						if (count($payments) > 0) {
							$payments = json_decode(json_encode($payments));
							$unsuccessful = "";
							foreach ($payments as $key => $payment) {
								$success = $this->xero_model->xero_payment($payment->paymentid);
								if ($success == false) {
									$unsuccessful .= $payment->paymentid . ',';
								}
							}
						}
					}
					if (empty($unsuccessful)) {

						echo json_encode(array(
							'success' => true,
							'message' => _l('payments_sync_success'),
						));
					} else {
						echo json_encode(array(
							'success' => false,
							'message' => _l('payments_sync_fail', $unsuccessful),
						));
					}
				} else {
					echo json_encode(array(
						'success' => false,
						'message' => _l('payments_sync_404'),
					));
				}
			} else {
				$this->xero_model->xero_payment_down();
				echo json_encode(array(
					'success' => true,
					'message' => _l('payments_sync_success'),
				));
			}

			die;
		}

		return true;
	}

	public function get_xero_payment_modes($select = "") {
		if (!empty($select)) {
			$this->db->select($select);
		}

		$result = $this->db->where(['xero_Id !=' => XERO_DEFAULT_ID])->get(db_prefix() . 'payment_modes')->result();

		echo json_encode($result);
	}

	public function get_xero_payments($select = "") {
		if (!empty($select)) {
			$this->db->select($select);
		}

		$result = $this->db->where(['xero_payment_Id !=' => XERO_DEFAULT_ID])->get(db_prefix() . 'invoicepaymentrecords')->result();

		echo json_encode($result);
	}

	public function get_payment_method_acc_and_select_field($id = null) {
		if ($this->input->is_ajax_request()) {
			if (!is_numeric($id)) {
				$payment_mode = null;
				$selected = null;
				$acc = null;
			} else {
				$payment_mode = $this->db->where(['id' => $id])->get(db_prefix() . 'payment_modes')->row();
				$selected = $payment_mode->xero_hasSortCode;
				$acc = $payment_mode->xero_accountNumber;
			}

			$data = array();
			$data['selected'] = $selected;
			$data['true_false'] = [0 => 'No', 1 => 'Yes'];
			$data['accountNumber'] = $acc;

			$this->load->view('includes/payment_mode_xero_mod', $data);
		}
	}

/*==========================================================
//  EXPENSES
//==========================================================*/

	public function synchallexpenses() {

		$bg_process = false;
		if ($this->input->get('bg_process', true) !== null) {
			$bg_process = $this->input->get('bg_process', true);
		}

		if ($this->input->is_ajax_request() || $bg_process) {
			if ($this->xero_model->xero_expense()) {
				echo json_encode(array(
					'success' => true,
					'message' => _l('xero_expenses_sync_success'),
				));
			} else {
				echo json_encode(array(
					'success' => false,
					'message' => _l('xero_expenses_sync_fail'),
				));
			}
		}

		return true;
	}

	public function get_xero_expenses_categories($select = "") {
		if (!empty($select)) {
			$this->db->select($select);
		}

		$result = $this->db->where(['xero_Id !=' => XERO_DEFAULT_ID])->get(db_prefix() . 'expenses_categories')->result();

		echo json_encode($result);
	}

/*==========================================================
//  RESET ALL SYNC
//==========================================================*/
	public function reset_all_sync() {
		if ($this->input->is_ajax_request()) {
			//Chart of acc
			$this->db->query("TRUNCATE " . db_prefix() . "xero_chart_accounts");
			$this->db->query("ALTER TABLE " . db_prefix() . "xero_chart_accounts  AUTO_INCREMENT = 1");

			//Taxes
			$this->db->query("TRUNCATE " . db_prefix() . "xero_taxes");
			$this->db->query("ALTER TABLE " . db_prefix() . "xero_taxes  AUTO_INCREMENT = 1");

			//items
			$this->db->query("TRUNCATE " . db_prefix() . "xero_items");
			$this->db->query("ALTER TABLE " . db_prefix() . "xero_items  AUTO_INCREMENT = 1");
			$this->db->update(db_prefix() . 'items', ["xero_incomeAccountRef" => Null], ['id >' => 0]);

			//expenses
			$this->db->update(db_prefix() . 'expenses', ["xero_paymentAccountRef" => Null, "xero_Id" => XERO_DEFAULT_ID], ['id >' => 0]);
			$this->db->update(db_prefix() . 'expenses_categories', ["xero_Id" => XERO_DEFAULT_ID], ['id >' => 0]);

			//Clients
			$this->db->update(db_prefix() . 'clients', ["xero_Id" => XERO_DEFAULT_ID], ['userid >' => 0]);

			//Customer Groups
			$this->db->update(db_prefix() . 'customers_groups', ["xero_Id" => XERO_DEFAULT_ID], ['id >' => 0]);

			//invoices
			$this->db->update(db_prefix() . 'invoices', ["xero_Id" => XERO_DEFAULT_ID], ['id >' => 0]);

			//Payments
			$this->db->update(db_prefix() . 'invoicepaymentrecords', ["xero_payment_Id" => XERO_DEFAULT_ID], ['id >' => 0]);

			$this->db->update(db_prefix() . 'payment_modes', ["xero_Id" => XERO_DEFAULT_ID], ['id >' => 0]);

			//Settings
			update_option('xero_default_income_account', '');
			update_option('xero_default_payment_account', '');
			update_option('xero_default_asset_account', '');

			echo json_encode(array('success' => true, 'message' => _l('reset_success')));
			return true;
		}
	}

	public function test_pay() {
		echo $this->xero_model->xero_payment_method();
	}

	public function test_inv($id, $debug = "input") {
		echo $this->xero_model->xero_invoice($id, 'add_edit', $debug);
	}
}

/* End of file Xero.php */
/* Location: ../xero/controllers/Xero.php */