<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">

      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="row">
                     <div class="col-md-8">
                        <h4 class="no-margin"><?php echo _l('nedarimpay_dashboard'); ?></h4>
                     </div>
                     <div class="col-md-4 text-right">
                        <?php if (has_permission('nedarimpay', '', 'create')) : ?>
                        <a href="<?php echo admin_url('nedarimpay/manual_charge'); ?>" class="btn btn-info btn-sm">
                           <i class="fa fa-bolt"></i> <?php echo _l('nedarimpay_manual_charge'); ?>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo admin_url('nedarimpay/settings'); ?>" class="btn btn-default btn-sm">
                           <i class="fa fa-cog"></i> <?php echo _l('settings'); ?>
                        </a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <?php if ($pending_count > 0) : ?>
      <div class="row">
         <div class="col-md-12">
            <div class="alert alert-warning">
               <i class="fa fa-clock-o"></i>
               <?php echo sprintf(_l('nedarimpay_pending_alert'), $pending_count); ?>
            </div>
         </div>
      </div>
      <?php endif; ?>

      <?php if ($failed_count > 0) : ?>
      <div class="row">
         <div class="col-md-12">
            <div class="alert alert-danger">
               <i class="fa fa-exclamation-triangle"></i>
               <?php echo sprintf(_l('nedarimpay_failed_alert'), $failed_count); ?>
               &nbsp;
               <a href="<?php echo admin_url('nedarimpay/transactions?status=failed'); ?>" class="alert-link">
                  <?php echo _l('nedarimpay_view_failed'); ?> &rarr;
               </a>
            </div>
         </div>
      </div>
      <?php endif; ?>

      <!-- KPI Cards -->
      <div class="row">

         <div class="col-md-3 col-sm-6">
            <div class="panel_s">
               <div class="panel-body text-center">
                  <p class="text-muted no-margin"><?php echo _l('nedarimpay_total_transactions'); ?></p>
                  <h2 class="no-margin text-success bold"><?php echo $total_transactions; ?></h2>
               </div>
            </div>
         </div>

         <div class="col-md-3 col-sm-6">
            <div class="panel_s">
               <div class="panel-body text-center">
                  <p class="text-muted no-margin"><?php echo _l('nedarimpay_student_volume'); ?></p>
                  <h2 class="no-margin text-info bold">
                     ₪ <?php echo number_format($total_student_amount, 2); ?>
                  </h2>
                  <small class="text-muted"><span class="label label-info"><?php echo _l('nedarimpay_type_student'); ?></span></small>
               </div>
            </div>
         </div>

         <div class="col-md-3 col-sm-6">
            <div class="panel_s">
               <div class="panel-body text-center">
                  <p class="text-muted no-margin"><?php echo _l('nedarimpay_donation_volume'); ?></p>
                  <h2 class="no-margin text-primary bold">
                     ₪ <?php echo number_format($total_donation_amount, 2); ?>
                  </h2>
                  <small class="text-muted"><span class="label label-primary"><?php echo _l('nedarimpay_type_donation'); ?></span></small>
               </div>
            </div>
         </div>

         <div class="col-md-3 col-sm-6">
            <div class="panel_s">
               <div class="panel-body text-center">
                  <p class="text-muted no-margin"><?php echo _l('nedarimpay_standing_orders'); ?></p>
                  <h2 class="no-margin text-warning bold"><?php echo $active_keva; ?></h2>
                  <small class="text-muted">הוראת קבע (HK)</small>
               </div>
            </div>
         </div>

      </div>
      <!-- /KPI Cards -->

      <!-- Recent Transactions -->
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo _l('nedarimpay_recent_transactions'); ?></h4>
                  <hr class="hr-panel-heading" />

                  <?php if (empty($recent_transactions)) : ?>
                     <p class="text-muted"><?php echo _l('nedarimpay_no_transactions_yet'); ?></p>
                  <?php else : ?>
                  <div class="table-responsive">
                     <table class="table table-hover">
                        <thead>
                           <tr>
                              <th><?php echo _l('nedarimpay_receipt_number'); ?></th>
                              <th><?php echo _l('nedarimpay_client_name'); ?></th>
                              <th><?php echo _l('nedarimpay_type'); ?></th>
                              <th><?php echo _l('nedarimpay_amount'); ?></th>
                              <th><?php echo _l('nedarimpay_email_sent'); ?></th>
                              <th><?php echo _l('nedarimpay_date'); ?></th>
                              <th><?php echo _l('nedarimpay_status_col'); ?></th>
                              <th></th>
                           </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recent_transactions as $tx) : ?>
                           <tr>
                              <td><strong><?php echo htmlspecialchars($tx['receipt_number'] ?? '—'); ?></strong></td>
                              <td><?php echo htmlspecialchars($tx['client_name']); ?></td>
                              <td>
                                 <?php if ($tx['receipt_type'] === 'student') : ?>
                                    <span class="label label-info"><?php echo _l('nedarimpay_type_student'); ?></span>
                                 <?php else : ?>
                                    <span class="label label-primary"><?php echo _l('nedarimpay_type_donation'); ?></span>
                                 <?php endif; ?>
                              </td>
                              <td>
                                 <strong><?php echo number_format($tx['amount'], 2); ?></strong>
                                 <?php echo $tx['currency'] == 2 ? '$' : '₪'; ?>
                              </td>
                              <td class="text-center">
                                 <?php if ($tx['email_sent']) : ?>
                                    <i class="fa fa-check text-success"></i>
                                 <?php else : ?>
                                    <i class="fa fa-times text-danger"></i>
                                 <?php endif; ?>
                              </td>
                              <td><?php echo _dt($tx['created_at']); ?></td>
                              <td>
                                 <?php
                                 $badge_map = ['processed' => 'success', 'pending' => 'warning', 'failed' => 'danger', 'duplicate' => 'default'];
                                 $b = $badge_map[$tx['status']] ?? 'default';
                                 ?>
                                 <span class="label label-<?php echo $b; ?>">
                                    <?php echo _l('nedarimpay_status_' . $tx['status']); ?>
                                 </span>
                              </td>
                              <td>
                                 <a href="<?php echo admin_url('nedarimpay/transaction_detail/' . $tx['id']); ?>"
                                    class="btn btn-xs btn-default">
                                    <i class="fa fa-eye"></i>
                                 </a>
                              </td>
                           </tr>
                        <?php endforeach; ?>
                        </tbody>
                     </table>
                  </div>
                  <a href="<?php echo admin_url('nedarimpay/transactions'); ?>" class="btn btn-default btn-sm">
                     <?php echo _l('nedarimpay_view_all_transactions'); ?> &rarr;
                  </a>
                  <?php endif; ?>

               </div>
            </div>
         </div>
      </div>
      <!-- /Recent Transactions -->

   </div>
</div>
<?php init_tail(); ?>
