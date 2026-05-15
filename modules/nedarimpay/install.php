<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// ─── TABLE: nedarimpay_transactions ──────────────────────────────────────────
// Stores every webhook callback received from Nedarim Plus, raw + parsed.
if (!$CI->db->table_exists(db_prefix() . 'nedarimpay_transactions')) {
    $CI->db->query('
        CREATE TABLE `' . db_prefix() . 'nedarimpay_transactions` (
            `id`                  INT(11)        NOT NULL AUTO_INCREMENT,
            `transaction_id`      VARCHAR(100)   NOT NULL COMMENT "TransactionId from Nedarim",
            `keva_id`             VARCHAR(100)   DEFAULT NULL COMMENT "KevaId — standing order ref",
            `client_id_nedarim`   VARCHAR(50)    DEFAULT NULL COMMENT "ClientId in Nedarim system",
            `perfex_client_id`    INT(11)        DEFAULT NULL COMMENT "Matched Perfex client userid",
            `perfex_invoice_id`   INT(11)        DEFAULT NULL COMMENT "Created Perfex invoice id",
            `perfex_payment_id`   INT(11)        DEFAULT NULL COMMENT "Created Perfex payment record id",
            `receipt_type`        ENUM("student","donation") NOT NULL DEFAULT "student",
            `receipt_number`      VARCHAR(50)    DEFAULT NULL COMMENT "Final receipt number issued",
            `zeout`               VARCHAR(20)    DEFAULT NULL,
            `client_name`         VARCHAR(150)   DEFAULT NULL,
            `email`               VARCHAR(150)   DEFAULT NULL,
            `phone`               VARCHAR(30)    DEFAULT NULL,
            `address`             VARCHAR(300)   DEFAULT NULL,
            `amount`              DECIMAL(15,2)  NOT NULL DEFAULT 0,
            `currency`            TINYINT(1)     NOT NULL DEFAULT 1 COMMENT "1=ILS 2=USD",
            `transaction_type`    VARCHAR(30)    DEFAULT NULL COMMENT "Ragil/Tashlumim/HK",
            `groupe`              VARCHAR(150)   DEFAULT NULL,
            `comments`            TEXT           DEFAULT NULL,
            `tashloumim`          INT(5)         DEFAULT NULL,
            `first_tashloum`      DECIMAL(15,2)  DEFAULT NULL,
            `last_num`            VARCHAR(10)    DEFAULT NULL COMMENT "Last 4 digits of card",
            `tokef`               VARCHAR(10)    DEFAULT NULL COMMENT "Card expiry",
            `confirmation`        VARCHAR(50)    DEFAULT NULL,
            `shovar`              VARCHAR(50)    DEFAULT NULL,
            `makor`               VARCHAR(50)    DEFAULT NULL COMMENT "Source of transaction",
            `masof_id`            VARCHAR(50)    DEFAULT NULL,
            `debit_iframe`        TINYINT(1)     DEFAULT 0,
            `transaction_time`    DATETIME       DEFAULT NULL,
            `raw_payload`         LONGTEXT       DEFAULT NULL COMMENT "Full JSON from Nedarim",
            `email_sent`          TINYINT(1)     NOT NULL DEFAULT 0,
            `email_sent_at`       DATETIME       DEFAULT NULL,
            `status`              ENUM("pending","processed","failed","duplicate") NOT NULL DEFAULT "pending",
            `error_message`       TEXT           DEFAULT NULL,
            `created_at`          DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_transaction_id` (`transaction_id`),
            KEY `idx_perfex_client`  (`perfex_client_id`),
            KEY `idx_receipt_type`   (`receipt_type`),
            KEY `idx_status`         (`status`),
            KEY `idx_created_at`     (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
    ');
}

// ─── TABLE: nedarimpay_keva (Standing Orders) ────────────────────────────────
// Stores הוראת קבע (recurring standing orders) setup notifications.
if (!$CI->db->table_exists(db_prefix() . 'nedarimpay_keva')) {
    $CI->db->query('
        CREATE TABLE `' . db_prefix() . 'nedarimpay_keva` (
            `id`                  INT(11)        NOT NULL AUTO_INCREMENT,
            `keva_id`             VARCHAR(100)   NOT NULL COMMENT "KevaId from Nedarim",
            `client_id_nedarim`   VARCHAR(50)    DEFAULT NULL,
            `perfex_client_id`    INT(11)        DEFAULT NULL,
            `zeout`               VARCHAR(20)    DEFAULT NULL,
            `client_name`         VARCHAR(150)   DEFAULT NULL,
            `email`               VARCHAR(150)   DEFAULT NULL,
            `phone`               VARCHAR(30)    DEFAULT NULL,
            `address`             VARCHAR(300)   DEFAULT NULL,
            `amount`              DECIMAL(15,2)  NOT NULL DEFAULT 0,
            `currency`            TINYINT(1)     NOT NULL DEFAULT 1,
            `next_date`           DATE           DEFAULT NULL,
            `last_num`            VARCHAR(10)    DEFAULT NULL,
            `tokef`               VARCHAR(10)    DEFAULT NULL,
            `groupe`              VARCHAR(150)   DEFAULT NULL,
            `comments`            TEXT           DEFAULT NULL,
            `tashloumim`          INT(5)         DEFAULT NULL COMMENT "Months count; NULL = unlimited",
            `masof_id`            VARCHAR(50)    DEFAULT NULL,
            `debit_iframe`        TINYINT(1)     DEFAULT 0,
            `receipt_type`        ENUM("student","donation") NOT NULL DEFAULT "student",
            `raw_payload`         LONGTEXT       DEFAULT NULL,
            `created_at`          DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_keva_id` (`keva_id`),
            KEY `idx_perfex_client` (`perfex_client_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
    ');
}

// ─── TABLE: nedarimpay_manual_charges ────────────────────────────────────────
// Tracks outbound occasional charges pushed FROM Perfex → Nedarim Plus.
if (!$CI->db->table_exists(db_prefix() . 'nedarimpay_manual_charges')) {
    $CI->db->query('
        CREATE TABLE `' . db_prefix() . 'nedarimpay_manual_charges` (
            `id`                  INT(11)        NOT NULL AUTO_INCREMENT,
            `perfex_client_id`    INT(11)        NOT NULL,
            `client_id_nedarim`   VARCHAR(50)    DEFAULT NULL,
            `amount`              DECIMAL(15,2)  NOT NULL,
            `currency`            TINYINT(1)     NOT NULL DEFAULT 1,
            `description`         VARCHAR(300)   DEFAULT NULL,
            `groupe`              VARCHAR(150)   DEFAULT NULL,
            `receipt_type`        ENUM("student","donation") NOT NULL DEFAULT "student",
            `charge_type`         ENUM("tuition","travel","other","donation") NOT NULL DEFAULT "other",
            `nedarim_response`    TEXT           DEFAULT NULL,
            `status`              ENUM("pending","sent","confirmed","failed") NOT NULL DEFAULT "pending",
            `staff_id`            INT(11)        DEFAULT NULL,
            `created_at`          DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_client` (`perfex_client_id`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
    ');
}

// ─── TABLE: nedarimpay_webhook_log ───────────────────────────────────────────
// Raw webhook log for debugging / audit trail.
if (!$CI->db->table_exists(db_prefix() . 'nedarimpay_webhook_log')) {
    $CI->db->query('
        CREATE TABLE `' . db_prefix() . 'nedarimpay_webhook_log` (
            `id`          INT(11)    NOT NULL AUTO_INCREMENT,
            `received_at` DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `ip`          VARCHAR(45) DEFAULT NULL,
            `payload`     LONGTEXT   DEFAULT NULL,
            `type`        VARCHAR(30) DEFAULT NULL COMMENT "transaction / keva",
            `result`      VARCHAR(50) DEFAULT NULL COMMENT "processed / duplicate / error",
            `notes`       TEXT       DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_received_at` (`received_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';
    ');
}

// ─── Options (Settings) ───────────────────────────────────────────────────────

// Nedarim Plus credentials
$defaults = [
    'nedarimpay_mosad_number'         => '',   // 7-digit institution number
    'nedarimpay_api_valid'            => '',   // ApiValid auth token
    'nedarimpay_api_key'              => '',   // Full API key URL / token
    'nedarimpay_webhook_secret'       => '',   // Optional HMAC secret for webhook validation

    // Receipt series — students (tuition + occasional)
    'nedarimpay_student_prefix'       => 'T',  // e.g. T-0001
    'nedarimpay_student_next_number'  => '1',

    // Receipt series — donations (completely separate)
    'nedarimpay_donation_prefix'      => 'D',  // e.g. D-0001
    'nedarimpay_donation_next_number' => '1',

    // Email templates
    'nedarimpay_student_email_subject'  => 'Receipt for your payment',
    'nedarimpay_student_email_body'     => 'Dear {client_name}, please find your receipt attached.',
    'nedarimpay_donation_email_subject' => 'Thank you for your donation',
    'nedarimpay_donation_email_body'    => 'Dear {client_name}, thank you for your generous donation. Please find your receipt attached.',

    // Nedarim Groupe mapping
    'nedarimpay_student_groupe'       => '',   // Groupe value that means "student"
    'nedarimpay_donation_groupe'      => '',   // Groupe value that means "donation"

    // Auto-match client by
    'nedarimpay_match_field'          => 'email', // email | phone | zeout | nedarim_client_id

    // Payment mode ID in Perfex to use for Nedarim payments
    'nedarimpay_payment_mode_id'      => '',
];

foreach ($defaults as $key => $value) {
    if (get_option($key) === null || get_option($key) === false) {
        add_option($key, $value);
    }
}
