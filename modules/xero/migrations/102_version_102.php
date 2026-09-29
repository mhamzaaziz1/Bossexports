<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_102 extends App_module_migration {
	public function __construct() {
		parent::__construct();
	}

	public function up() {
		$CI = &get_instance();
		if (!$CI->db->field_exists("xero_accountNumber", db_prefix() . 'payment_modes')) {
			$CI->db->query("ALTER TABLE " . db_prefix() . 'payment_modes' . " ADD xero_accountNumber varchar(100) DEFAULT NULL");
			$CI->db->query("ALTER TABLE " . db_prefix() . 'payment_modes' . " ADD xero_hasSortCode TINYINT(1) DEFAULT 0");
		}
	}
}
