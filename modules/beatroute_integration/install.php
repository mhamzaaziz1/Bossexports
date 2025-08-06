<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Permissions are handled by the register_staff_capabilities function in the main module file

// Create necessary tables for Beatroute integration

// Table for storing Beatroute API configuration
if (!$CI->db->table_exists(db_prefix() . 'beatroute_api_config')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'beatroute_api_config` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `api_key` VARCHAR(255) NOT NULL,
        `api_secret` VARCHAR(255) NOT NULL,
        `api_url` VARCHAR(255) NOT NULL,
        `webhook_secret` VARCHAR(255) NULL,
        `active` TINYINT(1) NOT NULL DEFAULT 1,
        `last_sync_time` DATETIME NULL,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Table for storing SKUs
if (!$CI->db->table_exists(db_prefix() . 'beatroute_skus')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'beatroute_skus` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `beatroute_id` VARCHAR(255) NOT NULL,
        `item_id` INT(11) NULL,
        `name` VARCHAR(255) NOT NULL,
        `description` TEXT NULL,
        `price` DECIMAL(15,2) NOT NULL,
        `sku_code` VARCHAR(255) NOT NULL,
        `status` VARCHAR(50) NOT NULL,
        `last_sync` DATETIME NOT NULL,
        `sync_status` VARCHAR(50) NOT NULL DEFAULT "pending",
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `beatroute_id` (`beatroute_id`),
        KEY `item_id` (`item_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Table for storing customers
if (!$CI->db->table_exists(db_prefix() . 'beatroute_customers')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'beatroute_customers` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `beatroute_id` VARCHAR(255) NOT NULL,
        `client_id` INT(11) NULL,
        `first_name` VARCHAR(255) NOT NULL,
        `last_name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(50) NULL,
        `address` TEXT NULL,
        `city` VARCHAR(100) NULL,
        `state` VARCHAR(100) NULL,
        `zip` VARCHAR(20) NULL,
        `country` VARCHAR(100) NULL,
        `status` VARCHAR(50) NOT NULL,
        `last_sync` DATETIME NOT NULL,
        `sync_status` VARCHAR(50) NOT NULL DEFAULT "pending",
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `beatroute_id` (`beatroute_id`),
        KEY `client_id` (`client_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Table for storing invoices
if (!$CI->db->table_exists(db_prefix() . 'beatroute_invoices')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'beatroute_invoices` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `beatroute_id` VARCHAR(255) NOT NULL,
        `invoice_id` INT(11) NULL,
        `beatroute_customer_id` VARCHAR(255) NOT NULL,
        `invoice_number` VARCHAR(255) NOT NULL,
        `date` DATE NOT NULL,
        `due_date` DATE NOT NULL,
        `currency` VARCHAR(10) NOT NULL,
        `subtotal` DECIMAL(15,2) NOT NULL,
        `total` DECIMAL(15,2) NOT NULL,
        `status` VARCHAR(50) NOT NULL,
        `last_sync` DATETIME NOT NULL,
        `sync_status` VARCHAR(50) NOT NULL DEFAULT "pending",
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `beatroute_id` (`beatroute_id`),
        KEY `invoice_id` (`invoice_id`),
        KEY `beatroute_customer_id` (`beatroute_customer_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Table for storing invoice items
if (!$CI->db->table_exists(db_prefix() . 'beatroute_invoice_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'beatroute_invoice_items` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `beatroute_invoice_id` VARCHAR(255) NOT NULL,
        `beatroute_sku_id` VARCHAR(255) NOT NULL,
        `description` TEXT NOT NULL,
        `qty` DECIMAL(15,2) NOT NULL,
        `rate` DECIMAL(15,2) NOT NULL,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `beatroute_invoice_id` (`beatroute_invoice_id`),
        KEY `beatroute_sku_id` (`beatroute_sku_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Table for storing payments
if (!$CI->db->table_exists(db_prefix() . 'beatroute_payments')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'beatroute_payments` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `beatroute_id` VARCHAR(255) NOT NULL,
        `payment_id` INT(11) NULL,
        `beatroute_invoice_id` VARCHAR(255) NOT NULL,
        `amount` DECIMAL(15,2) NOT NULL,
        `payment_mode` VARCHAR(100) NOT NULL,
        `payment_date` DATE NOT NULL,
        `transaction_id` VARCHAR(255) NULL,
        `status` VARCHAR(50) NOT NULL,
        `last_sync` DATETIME NOT NULL,
        `sync_status` VARCHAR(50) NOT NULL DEFAULT "pending",
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `beatroute_id` (`beatroute_id`),
        KEY `payment_id` (`payment_id`),
        KEY `beatroute_invoice_id` (`beatroute_invoice_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Table for storing sync logs
if (!$CI->db->table_exists(db_prefix() . 'beatroute_sync_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'beatroute_sync_logs` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `entity_type` VARCHAR(50) NOT NULL,
        `entity_id` VARCHAR(255) NOT NULL,
        `action` VARCHAR(50) NOT NULL,
        `status` VARCHAR(50) NOT NULL,
        `message` TEXT NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `entity_type` (`entity_type`),
        KEY `entity_id` (`entity_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Add default options
add_option('beatroute_integration_enabled', 1);
add_option('beatroute_sync_interval', 'hourly');
add_option('beatroute_auto_sync_enabled', 1);
add_option('beatroute_webhook_enabled', 1);
