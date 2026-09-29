<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Checklist extends AdminController
{

    public function __construct()
    {

        parent::__construct();

    }

    function save_checklist( $lead_id = 0 )
    {

        if ( $this->input->is_ajax_request() && !empty( $lead_id ) )
        {

            $record_id = $this->input->post('record_id');


            $data = [
                'lead_id' => $lead_id ,
                'checklist_text' => $this->input->post('value') ,
            ];

            if ( empty( $record_id ) )
            {

                $data['added_staff_id'] = get_staff_user_id();
                $data['added_date'] = date('Y-m-d H:i:s');

                $this->db->insert(db_prefix().'lead_checklists' , $data );

                $record_id = $this->db->insert_id();

            }
            else
                $this->db->where('id',$record_id)->update(db_prefix().'lead_checklists' , $data );


            $content = $this->get_content( $lead_id , $record_id );

            echo json_encode( [ 'success' => true , 'record_id' => $record_id , 'content' => $content ] );

        }

    }


    /**
     * saving checklist from template
     */
    public function add_template( $lead_id = 0 )
    {

        if ( $this->input->is_ajax_request() && !empty( $lead_id ) )
        {

            $template_id = $this->input->post('template_id');

            $template = get_lead_checklist_templates( $template_id );

            if ( !empty( $template->checklist_text ) )
            {

                $data = [
                    'lead_id' => $lead_id ,
                    'template_id' => $template->id ,
                    'checklist_text' => $template->checklist_text ,
                    'added_staff_id' => get_staff_user_id() ,
                    'added_date' => date('Y-m-d H:i:s')
                ];


                $this->db->insert(db_prefix().'lead_checklists' , $data );

                $record_id = $this->db->insert_id();

                $content = $this->get_content( $lead_id , $record_id );

                echo json_encode( [ 'success' => true , 'record_id' => $record_id , 'content' => $content ] );


            }
            else
                echo json_encode( [ 'success' => false , 'message' => 'Record not found' ] );


        }


    }



    public function remove_checklist( $lead_id = 0 )
    {

        if ( $this->input->is_ajax_request() )
        {

            $record_id = $this->input->post('record_id');

            $detail = get_lead_checklists( $lead_id ,$record_id );

            $this->db->where('id',$record_id)->where('lead_id',$lead_id)->delete(db_prefix().'lead_checklists' );

            log_activity( "Lead checklist removed. [ Lead : $lead_id checklist : $detail->checklist_text  ]" );

            echo json_encode( [ 'success' => true ] );

        }

    }


    public function save_template( $lead_id = 0 )
    {

        if ( $this->input->is_ajax_request() )
        {

            $record_id = $this->input->post('record_id');

            $detail = get_lead_checklists( $lead_id ,$record_id );

            $checklist_text = '';

            if ( !empty( $detail->checklist_text ) )
            {

                $checklist_text = $detail->checklist_text;

                if ( total_rows(db_prefix() . 'lead_checklist_templates', [ 'checklist_text' => $detail->checklist_text ] ) == 0 )

                    $this->db->insert(db_prefix().'lead_checklist_templates' , [ 'checklist_text' => $detail->checklist_text ] );

            }

            echo json_encode( [ 'success' => true , 'name' =>  $checklist_text ] );

        }

    }


    public function status_change( $lead_id = 0 )
    {

        if ( $this->input->is_ajax_request() && !empty( $lead_id ) )
        {

            $status = $this->input->post('status');

            $record_id = $this->input->post('record_id');

            $detail = get_lead_checklists( $lead_id , $record_id );

            if ( !empty( $detail ) )
            {

                if ( empty( $status ) ) // uncheck
                    $data = [
                        'complated_staff_id' => 0 ,
                        'complated_date' => null,
                    ];
                else // check
                    $data = [
                        'complated_staff_id' => get_staff_user_id() ,
                        'complated_date' => date('Y-m-d H:i:s') ,
                    ];

                $this->db->where('id',$record_id)->where('lead_id',$lead_id)->update(db_prefix().'lead_checklists' , $data );

                log_activity( "lead checklist status changed. [ Lead ID : $lead_id checklist : $detail->checklist_text status : $status  ]" );

                echo json_encode( [ 'success' => true ] );

            }
            else
                echo json_encode( [ 'success' => false , 'message' => 'Record not found' ] );

        }

    }


    public function assigned_staff( $lead_id = 0 )
    {

        if ( $this->input->is_ajax_request() && !empty( $lead_id ) )
        {

            $assigned = $this->input->post('assigned');

            $record_id = $this->input->post('record_id');

            $detail = get_lead_checklists( $lead_id , $record_id );

            if ( !empty( $detail ) )
            {

                $data = [
                    'assigned_staff_id' => $assigned ,
                    'assigned_date'     => date('Y-m-d H:i:s') ,
                ];

                $this->db->where('id',$record_id)->where('lead_id',$lead_id)->update(db_prefix().'lead_checklists' , $data );



                /**
                 * Send notification to staff
                 */
                add_notification([

                    'description'     => 'lead_checklist_notification',

                    'touserid'        => $assigned,

                    'fromcompany'     => 1,

                    'fromuserid'      => get_staff_user_id(),

                    'link'            => '#leadid='.$lead_id ,

                    'additional_data' => '',

                ]);




                echo json_encode( [ 'success' => true ] );

            }
            else
                echo json_encode( [ 'success' => false , 'message' => 'Record not found' ] );

        }

    }

    public function get_checklist_content( $lead_id = 0 )
    {

        if ( $this->input->is_ajax_request() && !empty( $lead_id ) )
        {

            $record_id = $this->input->post('record_id');

            $content = $this->get_content( $lead_id , $record_id );

            echo json_encode( [ 'success' => true , 'record_id' => $record_id , 'content' => $content ] );

        }

    }

    private function get_content( $lead_id = 0, $record_id = 0 )
    {

        $content = '';

        if ( empty( $lead_id ) || empty( $record_id ) )
            return $content;


        $checklist = get_lead_checklists( $lead_id , $record_id );

        if ( !empty( $checklist ) )
        {

            $task_staff_members = $this->db->select('staffid, lastname, firstname')->from(db_prefix().'staff')->where('active',1)->get()->result_array();

            $content = $this->load->view('inc_checklist' , [ 'checklist' => $checklist , 'task_staff_members' => $task_staff_members ] , true );

        }

        return $content;

    }


    public function definition()
    {

        lead_checklist_db_changes();

        $data["title"] = _l('lead_checklist_definition');

        $this->load->view('v_definition',$data);

    }

    public function definition_lists(){

        if( $this->input->is_ajax_request() )
        {

            $sTable = db_prefix().'lead_checklist_templates';

            $select = [

                'id' ,

                'checklist_text' ,

                'add_auto' ,

            ];

            $where = [];


            $join = [ ];


            $sIndexColumn = 'id';

            $result = data_tables_init($select, $sIndexColumn, $sTable, $join, $where , []);

            $output  = $result['output'];

            $rResult = $result['rResult'];

            foreach ($rResult as $aRow){

                $row = [];

                $href_delete = admin_url("lead_checklist/checklist/delete_template/" . $aRow['id']);

                $numberOutput = '<div class="row-options" data-id=" '.$aRow['id'].' ">';

                $numberOutput .= '<a href="#" onclick="fnc_checklist_template_detail( '. $aRow['id'] .'   ); return false;" >' . _l('edit') . '</a>';

                $numberOutput .= ' | <a href="' . $href_delete . '" class="_delete text-danger" >' . _l('delete') . '</a>';

                $numberOutput .= '</div>';

                $row[] =  $aRow['id'];

                $row[] = $aRow['checklist_text'].$numberOutput;



                $toggleActive = '<div class="onoffswitch" data-toggle="tooltip" data-title="Auto Add">

                                    <input type="checkbox" data-switch-url="' . admin_url() . 'lead_checklist/checklist/change_status" name="onoffswitch" 
                                            class="onoffswitch-checkbox" id="snack_' . $aRow['id'] . '" 
                                            data-id="' . $aRow['id'] . '" ' . ($aRow['add_auto'] == 1 ? 'checked' : '') . '>
                                
                                    <label class="onoffswitch-label" for="snack_' . $aRow['id'] . '"></label>
                                
                                </div>';



                // For exporting

                $toggleActive .= '<span class="hide">' . ($aRow['add_auto'] == 1 ? _l('yes') : _l('no')) . '</span>';


                $row[] = $toggleActive;

                $output['aaData'][] = $row;
            }

            echo json_encode($output);

            die;

        }

    }

    public function delete_template( $id )
    {

        $this->db->where('id',$id)->delete(db_prefix().'lead_checklist_templates');

        set_alert('success' , _l('deleted', _l('lead_checklist') ) );

        redirect( admin_url('lead_checklist/checklist/definition') );

    }

    public function change_status( $id , $status )
    {

        if ($this->input->is_ajax_request()) {

            $this->db->where('id', $id);

            $this->db->update(db_prefix().'lead_checklist_templates', [

                'add_auto' => $status,

            ]);

            if ($this->db->affected_rows() > 0)
                return true;
            else
                return false;

        }

    }

    public function definition_detail( $record_id )
    {

        $data['data'] = $this->db->select('*')->from(db_prefix().'lead_checklist_templates')->where('id',$record_id)->get()->row();

        if ( empty( $data['data'] ) )
        {

            $data['data'] = new stdClass();

            $data['data']->id = 0;
            $data['data']->checklist_text = '';
            $data['data']->add_auto = 0;

        }

        $this->load->view('definition_detail' , $data );

    }

    public function definition_save( $record_id = 0 )
    {

        if ( $this->input->post() )
        {

            $post_data = $this->input->post();

            if ( empty( $record_id ) )
            {

                $this->db->insert(db_prefix().'lead_checklist_templates',$post_data);

                set_alert('success' , _l('added_successfully', _l('lead_checklist') ) );
            }
            else
            {

                $this->db->where('id',$record_id)->update(db_prefix().'lead_checklist_templates',$post_data);

                set_alert('success' , _l('updated_successfully', _l('lead_checklist') ) );

            }

            redirect( admin_url('lead_checklist/checklist/definition') );

        }

    }

    
}
