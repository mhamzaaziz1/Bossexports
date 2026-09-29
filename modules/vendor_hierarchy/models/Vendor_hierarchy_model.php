<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Vendor_hierarchy_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // SETTINGS
    // =========================================================================

    /**
     * Get all hierarchy level labels
     * @return array associative array of level labels
     */
    public function get_level_labels()
    {
        $labels = [];
        $result = $this->db->get(db_prefix() . 'vendor_hierarchy_settings')->result_array();
        foreach ($result as $row) {
            $labels[$row['setting_key']] = $row['setting_value'];
        }

        // Ensure defaults
        $defaults = [
            'level_1_label' => 'Group',
            'level_2_label' => 'Company',
            'level_3_label' => 'Regional',
            'level_4_label' => 'Branch',
        ];
        foreach ($defaults as $key => $val) {
            if (!isset($labels[$key]) || empty($labels[$key])) {
                $labels[$key] = $val;
            }
        }

        return $labels;
    }

    /**
     * Get the label for a specific level
     * @param int $level  1-4
     * @return string
     */
    public function get_level_label($level)
    {
        $labels = $this->get_level_labels();
        $key = 'level_' . $level . '_label';
        return isset($labels[$key]) ? $labels[$key] : 'Level ' . $level;
    }

    /**
     * Update hierarchy settings
     * @param array $data
     * @return bool
     */
    public function update_settings($data)
    {
        foreach ($data as $key => $value) {
            if (strpos($key, 'level_') === 0 && strpos($key, '_label') !== false) {
                $exists = $this->db->where('setting_key', $key)
                    ->count_all_results(db_prefix() . 'vendor_hierarchy_settings');

                if ($exists > 0) {
                    $this->db->where('setting_key', $key);
                    $this->db->update(db_prefix() . 'vendor_hierarchy_settings', ['setting_value' => $value]);
                } else {
                    $this->db->insert(db_prefix() . 'vendor_hierarchy_settings', [
                        'setting_key'   => $key,
                        'setting_value' => $value,
                    ]);
                }
            }
        }
        return true;
    }

    // =========================================================================
    // HIERARCHY CORE LOGIC
    // =========================================================================

    /**
     * Get the depth (level number) of a vendor in the hierarchy.
     * A top-level vendor (no parent) is at depth 1.
     * @param int $vendor_id
     * @return int
     */
    public function get_vendor_depth($vendor_id)
    {
        $depth = 1;
        $current_id = $vendor_id;
        $visited = [];

        while ($current_id) {
            if (in_array($current_id, $visited)) {
                break; // Prevent infinite loop on circular data
            }
            $visited[] = $current_id;

            $parent = $this->db->select('parent_vendor_id')
                ->where('userid', $current_id)
                ->get(db_prefix() . 'pur_vendor')
                ->row();

            if ($parent && !empty($parent->parent_vendor_id)) {
                $depth++;
                $current_id = $parent->parent_vendor_id;
            } else {
                break;
            }
        }

        return $depth;
    }

    /**
     * Get the maximum depth among all descendants of a vendor.
     * Returns 0 if no children.
     * @param int $vendor_id
     * @return int
     */
    public function get_max_descendant_depth($vendor_id)
    {
        $children = $this->get_direct_children($vendor_id);
        if (empty($children)) {
            return 0;
        }

        $max = 0;
        foreach ($children as $child) {
            $child_depth = 1 + $this->get_max_descendant_depth($child['userid']);
            if ($child_depth > $max) {
                $max = $child_depth;
            }
        }
        return $max;
    }

    /**
     * Validate if setting a parent for a vendor is allowed.
     * Checks: max 4-level depth AND no circular reference.
     *
     * @param int $vendor_id       The vendor being edited
     * @param int $parent_vendor_id The proposed parent
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validate_parent_assignment($vendor_id, $parent_vendor_id)
    {
        // Allow removing parent
        if (empty($parent_vendor_id) || $parent_vendor_id == 0) {
            return ['valid' => true, 'message' => ''];
        }

        // 1. Cannot be its own parent
        if ($vendor_id == $parent_vendor_id) {
            return ['valid' => false, 'message' => _l('vendor_hierarchy_circular_ref')];
        }

        // 2. Check circular reference: parent cannot be a descendant of vendor
        if ($vendor_id && $this->is_descendant_of($parent_vendor_id, $vendor_id)) {
            return ['valid' => false, 'message' => _l('vendor_hierarchy_circular_ref')];
        }

        // 3. Check depth constraint
        // The parent's depth (from root)
        $parent_depth = $this->get_vendor_depth($parent_vendor_id);

        // The maximum depth of vendor's own descendants
        $vendor_max_desc = 0;
        if ($vendor_id) {
            $vendor_max_desc = $this->get_max_descendant_depth($vendor_id);
        }

        // Total chain: parent's depth + 1 (this vendor) + descendant depth
        $total_depth = $parent_depth + 1 + $vendor_max_desc;

        if ($total_depth > 4) {
            return ['valid' => false, 'message' => _l('vendor_hierarchy_max_depth')];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Check if $child_id is a descendant (child, grandchild…) of $ancestor_id
     * @param int $child_id
     * @param int $ancestor_id
     * @return bool
     */
    public function is_descendant_of($child_id, $ancestor_id)
    {
        $children = $this->get_direct_children($ancestor_id);
        foreach ($children as $child) {
            if ($child['userid'] == $child_id) {
                return true;
            }
            if ($this->is_descendant_of($child_id, $child['userid'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the list of all ancestors (parent chain) from immediate parent to root.
     * @param int $vendor_id
     * @return array of vendor rows from immediate parent → top
     */
    public function get_ancestors($vendor_id)
    {
        $ancestors = [];
        $current_id = $vendor_id;
        $visited = [];

        while ($current_id) {
            if (in_array($current_id, $visited)) break;
            $visited[] = $current_id;

            $vendor = $this->db->select('userid, company, parent_vendor_id')
                ->where('userid', $current_id)
                ->get(db_prefix() . 'pur_vendor')
                ->row();

            if ($vendor && !empty($vendor->parent_vendor_id)) {
                $parent = $this->db->select('userid, company, parent_vendor_id')
                    ->where('userid', $vendor->parent_vendor_id)
                    ->get(db_prefix() . 'pur_vendor')
                    ->row();

                if ($parent) {
                    $ancestors[] = $parent;
                    $current_id = $parent->userid;
                } else {
                    break;
                }
            } else {
                break;
            }
        }

        return array_reverse($ancestors);
    }

    /**
     * Get the breadcrumb path for display (root → ... → current vendor)
     * @param int $vendor_id
     * @return array of ['userid'=>..., 'company'=>..., 'level'=>..., 'level_label'=>...]
     */
    public function get_breadcrumb($vendor_id)
    {
        $ancestors = $this->get_ancestors($vendor_id);
        $labels = $this->get_level_labels();
        $crumbs = [];

        $level = 1;
        foreach ($ancestors as $anc) {
            $label_key = 'level_' . $level . '_label';
            $crumbs[] = [
                'userid'      => $anc->userid,
                'company'     => $anc->company,
                'level'       => $level,
                'level_label' => isset($labels[$label_key]) ? $labels[$label_key] : 'Level ' . $level,
            ];
            $level++;
        }

        // Add current vendor
        $current = $this->db->select('userid, company')
            ->where('userid', $vendor_id)
            ->get(db_prefix() . 'pur_vendor')
            ->row();

        if ($current) {
            $label_key = 'level_' . $level . '_label';
            $crumbs[] = [
                'userid'      => $current->userid,
                'company'     => $current->company,
                'level'       => $level,
                'level_label' => isset($labels[$label_key]) ? $labels[$label_key] : 'Level ' . $level,
            ];
        }

        return $crumbs;
    }

    // =========================================================================
    // CHILDREN / TREE
    // =========================================================================

    /**
     * Get direct children of a vendor
     * @param int $parent_id
     * @return array
     */
    public function get_direct_children($parent_id)
    {
        return $this->db->select('userid, company, phonenumber, active, parent_vendor_id')
            ->where('parent_vendor_id', $parent_id)
            ->order_by('company', 'asc')
            ->get(db_prefix() . 'pur_vendor')
            ->result_array();
    }

    /**
     * Get entire subtree as a nested array.
     * @param int $parent_id
     * @param int $depth current depth counter
     * @return array
     */
    public function get_subtree($parent_id, $depth = 1)
    {
        $children = $this->get_direct_children($parent_id);
        $labels = $this->get_level_labels();
        $tree = [];

        foreach ($children as $child) {
            $label_key = 'level_' . ($depth + 1) . '_label';
            $node = [
                'userid'      => $child['userid'],
                'company'     => $child['company'],
                'phonenumber' => $child['phonenumber'],
                'active'      => $child['active'],
                'level'       => $depth + 1,
                'level_label' => isset($labels[$label_key]) ? $labels[$label_key] : 'Level ' . ($depth + 1),
                'children'    => [],
            ];

            if ($depth + 1 < 4) {
                $node['children'] = $this->get_subtree($child['userid'], $depth + 1);
            }

            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * Count total descendants (recursive)
     * @param int $parent_id
     * @return int
     */
    public function count_descendants($parent_id)
    {
        $children = $this->get_direct_children($parent_id);
        $count = count($children);
        foreach ($children as $child) {
            $count += $this->count_descendants($child['userid']);
        }
        return $count;
    }

    // =========================================================================
    // DROPDOWN DATA
    // =========================================================================

    /**
     * Get list of vendors eligible to be parents for a given vendor.
     * Excludes: the vendor itself + all its descendants.
     * Only vendors with depth < 4 (so that the child would be at depth <= 4).
     *
     * @param int $vendor_id  The vendor being edited (0 for new vendor)
     * @return array
     */
    public function get_eligible_parents($vendor_id = 0)
    {
        $all_vendors = $this->db->select('userid, company, parent_vendor_id')
            ->order_by('company', 'asc')
            ->get(db_prefix() . 'pur_vendor')
            ->result_array();

        $excluded_ids = [];
        if ($vendor_id > 0) {
            $excluded_ids[] = $vendor_id;
            // Exclude all descendants
            $this->_collect_descendant_ids($vendor_id, $excluded_ids);
        }

        // Get descendant depth of the current vendor (for depth validation)
        $vendor_max_desc = 0;
        if ($vendor_id > 0) {
            $vendor_max_desc = $this->get_max_descendant_depth($vendor_id);
        }

        $eligible = [];
        foreach ($all_vendors as $v) {
            if (in_array($v['userid'], $excluded_ids)) {
                continue;
            }

            // Depth check: parent_depth + 1 (this vendor) + descendant_depth <= 4
            $parent_depth = $this->get_vendor_depth($v['userid']);
            if ($parent_depth + 1 + $vendor_max_desc > 4) {
                continue;
            }

            $level_label = $this->get_level_label($parent_depth);
            $v['depth'] = $parent_depth;
            $v['level_label'] = $level_label;
            $eligible[] = $v;
        }

        return $eligible;
    }

    /**
     * Collect all descendant IDs recursively
     */
    private function _collect_descendant_ids($parent_id, &$ids)
    {
        $children = $this->db->select('userid')
            ->where('parent_vendor_id', $parent_id)
            ->get(db_prefix() . 'pur_vendor')
            ->result_array();

        foreach ($children as $child) {
            $ids[] = $child['userid'];
            $this->_collect_descendant_ids($child['userid'], $ids);
        }
    }

    /**
     * Get parent vendor info
     * @param int $vendor_id
     * @return object|null
     */
    public function get_parent_vendor($vendor_id)
    {
        $vendor = $this->db->select('parent_vendor_id')
            ->where('userid', $vendor_id)
            ->get(db_prefix() . 'pur_vendor')
            ->row();

        if ($vendor && !empty($vendor->parent_vendor_id)) {
            return $this->db->select('userid, company')
                ->where('userid', $vendor->parent_vendor_id)
                ->get(db_prefix() . 'pur_vendor')
                ->row();
        }

        return null;
    }

    // =========================================================================
    // SEARCH
    // =========================================================================

    /**
     * Search vendors with hierarchy filters
     *
     * @param string $keyword   Search by company name
     * @param int    $level     Filter by hierarchy level (1-4), 0 = all
     * @param int    $parent_id Filter by parent vendor ID
     * @param string $status    'active', 'inactive', or '' for all
     * @return array
     */
    public function search_vendors($keyword = '', $level = 0, $parent_id = 0, $status = '')
    {
        $this->db->select('userid, company, phonenumber, city, state, active, parent_vendor_id');

        if (!empty($keyword)) {
            $this->db->group_start();
            $this->db->like('company', $keyword);
            $this->db->or_like('phonenumber', $keyword);
            $this->db->or_like('city', $keyword);
            $this->db->group_end();
        }

        if (!empty($parent_id)) {
            $this->db->where('parent_vendor_id', $parent_id);
        }

        if ($status === 'active') {
            $this->db->where('active', 1);
        } elseif ($status === 'inactive') {
            $this->db->where('active', 0);
        }

        $this->db->order_by('company', 'asc');
        $vendors = $this->db->get(db_prefix() . 'pur_vendor')->result_array();

        $labels = $this->get_level_labels();
        $results = [];

        foreach ($vendors as $v) {
            $depth = $this->get_vendor_depth($v['userid']);

            // Level filter (applied post-query since depth is computed)
            if ($level > 0 && $depth != $level) {
                continue;
            }

            $label_key = 'level_' . $depth . '_label';
            $parent = $this->get_parent_vendor($v['userid']);
            $breadcrumb = $this->get_breadcrumb($v['userid']);
            $children_count = count($this->get_direct_children($v['userid']));

            $results[] = [
                'userid'          => $v['userid'],
                'company'         => $v['company'],
                'phonenumber'     => $v['phonenumber'],
                'city'            => $v['city'],
                'state'           => $v['state'],
                'active'          => $v['active'],
                'depth'           => $depth,
                'level_label'     => isset($labels[$label_key]) ? $labels[$label_key] : 'Level ' . $depth,
                'parent_id'       => $v['parent_vendor_id'],
                'parent_company'  => $parent ? $parent->company : '',
                'children_count'  => $children_count,
                'breadcrumb'      => $breadcrumb,
            ];
        }

        return $results;
    }

    // =========================================================================
    // REPORTING
    // =========================================================================

    /**
     * Get vendors grouped by hierarchy level
     * @return array  ['level_1' => [...], 'level_2' => [...], ...]
     */
    public function get_vendors_by_level()
    {
        $all_vendors = $this->db->select('userid, company, parent_vendor_id, phonenumber, active, city, state')
            ->order_by('company', 'asc')
            ->get(db_prefix() . 'pur_vendor')
            ->result_array();

        $levels = [1 => [], 2 => [], 3 => [], 4 => []];
        $labels = $this->get_level_labels();

        foreach ($all_vendors as $v) {
            $depth = $this->get_vendor_depth($v['userid']);
            if ($depth >= 1 && $depth <= 4) {
                $v['depth'] = $depth;
                $v['level_label'] = $this->get_level_label($depth);
                $v['children_count'] = count($this->get_direct_children($v['userid']));
                $v['total_descendants'] = $this->count_descendants($v['userid']);
                $levels[$depth][] = $v;
            }
        }

        return ['levels' => $levels, 'labels' => $labels];
    }

    /**
     * Get the full hierarchy tree starting from all root vendors
     * @return array
     */
    public function get_full_tree()
    {
        $root_vendors = $this->db->select('userid, company, phonenumber, active, parent_vendor_id')
            ->where('parent_vendor_id IS NULL OR parent_vendor_id = 0')
            ->order_by('company', 'asc')
            ->get(db_prefix() . 'pur_vendor')
            ->result_array();

        $labels = $this->get_level_labels();
        $tree = [];

        foreach ($root_vendors as $root) {
            $label_key = 'level_1_label';
            $node = [
                'userid'      => $root['userid'],
                'company'     => $root['company'],
                'phonenumber' => $root['phonenumber'],
                'active'      => $root['active'],
                'level'       => 1,
                'level_label' => isset($labels[$label_key]) ? $labels[$label_key] : 'Level 1',
                'children'    => $this->get_subtree($root['userid'], 1),
            ];
            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * Get hierarchy summary statistics
     * @return array
     */
    public function get_hierarchy_summary()
    {
        $total_vendors = $this->db->count_all(db_prefix() . 'pur_vendor');

        $top_level = $this->db->where('parent_vendor_id IS NULL OR parent_vendor_id = 0')
            ->count_all_results(db_prefix() . 'pur_vendor');

        $with_children = $this->db->query('SELECT COUNT(DISTINCT parent_vendor_id) as cnt
            FROM ' . db_prefix() . 'pur_vendor
            WHERE parent_vendor_id IS NOT NULL AND parent_vendor_id > 0')
            ->row()->cnt;

        // Find deepest chain
        $all_vendors = $this->db->select('userid')
            ->get(db_prefix() . 'pur_vendor')
            ->result_array();

        $max_depth = 0;
        foreach ($all_vendors as $v) {
            $depth = $this->get_vendor_depth($v['userid']);
            if ($depth > $max_depth) {
                $max_depth = $depth;
            }
        }

        $avg_children = 0;
        if ($with_children > 0) {
            $total_children = $this->db->query('SELECT COUNT(*) as cnt
                FROM ' . db_prefix() . 'pur_vendor
                WHERE parent_vendor_id IS NOT NULL AND parent_vendor_id > 0')
                ->row()->cnt;
            $avg_children = round($total_children / $with_children, 1);
        }

        // Level distribution
        $level_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        foreach ($all_vendors as $v) {
            $depth = $this->get_vendor_depth($v['userid']);
            if (isset($level_counts[$depth])) {
                $level_counts[$depth]++;
            }
        }

        return [
            'total_vendors'   => $total_vendors,
            'top_level'       => $top_level,
            'with_children'   => $with_children,
            'deepest_chain'   => $max_depth,
            'avg_children'    => $avg_children,
            'level_counts'    => $level_counts,
        ];
    }

    /**
     * Get orphan vendors (referencing non-existent parent)
     * @return array
     */
    public function get_orphan_vendors()
    {
        return $this->db->query('SELECT v.userid, v.company, v.parent_vendor_id
            FROM ' . db_prefix() . 'pur_vendor v
            WHERE v.parent_vendor_id IS NOT NULL
              AND v.parent_vendor_id > 0
              AND v.parent_vendor_id NOT IN (
                  SELECT userid FROM ' . db_prefix() . 'pur_vendor
              )
            ORDER BY v.company ASC')
            ->result_array();
    }

    /**
     * Export hierarchy data as CSV-ready array
     * @return array
     */
    public function get_export_data()
    {
        $all_vendors = $this->db->select('userid, company, phonenumber, city, state, active, parent_vendor_id')
            ->order_by('company', 'asc')
            ->get(db_prefix() . 'pur_vendor')
            ->result_array();

        $labels = $this->get_level_labels();
        $data = [];

        foreach ($all_vendors as $v) {
            $depth = $this->get_vendor_depth($v['userid']);
            $label_key = 'level_' . $depth . '_label';
            $parent = $this->get_parent_vendor($v['userid']);

            $crumbs = $this->get_breadcrumb($v['userid']);
            $path_parts = [];
            foreach ($crumbs as $crumb) {
                $path_parts[] = $crumb['company'];
            }

            $data[] = [
                'ID'              => $v['userid'],
                'Company'         => $v['company'],
                'Level'           => $depth,
                'Level_Label'     => isset($labels[$label_key]) ? $labels[$label_key] : 'Level ' . $depth,
                'Parent_ID'       => $v['parent_vendor_id'] ? $v['parent_vendor_id'] : '',
                'Parent_Company'  => $parent ? $parent->company : '',
                'Hierarchy_Path'  => implode(' > ', $path_parts),
                'Phone'           => $v['phonenumber'],
                'City'            => $v['city'],
                'State'           => $v['state'],
                'Active'          => $v['active'] ? 'Yes' : 'No',
                'Children'        => count($this->get_direct_children($v['userid'])),
                'Total_Descendants' => $this->count_descendants($v['userid']),
            ];
        }

        return $data;
    }
}
