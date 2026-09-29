<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_107 extends App_module_migration
{
    public function up()
    {
        // Perform database upgrade here
    }
    public function logChanged(){
        /*
        -------- 1.0.7 (February 10, 2024) --------
        NEW UPDATE
        - WIDGETS: Integrate widget functionality to display data at any location within PerfexCRM. The widget feature operates similarly to WordPress CMS. PolyUtilities will support hook positions corresponding to widget-supported positions without the need to intervene in the CRM source code. For this widget feature, Polyxgo supports displaying data from third-party modules on demand.
        - Integrate an icon to support 'Login as Client' for the Customer List section and various customer information display locations. Aid administrators in visually previewing customer information that is visible to them.
        - Remember the Activate/Deactivate options for the list of modules. Supports retaining previously displayed states.

        CHANGED
        - Integration supports Line-Break in the custom JavaScript and CSS code editor.

        FIXED
        - Fix the issue of displaying the project details page on mobile where it overflows the screen width due to the Avatar and project status being on the same line as the long project title.
        - Adjust the permissions for using features.
        */
    }
}