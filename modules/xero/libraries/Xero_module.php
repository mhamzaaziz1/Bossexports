<?php
//error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');

use XeroAPI\XeroPHP\AccountingObjectSerializer;
use XeroAPI\XeroPHP\ApiException as XeroException;

class Xero_module {

	/**
	 *
	 * @var CI_Controller
	 */
	private $CI;

	/**
	 * @var ApiInstance
	 */
	private $apiInstance;

	public function __construct() {
		$this->CI = &get_instance();
		$this->CI->load->model('clients_model');
	}

	public function init($arg) {
		$apiInstance = $arg;
	}

/*==========================================================
//  Connection Delete
//==========================================================*/
	public function deleteConnection($xeroTenantId, $apiInstance) {
		try {
			$apiInstance->deleteConnection(get_option('xero_connection_id'));
			return true;
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

/*==========================================================
//  Get Organization
//==========================================================*/

	public function getOrganisationName($xeroTenantId, $apiInstance, $returnObj = false) {
		try {
			$result = $apiInstance->getOrganisations($xeroTenantId);

			if ($returnObj) {
				return $result->getOrganisations()[0];
			} else {
				return $result->getOrganisations()[0]->getName();
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

/*==========================================================
//  Reconnect to get new Access Token for 30min
//==========================================================*/
	public function get_access_token($manual = false) {
		if ((get_option('xero_active') == '1' && time() > ((int) get_option('xero_expiration_date') - 10 * 60)) || $manual == true) {
			try {

				$clientSecret = get_xero_clientSecret();
				$clientId = get_xero_clientID();
				$theRefreshTokenValue = get_option('xero_refresh_token');

				$provider = new \League\OAuth2\Client\Provider\GenericProvider([
					'clientId' => $clientId,
					'clientSecret' => $clientSecret,
					'redirectUri' => site_url('xero/xero_auth'),
					'urlAuthorize' => 'https://login.xero.com/identity/connect/authorize',
					'urlAccessToken' => 'https://identity.xero.com/connect/token',
					'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation',
				]);

				$accessTokenObj = $provider->getAccessToken('refresh_token', [
					'refresh_token' => $theRefreshTokenValue,
				]);

				update_option('xero_access_token', $accessTokenObj->getToken());
				update_option('xero_refresh_token', $accessTokenObj->getRefreshToken());
				update_option('xero_expiration_date', $accessTokenObj->getExpires());
				update_option('xero_is_expired', false);

				return true;

			} catch (XeroException $ex) {
				log_message('error', $ex->getMessage());
			}

		}
		return false;
	}

/*==========================================================
//  Account Get
//==========================================================*/
	public function getAccounts($xeroTenantId, $apiInstance, $returnObj = false) {
		try {
			$where = 'Status=="ACTIVE"';
			$result2 = $apiInstance->getAccounts($xeroTenantId, null, $where);
			if ($returnObj) {
				return $result2;
			} else {
				return count($result2->getAccounts());
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function deleteAccount($xeroTenantId, $apiInstance, $accountId = null, $returnObj = false) {
		try {

			$result = $apiInstance->deleteAccount($xeroTenantId, $accountId);

			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

/*==========================================================
//  Client Group CRUD
//==========================================================*/

	public function getContactGroup($xeroTenantId, $apiInstance, $ContactGroupId = null, $returnObj = false) {
		try {
			$result = $apiInstance->getContactGroups($xeroTenantId);
			if (!is_null($ContactGroupId)) {
				$result = $apiInstance->getContactGroup($xeroTenantId, $ContactGroupId);
			}

			if ($returnObj) {
				return $result;
			} else {
				if (is_null($ContactGroupId)) {
					return count($result->getContactGroups());
				}
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function createContactGroup($groupName, $xeroTenantId, $apiInstance, $returnObj = false) {

		$contactgroup = new XeroAPI\XeroPHP\Models\Accounting\ContactGroup;
		$contactgroup->setName($groupName);

		try {
			$result = $apiInstance->createContactGroup($xeroTenantId, $contactgroup);

			if ($returnObj) {
				return $result;
			} else {
				return $result->getContactGroups()[0]->getContactGroupId();
			}
		} catch (\XeroAPI\XeroPHP\ApiException $e) {
			$error = AccountingObjectSerializer::deserialize($e->getResponseBody(), '\XeroAPI\XeroPHP\Models\Accounting\Error', []);
			log_message('error', $error->getElements()[0]["validation_errors"][0]["message"]);
			return false;
		}
	}

	public function updateContactGroup($newGroupName, $contactgroupId, $xeroTenantId, $apiInstance, $returnObj = false) {
		try {
			$contactgroup = new XeroAPI\XeroPHP\Models\Accounting\ContactGroup;
			$contactgroup->setName($newGroupName);
			$result = $apiInstance->updateContactGroup($xeroTenantId, $contactgroupId, $contactgroup);

			if ($returnObj) {
				return $result;
			} else {
				return $result->getContactGroups()[0]->getContactGroupId();
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function archiveContactGroup($ContactGroupId, $xeroTenantId, $apiInstance, $returnObj = false) {

		try {
			$contactGroupCheck = $apiInstance->getContactGroup($xeroTenantId, $ContactGroupId);

			if (count($contactGroupCheck) > 0) {

				$contactgroup = new XeroAPI\XeroPHP\Models\Accounting\ContactGroup;
				$contactgroup->setStatus(XeroAPI\XeroPHP\Models\Accounting\ContactGroup::STATUS_DELETED);
				$result = $apiInstance->updateContactGroup($xeroTenantId, $ContactGroupId, $contactgroup);
				if ($returnObj) {
					return $result;
				} else {
					return true;
				}
			} else {
				return false;
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

/*==========================================================
//  Clients CRUD
//==========================================================*/

	public function getContacts($xeroTenantId, $apiInstance, $emailAddress = null, $contactId = null, $returnObj = false) {

		try {

			$where = 'ContactStatus=="ACTIVE"';
			if (!empty($emailAddress)) {
				$where .= ' AND EmailAddress=="' . $emailAddress . '"';
			}
			$result = $apiInstance->getContacts($xeroTenantId, null, $where, null, [$contactId]);

			if ($returnObj) {
				return $result;
			} else {
				return count($result->getContacts());
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function createContact($client = array(), $xeroTenantId, $apiInstance, $returnObj = false) {

		try {
			$client = json_decode(json_encode($client));
			$contactid = get_primary_contact_user_id($client->userid);
			$contact_fullnames = get_contact_full_name($contactid);
			$contact = $this->CI->clients_model->get_contact($contactid);

			$arr_contacts = [];

			$address_arr = [
				[
					"AddressType" => "POBOX",
					"AddressLine1" => $client->billing_street,
					"City" => $client->billing_city,
					"Region" => $client->billing_state,
					"PostalCode" => $client->billing_zip,
					"Country" => get_country_name($client->billing_country),
				],
				[
					"AddressType" => "STREET",
					"AddressLine1" => $client->shipping_street,
					"City" => $client->shipping_city,
					"Region" => $client->shipping_state,
					"PostalCode" => $client->shipping_zip,
					"Country" => get_country_name($client->shipping_country),
				],
			];

			$clientCountry = !empty($client->country) ? $client->country : (!empty($client->billing_country) ? $client->billing_country : (!empty($client->shipping_country) ? $client->shipping_country : 236));
			$country_xero = json_decode(json_encode(get_country($clientCountry)));

			$phone_arr = [[
				"PhoneType" => "DEFAULT",
				"PhoneNumber" => empty($client->phonenumber) ? $contact->phonenumber : $client->phonenumber,
				"PhoneAreaCode" => "",
				"PhoneCountryCode" => is_array($country_xero) ? $country_xero->calling_code : "",
			]];

			$customer_group_Arr = [];
			$client_groups = get_xero_customer_groups($client->userid);
			foreach ($client_groups as $key => $client_group) {
				if ($client_group['xero_Id'] != XERO_DEFAULT_ID && !empty($client_group['xero_Id'])) {
					array_push($customer_group_Arr, ["ContactGroupID" => $client_group['xero_Id']]);
				}
			}

			$contact1 = new XeroAPI\XeroPHP\Models\Accounting\Contact;
			$contact1->setName(empty($client->company) ? $contact_fullnames : $client->company)
				->setFirstName($contact->firstname)
				->setLastName($contact->lastname)
				->setEmailAddress($contact->email)
				->setTaxNumber($client->vat)
				->setAddresses($address_arr)
				->setPhones($phone_arr)
				->setContactGroups($customer_group_Arr)
				->setWebsite($client->website);
			array_push($arr_contacts, $contact1);

			$contacts = new XeroAPI\XeroPHP\Models\Accounting\Contacts;
			$contacts->setContacts($arr_contacts);

			$result = $apiInstance->createContacts($xeroTenantId, $contacts);

			if ($returnObj) {
				return $result;
			} else {
				return $result->getContacts()[0]->getContactId();
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function updateContact($client = array(), $xeroTenantId, $apiInstance, $returnObj = false) {
		try {
			$client = json_decode(json_encode($client));
			$contactid = get_primary_contact_user_id($client->userid);
			$contact_fullnames = get_contact_full_name($contactid);
			$contact = $this->CI->clients_model->get_contact($contactid);

			$address_arr = [
				[
					"AddressType" => "POBOX",
					"AddressLine1" => $client->billing_street,
					"City" => $client->billing_city,
					"Region" => $client->billing_state,
					"PostalCode" => $client->billing_zip,
					"Country" => get_country_name($client->billing_country),
				],
				[
					"AddressType" => "STREET",
					"AddressLine1" => $client->shipping_street,
					"City" => $client->shipping_city,
					"Region" => $client->shipping_state,
					"PostalCode" => $client->shipping_zip,
					"Country" => get_country_name($client->shipping_country),
				],
			];

			$clientCountry = !empty($client->country) ? $client->country : (!empty($client->billing_country) ? $client->billing_country : (!empty($client->shipping_country) ? $client->shipping_country : 236));
			$country_xero = json_decode(json_encode(get_country($clientCountry)));
			$phone_arr = [[
				"PhoneType" => "DEFAULT",
				"PhoneNumber" => empty($client->phonenumber) ? $contact->phonenumber : $client->phonenumber,
				"PhoneAreaCode" => "",
				"PhoneCountryCode" => is_array($country_xero->calling_code) ? $country_xero->calling_code : "",
			]];

			$customer_group_Arr = [];
			$client_groups = get_xero_customer_groups($client->userid);
			if (count($client_groups) > 0) {
				foreach ($client_groups as $key => $client_group) {
					if ($client_group['xero_Id'] != XERO_DEFAULT_ID && !empty($client_group['xero_Id'])) {
						array_push($customer_group_Arr, ["ContactGroupID" => $client_group['xero_Id']]);
					}
				}
			}

			$contact1 = new XeroAPI\XeroPHP\Models\Accounting\Contact;
			$contact1->setName(empty($client->company) ? $contact_fullnames : $client->company)
				->setFirstName($contact->firstname)
				->setLastName($contact->lastname)
				->setEmailAddress($contact->email)
				->setTaxNumber($client->vat)
				->setAddresses($address_arr)
				->setPhones($phone_arr)
				->setContactGroups($customer_group_Arr)
				->setWebsite($client->website);

			$result = $apiInstance->updateContact($xeroTenantId, $client->xero_Id, $contact1);

			if ($returnObj) {
				return $result;
			} else {
				return $result->getContacts()[0]->getContactId();
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function archiveContact($contactId, $xeroTenantId, $apiInstance) {
		try {
			$contactGroupCheck = $apiInstance->getContacts($xeroTenantId, $contactId);

			if (count($contactGroupCheck) > 0) {
				$contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
				$contact->setContactStatus(\XeroAPI\XeroPHP\Models\Accounting\Contact::CONTACT_STATUS_ARCHIVED);
				$result = $apiInstance->updateContact($xeroTenantId, $contactId, $contact);
				if ($returnObj) {
					return $result;
				} else {
					return true;
				}
			} else {
				return false;
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

/*==========================================================
//  Taxes CRUD
//==========================================================*/
	public function getTaxRate($xeroTenantId, $apiInstance, $taxName = null, $returnObj = false) {
		try {
			$where = is_null($taxName) ? $taxName : 'Name=="' . $taxName . '"';
			$result = $apiInstance->getTaxRates($xeroTenantId, $where);
			if ($returnObj) {
				return $result;
			} else {
				return count($result->getTaxRates());
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function createTaxRates($tax = array(), $xeroTenantId, $apiInstance, $returnObj = false) {
		try {

			$taxcomponent = new XeroAPI\XeroPHP\Models\Accounting\TaxComponent;
			$taxcomponent->setName($tax->compName)
				->setRate($tax->taxrate);

			if (!empty($tax->xero_TaxType) && !is_null($tax->xero_TaxType)) {
				$taxcomponent->setTaxType($tax->xero_TaxType);
			}

			$arr_taxcomponent = [];
			array_push($arr_taxcomponent, $taxcomponent);

			$taxrate = new XeroAPI\XeroPHP\Models\Accounting\TaxRate;
			$taxrate->setName($tax->name)
				->setTaxComponents($arr_taxcomponent);

			$result = $apiInstance->createTaxRates($xeroTenantId, $taxrate);

			if ($returnObj) {
				return $result;
			} else {
				return $result->getTaxRates()[0]->getName();
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function updateTaxRate($tax = array(), $xeroTenantId, $apiInstance) {
		try {
			$taxrate = new XeroAPI\XeroPHP\Models\Accounting\TaxRate;
			$taxrate->setName($tax['name'])
				->setRate($tax['taxrate']);
			$result = $apiInstance->updateTaxRate($xeroTenantId, $taxrate);
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function archiveTaxRate($taxName, $xeroTenantId, $apiInstance) {
		try {
			$taxrate = new XeroAPI\XeroPHP\Models\Accounting\TaxRate;
			$taxrate->setName($taxName)
				->setStatus(XeroAPI\XeroPHP\Models\Accounting\TaxRate::STATUS_DELETED);
			$result = $apiInstance->updateTaxRate($xeroTenantId, $taxrate);
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

/*==========================================================
//  Items CRUD
//==========================================================*/

	public function getItemByCode($xeroTenantId, $apiInstance, $itemName = null, $returnObj = false) {
		try {
			$where = 'Code == "' . $itemName . '"';
			$result = $apiInstance->getItems($xeroTenantId, null, $where);
			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function getItemByItemID($xeroTenantId, $apiInstance, $itemID = null, $returnObj = false) {
		try {
			$result = $apiInstance->getItem($xeroTenantId, $itemID);
			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function createItems($item_data = array(), $xeroTenantId, $apiInstance, $returnObj = false) {
		try {
			$arr_items = [];
			$incomeAccount = empty($item_data->xero_incomeAccountRef) ? get_option('xero_default_income_account') : $item_data->xero_incomeAccountRef;
			//services & products
			$sales = new XeroAPI\XeroPHP\Models\Accounting\Purchase;
			$sales->setUnitPrice($item_data->rate)
				->setAccountCode($incomeAccount); //set revenue account

			if (!empty($item_data->taxtype) && !is_null($item_data->taxtype)) {
				$sales->setTaxType($item_data->taxtype);
			} else {
				$sales->setTaxType("NONE");
			}

			$item = new XeroAPI\XeroPHP\Models\Accounting\Item;
			$item->setName($item_data->description)
				->setCode(XERO_ITEM_CODE_PREFIX . $item_data->id)
				->setDescription($item_data->description)
				->setIsTrackedAsInventory(false)
				->setSalesDetails($sales);

			array_push($arr_items, $item);

			$items = new XeroAPI\XeroPHP\Models\Accounting\Items;
			$items->setItems($arr_items);

			$result = $apiInstance->createItems($xeroTenantId, $items);

			if ($returnObj) {
				return $result;
			} else {
				return $result->getItems()[0]->getItemId();
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return $ex->getMessage();
		}
	}

	public function updateItem($item_data = array(), $xeroTenantId, $apiInstance, $returnObj = false) {
		try {
			$item_data = json_decode(json_encode($item_data));
			$arr_items = [];
			$incomeAccount = empty($item_data->xero_incomeAccountRef) ? get_option('xero_default_income_account') : $item_data->xero_incomeAccountRef;

			//services & products
			$sales = new XeroAPI\XeroPHP\Models\Accounting\Purchase;
			$sales->setUnitPrice($item_data->rate)
				->setAccountCode($incomeAccount); //set revenue account

			if (!empty($item_data->taxtype) && !is_null($item_data->taxtype)) {
				$sales->setTaxType($item_data->taxtype);
			} else {
				$sales->setTaxType("NONE");
			}

			$item = new XeroAPI\XeroPHP\Models\Accounting\Item;
			$item->setName($item_data->description)
				->setCode(XERO_ITEM_CODE_PREFIX . $item_data->id)
				->setDescription($item_data->long_description)
				->setIsTrackedAsInventory(false)
				->setSalesDetails($sales);

			$result = $apiInstance->updateItem($xeroTenantId, $item_data->xero_Id, $item);

			if ($returnObj) {
				return $result;
			} else {
				return $result->getItems()[0]->getItemId();
			}

		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function deleteItem($itemId, $xeroTenantId, $apiInstance) {
		try {
			$result = $apiInstance->deleteItem($xeroTenantId, $itemId);
			if ($result) {
				return true;
			}

		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

/*==========================================================
//  Invoices CRUD
//==========================================================*/

	public function getInvoices($xeroTenantId, $apiInstance, $where = null, $returnObj = false) {

		try {
			$result = $apiInstance->getInvoices($xeroTenantId, null, $where);
			if ($returnObj) {
				return $result;
			} else {
				return count($result->getInvoices());
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function getInvoice($xeroTenantId, $apiInstance, $invoiceId = null, $returnObj = false) {

		try {
			$result = $apiInstance->getInvoice($xeroTenantId, $invoiceId);
			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function getLineItem($item_data) {

		$lineitem = new XeroAPI\XeroPHP\Models\Accounting\LineItem;
		$lineitem->setDescription($item_data->description)
			->setQuantity($item_data->qty)
			->setUnitAmount($item_data->rate)
			->setItemCode($item_data->xero_code);
		if ($item_data->percentage_based) {
			$lineitem->setDiscountRate($item_data->discount_percent);
		} else {
			$lineitem->setDiscountAmount($item_data->discount_amount);
		}

		if (!empty($item_data->xero_TaxType)) {
			$lineitem->setTaxType($item_data->xero_TaxType);
		} else {
			$lineitem->setTaxType("NONE");
		}

		if (isset($item_data->account_code)) {
			$lineitem->setAccountCode($item_data->account_code);
		}

		return $lineitem;
	}

	public function createInvoiceAccRec($invoice_data = array(), $xeroTenantId, $apiInstance, $returnObj = false) {
		try {
			$invoice_data = json_decode(json_encode($invoice_data));

			$lineitems = [];
			foreach ($invoice_data->lineitems as $key => $lineitemData) {
				array_push($lineitems, $this->getLineItem($lineitemData));
			}

			$contactId = $invoice_data->client_xero_id;

			$contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
			$contact->setContactId($contactId);

			$invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;

			$invoice->setReference($invoice_data->ref_number)
				->setInvoiceNumber($invoice_data->invoice_number)
				->setDueDate(new DateTime($invoice_data->due_date))
				->setDate(new DateTime($invoice_data->txn_date))
				->setContact($contact)
				->setLineItems($lineitems)
				->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_AUTHORISED)
				->setType(XeroAPI\XeroPHP\Models\Accounting\Invoice::TYPE_ACCREC)
				->setLineAmountTypes(\XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes::EXCLUSIVE);
			$result = $apiInstance->createInvoices($xeroTenantId, $invoice);

			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function updateInvoice($invoice_data = array(), $invoiceID = null, $xeroTenantId, $apiInstance, $returnObj = false) {
		try {

			$invoice_data = json_decode(json_encode($invoice_data));

			$lineitems = [];
			foreach ($invoice_data->lineitems as $key => $lineitemData) {
				array_push($lineitems, $this->getLineItem($lineitemData));
			}

			$contactId = $invoice_data->client_xero_id;

			$contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
			$contact->setContactId($contactId);

			$invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;

			$invoice->setReference($invoice_data->ref_number)
				->setInvoiceNumber($invoice_data->invoice_number)
				->setDueDate(new DateTime($invoice_data->due_date))
				->setDate(new DateTime($invoice_data->txn_date))
				->setContact($contact)
				->setLineItems($lineitems)
				->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_AUTHORISED)
				->setType(XeroAPI\XeroPHP\Models\Accounting\Invoice::TYPE_ACCREC)
				->setLineAmountTypes(\XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes::EXCLUSIVE);

			$result = $apiInstance->updateInvoice($xeroTenantId, $invoiceID, $invoice);

			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function deleteInvoice($invoiceID = null, $xeroTenantId, $apiInstance, $returnObj = false) {
		try {
			$invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
			$invoice->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_DELETED);
			$result = $apiInstance->updateInvoice($xeroTenantId, $invoiceID, $invoice);
			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

/*==========================================================
//  Payments Methods CRUD
//==========================================================*/

	public function getBankAccounts($xeroTenantId, $apiInstance, $code = null, $returnObj = false, $account_data = array()) {
		try {
			// READ only ACTIVE
			$where = 'Status=="' . \XeroAPI\XeroPHP\Models\Accounting\Account::STATUS_ACTIVE . '" AND Type=="' . \XeroAPI\XeroPHP\Models\Accounting\Account::BANK_ACCOUNT_TYPE_BANK . '"';
			if (isset($account_data->xero_accountNumber) && isset($account_data->xero_hasSortCode) && $account_data->xero_hasSortCode = 1) {
				$where .= ' AND BankAccountNumber=="' . $account_data->xero_accountNumber . '"';
				$code = null;
			}

			if (!empty($code)) {
				$where .= ' AND Code=="' . $code . '"';
			}
			$result = $apiInstance->getAccounts($xeroTenantId, null, $where);

			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function createBankAccount($account_data = array(), $xeroTenantId, $apiInstance, $returnObj = false) {
		try {
			$account = new XeroAPI\XeroPHP\Models\Accounting\Account;
			$account->setName($account_data->name);
			$account->setType(\XeroAPI\XeroPHP\Models\Accounting\Account::BANK_ACCOUNT_TYPE_BANK);

			if (isset($account_data->xero_accountNumber) && isset($account_data->xero_hasSortCode) && $account_data->xero_hasSortCode == 1) {
				$account->setBankAccountNumber($account_data->xero_accountNumber);
			} else {
				$account->setBankAccountNumber(isset($account_data->xero_accountNumber) ? $account_data->accountNumber : '000000' . $account_data->code);
				$account->setCode($account_data->code);
			}

			$result = $apiInstance->createAccount($xeroTenantId, $account);
			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	/**
	 * CURRENTLY NOT SUPPORTED BY XERO
	 * @param  array   $account_data [description]
	 * @param  [type]  $accountId    [description]
	 * @param  [type]  $xeroTenantId [description]
	 * @param  [type]  $apiInstance  [description]
	 * @param  boolean $returnObj    [description]
	 * @return [type]                [description]
	 */
	public function updateBankAccount($account_data = array(), $accountId = null, $xeroTenantId, $apiInstance, $returnObj = false) {
		try {
			$account = new XeroAPI\XeroPHP\Models\Accounting\Account;
			$account->setName($account_data->name);

			$result = $apiInstance->updateAccount($xeroTenantId, $accountId, $account);
			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

/*==========================================================
//  Payments CRUD
//==========================================================*/
	public function getPayments($xeroTenantId, $apiInstance, $where = null, $returnObj = false) {
		try {
			$result = $apiInstance->getPayments($xeroTenantId, null, $where);

			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function getPayment($xeroTenantId, $apiInstance, $payment_id = null, $returnObj = false) {
		try {
			$result = $apiInstance->getPayment($xero_tenant_id, $payment_id);

			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function createPayment($payment_data, $xeroTenantId, $apiInstance, $returnObj = false) {
		try {
			$invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
			$invoice->setInvoiceID($payment_data->invoiceID);

			$bankaccount = new XeroAPI\XeroPHP\Models\Accounting\Account;
			$bankaccount->setAccountID($payment_data->accountID);

			$payment = new XeroAPI\XeroPHP\Models\Accounting\Payment;
			$payment->setInvoice($invoice)
				->setAccount($bankaccount)
				->setAmount($payment_data->totalAmt)
				->setDate(new DateTime($payment_data->txnDate))
				->setReference($payment_data->paymentRefNum);

			$result = $apiInstance->createPayment($xeroTenantId, $payment);

			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function deletePayment($xeroTenantId, $apiInstance, $paymentId = null, $returnObj = false) {
		try {
			$payment = new XeroAPI\XeroPHP\Models\Accounting\Payment;
			$payment->setPaymentID($paymentId)
				->setStatus(XeroAPI\XeroPHP\Models\Accounting\PAYMENT::STATUS_DELETED);

			$result = $apiInstance->deletePayment($xeroTenantId, $paymentId, $payment);

			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

/*==========================================================
//  Expenses Categories
//==========================================================*/

	public function createExpenseAccount($expenseAccount_data = array(), $xeroTenantId, $apiInstance, $returnObj = false) {
		try {
			$account = new XeroAPI\XeroPHP\Models\Accounting\Account;
			$account->setCode($expenseAccount_data->code);
			$account->setName($expenseAccount_data->name);
			$account->setType("EXPENSE");
			$account->setDescription($expenseAccount_data->description);
			$result = $apiInstance->createAccount($xeroTenantId, $account);
			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function updateExpenseAccount($expenseAccount_data = array(), $accountID = Null, $xeroTenantId, $apiInstance, $returnObj = false) {
		try {
			$account = new XeroAPI\XeroPHP\Models\Accounting\Account;
			$account->setName($expenseAccount_data->name);
			$account->setDescription($expenseAccount_data->description);
			$result = $apiInstance->updateAccount($xeroTenantId, $accountId, $account);
			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

/*==========================================================
//  Expenses CRUD
//==========================================================*/

	public function getExpense($xeroTenantId, $apiInstance, $invoiceId = null, $returnObj = false) {

		try {
			$result = $apiInstance->getInvoice($xeroTenantId, $invoiceId);
			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

	public function getExpenseLineItem($item_data) {

		$lineitem = new XeroAPI\XeroPHP\Models\Accounting\LineItem;
		$lineitem->setDescription($item_data->description)
			->setQuantity($item_data->qty)
			->setUnitAmount($item_data->rate)
			->setItemCode(format_xero_item_code_name($item_data->description));

		if (!empty($item_data->xero_TaxType)) {
			$lineitem->setTaxType($item_data->xero_TaxType);
		} else {
			$lineitem->setTaxType("NONE");
		}

		return $lineitem;
	}

	public function createInvoiceAccPay($invoice_data = array(), $xeroTenantId, $apiInstance, $returnObj = false) {
		try {
			$invoice_data = json_decode(json_encode($invoice_data));

			$lineitems = [];
			foreach ($invoice_data->lineitems as $key => $lineitemData) {
				array_push($lineitems, $this->getExpenseLineItem($lineitemData));
			}

			$contactId = $invoice_data->client_xero_id;

			$contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
			$contact->setContactId($contactId);

			$invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;

			$invoice->setReference($invoice_data->ref_number)
				->setDueDate(new DateTime($invoice_data->due_date))
				->setDate(new DateTime($invoice_data->txn_date))
				->setContact($contact)
				->setLineItems($lineitems)
				->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_AUTHORISED)
				->setType(XeroAPI\XeroPHP\Models\Accounting\Invoice::TYPE_ACCPAY)
				->setLineAmountTypes(\XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes::EXCLUSIVE);
			$result = $apiInstance->createInvoices($xeroTenantId, $invoice);

			if ($returnObj) {
				return $result;
			} else {
				return count($result);
			}
		} catch (XeroException $ex) {
			log_message('error', $ex->getMessage());
			return false;
		}
	}

}