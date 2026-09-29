<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_106 extends App_module_migration
{
    public function up()
    {
        // Perform database upgrade here
    }
    public function logChanged(){
        /*
         * -------- 1.0.6 - January 04, 2024 --------
            NEW UPDATE
            - Display menu labels when hovering over icons if the Collapsed Menu feature is activated.
            - Quick Access Menu: Add options to activate features, quick additional activation in PolyUtilities/Settings.
            - Search Menu: Add an option to activate the Search Menu in PolyUtilities/Settings.
         */
    }
}