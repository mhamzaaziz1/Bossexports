<script>
	$(function(){
		'use strict';
		var ProposalServerParams = {
			"client_filter": "[name='client_filter[]']",
			"order_filter": "[name='order_filter[]']",
			"product_filter": "[name='product_filter[]']",
			"delivery_note_filter": "[name='delivery_note_filter[]']",
			"expired": function () {
				return $('#showExpiredWarranty').hasClass('active-filter') ? 1 : '';
			},
			"active": function () {
				return $('#showActiveWarranty').hasClass('active-filter') ? 1 : '';
			}

		};

		var warranty_information_table = $('table.table-warranty_information_table');
		var _table_api = initDataTable(warranty_information_table, admin_url+'warranty_management/warranty_information_table', [0], [0], ProposalServerParams,  [0, 'desc']);
		// $.each(ProposalServerParams, function(i, obj) {
		// 	$('select' + obj).on('change', function() {  
		// 		warranty_information_table.DataTable().ajax.reload();
		// 	});
		// });

		
		$.each(ProposalServerParams, function(i, obj) {

			if(typeof obj === "string"){
				$('select' + obj).on('change', function(){
					warranty_information_table.DataTable().ajax.reload();
				});
			}

		});

		var hidden_columns = [0,8];
		warranty_information_table.DataTable().columns(hidden_columns).visible(false, false);

	});

	function add_goods_delivery_expiration_date(goods_delivery_detail_id) {
		"use strict";

		$("#modal_wrapper").load("<?php echo admin_url('warranty_management/warranty_management/goods_delivery_expiration_modal'); ?>", {
			goods_delivery_detail_id: goods_delivery_detail_id,
		}, function() {

			$("body").find('#appointmentModal').modal({ show: true, backdrop: 'static' });
		});

		init_selectpicker();
		$(".selectpicker").selectpicker('refresh');

	}

	

</script>