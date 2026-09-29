<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if(isset($client)){ ?>
<h4 class="customer-profile-group-heading"><?php echo _l('quotes'); ?></h4>
<?php if(has_permission('quotes','','create')){ ?>
<a href="<?php echo admin_url('quotes/quote?rel_type=customer&rel_id='.$client->userid); ?>" class="btn btn-info mbot25<?php if($client->active == 0){echo ' disabled';} ?>"><?php echo _l('new_quote'); ?></a>
<?php } ?>
<?php if(total_rows(db_prefix().'quotes',array('rel_type'=>'customer','rel_id'=>$client->userid))> 0 && (has_permission('quotes','','create') || has_permission('quotes','','edit'))){ ?>
<a href="#" class="btn btn-info mbot25" data-toggle="modal" data-target="#sync_data_quote_data"><?php echo _l('sync_data'); ?></a>
<?php $this->load->view('admin/quotes/sync_data',array('related'=>$client,'rel_id'=>$client->userid,'rel_type'=>'customer')); ?>
<?php } ?>
<?php
$table_data = array(
 _l('quote') . ' #',
 _l('quote_subject'),
 _l('quote_total'),
 _l('quote_date'),
 _l('quote_open_till'),
 _l('tags'),
 _l('quote_date_created'),
 _l('quote_status'));
$custom_fields = get_custom_fields('quote',array('show_on_table'=>1));
foreach($custom_fields as $field){
 array_push($table_data,$field['name']);
}

$table_data = hooks()->apply_filters('quotes_relation_table_columns', $table_data);

render_datatable($table_data,'quotes-client-profile',[],[
    'data-last-order-identifier' => 'quotes-relation',
    'data-default-order'         => get_table_last_order('quotes-relation'),
]);
?>
<?php } ?>
