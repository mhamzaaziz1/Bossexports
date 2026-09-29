<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php

$isGridView = 0;

if ($this->session->has_userdata('bpmn_grid_view') && $this->session->userdata('bpmn_grid_view') == 'true') {

    $isGridView = 1;

}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="_filters _hidden_inputs hidden">
                        <?php
                        echo form_hidden('my_bpmn');
                        foreach($staffs as $staff){
                            echo form_hidden('staffid_'.$staff['staffid']);
                        }
                        foreach($groups as $group){
                            echo form_hidden('bpmn_group_id_'.$group['id']);
                        }
                        ?>
                    </div>
                    <div class="panel-body">
                        <div class="_buttons">
                            <div class="btn-group">
                            <?php if(has_permission('bpmn','','create')){ ?>
                                 <button type="button" class="btn btn-info pull-left display-block mright5 grid_view dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                             <?php echo _l('bpmn_create_new'); ?> <span class="caret"></span>
                             </button>
                             <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <a href="#" onclick="new_bpmn();return false;"><?php echo _l('bpmn_create_new'); ?></a>
                                </li>
                                <li>
                                    <a href="#" onclick="new_group();return false;"><?php echo _l('new_bpmn_group'); ?></a>
                                </li>
                               </ul>
                                 

                            <?php } ?>
                            </div>


                            <a href="<?php echo admin_url('bpmn/switch_grid/'.$switch_grid); ?>" class="btn btn-default hidden-xs">

                                <?php if($switch_grid == 1){ echo _l('bpmn_switch_to_list_view');}else{echo _l('bpmn_switch_to_grid_view');}; ?>

                            </a>

                            <div class="visible-xs">

                                <div class="clearfix"></div>

                            </div>

                        </div>

                        <div class="clearfix"></div>

                        <hr class="hr-panel-heading" />

                        <div class="clearfix mtop20"></div>

                        <div class="row" id="bpmn-table">

                            <?php if($isGridView ==0){ ?>

                            <div class="col-md-12">

                                <div class="row">

                                    <div class="col-md-12">

                                        <p class="bold"><?php echo _l('filter_by'); ?></p>

                                    </div>

                                    <?php if(has_permission('bpmn','','view')){ ?>

                                        <div class="col-md-3 bpmn-filter-column">

                                            <?php echo render_select('view_assigned',$staffs,array('staffid',array('firstname','lastname')),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('bpmn_staff')),array(),'no-mbot'); ?>

                                        </div>

                                    <?php } ?>

                                    <div class="col-md-3 bpmn-filter-column">

                                        <?php echo render_select('view_group',$groups,array('id',array('name')),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('bpmn_group')),array(),'no-mbot'); ?>

                                    </div>

                                </div>

                            </div>

                            <div class="clearfix"></div>

                            <hr class="hr-panel-heading" />

                            <?php } ?>

                            <div class="col-md-12">

                        <?php if($this->session->has_userdata('bpmn_grid_view') && $this->session->userdata('bpmn_grid_view') == 'true') { ?>

                            <div class="grid-tab" id="grid-tab">

                                <div class="row">

                                    <div id="bpmn-grid-view" class="container-fluid">



                                    </div>

                                </div>

                            </div>

                        <?php } else { ?>

                            <?php render_datatable(array(

                                _l('bpmn_title'),

                                _l('bpmn_desc'),

                                _l('bpmn_staff'),

                                _l('bpmn_group'),

                                _l('bpmn_created_at')

                            ),'bpmn', array('customizable-table'),

                              array(

                                  'id'=>'table-bpmn',

                                  'data-last-order-identifier'=>'bpmn',

                                  'data-default-order'=>get_table_last_order('bpmn'),

                              )); ?>

                        <?php } ?>

                        </div>

                        </div>



                        </div>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- bpmn Modal-->

<div class="modal fade bpmn-modal" id="bpmn-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">

    <div class="modal-dialog modal-lg">

        <div class="modal-content data">



        </div>

    </div>

</div>
<div class="modal fade bpmn-modal" id="bpmn_create" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-lg">
        <?php echo form_open_multipart(admin_url('bpmn/bpmn_create'),array('id'=>'bpmn-form')) ;?>
            <?php echo render_input('staffid','', get_staff_user_id(), 'hidden'); ?>
        <div class="modal-content data">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">
                    <span class="edit-title hide"><?php echo _l('bpmn'); ?></span>
                    <span class="add-title"><?php echo _l('bpmn'); ?></span>
                </h4>
            </div>
                <div class="panel-body">
                       
                        <hr>
                           
                        <?php echo render_input('title','Title','');


                        $selected = '';
                        if(is_admin() || get_option('staff_members_create_inline_bpmn_group') == '1'){
                            echo render_select_with_input_group('bpmn_group_id',$bpmn_groups,array('id','name'),'bpmn_groups',$selected,'<a href="#" onclick="new_group();return false;"><i class="fa fa-plus"></i></a>');
                        } else {
                            echo render_select('bpmn_group_id',$bpmn_groups,array('id','name'),'bpmn_groups',$selected);
                        }
                         ?>
                        <?php echo render_textarea('description','Description','',array('rows'=>4),array());

                        echo render_select_with_input_group('project_id',$projects,array('id','name'),'project_group');
                        ?>

                    </div>
                    <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info bpmn-btn" data-loading-text="Please wait..." data-autocomplete="off" data-form="#bpmn-form">Save</button>
            </div>
        </div>
        

       <?php echo form_close();?>
    </div>
</div>
<?php $this->load->view('bpmn/bpmn_group'); ?>
<?php init_tail(); ?>
<script src="<?php echo base_url();?>modules/bpmn/assets/js/mynjquery.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>modules/bpmn/assets/js/style.js"></script>


<script>

$("button.bpmn-btn").on('click', function (e) {
    console.log('click');
    if($('#title').val() == '' || $('#bpmn_group_id').val() == '' || $('#description').val() == ''){
        console.log('in');
        validate_bpmn_form();
    }else{
        console.log('out');
        $('#bpmn-form').submit();
    }
    
 })
function validate_bpmn_form(){
    appValidateForm($('#bpmn-form'), {
        title: 'required',
        bpmn_group_id: 'required',
        description : 'required',
    });
    $('#bpmn-form').submit();
}

var _lnth = 12;

$(function(){

    var TblServerParams = {

        "assigned": "[name='view_assigned']",

        "group": "[name='view_group']",

    };



    if(<?php echo $isGridView ?> == 0) {

        var tAPI = initDataTable('.table-bpmn', admin_url+'bpmn/table', [2, 3], [2, 3], TblServerParams,[4, 'desc']);



        $.each(TblServerParams, function(i, obj) {

            $('select' + obj).on('change', function() {

                $('table.table-bpmn').DataTable().ajax.reload()

                    .columns.adjust()

                    .responsive.recalc();

            });

        });



    }else{
         $('.grid_view').on('click',function(){
            console.log('asd');
           $('.btn-group').toggleClass('open');

        });
         $(document).ready(function(){

            $('.select-bpmn_group_id').on('click',function(){
                $('.select-bpmn_group_id .bootstrap-select').toggleClass('open');
                });
                $('.select-project_id').on('click',function(){
                    $('.select-project_id .bootstrap-select').toggleClass('open');
                });
        });

        loadGridView();



        $(document).off().on('click','a.paginate',function(e){

            e.preventDefault();

            console.log("$(this)", $(this).data('ci-pagination-page'))

            var pageno = $(this).data('ci-pagination-page');

            var formData = {

                search: $("input#search").val(),

                start: (pageno-1),

                length: _lnth,

                draw: 1

            }

            gridViewDataCall(formData, function (resposne) {

                $('div#grid-tab').html(resposne)

            })

        });

    }

});

function new_bpmn(){
    $('.btn-group').toggleClass('open');
    $('#bpmn_create').modal('show');
}

</script>

</body>

</html>

