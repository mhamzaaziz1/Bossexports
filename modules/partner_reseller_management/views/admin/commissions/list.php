<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
  <h4 class="no-margin"><?= _l('prm_commissions') ?></h4><hr/>
  <div class="panel panel-default"><div class="panel-body">
    <?php render_datatable([
        _l('prm_code'),
        _l('prm_partner'),
        _l('prm_kind'),
        _l('prm_amount'),
        _l('prm_status'),
        _l('prm_date'),
        '',
    ], 'commissions'); ?>
  </div></div>
</div></div>

<div class="modal fade" id="clawback_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <?= form_open(admin_url('partner_reseller_management/commissions/clawback'), ['id' => 'clawback_form']) ?>
      <input type="hidden" name="commission_id" id="clawback_commission_id">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?= _l('prm_clawback') ?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="reason_code"><?= _l('prm_reason_code') ?></label>
          <select name="reason_code" id="reason_code" class="form-control">
            <?php foreach (['refund','cancellation','chargeback','dispute','fraud','other'] as $rc): ?>
              <option value="<?= $rc ?>"><?= ucfirst($rc) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="reason"><?= _l('prm_reason') ?></label>
          <textarea name="reason" id="reason" class="form-control" rows="4" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close') ?></button>
        <button type="submit" class="btn btn-danger"><?= _l('prm_clawback') ?></button>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
      initDataTable('.table-commissions', window.location.href.replace('commissions', 'table/commissions'), [6], [6]);
      
      $('#clawback_modal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var modal = $(this);
        modal.find('#clawback_commission_id').val(id);
        modal.find('form').attr('action', admin_url + 'partner_reseller_management/commissions/clawback/' + id);
      });
  });
</script>
