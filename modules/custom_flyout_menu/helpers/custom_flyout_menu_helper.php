<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('cfm_get_setting')) {
    function cfm_get_setting($key, $default = null)
    {
        try {
            $CI = &get_instance();
            
            // Check if database is available
            if (!isset($CI->db) || !$CI->db) {
                return (string)$default;
            }
            
            // Check if table exists
            if (!$CI->db->table_exists(db_prefix() . 'cfm_settings')) {
                return (string)$default;
            }
            
            $CI->db->select('setting_value');
            $CI->db->from(db_prefix() . 'cfm_settings');
            $CI->db->where('setting_key', $key);
            $query = $CI->db->get();
            
            if ($query && $query->num_rows() > 0) {
                $value = $query->row()->setting_value;
                // Cast to string to ensure consistent type
                return $value !== null && $value !== '' ? (string)$value : (string)$default;
            }
        } catch (Exception $e) {
            // Database error - return default as string
            return (string)$default;
        }
        
        // Not found - return default as string
        return (string)$default;
    }
}

if (!function_exists('cfm_update_setting')) {
    function cfm_update_setting($key, $value)
    {
        try {
            $CI = &get_instance();
            
            // Check if database is available
            if (!isset($CI->db) || !$CI->db) {
                return false;
            }
            
            // Check if table exists
            if (!$CI->db->table_exists(db_prefix() . 'cfm_settings')) {
                return false;
            }
            
            // Ensure value is string
            $value = (string)$value;
            
            // Check if setting already exists
            $CI->db->where('setting_key', $key);
            $query = $CI->db->get(db_prefix() . 'cfm_settings');
            
            $data = [
                'setting_value' => $value,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($query && $query->num_rows() > 0) {
                // Update existing setting
                $CI->db->where('setting_key', $key);
                $result = $CI->db->update(db_prefix() . 'cfm_settings', $data);
                return $result !== false;
            } else {
                // Insert new setting
                $data['setting_key'] = $key;
                $result = $CI->db->insert(db_prefix() . 'cfm_settings', $data);
                return $result !== false;
            }
        } catch (Exception $e) {
            // Database error - return false
            return false;
        }
        
        return false;
    }
}

if (!function_exists('cfm_should_apply_menu')) {
    function cfm_should_apply_menu()
    {
        // Determine area based on URI
        $CI = &get_instance();
        $area = 'admin';
        
        // Check if we're in client area
       if (
            defined('CLIENTS_AREA')
            || (isset($CI->uri) && $CI->uri->segment(1) === 'clients')
            || (isset($CI->router) && $CI->router->fetch_directory() === 'clients/')
        ) {
            $area = 'client';
        }
        // Check if menu is enabled for this area
        if (!cfm_get_setting($area . '_menu_enabled', '1')) {
            return false;
        }
        
        // Check SaaS super admin exclusion
        if (function_exists('perfex_saas_is_tenant')) {
            if (!perfex_saas_is_tenant() && cfm_get_setting('exclude_super_admin', '1') == '1') {
                return false;
            }
        }
        
        return true;
    }
}

if (!function_exists('cfm_filter_by_permissions')) {
    function cfm_filter_by_permissions($menu_items)
    {
        $filtered = [];
        
        foreach ($menu_items as $item) {
            if (!empty($item['permission'])) {
                // Check if has_permission function exists
                if (function_exists('has_permission') && function_exists('is_admin')) {
                    if (!has_permission($item['permission'], '', 'view') && !is_admin()) {
                        continue;
                    }
                }
            }
            
            if (!empty($item['children'])) {
                $item['children'] = cfm_filter_by_permissions($item['children']);
                
                if (empty($item['children']) && $item['has_children']) {
                    continue;
                }
            }
            
            $filtered[] = $item;
        }
        
        return $filtered;
    }
}

if (!function_exists('cfm_generate_flyout_html')) {
    function cfm_generate_flyout_html($menu_data, $area = 'admin')
    {
        if (empty($menu_data)) {
            return '<!-- CFM: No menu data available -->';
        }
        
        // Map group slugs to appropriate icons
        $group_icons = [
            'sales' => 'ph-chart-line-up',
            'support' => 'ph-headset',
            'management' => 'ph-briefcase',
            'reports' => 'ph-chart-bar',
            'saas' => 'ph-cloud',
            'billing' => 'ph-credit-card',
            'projects' => 'ph-folder-open',
            'other' => 'ph-dots-three'
        ];
        
        $html = '<div id="cfm-flyout-sidebar" class="cfm-sidebar cfm-' . $area . '">';
        $html .= '<div class="cfm-icon-rail">';
        
        // Generate icon rail
        foreach ($menu_data as $section) {
            if (empty($section['items'])) {
                continue;
            }
            
            $group = $section['group'];
            $slug = isset($group['slug']) ? $group['slug'] : 'other';
            
            // Determine icon - use group's custom icon if set, otherwise use default based on slug
            $icon = 'ph-folder';
            if (!empty($group['icon'])) {
                $icon = $group['icon'];
            } elseif (isset($group_icons[$slug])) {
                $icon = $group_icons[$slug];
            }
            
            $name = isset($group['name']) ? $group['name'] : 'Menu';
            
            $html .= '<div class="cfm-icon-item" data-flyout-target="' . html_escape($slug) . '">';
            $html .= '<i class="ph ' . html_escape($icon) . '"></i>';
            $html .= '<span>' . html_escape($name) . '</span>';
            $html .= '</div>';
        }
        
        $html .= '</div>'; // End icon-rail
        $html .= '</div>'; // End flyout-sidebar
        
        // Generate flyout panels
        $html .= '<div id="cfm-flyout-panel" class="cfm-panel hidden">';
        
        foreach ($menu_data as $section) {
            if (empty($section['items'])) {
                continue;
            }
            
            $group = $section['group'];
            $slug = isset($group['slug']) ? $group['slug'] : 'other';
            $name = isset($group['name']) ? $group['name'] : 'Menu';
            
            $html .= '<div class="cfm-panel-content" data-flyout-id="' . html_escape($slug) . '">';
            $html .= '<div class="cfm-section">';
            $html .= '<div class="cfm-section-label">' . strtoupper(html_escape($name)) . '</div>';
            
            if (!empty($section['items'])) {
                foreach ($section['items'] as $item) {
                    $html .= cfm_render_menu_item($item, $area);
                }
            } else {
                $html .= '<div class="cfm-empty-state">';
                $html .= '<i class="ph ph-folder-open"></i>';
                $html .= '<p>No items in this section</p>';
                $html .= '</div>';
            }
            
            $html .= '</div>'; // End cfm-section
            $html .= '</div>'; // End cfm-panel-content
        }
        
        $html .= '</div>'; // End cfm-flyout-panel
        
        return $html;
    }
}

if (!function_exists('cfm_render_menu_item')) {
    function cfm_render_menu_item($item, $area = 'admin')
    {
        // Build URL
        $url = isset($item['url']) ? $item['url'] : '#';
        
        // Ensure URL is properly formatted
        if ($url !== '#' && strpos($url, 'http') !== 0 && strpos($url, '//') !== 0) {
            // It's a relative URL
            $url = $area == 'admin' ? admin_url($url) : site_url($url);
        }
        
        // Get icon
        $icon = !empty($item['icon']) ? $item['icon'] : 'ph-file';
        
        // Ensure icon has 'ph' class prefix
        if (strpos($icon, 'ph-') === false && strpos($icon, 'ph ') === false) {
            $icon = 'ph-' . $icon;
        }
        
        // Get label
        $label = isset($item['label']) ? $item['label'] : 'Menu Item';
        
        // Build menu item HTML
        $html = '<a href="' . html_escape($url) . '" class="cfm-menu-item">';
        $html .= '<i class="ph ' . html_escape($icon) . '"></i>';
        $html .= '<span>' . html_escape($label) . '</span>';
        
        // Add badge if present
        if (!empty($item['badge_text'])) {
            $badge_class = !empty($item['badge_color_class']) ? $item['badge_color_class'] : 'cfm-badge-info';
            $html .= '<span class="cfm-badge ' . html_escape($badge_class) . '">' . html_escape($item['badge_text']) . '</span>';
        }
        
        $html .= '</a>';
        
        // Render children if any
        if (!empty($item['children']) && is_array($item['children'])) {
            $html .= '<div class="cfm-submenu">';
            foreach ($item['children'] as $child) {
                $html .= cfm_render_menu_item($child, $area);
            }
            $html .= '</div>';
        }
        
        return $html;
    }
}
