<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Container-wise Purchase Reconciliation</h4>
                        <hr/>
                        <?php echo form_open(admin_url('reports/container_report'), array('class' => 'form-inline', 'autocomplete' => 'off')); ?>
                            <div class="form-group mright10">
                                <label for="container_no" class="control-label mright5">Container No</label>
                                <input type="text" name="container_no" id="container_no" class="form-control" value="<?php echo isset($selected_container_no) ? html_entity_decode($selected_container_no) : ''; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-info">Filter</button>
                            <?php if (isset($selected_container_no) && $selected_container_no != '') { ?>
                                <a href="<?php echo admin_url('reports/container_report?format=csv&container_no=' . urlencode($selected_container_no)); ?>" class="btn btn-default mleft10">Export CSV</a>
                            <?php } ?>
                        <?php echo form_close(); ?>

                        <?php if (isset($report_rows)) { ?>
                            <div class="table-responsive mtop20">
                                <!-- Added 'dt-table' class for styling and 'table-container-report' for JS selection -->
                                <table class="table table-striped table-bordered dt-table table-container-report">
                                    <thead>
                                        <tr>
                                          <th>Item Code</th>
                                          <th>PO Qty (Ordered)</th>
                                          <th>P Qty (Received)</th>
                                          <th>Difference</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($report_rows) === 0) { ?>
                                            <!-- Do not output this row if using DataTables, or handle it gracefully. 
                                                 DataTables usually handles empty states automatically, but keeping for fallback. -->
                                        <?php } else { ?>
                                            <?php foreach ($report_rows as $r) { ?>
                                                <tr>
                                                    <td><?php echo html_entity_decode($r['item_code']); ?></td>
                                                    <td class="text-right"><?php echo (float)$r['estimate_qty']; ?></td>
                                                    <td class="text-right"><?php echo (float)$r['po_qty']; ?></td>
                                                    <td class="text-right"><?php echo (float)$r['difference']; ?></td>
                                                </tr>
                                            <?php } ?>
                                        <?php } ?>
                                    </tbody>
                                    <?php if (count($report_rows) > 0) { ?>
                                        <tfoot>
                                            <tr>
                                                <th>Total</th>
                                                <th class="text-right"><?php echo (float)$totals['estimate_qty']; ?></th>
                                                <th class="text-right"><?php echo (float)$totals['po_qty']; ?></th>
                                                <th class="text-right"><?php echo (float)$totals['difference']; ?></th>
                                            </tr>
                                        </tfoot>
                                    <?php } ?>
                                </table>
                            </div>
                        <?php } ?>

                        <?php if (isset($other_reports) && count($other_reports) > 0) { ?>
                            <hr/>
                            <h5>Additional stats</h5>
                            <ul>
                                <li>Total PO matched: <?php echo (int)$other_reports['estimates_count']; ?></li>
                                <li>Total purchase matched: <?php echo (int)$other_reports['po_count']; ?></li>
                            </ul>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function(){
        // Initialize the table as a Perfex DataTable (allows sorting/search/pagination)
        // Only init if the table exists (i.e., report_rows is set)
        if($('.table-container-report').length > 0){
            initDataTableInline($('.table-container-report'));
        }
    });
</script>
</body>
</html>