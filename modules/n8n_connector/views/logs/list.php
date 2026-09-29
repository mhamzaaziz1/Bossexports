<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-mb-4">
                    <h4 class="tw-my-0 tw-font-bold tw-text-xl"><?= _l('n8n_webhook_logs'); ?></h4>
                    <div class="_buttons">
                        <?php if (staff_can('delete', 'n8n_webhook_log')): ?>
                            <a href="<?php echo admin_url(N8N_CONNECTOR_MODULE . '/clear_logs'); ?>"
                                class="btn btn-danger pull-right _delete">
                                <i class="fa fa-trash"></i> <?php echo _l('n8n_clear_all_logs'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="panel-table-full">
                            <?php render_datatable([
                                _l('n8n_webhook'),
                                _l('n8n_event'),
                                _l('n8n_status'),
                                _l('n8n_response_code'),
                                _l('n8n_log_triggered_at'),
                                _l('actions'),
                            ], 'n8n-logs'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
    initDataTable('.table-n8n-logs', window.location.href, undefined, undefined, undefined, [4, 'desc']);
</script>


</body>

</html>