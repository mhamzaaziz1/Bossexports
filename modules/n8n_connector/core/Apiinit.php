<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Apiinit {

    public static function the_da_vinci_code($module_name) {

        return true;
    }

    public static function ease_of_mind($module_name) {

        return true;
    }

    public static function activate($module) {

        return true;
    }


    public static function pre_validate($module_name, $code = '', $username = '') {

        return ['status' => true];
    }

    private static function downloadUpdate($module_name, $code, $username, $return, $supported_until) {

        return ['status' => true, 'message' => 'Activated...'];
    }
}
