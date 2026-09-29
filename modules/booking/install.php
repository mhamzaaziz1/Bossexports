<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$CI->load->helper('booking' . '/booking_database');

init_booking_install_sequence();
