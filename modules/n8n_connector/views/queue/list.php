<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-6">
                    <div class="tw-mb-3">
                        <h4 class="tw-my-0 tw-font-bold tw-text-xl">
                            <?= _l('n8n_queue'); ?>
                        </h4>
                    </div>

                    <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-5 tw-gap-2">
                        <div class="tw-w-full tw-border-neutral-300/80 tw-shadow-sm tw-text-sm tw-border tw-border-solid tw-rounded-lg tw-px-4 tw-py-3 tw-flex tw-items-center tw-bg-white">
                            <span class="tw-font-semibold tw-mr-1 rtl:tw-ml-1">
                                <?php echo $stats['total']; ?>
                            </span>
                            <span class="text-dark tw-truncate">
                                <?= _l('n8n_queue_total_today'); ?>
                            </span>
                        </div>

                        <div class="tw-w-full tw-border-neutral-300/80 tw-shadow-sm tw-text-sm tw-border tw-border-solid tw-rounded-lg tw-px-4 tw-py-3 tw-flex tw-items-center tw-bg-white">
                            <span class="tw-font-semibold tw-mr-1 rtl:tw-ml-1">
                                <?php echo $stats['pending']; ?>
                            </span>
                            <span class="text-info tw-truncate">
                                <?= _l('n8n_queue_pending'); ?>
                            </span>
                        </div>

                        <div class="tw-w-full tw-border-neutral-300/80 tw-shadow-sm tw-text-sm tw-border tw-border-solid tw-rounded-lg tw-px-4 tw-py-3 tw-flex tw-items-center tw-bg-white">
                            <span class="tw-font-semibold tw-mr-1 rtl:tw-ml-1">
                                <?php echo $stats['completed']; ?>
                            </span>
                            <span class="text-success tw-truncate">
                                <?= _l('n8n_queue_completed'); ?>
                            </span>
                        </div>

                        <div class="tw-w-full tw-border-neutral-300/80 tw-shadow-sm tw-text-sm tw-border tw-border-solid tw-rounded-lg tw-px-4 tw-py-3 tw-flex tw-items-center tw-bg-white">
                            <span class="tw-font-semibold tw-mr-1 rtl:tw-ml-1">
                                <?php echo $stats['failed']; ?>
                            </span>
                            <span class="text-danger tw-truncate">
                                <?= _l('n8n_queue_failed'); ?>
                            </span>
                        </div>

                        <div class="tw-w-full tw-border-neutral-300/80 tw-shadow-sm tw-text-sm tw-border tw-border-solid tw-rounded-lg tw-px-4 tw-py-3 tw-flex tw-items-center tw-bg-white">
                            <span class="tw-font-semibold tw-mr-1 rtl:tw-ml-1">
                                <?php echo $stats['retrying']; ?>
                            </span>
                            <span class="text-warning tw-truncate">
                                <?= _l('n8n_queue_retrying'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <div class="panel-table-full">
                            <!-- Queue Table -->
                            <?php render_datatable([
                                _l('n8n_queue_id'),
                                _l('n8n_webhook'),
                                _l('n8n_event_type'),
                                _l('n8n_status'),
                                _l('n8n_retry_count'),
                                _l('n8n_next_retry'),
                                _l('n8n_created_at'),
                                _l('options'),
                            ], 'queue'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script>
    $(function() {
        initDataTable('.table-queue', window.location.href, [7], [7], {}, [0, 'desc']);
    });
</script>

</body>

</html>