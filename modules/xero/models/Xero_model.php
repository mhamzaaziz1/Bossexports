<?php
//error_reporting(-1);
//ini_set('display_errors', 1);
defined('BASEPATH') OR exit('No direct script access allowed');

class Xero_model extends App_Model {

	private $accountingApi = null;
	private $identityApi = null;

	public function __construct() {
		parent::__construct();
		$this->load->model('clients_model');
		$this->load->model('client_groups_model');
		$this->load->model('invoices_model');
		$this->load->model('payments_model');
		$this->load->model('payment_modes_model');
		$this->load->model('expenses_model');

		$config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken(get_option('xero_access_token'));

		$this->identityApi = new XeroAPI\XeroPHP\Api\IdentityApi(
			new GuzzleHttp\Client(),
			$config
		);

		$this->accountingApi = new XeroAPI\XeroPHP\Api\AccountingApi(
			new GuzzleHttp\Client(),
			$config
		);
	}

/*==========================================================
//  Disconnect Xero
//==========================================================*/
	public function disconnect() {
		return $this->xero_module->deleteConnection(get_option('xero_tenant_id'), $this->identityApi);
	}

/*==========================================================
//  Chart of accounts
//==========================================================*/

	public function xero_synch_accounts() {
		try {
			$chart_table = db_prefix() . 'xero_chart_accounts';
			$this->db->truncate($chart_table);

			$allAccounts = $this->xero_module->getAccounts(get_option('xero_tenant_id'), $this->accountingApi, true);
			$allAccounts = json_decode(json_encode($allAccounts));
			if (!empty($allAccounts) && count($allAccounts) > 0) {
				$chart = array();
				$list_table_fields = array();
				$columns = $this->db->query("SHOW COLUMNS FROM " . $chart_table)->result_array();
				foreach ($columns as $column) {
					$list_table_fields[] = $column['Field'];
				}

				foreach ($allAccounts as $oneAccount) {

					foreach ($oneAccount as $key => $value) {
						if (!in_array($key, $list_table_fields)) {
							unset($oneAccount->$key);
						}
					}

					$chart[] = $oneAccount;
					if (get_field_value($chart_table, array('AccountID' => $oneAccount->AccountID), 'chart_id')) {
						//update
						$this->db->where('AccountID', $oneAccount->AccountID)->update($chart_table, $oneAccount);
					} else {
						$this->db->insert($chart_table, $oneAccount);
					}

					$chart[] = $oneAccount;
				}
				return true;
			} else {
				return false;
			}

		} catch (Exception $ex) {
			log_message('error', $ex->getMessage());
		}

		return false;
	}

	/*Get XERO Accounts*/
	public function get_xero_accounts($select = "", $Classification = "", $AccountType = "", $combined = false) {
		if (!empty($select)) {
			$this->db->select($select);
		}
		$query = $this->db->order_by("Class", "asc")->get(db_prefix() . 'xero_chart_accounts');

		$accs = array();
		$accs_combined = array();
		$acc_classifications_arr = array();
		$currency = "";

		if ($results = $query->result()) {
			if (count($results) > 0) {
				foreach ($results as $result) {
					$_Classification = $result->Class;
					$_Classification_lower = strtolower($_Classification);

					if ($_Classification) {
						$accs['chart'][$_Classification_lower][] = $result;
						$sets = $accs['chart'][$_Classification_lower];
						unset($accs['chart'][$_Classification_lower]);
						foreach ($sets as $sete) {
							$_AccountType = $sete->Type;
							$accs['chart'][$_Classification][$_AccountType][] = $sete;
							$accs_combined['chart'][$_Classification][] = $sete;
						}
					}

					$currency = $result->CurrencyCode;

					$acc_classifications_arr[] = $_Classification;
				}

				if (!empty($Classification)) {
					foreach ($acc_classifications_arr as $cls) {
						if ($cls != $Classification) {
							unset($accs['chart'][$cls]);
						} else {
							if (!empty($AccountType)) {
								foreach ($accs['chart'][$cls] as $key => $acc_typ) {
									if ($key != $AccountType) {
										unset($accs['chart'][$cls][$key]);
									}
								}
							}
						}

					}
				}

				$accs['currency'] = $currency;
			} else {
				$accs = array();
			}
		}
		if (!$combined) {
			return json_decode(json_encode($accs));
		} else {
			return json_decode(json_encode($accs_combined));
		}

	}

/*==========================================================
//  Clients
//==========================================================*/

	public function get_xero_customer_groups($id) {
		return $this->db->select(db_prefix() . 'customers_groups.*')
			->where('customer_id', $id)
			->from(db_prefix() . 'customer_groups')
			->join(db_prefix() . 'customers_groups', db_prefix() . 'customer_groups.groupid = ' . db_prefix() . 'customers_groups.id', 'left')
			->get()->result_array();
	}

	public function delete_customer_group($ContactGroupId) {
		try {
			return $this->xero_module->archiveContactGroup($ContactGroupId, get_option('xero_tenant_id'), $this->accountingApi);
		} catch (Exception $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function xero_synch_client_groups() {
		try {
			//sync down
			$xero_client_groups = $this->xero_module->getContactGroup(get_option('xero_tenant_id'), $this->accountingApi, null, true);
			if (count($xero_client_groups) > 0) {
				$xero_client_groups = json_decode(json_encode($xero_client_groups));

				foreach ($xero_client_groups as $key => $xero_client_group) {
					if (total_rows(db_prefix() . 'customers_groups', ['xero_Id' => $xero_client_group->ContactGroupID]) > 0) {
						//update
						$this->db->where('xero_Id', $xero_client_group->ContactGroupID);
						$this->db->update(db_prefix() . 'customers_groups', [
							'name' => $xero_client_group->Name,
						]);

					} else {
						//insert
						$data = array('name' => $xero_client_group->Name, 'xero_Id' => $xero_client_group->ContactGroupID);
						$this->client_groups_model->add($data);
					}
				}
			}

			//sync up
			$local_client_groups = $this->client_groups_model->get_groups();
			if (count($local_client_groups) > 0) {
				$local_client_groups = json_decode(json_encode($local_client_groups));
				foreach ($local_client_groups as $key => $local_client_group) {
					if ($local_client_group->xero_Id == XERO_DEFAULT_ID) {
						//Synch with xero
						$xero_Id = $this->xero_module->createContactGroup($local_client_group->name, get_option('xero_tenant_id'), $this->accountingApi);
						//update xero_Id
						$this->db->where('id', $local_client_group->id);
						$this->db->update(db_prefix() . 'customers_groups', [
							'xero_Id' => $xero_Id,
						]);
					}
				}
			}
			return true;
		} catch (Exception $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function xero_synch_client($client) {
		try {
			if (count($client) > 0) {

				$client = json_decode(json_encode($client));

				$contactid = get_primary_contact_user_id($client->userid);
				$contact = $this->clients_model->get_contact($contactid);

				$xero_client = $this->xero_module->getContacts(get_option('xero_tenant_id'), $this->accountingApi, $contact->email); //using email

				if ($client->xero_Id != XERO_DEFAULT_ID) {
					$xero_client = $this->xero_module->getContacts(get_option('xero_tenant_id'), $this->accountingApi, "", $client->xero_Id); //using xero ContactID
				}

				//That client does not exist in xero
				if ($xero_client < 1) {
					//Create
					$xero_Id = $this->xero_module->createContact($client, get_option('xero_tenant_id'), $this->accountingApi);

					if ($xero_Id) {
						$data = array();
						$data['xero_Id'] = $xero_Id;
						$this->db->where('userid', $client->userid)->update(db_prefix() . 'clients', $data);
						if ($this->db->affected_rows() > 0) {
							log_activity('Xero Customer added [ID: ' . $xero_Id . ', USERID: ' . $client->userid . ']');
						}
						return $xero_Id;
					}

				} else {
					//Update
					$xero_Id = $this->xero_module->updateContact($client, get_option('xero_tenant_id'), $this->accountingApi);

					if ($xero_Id) {
						$data = array();
						$data['xero_Id'] = $xero_Id;
						$this->db->where('userid', $client->userid)->update(db_prefix() . 'clients', $data);
						if ($this->db->affected_rows() > 0) {
							log_activity('Xero Customer updated [ID: ' . $xero_Id . ', USERID: ' . $client->userid . ']');
						}
						return $xero_Id;
					}
				}
			}
			return true;
		} catch (Exception $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

/*==========================================================
//  Taxes
//==========================================================*/

	public function getTaxes($where = array()) {
		$this->db->select(db_prefix() . "taxes.id, name, taxrate, xero_TaxType, xero_TaxName, sync_xero");
		$this->db->from(db_prefix() . 'taxes');
		$this->db->join(db_prefix() . 'xero_taxes', db_prefix() . 'xero_taxes.tax_id=' . db_prefix() . 'taxes.id');
		$this->db->where($where);
		$this->db->order_by('taxrate', 'ASC');

		return $this->db->get()->result_array();
	}

	public function getTax($where = array()) {
		$this->db->select(db_prefix() . "taxes.id, name, taxrate, xero_TaxType, xero_TaxName, sync_xero");
		$this->db->from(db_prefix() . 'taxes');
		$this->db->join(db_prefix() . 'xero_taxes', db_prefix() . 'xero_taxes.tax_id=' . db_prefix() . 'taxes.id');
		$this->db->where($where);
		return $this->db->get()->row();
	}

	public function xero_synch_tax_up($tax) {
		try {

			if (!empty($tax)) {
				//Check if name contains a delimiter ~ for multi tax
				$taxName = explode('~', $tax['name']);
				$tax['xero_TaxType'] = "PERFEX-TAX" . $tax['id'];
				if (count($taxName) > 1) {
					$tax['name'] = $taxName[0];
					$tax['compName'] = $taxName[1];
					if (total_rows(db_prefix() . 'xero_taxes', ['xero_TaxName' => $tax['name']]) > 0) {
						$existTax = $this->getTax(['xero_TaxName' => $tax['name']]);
						$tax['xero_TaxType'] = $existTax->xero_TaxType;
					}
				} else {
					$tax['name'] = $taxName[0];
					$tax['compName'] = $taxName[0];
				}
				$tax = json_decode(json_encode($tax));
				if ($tax->sync_xero == 0) {

					$xero_tax_count = $this->xero_module->getTaxRate(get_option('xero_tenant_id'), $this->accountingApi, $tax->name);

					if ($xero_tax_count == 0) {
						//Create In xero
						$xero_new_tax = $this->xero_module->createTaxRates($tax, get_option('xero_tenant_id'), $this->accountingApi);

						$this->db->insert(db_prefix() . 'xero_taxes', ['tax_id' => $tax->id, 'sync_xero' => 1, 'xero_TaxType' => $xero_new_tax->TaxType, 'xero_TaxName' => $xero_new_tax->Name]);
						if ($this->db->insert_id()) {
							log_activity('Tax Rate synchronized with xero & updated [ID: ' . $tax->id . ']');
						}
					}
				}
			}
			return true;
		} catch (Exception $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function xero_synch_tax_down() {
		try {

			$xero_taxes = $this->xero_module->getTaxRate(get_option('xero_tenant_id'), $this->accountingApi, null, true);
			if (count($xero_taxes) > 0) {
				$xero_taxes = json_decode(json_encode($xero_taxes));
				foreach ($xero_taxes as $key => $xero_tax) {
					if (total_rows(db_prefix() . 'xero_taxes', ['xero_TaxName' => $xero_tax->Name]) == 0) {
						foreach ($xero_tax->TaxComponents as $key => $value) {
							//create
							$xero_tax_id = get_field_value(db_prefix() . 'taxes', ['name' => count($xero_tax->TaxComponents) == 1 ? $xero_tax->Name : $xero_tax->Name . '~' . $value->Name], "id");
							if (is_null($xero_tax_id) || empty($xero_tax_id)) {
								$this->db->insert(db_prefix() . 'taxes', ['name' => count($xero_tax->TaxComponents) == 1 ? $xero_tax->Name : $xero_tax->Name . '~' . $value->Name, 'taxrate' => $value->Rate]);
								$xero_tax_id = $this->db->insert_id();
							}

							if ($xero_tax_id) {
								$this->db->insert(db_prefix() . 'xero_taxes', ['tax_id' => $xero_tax_id, 'xero_TaxName' => $xero_tax->Name, 'xero_TaxType' => $xero_tax->TaxType, 'sync_xero' => 1]);

								$insert_id = $this->db->insert_id();
								if ($insert_id) {
									log_activity('New Tax Added from Xero [ID: ' . $insert_id . ', ' . $xero_tax->Name . ']');
								}
							}
						}
					} else {
						foreach ($xero_tax->TaxComponents as $key => $value) {
							if (total_rows(db_prefix() . 'taxes', ['name' => count($xero_tax->TaxComponents) == 1 ? $xero_tax->Name : $xero_tax->Name . '~' . $value->Name]) == 1) {

								$tax_id = get_field_value(db_prefix() . 'taxes', ['name' => count($xero_tax->TaxComponents) == 1 ? $xero_tax->Name : $xero_tax->Name . '~' . $value->Name], 'id');

								$this->db->update(db_prefix() . 'taxes', ['taxrate' => $value->Rate], ['id' => $tax_id]);

								$xero_tax_id = get_field_value(db_prefix() . 'xero_taxes', ['tax_id' => $tax_id], "tax_id");
								if (!is_null($xero_tax_id) && !empty($xero_tax_id)) {
									$this->db->update(db_prefix() . 'xero_taxes', ['xero_TaxType' => $xero_tax->TaxType, 'xero_TaxName' => $xero_tax->Name, 'sync_xero' => 1], ['id' => $tax_id]);

									if ($this->db->affected_rows() > 0) {
										log_activity('Xero Tax Updated [ Name' . $xero_tax->Name . ']');

									}
								} else {
									$this->db->insert(db_prefix() . 'xero_taxes', ['tax_id' => $tax_id, 'xero_TaxName' => $xero_tax->Name, 'xero_TaxType' => $xero_tax->TaxType, 'sync_xero' => 1]);

									if ($this->db->insert_id()) {
										log_activity('Xero Tax Added from Xero [ Name' . $xero_tax->Name . ']');

									}
								}
							} else {
								$this->db->insert(db_prefix() . 'taxes', ['name' => count($xero_tax->TaxComponents) == 1 ? $xero_tax->Name : $xero_tax->Name . '~' . $value->Name, 'taxrate' => $value->Rate]);

								if ($this->db->insert_id()) {
									$this->db->insert(db_prefix() . 'xero_taxes', ['tax_id' => $this->db->insert_id(), 'xero_TaxName' => $xero_tax->Name, 'xero_TaxType' => $xero_tax->TaxType, 'sync_xero' => 1]);

									$insert_id = $this->db->insert_id();
									if ($insert_id) {
										log_activity('New Tax Added from Xero [ID: ' . $insert_id . ', ' . $xero_tax->Name . ']');
									}
								}

							}
						}
					}
				}
			}
			return true;
		} catch (Exception $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

/*==========================================================
//  ITEMS
//==========================================================*/
	public function get_xero_item($item, $byCode = false, $check = "") {
		$xero_item_res = [];
		if ($byCode) {
			$xero_item_res = $this->xero_module->getItemByCode(get_option('xero_tenant_id'), $this->accountingApi, XERO_ITEM_CODE_PREFIX . $item, true);
		} else {
			$xero_item_res = $this->xero_module->getItemByItemID(get_option('xero_tenant_id'), $this->accountingApi, $item, true);
		}
		if ($xero_item_res != false && count($xero_item_res) == 1) {
			//Should be one record
			if (empty($check)) {
				return json_decode(json_encode($xero_item_res))[0];
			} else {
				return json_decode(json_encode($xero_item_res))[0]->$check;
			}
		}
		return [];
	}

	public function xero_item($id = "", $check = 'add_edit') {
		if (!empty($check) && !empty($id)) {
			$item_xero_map = array();
			$xero_Id = null;
			$db_item = $this->get_items($id);
			$xero_Id = get_field_value(db_prefix() . "xero_items", array("item_id" => $id), "xero_Id");
			if ($check == "add_edit") {
				//Query Xero
				$xero_item_by_xero_name = $this->get_xero_item($id, true);

				//Remove special characters to avoid Xero violation
				$db_item->description = format_xero_item_name($db_item->description);
				$db_item->long_description = format_xero_item_description($db_item->long_description);
				$db_item->id = $id;

				if (empty($xero_item_by_xero_name) && count($xero_item_by_xero_name) == 0) {
					//Add New
					try {

						$new_item = $this->xero_module->createItems($db_item, get_option('xero_tenant_id'), $this->accountingApi, true);
						$new_item = json_decode(json_encode($new_item));
						//echo json_encode($new_item);exit;
						if ($new_item && count($new_item) > 0) {
							$new_item = $new_item[0];
							$data = array();
							$data['item_id'] = $id;
							$data['xero_Id'] = $new_item->ItemID;

							if (total_rows(db_prefix() . 'xero_items', array('item_id' => $id)) == 0) {
								$this->db->insert(db_prefix() . 'xero_items', $data);
								if ($this->db->insert_id()) {
									log_activity('Xero Item added [ID: ' . $data['xero_Id'] . ', Local Item ID' . $id . ']');
								}
							} else {
								$this->db->update(db_prefix() . 'xero_items', array('xero_Id' => $data['xero_Id']), array('item_id' => $id));
								if ($this->db->affected_rows() > 0) {
									log_activity('Xero Item Updated [ID: ' . $data['xero_Id'] . ', Local Item ID' . $id . ']');
								}
							}
							return $data['xero_Id'];
						}
					} catch (Exception $ex) {
						log_message('error', $ex->getMessage());
					}

				} else {
					//Update
					try {

						$db_item = array_merge(json_decode(json_encode($db_item), true), array('xero_Id' => $xero_item_by_xero_name->ItemID));

						$update_item = $this->xero_module->updateItem($db_item, get_option('xero_tenant_id'), $this->accountingApi, true);

						$update_item = json_decode(json_encode($update_item));
						if ($update_item && count($update_item) > 0) {
							$update_item = $update_item[0];
							$data = array();
							$data['item_id'] = $id;
							$data['xero_Id'] = $update_item->ItemID;
							if (total_rows(db_prefix() . 'xero_items', array('item_id' => $id)) == 0) {
								$this->db->insert(db_prefix() . 'xero_items', $data);
								if ($this->db->insert_id()) {
									log_activity('Xero Item Updated and Added [ID: ' . $data['xero_Id'] . ', Local Item ID' . $id . ']');
								}
							} else {
								$this->db->update(db_prefix() . 'xero_items', array('xero_Id' => $data['xero_Id']), array('item_id' => $id));
								if ($this->db->affected_rows() > 0) {
									log_activity('Xero Item Updated [ID: ' . $data['xero_Id'] . ', Local Item ID' . $id . ']');
								}
							}
							return $data['xero_Id'];
						}

					} catch (Exception $ex) {
						log_message('error', $ex->getMessage());
					}

				}
			} else if ($check == 'delete') {
				try {
					if (count($xero_Id) > 0) {
						$deleted = $this->xero_module->deleteItem($xero_Id, get_option('xero_tenant_id'), $this->accountingApi);
						if ($deleted) {
							$this->db->where('item_id', $id);
							$this->db->where('xero_Id', $xero_Id);
							$this->db->delete(db_prefix() . 'xero_items');
							log_activity('Xero Item updated as Inactive [ID: ' . $xero_Id . ', Local Item ID' . $id . ']');
						}
					}
					return true;
				} catch (Exception $ex) {
					log_message('error', $ex->getMessage());
				}

			}
		}
		return false;
	}

	public function get_items($id = '', $where = array()) {
		$columns = $this->db->list_fields(db_prefix() . 'items');
		$rateCurrencyColumns = '';
		foreach ($columns as $column) {
			if (strpos($column, 'rate_currency_') !== false) {
				$rateCurrencyColumns .= $column . ',';
			}
		}
		$this->db->select($rateCurrencyColumns . '' . db_prefix() . 'items.id as itemid,rate,
			t1.taxrate as taxrate,t1.id as taxid,t1.name as taxname, xt1.xero_TaxType as taxtype,
			t2.taxrate as taxrate_2,t2.id as taxid_2,t2.name as taxname_2, xt2.xero_TaxType as taxtype_2,
			description,long_description,group_id,' . db_prefix() . 'items_groups.name as group_name,unit, xero_incomeAccountRef');
		$this->db->from(db_prefix() . 'items');
		$this->db->join('' . db_prefix() . 'taxes t1', 't1.id = ' . db_prefix() . 'items.tax', 'left');
		$this->db->join('' . db_prefix() . 'taxes t2', 't2.id = ' . db_prefix() . 'items.tax2', 'left');
		$this->db->join('' . db_prefix() . 'xero_taxes xt1', 'xt1.tax_id = ' . db_prefix() . 'items.tax', 'left');
		$this->db->join('' . db_prefix() . 'xero_taxes xt2', 'xt2.tax_id = ' . db_prefix() . 'items.tax2', 'left');
		$this->db->join(db_prefix() . 'items_groups', '' . db_prefix() . 'items_groups.id = ' . db_prefix() . 'items.group_id', 'left');

		$this->db->where($where);

		if (is_numeric($id)) {
			$this->db->where(db_prefix() . 'items.id', $id);

			return $this->db->get()->row();
		}
		$this->db->order_by('description', 'asc');

		return $this->db->get()->result_array();
	}

	/*
		 * Create new invoice item on the go
	*/
	public function inv_item($db_item = array()) {
		if (!empty($db_item)) {
			$db_item = json_decode(json_encode($db_item));
			try {
				$new_item = $this->xero_module->createItems($db_item, get_option('xero_tenant_id'), $this->accountingApi, true);
				if ($new_item && is_array($new_item) && count($new_item) > 0) {
					$new_item = json_decode(json_encode($new_item))[0];
					return $new_item;
				}

			} catch (Exception $ex) {
				log_message('error', $ex->getMessage());
			}
		}

		return '';

	}

/*==========================================================
//  INVOICES
//==========================================================*/

	/*
		 * QB INVOICE CRUD
	*/
	public function xero_invoice($id = "", $check = "add_edit", $debug = "none") {
		/* This sample performs the folowing functions: no: canceled or draft
			     1.   Add a customer
			     2.   Add an item
			     3    Create invoice using the information above
			     4.   Email invoice to customer
			     5.   Receive payments for the invoice created above
		*/
		if (!empty($check) && !empty($id)) {
			$inv_obj = array();
			$xero_id = null;
			$db_invoice = $this->invoices_model->get($id, array('status <' => 5));

			if (!empty($db_invoice) && !is_null($db_invoice)) {
				try {
					//handle client
					$client = $db_invoice->client;
					$client_xero_id = $this->xero_model->xero_synch_client(json_decode(json_encode($client), true));

					//handle item
					$raw_items = $db_invoice->items;
					$xero_item_line = array();
					foreach ($raw_items as $key => $item) {
						//Query xero Item
						$itemCode = XERO_ITEM_CODE_PREFIX . $item['id']; //format_xero_item_code_name($item['description']);
						$_xero_item = $this->get_xero_item($item['id'], true);

						$item['xero_TaxType'] = !empty(get_invoice_item_tax_xero_id($item['id'])) ? get_invoice_item_tax_xero_id($item['id'])[0] : "";
						$item['description'] = format_xero_item_name($item['description']);
						$item['long_description'] = format_xero_item_description($item['long_description']);

						//if it doesnt exist in our local records
						if (empty($_xero_item) && count($_xero_item) == 0) {
							//synch item
							$xero_item_new = $this->xero_model->inv_item($item);
							$item_xeroid = $xero_item_new->ItemID;
							$itemCode = $xero_item_new->Code;
						} else {

							$item_xeroid = $_xero_item->ItemID;
						}

						$xero_item_line[] = [
							"description" => $item['description'],
							"qty" => $item['qty'],
							"rate" => $item['rate'],
							"xero_code" => $itemCode,
							"percentage_based" => ($db_invoice->discount_total > 0 && $db_invoice->discount_percent > 0) ? true : false,
							"discount_percent" => $db_invoice->discount_percent,
							"discount_amount" => $db_invoice->discount_total / count($raw_items),
							"xero_TaxType" => $item['xero_TaxType'],
							"account_code" => empty(get_option('xero_default_income_account')) ? "200" : get_option('xero_default_income_account'),
						];

					}

					$ref_number = "Ref-00" . $db_invoice->id;
					if ($db_invoice->project_id > 0 && get_option('xero_sync_invoice_ref_with_project_details')) {
						$ref_number = $db_invoice->project_id . '-' . get_project_name_by_id($db_invoice->project_id);
					}

					$inv_obj = [
						"client_xero_id" => $client_xero_id,
						"ref_number" => $ref_number,
						"invoice_number" => format_invoice_number($db_invoice->id),
						"due_date" => $db_invoice->duedate,
						"txn_date" => $db_invoice->date,
						"ApplyTaxAfterDiscount" => ($db_invoice->discount_type == "before_tax") ? true : false,
						"lineitems" => $xero_item_line,
					];

					if ($debug == "input") {
						return '<pre>' . json_encode($inv_obj);
					}

					if ($check == 'add_edit') {
						//Query Invoices:
						$xero_invoice = $this->xero_module->getInvoices(get_option('xero_tenant_id'), $this->accountingApi, 'InvoiceNumber=="' . format_invoice_number($db_invoice->id) . '"', true);
						if (count($xero_invoice) < 1) {
							//create
							$resultingObj = $this->xero_module->createInvoiceAccRec($inv_obj, get_option('xero_tenant_id'), $this->accountingApi, true);

							if ($debug == "create") {
								return '<pre>' . json_encode($resultingObj);
							}

							if ($resultingObj) {
								$resultingObj = json_decode(json_encode($resultingObj))[0];
								if (!$resultingObj->HasErrors) {
									$data = array();
									$data['xero_Id'] = $resultingObj->InvoiceID;
									$this->db->where('id', $db_invoice->id)->update(db_prefix() . 'invoices', $data);
									if ($this->db->affected_rows() > 0) {
										log_activity('Xero Invoice added [ID: ' . $data['xero_Id'] . ', Invoice ID' . $db_invoice->id . ']');
									}
									return $data['xero_Id'];
								}
							}
						} else {
							//update
							$invoiceID = json_decode(json_encode($xero_invoice))[0]->InvoiceID;
							$resultingObj = $this->xero_module->updateInvoice($inv_obj, $invoiceID, get_option('xero_tenant_id'), $this->accountingApi, true);

							if ($debug == "update") {
								return '<pre>' . json_encode($resultingObj);
							}

							if ($resultingObj && !empty($resultingObj)) {
								$resultingObj = json_decode(json_encode($resultingObj))[0];

								if ($resultingObj->HasErrors == false) {
									$data = array();
									$data['xero_Id'] = $resultingObj->InvoiceID;
									$this->db->where('id', $db_invoice->id)->update(db_prefix() . 'invoices', $data);
									if ($this->db->affected_rows() > 0) {
										log_activity('Xero Invoice updated [ID: ' . $data['xero_Id'] . ', Invoice ID' . $db_invoice->id . ']');
									}
									return $data['xero_Id'];
								}

								return false;
							} else {
								$data['xero_Id'] = $invoiceID;
								$this->db->where('id', $db_invoice->id)->update(db_prefix() . 'invoices', $data);
								if ($this->db->affected_rows() > 0) {
									log_activity('Xero Invoice updated [ID: ' . $data['xero_Id'] . ', Invoice ID' . $db_invoice->id . ']');
								}
							}
						}

					} else if ($check == 'delete') {
						//delete
						if ($db_invoice->xero_Id != XERO_DEFAULT_ID) {
							$resultingObj = $this->xero_module->deleteInvoice($db_invoice->xero_Id, get_option('xero_tenant_id'), $this->accountingApi);
							if ($resultingObj) {
								log_activity('Xero Invoice deleted [ID: ' . $db_invoice->xero_Id . ', Invoice ID' . $db_invoice->id . ']');
							}
						}
					}

				} catch (Exception $ex) {
					log_message('error', $ex->getMessage());
				}

			}
		}
		return false;
	}

/*==========================================================
//  PAYMENT METHODS
//==========================================================*/

	public function xero_get_paymentmodes($id) {
		$pms = $this->xero_module->getBankAccounts(get_option('xero_tenant_id'), $this->accountingApi, XERO_PAYMENT_METHOD_PREFIX . $id, true);
		if ($pms) {
			$pms = json_decode(json_encode($pms));
			if (count($pms) > 0) {
				return $pms[0];
			} else {
				return [];
			}

		} else {
			return [];
		}

	}

	public function xero_payment_method() {
		try {
			//Sync Up
			$local_payment_modes = $this->payment_modes_model->get();
			if (count($local_payment_modes) > 0) {
				$local_payment_modes = json_decode(json_encode($local_payment_modes));
				//echo '<pre>' . json_encode($local_payment_modes);exit;
				foreach ($local_payment_modes as $key => $l_payment_mode) {
					$pmss = $this->xero_module->getBankAccounts(get_option('xero_tenant_id'), $this->accountingApi, XERO_PAYMENT_METHOD_PREFIX . $l_payment_mode->id, true, $l_payment_mode);
					//echo '<pre>' . json_encode($pmss) . '</pre><br>';
					if (isset($l_payment_mode->xero_Id)) {
						//offline payments
						if ($pmss && !empty(json_decode(json_encode($pmss)))) {
							$pmss = json_decode(json_encode($pmss))[0];

							//XERO DOES NOT ALLOW BANK ACCOUNT UPDATE VIA API

							//update local
							$this->db->update(db_prefix() . 'payment_modes', ['xero_Id' => $pmss->AccountID], ['id' => $l_payment_mode->id]);
						} else {
							//create
							$account_data = [
								"name" => $l_payment_mode->name,
								"code" => XERO_PAYMENT_METHOD_PREFIX . $l_payment_mode->id,
								"description" => !empty($l_payment_mode->description) ? $l_payment_mode->description : "",
							];
							if (isset($l_payment_mode->xero_accountNumber) && isset($l_payment_mode->xero_hasSortCode)) {
								$account_data['xero_accountNumber'] = $l_payment_mode->xero_accountNumber;
								$account_data['xero_hasSortCode'] = $l_payment_mode->xero_hasSortCode;
							}

							$account_data = json_decode(json_encode($account_data));
							$posted = $this->xero_module->createBankAccount($account_data, get_option('xero_tenant_id'), $this->accountingApi, true);
							$posted = json_decode(json_encode($posted))[0];
							if (isset($posted->PaymentMethod)) {
								$this->db->update(db_prefix() . 'payment_modes', ['xero_Id' => $posted->AccountID], ['id' => $l_payment_mode->id]);
							}

						}

					} else {
						//online payment
						if (!$pmss || empty(json_decode(json_encode($pmss)))) {
							//create
							$account_data = [
								"name" => $l_payment_mode->name,
								"code" => XERO_PAYMENT_METHOD_PREFIX . $l_payment_mode->id,
								"description" => !empty($l_payment_mode->description) ? $l_payment_mode->description : "",
							];
							$account_data = json_decode(json_encode($account_data));
							$this->xero_module->createBankAccount($account_data, get_option('xero_tenant_id'), $this->accountingApi);
						}
					}
				}
			}
			return true;
		} catch (Exception $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	/**
	 * CURRENTLY NOT APPLIED UNTILL PERFEX ADDS A HOOK
	 * Delete Bank account from Xero
	 * @param  [type] $accountID [description]
	 * @return [type]            [description]
	 */
	public function xero_delete_payment_method($accountID) {
		try {
			$this->xero_module->getBankAccounts(get_option('xero_tenant_id'), $this->accountingApi, $accountID);
			return true;
		} catch (Exception $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

/*==========================================================
//  PAYMENTS
//==========================================================*/
	public function xero_payment($id = "", $check = "add_edit", $posted_data = array()) {
		if (!empty($check) && !empty($id)) {
			$xero_id = null;
			$db_payment = $this->payments_model->get($id);
			if (!empty($db_payment)) {
				$db_invoice = $this->invoices_model->get($db_payment->invoiceid);
				$xero_bank_account = $this->xero_get_paymentmodes($db_payment->paymentmode);
				if (!empty($db_invoice) && !empty($xero_bank_account)) {
					try {

						//handle invoice
						$pay_obj = [
							"invoiceID" => $db_invoice->xero_Id,
							"accountID" => $xero_bank_account->AccountID,
							"totalAmt" => empty($posted_data) ? $db_payment->amount : $posted_data['amount'],
							"txnDate" => empty($posted_data) ? $db_payment->daterecorded : $posted_data['date'],
							"depositToAccount" => get_option('xero_default_income_account'),
							"paymentRefNum" => empty($posted_data) ? $db_payment->transactionid : $posted_data['transactionid'],
							"privateNote" => empty($posted_data) ? $db_payment->note : $posted_data['note'],
						];

						//payment METHOD
						if ($check == 'add_edit') {
							$pay_obj = json_decode(json_encode($pay_obj));
							if (($db_payment->xero_payment_Id == XERO_DEFAULT_ID || empty($db_payment->xero_payment_Id)) && empty($posted_data)) {

								$paymentObj = $this->xero_module->createPayment($pay_obj, get_option('xero_tenant_id'), $this->accountingApi, true);
								if ($paymentObj) {
									$paymentObj = json_decode(json_encode($paymentObj))[0];
									$data = array();
									$data['xero_payment_Id'] = $paymentObj->PaymentID;
									$this->db->where('id', $db_payment->paymentid)->update(db_prefix() . 'invoicepaymentrecords', $data);
									if ($this->db->affected_rows() > 0) {
										log_activity('Xero Payment added [ID: ' . $data['xero_payment_Id'] . ', Payment ID' . $db_payment->paymentid . ']');
									}
									return $data['xero_payment_Id'];
								}
							} elseif (!empty($posted_data)) {
								$deleteObj = $this->xero_module->deletePayment(get_option('xero_tenant_id'), $this->accountingApi, $db_payment->xero_payment_Id);
								if ($deleteObj) {
									$paymentObj = $this->xero_module->createPayment($pay_obj, get_option('xero_tenant_id'), $this->accountingApi, true);
									if ($paymentObj) {
										$paymentObj = json_decode(json_encode($paymentObj))[0];
										$data = array();
										$data['xero_payment_Id'] = $paymentObj->PaymentID;
										$this->db->where('id', $id)->update(db_prefix() . 'invoicepaymentrecords', $data);
										if ($this->db->affected_rows() > 0) {
											log_activity('Xero Payment added [ID: ' . $data['xero_payment_Id'] . ', Payment ID' . $db_payment->paymentid . ']');
										}
										return $data['xero_payment_Id'];
									}
								}
							}

							return true;

						} else if ($check == 'delete') {
							$resultingObj = $this->xero_module->deletePayment(get_option('xero_tenant_id'), $this->accountingApi, $db_payment->xero_payment_Id);
							if ($resultingObj) {
								log_activity('Xero Payment deleted [ID: ' . $db_payment->xero_payment_Id . ', Payment ID' . $db_payment->paymentid . ']');
							}
						}

					} catch (Exception $ex) {
						log_message('error', $ex->getMessage());
						return false;
					}

				}
			}
		}

		return false;
	}

	public function xero_payment_down() {
		$payments_raw = $this->xero_module->getPayments(get_option('xero_tenant_id'), $this->accountingApi, 'PaymentType=="ACCRECPAYMENT" AND Status=="AUTHORISED"', true);
		$payments = json_decode(json_encode($payments_raw));
		if ($payments_raw) {
			foreach ($payments as $key => $payment) {
				$invoice_obj = $payment->Invoice;
				$invoices = $this->invoices_model->get('', array('status <' => 5, 'xero_Id' => $invoice_obj->InvoiceID));
				if (!empty($invoices)) {
					$account = $this->xero_module->getBankAccounts(get_option('xero_tenant_id'), $this->accountingApi, $payment->Account->Code, true);
					$account = json_decode(json_encode($account))[0];

					//payment method check
					//$this->payment_modes_model->get()
					//$this->db->update(db_prefix() . 'payment_modes', ['xero_Id' => $pmss->AccountID], ['id' => $l_payment_mode->id]);

					//"Date": "/Date(1604966400000+0000)/",
					$data = [
						"invoiceid" => reset($invoices)['id'],
						"amount" => $payment->Amount,
						"date" => gmdate('Y-m-d', substr(preg_replace("/[^0-9]/", "", $payment->Date), 0, 10)),
						"paymentmode" => substr($account->Code, 3),
						"do_not_redirect" => "on",
						"transactionid" => $payment->Reference,
						"note" => "",
						"do_not_send_email_template" => true,
						"xero_payment_Id" => $payment->PaymentID,
					];

					$this->load->model('payments_model');
					$check_payment_exists = $this->get_payment_record($payment->PaymentID);
					if (!$check_payment_exists) {
						$this->payments_model->add($data);
					}

					//echo '<pre>' . json_encode($data) . '</pre><br>';
				}
			}
		}
	}

	public function get_payment_record($paymentID) {
		$this->db->select('*,' . db_prefix() . 'invoicepaymentrecords.id as paymentid');
		$this->db->join(db_prefix() . 'payment_modes', db_prefix() . 'payment_modes.id = ' . db_prefix() . 'invoicepaymentrecords.paymentmode', 'left');
		$this->db->order_by(db_prefix() . 'invoicepaymentrecords.id', 'asc');
		$this->db->where(db_prefix() . 'invoicepaymentrecords.xero_payment_Id', $paymentID);
		$payment = $this->db->get(db_prefix() . 'invoicepaymentrecords')->row();
		if (!$payment) {
			return false;
		}

		return $payment;
	}

/*==========================================================
//  EXPENSE CATEGORIES
//==========================================================*/
	public function get_expense_category($id) {

		$pmodes = $this->db->where('id', $id)->get(db_prefix() . 'expenses_categories')->row();

		if ($pmodes->xero_Id != XERO_DEFAULT_ID || empty($pmodes->xero_Id)) {
			return $pmodes->xero_Id;
		} else {
			return '';
		}

	}

	public function xero_expense_category() {
		//Synch down
		$xero_categories = $this->get_xero_accounts("*", "EXPENSE", "EXPENSE");
		if (!empty($xero_categories)) {
			foreach ($xero_categories->chart->EXPENSE->EXPENSE as $key => $xero_category) {
				$local_category = $this->db->where(array('xero_Id' => $xero_category->AccountID))->get(db_prefix() . 'expenses_categories')->row();
				if (!empty($local_category)) {
					//update
					$this->db->update(db_prefix() . 'expenses_categories', ['description' => $xero_category->Name, 'xero_Id' => $xero_category->AccountID], ['id' => $local_category->id]);
				} else {
					//create
					$this->db->insert(db_prefix() . 'expenses_categories', ['name' => $xero_category->Name, 'description' => $xero_category->Name, 'xero_Id' => $xero_category->AccountID]);
				}

			}
		}

		//Synch up
		$local_categories = $this->db->where(array('xero_Id' => XERO_DEFAULT_ID))->get(db_prefix() . 'expenses_categories')->result();
		foreach ($local_categories as $key => $local_category) {
			//create
			$expenseAccountRequestObj = [
				"code" => XERO_EXPENSE_CATEGORY_PREFIX . $local_category->id,
				"name" => $local_category->name,
				"description" => $local_category->description,
			];

			$requestObj = $this->xero_module->createExpenseAccount($expenseAccountRequestObj, get_option('xero_tenant_id'), $this->accountingApi, true);
			if ($requestObj) {
				$requestObj = json_decode(json_encode($requestObj))[0];
				$this->db->update(db_prefix() . 'expenses_categories', ['xero_Id' => $requestObj->AccountID], ['id' => $local_category->id]);
			}
		}

	}
/*==========================================================
//  EXPENSES
//==========================================================*/
	public function xero_expense() {
		try {
			$xero_Id = null;
			$db_expenses = json_decode(json_encode($this->expenses_model->get()));
			if (count($db_expenses) > 0) {

				//loop through
				foreach ($db_expenses as $key => $db_expense) {

					//COMING SOON VERSION 2

				}

				return true;
			}
		} catch (Exception $ex) {
			log_message('error', $ex->getMessage());
		}

		return false;
	}

/*==========================================================
//  Cron Jobs
//==========================================================*/

	/**
	 * Runs after every 0.5 hour or 30 minutes.
	 * @param string $value
	 */
	public function half_hourly_scheduled_synch($manually) {

		$get_token = $this->xero_module->get_access_token($manually);
		if ($get_token) {
			$this->xero_synch_accounts();
			$this->xero_payment_method();
			//$this->xero_expense_category();
			$this->xero_synch_client_groups();

		}
	}

	/*
		 * Runs ones a day at a specified time
	*/
	public function daily_scheduled_synch($manually) {
		if (get_option('xero_manual_cron_sync_enabled') == '0') {
			$manually = false;
		}

		$xero_auto_operations_hour = get_option('xero_auto_operations_hour');
		if ($xero_auto_operations_hour == '') {
			$xero_auto_operations_hour = 9;
		}

		$xero_auto_operations_hour = intval($xero_auto_operations_hour);
		$hour_now = date('G');
		if ($hour_now != $xero_auto_operations_hour && $manually === false) {
			return;
		}

		//Customers
		if (get_option('xero_auto_cron_sync_clients_enabled')) {
			$clients = $this->clients_model->get();
			if (!empty($clients) && !is_null($clients)) {
				foreach ($clients as $client) {
					if (total_rows(db_prefix() . 'contacts', ['active' => 1, 'userid' => $client['userid']]) > 0) {
						$this->xero_synch_client($client);
					}
				}
			}
		}

		//Taxes
		if (get_option('xero_auto_cron_sync_taxes_enabled')) {
			//Synch Down
			$this->xero_sync_tax_down();

			//Synch Up
			$taxes = $this->taxes_model->get();
			if (count($taxes) > 0) {
				foreach ($taxes as $tax) {
					if (is_numeric($tax['agency_id']) && $tax['agency_id'] > 0) {
						$this->xero_synch_tax_up($tax);
					}
				}
			}
		}

		//Items
		if (get_option('xero_auto_cron_sync_items_enabled')) {
			$items = $this->get_items('', array('xero_incomeAccountRef !=' => null));
			if (!empty($items) && !is_null($items)) {
				$items = json_decode(json_encode($items));
				foreach ($items as $key => $item) {
					$this->xero_item($item->itemid);
				}
			}
		}

		//Invoices
		if (get_option('xero_auto_cron_sync_invoices_enabled')) {
			$invoices = $this->invoices_model->get('', array('status <' => 5));
			if (!empty($invoices) && !is_null($invoices)) {
				$invoices = json_decode(json_encode($invoices));
				foreach ($invoices as $key => $invoice) {
					$this->xero_invoice($invoice->id);
				}
			}
		}

		//Payments
		if (get_option('xero_auto_cron_sync_payments_enabled')) {
			$invoices = $this->invoices_model->get('', array('status <' => 4, 'status >' => 1));
			if (!empty($invoices) && !is_null($invoices)) {
				$invoices = json_decode(json_encode($invoices));
				foreach ($invoices as $key => $invoice) {
					$payments = $this->payments_model->get_invoice_payments($invoice->id);
					if (count($payments) > 0) {
						$payments = json_decode(json_encode($payments));
						foreach ($payments as $key => $payment) {
							$this->xero_payment($payment->paymentid);
						}
					}
				}
			}
		}

	}
}

/* End of file Xero_model.php */
/* Location: ../xero/model/Xero_model.php */