<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Carbon\Carbon;

class Bpmn extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('bpmn_model');
        $this->load->model('staff_model');
        $this->load->model('clients_model');
    }

    /* List all BPMN */
    public function index()
    {
        if (!has_permission('bpmn', '', 'view')) {
            access_denied('bpmn');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('bpmn', 'table'));
        }

        $data['switch_grid'] = false;

        if ($this->session->userdata('bpmn_grid_view') == 'true') {
            $data['switch_grid'] = true;
        }

        $this->load->model('staff_model');
        $data['staffs'] = $this->staff_model->get();
        $data['groups'] = $this->bpmn_model->get_groups();
        $data['bpmn_groups']    = $this->bpmn_model->get_groups();
        $data['projects']    = $this->bpmn_model->get_projects();
        $data['title'] = _l('bpmn');
        $this->app_scripts->add('bpmn-js','modules/bpmn/assets/js/bpmn.js');
        $this->load->view('manage', $data);
    }

    public function table()
    {
        if (!has_permission('bpmn', '', 'view')) {
            access_denied('bpmn');
        }

        $this->app->get_table_data(module_views_path('bpmn', 'table'));
    }

    public function project_table()
    {
       
        if (!has_permission('bpmn', '', 'view')) {
            access_denied('bpmn');
        }

        $this->app->get_table_data(module_views_path('bpmn', 'project_table'));
    }
    public function grid()
    {
        echo $this->load->view('bpmn/grid', [], true);
    }

    /**
     * Task ajax request modal
     * @param  mixed $id
     * @return mixed
     */
    public function get_bpmn_data($id)
    {
        $bpmn = $this->bpmn_model->get($id);

        if (!$bpmn) {
            header('HTTP/1.0 404 Not Found');
            echo 'Bpmn not found';
            die();
        }
        $this->load->model('staff_model');

        $data['bpmn']  = $bpmn;
        $data['staff'] = $this->staff_model->get($data['bpmn']->staffid);
        $data['group'] = $this->bpmn_model->get_groups($data['bpmn']->bpmn_group_id);
        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // die();

        $html =  $this->load->view('view_bpmn_template', $data, true);
        echo $html;
    }

    public function bpmn_create($id = '')
    {
        if (!has_permission('bpmn', '', 'view')) {
            access_denied('bpmn');
        }
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('bpmn', '', 'create')) {
                    access_denied('bpmn');
                }
                $id = $this->bpmn_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('bpmn')));
                    redirect(admin_url('bpmn/bpmn_create/' . $id));
                    // redirect(admin_url('bpmn'));
                }
            } else {
                if (!has_permission('bpmn', '', 'edit')) {
                    access_denied('bpmn');
                }
                $success = $this->bpmn_model->update($this->input->post(), $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('bpmn')));
                }
                redirect(admin_url('bpmn/bpmn_create/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('bpmn_add_new', _l('bpmn'));
        } else {
            $data['bpmn']        = $this->bpmn_model->get($id);

            $title = _l('bpmn_edit', _l('bpmn'));
        }
        
        $data['bpmn_groups']    = $this->bpmn_model->get_groups();

        $data['projects']    = $this->bpmn_model->get_projects();
        $data['title']                 = $title;
        $data['staff']         = $this->staff_model->get('', ['active' => 1]);
        $data['contacts'] = $this->clients_model->get_contacts('',array('active'=>1));
        $this->app_scripts->add('comments.min','modules/bpmn/assets/plugins/jquery-comments/js/jquery-comments.min.js');
        $this->app_scripts->add('circle-progress-js','assets/plugins/jquery-circle-progress/circle-progress.min.js');
        $this->load->view('bpmn', $data);
    }


    /* bpmn function to handle preview views. */
    public function preview($id = 0)
    {
        if (!has_permission('bpmn', '', 'view')) {
            access_denied('bpmn');
        }
        $data['bpmn']        = $this->bpmn_model->get($id);

        if (!$data['bpmn']) {
            blank_page(_l('bpmn_not_found'), 'danger');
        }
        if($this->input->post('preview')){
            $post_data = $this->input->post();
            unset($post_data['preview']);
            unset($post_data['color']);
            if (!has_permission('bpmn', '', 'edit')) {
                    access_denied('bpmn');
                }

                $success = $this->bpmn_model->update($post_data, $id);
                if ($success && ($this->input->server('REQUEST_METHOD') === 'GET')) {
                    set_alert('success', _l('updated_successfully', _l('bpmn')));
                }
        }
        $title = _l('preview_bpmn');
        $data['title']                 = $title;

        $data['projects']    = $this->bpmn_model->get_projects();
        $data['staff']         = $this->staff_model->get('', ['active' => 1]);
        $data['contacts'] = $this->clients_model->get_contacts('',array('active'=>1));
        $data['bpmn_groups']    = $this->bpmn_model->get_groups();
        $data['bpmn_group']    = $this->bpmn_model->get_groups($data['bpmn']->bpmn_group_id);
        $this->app_scripts->add('comments.min','modules/bpmn/assets/plugins/jquery-comments/js/jquery-comments.min.js');
        $this->app_scripts->add('circle-progress-js','assets/plugins/jquery-circle-progress/circle-progress.min.js');
        $this->load->view('preview', $data);
    }


    /* Delete from database */
    public function delete($id)
    {
        if (!has_permission('bpmn', '', 'delete')) {
            access_denied('bpmn');
        }
        if (!$id) {
            redirect(admin_url('bpmn'));
        }
        $response = $this->bpmn_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('bpmn_deleted', _l('bpmn')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('bpmn_lowercase')));
        }
        redirect(admin_url('bpmn'));
    }

    public function switch_grid($set = 0, $manual = false)
    {
        if ($set == 1) {
            $set = 'false';
        } else {
            $set = 'true';
        }

        $this->session->set_userdata([
            'bpmn_grid_view' => $set,
        ]);
        if ($manual == false) {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }



     public function update_bpmn(){
        $post_data = $_POST;
       
        $id = $_POST['id'];
        if (!$id) {
            redirect(admin_url('bpmn'));
        }
        $success = $this->bpmn_model->update_by_ajax($post_data, $id);
        if ($success && ($this->input->server('REQUEST_METHOD') === 'POST')) {
            set_alert('success', _l('updated_successfully', _l('bpmn')));
        }
    }

    /*********bpmn group**********/
    public function groups(){
        if (!is_admin()) {
            access_denied('bpmn');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('bpmn', 'admin/groups_table'));
        }
        $data['title'] = _l('bpmn_group');
        $this->load->view('bpmn/admin/groups_manage', $data);
    }

    public function group()
    {
        if (!is_admin() && get_option('staff_members_create_inline_bpmn_group') == '0') {
            access_denied('bpmn');
        }

        if ($this->input->post()) {
            if (!$this->input->post('id')) {
                $id = $this->bpmn_model->add_group($this->input->post());
               if ($id) {
                 	  echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $id ? _l('added_successfully', _l('bpmn_group')) : '',
                    'id'      => $id,
                    'name'    => $this->input->post('name'),
                ]);
                    
                 	
                }

            } else {
                $data = $this->input->post();
                $id   = $data['id'];
                unset($data['id']);
                $success = $this->bpmn_model->update_group($data, $id);
                $message = _l('updated_successfully', _l('bpmn_group'));
                echo json_encode(['success' => $success, 'message' => $message]);
            }
            
        }
    }


    public function delete_group($id)
    {
        if (!$id) {
            redirect(admin_url('bpmn'));
        }
        $response = $this->bpmn_model->delete_group($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('bpmn_group')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('bpmn_group')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('bpmn_group')));
        }
        redirect(admin_url('bpmn/groups'));
    }
    public function check_frame()
    {
        $this->load->view('view_iframe');
    }


    public function sendEmail(){

        $companyname = !empty(get_option('companyname')) ? get_option('companyname') : 'Perfex';
        $staff_id = get_staff_user_id();
        $staff    = $this->staff_model->get($_POST['from']);
        $hash    = $this->bpmn_model->get($_POST['bpmn_id']);
       
        $contact  = $this->clients_model->get_contact($_POST['eml'],array('active'=>1));
        // $pdfdoc   = $_POST['fileDataURI'];   
        $from     = $_POST['from'];
        $subject  = $_POST['subject'];
        $subject  = $_POST['subject'];
        $content  = str_replace("{crmcompanyname}",$companyname,$_POST['content']);

          $eml  = $contact->email;      
        // $eml  = 'mohdnadeemzonv@gmail.com';        
        // $b64file        = trim( str_replace( 'data:application/pdf;base64,', '', $pdfdoc ) );
        // $b64file        = str_replace( ' ', '+', $b64file );
        // $decoded_pdf    = base64_decode( $b64file );     

        $mail = new PHPMailer;
        $mail->setFrom($staff->email, $companyname );
        $mail->addAddress($eml);
        $url = '<br><br>View: <a href="'.site_url('/bpmn/view_bpmn/bpmn/' . $hash->id . '/' .  $hash->hash).'">'.$hash->title.' Bpmn</a>';
        // $mail->addAddress( $to);
        $mail->Subject  = $subject;
        $mail->Body     = $content.$url;
        // $mail->addStringAttachment($decoded_pdf, "nalog.pdf");       
        $mail->isHTML( true );
        if($mail->send()){
             set_alert('success', _l('bpmn_email_send', _l('bpmn')));
             echo "true";
         }

    }
    /******** discussion comments *********/
      public function get_discussion_comments($id, $type)
    {
      
        echo json_encode($this->bpmn_model->get_discussion_comments($id, $type));
    }

    public function add_discussion_comment($discussion_id, $type)
    {
        echo json_encode($this->bpmn_model->add_discussion_comment(
            $this->input->post(null, false),
            $discussion_id,
            $type
        ));
    }

    public function update_discussion_comment()
    {
        echo json_encode($this->bpmn_model->update_discussion_comment($this->input->post(null, false)));
    }
    public function update_discussion_comment_rating()
    {
        echo json_encode($this->bpmn_model->update_discussion_comment_rating($this->input->post(null, false)));
    }
    public function delete_discussion_comment($id)
    {
        echo json_encode($this->bpmn_model->delete_discussion_comment($id));
    }

   
}
