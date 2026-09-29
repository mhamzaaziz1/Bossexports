<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Vendor_hierarchy extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('vendor_hierarchy/vendor_hierarchy_model');
    }



    /**
     * Search / Browse Hierarchy page
     */
    public function search()
    {
        if (!has_permission('vendor_hierarchy', '', 'view') && !is_admin()) {
            access_denied('vendor_hierarchy');
        }

        $data['title']  = _l('vendor_hierarchy_search');
        $data['labels'] = $this->vendor_hierarchy_model->get_level_labels();

        // Pre-selected level from menu click
        $data['selected_level'] = $this->input->get('level') ? (int)$this->input->get('level') : 0;

        // Get all vendors with parent info for the search selectpicker
        $data['all_vendors'] = $this->db->select('userid, company')
            ->order_by('company', 'asc')
            ->get(db_prefix() . 'pur_vendor')
            ->result_array();

        $this->load->view('search', $data);
    }

    /**
     * AJAX: Search vendors with hierarchy filters
     */
    public function search_ajax()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $keyword     = $this->input->post('keyword');
        $level       = $this->input->post('level');
        $parent_id   = $this->input->post('parent_id');
        $status      = $this->input->post('status');

        $results = $this->vendor_hierarchy_model->search_vendors($keyword, $level, $parent_id, $status);

        echo json_encode(['success' => true, 'results' => $results]);
        die;
    }

    /**
     * AJAX: Get children of a vendor for expandable tree in search
     */
    public function get_children_ajax()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $vendor_id = $this->input->post('vendor_id');
        $children  = $this->vendor_hierarchy_model->get_direct_children($vendor_id);
        $labels    = $this->vendor_hierarchy_model->get_level_labels();

        $enriched = [];
        foreach ($children as $child) {
            $depth = $this->vendor_hierarchy_model->get_vendor_depth($child['userid']);
            $child['depth'] = $depth;
            $child['level_label'] = $this->vendor_hierarchy_model->get_level_label($depth);
            $child['children_count'] = count($this->vendor_hierarchy_model->get_direct_children($child['userid']));
            $child['breadcrumb'] = $this->vendor_hierarchy_model->get_breadcrumb($child['userid']);
            $enriched[] = $child;
        }

        echo json_encode(['success' => true, 'children' => $enriched]);
        die;
    }

    /**
     * Reports page
     */
    public function reports()
    {
        if (!has_permission('vendor_hierarchy', '', 'view')) {
            access_denied('vendor_hierarchy');
        }

        $data['title']   = _l('vendor_hierarchy_reports');
        $data['labels']  = $this->vendor_hierarchy_model->get_level_labels();
        $data['summary'] = $this->vendor_hierarchy_model->get_hierarchy_summary();
        $data['by_level'] = $this->vendor_hierarchy_model->get_vendors_by_level();
        $data['full_tree'] = $this->vendor_hierarchy_model->get_full_tree();
        $data['orphans']  = $this->vendor_hierarchy_model->get_orphan_vendors();

        $this->load->view('reports', $data);
    }

    /**
     * AJAX: Validate parent assignment
     */
    public function validate_parent()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $vendor_id = $this->input->post('vendor_id');
        $parent_vendor_id = $this->input->post('parent_vendor_id');

        $result = $this->vendor_hierarchy_model->validate_parent_assignment(
            $vendor_id ? $vendor_id : 0,
            $parent_vendor_id
        );

        echo json_encode($result);
        die;
    }

    /**
     * AJAX: Get subtree for a vendor (used in tree view)
     */
    public function get_subtree()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $vendor_id = $this->input->get('vendor_id');
        $tree = $this->vendor_hierarchy_model->get_subtree($vendor_id);

        echo json_encode(['success' => true, 'tree' => $tree]);
        die;
    }

    /**
     * AJAX: Get breadcrumb for a vendor
     */
    public function get_breadcrumb()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $vendor_id = $this->input->get('vendor_id');
        $crumbs = $this->vendor_hierarchy_model->get_breadcrumb($vendor_id);

        echo json_encode(['success' => true, 'breadcrumb' => $crumbs]);
        die;
    }

    /**
     * Export hierarchy data as CSV
     */
    public function export_csv()
    {
        if (!has_permission('vendor_hierarchy', '', 'view')) {
            access_denied('vendor_hierarchy');
        }

        $data = $this->vendor_hierarchy_model->get_export_data();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=vendor_hierarchy_export_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');

        // Header row
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        die;
    }

    /**
     * AJAX: Get eligible parents for dropdown
     */
    public function get_eligible_parents()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $vendor_id = $this->input->get('vendor_id') ? $this->input->get('vendor_id') : 0;
        $parents = $this->vendor_hierarchy_model->get_eligible_parents($vendor_id);

        echo json_encode(['success' => true, 'parents' => $parents]);
        die;
    }

    /**
     * AJAX endpoint for report chart data
     */
    public function chart_data()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $summary = $this->vendor_hierarchy_model->get_hierarchy_summary();
        $labels = $this->vendor_hierarchy_model->get_level_labels();

        $chart = [
            'labels' => [],
            'data'   => [],
        ];

        for ($i = 1; $i <= 4; $i++) {
            $key = 'level_' . $i . '_label';
            $chart['labels'][] = isset($labels[$key]) ? $labels[$key] : 'Level ' . $i;
            $chart['data'][] = isset($summary['level_counts'][$i]) ? $summary['level_counts'][$i] : 0;
        }

        echo json_encode(['success' => true, 'chart' => $chart]);
        die;
    }
}
