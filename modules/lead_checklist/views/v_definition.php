<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">

    <div class="content">

        <div class="row">

            <div class="col-md-12">
                <a class="btn btn-primary mbot10" onclick="fnc_checklist_template_detail(0); return false; " > <?php echo _l('lead_checklist_add_checklist')?> </a>
            </div>

            <div class="col-md-12">

                <div class="panel_s">

                    <div class="panel-heading">

                        <div>

                            <strong style="font-size: 20px"> <?php echo _l('lead_checklist_definition')?> </strong>

                        </div>

                    </div>


                    <div class="panel-body">

                        <div class="table-responsive ">

                            <table class="table table-lead-templates">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('id')?></th>
                                        <th><?php echo _l('name')?></th>
                                        <th><?php echo _l('lead_checklist_add_auto')?></th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<div class="modal fade" id="lead_checklist_definition_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">

    <div class="modal-dialog" role="document">

        <div class="modal-content" id="lead_checklist_definition_content">


        </div>

    </div>

</div>


<?php init_tail(); ?>

<script>

    $(document).ready(function (){

        $('body').removeClass('checklist ');

        initDataTable('.table-lead-templates', admin_url + 'lead_checklist/checklist/definition_lists' );

    });

    function fnc_checklist_template_detail( record_id )
    {

        requestGet(admin_url+"lead_checklist/checklist/definition_detail/"+record_id).done(function (response){

            $('#lead_checklist_definition_content').html(response);

            $('#lead_checklist_definition_modal').modal();

        });



    }

</script>

</body>



</html>
