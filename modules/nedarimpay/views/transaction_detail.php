<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">

      <!-- Page Header -->
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="row">
                     <div class="col-md-8">
                        <h4 class="no-margin">
                           <?php echo _l('nedarimpay_transaction_detail'); ?>
                           <?php if (!empty($transaction['receipt_number'])) : ?>
                           &nbsp;<small class="text-muted"><?php echo htmlspecialchars($transaction['receipt_number']); ?></small>
                           <?php endif; ?>
                        </h4>
                     </div>
                     <div class="col-md-4 text-right">
                        <a href="<?php echo admin_url('nedarimpay/transactions'); ?>" class="btn btn-default btn-sm">
                           <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
                        </a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <div class="row">

         <!-- LEFT: Transaction Info -->
         <div class="col-md-8">

            <div class="panel_s">
               <div class="panel-body">

                  <div class="row">
                     <div class="col-md-6">
                        <h4 class="no-margin">
                           <?php if ($transaction['receipt_type'] === 'student') : ?>
                              <span class="label label-info"><?php echo _l('nedarimpay_type_student'); ?></span>
                           <?php else : ?>
                              <span class="label label-primary"><?php echo _l('nedarimpay_type_donation'); ?></span>
                           <?php endif; ?>
                        </h4>
                     </div>
                     <div class="col-md-6 text-right">
                        <?php
                        $bmap = ['processed' => 'success', 'pending' => 'warning', 'failed' => 'danger', 'duplicate' => 'default'];
                        $b    = $bmap[$transaction['status']] ?? 'default';
                        ?>
                        <span class="label label-<?php echo $b; ?> label-lg">
                           <?php echo _l('nedarimpay_status_' . $transaction['status']); ?>
                        </span>
                     </div>
                  </div>

                  <hr class="hr-panel-heading" />

                  <div class="row">

                     <div class="col-md-6">
                        <table class="table table-striped no-margin">
                           <tbody>
                              <tr>
                                 <td class="bold"><?php echo _l('nedarimpay_receipt_number'); ?></td>
                                 <td><strong><?php echo htmlspecialchars($transaction['receipt_number'] ?? '—'); ?></strong></td>
                              </tr>
                              <tr>
                                 <td class="bold"><?php echo _l('nedarimpay_transaction_id_label'); ?></td>
                                 <td><code><?php echo htmlspecialchars($transaction['transaction_id']); ?></code></td>
                              </tr>
                              <tr>
                                 <td class="bold"><?php echo _l('nedarimpay_amount'); ?></td>
                                 <td>
                                    <strong class="text-success" style="font-size:18px;">
                                       <?php echo number_format($transaction['amount'], 2); ?>
                                       <?php echo $transaction['currency'] == 2 ? ' USD' : ' ₪'; ?>
                                    </strong>
                                 </td>
                              </tr>
                              <tr>
                                 <td class="bold"><?php echo _l('nedarimpay_transaction_type'); ?></td>
                                 <td><?php echo htmlspecialchars($transaction['transaction_type'] ?? '—'); ?></td>
                              </tr>
                              <tr>
                                 <td class="bold"><?php echo _l('nedarimpay_tashloumim'); ?></td>
                                 <td><?php echo htmlspecialchars($transaction['tashloumim'] ?? '1'); ?></td>
                              </tr>
                              <tr>
                                 <td class="bold"><?php echo _l('nedarimpay_transaction_time'); ?></td>
                                 <td><?php echo _dt($transaction['transaction_time']); ?></td>
                              </tr>
                           </tbody>
                        </table>
                     </div>

                     <div class="col-md-6">
                        <table class="table table-striped no-margin">
                           <tbody>
                              <tr>
                                 <td class="bold"><?php echo _l('nedarimpay_client_name'); ?></td>
                                 <td>
                                    <?php if ($transaction['perfex_client_id']) : ?>
                                       <a href="<?php echo admin_url('clients/client/' . $transaction['perfex_client_id']); ?>">
                                          <?php echo htmlspecialchars($transaction['client_name']); ?>
                                       </a>
                                    <?php else : ?>
                                       <?php echo htmlspecialchars($transaction['client_name']); ?>
                                    <?php endif; ?>
                                 </td>
                              </tr>
                              <tr>
                                 <td class="bold"><?php echo _l('nedarimpay_email'); ?></td>
                                 <td><?php echo htmlspecialchars($transaction['email']); ?></td>
                              </tr>
                              <tr>
                                 <td class="bold"><?php echo _l('nedarimpay_phone'); ?></td>
                                 <td><?php echo htmlspecialchars($transaction['phone']); ?></td>
                              </tr>
                              <tr>
                                 <td class="bold"><?php echo _l('nedarimpay_groupe'); ?></td>
                                 <td><?php echo htmlspecialchars($transaction['groupe'] ?? '—'); ?></td>
                              </tr>
                              <tr>
                                 <td class="bold"><?php echo _l('nedarimpay_card_last_4'); ?></td>
                                 <td>
                                    <?php echo $transaction['last_num'] ? '••••&nbsp;' . htmlspecialchars($transaction['last_num']) : '—'; ?>
                                 </td>
                              </tr>
                              <tr>
                                 <td class="bold"><?php echo _l('nedarimpay_confirmation'); ?></td>
                                 <td><?php echo htmlspecialchars($transaction['confirmation'] ?? '—'); ?></td>
                              </tr>
                           </tbody>
                        </table>
                     </div>

                  </div>

                  <?php if (!empty($transaction['comments'])) : ?>
                  <div class="alert alert-default mtop10">
                     <strong><?php echo _l('nedarimpay_comments'); ?>:</strong>
                     <?php echo htmlspecialchars($transaction['comments']); ?>
                  </div>
                  <?php endif; ?>

                  <?php if (!empty($transaction['error_message'])) : ?>
                  <div class="alert alert-danger mtop10">
                     <strong><?php echo _l('nedarimpay_error'); ?>:</strong>
                     <?php echo htmlspecialchars($transaction['error_message']); ?>
                  </div>
                  <?php endif; ?>

               </div>
            </div>

            <!-- Raw Payload -->
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin">
                     <i class="fa fa-code"></i>
                     <?php echo _l('nedarimpay_raw_payload'); ?>
                     <a class="pull-right text-muted small" data-toggle="collapse" href="#raw-payload-body" style="font-size:13px;">
                        <?php echo _l('nedarimpay_toggle'); ?> <i class="fa fa-chevron-down"></i>
                     </a>
                  </h4>
                  <hr class="hr-panel-heading" />
                  <div id="raw-payload-body" class="collapse">
                     <pre style="max-height:300px;overflow:auto;font-size:12px;background:#f8f8f8;padding:10px;border-radius:4px;"><?php
                        $raw = json_decode($transaction['raw_payload'] ?? '{}', true);
                        echo htmlspecialchars(json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                     ?></pre>
                  </div>
               </div>
            </div>

         </div>
         <!-- /LEFT -->

         <!-- RIGHT: Actions & Status -->
         <div class="col-md-4">

            <!-- Actions -->
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo _l('nedarimpay_actions'); ?></h4>
                  <hr class="hr-panel-heading" />

                  <?php if ($transaction['perfex_invoice_id']) : ?>
                  <a href="<?php echo admin_url('invoices/list_invoices/' . $transaction['perfex_invoice_id']); ?>"
                     class="btn btn-default btn-block mbot10" target="_blank">
                     <i class="fa fa-file-text-o"></i> <?php echo _l('nedarimpay_view_invoice'); ?>
                  </a>
                  <?php endif; ?>

                  <?php if (!empty($transaction['email'])) : ?>
                  <a href="<?php echo admin_url('nedarimpay/resend_email/' . $transaction['id']); ?>"
                     class="btn btn-info btn-block mbot10"
                     onclick="return confirm('<?php echo _l('nedarimpay_confirm_resend'); ?>')">
                     <i class="fa fa-paper-plane-o"></i> <?php echo _l('nedarimpay_resend_email'); ?>
                  </a>
                  <?php endif; ?>

                  <a href="<?php echo admin_url('nedarimpay/transactions'); ?>" class="btn btn-default btn-block">
                     <i class="fa fa-list"></i> <?php echo _l('nedarimpay_transactions'); ?>
                  </a>

               </div>
            </div>

            <!-- Email Status -->
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo _l('nedarimpay_email_status'); ?></h4>
                  <hr class="hr-panel-heading" />

                  <?php if ($transaction['email_sent']) : ?>
                     <p>
                        <i class="fa fa-check-circle fa-2x text-success"></i>
                        &nbsp;<span class="text-success bold"><?php echo _l('nedarimpay_email_sent_ok'); ?></span>
                     </p>
                     <p class="text-muted">
                        <small><i class="fa fa-clock-o"></i> <?php echo _dt($transaction['email_sent_at']); ?></small>
                     </p>
                     <p class="text-muted">
                        <i class="fa fa-envelope-o"></i> <?php echo htmlspecialchars($transaction['email']); ?>
                     </p>
                  <?php else : ?>
                     <p>
                        <i class="fa fa-times-circle fa-2x text-danger"></i>
                        &nbsp;<span class="text-danger bold"><?php echo _l('nedarimpay_email_not_sent'); ?></span>
                     </p>
                     <?php if (!empty($transaction['email'])) : ?>
                     <p class="text-muted">
                        <i class="fa fa-envelope-o"></i> <?php echo htmlspecialchars($transaction['email']); ?>
                     </p>
                     <?php endif; ?>
                  <?php endif; ?>
               </div>
            </div>

            <!-- Nedarim IDs -->
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo _l('nedarimpay_ids_panel'); ?></h4>
                  <hr class="hr-panel-heading" />
                  <table class="table no-margin">
                     <tr>
                        <td class="text-muted"><?php echo _l('nedarimpay_nedarim_client_id'); ?></td>
                        <td><code><?php echo htmlspecialchars($transaction['client_id_nedarim'] ?? '—'); ?></code></td>
                     </tr>
                     <?php if ($transaction['keva_id']) : ?>
                     <tr>
                        <td class="text-muted">KevaId</td>
                        <td><code><?php echo htmlspecialchars($transaction['keva_id']); ?></code></td>
                     </tr>
                     <?php endif; ?>
                     <tr>
                        <td class="text-muted">Shovar</td>
                        <td><?php echo htmlspecialchars($transaction['shovar'] ?? '—'); ?></td>
                     </tr>
                     <tr>
                        <td class="text-muted">Makor</td>
                        <td><?php echo htmlspecialchars($transaction['makor'] ?? '—'); ?></td>
                     </tr>
                     <tr>
                        <td class="text-muted">MasofId</td>
                        <td><?php echo htmlspecialchars($transaction['masof_id'] ?? '—'); ?></td>
                     </tr>
                  </table>
               </div>
            </div>

         </div>
         <!-- /RIGHT -->

      </div>

   </div>
</div>
<?php init_tail(); ?>
