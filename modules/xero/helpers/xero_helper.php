<?php
//error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');

function get_xero_clientID() {
	return get_option('xero_client_id');
}
function get_xero_clientSecret() {
	return get_option('xero_client_secret');
}

if (!function_exists('get_field_value')) {
/**
 * Helps to get a specific value in atable field
 * @param  [type] $table [description]
 * @param  array  $where [description]
 * @param  [type] $check [description]
 * @return [type]        [description]
 */

	function get_field_value($table, $where = array(), $check) {
		$CI = &get_instance();
		$CI->db->where($where);
		$data = $CI->db->get($table)->row();
		if (!empty($data)) {
			return $data->$check;
		} else {
			return null;
		}
	}
}

/**
 * Chart of Accounts body
 * @param  [type] $_account [description]
 * @return [type]           [description]
 */
function print_xero_account_chart($_account) {
	$CI = &get_instance();

	$chart_output = "";
	$i_g = 1;
	foreach ($_account as $g_key => $group) {
		$group_no = sprintf("%02s", $i_g); ++$i_g;
		$chart_output .= '<tr class="tr-group tr-root-group"><td>' . $group_no . '</td><td>' . $g_key . '</td><td>Group</td><td>-</td></tr>';
		$i_sg = 1;
		foreach ($group as $sg_key => $sub_group) {
			$sub_group_no = sprintf("%02s", $i_sg); ++$i_sg;
			$chart_output .= '<tr class="tr-group tr-sub-group"><td>' . print_space(1) . $group_no . '-' . $sub_group_no . '</td><td>' . print_space(1) . $sg_key . '</td><td>Group</td><td>-</td></tr>';
			$i_a = 1;
			foreach ($sub_group as $l_key => $ledger) {
				$ledger_no = sprintf("%04s", $i_a); ++$i_a;
				$chart_output .= '<tr class="tr-ledger"><td>' . print_space(2) . $group_no . '-' . $sub_group_no . '-' . $ledger_no . '</td><td>' . print_space(2) . $ledger->Name . '</td><td>Ledger</td><td>' . $ledger->Code . '</td></tr>';
			}
		}
	}

	if (get_option('xero_access_token') && time() < get_option('xero_expiration_date')):
		return $chart_output;
	else:return null;
	endif;
}

if (!function_exists('print_space')) {

/**
 * Add spaces to chart of acc
 * @param  [type] $count [description]
 * @return [type]        [description]
 */
	function print_space($count) {
		$html = '';
		for ($i = 1; $i <= $count; $i++) {
			$html .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		return $html;
	}
}

function get_xero_customer_groups($id) {
	$CI = &get_instance();
	$CI->load->model('xero_model');
	return $CI->xero_model->get_xero_customer_groups($id);
}

/**
 * Get Quick Books TAX RATE ID
 * @param  [type] $tax_id [description]
 * @return [type]         [description]
 */
function get_xero_TaxType_from_tax_id($tax_id) {
	$CI = &get_instance();
	$CI->load->model('xero_model');
	$result = $CI->xero_model->getTax(['taxes.id' => $tax_id]);
	if ($result) {
		return $result->xero_TaxType;
	} else {
		return '';
	}

}

/**
 * Function that return invoice item taxes based on passed item id
 * @param  mixed $itemid
 * @return array
 */
function get_invoice_item_tax_xero_id($itemid) {
	$CI = &get_instance();
	$CI->db->where('itemid', $itemid);
	$CI->db->where('rel_type', 'invoice');
	$taxes = $CI->db->get(db_prefix() . 'item_tax')->result_array();
	$i = 0;
	$taxes_qb = array();
	foreach ($taxes as $tax) {

		$taxes_r = $CI->db->where(array('name' => $tax['taxname'], 'taxrate' => $tax['taxrate']))->get(db_prefix() . 'taxes')->row();
		$taxes_qb[] = get_xero_TaxType_from_tax_id($taxes_r->id);
	}

	return $taxes_qb;
}

/**
 * Get Quick Books CUSOMER ID
 * @param  [type] $client_id [description]
 * @return [type]            [description]
 */
function get_xero_id_from_client_id($client_id) {
	$CI = &get_instance();
	$CI->load->model('clients_model');
	$result = $CI->clients_model->get($client_id);
	return $result->xero_id;
}

function format_xero_item_code_name($string) {
	return preg_replace('/(^([^a-zA-Z0-9])*|([^a-zA-Z0-9])*$)/', '', preg_replace("/[\W_]+/u", '-', strtoupper($string)));
}

function format_xero_item_name($string) {
	return preg_replace('/(^([^a-zA-Z0-9])*|([^a-zA-Z0-9])*$)/', '', preg_replace("/[\W_]+/u", ' ', $string));
}

function format_xero_item_description($string) {
	return preg_replace("/[[:blank:]]+/", " ", preg_replace("/\r|\n/", " ", $string));
}