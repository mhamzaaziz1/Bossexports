<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'xero')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'xero` (
  `id` int(11) NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `client_secret` varchar(255) NOT NULL,
  `redirect_uri` varchar(255) DEFAULT NULL

) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'xero`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'xero`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'contacts`
  ADD `xero_contactid` varchar(255);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'invoices`
  ADD `xero_invoiceid` varchar(255);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'payment_modes`
  ADD `xero_accountid` varchar(255);');

}


