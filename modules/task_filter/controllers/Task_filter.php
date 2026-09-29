<?php

defined('BASEPATH') or exit('No direct script access allowed');

class task_filter extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('task_filter_model');
    }

    /* List all announcements */
    public function index()
    {
        $this->load->model('staff_model');
        $data['staff'] = $this->staff_model->get();
        $data['title'] = _l('task_filter');
        $this->load->view('manage_task_filter', $data);
    }
    public function task_filter_table(){
        $this->app->get_table_data(module_views_path('task_filter', 'table_task_filter'));
    }
    public function task_filters($id = ''){
        if ($this->input->post()) {
            $data                = $this->input->post();
            if (!$this->input->post('id')) {
                $id = $this->task_filter_model->add_task_filter($data);
                if($id){
                    set_alert('success', _l('added_successfully', _l('task_filter')));
                    redirect(admin_url('task_filter'));
                }
            }else{
                $id = $data['id'];
                unset($data['id']);
                $success = $this->task_filter_model->update_task_filter($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('task_filter')));
                }
                redirect(admin_url('task_filter'));
            }
        }
    }
    public function delete_task_filter($id = ''){
        if (!$id) {
            redirect(admin_url('task_filter'));
        }
        $response = $this->task_filter_model->delete_task_filter($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('task_filter')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('task_filter')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('task_filter')));
        }
        redirect(admin_url('task_filter'));
    }
    public function get_edit_task_filter_data($id){
        $project = $this->task_filter_model->get_data_task_filter($id,'project');
        $milestone = $this->task_filter_model->get_data_task_filter($id,'milestone');
        $task = $this->task_filter_model->get_data_task_filter($id,'task');
        $time = $this->task_filter_model->get_data_task_filter($id,'time');
        $priority = $this->task_filter_model->get_data_task_filter($id,'priority');
        $status = $this->task_filter_model->get_data_task_filter($id,'status');
        $assigned = $this->task_filter_model->get_data_task_filter($id,'assigned');
        if($time[0] == 'xday'){
            $xday = $this->task_filter_model->get_xday_task_filter($id,'time');
            echo json_encode([
            'project' => $project,
            'milestone' => $milestone,
            'task' => $task,
            'time' => $time,
            'xday' => $xday,
            'priority' => $priority,
            'status' => $status,
            'assigned' => $assigned
        ]);
        }
        elseif($time[0] == 'day_to_day'){
            $from_day = $this->task_filter_model->get_day_to_day_task_filter($id,'time','from_day');
            $to_day = $this->task_filter_model->get_day_to_day_task_filter($id,'time','to_day');
            echo json_encode([
            'project' => $project,
            'milestone' => $milestone,
            'task' => $task,
            'time' => $time,
            'from_day' => $from_day,
            'to_day' => $to_day,
            'priority' => $priority,
            'status' => $status,
            'assigned' => $assigned
        ]);
        }else{
            echo json_encode([
            'project' => $project,
            'milestone' => $milestone,
            'task' => $task,
            'time' => $time,
            'priority' => $priority,
            'status' => $status,
            'assigned' => $assigned
        ]);
        }
    }
    public function view_data_filter($id){
        $task_filter = $this->task_filter_model->get_task_filter($id);
        if($task_filter->creator == get_staff_user_id()){
            $data['field'] = [];
            $project = $this->task_filter_model->get_data_task_filter($id,'project');
            $milestone = $this->task_filter_model->get_data_task_filter($id,'milestone');
            $task = $this->task_filter_model->get_data_task_filter($id,'task');
            $data['field']['project'] = $project;
            $data['field']['milestone'] = $milestone;        
            $data['field']['task'] = $task;
            $data['id'] = $id;
            $data['title'] = $task_filter->filter_name;
            $this->load->view('view_task_filter', $data);
        }else{
            access_denied('reports');
        }
        
    }
    public function view_filter_table($id){
        $field = [];
        $project = $this->task_filter_model->get_data_task_filter($id,'project');
        $milestone = $this->task_filter_model->get_data_task_filter($id,'milestone');
        $task = $this->task_filter_model->get_data_task_filter($id,'task');
        $field['project'] = $project;
        $field['milestone'] = $milestone;        
        $field['task'] = $task;
        $time = $this->task_filter_model->get_data_task_filter($id,'time');
        $priority = $this->task_filter_model->get_data_task_filter($id,'priority');
        $status = $this->task_filter_model->get_data_task_filter($id,'status');
        $assigned = $this->task_filter_model->get_data_task_filter($id,'assigned');
        
        if($time[0] == 'xday'){
            $xday = $this->task_filter_model->get_xday_task_filter($id,'time');
            $this->app->get_table_data(module_views_path('task_filter', 'table_view_filter'), [
            'field' => $field,
            'time'  => $time,
            'xday'  => $xday,
            'priority' => $priority,
            'assigned' => $assigned,
            'status' => $status,
        ]);
        }
        elseif($time[0] == 'day_to_day'){
            $from_day = $this->task_filter_model->get_day_to_day_task_filter($id,'time','from_day');
            $to_day = $this->task_filter_model->get_day_to_day_task_filter($id,'time','to_day');
            $this->app->get_table_data(module_views_path('task_filter', 'table_view_filter'), [
            'field' => $field,
            'time'  => $time,
            'from_day' => $from_day,
            'to_day'   => $to_day,
            'priority' => $priority,
            'assigned' => $assigned,
            'status' => $status,
        ]);
        }else{
            $this->app->get_table_data(module_views_path('task_filter', 'table_view_filter'), [
            'field' => $field,
            'time'  => $time,
            'priority' => $priority,
            'assigned' => $assigned,
            'status' => $status,
        ]);
        }
    }
}