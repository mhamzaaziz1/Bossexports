<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_105 extends App_module_migration
{
    public function up()
    {
        // Perform database upgrade here
    }
    public function logChanged(){
        /*
         * -------- 1.0.5 - January 02, 2024 --------
        NEW UPDATE
        - Search Menu: Accessing menus while working within a system installed with numerous modules can be quite time-consuming. Alongside the Quick Access Menu, the menu search feature will assist you in quickly finding the desired feature menu. After searching for a menu, you can also add the search results to the Quick Access Menu for swift use in subsequent work sessions. (v1.0.5)
        - Custom JavaScript code editing with the option to embed script/CSS links from CDN. This supports users in inserting widgets, chatbots, etc., more conveniently.
        - Option to place custom JavaScript code at the header or footer of the page. 

        CHANGE
        - Adjust default display of the collapsed icon when activating the icon-only Sidebar Menu.

        FIXED
        - Utilize the default sidebar menu without activating the Collapsed feature on mobile and small interfaces.
         */
    }
}