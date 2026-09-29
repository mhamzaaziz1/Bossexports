<?php
defined('BASEPATH') or exit('No direct script access allowed');
if(!$CI->db->table_exists(db_prefix() . 'bd_expenses')) {	
	$CI->db->query('CREATE TABLE `' . db_prefix() . "bd_expenses` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
  	`category` int(11) NOT NULL,
  	`currency` int(11) NOT NULL,
  	`amount` decimal(15,2) NOT NULL,
  	`tax` int(11) DEFAULT NULL,
  	`tax2` int(11) NOT NULL DEFAULT '0',
  	`reference_no` varchar(100) DEFAULT NULL,
  	`note` text,
  	`expense_name` varchar(191) DEFAULT NULL,
  	`clientid` int(11) NOT NULL,
  	`project_id` int(11) NOT NULL DEFAULT '0',
  	`billable` int(11) DEFAULT '0',
  	`invoiceid` int(11) DEFAULT NULL,
  	`paymentmode` varchar(50) DEFAULT NULL,
  	`date` date NOT NULL,
  	`recurring_type` varchar(10) DEFAULT NULL,
  	`repeat_every` int(11) DEFAULT NULL,
  	`recurring` int(11) NOT NULL DEFAULT '0',
  	`cycles` int(11) NOT NULL DEFAULT '0',
  	`total_cycles` int(11) NOT NULL DEFAULT '0',
  	`custom_recurring` int(11) NOT NULL DEFAULT '0',
  	`last_recurring_date` date DEFAULT NULL,
  	`create_invoice_billable` tinyint(1) DEFAULT NULL,
  	`send_invoice_to_customer` tinyint(1) NOT NULL,
  	`recurring_from` int(11) DEFAULT NULL,
  	`dateadded` datetime NOT NULL,
  	`addedfrom` int(11) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `clientid` (`clientid`),
  	KEY `project_id` (`project_id`),
  	KEY `category` (`category`),
  	KEY `currency` (`currency`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
