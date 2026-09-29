<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_100 extends App_module_migration {

	public function up()
	{
		// Create maintenance tasks table
		if ( ! $this->ci->db->table_exists(db_prefix().'wmm_maintenance_tasks'))
		{
			$this->ci->db->query(
				'CREATE TABLE `'.db_prefix()."wmm_maintenance_tasks` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(255) NOT NULL,
				  `description` text,
				  `category` int(11),
				  `is_active` tinyint(1) DEFAULT '1',
				  `priority` ENUM('low','medium','high','urgent') DEFAULT 'medium',
				  `created_by` int(11) NOT NULL,
				  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
				  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`),
				  KEY `created_by` (`created_by`)
				) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.';',
			);
		}

		// Create websites table
		if ( ! $this->ci->db->table_exists(db_prefix().'wmm_websites'))
		{
			$this->ci->db->query(
				'CREATE TABLE `'.db_prefix()."wmm_websites` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `project_id` int(11) NOT NULL,
				  `client_id` int(11) NOT NULL,
				  `website_url` varchar(500) DEFAULT NULL,
				  `is_active` tinyint(1) DEFAULT '1',
				  `added_by` int(11) NOT NULL,
				  `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`),
				  KEY `project_id` (`project_id`),
				  KEY `client_id` (`client_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.';',
			);
		}

		// Create maintenance logs table
		if ( ! $this->ci->db->table_exists(db_prefix().'wmm_maintenance_logs'))
		{
			$this->ci->db->query(
				'CREATE TABLE `'.db_prefix()."wmm_maintenance_logs` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `website_id` int(11) NOT NULL,
				  `performed_by` int(11) NOT NULL,
				  `performed_at` datetime DEFAULT CURRENT_TIMESTAMP,
				  `start_time` datetime DEFAULT NULL,
				  `end_time` datetime DEFAULT NULL,
				  `time_spent` int(11) DEFAULT NULL,
				  `is_completed` tinyint(1) DEFAULT 1,
				  `notes` text,
				  `email_sent` tinyint(1) DEFAULT '0',
				  `email_sent_at` datetime DEFAULT NULL,				  
				  `invoice_id` INT(11) NULL DEFAULT NULL,
                  `hourly_rate` DECIMAL(15,2) NULL DEFAULT NULL,
                  `is_billable` TINYINT(1) DEFAULT 0,
                  `invoice_created` TINYINT(1) DEFAULT 0 ,            
				  PRIMARY KEY (`id`),
				  KEY `website_id` (`website_id`),
				  KEY `performed_by` (`performed_by`),
				  KEY `invoice_id` (`invoice_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.';',
			);
		}

		// Create maintenance log tasks table
		if ( ! $this->ci->db->table_exists(db_prefix().'wmm_maintenance_log_tasks'))
		{
			$this->ci->db->query(
				'CREATE TABLE `'.db_prefix()."wmm_maintenance_log_tasks` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `log_id` int(11) NOT NULL,
				  `task_id` int(11) NOT NULL,
				  `is_completed` tinyint(1) DEFAULT '1',
				  PRIMARY KEY (`id`),
				  KEY `log_id` (`log_id`),
				  KEY `task_id` (`task_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.';',
			);
		}

		// Add attachments table for version 1.0.1
		if ( ! $this->ci->db->table_exists(db_prefix().'wmm_maintenance_attachments'))
		{
			$this->ci->db->query(
				'CREATE TABLE `'.db_prefix()."wmm_maintenance_attachments` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `log_id` int(11) NOT NULL,
				  `file_name` varchar(255) NOT NULL,
				  `filetype` varchar(100) DEFAULT NULL,
				  `dateadded` datetime DEFAULT CURRENT_TIMESTAMP,
				  `staffid` int(11) DEFAULT NULL,
				  `contact_id` int(11) DEFAULT NULL,
				  `external` varchar(40) DEFAULT NULL,
				  `external_link` text,
				  `thumbnail_link` text,
				  `attachment_key` varchar(32) DEFAULT NULL,
				  PRIMARY KEY (`id`),
				  KEY `log_id` (`log_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.';',
			);
		}

		// Add assignees table for maintenance tasks
		if ( ! $this->ci->db->table_exists(db_prefix().'wmm_task_assigned'))
		{
			$this->ci->db->query(
				'CREATE TABLE `'.db_prefix()."wmm_task_assigned` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `task_id` int(11) NOT NULL,
				  `staffid` int(11) NOT NULL,
				  `assigned_from` int(11) NOT NULL,
				  `assigned_at` datetime DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`),
				  KEY `task_id` (`task_id`),
				  KEY `staffid` (`staffid`)
				) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.';',
			);
		}

		// Create categories table
		if ( ! $this->ci->db->table_exists(db_prefix().'wmm_categories'))
		{
			$this->ci->db->query(
				'CREATE TABLE `'.db_prefix()."wmm_categories` (
		      `id` int(11) NOT NULL AUTO_INCREMENT,
		      `name` varchar(100) NOT NULL,
		      `slug` varchar(100) NOT NULL,
		      `description` text,
		      `icon` varchar(50) DEFAULT NULL,
		      `color` varchar(7) DEFAULT NULL,
		      `is_active` tinyint(1) DEFAULT '1',
		      `display_order` int(11) DEFAULT '0',
		      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
		      `created_by` int(11) DEFAULT NULL,
		      PRIMARY KEY (`id`),
		      UNIQUE KEY `slug` (`slug`)
		    ) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.';',
			);
		}

		// Insert default categories
		$default_categories = [
			[
				'name'          => 'Plugin Updates',
				'slug'          => 'plugin',
				'description'   => 'WordPress plugin updates and maintenance',
				'icon'          => 'fa-puzzle-piece',
				'color'         => '#3b82f6',
				'display_order' => 1,
			],
			[
				'name'          => 'Theme Updates',
				'slug'          => 'theme',
				'description'   => 'WordPress theme updates and customization',
				'icon'          => 'fa-paint-brush',
				'color'         => '#8b5cf6',
				'display_order' => 2,
			],
			[
				'name'          => 'Core Updates',
				'slug'          => 'core',
				'description'   => 'WordPress core updates',
				'icon'          => 'fa-wordpress',
				'color'         => '#10b981',
				'display_order' => 3,
			],
			[
				'name'          => 'Security',
				'slug'          => 'security',
				'description'   => 'Security patches and fixes',
				'icon'          => 'fa-shield-alt',
				'color'         => '#ef4444',
				'display_order' => 4,
			],
			[
				'name'          => 'Backup',
				'slug'          => 'backup',
				'description'   => 'Website backup tasks',
				'icon'          => 'fa-database',
				'color'         => '#f59e0b',
				'display_order' => 5,
			],
			[
				'name'          => 'Performance',
				'slug'          => 'performance',
				'description'   => 'Performance optimization',
				'icon'          => 'fa-tachometer-alt',
				'color'         => '#06b6d4',
				'display_order' => 6,
			],
			[
				'name'          => 'Other',
				'slug'          => 'other',
				'description'   => 'Other maintenance tasks',
				'icon'          => 'fa-tasks',
				'color'         => '#64748b',
				'display_order' => 99,
			],
		];

		foreach ($default_categories as $category)
		{
			// Check if category already exists
			$exists = $this->ci->db
				->where('slug', $category['slug'])
				->get(db_prefix().'wmm_categories')
				->row();

			if ( ! $exists)
			{
				$this->ci->db->insert(db_prefix().'wmm_categories', $category);
			}
		}

		// Check if email templates already exist
		$email_template_exists = $this->ci->db
			->where('slug', 'wmm-maintenance-started')
			->or_where('slug', 'wmm-maintenance-completed')
			->get(db_prefix().'emailtemplates')
			->num_rows();

		if ($email_template_exists == 0)
		{
			$this->add_wmm_email_templates();
		}
	}

	public function down(): void
	{
		$this->ci->db->query("DROP TABLE IF EXISTS ".db_prefix()."knowledge_base_spaces");
	}

	public function add_wmm_email_templates(): void
	{
		// Get all available languages
		$languages = $this->ci->app->get_available_languages();

		foreach ($languages as $language)
		{
			// Template 1: Maintenance Started
			$started_template = [
				'type'      => 'website_maintenance',
				'slug'      => 'wmm-maintenance-started',
				'language'  => $language,
				'name'      => 'Website Maintenance Started'.($language != 'english' ? ' ['.$language.']' : ''),
				'subject'   => 'Website Maintenance Started - {project_name}',
				'message'   => '<p>Dear {client_name},</p>

<p>This is to inform you that we have started maintenance work on your website: <strong>{project_name}</strong></p>

<p><strong>Website:</strong> {website_url}</p>
<p><strong>Started At:</strong> {maintenance_start_time}</p>
<p><strong>Performed By:</strong> {staff_name}</p>

<p>We will notify you once the maintenance is completed.</p>

<p>If you have any questions or concerns, please feel free to contact us.</p>

<p>Best regards,<br>{company_name}</p>',
				'fromname'  => '{companyname} | CRM',
				'fromemail' => '',
				'plaintext' => 0,
				'active'    => 1,
				'order'     => 1,
			];

			$this->ci->db->insert(db_prefix().'emailtemplates', $started_template);

			// Template 2: Maintenance Completed
			$completed_template = [
				'type'      => 'website_maintenance',
				'slug'      => 'wmm-maintenance-completed',
				'language'  => $language,
				'name'      => 'Website Maintenance Completed'.($language != 'english' ? ' ['.$language.']' : ''),
				'subject'   => 'Website Maintenance Completed - {project_name}',
				'message'   => '<p>Dear {client_name},</p>

<p>This is to inform you that maintenance work has been completed on your website: <strong>{project_name}</strong></p>

<p><strong>Website:</strong> {website_url}</p>
<p><strong>Maintenance Date:</strong> {maintenance_date}</p>
<p><strong>Time Spent:</strong> {time_spent}</p>

<h3>Tasks Completed:</h3>
{tasks_completed}

{notes}

<p>If you have any questions or concerns, please feel free to contact us.</p>

<p>Best regards,<br>{company_name}</p>',
				'fromname'  => '{companyname} | CRM',
				'fromemail' => '',
				'plaintext' => 0,
				'active'    => 1,
				'order'     => 2,
			];

			$this->ci->db->insert(db_prefix().'emailtemplates', $completed_template);
		}

		// Record migration
		$this->ci->db->insert(db_prefix().'module_migrations', [
			'module'     => 'website_maintenance_management',
			'version'    => 110,
			'applied_at' => date('Y-m-d H:i:s'),
		]);

		log_activity('Website Maintenance Management: Email templates created');
	}

}