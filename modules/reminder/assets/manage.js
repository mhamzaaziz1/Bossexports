"use strict"; 
var _customer_id = $("#customer").val();
$(document).ready(function() {
    $('tr td:nth-child(1)').hide();
});
var reminderid;
$(function(){
   var reminder_ServerParams = {};
   $.each($('._hidden_inputs._filters input'),function(){
     reminder_ServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
 });
   var tAPI=  initDataTable('.table-reminder', admin_url+'reminder/table', ['undefined'], ['undefined'], reminder_ServerParams, [0, 'desc']);
   init_reminder();
   $('#small-table').removeClass('hide');
   $('.complete_data').on('change',function(){
    var $checked = $('input[name="show_complete"]').prop('checked');
    if($checked) {
      $('input[name="complete_reminder"]').val('0');
      $('.table-reminder').DataTable().ajax.reload().columns.adjust().responsive.recalc();;
  }else{
      $('input[name="complete_reminder"]').val('1');
      $('.table-reminder').DataTable().ajax.reload().columns.adjust().responsive.recalc();;
  }
});
   $(document).on('click', '#all_filter_search', function(e) {
    searchTableFilters('.table-reminder');
    filterAllData();
});
   $('#all_filter_reset').on('click', function(){
    resetFilters('.table-reminder');
    $.ajax({
      type: 'POST',
      data: { csrfName: csrfHash },
      url: admin_url+'reminder/reset_filters_session',
      success: function(res){
      }
  });
});
//Open modal
function getQueryStringValue (key) {  
    return decodeURIComponent(window.location.search.replace(new RegExp("^(?:.*[&\\?]" + encodeURIComponent(key).replace(/[\.\+\*]/g, "\\$&") + "(?:\\=([^&]*))?)?.*$", "i"), "$1"));  
}  
if(getQueryStringValue("add")!= 'undefined' && getQueryStringValue("add") == "true"){
    $('#reminderAddModal').modal('show');
}
});
// Init single proposal
function init_reminder(id) {
    load_small_table_item(id, '#diaries', 'reminderid', 'reminder/get_reminder_data_ajax', '.table-reminder');
    $('#small-table').toggleClass('hide');
}
$( "#reminderModal" ).remove();
function getmodal(id='')
{
    $( "#reminderModal" ).remove();
    $("#reminderModal").modal('show');
    $('body').append('<div class="dt-loader"></div>');
    $.post(admin_url + 'reminder/getreminderEditModal', {
        id: id
    }).done(function (response) {
        $('body').find('.dt-loader').remove();
        $("#reminderModalData").html(response);
        $(".reminder-modal").modal('show');
    });
}
$('body').on('change','#rel_type', function() {
  $('#rel_type').eq(1).prop('selected', true);
  $('#rel_id').eq(1).prop('selected', true);
  var rel_type = $(this).val() ? $(this).val() : 0;
  $.get(admin_url + 'reminder/get_related_doc/' + rel_type+'/'+_customer_id, function(response) {
      if(response){
        $('#rel_id').html(response);
        $('#rel_id').selectpicker('refresh');
    }
});
});
function validate_reminder_form(selector) {
    selector = typeof (selector) == 'undefined' ? '#reminder-form' : selector;
    appValidateForm($(selector), {
        date: 'required',
        customer: 'required',
        description: 'required',
        contact: 'required',
        assigned_to: 'required',
        rel_type: 'required',
    });
}
$(function(){
    validate_reminder_form();
});
var _rel_id = $('#rel_id'),_rel_type = $('#rel_type'),_rel_id_wrapper = $('#rel_id_wrapper'),data = {};
$('body').on('change','#customer', function() {
    _customer_id = $("#customer").val() ? $("#customer").val() : 0;
    $('select[name=rel_type]').val(1);
    $('.selectpicker').selectpicker('refresh')
    if(_customer_id != ''){
      $.get(admin_url + 'reminder/get_contact_data_values/' + _customer_id + '/customer', function(response) {
          $('.proposal_to_wrap').html(response.field_to);
          $('#contact').selectpicker('refresh');
          $('.proposal_to_wrap').removeClass('hide');
      }, 'json');
  }else{
    var rel_type = $("#rel_type").val();
    $.get(admin_url + 'reminder/get_related_doc/' + rel_type+'/'+_customer_id, function(response) {
       if(response){
        $('#rel_id').html(response);
        $('#rel_id').selectpicker('refresh');
    }
});
    $('.proposal_to_wrap').addClass('hide');
    $('#rel_type').eq(1).prop('selected', true);
    $('#assigned_to').eq(1).prop('selected', true);
    $('#rel_id').eq(1).prop('selected', true);
}
});