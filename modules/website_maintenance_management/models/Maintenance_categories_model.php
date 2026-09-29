<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Maintenance_categories_model extends App_Model {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get category/categories
	 *
	 * @param  mixed  $id  Category ID or empty for all
	 *
	 * @return mixed
	 */
	public function get($id = '')
	{
		if (is_numeric($id))
		{
			$this->db->where('id', $id);

			return $this->db->get(db_prefix().'wmm_categories')->row();
		}

		$this->db->order_by('display_order', 'ASC');
		$this->db->order_by('name', 'ASC');

		return $this->db->get(db_prefix().'wmm_categories')->result_array();
	}

	/**
	 * Get active categories only
	 *
	 * @return array
	 */
	public function get_active()
	{
		$this->db->where('is_active', 1);
		$this->db->order_by('display_order', 'ASC');
		$this->db->order_by('name', 'ASC');

		return $this->db->get(db_prefix().'wmm_categories')->result_array();
	}

	/**
	 * Get categories as select options
	 *
	 * @return array
	 */
	public function get_dropdown()
	{
		$categories = $this->get_active();
		$dropdown   = [];

		foreach ($categories as $category)
		{
			$dropdown[] = [
				'id'   => $category['slug'],
				'name' => $category['name'],
			];
		}

		return $dropdown;
	}

	/**
	 * Add new category
	 *
	 * @param  array  $data  Category data
	 *
	 * @return mixed
	 */
	public function add($data): mixed
	{
		$data['is_active']  = isset($data['is_active']) ? 1 : 0;
		$data['created_by'] = get_staff_user_id();
		$data['created_at'] = date('Y-m-d H:i:s');

		// Generate slug from name if not provided
		if (empty($data['slug']))
		{
			$data['slug'] = $this->generate_slug($data['name']);
		} else
		{
			$data['slug'] = $this->generate_slug($data['slug']);
		}

		// Check if slug already exists
		if ($this->slug_exists($data['slug']))
		{
			return ['error' => _l('wmm_category_slug_exists')];
		}

		$this->db->insert(db_prefix().'wmm_categories', $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			log_activity('New Maintenance Category Added [ID:'.$insert_id.', Name: '.$data['name'].']');

			return $insert_id;
		}

		return FALSE;
	}

	/**
	 * Update category
	 *
	 * @param  array  $data  Category data
	 * @param  int  $id  Category ID
	 *
	 * @return array|bool
	 */
	public function update($data, $id)
	{
		$data['is_active'] = isset($data['is_active']) ? 1 : 0;

		// Generate slug from name if slug is changed
		if (isset($data['slug']))
		{
			$data['slug'] = $this->generate_slug($data['slug']);

			// Check if new slug exists (excluding current category)
			if ($this->slug_exists($data['slug'], $id))
			{
				return [
					'error' => _l('wmm_category_slug_exists'),
				];
			}
		}

		$this->db->where('id', $id);
		$this->db->update(db_prefix().'wmm_categories', $data);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Maintenance Category Updated [ID:'.$id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Delete category
	 *
	 * @param  int  $id  Category ID
	 *
	 * @return mixed
	 */
	public function delete($id)
	{
		// Check if category is being used in tasks
		$this->db->where('category', $this->get_category_slug($id));
		$tasks_count = $this->db->count_all_results(db_prefix().'wmm_maintenance_tasks');

		if ($tasks_count > 0)
		{
			return ['error' => _l('wmm_category_has_tasks', $tasks_count)];
		}

		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'wmm_categories');

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Maintenance Category Deleted [ID:'.$id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Toggle category active status
	 *
	 * @param  int  $id  Category ID
	 * @param  int  $status  New status
	 *
	 * @return bool
	 */
	public function toggle_active($id, $status)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix().'wmm_categories', ['is_active' => $status]);

		return $this->db->affected_rows() > 0;
	}

	/**
	 * Get category name by slug
	 *
	 * @param  string  $slug  Category slug
	 *
	 * @return string
	 */
	public function get_name_by_slug($slug)
	{
		$this->db->select('name');
		$this->db->where('slug', $slug);
		$category = $this->db->get(db_prefix().'wmm_categories')->row();

		return $category ? $category->name : $slug;
	}

	/**
	 * Get category slug by ID
	 *
	 * @param  int  $id  Category ID
	 *
	 * @return string
	 */
	private function get_category_slug($id)
	{
		$this->db->select('slug');
		$this->db->where('id', $id);
		$category = $this->db->get(db_prefix().'wmm_categories')->row();

		return $category ? $category->slug : '';
	}

	/**
	 * Generate unique slug
	 *
	 * @param  string  $text  Input text
	 *
	 * @return string
	 */
	private function generate_slug($text)
	{
		// Convert to lowercase
		$slug = strtolower($text);

		// Replace spaces and special characters with hyphens
		$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

		// Remove leading/trailing hyphens
		return trim($slug, '-');
	}

	/**
	 * Check if slug exists
	 *
	 * @param  string  $slug  Slug to check
	 * @param  int  $exclude_id  Exclude this ID from check
	 *
	 * @return bool
	 */
	private function slug_exists($slug, $exclude_id = NULL)
	{
		$this->db->where('slug', $slug);

		if ($exclude_id)
		{
			$this->db->where('id !=', $exclude_id);
		}

		return $this->db->count_all_results(db_prefix().'wmm_categories') > 0;
	}

}
