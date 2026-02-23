 <script>
   (function($) {
    "use strict";

  $('input[name="commodity_id"]').val("<?php echo new_html_entity_decode($commodity_item->id) ;?>");

    //inventory stock
    var ProposalServerParams = {
        "warehouse_ft": "[name='warehouse_filter[]']",
        "commodity_ft": "[name='commodity_id']",
        "alert_filter": "[name='alert_filter']",
    };
    var table_inventory_stock = $('table.table-table_inventory_stock');
   var  _table_api = initDataTable(table_inventory_stock, admin_url+'warehouse/table_inventory_stock', [], [], ProposalServerParams, [0, 'desc']);

   $('.table-table_inventory_stock').DataTable().columns([0]).visible(false, false);

    $.each(ProposalServerParams, function(i, obj) {
        $('select' + obj).on('change', function() {  
            table_inventory_stock.DataTable().ajax.reload();
        });
    });

    //expriry date
    var ProposalServerParams = {
        "warehouse_ft": "[name='warehouse_filter[]']",
        "commodity_ft": "[name='commodity_id']",
        "alert_filter": "[name='alert_filter']",
    };
    var table_view_commodity_detail = $('table.table-table_view_commodity_detail');
   var  _table_api = initDataTable(table_view_commodity_detail, admin_url+'warehouse/table_view_commodity_detail', [], [], ProposalServerParams);
    $.each(ProposalServerParams, function(i, obj) {
        $('select' + obj).on('change', function() {  
            table_view_commodity_detail.DataTable().ajax.reload();
        });
    });



//transaction, history
var ProposalServerParams = {
        "warehouse_ft": "[name='warehouse_filter[]']",
        "commodity_ft": "[name='commodity_id']",
        
    };

  var   table_warehouse_history = $('table.table-table_warehouse_history');
   var  _table_api = initDataTable(table_warehouse_history, admin_url+'warehouse/table_warehouse_history', [], [], ProposalServerParams, [0, 'desc']);

   $('.table-table_warehouse_history').DataTable().columns([0]).visible(false, false);

    $.each(ProposalServerParams, function(i, obj) {
        $('select' + obj).on('change', function() {  
            table_warehouse_history.DataTable().ajax.reload();
        });
    });

    // PO table
    var table_warehouse_PO = $('table.table-table_warehouse_PO');
    var _table_api_po = initDataTable(table_warehouse_PO, admin_url + 'warehouse/table_warehouse_PO', [], [], ProposalServerParams, [0, 'desc']);

    // SO table
    var table_warehouse_so = $('table.table-table_warehouse_so');
    var _table_api_so = initDataTable(table_warehouse_so, admin_url + 'warehouse/table_warehouse_so', [], [], ProposalServerParams, [0, 'desc']);

    // Top Buyers Table
    var table_commodity_buyers = $('table.table-table_commodity_buyers');
    var _table_api_buyers = initDataTable(table_commodity_buyers, admin_url + 'warehouse/table_commodity_buyers', [], [], ProposalServerParams, [1, 'desc']);

    // Top Sellers Table
    var table_commodity_sellers = $('table.table-table_commodity_sellers');
    var _table_api_sellers = initDataTable(table_commodity_sellers, admin_url + 'warehouse/table_commodity_sellers', [], [], ProposalServerParams, [1, 'desc']);

    init_ajax_search('items','#item_select_print_barcode.ajax-search',undefined,admin_url+'warehouse/wh_commodity_code_search_all');
    
})(jQuery);

</script>
<script>
    function change_commodity_status(id, el){
        if(el.checked){
            var status = 1;
        }else{
            var status = 0;
        }
        $.post(admin_url+'warehouse/change_commodity_status/'+id+'/'+status).done(function(response){
            response = JSON.parse(response);
            if(response.success == true){
                alert_float('success', response.message);
            }else{
                alert_float('warning', response.message);
            }
        });
    }
</script>