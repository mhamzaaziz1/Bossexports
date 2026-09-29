<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'smart_docs_categories')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_docs_categories` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `slug` varchar(255) NOT NULL,
      `description` text,
      `icon` varchar(50) DEFAULT NULL,
      `sort_order` int(11) DEFAULT 0,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'smart_docs_sections')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_docs_sections` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `category_id` int(11) NOT NULL,
      `name` varchar(255) NOT NULL,
      `slug` varchar(255) NOT NULL,
      `description` text,
      `sort_order` int(11) DEFAULT 0,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `category_id` (`category_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'smart_docs_articles')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_docs_articles` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `section_id` int(11) NOT NULL,
      `title` varchar(255) NOT NULL,
      `slug` varchar(255) NOT NULL,
      `content` longtext,
      `meta_title` varchar(255) DEFAULT NULL,
      `meta_description` text DEFAULT NULL,
      `keywords` text DEFAULT NULL,
      `is_published` tinyint(1) DEFAULT 0,
      `status` varchar(50) DEFAULT "draft",
      `visibility` varchar(50) DEFAULT "staff", 
      `role_visibility` text DEFAULT NULL,
      `related_module` varchar(150) DEFAULT NULL,
      `language` varchar(50) DEFAULT "english",
      `author_id` int(11) DEFAULT NULL,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `section_id` (`section_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Update existing table if columns missing
if (!$CI->db->field_exists('status', db_prefix() . 'smart_docs_articles')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "smart_docs_articles` ADD COLUMN `status` varchar(50) DEFAULT 'draft'");
}
if (!$CI->db->field_exists('related_module', db_prefix() . 'smart_docs_articles')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "smart_docs_articles` ADD COLUMN `related_module` varchar(150) DEFAULT NULL");
}
if (!$CI->db->field_exists('language', db_prefix() . 'smart_docs_articles')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "smart_docs_articles` ADD COLUMN `language` varchar(50) DEFAULT 'english'");
}

if (!$CI->db->table_exists(db_prefix() . 'smart_docs_media')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_docs_media` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `article_id` int(11) NOT NULL,
      `file_name` varchar(255) NOT NULL,
      `file_type` varchar(50) NOT NULL,
      `file_path` varchar(255) NOT NULL,
      `uploaded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `article_id` (`article_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'smart_docs_views')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_docs_views` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `article_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `user_type` varchar(50) NOT NULL, 
      `viewed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `article_id` (`article_id`),
      KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'smart_docs_feedback')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_docs_feedback` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `article_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `is_helpful` tinyint(1) NOT NULL,
      `comment` text DEFAULT NULL,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `article_id` (`article_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}
