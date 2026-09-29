<?php
defined('BASEPATH') or exit('No direct script access allowed');
// uninstall.php — Drops all module tables. Mirrors install.php
$CI =& get_instance();
$pfx = $CI->db->dbprefix;
$tables = [
    'tc_settings','tc_whatsapp_log','tc_lead_requests','tc_rotation_queue',
    'tc_unsubscribes','tc_email_queue','tc_email_log','tc_templates',
    'tc_campaigns','tc_targets','tc_call_log','tc_assignments',
    'tc_group_members','tc_groups',
];
foreach ($tables as $t) {
    $CI->db->query("DROP TABLE IF EXISTS `{$pfx}{$t}`");
}
