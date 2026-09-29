<?php
defined('BASEPATH') or exit('No direct script access allowed');
 
$isGridView = 0;

?>


 <div class="row" id="bpmn-table">
<div class="col-md-12">
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
                        </div>
                        </div>
                        <?php init_tail(); ?>
                        
 <script>
    var _lnth = 12;

$(function(){
var id = $('input[name=project_id]').val();
    var TblServerParams = {

        "assigned": "[name='view_assigned']",

        "group": "[name='view_group']",
        "project_id": id,

    };
    var tAPI = initDataTable('.table-bpmn', admin_url+'bpmn/project_table?project_id='+id, [2, 3], [2, 3], TblServerParams,[4, 'desc']);



        $.each(TblServerParams, function(i, obj) {

            $('select' + obj).on('change', function() {

                $('table.table-bpmn').DataTable().ajax.reload()

                    .columns.adjust()

                    .responsive.recalc();

            });

        });
    });
</script>