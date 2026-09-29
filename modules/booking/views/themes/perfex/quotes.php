<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s section-heading section-quotes">
  <div class="panel-body">
    <h4 class="no-margin section-text"><?php echo _l('quotes'); ?></h4>
  </div>
</div>
<div class="panel_s">
  <div class="panel-body">
    <table class="table dt-table table-quotes" data-order-col="3" data-order-type="desc">
      <thead>
        <tr>
          <th class="th-quote-number"><?php echo _l('quote') . ' #'; ?></th>
          <th class="th-quote-subject"><?php echo _l('quote_subject'); ?></th>
          <th class="th-quote-total"><?php echo _l('quote_total'); ?></th>
          <th class="th-quote-open-till"><?php echo _l('quote_open_till'); ?></th>
          <th class="th-quote-date"><?php echo _l('quote_date'); ?></th>
          <th class="th-quote-status"><?php echo _l('quote_status'); ?></th>
          <?php
          $custom_fields = get_custom_fields('quote',array('show_on_client_portal'=>1));
          foreach($custom_fields as $field){ ?>
            <th><?php echo $field['name']; ?></th>
          <?php } ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach($quotes as $quote){ ?>
          <tr>
            <td>
              <a href="<?php echo site_url('booking/quote/'.$quote['id'].'/'.$quote['hash']); ?>" class="td-quote-url">
                <?php echo format_quote_number($quote['id']); ?>
                <?php
                if ($quote['invoice_id']) {
                  echo '<br /><span class="text-success quote-invoiced">' . _l('estimate_invoiced') . '</span>';
                }
                ?>
              </a>
              <td>
                <a href="<?php echo site_url('booking/quote/'.$quote['id'].'/'.$quote['hash']); ?>" class="td-quote-url-subject">
                  <?php echo $quote['subject']; ?>
                </a>
                <?php
                if ($quote['invoice_id'] != NULL) {
                  $invoice = $this->invoices_model->get($quote['invoice_id']);
                  echo '<br /><a href="' . site_url('invoice/' . $invoice->id . '/' . $invoice->hash) . '" target="_blank" class="td-quote-invoice-url">' . format_invoice_number($invoice->id) . '</a>';
                } else if ($quote['estimate_id'] != NULL) {
                  $estimate = $this->estimates_model->get($quote['estimate_id']);
                  echo '<br /><a href="' . site_url('estimate/' . $estimate->id . '/' . $estimate->hash) . '" target="_blank" class="td-quote-estimate-url">' . format_estimate_number($estimate->id) . '</a>';
                }
                ?>
              </td>
              <td data-order="<?php echo $quote['total']; ?>">
                <?php
                if ($quote['currency'] != 0) {
                 echo app_format_money($quote['total'], get_currency($quote['currency']));
               } else {
                 echo app_format_money($quote['total'], get_base_currency());
               }
               ?>
             </td>
             <td data-order="<?php echo $quote['open_till']; ?>"><?php echo _d($quote['open_till']); ?></td>
             <td data-order="<?php echo $quote['date']; ?>"><?php echo _d($quote['date']); ?></td>
             <td><?php echo format_quote_status($quote['status']); ?></td>
             <?php foreach($custom_fields as $field){ ?>
               <td><?php echo get_custom_field_value($quote['id'],$field['id'],'quote'); ?></td>
             <?php } ?>
           </tr>
         <?php } ?>
       </tbody>
     </table>
   </div>
 </div>
