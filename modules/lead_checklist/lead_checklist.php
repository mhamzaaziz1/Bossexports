<?php

defined("BASEPATH") or exit("No direct script access allowed");

/*
Module Name: Lead Checklist
Description: Create a workflow for your leads by creating checlists
Author: Halil
Author URI: https://codecanyon.net/user/halilaltndg/portfolio
Version: 1.0.0
*/



define("LEAD_CHECKLIST_MODULE_NAME", "lead_checklist");


register_language_files(LEAD_CHECKLIST_MODULE_NAME, [LEAD_CHECKLIST_MODULE_NAME]);

$CI = &get_instance();

$CI->load->helper(LEAD_CHECKLIST_MODULE_NAME . '/lead_checklist');



hooks()->add_action("admin_init", function (){

    $capabilities = [];

    $capabilities["capabilities"] = [

        "lead_checklist"             => _l('lead_checklist_permission') ,

    ];

    register_staff_capabilities("lead_checklist", $capabilities , _l('lead_checklist_permission') );

});

hooks()->add_action("admin_init", function (){

    if ( staff_can( 'lead_checklist' , 'lead_checklist' ) )
    {

        $CI = &get_instance();

        $CI->app_menu->add_setup_children_item('leads', [

            'slug'     => 'lead-checklist',

            'name'     => _l('lead_checklist_definition'),

            'href'     => admin_url('lead_checklist/checklist/definition'),

            'position' => 17,

            'badge'    => [],

        ]);

    }

});


hooks()->add_action("app_admin_footer", function (){

    if ( staff_can( 'lead_checklist' , 'lead_checklist' )  )
    {
        echo "
                <script src='" . base_url("modules/".LEAD_CHECKLIST_MODULE_NAME."/assets/lead_checklist.js?v=1") ."'></script> 
                
                <style>
                     
                    .lead_checklists_table_list_process {
                        width: 70px;
                        height: 70px;
                        border-radius: 50%;
                        
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        position: relative;
                        font-family: Arial, sans-serif;
                        font-size: 13px;
                        color: #000;
                    }
                    
                    .lead_checklists_table_list_process::before {
                        content: '';
                        position: absolute;
                        width: 70%;
                        height: 70%;
                        background: #fff;
                        border-radius: 50%;
                    }
                    
                    .lead_checklists_table_list_process span {
                        position: absolute;
                    }
                    
                </style>
                
            ";
    }


});


hooks()->add_action("after_lead_lead_tabs",function( $lead = null ) {

    if ( staff_can( 'lead_checklist' , 'lead_checklist' )  )
    {

        lead_checklist_db_changes();

        $checklist_badge_text = '';

        if ( !empty( $lead->id ) )
        {

            $checklist_badge_total = total_rows( db_prefix().'lead_checklists' , [ 'lead_id' => $lead->id , 'COALESCE( complated_staff_id , 0 ) = 0' => null ] );

            if ( !empty( $checklist_badge_total ) )

                $checklist_badge_text = ' <span class="badge">'.$checklist_badge_total.'</span>';

        }

        echo '<li role="presentation">
                <a href="#lead_checklist_tab" aria-controls="gdpr" role="tab" data-toggle="tab">
                    '. _l('lead_checklist') .$checklist_badge_text.'
                </a>
            </li>';

    }


} , 1 );

hooks()->add_action("after_lead_tabs_content",function ( $lead = null ) {

    if ( staff_can( 'lead_checklist' , 'lead_checklist' )  )
    {

        $CI = &get_instance();

        echo $CI->load->view( 'lead_checklist/inc_lead_tab', [ 'lead' => $lead ], true);

    }

} );


hooks()->add_action('lead_created',function ( $lead_id = 0 ){

    $CI = &get_instance();

    if ( !empty( $lead_id ) && $CI->db->table_exists( db_prefix().'lead_checklist_templates' ) )
    {

        $templates = $CI->db->select('*')->from(db_prefix().'lead_checklist_templates')->where('add_auto',1)->get()->result();

        if ( !empty( $templates ) )
        {

            foreach ( $templates as $template )
            {

                $CI->db->insert(db_prefix().'lead_checklists' , [
                    'lead_id' => $lead_id ,
                    'checklist_text' => $template->checklist_text ,
                    'template_id' => $template->id ,
                    'added_staff_id' => get_staff_user_id() ,
                    'added_date' => date('Y-m-d H:i:s'),
               ]);

           }

       }

   }

});


hooks()->add_filter('leads_table_columns',function ( $table_data ){

    if ( staff_can( 'lead_checklist' , 'lead_checklist' )  )
    {

        $table_data[] = _l('lead_checklist');

    }

    return $table_data;

},33);



hooks()->add_filter('leads_table_sql_columns',function( $aColumns ) {

    if ( staff_can( 'lead_checklist' , 'lead_checklist' )  )
    {

        $aColumns[] = '2 as lead_checklist';

    }

    return $aColumns;

},33);




hooks()->add_filter('leads_table_row_data', function( $row , $aRow ) {

    $CI = &get_instance();

    if ( staff_can( 'lead_checklist' , 'lead_checklist' ) )
    {

        $value = ' ';

        if ( $CI->db->table_exists( db_prefix().'lead_checklist_templates' ) && !empty( $aRow['id'] ) )
        {

            $lead_id = $aRow['id'];

            $checklists = $CI->db->select('complated_staff_id')->from(db_prefix().'lead_checklists')->where('lead_id',$lead_id)->get()->result();

            if ( !empty( $checklists ) )
            {

                $total_checklists = count( $checklists );
                $total_completed = 0;

                foreach ( $checklists as $checklist )
                {

                    if ( !empty( $checklist->complated_staff_id ) )
                        $total_completed++;

                }

                $percent = 0;

                if ( $total_completed > 0 )
                    $percent = ceil( ( $total_completed / $total_checklists ) * 100 );

                $value = '  <div class="lead_checklists_table_list_process" style="background: conic-gradient( #4caf50 0% '.$percent.'%, #ccc '.$percent.'% 100% );"> <span>'.$percent.'%</span> </div>' ;

            }

        }



        $DT_RowId = '';
        $DT_RowClass = '';


        if (isset($row['DT_RowId']))
        {
            $DT_RowId = $row['DT_RowId'];
            unset($row['DT_RowId']);
        }

        if (isset($row['DT_RowClass']))
        {
            $DT_RowClass = $row['DT_RowClass'];
            unset($row['DT_RowClass']);
        }

        $row[] = $value;

        if ( $DT_RowId != '' )
            $row['DT_RowId'] = $DT_RowId;

        if ( $DT_RowClass != '' )
            $row['DT_RowClass'] = $DT_RowClass;


    }

    return $row;

},33,2);

