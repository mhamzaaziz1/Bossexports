<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_103 extends App_module_migration {

	public function up()
	{
		// Create support hour packages table
		if ( ! $this->ci->db->table_exists(db_prefix().'wmm_support_packages'))
		{
			$this->ci->db->query(
				'CREATE TABLE `'.db_prefix()."wmm_support_packages` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `client_id` int(11) NOT NULL,
                  `website_id` int(11) DEFAULT NULL COMMENT 'NULL means package is for all client websites',
                  `package_name` varchar(255) NOT NULL,
                  `total_hours` decimal(10,2) NOT NULL,
                  `hours_used` decimal(10,2) DEFAULT 0.00,
                  `hours_remaining` decimal(10,2) NOT NULL,
                  `hourly_rate` decimal(15,2) DEFAULT NULL,
                  `package_price` decimal(15,2) DEFAULT NULL,
                  `low_balance_threshold` decimal(10,2) DEFAULT 2.00 COMMENT 'Hours threshold for low balance notification',
                  `low_balance_notify` tinyint(1) DEFAULT 0,
                  `status` enum('active','exhausted','expired','cancelled') DEFAULT 'active',
                  `start_date` date DEFAULT NULL,
                  `expiry_date` date DEFAULT NULL,
                  `invoice_id` int(11) DEFAULT NULL,
                  `notes` text,
                  `created_by` int(11) NOT NULL,
                  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `client_id` (`client_id`),
                  KEY `website_id` (`website_id`),
                  KEY `status` (`status`),
                  KEY `invoice_id` (`invoice_id`),
                  CONSTRAINT `fk_wmm_support_packages_client` FOREIGN KEY (`client_id`) REFERENCES `".db_prefix()."clients` (`userid`) ON DELETE CASCADE,
                  CONSTRAINT `fk_wmm_support_packages_website` FOREIGN KEY (`website_id`) REFERENCES `".db_prefix()."wmm_websites` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.';',
			);
		}

		// Create package usage history table to track which logs consumed hours
		if ( ! $this->ci->db->table_exists(db_prefix().'wmm_package_usage'))
		{
			$this->ci->db->query(
				'CREATE TABLE `'.db_prefix()."wmm_package_usage` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `package_id` int(11) NOT NULL,
                  `log_id` int(11) NOT NULL,
                  `hours_consumed` decimal(10,2) NOT NULL,
                  `consumed_at` datetime DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `package_id` (`package_id`),
                  KEY `log_id` (`log_id`),
                  CONSTRAINT `fk_wmm_package_usage_package` FOREIGN KEY (`package_id`) REFERENCES `".db_prefix()."wmm_support_packages` (`id`) ON DELETE CASCADE,
                  CONSTRAINT `fk_wmm_package_usage_log` FOREIGN KEY (`log_id`) REFERENCES `".db_prefix()."wmm_maintenance_logs` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.';',
			);
		}

		// Add package_id field to maintenance logs table
		if ( ! $this->ci->db->field_exists('package_id', db_prefix().'wmm_maintenance_logs'))
		{
			$this->ci->db->query(
				'ALTER TABLE `'.db_prefix().'wmm_maintenance_logs`
                 ADD COLUMN `package_id` int(11) DEFAULT NULL AFTER `invoice_created`,
                 ADD KEY `package_id` (`package_id`),
                 ADD CONSTRAINT `fk_wmm_maintenance_logs_package` FOREIGN KEY (`package_id`) REFERENCES `'.db_prefix().'wmm_support_packages` (`id`) ON DELETE SET NULL',
			);
		}
		// Add deducted_from_package field to track if hours were deducted
		if ( ! $this->ci->db->field_exists('deduct_from_package', db_prefix().'wmm_maintenance_logs'))
		{
			$this->ci->db->query(
				'ALTER TABLE `'.db_prefix().'wmm_maintenance_logs`
                 ADD COLUMN `deduct_from_package` tinyint(1) DEFAULT 0 AFTER `package_id`',
			);
		}

		// Add deducted_from_package field to track if hours were deducted
		if ( ! $this->ci->db->field_exists('deducted_from_package', db_prefix().'wmm_maintenance_logs'))
		{
			$this->ci->db->query(
				'ALTER TABLE `'.db_prefix().'wmm_maintenance_logs`
                 ADD COLUMN `deducted_from_package` tinyint(1) DEFAULT 0 AFTER `deduct_from_package`',
			);
		}

		// Check if email template already exists
		$email_template_exists = $this->ci->db
			->where('slug', 'wmm-package-low-balance')
			->get(db_prefix().'emailtemplates')
			->num_rows();

		if ($email_template_exists == 0)
		{
			$this->add_low_balance_email_template();
		}

		log_activity('Website Maintenance Management: Support Packages feature installed (v1.0.3)');
	}

	/**
	 * Add low balance notification email template
	 */
	public function add_low_balance_email_template(): void
	{
		// Get all available languages
		$languages = $this->ci->app->get_available_languages();

		foreach ($languages as $language)
		{
			$template = [
				'type'      => 'website_maintenance',
				'slug'      => 'wmm-package-low-balance',
				'language'  => $language,
				'name'      => 'Support Package Low Balance Warning'.($language != 'english' ? ' ['.$language.']' : ''),
				'subject'   => 'Low Balance Alert - {package_name}',
				'message'   => '<p>Dear {client_name},</p>

<p>This is to inform you that your support hour package is running low on available hours.</p>

<p><strong>Package Details:</strong></p>
<ul>
    <li><strong>Package Name:</strong> {package_name}</li>
    <li><strong>Website/Project:</strong> {website_info}</li>
    <li><strong>Hours Remaining:</strong> <span style="color: #dc2626; font-weight: bold;">{hours_remaining} hours</span></li>
    <li><strong>Total Hours:</strong> {total_hours} hours</li>
    <li><strong>Hours Used:</strong> {hours_used} hours</li>
    <li><strong>Expiry Date:</strong> {expiry_date}</li>
</ul>

<p><strong style="color: #dc2626;">⚠️ Warning:</strong> Your package balance has dropped below the threshold of {threshold_hours} hours.</p>

<p>We recommend topping up your package or purchasing a new one to ensure uninterrupted maintenance services.</p>

<p>If you have any questions or would like to purchase additional hours, please don\'t hesitate to contact us.</p>

<p>Best regards,<br>{company_name}</p>',
				'fromname'  => '{companyname} | CRM',
				'fromemail' => '',
				'plaintext' => 0,
				'active'    => 1,
				'order'     => 3,
			];

			$this->ci->db->insert(db_prefix().'emailtemplates', $template);
		}

		log_activity('Website Maintenance Management: Low balance email template created');
	}

	public function down(): void
	{
		// Remove email template
		$this->ci->db->where('slug', 'wmm-package-low-balance');
		$this->ci->db->delete(db_prefix().'emailtemplates');

		// Remove foreign key constraints first
		if ($this->ci->db->field_exists('package_id', db_prefix().'wmm_maintenance_logs'))
		{
			$this->ci->db->query('ALTER TABLE `'.db_prefix().'wmm_maintenance_logs` DROP FOREIGN KEY `fk_wmm_maintenance_logs_package`');
			$this->ci->db->query('ALTER TABLE `'.db_prefix().'wmm_maintenance_logs` DROP COLUMN `package_id`, DROP COLUMN `deducted_from_package`');
		}

		// Drop tables
		$this->ci->db->query('DROP TABLE IF EXISTS '.db_prefix().'wmm_package_usage');
		$this->ci->db->query('DROP TABLE IF EXISTS '.db_prefix().'wmm_support_packages');
	}

}
