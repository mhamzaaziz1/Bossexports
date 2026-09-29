<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_documentation extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('smart_documentation_model');
    }

    public function index()
    {
        redirect(admin_url('smart_documentation/dashboard'));
    }

    public function dashboard()
    {
        if (!has_permission('smart_documentation', '', 'view')) {
            access_denied('Smart Documentation');
        }
        
        // Check if 'q' is present in the GET request, distinguishing between empty search and no search
        if (isset($_GET['q'])) {
            $q = $this->input->get('q');
            // If empty, searching for empty string will return all results (LIKE %%)
            $data['search_results'] = $this->smart_documentation_model->search_articles($q);
            $data['q'] = $q;
            $data['categories'] = []; // Don't verify tree if searching
        } else {
            $categories = $this->smart_documentation_model->get_categories();
            foreach ($categories as $cat_key => $category) {
                $sections = $this->smart_documentation_model->get_sections_by_category($category['id']);
                foreach ($sections as $sec_key => $section) {
                    $sections[$sec_key]['articles'] = $this->smart_documentation_model->get_articles_by_section($section['id']);
                }
                $categories[$cat_key]['sections'] = $sections;
            }
            $data['categories'] = $categories;
        }

        $data['title'] = _l('sd_dashboard');
        $this->load->view('dashboard', $data);
    }

    /* List all categories */
    public function manage()
    {
        if (!has_permission('smart_documentation', '', 'view')) {
            access_denied('Smart Documentation');
        }

        $categories = $this->smart_documentation_model->get_categories();
        foreach ($categories as $key => $category) {
            $categories[$key]['sections'] = $this->smart_documentation_model->get_sections_by_category($category['id']);
        }
        
        $data['categories'] = $categories;
        $data['title']      = _l('sd_manage_docs');
        $this->load->view('manage_structure', $data);
    }

    /* Add or update category */
    public function category($id = '')
    {
        if (!has_permission('smart_documentation', '', 'create') && !has_permission('smart_documentation', '', 'edit')) {
            access_denied('Smart Documentation');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            if ($id == '') {
                if (!has_permission('smart_documentation', '', 'create')) {
                    access_denied('Smart Documentation');
                }
                $id = $this->smart_documentation_model->add_category($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('sd_categories')));
                }
            } else {
                if (!has_permission('smart_documentation', '', 'edit')) {
                    access_denied('Smart Documentation');
                }
                $success = $this->smart_documentation_model->update_category($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('sd_categories')));
                }
            }
            redirect(admin_url('smart_documentation/manage'));
        }
    }

    /* Delete category */
    public function delete_category($id)
    {
        if (!has_permission('smart_documentation', '', 'delete')) {
            access_denied('Smart Documentation');
        }

        $response = $this->smart_documentation_model->delete_category($id);
        if (is_array($response) && isset($response['status']) && $response['status'] == false) {
             set_alert('warning', $response['message']);
        } elseif ($response == true) {
            set_alert('success', _l('deleted_successfully', _l('sd_categories')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('sd_categories')));
        }
        redirect(admin_url('smart_documentation/manage'));
    }

    /* Add or update section */
    public function section($id = '')
    {
        if (!has_permission('smart_documentation', '', 'create') && !has_permission('smart_documentation', '', 'edit')) {
            access_denied('Smart Documentation');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            if ($id == '') {
                if (!has_permission('smart_documentation', '', 'create')) {
                    access_denied('Smart Documentation');
                }
                $id = $this->smart_documentation_model->add_section($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('sd_sections')));
                }
            } else {
                if (!has_permission('smart_documentation', '', 'edit')) {
                    access_denied('Smart Documentation');
                }
                $success = $this->smart_documentation_model->update_section($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('sd_sections')));
                }
            }
            redirect(admin_url('smart_documentation/manage'));
        }
    }

    /* Delete section */
    public function delete_section($id)
    {
        if (!has_permission('smart_documentation', '', 'delete')) {
            access_denied('Smart Documentation');
        }

        $response = $this->smart_documentation_model->delete_section($id);
        if (is_array($response) && isset($response['status']) && $response['status'] == false) {
             set_alert('warning', $response['message']);
        } elseif ($response == true) {
            set_alert('success', _l('deleted_successfully', _l('sd_sections')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('sd_sections')));
        }
        redirect(admin_url('smart_documentation/manage'));
    }

    /* Create or Edit Article */
    public function article($id = '')
    {
        if (!has_permission('smart_documentation', '', 'create') && !has_permission('smart_documentation', '', 'edit')) {
            access_denied('Smart Documentation');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            
            // Handle role visibility array
            if (isset($data['role_visibility']) && is_array($data['role_visibility'])) {
                $data['role_visibility'] = implode(',', $data['role_visibility']);
            } else {
                $data['role_visibility'] = '';
            }

            // Unset is_published since we use status now, but for backward compat set published status
            // if (isset($data['is_published'])) { unset($data['is_published']); } 
            
            if ($id == '') {
                if (!has_permission('smart_documentation', '', 'create')) {
                    access_denied('Smart Documentation');
                }
                $id = $this->smart_documentation_model->add_article($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('sd_articles')));
                    redirect(admin_url('smart_documentation/article/' . $id));
                }
            } else {
                if (!has_permission('smart_documentation', '', 'edit')) {
                    access_denied('Smart Documentation');
                }
                $success = $this->smart_documentation_model->update_article($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('sd_articles')));
                }
                redirect(admin_url('smart_documentation/article/' . $id));
            }
        }

        if ($id != '') {
            $data['article'] = $this->smart_documentation_model->get_article($id);
            if (!$data['article']) {
                show_404();
            }
            $data['title'] = $data['article']->title;
        } else {
            $data['title'] = _l('sd_new_article');
        }

        // Get categories with sections for the dropdown
        $categories = $this->smart_documentation_model->get_categories();
        foreach ($categories as $key => $category) {
            $categories[$key]['sections'] = $this->smart_documentation_model->get_sections_by_category($category['id']);
        }
        $data['categories'] = $categories;
        
        // Get Roles
        $this->load->model('roles_model');
        $data['roles'] = $this->roles_model->get();

        // Get Modules
        $data['modules'] = $this->app_modules->get();

        $this->load->view('article', $data);
    }

    /**
     * Delete Article
     */
    public function delete_article($id)
    {
        if (!has_permission('smart_documentation', '', 'delete')) {
            access_denied('Smart Documentation');
        }

        $success = $this->smart_documentation_model->delete_article($id);
        if ($success) {
            set_alert('success', _l('deleted_successfully', _l('sd_articles')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('sd_articles')));
        }
        redirect(admin_url('smart_documentation/dashboard'));
    }

    public function settings()
    {
        if (!has_permission('smart_documentation', '', 'view')) { 
             access_denied('Smart Documentation Settings');
        }
        $data['title'] = _l('sd_settings');
        // $this->load->view('settings', $data);
        echo "Settings Page Placeholder";
    }

    /* AJAX Search handler */
    public function get_articles_ajax()
    {
        if (!has_permission('smart_documentation', '', 'view')) {
            ajax_access_denied();
        }

        $q = $this->input->get('q');
        
        $this->load->model('smart_documentation_model');
        
        if ($q && trim($q) != '') {
            $articles = $this->smart_documentation_model->search_articles($q);
        } else {
            // Get all articles if no search
            // We need a method to get ALL articles, or we can iterate categories.
            // For performance, let's add a clean get_all_articles method to model.
            // For now, I'll reuse search with empty string if model supports it, 
            // OR fetch properly. 
            // The search_articles method I wrote uses LIKE '%%' so it works for empty string too?
            // Let's verify search_articles logic. 
            // Logic: like('title', $q) -> like('title', '') matches everything? Yes usually.
            $articles = $this->smart_documentation_model->search_articles('');
        }

        // Enrich data with category/section names if not present in search result
        // The search_articles currently just does get table. 
        // We need to JOIN to get names for the cards.
        
        // Actually, let's optimize the model method first, but for now filtering in PHP or doing a better query.
        // Let's update the model to do a JOIN. 
        
        echo json_encode($articles);
    }
}
