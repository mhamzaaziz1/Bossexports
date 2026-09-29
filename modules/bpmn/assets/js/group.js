"use strict";
window.addEventListener('load',function(){
    appValidateForm($('#bpmn-group-form'),{name:'required'},manage_groups);
    $('#bpmn-group-modal').on('hidden.bs.modal', function(event) {
        $('#additional').html('');
        $('#bpmn-group-modal input[name="name"]').val('');
        $('#bpmn-group textarea').val('');
        $('.add-title').removeClass('hide');
        $('.edit-title').removeClass('hide');
    });
    $('#bpmn-group-modal').on('show.bs.modal', function(e) {
        var type_id = $('#bpmn-group-modal').find('input[type="hidden"][name="id"]').val();
        if (typeof(type_id) !== 'undefined') {
            $('#bpmn-group-modal .add-title').addClass('hide');
            $('#bpmn-group-modal .edit-title').removeClass('hide');
        }else{
            $('#bpmn-group-modal .add-title').removeClass('hide');
            $('#bpmn-group-modal .edit-title').addClass('hide');
        }
    });
    });
    function manage_groups(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);
            if(response.success == true){
                alert_float('success',response.message);
                if($('body').hasClass('bpmn') && typeof(response.id) != 'undefined') {
                    var category = $('#bpmn_group_id');
                    category.find('option:first').after('<option value="'+response.id+'">'+response.name+'</option>');
                    category.selectpicker('val',response.id);
                    category.selectpicker('refresh');
                }
            }
            if($.fn.DataTable.isDataTable('.table-bpmn-group')){
                $('.table-bpmn-group').DataTable().ajax.reload();
            }

            $('#bpmn-group-modal').modal('hide');
        });
        return false;
    }

    function new_group(){
        $('#bpmn-group-modal').modal('show');
        $('.edit-title').addClass('hide');
    }
    function edit_group(invoker,id){
        var name = $(invoker).data('name');
        var description = $(invoker).data('description');
        $('#additional').append(hidden_input('id',id));
        $('#bpmn-group-modal input[name="name"]').val(name);
        $('#bpmn-group-modal textarea').val(description);
        $('#bpmn-group-modal').modal('show');
        $('.add-title').addClass('hide');
    }
   