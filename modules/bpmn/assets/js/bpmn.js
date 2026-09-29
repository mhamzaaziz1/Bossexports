"use strict";
var diagramUrl='';
function loadGridView() {
    var formData = {
        search: $("input#search").val(),
        start: 0,
        length: _lnth,
        draw: 1
    }
    gridViewDataCall(formData, function (resposne) {
        $('div#grid-tab').html(resposne);
        setTimeout(__renderGridViewbpmn, 900)
    })
}
function gridViewDataCall(formData, successFn, errorFn) {
    $.ajax({
        url:  admin_url + 'bpmn/grid/'+(formData.start+1),
        method: 'POST',
        data: formData,
        async: false,
        error: function (res, st, err) {
            console.log("error API", err)
        },
        beforeSend: function () {
        },
        complete: function () {
        },
        success: function (response) {
            if ($.isFunction(successFn)) {
                successFn.call(this, response);
            }
        }
    });
}

function __renderGridViewbpmn() {
      
     $('div[id^="map_"]').each(function(index) {    
        setTimeout('', 200)
        var mId= $(this).attr('id');
        var filename = $('textarea#m_'+mId).val();
        var mainurl = $('#mainurl').val();
        $('#ifrm_'+mId).attr('src', mainurl+'modules/bpmn/views/view_iframe.php?filename='+filename);
      
      
});
}

function init_bpmn_modal(id) {
    var $bpmnModal = $('#bpmn-modal');
    requestGet('bpmn/get_bpmn_data/' + id).done(function(response) {
        _task_append_html(response);
    }).fail(function(data) {
        alert_float('danger', data.responseText);
    });
    requestGet('bpmn/get_bpmn_data/' + id).done(function(response) {
        _task_append_html(response);
    }).fail(function(data) {
        alert_float('danger', data.responseText);
    });
}

