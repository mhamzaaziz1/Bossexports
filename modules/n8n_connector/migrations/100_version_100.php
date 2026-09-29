<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_100 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        // General settings
        add_option('n8n_connector_enabled', 1);
        add_option('n8n_connector_debug_mode', 1);
        add_option('n8n_connector_enable_logging', 1);
        add_option('n8n_connector_log_retention_days', 30);
        add_option('n8n_connector_default_timeout', 5);

        // Security settings
        add_option('n8n_connector_enable_signatures', 1);
        add_option('n8n_connector_default_secret_key', bin2hex(random_bytes(32)));

        // Retry settings
        add_option('n8n_connector_retry_enabled', 1);
        add_option('n8n_connector_max_retries', 3);
        add_option('n8n_connector_retry_delay', 60);

        // Cron-based feature settings
        add_option('n8n_connector_auto_remove_logs', 1); // Enabled by default

        // Table 1: Webhooks Configuration
        if (!$CI->db->table_exists(db_prefix() . 'n8n_webhooks')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'n8n_webhooks` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `webhook_url` text NOT NULL,
                `secret_key` varchar(255) DEFAULT NULL,
                `is_active` tinyint(1) DEFAULT 1,
                `run_in_background` tinyint(1) NOT NULL DEFAULT 0 COMMENT "Execute webhook via cron queue (0=immediate, 1=background)",
                `include_relationships` tinyint(1) DEFAULT 1,
                `enabled_events` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`enabled_events`)),
                `custom_headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_headers`)),
                `last_triggered_at` datetime DEFAULT NULL,
                `total_triggers` int(11) DEFAULT 0,
                `total_failures` int(11) DEFAULT 0,
                `created_by` int(11) DEFAULT NULL,
                `created_at` datetime DEFAULT current_timestamp(),
                `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `is_active` (`is_active`),
                KEY `created_by` (`created_by`)
                ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }

        // Table 2: Webhook Delivery Logs
        if (!$CI->db->table_exists(db_prefix() . 'n8n_webhook_logs')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'n8n_webhook_logs` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `webhook_id` int(11) NOT NULL,
                `event_id` varchar(100) NOT NULL,
                `event_type` varchar(100) NOT NULL,
                `resource_type` varchar(50) NOT NULL,
                `resource_id` int(11) NOT NULL,
                `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
                `request_headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_headers`)),
                `response_code` int(11) DEFAULT NULL,
                `response_body` text DEFAULT NULL,
                `response_time_ms` int(11) DEFAULT NULL,
                `status` enum("pending","success","failed","retry") DEFAULT "pending",
                `attempt_number` int(11) DEFAULT 1,
                `error_message` text DEFAULT NULL,
                `triggered_at` datetime DEFAULT current_timestamp(),
                `completed_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `event_id` (`event_id`),
                KEY `webhook_id` (`webhook_id`),
                KEY `status` (`status`),
                KEY `triggered_at` (`triggered_at`),
                CONSTRAINT `fk_n8n_logs_webhook`
                    FOREIGN KEY (`webhook_id`)
                    REFERENCES `' . db_prefix() . 'n8n_webhooks` (`id`)
                    ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }

        // Table 3: Webhook Queue for cron-based delivery
        if (!$CI->db->table_exists(db_prefix() . 'n8n_webhook_queue')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'n8n_webhook_queue` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `log_id` int(11) DEFAULT NULL,
                `webhook_id` int(11) NOT NULL,
                `event_id` varchar(100) NOT NULL,
                `event_type` varchar(100) NOT NULL,
                `resource_type` varchar(50) NOT NULL,
                `resource_id` int(11) NOT NULL,
                `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
                `request_headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_headers`)),
                `status` enum("pending","processing","completed","failed") DEFAULT "pending",
                `retry_count` int(11) DEFAULT 0,
                `next_retry_at` datetime DEFAULT NULL,
                `created_at` datetime DEFAULT current_timestamp(),
                `processed_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `status` (`status`),
                KEY `webhook_id` (`webhook_id`),
                KEY `next_retry_at` (`next_retry_at`),
                CONSTRAINT `fk_n8n_queue_log`
                    FOREIGN KEY (`log_id`)
                    REFERENCES `' . db_prefix() . 'n8n_webhook_logs` (`id`)
                    ON DELETE SET NULL,
                CONSTRAINT `fk_n8n_queue_webhook`
                    FOREIGN KEY (`webhook_id`)
                    REFERENCES `' . db_prefix() . 'n8n_webhooks` (`id`)
                    ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }
    }

    public function down()
    {
        // Write your code here
    }
}
