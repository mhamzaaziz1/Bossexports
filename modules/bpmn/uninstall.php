<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

delete_option('staff_members_create_inline_bpmn_group');

$CI->db->query('DROP TABLE `' . db_prefix() . 'bpmn`');
$CI->db->query('DROP TABLE `' . db_prefix() . 'bpmn_groups`');
