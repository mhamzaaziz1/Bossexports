<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_documentation_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get categories
     * @param  mixed $id category id
     * @return mixed
     */
    public function get_categories($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'smart_docs_categories')->row();
        }

        $this->db->order_by('sort_order', 'asc');
        return $this->db->get(db_prefix() . 'smart_docs_categories')->result_array();
    }

    /**
     * Add new category
     * @param array $data category data
     */
    public function add_category($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = slug_it($data['name']);
        }
        
        $this->db->insert(db_prefix() . 'smart_docs_categories', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Smart Docs Category Added [ID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
            return $insert_id;
        }

        return false;
    }

    /**
     * Update category
     * @param  array $data category data
     * @param  mixed $id   category id
     * @return boolean
     */
    public function update_category($data, $id)
    {
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = slug_it($data['name']);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'smart_docs_categories', $data);

        if ($this->db->affected_rows() > 0) {
            log_activity('Smart Docs Category Updated [ID: ' . $id . ', Name: ' . $data['name'] . ']');
            return true;
        }

        return false;
    }

    /**
     * Delete category
     * @param  mixed $id category id
     * @return boolean
     */
    public function delete_category($id)
    {
        // Check if category has sections
        $this->db->where('category_id', $id);
        $sections = $this->db->get(db_prefix() . 'smart_docs_sections')->result_array();
        
        if (count($sections) > 0) {
            // Optional: Move sections to another category or prevent delete
            // For now, we returns false with message
            return ['status' => false, 'message' => _l('sd_category_has_sections')]; // You'll need to add this lang key or handle it
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'smart_docs_categories');

        if ($this->db->affected_rows() > 0) {
            log_activity('Smart Docs Category Deleted [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Get sections
     * @param  mixed $id section id
     * @return mixed
     */
    public function get_sections($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'smart_docs_sections')->row();
        }

        $this->db->select(db_prefix() . 'smart_docs_sections.*, ' . db_prefix() . 'smart_docs_categories.name as category_name');
        $this->db->join(db_prefix() . 'smart_docs_categories', db_prefix() . 'smart_docs_categories.id = ' . db_prefix() . 'smart_docs_sections.category_id', 'left');
        $this->db->order_by(db_prefix() . 'smart_docs_sections.sort_order', 'asc');
        
        return $this->db->get(db_prefix() . 'smart_docs_sections')->result_array();
    }
    
    /**
     * Get sections by category
     */
    public function get_sections_by_category($category_id)
    {
        $this->db->where('category_id', $category_id);
        $this->db->order_by('sort_order', 'asc');
        return $this->db->get(db_prefix() . 'smart_docs_sections')->result_array();
    }

    /**
     * Add new section
     * @param array $data section data
     */
    public function add_section($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = slug_it($data['name']);
        }

        $this->db->insert(db_prefix() . 'smart_docs_sections', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Smart Docs Section Added [ID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
            return $insert_id;
        }

        return false;
    }

    /**
     * Update section
     * @param  array $data section data
     * @param  mixed $id   section id
     * @return boolean
     */
    public function update_section($data, $id)
    {
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = slug_it($data['name']);
        }
        
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'smart_docs_sections', $data);

        if ($this->db->affected_rows() > 0) {
            log_activity('Smart Docs Section Updated [ID: ' . $id . ', Name: ' . $data['name'] . ']');
            return true;
        }

        return false;
    }

    /**
     * Delete section
     * @param  mixed $id section id
     * @return boolean
     */
    public function delete_section($id)
    {
         // Check if section has articles
        $this->db->where('section_id', $id);
        $articles = $this->db->get(db_prefix() . 'smart_docs_articles')->result_array();
        
        if (count($articles) > 0) {
            return ['status' => false, 'message' => _l('sd_section_has_articles')];
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'smart_docs_sections');

        if ($this->db->affected_rows() > 0) {
            log_activity('Smart Docs Section Deleted [ID: ' . $id . ']');
            return true;
        }

        return false;
    }
    /**
     * Get article
     * @param  mixed $id article id
     * @return mixed
     */
    public function get_article($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'smart_docs_articles')->row();
    }
    
    /**
     * Add new article
     * @param array $data article data
     */
    public function add_article($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = slug_it($data['title']);
        }
        $data['author_id'] = get_staff_user_id();

        $this->db->insert(db_prefix() . 'smart_docs_articles', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Smart Docs Article Added [ID: ' . $insert_id . ', Title: ' . $data['title'] . ']');
            return $insert_id;
        }

        return false;
    }

    /**
     * Update article
     * @param  array $data article data
     * @param  mixed $id   article id
     * @return boolean
     */
    public function update_article($data, $id)
    {
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = slug_it($data['title']);
        }
        
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'smart_docs_articles', $data);

        if ($this->db->affected_rows() > 0) {
            log_activity('Smart Docs Article Updated [ID: ' . $id . ', Title: ' . $data['title'] . ']');
            return true;
        }

        return false;
    }

    /**
     * Get articles by section
     * @param  mixed $section_id
     * @return mixed
     */
    public function get_articles_by_section($section_id)
    {
        $this->db->where('section_id', $section_id);
        $this->db->order_by('created_at', 'desc');
        return $this->db->get(db_prefix() . 'smart_docs_articles')->result_array();
    }


    /**
     * Delete article
     * @param  mixed $id article id
     * @return boolean
     */
    public function delete_article($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'smart_docs_articles');
        if ($this->db->affected_rows() > 0) {
            log_activity('Smart Docs Article Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }
    
    /**
     * Search articles
     * @param string $q query
     * @return array
     */
    public function search_articles($q)
    {
        $this->db->select(db_prefix() . 'smart_docs_articles.*, ' . db_prefix() . 'smart_docs_sections.name as section_name, ' . db_prefix() . 'smart_docs_categories.name as category_name, ' . db_prefix() . 'smart_docs_categories.icon as category_icon, ' . db_prefix() . 'smart_docs_articles.related_module');
        $this->db->from(db_prefix() . 'smart_docs_articles');
        $this->db->join(db_prefix() . 'smart_docs_sections', db_prefix() . 'smart_docs_sections.id = ' . db_prefix() . 'smart_docs_articles.section_id', 'left');
        $this->db->join(db_prefix() . 'smart_docs_categories', db_prefix() . 'smart_docs_categories.id = ' . db_prefix() . 'smart_docs_sections.category_id', 'left');
        
        $this->db->group_start();
        $this->db->like(db_prefix() . 'smart_docs_articles.title', $q);
        $this->db->or_like(db_prefix() . 'smart_docs_articles.content', $q);
        $this->db->group_end();
        
        $this->db->order_by(db_prefix() . 'smart_docs_articles.created_at', 'desc');
        return $this->db->get()->result_array();
    }
}
