<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('Sales Decision Support'); ?>">
   <div class="panel_s">
      <div class="panel-body">
         <div class="widget-dragger"></div>
         <h4 class="pull-left mtop5"><?php echo _l('Sales Decision Support System'); ?></h4>
         <div class="clearfix"></div>
         <hr class="hr-panel-heading-dashboard">
         
         <?php
            // Load Models
            $CI = &get_instance();
            $CI->load->model('invoices_model');
            $CI->load->model('proposals_model');
            $CI->load->model('estimates_model');
            $CI->load->model('leads_model');

            // --- 1. PREDICTED REVENUE CALCULATION ---
            // Formula: (Open Proposals * 20%) + (Sent Estimates * 50%) + (Unpaid Invoices * 100%)
            
            // Proposals (Open/Sent)
            $proposals_open = total_rows(db_prefix().'proposals', 'status IN (1,4,6)'); // Open, Sent, Revised
            $proposals_val_query = $CI->db->select_sum('total')->where_in('status', [1,4,6])->get(db_prefix().'proposals')->row();
            $proposals_value = $proposals_val_query ? $proposals_val_query->total : 0;
            
            // Estimates (Sent/Accepted but not invoiced yet? simpler to just take Sent)
            $estimates_sent = total_rows(db_prefix().'estimates', 'status = 2'); // Sent
            $estimates_val_query = $CI->db->select_sum('total')->where('status', 2)->get(db_prefix().'estimates')->row();
            $estimates_value = $estimates_val_query ? $estimates_val_query->total : 0;

            // Unpaid Invoices
            $invoices_unpaid = total_rows(db_prefix().'invoices', 'status IN (1,3,4)'); // Unpaid, Partial, Overdue
            $invoices_val_query = $CI->db->select_sum('total')->where_in('status', [1,3,4])->get(db_prefix().'invoices')->row();
            $invoices_value = $invoices_val_query ? $invoices_val_query->total : 0;

            $weighted_forecast = ($proposals_value * 0.20) + ($estimates_value * 0.50) + ($invoices_value * 1.0);
            $base_currency = get_base_currency();
         ?>

         <div class="row">
            <!-- Forecast Card -->
            <div class="col-md-4">
               <div class="panel_s" style="background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%); color: white; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                  <div class="panel-body text-center">
                     <h5 style="color: rgba(255,255,255,0.8); margin-top:0;">Predicted Next 30 Days Revenue</h5>
                     <h2 style="font-weight: bold; margin: 10px 0; color: white;">
                        <?php echo app_format_money($weighted_forecast, $base_currency); ?>
                     </h2>
                     <p style="font-size: 11px; opacity: 0.7;">Based on weighted pipeline probability</p>
                  </div>
               </div>
            </div>

            <!-- Funnel Mini-View -->
            <div class="col-md-8">
               <div class="row text-center mtop10">
                  <div class="col-xs-3">
                     <h4 class="bold text-muted"><?php echo total_rows(db_prefix().'leads'); ?></h4>
                     <span class="text-info">Leads</span>
                  </div>
                  <div class="col-xs-1 text-muted" style="padding-top: 10px;"><i class="fa fa-angle-right"></i></div>
                  <div class="col-xs-3">
                     <h4 class="bold text-muted"><?php echo total_rows(db_prefix().'proposals'); ?></h4>
                     <span class="text-warning">Proposals</span>
                  </div>
                  <div class="col-xs-1 text-muted" style="padding-top: 10px;"><i class="fa fa-angle-right"></i></div>
                  <div class="col-xs-3">
                     <h4 class="bold text-success"><?php echo total_rows(db_prefix().'invoices', 'status=2'); // Paid ?></h4>
                     <span class="text-success">Won</span>
                  </div>
               </div>
               <div class="progress mtop15" style="height: 10px;">
                  <?php 
                     $total_leads = total_rows(db_prefix().'leads');
                     $won = total_rows(db_prefix().'invoices', 'status=2');
                     $conversion = ($total_leads > 0) ? ($won / $total_leads) * 100 : 0;
                  ?>
                  <div class="progress-bar progress-bar-success" style="width: <?php echo $conversion; ?>%"></div>
               </div>
               <p class="text-center text-muted mtop5">Overall Conversion Rate: <b><?php echo number_format($conversion, 1); ?>%</b></p>
            </div>
         </div>

         <hr class="hr-10" />

         <!-- ACTIONABLE INSIGHTS -->
         <h4 class="text-danger"><i class="fa fa-bolt"></i> Urgent Actions Required</h4>
         <div class="table-responsive">
            <table class="table table-bordered table-condensed">
               <thead>
                  <tr>
                     <th>Insight</th>
                     <th>Impact</th>
                     <th>Action</th>
                  </tr>
               </thead>
               <tbody>
                  <?php
                     // Insight 1: High Value Expiring Proposals
                     $next_week = date('Y-m-d', strtotime('+7 days'));
                     $expiring_proposals = $CI->db->where('open_till <=', $next_week)
                                                ->where('open_till >=', date('Y-m-d'))
                                                ->where('status', 1) // Open
                                                ->order_by('total', 'DESC')
                                                ->limit(3)
                                                ->get(db_prefix().'proposals')
                                                ->result();
                     
                     foreach($expiring_proposals as $prop) {
                  ?>
                  <tr>
                     <td>Proposal #<?php echo $prop->id; ?> for <?php echo $prop->proposal_to; ?> is expiring soon.</td>
                     <td class="text-warning">Potentially lost <?php echo app_format_money($prop->total, $base_currency); ?></td>
                     <td><a href="<?php echo admin_url('proposals/list_proposals/'.$prop->id); ?>" class="btn btn-xs btn-default">Follow Up</a></td>
                  </tr>
                  <?php } ?>

                  <?php
                     // Insight 2: Overdue Invoices High Value
                     $overdue_invoices = $CI->db->where('status', 4) // Overdue
                                                ->order_by('total', 'DESC')
                                                ->limit(3)
                                                ->get(db_prefix().'invoices')
                                                ->result();
                     foreach($overdue_invoices as $inv) {
                  ?>
                  <tr>
                     <td>Invoice #<?php echo $inv->number; ?> is Overdue.</td>
                     <td class="text-danger">Recover <?php echo app_format_money($inv->total, $base_currency); ?></td>
                     <td><a href="<?php echo admin_url('invoices/list_invoices/'.$inv->id); ?>" class="btn btn-xs btn-info">Resend</a></td>
                  </tr>
                  <?php } ?>
               </tbody>
            </table>
         </div>
      </div>
   </div>
</div>
