<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!get_option('cfg_decimal_places')) {
    add_option('cfg_decimal_places', '2');
}
