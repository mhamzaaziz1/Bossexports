<?php
defined('BASEPATH') or exit('No direct script access allowed');

$route['api/customers/search_phone/(:any)'] = 'customer_phone_search/search/phone/$1';
