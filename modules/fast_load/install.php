<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Add options for fast load
if (get_option('fast_load_minify_html') === null) {
    add_option('fast_load_minify_html', '1');
}

if (get_option('fast_load_browser_cache') === null) {
    add_option('fast_load_browser_cache', '1');
}

if (get_option('fast_load_enable_db_cache') === null) {
    add_option('fast_load_enable_db_cache', '0');
}

// Optionally, create directories for caching if they don't exist
$fast_cache_dir = APPPATH . 'cache/fast_load/';
if (!is_dir($fast_cache_dir)) {
    mkdir($fast_cache_dir, 0755, true);
}
