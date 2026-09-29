<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Custom_flyout_menu_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ========== GROUPS ==========
    
    public function get_groups($area = 'admin', $for_listing = false)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'cfm_menu_groups');
        $this->db->where('area', $area);
        if(!$for_listing)
            $this->db->where('active', 1);
        $this->db->order_by('sort_order', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_group($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'cfm_menu_groups')->row_array();
    }

    public function save_group($data)
    {
        // Only save allowed fields (removes CSRF tokens, etc.)
        $allowed_fields = ['id', 'area', 'name', 'slug', 'icon', 'sort_order', 'active'];
        $clean_data = [];
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $clean_data[$field] = $data[$field];
            }
        }
        $data = $clean_data;
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        if (isset($data['id']) && !empty($data['id'])) {
            // Update
            $this->db->where('id', $data['id']);
            $this->db->update(db_prefix() . 'cfm_menu_groups', $data);
            $this->clear_cache();
            return $data['id'];
        } else {
            // Insert
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert(db_prefix() . 'cfm_menu_groups', $data);
            $this->clear_cache();
            return $this->db->insert_id();
        }
    }

    public function delete_group($id)
    {
        $this->db->where('id', $id);
        $result = $this->db->delete(db_prefix() . 'cfm_menu_groups');
        $this->clear_cache();
        return $result;
    }

    // ========== ITEMS ==========
    
    public function get_items($area = 'admin', $group_id = null, $for_listing = false)
    {
        $items_table  = db_prefix() . 'cfm_menu_items';
        $groups_table = db_prefix() . 'cfm_menu_groups';

        $this->db->select("
            i.*,
            p.label as parent_label,
            g.name as group_name
            ");
        $this->db->from($items_table . ' as i');

        // Join parent item (self join)
        $this->db->join($items_table . ' as p', 'p.id = i.parent_id', 'left');

        // Join group
        $this->db->join($groups_table . ' as g', 'g.id = i.group_id', 'left');

        $this->db->where('i.area', $area);

        if (!$for_listing) {
            $this->db->where('i.active', 1);
        }

        if ($group_id !== null) {
            $this->db->where('i.group_id', $group_id);
        }

        $this->db->order_by('i.sort_order', 'ASC');

        return $this->db->get()->result_array();
    }


    public function get_item($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'cfm_menu_items')->row_array();
    }

    public function save_item($data)
    {
        $allowed_fields = [
            'id','area','label','url','icon','group_id',
            'parent_id','badge_text','badge_color_class',
            'permission','sort_order','active'
        ];

        $clean_data = [];
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $clean_data[$field] = $data[$field];
            }
        }
        $data = $clean_data;

        $data['updated_at'] = date('Y-m-d H:i:s');

        // Sanitize
        if (isset($data['label'])) {
            $data['label'] = strip_tags($data['label']);
        }

        if (isset($data['icon'])) {
            $data['icon'] = preg_replace('/[^a-zA-Z0-9-]/', '', $data['icon']);
        }

        // Fix group_id
        if (empty($data['group_id'])) {
            $data['group_id'] = null;
        }

        // Fix parent_id (CRITICAL FIX)
        if (empty($data['parent_id']) || $data['parent_id'] == 0) {
            $data['parent_id'] = null;
        } else {
            // prevent self-parent
            if (isset($data['id']) && $data['parent_id'] == $data['id']) {
                $data['parent_id'] = null;
            } else {
                // check if parent exists
                $this->db->where('id', $data['parent_id']);
                $exists = $this->db->get(db_prefix() . 'cfm_menu_items')->row();
                if (!$exists) {
                    $data['parent_id'] = null;
                }
            }
        }

        if (isset($data['permission']) && $data['permission'] === '') {
            $data['permission'] = null;
        }

        if (!empty($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update(db_prefix() . 'cfm_menu_items', $data);
            $this->clear_cache();
            return $data['id'];
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert(db_prefix() . 'cfm_menu_items', $data);
            $this->clear_cache();
            return $this->db->insert_id();
        }
    }


    

    public function delete_item($id)
    {
        $this->db->where('id', $id);
        $result = $this->db->delete(db_prefix() . 'cfm_menu_items');
        $this->clear_cache();
        return $result;
    }

    public function update_sort_orders($items)
    {
        foreach ($items as $id => $order) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'cfm_menu_items', [
                'sort_order' => $order,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        $this->clear_cache();
        return true;
    }

    // ========== MENU STRUCTURE ==========
    
    public function get_menu_structure($area = 'admin')
    {
        // Get all groups
        $groups = $this->get_groups($area);
        
        // Get all items
        $items = $this->get_items($area);
        
        // Build hierarchy
        $tree = $this->build_tree($items);
        
        // Organize by groups
        $menu = [];
        
        foreach ($groups as $group) {
            $menu[] = [
                'group' => $group,
                'items' => $this->filter_items_by_group($tree, $group['id'])
            ];
        }
        
        // Add ungrouped items
        $ungrouped = $this->filter_items_by_group($tree, null);
        if (!empty($ungrouped)) {
            $menu[] = [
                'group' => ['name' => 'Other', 'slug' => 'other'],
                'items' => $ungrouped
            ];
        }
        
        return $menu;
    }

    public function get_full_structure($area = 'admin', $for_listing = false)
    {
        return [
            'groups' => $this->get_groups($area, $for_listing),
            'items' => $this->build_tree($this->get_items($area, null, $for_listing)),
        ];
    }

    private function build_tree($items, $parent_id = null)
    {
        $branch = [];
        
        foreach ($items as $item) {
            if ($item['parent_id'] == $parent_id) {
                $children = $this->build_tree($items, $item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $branch[] = $item;
            }
        }
        
        return $branch;
    }

    private function filter_items_by_group($items, $group_id)
    {
        $filtered = [];
        
        foreach ($items as $item) {
            if ($item['group_id'] == $group_id) {
                $filtered[] = $item;
            }
        }
        
        return $filtered;
    }

    // ========== CACHE ==========
    
    private function clear_cache()
    {
        delete_option('cfm_menu_cache_admin');
        delete_option('cfm_menu_cache_client');
    }
}
