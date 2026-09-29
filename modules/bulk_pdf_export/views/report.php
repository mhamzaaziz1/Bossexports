<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('bulk_pdf_export_report'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open(admin_url('bulk_pdf_export/generate'), ['id' => 'bulk-pdf-export-form']); ?>
                        <div class="row">
                            <div class="col-md-3">
                                <?php echo render_date_input('from_date', 'bulk_pdf_export_from_date'); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_date_input('to_date', 'bulk_pdf_export_to_date'); ?>
                            </div>
                            <div class="col-md-6 mtop25">
                                <div class="checkbox checkbox-primary checkbox-inline">
                                    <input type="checkbox" name="invoices" id="include_invoices" value="1" checked>
                                    <label for="include_invoices"><?php echo _l('bulk_pdf_export_include_invoices'); ?></label>
                                </div>
                                <div class="checkbox checkbox-primary checkbox-inline">
                                    <input type="checkbox" name="estimates" id="include_estimates" value="1" checked>
                                    <label for="include_estimates"><?php echo _l('bulk_pdf_export_include_estimates'); ?></label>
                                </div>
                                <div class="checkbox checkbox-primary checkbox-inline">
                                    <input type="checkbox" name="quotes" id="include_quotes" value="1" checked>
                                    <label for="include_quotes"><?php echo _l('bulk_pdf_export_include_quotes'); ?></label>
                                </div>
                                <div class="checkbox checkbox-primary checkbox-inline">
                                    <input type="checkbox" name="credit_notes" id="include_credit_notes" value="1" checked>
                                    <label for="include_credit_notes"><?php echo _l('credit_notes'); ?></label>
                                </div>
                                <hr />
                                <h5><?php echo _l('purchase'); ?></h5>
                                <div class="checkbox checkbox-primary checkbox-inline">
                                    <input type="checkbox" name="pur_requests" id="include_pur_requests" value="1" checked>
                                    <label for="include_pur_requests"><?php echo _l('purchase_request'); ?></label>
                                </div>
                                <div class="checkbox checkbox-primary checkbox-inline">
                                    <input type="checkbox" name="pur_quotations" id="include_pur_quotations" value="1" checked>
                                    <label for="include_pur_quotations"><?php echo _l('purchase_quotation'); ?></label>
                                </div>
                                <div class="checkbox checkbox-primary checkbox-inline">
                                    <input type="checkbox" name="pur_orders" id="include_pur_orders" value="1" checked>
                                    <label for="include_pur_orders"><?php echo _l('purchase_order'); ?></label>
                                </div>
                                <div class="checkbox checkbox-primary checkbox-inline">
                                    <input type="checkbox" name="pur_returns" id="include_pur_returns" value="1" checked>
                                    <label for="include_pur_returns">Purchase Return</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary"><?php echo _l('bulk_pdf_export_generate'); ?></button>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function(){
        appValidateForm($('#bulk-pdf-export-form'), {from_date:'required', to_date:'required'});
    });
</script>
</body>
</html>
