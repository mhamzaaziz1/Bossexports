<?php
defined('BASEPATH') or exit('No direct script access allowed');

use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

class Poly_utilities extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Scripts
     * @return view
     */
    public function scripts()
    {
        $data['title'] = _l('poly_utilities_scripts_extend');
        $this->load->view('scripts/manage', $data);
    }

    /**
     * Add Scripts
     * @return view
     */
    public function scripts_add()
    {
        $data['title'] = 'Add JavaScript';
        $this->load->view('scripts/create', $data);
    }

    /**
     * Styles
     * @return view
     */
    public function styles()
    {
        $data['title'] = _l('poly_utilities_styles_extend');
        $this->load->view('styles/manage', $data);
    }

    /**
     * Add Styles
     * @return view
     */
    public function styles_add()
    {
        $data['title'] = 'Add Style';
        $this->load->view('styles/create', $data);
    }

    /**
     * Quick Access Menu
     * @return view
     */
    public function quick_access()
    {
        $data['title'] = _l('poly_utilities_shortcut_menu_extend');
        $this->load->view('quick_access/manage', $data);
    }

    /**
     * Custom Menu
     * @return view
     */
    public function custom_menu()
    {
        $data['title'] = _l('poly_utilities_custom_menu_extend');
        $this->load->view('custom_menu/manage', $data);
    }
    
    /**
     * Widgets
     * @return view
     */
    public function widgets()
    {
        $data['title'] = _l('poly_utilities_widgets_extend');
        $this->load->view('widgets/manage', $data);
    }

    /**
     * Support
     * @return view
     */
    public function support()
    {
        $poly_utilities_aio_supports = clear_textarea_breaks(get_option(POLY_SUPPORTS));
        $poly_utilities_aio_supports = !empty($poly_utilities_aio_supports) ? json_decode($poly_utilities_aio_supports, true) : [];

        $data['title'] = _l('poly_utilities_support');
        $data['poly_utilities_aio_supports'] = $poly_utilities_aio_supports;

        $this->load->view('support/manage', $data);
    }

    /**
     * Settings
     * @return view
     */
    public function settings()
    {
        $poly_utilities_settings = clear_textarea_breaks(get_option(POLY_UTILITIES_SETTINGS));

        if (empty($poly_utilities_settings)) {
            $obj_settings = new stdClass();
            $obj_settings->is_sticky = false;
            $obj_settings->is_toggle_sidebar_menu = false;
            $obj_settings->is_table_of_content = false;
            $obj_settings->is_active_scripts = true;
            $obj_settings->is_active_styles = true;
            $obj_settings->is_note_confirm_delete = true;
            $obj_settings->is_operation_functions = true;
            $obj_settings->is_scroll_to_top = false;
            update_option(POLY_UTILITIES_SETTINGS, json_encode($obj_settings));
            $poly_utilities_settings = clear_textarea_breaks(get_option(POLY_UTILITIES_SETTINGS));
        }
        $poly_utilities_settings = !empty($poly_utilities_settings) ? json_decode($poly_utilities_settings, true) : [];

        $data['title'] = _l('poly_utilities_settings');
        $data['poly_utilities_settings'] = $poly_utilities_settings;

        $this->load->view('settings', $data);
    }
    
    /**
     * Remove Quick Access Menu
     * @return view
     */
    public function delete_quick_access()
    {
        foreach (['link'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
            $$input_object = trim($$input_object);
            $$input_object = nl2br($$input_object);
        }

        $obj_storage = clear_textarea_breaks(get_option(POLY_QUICK_ACCESS_MENU));

        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);

            if (poly_utilities_common_helper::isExisted($obj_old_data, 'link', $link)) {
                $x = poly_utilities_common_helper::removeDataByField($obj_old_data, 'link', $link);
                update_option(POLY_QUICK_ACCESS_MENU, json_encode($x));
                poly_utilities_ajax_response_helper::response_success("Remove {$link}");
            }
        }
    }

    /**
     * Update Quick Access Menu
     * @return view
     */
    public function update_quick_access_menu()
    {
        $objs = $this->input->post('data', FALSE);

        update_option(POLY_QUICK_ACCESS_MENU, json_encode($objs));
        poly_utilities_ajax_response_helper::response_success('Success');
    }

    /**
     * Add Quick Access Menu
     * @return view
     */
    public function save_quick_access()
    {
        foreach (['icon', 'title', 'link', 'shortcut_key','target','rel'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
            $$input_object = trim($$input_object);
            $$input_object = nl2br($$input_object);
        }

        $obj = new stdClass();
        $obj->index = poly_utilities_common_helper::generateUniqueID();
        $obj->icon = $icon;
        $obj->title = $title;
        $obj->link = $link;
        $obj->target = !empty($target)?$target:'_self';
        $obj->rel = !empty($rel)?$rel:'nofollow';
        $obj->shortcut_key = $shortcut_key;

        $obj_storage = clear_textarea_breaks(get_option(POLY_QUICK_ACCESS_MENU));

        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);
            if (!poly_utilities_common_helper::isExisted($obj_old_data, 'link', $obj->link)) {
                $obj_old_data[] = $obj;
                update_option(POLY_QUICK_ACCESS_MENU, json_encode($obj_old_data));
            } else {
                poly_utilities_ajax_response_helper::response_data_exists(_l('poly_utilities_data_existed'));
            }
        } else {
            $obj_old_data[] = $obj;
            update_option(POLY_QUICK_ACCESS_MENU, json_encode($obj_old_data));
        }
        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_add_success'));
    }

    /**
     * Uppdate All in One Supports
     * @return json
     */
    public function save_aio_supports(){
        $objs = $this->input->post('data', FALSE);
        update_option(POLY_SUPPORTS, json_encode($objs));
        poly_utilities_ajax_response_helper::response_success('Success');
    }

    /**
     * Add resource: js | css
     * @return view
     */
    public function save_resource()
    {
        foreach (['title', 'file', 'mode', 'content', 'state', 'resource', 'is_embed', 'is_embed_position'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
            $$input_object = trim($$input_object);
        }
        switch ($mode) {
            case 'truetrue':
                $mode = 'admin_customers';
                break;
            case 'truefalse':
                $mode = 'admin';
                break;
            case 'falsetrue':
                $mode = 'customers';
                break;
        }
        $resourceTable = POLY_SCRIPTS;
        $resourceExtension = '.js';
        switch ($resource) {
            case 'js': {
                    $resourceTable = POLY_SCRIPTS;
                    $resourceExtension = '.js';
                    break;
                }
            case 'css': {
                    $resourceTable = POLY_STYLES;
                    $resourceExtension = '.css';
                    break;
                }
        }

        $obj = new stdClass();
        $obj->title = $title;
        $obj->file = ($file ? $file : poly_utilities_common_helper::convertToFileName($title));
        $obj->mode = $mode; //admin, customers, admin_customers;
        $obj->is_embed = $is_embed;
        $obj->is_embed_position = $is_embed_position;

        $obj_storage = clear_textarea_breaks(get_option($resourceTable));
        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);
            if (isset($state) && $state == true) {
                foreach ($obj_old_data as &$item) {
                    if ($item['file'] === $obj->file) {
                        $item['title'] =  $obj->title;
                        $item['mode'] = $mode;
                        $item['is_embed'] = $is_embed;
                        $item['is_embed_position'] = $is_embed_position;
                    }
                }
                unset($item);

                $isSave = poly_utilities_common_helper::saveToFile($obj->file . $resourceExtension, POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/' . $resource, $content, true);
                if ($isSave == 1) {
                    update_option($resourceTable, json_encode($obj_old_data));
                    poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_update_success'));
                } else {
                    poly_utilities_ajax_response_helper::response_data_not_saved(_l('poly_utilities_resource_access_error'));
                }
            }
        }

        if (poly_utilities_common_helper::isExisted($obj_old_data, 'file', $obj->file)) {
            poly_utilities_ajax_response_helper::response_data_exists(_l('poly_utilities_data_existed'));
        } else {
            $file_path = POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/' . $resource . '/' . $file . $resourceExtension;
            if (file_exists($file_path)) {
                if (!unlink($file_path)) {
                    poly_utilities_ajax_response_helper::response_data_not_saved(_l('poly_utilities_resource_access_error'));
                }
            }
        }

        $obj_old_data[] = $obj;
        $isSave = poly_utilities_common_helper::saveToFile($obj->file . $resourceExtension, POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/' . $resource, $content);
        if ($isSave == 1) {
            update_option($resourceTable, json_encode($obj_old_data));
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_add_success'));
        } else {
            poly_utilities_ajax_response_helper::response_data_not_saved(_l('poly_utilities_resource_access_error'));
        }
    }

    /**
     * Remove resource: js | css
     * @return view
     */
    public function delete_resource()
    {
        foreach (['id', 'resource'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
            $$input_object = trim($$input_object);
        }

        $resourceTable = POLY_SCRIPTS;
        switch ($resource) {
            case 'js': {
                    $resourceTable = POLY_SCRIPTS;
                    break;
                }
            case 'css': {
                    $resourceTable = POLY_STYLES;
                    break;
                }
        }

        $obj_storage = clear_textarea_breaks(get_option($resourceTable));

        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);
            if (poly_utilities_common_helper::isExisted($obj_old_data, 'file', $id)) {
                $x = poly_utilities_common_helper::removeDataByField($obj_old_data, 'file', $id);
                update_option($resourceTable, json_encode($x));
                $file_path = POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/' . $resource . '/' . $id . '.' . $resource;
                if (file_exists($file_path)) {
                    if (unlink($file_path)) {
                        poly_utilities_ajax_response_helper::response_success("Remove {$id}");
                    }
                }
            }
        }
    }

    /**
     * Update Settings
     * @return view
     */
    public function update_settings()
    {
        $objs = $this->input->post('data', FALSE);
        update_option(POLY_UTILITIES_SETTINGS, json_encode($objs));
        poly_utilities_ajax_response_helper::response_success($objs);
    }

    /**
     * Update the display filter configuration for the data tables.
     * @return view
     */
    public function save_data_filters()
    {
        $objs = $this->input->post('data', FALSE);

        $obj = new stdClass();
        $obj->key = $objs['key'];
        $obj->value = $objs['value'];

        $dataFilters = get_option(POLY_TABLE_FILTERS);

        $dataTaleFilters=[];
        if (!empty($dataFilters)) {
            $dataTaleFilters = json_decode($dataFilters, true);

            //Update
            if (poly_utilities_common_helper::isExisted($dataTaleFilters, 'key', $obj->key)) {
                $dataTableFiltersUpdate=poly_utilities_common_helper::updateDataByField($dataTaleFilters, 'key', $obj->key, $obj);
                if(update_option(POLY_TABLE_FILTERS,json_encode($dataTableFiltersUpdate))===true){
                    poly_utilities_ajax_response_helper::response_success('Success');
                }else{
                    poly_utilities_ajax_response_helper::response_data_not_saved('Error');
                }
            }
        }

        //Add
        $dataTaleFilters[]=$obj;
        if(update_option(POLY_TABLE_FILTERS,json_encode($dataTaleFilters))===true){
            poly_utilities_ajax_response_helper::response_success('Success');
        }else{
            poly_utilities_ajax_response_helper::response_data_not_saved('Error');
        }
    }

    public function update_widget()
    {
        if (is_admin() || has_permission('poly_utilities_widgets_extend', '', 'edit') || has_permission('poly_utilities_widgets_extend', '', 'delete')) {
            $objs = $this->input->post('data', FALSE);
            update_option(POLY_WIDGETS, json_encode($objs));
            poly_utilities_ajax_response_helper::response_success('Success');
        }else{
            poly_utilities_ajax_response_helper::response_data_not_saved(_l('access_denied'));
        }
    }
}