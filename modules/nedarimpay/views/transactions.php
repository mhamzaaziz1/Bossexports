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
                        <h4 class="no-margin"><?php echo _l('nedarimpay_transactions'); ?></h4>
                     </div>
                     <div class="col-md-4 text-right">
                        <a href="<?php echo admin_url('nedarimpay/dashboard'); ?>" class="btn btn-default btn-sm">
                           <i class="fa fa-arrow-left"></i> <?php echo _l('nedarimpay_dashboard'); ?>
                        </a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Filters -->
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <?php echo form_open(admin_url('nedarimpay/transactions'), ['method' => 'get', 'class' => 'form-inline']); ?>
                  <div class="row">

                     <div class="col-md-2">
                        <div class="form-group">
                           <label class="control-label"><?php echo _l('nedarimpay_filter_type'); ?></label>
                           <select name="receipt_type" class="form-control input-sm">
                              <option value=""><?php echo _l('nedarimpay_all_types'); ?></option>
                              <option value="student"  <?php echo ($filters['receipt_type'] === 'student')  ? 'selected' : ''; ?>>
                                 <?php echo _l('nedarimpay_type_student'); ?>
                              </option>
                              <option value="donation" <?php echo ($filters['receipt_type'] === 'donation') ? 'selected' : ''; ?>>
                                 <?php echo _l('nedarimpay_type_donation'); ?>
                              </option>
                           </select>
                        </div>
                     </div>

                     <div class="col-md-2">
                        <div class="form-group">
                           <label class="control-label"><?php echo _l('nedarimpay_filter_status'); ?></label>
                           <select name="status" class="form-control input-sm">
                              <option value=""><?php echo _l('nedarimpay_all_statuses'); ?></option>
                              <?php foreach (['processed', 'pending', 'failed', 'duplicate'] as $s) : ?>
                              <option value="<?php echo $s; ?>" <?php echo ($filters['status'] === $s) ? 'selected' : ''; ?>>
                                 <?php echo _l('nedarimpay_status_' . $s); ?>
                              </option>
                              <?php endforeach; ?>
                           </select>
                        </div>
                     </div>

                     <div class="col-md-2">
                        <div class="form-group">
                           <label class="control-label"><?php echo _l('nedarimpay_date_from'); ?></label>
                           <input type="date" name="date_from" class="form-control input-sm"
                                  value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>">
                        </div>
                     </div>

                     <div class="col-md-2">
                        <div class="form-group">
                           <label class="control-label"><?php echo _l('nedarimpay_date_to'); ?></label>
                           <input type="date" name="date_to" class="form-control input-sm"
                                  value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>">
                        </div>
                     </div>

                     <div class="col-md-3">
                        <div class="form-group">
                           <label class="control-label"><?php echo _l('search'); ?></label>
                           <input type="text" name="search" class="form-control input-sm"
                                  value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                                  placeholder="<?php echo _l('nedarimpay_search_placeholder'); ?>">
                        </div>
                     </div>

                     <div class="col-md-1">
                        <div class="form-group">
                           <label class="control-label">&nbsp;</label><br>
                           <button type="submit" class="btn btn-info btn-sm">
                              <i class="fa fa-search"></i>
                           </button>
                           <?php if (array_filter($filters)) : ?>
                           <a href="<?php echo admin_url('nedarimpay/transactions'); ?>" class="btn btn-default btn-sm" title="<?php echo _l('clear'); ?>">
                              <i class="fa fa-times"></i>
                           </a>
                           <?php endif; ?>
                        </div>
                     </div>

                  </div>
                  <?php echo form_close(); ?>
               </div>
            </div>
         </div>
      </div>
      <!-- /Filters -->

      <!-- Table -->
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">

                  <?php if (empty($transactions)) : ?>
                     <p class="text-center text-muted"><?php echo _l('nedarimpay_no_transactions_found'); ?></p>
                  <?php else : ?>

                  <div class="table-responsive">
                     <table class="table table-hover dt-table">
                        <thead>
                           <tr>
                              <th><?php echo _l('nedarimpay_receipt_number'); ?></th>
                              <th><?php echo _l('nedarimpay_type'); ?></th>
                              <th><?php echo _l('nedarimpay_client_name'); ?></th>
                              <th><?php echo _l('nedarimpay_email'); ?></th>
                              <th><?php echo _l('nedarimpay_amount'); ?></th>
                              <th><?php echo _l('nedarimpay_transaction_type'); ?></th>
                              <th class="text-center"><?php echo _l('nedarimpay_email_sent'); ?></th>
                              <th><?php echo _l('nedarimpay_date'); ?></th>
                              <th><?php echo _l('nedarimpay_status_col'); ?></th>
                              <th class="not-export"></th>
                           </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($transactions as $tx) : ?>
                           <tr>
                              <td>
                                 <strong><?php echo htmlspecialchars($tx['receipt_number'] ?? '—'); ?></strong>
                              </td>
                              <td>
                                 <?php if ($tx['receipt_type'] === 'student') : ?>
                                    <span class="label label-info"><?php echo _l('nedarimpay_type_student'); ?></span>
                                 <?php else : ?>
                                    <span class="label label-primary"><?php echo _l('nedarimpay_type_donation'); ?></span>
                                 <?php endif; ?>
                              </td>
                              <td>
                                 <?php if ($tx['perfex_client_id']) : ?>
                                    <a href="<?php echo admin_url('clients/client/' . $tx['perfex_client_id']); ?>">
                                       <?php echo htmlspecialchars($tx['client_name']); ?>
                                    </a>
                                 <?php else : ?>
                                    <?php echo htmlspecialchars($tx['client_name']); ?>
                                 <?php endif; ?>
                              </td>
                              <td><?php echo htmlspecialchars($tx['email']); ?></td>
                              <td>
                                 <strong><?php echo number_format($tx['amount'], 2); ?></strong>
                                 <?php echo $tx['currency'] == 2 ? '$' : '₪'; ?>
                              </td>
                              <td>
                                 <small class="text-muted"><?php echo htmlspecialchars($tx['transaction_type'] ?? ''); ?></small>
                              </td>
                              <td class="text-center">
                                 <?php if ($tx['email_sent']) : ?>
                                    <i class="fa fa-check-circle text-success"
                                       title="<?php echo _dt($tx['email_sent_at']); ?>"></i>
                                 <?php else : ?>
                                    <i class="fa fa-times-circle text-danger"></i>
                                 <?php endif; ?>
                              </td>
                              <td><?php echo _dt($tx['created_at']); ?></td>
                              <td>
                                 <?php
                                 $bmap = ['processed' => 'success', 'pending' => 'warning', 'failed' => 'danger', 'duplicate' => 'default'];
                                 $b    = $bmap[$tx['status']] ?? 'default';
                                 ?>
                                 <span class="label label-<?php echo $b; ?>">
                                    <?php echo _l('nedarimpay_status_' . $tx['status']); ?>
                                 </span>
                              </td>
                              <td>
                                 <div class="btn-group">
                                    <a href="<?php echo admin_url('nedarimpay/transaction_detail/' . $tx['id'] . ($tx['makor'] === 'manual_charge' ? '?src=mc' : '')); ?>"
                                       class="btn btn-xs btn-default"
                                       data-toggle="tooltip" title="<?php echo _l('nedarimpay_view_detail'); ?>">
                                       <i class="fa fa-eye"></i>
                                    </a>
                                    <?php if ($tx['perfex_invoice_id']) : ?>
                                    <a href="<?php echo admin_url('invoices/list_invoices/' . $tx['perfex_invoice_id']); ?>"
                                       class="btn btn-xs btn-default"
                                       data-toggle="tooltip" title="<?php echo _l('nedarimpay_view_invoice'); ?>"
                                       target="_blank">
                                       <i class="fa fa-file-text-o"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (!$tx['email_sent'] && !empty($tx['email'])) : ?>
                                    <a href="<?php echo admin_url('nedarimpay/resend_email/' . $tx['id']); ?>"
                                       class="btn btn-xs btn-info"
                                       data-toggle="tooltip" title="<?php echo _l('nedarimpay_resend_email'); ?>">
                                       <i class="fa fa-paper-plane-o"></i>
                                    </a>
                                    <?php endif; ?>
                                 </div>
                              </td>
                           </tr>
                        <?php endforeach; ?>
                        </tbody>
                     </table>
                  </div>

                  <?php endif; ?>

               </div>
            </div>
         </div>
      </div>
      <!-- /Table -->

   </div>
</div>
<?php init_tail(); ?>
