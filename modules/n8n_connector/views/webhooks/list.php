<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_buttons tw-mb-4">
                    <?php if (staff_can('create', 'n8n_connector')): ?>
                        <a href="<?php echo admin_url(N8N_CONNECTOR_MODULE . '/webhook'); ?>"
                            class="btn btn-primary pull-left">
                            <i class="fa fa-plus"></i> <?php echo _l('n8n_add_webhook'); ?>
                        </a>
                    <?php endif; ?>
                    <div class="clearfix"></div>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="panel-table-full">
                            <?php render_datatable([
                                _l('n8n_webhook_name'),
                                _l('n8n_webhook_url'),
                                _l('n8n_event_types'),
                                _l('n8n_status'),
                                _l('options'),
                            ], 'n8n-webhooks'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
    initDataTable('.table-n8n-webhooks', window.location.href);
    $(document).on('click', '.copy-webhook-url', function() {
        const url = $(this).data('url');

        const temp = $('<input>');
        $('body').append(temp);
        temp.val(url).select();
        document.execCommand('copy');
        temp.remove();

        // Optional quick feedback
        alert_float('success', '<?= _l('url_copied') ?>');
    });

    $(document).on('change', '.onoffswitch-checkbox', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation(); 

        const $checkbox = $(this);
        const isChecked = $checkbox.is(':checked');
        toggleWebhookStatus($checkbox);
    });

    function toggleWebhookStatus($checkbox) {
        const webhookId = $checkbox.data('id');
        const url = $checkbox.data('switch-url');
        const isActive = $checkbox.is(':checked') ? 1 : 0;

        $.ajax({
            url: `${url}/${webhookId}/${isActive}`,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                alert_float(response.type, response.message)
            },
            error: function() {
                $checkbox.prop('checked', !isActive);
                lert_float('danger', 'Something went wrong. Please try again.')
            }
        });
    }
</script>


</body>

</html>