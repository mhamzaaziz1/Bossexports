<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_100_telecalling_ld extends App_Migration
{
    public function up()
    {
        $CI  =& get_instance();
        $pfx = $CI->db->dbprefix;

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$pfx}tc_groups` (
            `id`                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name`               VARCHAR(100) NOT NULL,
            `description`        TEXT,
            `manager_id`         INT UNSIGNED,
            `color`              VARCHAR(7) DEFAULT '#1F4E79',
            `weekly_call_target` INT DEFAULT 0,
            `active`             TINYINT(1) DEFAULT 1,
            `leader_can_assign`  TINYINT(1) DEFAULT 0,
            `created_at`         DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$pfx}tc_group_members` (
            `id`       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `group_id` INT UNSIGNED NOT NULL,
            `agent_id` INT UNSIGNED NOT NULL,
            UNIQUE KEY `uniq_member` (`group_id`,`agent_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$pfx}tc_assignments` (
            `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `lead_id`      INT UNSIGNED NOT NULL,
            `agent_id`     INT UNSIGNED NOT NULL,
            `group_id`     INT UNSIGNED,
            `status`       VARCHAR(30) DEFAULT 'pending',
            `completed`    TINYINT(1) DEFAULT 0,
            `assigned_by`  INT UNSIGNED,
            `assigned_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
            `completed_at` DATETIME,
            UNIQUE KEY `uniq_lead` (`lead_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$pfx}tc_call_log` (
            `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `lead_id`       INT UNSIGNED NOT NULL,
            `agent_id`      INT UNSIGNED NOT NULL,
            `call_status`   VARCHAR(30) NOT NULL,
            `notes`         TEXT,
            `lead_score`    INT DEFAULT 0,
            `ai_score`      INT DEFAULT 0,
            `ai_suggestion` TEXT,
            `called_at`     DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_agent_date` (`agent_id`,`called_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$pfx}tc_targets` (
            `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `agent_id`     INT UNSIGNED,
            `group_id`     INT UNSIGNED,
            `target_type`  ENUM('calls','appointments','conversions','emails_sent','customer_conversions') NOT NULL,
            `period`       ENUM('daily','weekly','monthly') NOT NULL,
            `target_value` INT NOT NULL DEFAULT 0,
            `start_date`   DATE NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$pfx}tc_campaigns` (
            `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name`          VARCHAR(150) NOT NULL,
            `description`   TEXT,
            `trigger_score` INT DEFAULT 0,
            `status`        ENUM('active','paused') DEFAULT 'active',
            `created_by`    INT UNSIGNED,
            `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$pfx}tc_templates` (
            `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `campaign_id` INT UNSIGNED NOT NULL,
            `subject`     VARCHAR(250) NOT NULL,
            `body`        LONGTEXT NOT NULL,
            `delay_days`  INT DEFAULT 0,
            `sort_order`  INT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$pfx}tc_email_log` (
            `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `lead_id`       INT UNSIGNED NOT NULL,
            `agent_id`      INT UNSIGNED,
            `campaign_id`   INT UNSIGNED,
            `template_id`   INT UNSIGNED,
            `to_email`      VARCHAR(200),
            `subject`       VARCHAR(250),
            `track_token`   VARCHAR(64) UNIQUE,
            `sent_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
            `opened_at`     DATETIME,
            `clicked_at`    DATETIME,
            `replied`       TINYINT(1) DEFAULT 0,
            `bounced`       TINYINT(1) DEFAULT 0,
            `bounce_reason` VARCHAR(250),
            `unsubscribed`  TINYINT(1) DEFAULT 0,
            KEY `idx_lead`  (`lead_id`),
            KEY `idx_token` (`track_token`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$pfx}tc_email_queue` (
            `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `lead_id`     INT UNSIGNED NOT NULL,
            `template_id` INT UNSIGNED NOT NULL,
            `campaign_id` INT UNSIGNED NOT NULL,
            `send_after`  DATETIME NOT NULL,
            `status`      ENUM('pending','sent','failed','skipped') DEFAULT 'pending',
            `attempts`    INT DEFAULT 0,
            `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_pending` (`status`,`send_after`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$pfx}tc_unsubscribes` (
            `id`       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `email`    VARCHAR(200) NOT NULL,
            `lead_id`  INT UNSIGNED,
            `unsub_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `reason`   VARCHAR(100) DEFAULT 'user_request',
            UNIQUE KEY `uniq_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$pfx}tc_rotation_queue` (
            `id`       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `lead_id`  INT UNSIGNED NOT NULL UNIQUE,
            `priority` INT DEFAULT 0,
            `added_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$pfx}tc_lead_requests` (
            `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `agent_id`     INT UNSIGNED NOT NULL,
            `requested`    INT DEFAULT 10,
            `message`      TEXT,
            `status`       ENUM('pending','approved','rejected') DEFAULT 'pending',
            `admin_note`   TEXT,
            `actioned_by`  INT UNSIGNED,
            `actioned_at`  DATETIME,
            `requested_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$pfx}tc_whatsapp_log` (
            `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `lead_id`   INT UNSIGNED NOT NULL,
            `agent_id`  INT UNSIGNED,
            `direction` ENUM('outbound','inbound') DEFAULT 'outbound',
            `message`   TEXT,
            `status`    VARCHAR(30) DEFAULT 'sent',
            `wa_msg_id` VARCHAR(100),
            `sent_at`   DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$pfx}tc_settings` (
            `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `skey`       VARCHAR(100) NOT NULL UNIQUE,
            `svalue`     TEXT,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    public function down()
    {
        $CI  =& get_instance();
        $pfx = $CI->db->dbprefix;
        foreach ([
            'tc_settings','tc_whatsapp_log','tc_lead_requests','tc_rotation_queue',
            'tc_unsubscribes','tc_email_queue','tc_email_log','tc_templates',
            'tc_campaigns','tc_targets','tc_call_log','tc_assignments',
            'tc_group_members','tc_groups',
        ] as $t) {
            $CI->db->query("DROP TABLE IF EXISTS `{$pfx}{$t}`");
        }
    }
}
