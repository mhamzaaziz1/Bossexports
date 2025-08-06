<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php hooks()->do_action('head_element_client'); ?>
<style>
        .card {
            border: 1px solid #007bff; /* Custom border color */
            border-radius: 0.5rem; /* Rounded corners */
        }
    </style>
    
    
    <?php
				$currency_name = '';
			    if(isset($base_currency)){
			       $currency_name = $base_currency->name;
			    }
				
				$cart_empty = 0;
				$list_id = [];
				if(isset($_COOKIE['cart_id_list'])){
					$list_id = $_COOKIE['cart_id_list'];
					if($list_id){
						$cart_empty = 1;
					}
				}
				$sub_total = 0;
				$date = date('Y-m-d');

			?>
			
			<?php 
				                  if($list_id){
				                    $array_list_id = explode(',',$list_id);
				                    $list_qty = $_COOKIE['cart_qty_list'];
				                    $array_list_qty = explode(',',$list_qty); ?>
				                    <?php $tot_wei =0;
				                    $tot_vol =0;
				                foreach ($array_list_id as $key => $product_id) { ?>
			
			<?php
				                                        $data_product = $this->omni_sales_model->get_product($product_id);
				                                        if($data_product){
				                                        }
				                                        $prices  = 0;
												        $data_prices = $this->omni_sales_model->get_price_channel($product_id,2);
												        if($data_prices){
												            $prices  = $data_prices->prices;
												        }

												        $discount_percent = 0;
				                                        $prices_discount  = 0;

												        $discount = $this->omni_sales_model->check_discount($product_id, $date, 2);
												        if($discount){
												              $discount_percent = $discount->discount;
												              $prices_discount = $prices-(($discount_percent * $prices) / 100);
												        }

											           $w_qty = 0;
												        $wh = $this->omni_sales_model->get_total_inventory_commodity($product_id);
												        if($wh){
												          if($wh->inventory_number){
												            $w_qty = $wh->inventory_number;
												          }
												        }
												        $true_p = $prices;
												        if($prices_discount>0){
												        	$true_p = $prices_discount;
												        }

													        
				                                     ?>
				                                     
				                                     
				                                     
				                                     <?php
				                               	  
                                                $tqty+=(int)$array_list_qty[$key];
				                               $line_total = (int)$array_list_qty[$key]*$true_p;
				                               $sub_total += $line_total;
				                                $tot_wei += get_custom_field_value($product_id, get_custom_fields('items')[1]['id'], 'items_pr')*(int)$array_list_qty[$key];
				                                $tot_vol += get_custom_field_value($product_id, get_custom_fields('items')[0]['id'], 'items_pr')*(int)$array_list_qty[$key];
				                                }}?>


<div class="container mt-5">
    <div class="row">
        <div class="col-md-3 grid col-sm-6">
	            <div class="grid product-cell">
	                <div class="product-content">
	                    <div class="title"><a href="">Total Weight</a></div> 
	                    <span class="price">
	                        
	                    		<?php echo $tot_wei; ?>	
	                    	                  	
	                    </span>					                    
	                </div>
	            </div>
	        </div>
        <div class="col-md-3 grid col-sm-6">
	            <div class="grid product-cell">
	                <div class="product-content">
	                    <div class="title"><a href="">Total Volume</a></div> 
	                    <span class="price">
	                        
	                    		<?php echo $tot_vol; ?>	
	                    	                  	
	                    </span>					                    
	                </div>
	            </div>
	        </div>
	        <div class="col-md-3 grid col-sm-6">
	            <div class="grid product-cell">
	                <div class="product-content">
	                    <div class="title"><a href=""># of Items</a></div> 
	                    <span class="price">
	                        
	                    		<?php echo $tqty; ?>	
	                    	                  	
	                    </span>					                    
	                </div>
	            </div>
	        </div>
	        <div class="col-md-3 grid col-sm-6">
	            <div class="grid product-cell">
	                <div class="product-content">
	                    <div class="title"><a href="">Total amount</a></div> 
	                    <span class="price">
	                        
	                    		<?php echo app_format_money($sub_total,''); ?>	
	                    	                  	
	                    </span>					                    
	                </div>
	            </div>
	        </div>
    </div>
</div><br><br><br>






<div class="col-md-3 left_bar">
    <ul class="nav-tabs--vertical nav" role="navigation">
	    <li class="head text-center">
	    	<h5><?php echo _l('category'); ?></h5>
	    	<a href="<?php echo site_url('omni_sales/omni_sales_client/index/1/0'); ?>" class="view_all"><?php echo _l('all_products'); ?></a> 
	    </li>
	    <?php 
	    $data['title_group'] = $title_group;
	    	foreach ($group_product as $key => $value) {
	    		$active = '';
	    		if($value['id'] == $group_id){
	    		  $active = 'active';
	    		  $data['title_group'] = $value['name'];
	    		}
	    	 ?>

	    		<li class="nav-item <?php echo html_entity_decode($active); ?>">
					<a href="<?php echo site_url('omni_sales/omni_sales_client/index/1/'.$value['id']); ?>" class="nav-link">
						<?php echo html_entity_decode($value['name']); ?>
					</a>
				</li>
	    <?php	}
	     ?>					
		
	</ul>
</div>
<div class="col-md-9 right_bar">

	<div class="row">
		<?php echo form_open(site_url('omni_sales/omni_sales_client/search_product/'.$group_id),array('id'=>'invoice-form','class'=>'_transaction_form invoice-form')); ?>
		<div class="col-md-3">
			<?php echo render_select('commodity_type',$commodity_types,array('commodity_type_id','commondity_name'),'commodity_type'); ?>
		</div>
		<div class="col-md-3">
                                 <?php echo render_select('style_id',$styles,array('style_type_id','style_name'),'styles'); ?>
                            </div>
		<div class="col-md-5">
		    <label>Search by name</label>
			<input type="text" id="keyword" name="keyword" class="form-control" placeholder="Search for products here ...">
		</div>
		<div class="col-md-1"><br>
			<button type="submit" class="btn btn-info pull-right"><i class="fa fa-search"></i></button>
		</div>
		<?php echo form_close(); ?>

	</div>
	<?php $this->load->view('client/list_product/list_product_with_page',$data);?>
<hr>
</div>
<div class="modal fade" id="alert_add" tabindex="-1" role="dialog">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-body">
        	<div class="row">
	        	<div class="col-md-12 alert_content">
	        		<div class="clearfix"></div>
	        		<br>
	        		<br>
	        		<center class="add_success hide"><h4><?php echo _l('successfully_added'); ?></h4></center>
	        		<center class="add_error hide"><h4><?php echo _l('sorry_the_number_of_current_products_is_not_enough'); ?></h4></center>
	        		<br>
	        		<br>
					<div class="clearfix"></div>
	        	</div>
        	</div>
        </div>              
  	</div>
</div>
</div>

<?php hooks()->do_action('client_pt_footer_js'); ?>

