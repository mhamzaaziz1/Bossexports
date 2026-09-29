<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="tour-tracking-stats-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close close-tour-stats" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('tour_statistics'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="relative" style="min-height:250px;max-height:250px;">
                    <canvas id="tour-tracking-stats-chart" height="250"></canvas>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<script>
    tourTrackingStatsData = <?php echo $stats; ?>;
</script>
