<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<?php
			echo form_open($this->uri->uri_string(),array('id'=>'estimate-form','class'=>'_transaction_form'));
			if(isset($estimate)){
				echo form_hidden('isedit');
			}
			?>
			<div class="col-md-12">
				<?php $this->load->view('admin/estimates/estimate_template'); ?>
			</div>
			<?php echo form_close(); ?>
			<?php $this->load->view('admin/invoice_items/item'); ?>
		</div>
	</div>
</div>
</div>
<?php init_tail(); ?>
<script>
	$(function(){
		validate_estimate_form();
		// Init accountacy currency symbol
		init_currency();
		// Project ajax search
		init_ajax_project_search_by_customer_id();
		// Maybe items ajax search
	    init_ajax_search('items','#item_select.ajax-search',undefined,admin_url+'items/search');

		// Calculate weight/volume
		$("#caldata").click(function(){
			var volume = 0.00;
			var weight = 0.00;
			for (var i = 1; i <= 500; i++) {
				var vol = Number($('[name="newitems['+i+'][custom_fields][items][5]"]').val()) || 0;
				volume += vol;
				var wei = Number($('[name="newitems['+i+'][custom_fields][items][1]"]').val()) || 0;
				weight += wei;
				var volit = Number($('[name="items['+i+'][custom_fields][items][5]"]').val()) || 0;
				volume += volit;
				var weiit = Number($('[name="items['+i+'][custom_fields][items][1]"]').val()) || 0;
				weight += weiit;
			}
			$("#weight").text(weight);
			$("#volu").text(volume);
		});

		// Adjust dropdown
		$("#adjust").change(function(){
			$("#adj").val($("#adjust option:selected").val());
			calculate_total();
		});
	});
</script>

</body>
</html>
