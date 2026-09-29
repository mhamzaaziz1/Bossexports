<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

add_option('staff_members_create_inline_bpmn_group', 1);

if (!$CI->db->table_exists(db_prefix() . 'bpmn')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "bpmn` (
    `id` int(11) NOT NULL,
    `title` varchar(255) DEFAULT NULL,
    `description` text,
    `staffid` int(11) DEFAULT '0' ,
    `bpmn_group_id` int(11) DEFAULT '0' ,
    `bpmn_content` text,
    `dateadded` datetime DEFAULT NULL,
    `dateaupdated` datetime DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

  $CI->db->query('ALTER TABLE `' . db_prefix() . 'bpmn`
    ADD PRIMARY KEY (`id`),
    ADD KEY `staffid` (`staffid`),
    ADD KEY `bpmn_group_id` (`bpmn_group_id`);');
  
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'bpmn`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');

  $CI->db->insert(db_prefix() . 'bpmn', array(
      'title' => 'Sales',
      'description' => 'Sales process',
      'staffid' => 0,
      'bpmn_group_id' => 0,
      'bpmn_content' => 'common.bpmn',
      'dateadded' => date('Y-m-d H:i:s'),
  ));
}

if (!$CI->db->table_exists(db_prefix() . 'bpmn_groups')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "bpmn_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(191) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'bpmn_groups`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'bpmn_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}
if (!$CI->db->table_exists(db_prefix() . 'bpmncomments')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . "bpmncomments` (
        `id` int(11) NOT NULL,
          `bpmn_id` int(11) NOT NULL,
          `discussion_type` varchar(10) NOT NULL,
          `parent` int(11) DEFAULT NULL,
          `created` datetime NOT NULL,
          `modified` datetime DEFAULT NULL,
          `content` text NOT NULL,
          `rating` TEXT NULL DEFAULT NULL,
          `contact_id` int(11) DEFAULT '0',
          `staff_id` int(11) NOT NULL,
          `fullname` varchar(191) DEFAULT NULL,
          `file_name` varchar(191) DEFAULT NULL,
          `file_mime_type` varchar(70) DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

      $CI->db->query('ALTER TABLE `' . db_prefix() . 'bpmncomments`
        ADD PRIMARY KEY (`id`)');
      
      $CI->db->query('ALTER TABLE `' . db_prefix() . 'bpmncomments`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
        
    }

     if (!is_dir(BPMN_DISCUSSION_ATTACHMENT_FOLDER)) {
      mkdir(BPMN_DISCUSSION_ATTACHMENT_FOLDER, 0755);
      fopen(BPMN_DISCUSSION_ATTACHMENT_FOLDER . 'index.html', 'w');
      $fp = fopen(BPMN_DISCUSSION_ATTACHMENT_FOLDER . 'index.html', 'a+');
      if ($fp) {
        fclose($fp);
      }
    }
      $bpmn = db_prefix() . 'bpmn';
     if (!$CI->db->field_exists('project_id', $bpmn)) {

            $CI->db->query("ALTER TABLE `" . $bpmn . "` ADD `project_id` INT(11) DEFAULT '0'  AFTER `staffid`;");

     }
     if (!$CI->db->field_exists('hash', $bpmn)) {
      $CI->db->query("ALTER TABLE `" . $bpmn . "` ADD `hash` TEXT NULL DEFAULT NULL   AFTER `bpmn_content`;");
        }



