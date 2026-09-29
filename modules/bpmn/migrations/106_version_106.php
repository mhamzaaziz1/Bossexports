<?php

defined('BASEPATH') or exit('No direct script access allowed');



class Migration_Version_106 extends App_module_migration

{

    public function up()

    {
    	$CI = &get_instance();

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

    }

}