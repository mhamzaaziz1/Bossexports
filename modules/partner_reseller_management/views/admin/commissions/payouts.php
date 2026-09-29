<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
  <h4 class="no-margin"><?= _l('prm_payouts') ?></h4><hr/>
  <div class="panel panel-default"><div class="panel-body">
    <?php render_datatable([
        '#',
        _l('prm_partner'),
        _l('prm_amount'),
        _l('prm_method'),
        _l('prm_status'),
        _l('prm_requested_at'),
        '',
    ], 'payouts'); ?>
  </div></div>
</div></div>

<div class="modal fade" id="pay_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <?= form_open(admin_url('partner_reseller_management/payouts/pay'), ['id' => 'pay_form']) ?>
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?= _l('prm_mark_paid') ?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="reference"><?= _l('prm_reference') ?></label>
          <input type="text" name="reference" id="reference" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close') ?></button>
        <button type="submit" class="btn btn-success"><?= _l('prm_confirm') ?></button>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
      initDataTable('.table-payouts', window.location.href.replace('payouts', 'table/payouts'), [6], [6]);
      
      $('#pay_modal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var modal = $(this);
        modal.find('form').attr('action', admin_url + 'partner_reseller_management/payouts/pay/' + id);
      });
  });
</script>
