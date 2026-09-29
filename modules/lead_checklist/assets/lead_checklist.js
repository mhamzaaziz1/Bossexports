(function($) {
    "use strict";

    $(document).ready(function (){



    })

})(jQuery);


function lead_save_checklist( record_id )
{

    var $input_obj = $('#lead_checklist_value_'+record_id);

    if ( $input_obj.val() )
    {

        $.post(admin_url + "lead_checklist/checklist/save_checklist/"+checklist_lead_id, { record_id: 0 , value : $input_obj.val() } )
            .done(function (response) {

                response = JSON.parse(response);

                if( response.success )
                {

                    $input_obj.val('');

                    if ( response.content )
                    {
                        $('#table_lead_checklist').append( response.content );
                    }

                    $input_obj.focus();

                }

            });

    }
    else
        $input_obj.focus();


}



function lead_checklist_add_template()
{

    var $template_obj = $('#lead_checklist_template_id');

    if ( $template_obj.val() )
    {

        $.post(admin_url + "lead_checklist/checklist/add_template/"+checklist_lead_id, { template_id : $template_obj.val() } )
            .done(function (response) {

                response = JSON.parse(response);

                if( response.success )
                {

                    if ( response.content )
                    {

                        $('#table_lead_checklist').append( response.content );

                    }

                    $template_obj.selectpicker('val','');

                    $template_obj.selectpicker('refresh');

                }
                else
                    alert_float( 'danger' , response.message );


            });

    }


}


function lead_checklist_save_template( record_id )
{

    $.post(admin_url + "lead_checklist/checklist/save_template/"+checklist_lead_id, { record_id: record_id } )
        .done(function (response) {

            response = JSON.parse(response);

            if( response.success )
            {

                var $template_obj = $('#lead_checklist_template_id');

                $template_obj.find('option:first').after('<option value="'+response.id+'">'+response.name+'</option>');

                $template_obj.selectpicker('val',response.id);

                $template_obj.selectpicker('refresh');

                $('.checklist_template_'+record_id).remove();

                alert_float( 'success' , 'Successful' );

            }

        });

}

function lead_checklist_remove( record_id )
{

    if( confirm_delete() )
    {

        $.post(admin_url + "lead_checklist/checklist/remove_checklist/"+checklist_lead_id, { record_id: record_id } )
            .done(function (response) {

                response = JSON.parse(response);

                if( response.success )
                {

                    alert_float( 'success' , 'Successful' );

                    $('.checklist_tr_'+record_id).remove();

                }

            });

    }

}

function lead_checklist_status_change( record_id )
{

    $.post(admin_url + "lead_checklist/checklist/status_change/"+checklist_lead_id, { record_id: record_id , status : $('#lead_checklist_value_'+record_id).prop('checked') ? 1 : 0 } )
        .done(function (response) {

            response = JSON.parse(response);

            if( response.success )
            {
                alert_float( 'success' , 'Successful' );
                get_lead_checklist_item( record_id );
            }
            else
                alert_float( 'danger' , response.message );

        });

}




function lead_checklist_assigned_staff(staff_id, record_id) {

    $.post( admin_url + 'lead_checklist/checklist/assigned_staff/'+checklist_lead_id, {  assigned: staff_id, record_id: record_id } ).done(function (response) {

        response = JSON.parse(response);

        if( response.success )
        {
            alert_float( 'success' , 'Successful' );
            get_lead_checklist_item( record_id );
        }
        else
            alert_float( 'danger' , response.message );

    });

}

function get_lead_checklist_item( record_id )
{

    $.post(admin_url + "lead_checklist/checklist/get_checklist_content/"+checklist_lead_id, { record_id:record_id } )
        .done(function (response) {

            response = JSON.parse(response);

            if( response.success )
            {

                if ( response.content )
                {
                    $('.checklist_tr_'+record_id).replaceWith( response.content );
                }

            }

        });


}

function toggle_lead_checklist_content( list_id , a_tag )
{

    // <i class="fa fa-arrow-down fa-lg"></i>
    if ( $(a_tag).find('i').hasClass('fa-arrow-down') ) // show detail
    {

        $('.checklist_toggle_'+list_id).show();

        $(a_tag).find('i').removeClass('fa-arrow-down').addClass('fa-arrow-up');

    }
    else
    {

        $('.checklist_toggle_'+list_id).hide();

        $(a_tag).find('i').addClass('fa-arrow-down').removeClass('fa-arrow-up');

    }


}



function lead_checklist_recalculate_progress() {

    var total_finished = $('.lead_checklists_box:checked').length;

    var total_checklist_items = $('.lead_checklists_box').length;

    var percent = 0, lead_progress_bar = $(".lead-checklist-progress-bar");


    if (total_checklist_items > 1) {

        lead_progress_bar.parents(".progress").removeClass("hide");

        percent = (total_finished * 100) / total_checklist_items;

        if (percent == 0) {

            $(".lead-checklist-progress-bar").addClass("text-dark");

        } else {

            $(".lead-checklist-progress-bar").removeClass("text-dark");

        }

    }

    lead_progress_bar.css("width", percent.toFixed(2) + "%");

    lead_progress_bar.text(percent.toFixed(2) + "%");



    if (total_finished > 0) {

        $(".chk-toggle-buttons").removeClass("hide");

    } else {

        $(".chk-toggle-buttons").addClass("hide");

    }



    if (percent == 100) {

        lead_progress_bar

            .removeClass("progress-bar-default")

            .addClass("progress-bar-success");

    } else {

        lead_progress_bar

            .removeClass("progress-bar-success")

            .addClass("progress-bar-default");

    }

}
