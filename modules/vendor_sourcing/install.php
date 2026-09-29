<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$table_name = db_prefix() . 'item_vendors';

if (!$CI->db->table_exists(db_prefix() . 'pur_vendor')) {
    set_alert('danger', 'Vendor Sourcing module requires the Purchase Management (PUR) module to be active.');
    return;
}

if (!$CI->db->table_exists($table_name)) {
    $CI->db->query('CREATE TABLE `' . $table_name . '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `vendor_item_code` varchar(100) DEFAULT NULL,
  `last_purchase_price` decimal(15,2) DEFAULT NULL,
  `currency` int(11) DEFAULT NULL,
  `is_preferred` boolean DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `vendor_id` (`vendor_id`),
  CONSTRAINT `' . $table_name . '_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `' . db_prefix() . 'items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `' . $table_name . '_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `' . db_prefix() . 'pur_vendor` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Inquiry Log table
$log_table = db_prefix() . 'vendor_inquiry_log';
if (!$CI->db->table_exists($log_table)) {
    $CI->db->query('CREATE TABLE `' . $log_table . '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `vendor_id` int(11) NOT NULL,
  `vendor_name` varchar(255) DEFAULT NULL,
  `vendor_email` varchar(255) DEFAULT NULL,
  `vendor_phone` varchar(100) DEFAULT NULL,
  `channel` enum("email","sms") NOT NULL DEFAULT "email",
  `subject` varchar(500) DEFAULT NULL,
  `message_body` text DEFAULT NULL,
  `sent_to_hierarchy` tinyint(1) NOT NULL DEFAULT 0,
  `hierarchy_vendor_id` int(11) DEFAULT NULL COMMENT "The root vendor if sent to hierarchy",
  `sent_by` int(11) DEFAULT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum("sent","failed") NOT NULL DEFAULT "sent",
  `error_message` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_id` (`vendor_id`),
  KEY `item_id` (`item_id`),
  KEY `sent_at` (`sent_at`),
  KEY `channel` (`channel`)
) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Default settings for inquiry templates
$defaults = [
    'vs_inquiry_email_subject'  => 'Product Inquiry: {item_name}',
    'vs_inquiry_email_template' => '<p>Dear {vendor_name},</p>
<p>We are writing to inquire about the following product:</p>
<ul>
<li><strong>Product:</strong> {item_name}</li>
<li><strong>SKU:</strong> {item_sku}</li>
</ul>
<p>Please provide us with your best pricing, availability, and lead times for this product.</p>
<p>Best regards,<br>{sender_name}<br>{company_name}</p>',
    'vs_inquiry_sms_template'   => 'Hi {vendor_name}, inquiry for {item_name} (SKU: {item_sku}). Please reply with pricing & availability. - {company_name}',
    'vs_inquiry_sms_api_url'    => '',
    'vs_inquiry_sms_api_key'    => '',
    'vs_inquiry_sms_sender_id'  => '',
];

foreach ($defaults as $key => $val) {
    if (get_option($key) === false || get_option($key) === '') {
        // Only set if option doesn't exist
        $exists = $CI->db->where('name', $key)->get(db_prefix() . 'options')->row();
        if (!$exists) {
            add_option($key, $val);
        }
    }
}
