<!--<div class="col-md-12" id="small-table">-->
<!--	<div class="row">-->
<!--      <h4 class="no-margin font-bold"><i class="fa fa-file-text-o" aria-hidden="true"></i> <?php echo _l('payments'); ?></h4>-->
<!--      <hr />-->
<!--  	</div>  	-->
<!--    <br>-->
<!--    <table class="table dt-table">-->
<!--       <thead>-->
<!--       	<th><?php echo _l('purchase_order'); ?></th>-->
<!--         <th><?php echo _l('payments_table_amount_heading'); ?></th>-->
<!--          <th><?php echo _l('payments_table_mode_heading'); ?></th>-->
<!--          <th><?php echo _l('payment_transaction_id'); ?></th>-->
<!--          <th><?php echo _l('payments_table_date_heading'); ?></th>-->
<!--       </thead>-->
<!--      <tbody>-->
<!--         <?php foreach($payments as $p) { ?>-->
<!--         	<td><a href="<?php echo admin_url('purchase/purchase_order/' . $p['pur_order']); ?>" ><?php echo html_entity_decode($p['pur_order_name']); ?></a></td>-->
<!--         	<td><?php echo app_format_money($p['amount'],''); ?></td>-->
<!--         	<td><?php echo get_payment_mode_by_id($p['paymentmode']); ?></td>-->
<!--         	<td><?php echo html_entity_decode($p['transactionid']); ?></td>-->
<!--         	<td><?php echo _d($p['date']); ?></td>-->
<!--         <?php } ?>-->
<!--      </tbody>-->
<!--   </table>	-->
<!--</div>-->
<div class="form-group select-placeholder col-md-4" id="GFG">
  <select
  class="selectpicker"
  name="range"
  id="range"
  data-width="100%" onchange="render_customer_statement(<?php echo $id ?>)">
  <option value='<?php echo json_encode(
 	array(
 	_d(date('Y-m-d',strtotime(date('2015-01-01')))),
 	_d(date('Y-m-d'))
 	)); ?>'selected>
 	<?php echo _l('All'); ?>
 </option>
  <option value='<?php echo json_encode(
    array(
      _d(date('Y-m-d')),
      _d(date('Y-m-d'))
    )); ?>'>
    <?php echo _l('today'); ?>
  </option>
  <option value='<?php echo json_encode(
    array(
      _d(date('Y-m-d', strtotime('monday this week'))),
      _d(date('Y-m-d', strtotime('sunday this week')))
    )); ?>'>
    <?php echo _l('this_week'); ?>
  </option>
  <option value='<?php echo json_encode(
    array(
      _d(date('Y-m-01')),
      _d(date('Y-m-t'))
    )); ?>' >
    <?php echo _l('this_month'); ?>
  </option>
  <option value='<?php echo json_encode(
    array(
      _d(date('Y-m-01', strtotime("-1 MONTH"))),
      _d(date('Y-m-t', strtotime('-1 MONTH')))
    )); ?>'>
    <?php echo _l('last_month'); ?>
  </option>
  <option value='<?php echo json_encode(
    array(
      _d(date('Y-m-d',strtotime(date('Y-01-01')))),
      _d(date('Y-m-d',strtotime(date('Y-12-31'))))
    )); ?>'>
    <?php echo _l('this_year'); ?>
  </option>
  <option value='<?php echo json_encode(
    array(
      _d(date('Y-m-d',strtotime(date(date('Y',strtotime('last year')).'-01-01')))),
      _d(date('Y-m-d',strtotime(date(date('Y',strtotime('last year')). '-12-31'))))
    )); ?>'>
    <?php echo _l('last_year'); ?>
  </option>
  <option value="period"><?php echo _l('period_datepicker'); ?></option>
  <option value='<?php echo json_encode(
    array(
      _d(date('Y-m-d',strtotime(date('2015-01-01')))),
      _d(date('Y-m-d',strtotime(date('2099-12-31'))))
    )); ?>'>
    <?php echo _l('Custom'); ?>
</select>
</div>
<div>
    <a href="<?php echo admin_url('purchase/vendor/'.$id.'?group=pdf_Statement&id='.$id.'&from='.$from.'&to='.$to); ?>" onclick="printDiv();return false;" class="btn btn-info pull-left new new-invoice-list mright5">Print</a>
</div>
<div class="col-md-12">
  <div class="text-right">
    <h4 class="no-margin bold"><?php echo _l('account_summary'); ?></h4>
    <p class="text-muted"><?php echo _l('statement_from_to',array($from,$to)); ?></p>
    <hr />
    <table class="table statement-account-summary">
      <tbody>
        <tr>
          <td class="text-left"><?php echo _l('statement_beginning_balance'); ?>:</td>
          <td><?php echo app_format_money($statement['beginning_balance'], $statement['currency']); ?></td>
        </tr>
        <tr>
          <td class="text-left"><?php echo _l('invoiced_amount'); ?>:</td>
          <td><?php echo app_format_money($statement['invoiced_amount'], $statement['currency']); ?></td>
        </tr>
        <tr>
          <td class="text-left"><?php echo _l('amount_paid'); ?>:</td>
          <td><?php echo app_format_money($statement['amount_paid'], $statement['currency']); ?></td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td class="text-left"><b><?php echo _l('balance_due'); ?></b>:</td>
          <td><?php echo app_format_money($statement['balance_due'], $statement['currency']); ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<div class="row mtop15">
  <form action="<?php echo admin_url('purchase/vendor/'.$id); ?>" method="get">
    <div class="col-md-4 hide">
      <?php echo render_input('group','','Statement'); ?>
    </div>
    <div class="col-md-4 hide">
      <?php echo render_input('id','',$id); ?>
    </div>
    <div class="col-md-4">
      <?php echo render_date_input('from','',''); ?>
    </div>
    <div class="col-md-4 ">
      <?php echo render_date_input('to','',''); ?>
    </div>
    <div class="col-md-4">
      <button type="submit" class="btn btn-info pull-left new new-invoice-list mright5">fetch</button>
    </div>
  </form>
</div>
<div class="col-md-12">
  <div class="text-center bold padding-10">
    <?php echo _l('customer_statement_info',array($from,$to)); ?>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered table-striped">
     <thead>
       <tr>
         <th><b><?php echo _l('statement_heading_date'); ?></b></th>
         <th><b><?php echo _l('statement_heading_details'); ?></b></th>
         <th class="text-right"><b><?php echo _l('Debit'); ?></b></th>
         <th class="text-right"><b><?php echo _l('credit'); ?></b></b></th>
         <th class="text-right"><b><?php echo _l('statement_heading_balance'); ?></b></b></th>
       </tr>
     </thead>
     <tbody>
       <tr>
         <td><?php echo $from; ?></td>
         <td><?php echo _l('statement_beginning_balance'); ?></td>
         <td class="text-right"><?php echo app_format_money($statement['beginning_balance'], $statement['currency'], true); ?></td>
         <td></td>
         <td class="text-right"><?php echo app_format_money($statement['beginning_balance'], $statement['currency'], true); ?></td>
       </tr>
       <?php
       $tmpBeginningBalance = $statement['beginning_balance'];
       sort($statement['result'], function ($a, $b) {
    return strtotime($a) - strtotime($b);
});
       foreach($statement['result'] as $data){ ?>
         <tr>
           <td><?php echo _d($data['date']); ?></td>
           <td>
            <?php
            if(isset($data['invoice_id'])) {
                $sql_query = 'SELECT * FROM ' . db_prefix() . 'pur_orders
        WHERE tblpur_orders.id = ' . $data['invoice_id'];
        $purchase = $this->db->query($sql_query)->result_array();
                if($data['invoice_amount'] >0){
                  echo '<a href="'.admin_url('purchase/purchase_order/'.$data['invoice_id']).'" target="_blank">Purchase '. $purchase[0]['pur_order_number'].'-'. $purchase[0]['pur_order_name'].'</a>';
                }else{
                    echo '<a href="'.admin_url('purchase/purchase_order/'.$data['invoice_id']).'" target="_blank">Purchase Return '. $purchase[0]['pur_order_number'].'</a>';
                }
            } else if(isset($data['payment_id'])){
                $sql_query = 'SELECT * FROM ' . db_prefix() . 'pur_orders
        WHERE tblpur_orders.id = ' . $data['payment_invoice_id'];
        $purchase = $this->db->query($sql_query)->result_array();
             echo '<a href="#" target="_blank">Payment '.'#'.$data['payment_id'].'</a> to Purchase '. $purchase[0]['pur_order_number'].'';
           }
          ?>
        </td>
        <td class="text-right">
          <?php
          if(isset($data['invoice_id'])) {
            echo app_format_money($data['invoice_amount'], $statement['currency'], true);
          } else if(isset($data['credit_note_id'])) {
            echo app_format_money($data['credit_note_amount'], $statement['currency'], true);
          }
          ?>
        </td>
        <td class="text-right">
          <?php
          if ($data['invoice_amount'] <1 && isset($data['invoice_id'])){
            echo app_format_money($data['payment_total'], $statement['currency'], true);
        }
          if(isset($data['payment_id'])) {
              
              
            echo app_format_money($data['payment_total'], $statement['currency'], true);
          } else if(isset($data['credit_note_refund_id'])) {
            echo app_format_money($data['refund_amount'], $statement['currency'], true);
          }
          ?>
        </td>
        <td class="text-right">
          <?php
          if(isset($data['invoice_id'])) {
            $tmpBeginningBalance = ($tmpBeginningBalance + $data['invoice_amount']);
          } else if(isset($data['invoice_id'])){
              $tmpBeginningBalance = ($tmpBeginningBalance - $data['payment_total']);
          } else if(isset($data['payment_id'])){
            $tmpBeginningBalance = ($tmpBeginningBalance - $data['payment_total']);
          } else if(isset($data['credit_note_id'])) {
            $tmpBeginningBalance = ($tmpBeginningBalance - $data['credit_note_amount']);
          } else if(isset($data['credit_note_refund_id'])) {
            $tmpBeginningBalance = ($tmpBeginningBalance + $data['refund_amount']);
          }
          if(!isset($data['credit_id'])){
              echo app_format_money($tmpBeginningBalance, $statement['currency'], true);
          }
          ?>
        </td>
      </tr>
    <?php } ?>
  </tbody>
  <tfoot class="statement_tfoot">
   <tr>
     <td colspan="3" class="text-right">
       <b><?php echo _l('balance_due'); ?></b>
     </td>
     <td class="text-right" colspan="2">
       <b><?php echo app_format_money($statement['balance_due'], $statement['currency']); ?></b>
     </td>
   </tr>
 </tfoot>
</table>
</div>
</div>
 <script>
//         function printDiv() {
//             var divContents = document.getElementById("GFG").innerHTML;
//             var a = window.open('', '', 'height=500, width=500');
//             a.document.write('<html>');
//             a.document.write('<body > <h1>Div contents are <br>');
//             a.document.write(divContents);
//             a.document.write('</body></html>');
//             a.document.close();
//             a.print();
//         }
//     </script>