<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'zatca_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'zatca_logs` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `invoice_id` int(11) NOT NULL,
      `uuid` varchar(255) DEFAULT NULL,
      `hash` text DEFAULT NULL,
      `xml_path` text DEFAULT NULL,
      `qr_code` text DEFAULT NULL,
      `status` varchar(50) DEFAULT NULL COMMENT "REPORTED, CLEARED, REJECTED, FAILED",
      `api_response` longtext DEFAULT NULL,
      `response_code` varchar(10) DEFAULT NULL,
      `reported_at` datetime DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Add logs invoice_id index
$CI->db->query('ALTER TABLE `' . db_prefix() . 'zatca_logs` ADD INDEX(`invoice_id`);');


// Default Options
if (get_option('zatca_mode') === null) {
    add_option('zatca_mode', 'sandbox'); // sandbox or production
}

if (get_option('zatca_otp') === null) {
    add_option('zatca_otp', '');
}

if (get_option('zatca_csr_common_name') === null) {
    add_option('zatca_csr_common_name', '');
}

// Store Compliance CSID (Temporary for onboarding)
if (get_option('zatca_compliance_csid') === null) {
    add_option('zatca_compliance_csid', '');
}

// Store Compliance Secret
if (get_option('zatca_compliance_secret') === null) {
    add_option('zatca_compliance_secret', '');
}

// Store Production CSID (Used for signing)
if (get_option('zatca_production_csid') === null) {
    add_option('zatca_production_csid', '');
}

// Store Production Secret
if (get_option('zatca_production_secret') === null) {
    add_option('zatca_production_secret', '');
}

// Private Key (Should be secured, maybe encrypted, but for now stored in options)
if (get_option('zatca_private_key') === null) {
    add_option('zatca_private_key', '');
}

if (get_option('zatca_public_key') === null) {
    add_option('zatca_public_key', '');
}
