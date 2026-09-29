<?php

defined("BASEPATH") or exit("No direct script access allowed");

/*
Module Name: Sidebar Search Module
Description: Sidebar Search module
Author: Halil [ halilaltndg@gmail.com ]
Author URI: https://www.fiverr.com/halilaltndg
Version: 1.0.0
Requires at least: 2.3.*
*/


define("PERFEX_SEARCH_MODULE_NAME", "perfex_search");

hooks()->add_action('before_render_aside_menu','perfex_search_render_aside_menu');

hooks()->add_action("app_admin_footer", "perfex_search_include_footer_static_js");


function perfex_search_render_aside_menu()
{

    echo '<div class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" id="menu-filter" class="form-control" placeholder="Search...">
                <span class="input-group-btn">
                    <button class="btn btn-flat " id="aside_search_button"><i class="fa fa-search"></i></button>
                </span>
            </div>
        </div>';

}


function perfex_search_include_footer_static_js()
{

    $time = time();

    echo "
    <script src='" . base_url("modules/perfex_search/assets/perfex_search.js?").$time ."'></script>";

}
