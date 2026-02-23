<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if(isset($client)){ ?>
	<h4 class="customer-profile-group-heading"><?php echo _l('estimates'); ?></h4>
	<?php if(has_permission('estimates','','create')){ ?>
		<a href="<?php echo admin_url('estimates/estimate?customer_id='.$client->userid); ?>" class="btn btn-info mbot15<?php if($client->active == 0){echo ' disabled';} ?>"><?php echo _l('create_new_estimate'); ?></a>
	<?php } ?>
	<?php if(has_permission('estimates','','view') || has_permission('estimates','','view_own') || get_option('allow_staff_view_estimates_assigned') == '1'){ ?>
		<a href="#" class="btn btn-info mbot15" data-toggle="modal" data-target="#client_zip_estimates"><?php echo _l('zip_estimates'); ?></a>
	<?php } ?>
	<div id="estimates_total"></div>
	<div id="estimates_total">
        <h6>Stats of Retained</h6>
        <?php
        $CI = & get_instance();
        $CI->db->select('(SUM(total)*0.15) as retained');
        $CI->db->from('tblestimates');
        $CI->db->where('retained', "1");
        $CI->db->where('clientid', $client->userid);
        $query = $CI->db->get()->result();
        
        $CI->db->select('(SUM(total)*0.15) as retained');
        $CI->db->from('tblestimates');
        $CI->db->where('retained', "0");
        $CI->db->where('clientid', $client->userid);
        $query1 = $CI->db->get()->result();
        ?>
        <div class="row">
            <div class="col-md-5ths col-xs-12 total-column">
              <div class="panel_s">
                <div class="panel-body">
                  <h3 class="text-muted _total">
                    <?php echo $query1[0]->retained ?></h3>
                  <span class="text-default">Available Retained</span>
                </div>
              </div>
            </div>
            <div class="col-md-5ths col-xs-12 total-column">
              <div class="panel_s">
                <div class="panel-body">
                  <h3 class="text-muted _total">
                  <?php echo $query[0]->retained ?></h3>
                  <span class="text-info">Total released</span>
                </div>
              </div>
            </div>
        </div>
    </div>
	<?php
	$this->load->view('admin/estimates/table_html', array('class'=>'estimates-single-client'));
	$this->load->view('admin/clients/modals/zip_estimates');
	?>
<?php } ?>
