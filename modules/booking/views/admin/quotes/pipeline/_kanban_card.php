<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if ($quote['status'] == $status) { ?>
<li data-quote-id="<?php echo $quote['id']; ?>" class="<?php if($quote['invoice_id'] != NULL || $quote['estimate_id'] != NULL){echo 'not-sortable';} ?>">
   <div class="panel-body">
      <div class="row">
         <div class="col-md-12">
            <h4 class="bold pipeline-heading">
               <a href="<?php echo admin_url('booking/quotes/list_quotes/'.$quote['id']); ?>" data-toggle="tooltip" data-title="<?php echo $quote['subject']; ?>" onclick="quote_pipeline_open(<?php echo $quote['id']; ?>); return false;"><?php echo format_quote_number($quote['id']); ?></a>
               <?php if(has_permission('estimates','','edit')){ ?>
               <a href="<?php echo admin_url('booking/quotes/quote/'.$quote['id']); ?>" target="_blank" class="pull-right"><small><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i></small></a>
               <?php } ?>
            </h4>
            <span class="mbot10 inline-block full-width">
            <?php
               if($quote['rel_type'] == 'lead'){
                 echo '<a href="'.admin_url('leads/index/'.$quote['rel_id']).'" onclick="init_lead('.$quote['rel_id'].'); return false;" data-toggle="tooltip" data-title="'._l('lead').'">' .$quote['quote_to'].'</a><br />';
               } else if($quote['rel_type'] == 'customer'){
                 echo '<a href="'.admin_url('booking/clients/client/'.$quote['rel_id']).'" data-toggle="tooltip" data-title="'._l('client').'">' .$quote['quote_to'].'</a><br />';
               }
               ?>
            </span>
         </div>
         <div class="col-md-12">
            <div class="row">
               <div class="col-md-8">
                  <?php if($quote['total'] != 0){
                     ?>
                  <span class="bold"><?php echo _l('quote_total'); ?>:
                     <?php echo app_format_money($quote['total'], get_currency($quote['currency'])); ?>
                  </span>
                  <br />
                  <?php } ?>
                  <?php echo _l('quote_date'); ?>: <?php echo _d($quote['date']); ?>
                  <?php if(is_date($quote['open_till'])){ ?>
                  <br />
                  <?php echo _l('quote_open_till'); ?>: <?php echo _d($quote['open_till']); ?>
                  <?php } ?>
                  <br />
               </div>
               <div class="col-md-4 text-right">
                  <small><i class="fa fa-comments" aria-hidden="true"></i> <?php echo _l('quote_comments'); ?>: <?php echo total_rows(db_prefix().'quote_comments', array(
                     'quoteid' => $quote['id']
                     )); ?></small>
               </div>
               <?php $tags = get_tags_in($quote['id'],'quote');
                  if(count($tags) > 0){ ?>
               <div class="col-md-12">
                  <div class="mtop5 kanban-tags">
                     <?php echo render_tags($tags); ?>
                  </div>
               </div>
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</li>
<?php } ?>
