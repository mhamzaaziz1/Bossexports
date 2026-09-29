<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php

if ( !empty( $lead->id ) )
{

    $checklist_lead_id =   $lead->id  ;

    echo "<script> var checklist_lead_id = $checklist_lead_id; </script>";

    $checklists = get_lead_checklists( $checklist_lead_id );

    $checklists_templates = get_lead_checklist_templates();


    if ( !empty( $checklists ) )
    {

        $task_staff_members = $this->db->select('staffid, lastname, firstname')->from(db_prefix().'staff')->where('active',1)->get()->result_array();

    }

    ?>

    <div role="tabpanel" class="tab-pane" id="lead_checklist_tab">

        <div class="clearfix"></div>

        <div class="row">

            <div class="col-md-12">

                <div class="form-group">

                    <div class="input-group">

                        <input class="form-control" id="lead_checklist_value_0" placeholder="<?php echo _l('lead_checklist_add_checklist')?>" autocomplete="off" >

                        <div class="input-group-addon">

                            <i class="fa fa-save"></i>
                            <a style="cursor: pointer" onclick="lead_save_checklist(  0 )"><?php echo _l('lead_checklist_save')?></a>

                        </div>

                    </div>

                </div>

                <?php if( !empty( $checklists_templates ) ) { ?>

                    <div class="form-group">
                        <select data-live-search="true" id="lead_checklist_template_id" onchange="lead_checklist_add_template()" class="selectpicker" data-actions-box="true" data-width="100%" data-none-selected-text="<?php echo _l('lead_checklist_templates')?>">
                            <option></option>
                            <?php foreach ( $checklists_templates as $checklists_template ) {
                                echo "<option value='$checklists_template->id'>$checklists_template->checklist_text</option>";
                            } ?>
                        </select>
                    </div>

                <?php } ?>


                <div class="progress mtop15 no-mbot hide">

                    <div class="progress-bar not-dynamic progress-bar-default lead-checklist-progress-bar" role="progressbar" aria-valuenow="40"

                         aria-valuemin="0" aria-valuemax="100" style="width:0%">

                    </div>

                </div>


                <table class="table table-hover table-striped" id="table_lead_checklist">
                    <thead>
                        <tr>
                            <th><?php echo _l('lead_checklist')?></th>
                            <th style="width: 150px;"><?php echo _l('options')?></th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php if ( !empty( $checklists ) ):

                        foreach ( $checklists as $checklist ) :

                            $this->load->view('inc_checklist' , [ 'checklist' => $checklist , 'task_staff_members' => $task_staff_members ] );

                        endforeach;

                    endif; ?>

                    </tbody>
                </table>

            </div>

        </div>


        <script>

            $(document).ready(function (){


            })


        </script>

    </div>

<?php } ?>
