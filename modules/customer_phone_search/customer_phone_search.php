<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Customer Phone Search
Description: API endpoint to search customers by phone using the `api` module authentication.
Version: 1.0.0
Requires at least: 2.3.*
*/

define('CUSTOMER_PHONE_SEARCH_MODULE_NAME', 'customer_phone_search');

/**
* Register activation module hook
*/
register_activation_hook(CUSTOMER_PHONE_SEARCH_MODULE_NAME, 'customer_phone_search_module_activation_hook');

function customer_phone_search_module_activation_hook()
{
    // No installation needed
}
