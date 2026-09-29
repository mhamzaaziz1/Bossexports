<?php

function lead_checklist_db_changes()
{

    $CI = &get_instance();


    if ( !$CI->db->table_exists( db_prefix().'lead_checklists' ) )
    {

        $CI->db->query('
                        CREATE TABLE `'.db_prefix().'lead_checklists` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `lead_id` int(11) DEFAULT NULL,
                          `checklist_text` varchar(255) DEFAULT NULL,
                          `template_id` int(11) DEFAULT NULL,
                          `added_staff_id` int(11) DEFAULT NULL,
                          `added_date` datetime DEFAULT NULL,
                          `complated_staff_id` int(11) DEFAULT 0,
                          `complated_date` datetime DEFAULT NULL,
                          `assigned_staff_id` int(11) DEFAULT 0,
                          `assigned_date` datetime DEFAULT NULL,
                          PRIMARY KEY (`id`),
                          KEY `lead_id` (`lead_id`),
                          KEY `added_staff_id` (`added_staff_id`),
                          KEY `complated_staff_id` (`complated_staff_id`)
                        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');


    }

    if ( !$CI->db->table_exists( db_prefix().'lead_checklist_templates' ) )
    {

        $CI->db->query('
                        CREATE TABLE `'.db_prefix().'lead_checklist_templates` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `checklist_text` varchar(255) DEFAULT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');


    }




    if( !$CI->db->field_exists('add_auto', db_prefix() .'lead_checklist_templates') )
    {

        $CI->db->query('ALTER TABLE `'.db_prefix().'lead_checklist_templates`
                            ADD COLUMN `add_auto` tinyint NULL DEFAULT 0 AFTER `checklist_text`; ');

    }



}


function get_lead_checklists( $lead_id = 0 , $record_id = 0 )
{

    $CI = &get_instance();

    $CI->db->select('*')->from(db_prefix().'lead_checklists')->where('lead_id',$lead_id);

    if ( !empty( $record_id ) )
        return $CI->db->where('id',$record_id)->get()->row();

    return $CI->db->order_by('id')->get()->result();

}


function get_lead_checklist_templates( $template_id = 0 )
{

    $CI = &get_instance();

    if ( !empty( $template_id ) )

        return $CI->db->select('*')->from(db_prefix().'lead_checklist_templates')->where('id',$template_id)->get()->row();

    return $CI->db->select('*')->from(db_prefix().'lead_checklist_templates')->order_by('id')->get()->result();

}

