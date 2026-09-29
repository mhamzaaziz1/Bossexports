<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * The file is responsible for handing the installation
 */

/**
 * Mautic Settings Table
 */
if (!$CI->db->table_exists(db_prefix() . 'mailwizz_marketing_settings')) {

    $CI->db->query('CREATE TABLE `' . db_prefix() . 'mailwizz_marketing_settings` (

		  `id` int(11) NOT NULL,
		  `public_key` varchar(145) NOT NULL,
		  `mailwizz_marketing_base_url` varchar(345) NOT NULL,
		  `secret_key` varchar(145) NOT NULL,
		   `mailwizz_marketing_lead_status` int(15) NULL,
		   `mailwizz_marketing_view_source` int(15) NULL,
		   `mailwizz_marketing_lead_assigned` int(15) NULL

		) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'mailwizz_marketing_settings`

		  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'mailwizz_marketing_settings`

		  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');

    // Dummy API Key & API SECRET

    $username = 'Username';

    $secret_key = 'Password';

    $base_url = 'www.domain.com';

    $data = array(

        'public_key' => $username,

        'secret_key' => $secret_key,

        'mailwizz_marketing_base_url' => $base_url,

    );

    $CI->db->insert('tblmailwizz_marketing_settings', $data);

}
/**
 * Mautic Campaign Table
 */

if (!$CI->db->table_exists(db_prefix() . 'mailwizz_marketing_campaign')) {

    $CI->db->query('CREATE TABLE `' . db_prefix() . 'mailwizz_marketing_campaign` (

		  `id` varchar(225) NOT NULL,
		  `mailwizz_marketing_campaign_name` varchar(125) NULL,
		  `mailwizz_marketing_campaign_status` varchar(255) NULL,
		   `mailwizz_marketing_campaign_group` varchar(255) NULL

		) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'mailwizz_marketing_campaign`

		  ADD PRIMARY KEY (`id`);');

}
