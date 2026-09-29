<?php
defined('BASEPATH') or exit('No direct script access allowed');

//add in settings
add_option(BD_GREETINGS_MODULE_NAME.'_contact_cust_field_bday','');
add_option(BD_GREETINGS_MODULE_NAME.'_contact_cust_field_anniversary','');
add_option(BD_GREETINGS_MODULE_NAME.'_send_sms',1);
add_option(BD_GREETINGS_MODULE_NAME.'_send_email',1);
add_option(BD_GREETINGS_MODULE_NAME.'_email_attachment_bday','');
add_option(BD_GREETINGS_MODULE_NAME.'_email_attachment_anniversary','');
add_option(BD_GREETINGS_MODULE_NAME.'_trigger_auto_operations_hour',9);//at 9 AM
add_option(BD_GREETINGS_MODULE_NAME.'_trigger_auto_operations_last_run',date('Y-m-d',strtotime('-1 day')));//at 9 AM
add_option('sms_trigger_'.BD_GREETINGS_MODULE_NAME.'_client_bday','');
add_option('sms_trigger_'.BD_GREETINGS_MODULE_NAME.'_client_anniversary','');

//create email templates
//for birthday
create_email_template('With you a very Happy Birthday!', '<span style=\"font-size: 12pt;\"> Hello {contact_firstname} {contact_lastname}, </span><br /><br /><span style=\"font-size: 12pt;\"><strong>Wish you a very Happy Birthday</strong></span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards</span><br /><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', 'bd_greetings', 'Birthday Wish (Sent to Contact)', 'bd-greetings-cron-birthday-wish-to-contact');
//for anniversary
create_email_template('Wish you a Happy Anniversary!', '<span style=\"font-size: 12pt;\"> Hello {contact_firstname} {contact_lastname}, </span><br /><br /><span style=\"font-size: 12pt;\"><strong>Wish you a  Happy Anniversary</strong></span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards</span><br /><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', 'bd_greetings', 'Anniversary Wish (Sent to Contact)', 'bd-greetings-cron-anniversary-wish-to-contact');