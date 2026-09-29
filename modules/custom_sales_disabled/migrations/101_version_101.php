<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_101 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        foreach (custom_sales_get_item_types() as $type) {

            $source_resources = custom_sales_get_resources_plural($type);
            $CI->custom_sales_model->db->where('resources', $source_resources)->update($CI->custom_sales_model->items_titles_table, ['resources' => $type]);
        }
        require(__DIR__ . '/../install.php');
    }

    public function down()
    {
    }
}
