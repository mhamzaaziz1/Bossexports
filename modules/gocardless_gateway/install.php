<?php

defined('BASEPATH') or exit('No direct script access allowed');

add_option('gocardless_gateway', 'enable');

// Extend payments controller class
$controllerextfile = getcwd() . '/modules/gocardless_gateway/essentials/Payments.php';
if (file_exists($controllerextfile)) {
    rename($controllerextfile, getcwd() . '/application/controllers/Payments.php');
}

