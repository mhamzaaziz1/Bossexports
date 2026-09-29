<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Stock full view report</h4>
                        <hr/>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered dt-table table-stock-full-view">
                                <thead>
                                    <tr>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th class="text-right">Stock on Hand</th>
                                        <th class="text-right">Stock on Sales Order</th>
                                        <th class="text-right">Available to Sell</th>
                                        <?php if (isset($warehouses)) { foreach ($warehouses as $w) { ?>
                                            <th class="text-right"><?php echo html_entity_decode($w['warehouse_name']); ?></th>
                                        <?php }} ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!isset($rows) || count($rows) === 0) { ?>
                                        <tr>
                                            <td colspan="<?php echo 5 + (isset($warehouses) ? count($warehouses) : 0); ?>" class="text-center">No stock data found.</td>
                                        </tr>
                                    <?php } else { ?>
                                        <?php foreach ($rows as $r) { ?>
                                            <tr>
                                                <td><?php echo html_entity_decode($r['code']); ?></td>
                                                <td><?php echo html_entity_decode($r['name']); ?></td>
                                                <td class="text-right"><?php echo (float)$r['on_hand']; ?></td>
                                                <td class="text-right"><?php echo (float)$r['on_so']; ?></td>
                                                <td class="text-right"><?php echo (float)$r['available']; ?></td>
                                                <?php foreach ($warehouses as $w) { $wid = (int)$w['warehouse_id']; ?>
                                                    <td class="text-right"><?php echo isset($r['per_wh'][$wid]) ? (float)$r['per_wh'][$wid] : 0; ?></td>
                                                <?php } ?>
                                            </tr>
                                        <?php } ?>
                                    <?php } ?>
                                </tbody>
                                <?php if (isset($rows) && count($rows) > 0) { ?>
                                <tfoot>
                                    <tr>
                                        <th>Totals</th>
                                        <th></th>
                                        <th class="text-right"><?php echo isset($totals['on_hand']) ? (float)$totals['on_hand'] : 0; ?></th>
                                        <th class="text-right"><?php echo isset($totals['on_so']) ? (float)$totals['on_so'] : 0; ?></th>
                                        <th class="text-right"><?php echo isset($totals['available']) ? (float)$totals['available'] : 0; ?></th>
                                        <?php foreach ($warehouses as $w) { $wid = (int)$w['warehouse_id']; ?>
                                            <th class="text-right"><?php echo isset($totals['per_wh'][$wid]) ? (float)$totals['per_wh'][$wid] : 0; ?></th>
                                        <?php } ?>
                                    </tr>
                                </tfoot>
                                <?php } ?>
                            </table>
                        </div>

                        <p class="text-muted mtop10">
                            Notes: "Stock on Hand" is computed from inventory movements (inventory_manage). "Stock on Sales Order" sums quantities from Sales Orders (Estimates not Cancelled). "Available to Sell" = On Hand - On Sales Order.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?> <!-- This was the missing line -->

<script>
  $(function(){
    // Initialize as inline DataTable like other reports
    // This will now work correctly
    initDataTableInline($('.table-stock-full-view'));
  });
</script>

</body>
</html>