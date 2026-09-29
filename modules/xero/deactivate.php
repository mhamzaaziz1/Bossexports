<?php

defined('BASEPATH') or exit('No direct script access allowed');

$config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken(get_option('xero_access_token'));

$identityApi = new XeroAPI\XeroPHP\Api\IdentityApi(
	new GuzzleHttp\Client(),
	$config
);

$CI = &get_instance();
$CI->load->library(XERO_MODULE_NAME . '/xero_module');
$CI->xero_module->deleteConnection(get_option('xero_tenant_id'), $identityApi);

update_option('xero_access_token', '');
update_option('xero_refresh_token', '');
update_option('xero_expiration_date', '');
update_option('xero_is_expired', '');
update_option('xero_realmid', '');
update_option('xero_refresh_token_expires_in', '');
update_option('xero_tenant_id', '');