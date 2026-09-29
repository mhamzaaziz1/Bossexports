"use strict";

var xero_progress_bar = '<div class="row mtop10 hide"><div class="col-md-12" id="process"><div class="progress progress-bar-mini"> <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:20%"></div></div></div></div>';



//==========================================================
// Chart of Accounts Table
//==========================================================
var table_ledger = $('#xero_ledgertable');
appDataTableInline(table_ledger, {
    supportsButtons: true,
    supportsLoading: true,
    autoWidth: false,
    paging: false,
    ordering: false,
    scrollResponsive: app.options.scroll_responsive_tables,
});

$(function(){
    $('#xero_chat_of_acc').find('.dt-buttons').append('<button class="btn btn-default" tabindex="0" aria-controls="xero_ledgertable" type="button" onclick="location.reload();"><i class="fa fa-refresh"></i> <span>Reload</span></button>');
});

$('body').on('click', '#synch_chart_of_accounts', function() {
    var btn_synch = $(this);
    var btn_label = btn_synch.html();
    btn_synch.prop('disabled', true);
    btn_synch.html('<i class="fa fa-refresh fa-spin"></i> Loading...');  

    var $xero_synch_area = btn_synch.data('xero_synch_area');  

    //ajax call
    ajax_synch_chart_of_accounts(btn_synch, btn_label, $xero_synch_area);
});

function ajax_synch_chart_of_accounts(btn_synch, btn_label, xero_synch_area){
    if(xero_synch_area){
     btn_synch.parent().append(xero_progress_bar);
 }

 $.get(admin_url + 'xero/synchallaccounts', function(response) {
    if (response.success == true) {
        $('#process').parent().removeClass('hide');
        var percentage = 0;
        var timer = setInterval(function() {
            percentage = percentage + 20;
            progress_bar_process(percentage, timer, response.message);
        }, 1000);

        setTimeout(function() {
            btn_synch.html(btn_label);
            btn_synch.prop('disabled', false);
            if(xero_synch_area){
                $('#process').parent().remove();
            }
            if ($.fn.DataTable.isDataTable('#xero_ledgertable')) {
                    //$('#xero_ledgertable').DataTable().ajax.reload();
                }
            }, 5000);

    } else {
        alert_float('warning', response.message);
        btn_synch.html(btn_label);
        btn_synch.prop('disabled', false);
        if(xero_synch_area){
            $('#process').parent().remove();
        }
    }

}, 'json');
}

//==========================================================
//  TAXES
//==========================================================


    $(function() {
        if ($('body').hasClass('taxes')) {
            add_xero_comp_taxes();

            $('body').find('.panel-body').prepend('<div class="alert alert-info">Now that you have the Xero® Online module active, make sure that all Tax Rates are synchronized. <br>Note: <ul><li>1. <div class="badge bg-info">Xero</div> Means it has already been synchronized.</li></ul></div>');
            $('body').find('.panel-body ._buttons').addClass('row').append('<button type="button" class="btn btn-info pull-right" id="synch_taxes"><i class="fa fa-refresh"></i> Xero® Online</button>');
            var originalContents = $('body').find('.panel-body ._buttons').html();
            $('body').find('.panel-body ._buttons').empty().html('<div class="col-md-12">' + originalContents + '</div>');
            $(xero_progress_bar).insertAfter('.panel-body ._buttons');
        }
    });


    $('body').on('click', '#synch_taxes', function() {
        var btn_synch = $(this);
        var btn_label = btn_synch.html();
        btn_synch.prop('disabled', true);
        btn_synch.html('<i class="fa fa-refresh fa-spin"></i> Loading...');  

        var $xero_synch_area = btn_synch.data('xero_synch_area');  

        //ajax call
        ajax_synch_taxes(btn_synch, btn_label, $xero_synch_area);
    });

    function ajax_synch_taxes(btn_synch, btn_label, xero_synch_area){
            if(xero_synch_area){
             btn_synch.parent().append(xero_progress_bar);
         }

        $.get(admin_url + 'xero/synchalltaxes', function(response) {
            if (response.success == true) {
                $('#process').parent().removeClass('hide');
                var percentage = 0;
                var timer = setInterval(function() {
                    percentage = percentage + 20;
                    progress_bar_process(percentage, timer, response.message);
                }, 1000);

                setTimeout(function() {
                    btn_synch.html(btn_label);
                    btn_synch.prop('disabled', false);
                    if(xero_synch_area){
                        $('#process').parent().remove();
                    }
                    if ($.fn.DataTable.isDataTable('.table-taxes')) {
                        $('.table-taxes').DataTable().ajax.reload();
                        add_xero_comp_taxes();
                    }
                }, 5000);

            } else {
                alert_float('warning', response.message);
                btn_synch.html(btn_label);
                btn_synch.prop('disabled', false);
                if(xero_synch_area){
                    $('#process').parent().remove();
                }
            }

        }, 'json');
    }


 function add_xero_comp_taxes() {
    $.get(admin_url + 'xero/get_xero_taxes', function(response) {
        if (response.length > 0) {
            var xero_tax_r = [];
            jQuery.each(response, function(i, val) {
                xero_tax_r.push(val['id']);
            });
                //console.log(xero_tax_r);
                var table = $('.table-taxes').DataTable();
                if ($.fn.DataTable.isDataTable('.table-taxes')) {
                    table.on('order.dt search.dt', function() {
                        table.column(0, {
                            search: 'applied',
                            order: 'applied'
                        }).nodes().each(function(cell, i) {

                            if ($.inArray(cell.innerHTML, xero_tax_r) >= 0) {
                                cell.innerHTML = cell.innerHTML + ' <div class="badge-xs badge bg-info">Xero</div>';
                            }
                        });
                    }).draw()
                }

            }
        }, 'json');
 }



//==========================================================
//  CUSTOMER
//==========================================================
    $(function() {
        if ($('body').hasClass('clients') && !$('body').hasClass('customer-profile') && !$('body').hasClass('groups')) {
            add_xero_comp_clients();

            $('body').find('.panel-body').prepend('<div class="alert alert-info">Now that you have the Xero® Online module active. Note: <ul><li>1. <div class="badge bg-info">Xero</div> Means it has already been synchronized.</li></ul></div>');
            $('body').find('.panel-body ._buttons').addClass('row').append('<button type="button" class="btn btn-info pull-right mright10" id="synch_clients"><i class="fa fa-refresh"></i> Xero® Online</button>');
            var originalContents = $('body').find('.panel-body ._buttons').html();
            $('body').find('.panel-body ._buttons').empty().html('<div class="col-md-12">' + originalContents + '</div>');
            $(xero_progress_bar).insertAfter('.panel-body ._buttons');
        }
    });

    $('body').on('click', '#synch_clients', function() {
        var btn_synch = $(this);
        var btn_label = btn_synch.html();
        btn_synch.prop('disabled', true);
        btn_synch.html('<i class="fa fa-refresh fa-spin"></i> Loading...');

        var $xero_synch_area = btn_synch.data('xero_synch_area');

        //ajax call
        ajax_synch_clients(btn_synch, btn_label, $xero_synch_area);

    });

    function ajax_synch_clients(btn_synch, btn_label, xero_synch_area){
        if(xero_synch_area){
         btn_synch.parent().append(xero_progress_bar);
     }

     $.get(admin_url + 'xero/synchallclients', function(response) {
        console.log(response);
        if (response.success == true) {
            $('#process').parent().removeClass('hide');
            var percentage = 0;
            var timer = setInterval(function() {
                percentage = percentage + 20;
                progress_bar_process(percentage, timer, response.message);
            }, 1000);

            setTimeout(function() {
                btn_synch.html(btn_label);
                btn_synch.prop('disabled', false);
                if(xero_synch_area){
                    $('#process').parent().remove();
                }

                if ($.fn.DataTable.isDataTable('.table-clients')) {
                    $('.table-clients').DataTable().ajax.reload();
                    add_xero_comp_clients();
                }
            }, 5000);

        } else {
            alert_float('warning', response.message);
            btn_synch.html(btn_label);
            btn_synch.prop('disabled', false);
            if(xero_synch_area){
                $('#process').parent().remove();
            }

        }

    }, 'json');
    }


    function add_xero_comp_clients() {
        $.get(admin_url + 'xero/get_xero_clients/userid', function(response) {
            if (response.length > 0) {
                var xero_client_r = [];
                jQuery.each(response, function(i, val) {
                    xero_client_r.push(val['userid']);
                });
                //console.log(xero_client_r);
                var table = $('.table-clients').DataTable();
                if ($.fn.DataTable.isDataTable('.table-clients')) {
                    table.on('order.dt search.dt', function() {
                        table.column(1, {
                            search: 'applied',
                            order: 'applied'
                        }).nodes().each(function(cell, i) {

                            if ($.inArray(cell.innerHTML, xero_client_r) >= 0) {
                                cell.innerHTML = cell.innerHTML + ' <div class="badge-xs badge bg-info">Xero</div>';
                            }
                        });
                    }).draw()
                }

            }
        }, 'json');
    }

//==========================================================
//  CUSTOMER GROUPS
//==========================================================

    $("body").on('click', '.table-customer-groups ._delete', function(e) {
        var id = $(this).parent().find('a:first-child').data('id');
        if (confirm_delete()) {
            $.get(admin_url + 'xero/deleteCustomerGroup/'+id, function(response) {
             if (response.success) {
               return true;
           }
       }, 'json');
            return true; 
        }
        return false;
    });

    $(function() {
        if ($('body').hasClass('clients') && $('body').hasClass('groups')) {
            add_xero_comp_client_groups();

            $('body').find('.panel-body').prepend('<div class="alert alert-info">Now that you have the Xero® Online module active. Note: <ul><li>1. <div class="badge bg-info">Xero</div> Means it has already been synchronized.</li></ul></div>');
            $('body').find('.panel-body ._buttons').addClass('row').append('<button type="button" class="btn btn-info pull-right mright10" id="synch_client_groups"><i class="fa fa-refresh"></i> Xero® Online</button>');
            var originalContents = $('body').find('.panel-body ._buttons').html();
            $('body').find('.panel-body ._buttons').empty().html('<div class="col-md-12">' + originalContents + '</div>');
            $(xero_progress_bar).insertAfter('.panel-body ._buttons');
        }
    });

    $('body').on('click', '#synch_client_groups', function() {
        var btn_synch = $(this);
        var btn_label = btn_synch.html();
        btn_synch.prop('disabled', true);
        btn_synch.html('<i class="fa fa-refresh fa-spin"></i> Loading...');

        var $xero_synch_area = btn_synch.data('xero_synch_area');

        //ajax call
        ajax_synch_client_groups(btn_synch, btn_label, $xero_synch_area);

    });

    function ajax_synch_client_groups(btn_synch, btn_label, xero_synch_area){
        if(xero_synch_area){
         btn_synch.parent().append(xero_progress_bar);
     }

     $.get(admin_url + 'xero/synchallclientgroups', function(response) {
        if (response.success == true) {
            $('#process').parent().removeClass('hide');
            var percentage = 0;
            var timer = setInterval(function() {
                percentage = percentage + 20;
                progress_bar_process(percentage, timer, response.message);
            }, 1000);

            setTimeout(function() {
                btn_synch.html(btn_label);
                btn_synch.prop('disabled', false);
                if(xero_synch_area){
                    $('#process').parent().remove();
                }

                if ($.fn.DataTable.isDataTable('.table-customer-groups')) {
                    $('.table-customer-groups').DataTable().ajax.reload();
                    add_xero_comp_client_groups();
                }
            }, 5000);

        } else {
            alert_float('warning', response.message);
            btn_synch.html(btn_label);
            btn_synch.prop('disabled', false);
            if(xero_synch_area){
                $('#process').parent().remove();
            }

        }

    }, 'json');
    }


    function add_xero_comp_client_groups() {
        $.get(admin_url + 'xero/get_xero_client_groups/id', function(response) {
            if (response.length > 0) {
                var xero_client_r = [];
                jQuery.each(response, function(i, val) {
                    xero_client_r.push(val['id']);
                });
                //console.log(xero_client_r);
                var table = $('.table-customer-groups').DataTable();
                if ($.fn.DataTable.isDataTable('.table-customer-groups')) {
                    table.on('order.dt search.dt', function() {
                        table.column(0, {
                            search: 'applied',
                            order: 'applied'
                        }).nodes().each(function(cell, i) {

                            var str = cell.innerHTML; 
                            var matchedString = str.match(/data-id=\"(.*?)\"/);


                            if ($.inArray(matchedString[1], xero_client_r) >= 0) {
                                var checkstr = cell.innerHTML;
                                var check = checkstr.match(/class=\"badge-xs(.*?)\"/);
                                cell.innerHTML = check ? cell.innerHTML : ' <div class="badge-xs badge bg-info">Xero</div> ' + cell.innerHTML;
                            }
                        });
                    }).draw()
                }

            }
        }, 'json');
    }


//==========================================================
//  ITEMS
//==========================================================
/**
 * Item modal Update
 */
 $('#sales_item_modal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    var id = button.data('id');

    var working_area = $(this).find('.modal-body .row .col-md-12');
    working_area.find('.inc_acc').remove();
    handleTaxes(working_area);
    //ajax call
    $.get(admin_url + 'xero/get_item_income_acc_select_field/' + id, function(data) {
        $(data).insertAfter(working_area.find('div:eq(3)'));
        working_area.find('select[name="xero_incomeAccountRef"]').selectpicker('refresh');
    }, 'html');

    if (typeof(id) === 'undefined') {
        working_area.find('select[name="xero_incomeAccountRef"]').selectpicker('refresh').selectpicker('val', '');
    }

});


 function handleTaxes(working_area){
    var tax = working_area.find('select[name="tax"]');
    var tax2 = working_area.find('select[name="tax2"]');

    tax.on('change', function(e){
       var tax_value = tax.selectpicker('val');
       if($.isNumeric(tax_value)){
        var tax_name = $('select[name="tax"] option:selected').data('subtext');
        var res = tax_name.split("~");
        if(res.length > 1){
          tax2.addClass('required').selectpicker('refresh').selectpicker('val', '');
          $('.tax_alert').remove();
          tax.parent().parent().parent().parent().append('<div class="col-md-12 tax_alert hide"><span class="text-info"><i class="fa fa-warning"></i> You have selected a multi-tax, Tax 2 must contain</span> <strong class="text-danger">'+res[0]+'~</strong></div>');
          $('.tax_alert').removeClass('hide');
        } else{            
          tax2.removeClass('required').selectpicker('refresh').selectpicker('val', '');
          $('.tax_alert').addClass('hide');
        }
       }
    });

    tax2.on('change', function(e){
        var tax_value2 = tax2.selectpicker('val');
       if($.isNumeric(tax_value2)){
        var tax_name = $('select[name="tax"] option:selected').data('subtext');
        var res = tax_name.split("~");

        var tax_name2 = $('select[name="tax2"] option:selected').data('subtext');
        var res2 = tax_name2.split("~");
        if(res.length > 1){
            if(res[0] != res2[0]){
               tax2.selectpicker('refresh').selectpicker('val', '');
               alert_float('warning', 'Tax 2 must contain <strong class="text-danger">'+res[0]+'~</strong>');
            }
            if(res[1] == res2[1]){
               tax2.selectpicker('refresh').selectpicker('val', '');
               alert_float('warning', 'Tax 2 must be different from Tax 1');
            }
        }
        if(res.length == 0){            
            tax2.selectpicker('refresh').selectpicker('val', '');
            alert_float('warning', 'Select Tax 1 first');
        }
       }
    });
 }

/**
 * Synch All invoice items
 */
 $(function() {
    if ($('body').hasClass('invoice_items')) {
        add_xero_comp_items();

        $('body').find('.panel-body').prepend('<div class="alert alert-info">Now that you have the Xero® Online module active. Note: <ul><li>1. <div class="badge bg-info">Xero</div> Means it has already been synchronized.</li></ul></div>');
        $('body').find('.panel-body ._buttons').addClass('row').append('<button type="button" class="btn btn-info pull-right mright10" id="synch_items"><i class="fa fa-refresh"></i> Xero® Online</button>');
        var originalContents = $('body').find('.panel-body ._buttons').html();
        $('body').find('.panel-body ._buttons').empty().html('<div class="col-md-12">' + originalContents + '</div>');
        $(xero_progress_bar).insertAfter('.panel-body ._buttons');
    }
});

$('body').on('click', '#synch_items', function() {
    var btn_synch = $(this);
    var btn_label = btn_synch.html();
    btn_synch.prop('disabled', true);
    btn_synch.html('<i class="fa fa-refresh fa-spin"></i> Loading...');

    var $xero_synch_area = btn_synch.data('xero_synch_area');

    //ajax call
    ajax_synch_items(btn_synch, btn_label, $xero_synch_area);

});

 function ajax_synch_items(btn_synch, btn_label, xero_synch_area){
    if(xero_synch_area){
     btn_synch.parent().append(xero_progress_bar);
 }

 $.get(admin_url + 'xero/synchallitems', function(response) {
    if (response.success == true) {
        $('#process').parent().removeClass('hide');
        var percentage = 0;
        var timer = setInterval(function() {
            percentage = percentage + 20;
            progress_bar_process(percentage, timer, response.message);
        }, 1000);

        setTimeout(function() {
            btn_synch.html(btn_label);
            btn_synch.prop('disabled', false);
            if(xero_synch_area){
                $('#process').parent().remove();
            }

            if ($.fn.DataTable.isDataTable('.table-invoice-items')) {
                $('.table-invoice-items').DataTable().ajax.reload();
                add_xero_comp_items();
            }
        }, 5000);

    } else {
        alert_float('warning', response.message);
        btn_synch.html(btn_label);
        btn_synch.prop('disabled', false);
        if(xero_synch_area){
            $('#process').parent().remove();
        }

    }

}, 'json');
}


function add_xero_comp_items() {    
    $.get(admin_url + 'xero/get_xero_items/item_id', function(response) {
        if (response.length > 0) {
            var xero_item_r = [];
            jQuery.each(response, function(i, val) {
                xero_item_r.push(val['item_id']);
            });
            //console.log(xero_item_r);
            var table = $('.table-invoice-items').DataTable();
            if ($.fn.DataTable.isDataTable('.table-invoice-items')) {
                table.on('order.dt search.dt', function() {
                $('.xero_item_r').remove(); //avoid duplication of qb badges
                table.column(0, {
                    search: 'applied',
                    order: 'applied'
                }).nodes().each(function(cell, i) {

                    var str = cell.innerHTML; 
                    var matchedString = str.match(/value=\"(.*?)\"/);


                    if ($.inArray(matchedString[1], xero_item_r) >= 0) {
                        cell.innerHTML = cell.innerHTML + ' <div class="xero_item_r badge-xs badge bg-info">Xero</div>';
                    }
                });
            }).draw()
            }

        }
    }, 'json');
}

//==========================================================
//  INVOICES
//==========================================================
/**
 * Synch all invoices
 */

 $(function() {
    if ($('body').hasClass('invoices')) {
        add_xero_comp_invoices();

        $('body').find('._buttons').prepend('<div class="alert alert-info"><div class="row"><div class="col-md-7">Now that you have the Xero® Online module active. Note: <ul><li>1. <div class="badge bg-info">Xero</div> Means it has already been synchronized.</li></ul></div> <div class="col-md-5"><button type="button" class="btn btn-info pull-right mright10" id="synch_invoices"><i class="fa fa-refresh"></i> Xero® Online</button></div></div>');

        var originalContents = $('body').find('._buttons').html();
        $('body').find('._buttons').empty().html('<div class="col-md-12">' + originalContents + '</div>');
        $(xero_progress_bar).insertAfter('._buttons .display-block');
    }
});

 function add_xero_comp_invoices() {
        $.get(admin_url + 'xero/get_xero_invoice/id', function(response) {
            if (response.length > 0) {
                var xero_client_r = [];
                jQuery.each(response, function(i, val) {
                    xero_client_r.push(val['id']);
                });
                //console.log(xero_client_r);
                var table = $('.table-invoices').DataTable();
                if ($.fn.DataTable.isDataTable('.table-invoices')) {
                    table.on('order.dt search.dt', function() {
                        table.column(0, {
                            search: 'applied',
                            order: 'applied'
                        }).nodes().each(function(cell, i) {
                            console.log(i);
                            var str = cell.innerHTML; 
                            var matchedString = str.match(/onclick=\"init_invoice\((.*?)\); return false;\"/);
                            
                            if ($.inArray(matchedString[1], xero_client_r) >= 0) {
                                var checkstr = cell.innerHTML;
                                var check = checkstr.match(/class=\"badge-xs(.*?)\"/);
                                cell.innerHTML = check ? cell.innerHTML : ' <div class="badge-xs badge bg-info">Xero</div> ' + cell.innerHTML;
                            }
                        });
                    }).draw()
                }

            }
        }, 'json');
    }


 $('body').on('click', '#synch_invoices', function() {
    var btn_synch = $(this);
    var btn_label = btn_synch.html();
    btn_synch.prop('disabled', true);
    btn_synch.html('<i class="fa fa-refresh fa-spin"></i> Loading...');

    var $xero_synch_area = btn_synch.data('xero_synch_area');

    //ajax call
    ajax_synch_invoices(btn_synch, btn_label, $xero_synch_area);

});

 function ajax_synch_invoices(btn_synch, btn_label, xero_synch_area){
    if(xero_synch_area){
     btn_synch.parent().append(xero_progress_bar);
 }

 $.get(admin_url + 'xero/synchallinvoices', function(response) {
    if (response.success == true) {
        $('#process').parent().removeClass('hide');
        var percentage = 0;
        var timer = setInterval(function() {
            percentage = percentage + 20;
            progress_bar_process(percentage, timer, response.message);
        }, 1000);

        setTimeout(function() {
            btn_synch.html(btn_label);
            btn_synch.prop('disabled', false);
            if(xero_synch_area){
                $('#process').parent().remove();
            }

            if ($.fn.DataTable.isDataTable('.table-invoices')) {
                $('.table-invoices').DataTable().ajax.reload();
                    //add_xero_comp_items();
                }
            }, 5000);

    } else {
        alert_float('warning', response.message);
        btn_synch.html(btn_label);
        btn_synch.prop('disabled', false);
        if(xero_synch_area){
            $('#process').parent().remove();
        }

    }

}, 'json');
}

//==========================================================
//  PAYMENTS MODES / METHODS
//==========================================================
/**
 * Synch all payment modes
 */

 $(function() {
    if ($('body').hasClass('paymentmodes')) {
        add_xero_comp_payment_modes();

        $('body').find('.panel-body').prepend('<div class="alert alert-info">Now that you have the Xero® Online module active. Note: <ul><li>1. <div class="badge bg-info">Xero</div> Means it has already been synchronized. Click <i class="fa fa-refresh"></i> to verify.</li></ul></div>');
        var originalContents = $('body').find('.panel-body ._buttons').html();
        $('body').find('.panel-body ._buttons').empty().html('<div class="col-md-12">' + originalContents + '</div>');
        $(xero_progress_bar).insertAfter('.panel-body ._buttons');
    }
});

 function add_xero_comp_payment_modes() {
        $.get(admin_url + 'xero/get_xero_payment_modes/id', function(response) {
            if (response.length > 0) {
                var xero_client_r = [];
                jQuery.each(response, function(i, val) {
                    xero_client_r.push(val['id']);
                });
                //console.log(xero_client_r);
                var table = $('.table-payment-modes').DataTable();
                if ($.fn.DataTable.isDataTable('.table-payment-modes')) {
                    table.on('order.dt search.dt', function() {
                        table.column(0, {
                            search: 'applied',
                            order: 'applied'
                        }).nodes().each(function(cell, i) {

                            var str = cell.innerHTML; 
                            var matchedString = str.match(/data-id=\"(.*?)\"/);


                            if ($.inArray(matchedString[1], xero_client_r) >= 0) {
                                var checkstr = cell.innerHTML;
                                var check = checkstr.match(/class=\"badge-xs(.*?)\"/);
                                cell.innerHTML = check ? cell.innerHTML : ' <div class="badge-xs badge bg-info">Xero</div> ' + cell.innerHTML;
                            }
                        });
                    }).draw()
                }

            }
        }, 'json');
    }

//==========================================================
//  PAYMENTS
//==========================================================
/**
 * Synch all payments
 */

 $(function() {
    if ($('body').hasClass('payments')) {
        add_xero_comp_payments();

        $('body').find('.panel-body').prepend('<div class="alert alert-info">Now that you have the Xero® Online module active. Note: <ul><li>1. <div class="badge bg-info">Xero</div> Means it has already been synchronized.</li></ul></div><div class="_buttons"></div><hr>');
        $('body').find('.panel-body ._buttons').addClass('row').append('<button type="button" class="btn btn-info pull-right mright10" id="synch_payments"><i class="fa fa-refresh"></i> Xero® Online</button>');
        var originalContents = $('body').find('.panel-body ._buttons').html();
        $('body').find('.panel-body ._buttons').empty().html('<div class="col-md-12">' + originalContents + '</div>');
        $(xero_progress_bar).insertAfter('.panel-body ._buttons');
    }
});

 function add_xero_comp_payments() {
        $.get(admin_url + 'xero/get_xero_payments/id', function(response) {
            if (response.length > 0) {
                var xero_payment_r = [];
                jQuery.each(response, function(i, val) {
                    xero_payment_r.push(val['id']);
                });
                console.log(xero_payment_r);
                var table = $('.table-payments').DataTable();
                if ($.fn.DataTable.isDataTable('.table-payments')) {
                    table.on('order.dt search.dt', function() {
                        table.column(0, {
                            search: 'applied',
                            order: 'applied'
                        }).nodes().each(function(cell, i) {
                            
                            var str = cell.innerHTML;
                            var matchedString = str.match(/\>(.*?)\<\/a\>\<div class\="row-options"\>/);


                            if ($.inArray(matchedString[1], xero_payment_r) >= 0) {
                                var checkstr = cell.innerHTML;
                                var check = checkstr.match(/class=\"badge-xs(.*?)\"/);
                                cell.innerHTML = check ? cell.innerHTML : ' <div class="badge-xs badge bg-info">Xero</div> ' + cell.innerHTML;
                            }
                        });
                    }).draw()
                }

            }
        }, 'json');
    }


 $('body').on('click', '#synch_payments', function() {
    var btn_synch = $(this);
    var btn_label = btn_synch.html();
    btn_synch.prop('disabled', true);
    btn_synch.html('<i class="fa fa-refresh fa-spin"></i> Loading...');

    var $xero_synch_area = btn_synch.data('xero_synch_area');

    //ajax call
    ajax_synch_payments(btn_synch, btn_label, $xero_synch_area);

});

 function ajax_synch_payments(btn_synch, btn_label, xero_synch_area){
    if(xero_synch_area){
     btn_synch.parent().append(xero_progress_bar);
    }

 $.get(admin_url + 'xero/synchallpayments', function(response) {
    if (response.success == true) {
        $('#process').parent().removeClass('hide');
        var percentage = 0;
        var timer = setInterval(function() {
            percentage = percentage + 20;
            progress_bar_process(percentage, timer, response.message);
        }, 1000);

        setTimeout(function() {
            btn_synch.html(btn_label);
            btn_synch.prop('disabled', false);
            if(xero_synch_area){
                $('#process').parent().remove();
            }

            if ($.fn.DataTable.isDataTable('.table-payments')) {
                $('.table-payments').DataTable().ajax.reload();
                    //add_xero_comp_items();
                }
            }, 5000);

    } else {
        alert_float('warning', response.message);
        btn_synch.html(btn_label);
        btn_synch.prop('disabled', false);
        if(xero_synch_area){
            $('#process').parent().remove();
        }

    }

}, 'json');
}

//==========================================================
//  EXPENSES CATEGORIES
//==========================================================
/**
 * Synch all Expenses Categories
 */

 $(function() {
    if ($('body').hasClass('categories','expenses')) {
        add_xero_comp_expenses_categories();

        $('body').find('.panel-body').prepend('<div class="alert alert-info">Now that you have the Xero® Online module active. Note: <ul><li>1. <div class="badge bg-info">Xero</div> Means it has already been synchronized. Click <i class="fa fa-refresh"></i> to verify.</li></ul></div>');
        var originalContents = $('body').find('.panel-body ._buttons').html();
        $('body').find('.panel-body ._buttons').empty().html('<div class="col-md-12">' + originalContents + '</div>');
        $(xero_progress_bar).insertAfter('.panel-body ._buttons');
    }
});

 function add_xero_comp_expenses_categories() {
        $.get(admin_url + 'xero/get_xero_expenses_categories/id', function(response) {
            if (response.length > 0) {
                var xero_client_r = [];
                jQuery.each(response, function(i, val) {
                    xero_client_r.push(val['id']);
                });
                //console.log(xero_client_r);
                var table = $('.table-expenses-categories').DataTable();
                if ($.fn.DataTable.isDataTable('.table-expenses-categories')) {
                    table.on('order.dt search.dt', function() {
                        table.column(0, {
                            search: 'applied',
                            order: 'applied'
                        }).nodes().each(function(cell, i) {

                            var str = cell.innerHTML; 
                            var matchedString = str.match(/onclick=\"edit_category\(this,(.*?)\); return false;\"/);
                            
                            if ($.inArray(matchedString[1], xero_client_r) >= 0) {
                                var checkstr = cell.innerHTML;
                                var check = checkstr.match(/class=\"badge-xs(.*?)\"/);
                                cell.innerHTML = check ? cell.innerHTML : ' <div class="badge-xs badge bg-info">Xero</div> ' + cell.innerHTML;
                            }
                        });
                    }).draw()
                }

            }
        }, 'json');
    }  

//==========================================================
//  EXPENSES
//==========================================================
/**
 * Synch all expenses
 */
 $('body').on('click', '#synch_expenses', function() {
    var btn_synch = $(this);
    var btn_label = btn_synch.html();
    btn_synch.prop('disabled', true);
    btn_synch.html('<i class="fa fa-refresh fa-spin"></i> Loading...');

    var $xero_synch_area = btn_synch.data('xero_synch_area');

    //ajax call
    ajax_synch_expenses(btn_synch, btn_label, $xero_synch_area);

});

 function ajax_synch_expenses(btn_synch, btn_label, xero_synch_area){
    if(xero_synch_area){
     btn_synch.parent().append(xero_progress_bar);
 }

 $.get(admin_url + 'xero/synchallexpenses', function(response) {
    if (response.success == true) {
        $('#process').parent().removeClass('hide');
        var percentage = 0;
        var timer = setInterval(function() {
            percentage = percentage + 20;
            progress_bar_process(percentage, timer, response.message);
        }, 1000);

        setTimeout(function() {
            btn_synch.html(btn_label);
            btn_synch.prop('disabled', false);
            if(xero_synch_area){
                $('#process').parent().remove();
            }

            if ($.fn.DataTable.isDataTable('.table-expenses')) {
                $('.table-expenses').DataTable().ajax.reload();
                    //add_xero_comp_items();
                }
            }, 5000);

    } else {
        alert_float('warning', response.message);
        btn_synch.html(btn_label);
        btn_synch.prop('disabled', false);
        if(xero_synch_area){
            $('#process').parent().remove();
        }

    }

}, 'json');
}

$(function(){
    var expense_working_area = $('#expense-form');
    handleTaxes(expense_working_area);
});

//==========================================================
//  DISCONNECT FROM QUICKBOOKS ONLINE
//==========================================================
$(function() {
    $('#xero_disconnect_btn').css('padding', '5px');
});

$('body').on('click', '#xero_disconnect_btn', function() {
    var btn_synch = $(this);
    var btn_label = btn_synch.html();
    btn_synch.prop('disabled', true);
    btn_synch.css({'padding': '10px 15px'}).removeClass('no-padding');
    btn_synch.html('<i class="fa fa-refresh fa-spin"></i> Loading...');
    //ajax call
    $.get(admin_url + 'xero/xero_auth/disconnect', function(response) {
        if (response.success == true) {
            $('#process').parent().removeClass('hide');
            var percentage = 0;
            var timer = setInterval(function() {
                percentage = percentage + 20;
                progress_bar_process(percentage, timer, response.message);
            }, 1000);

            setTimeout(function() {
                btn_synch.addClass('no-padding');
                btn_synch.html(btn_label);
                //refresh page
                location.reload(true);
            }, 5000);

        } else {
            alert_float('warning', response.message);
            btn_synch.html(btn_label);
            btn_synch.prop('disabled', false);
        }
    }, 'json');

});

//==========================================================
//  CONFIG PAGE
//==========================================================
$(function() {
    if(xero_manual_cron_sync_enabled === '1') {
        $('#auto_synch_area').addClass('hide');
     } else {
        $('#auto_synch_area').removeClass('hide');
      }

    $("[name='settings[xero_manual_cron_sync_enabled]']").on('change',  function(e) {
       if(this.value === '1') {
            $('#auto_synch_area').addClass('hide');
        } else {
            $('#auto_synch_area').removeClass('hide');
        }
    });
});



//==========================================================
//  GENERAL
//==========================================================
function progress_bar_process(percentage, timer, message) {
    $('.progress-bar').css('width', percentage + '%');
    if (percentage > 100) {
        clearInterval(timer);
        $('#process').parent().addClass('hide');
        $('.progress-bar').css('width', '0%');
        alert_float('success', message);
    }
}



//==========================================================
//  RESET
//==========================================================

$('body').on('click', '#xero_reset_all_sync', function() {
    "use strict";
    var btn_synch = $(this);
    var btn_label = btn_synch.html();
    btn_synch.prop('disabled', true);
    btn_synch.html('<i class="fa fa-refresh fa-spin"></i> Loading...');
    //ajax call
    $.get(admin_url + 'xero/reset_all_sync', function(response) {
        if (response.success == true) {
            $('#xero_reset_progress_area').removeClass('hide');
            var percentage = 0;
            var timer = setInterval(function() {
                percentage = percentage + 20;
                progress_bar_process(percentage, timer, response.message);
            }, 1000);

            setTimeout(function() {
                btn_synch.html(btn_label);
                btn_synch.prop('disabled', false);
                $('#xero_reset_progress_area').addClass('hide');
                //refresh page
                //location.reload(true);
            }, 5000);

        } else {
            alert_float('warning', response.message);
            btn_synch.html(btn_label);
            btn_synch.prop('disabled', false);
        }
    }, 'json');

});

//==========================================================
//  PAYMENT METHOD
//==========================================================
/**
 * PAYMENT METHOD modal Update
 */
 $('#payment_mode_modal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    var id = button.data('id');

    var working_area = $(this).find('.modal-body .row .col-md-12');
    working_area.find('.inc_acc').remove();
    handleHasSortCode(working_area);
    //ajax call
    $.get(admin_url + 'xero/get_payment_method_acc_and_select_field/' + id, function(data) {
        $(data).insertAfter(working_area.find('div:eq(0)'));
        working_area.find('select[name="xero_hasSortCode"]').selectpicker('refresh').change();
    }, 'html');

    if (typeof(id) === 'undefined') {
        working_area.find('select[name="xero_hasSortCode"]').selectpicker('refresh').selectpicker('val', '').change();
    }

        
});

function handleHasSortCode(working_area){
    $('body').on('change', '#xero_hasSortCode', function(e){
       var xero_hasSortCode_value = this.value;
       console.log(xero_hasSortCode_value);
       if(xero_hasSortCode_value == '1') {
        $('.no_sort').addClass('hide');
        $('.has_sort').removeClass('hide'); 
       }else{        
         $('.has_sort').addClass('hide');
         $('.no_sort').removeClass('hide');
       }
   });
}

