<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_200 extends App_module_migration
{
    public function up()
    {
        // Add optimatrix api settings
        add_option('optimetriks_client_id', '');
        add_option('optimetriks_email', '');
        add_option('optimetriks_password', '');
    }
}
