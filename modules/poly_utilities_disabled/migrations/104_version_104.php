<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_104 extends App_module_migration
{
    public function up()
    {
        // Perform database upgrade here
    }
    public function logChanged(){
        /*
        -------- 1.0.3 -> 1.0.4 - December 29, 2023 --------
        NEW UPDATE
        - Menu: Integrating a feature to support customizing the display of icon lists when collapsing the sidebar menu. (Thank you, manuelfer13, for your request and suggestions).
        */
    }
}