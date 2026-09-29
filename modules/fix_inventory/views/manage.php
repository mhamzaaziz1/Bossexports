<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="no-margin"><?php echo $title; ?></h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="<?php echo admin_url('fix_inventory/audit_report'); ?>" class="btn btn-primary"><i class="fa fa-history"></i> Product Audit Report</a>
                                <button type="button" class="btn btn-danger" id="fix_all_btn">Fix All Gaps</button>
                                <a href="<?php echo admin_url('fix_inventory'); ?>" class="btn btn-info"><i class="fa fa-refresh"></i> Refresh</a>
                            </div>
                        </div>
                        <hr class="hr-panel-heading" />
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="simple-fix-table">
                                <thead>
                                    <tr>
                                        <th>Item Name (SKU)</th>
                                        <th>Warehouse</th>
                                        <th>Actual Qty</th>
                                        <th>Breakdown (Op + Rec - Del + Adj + TIn - TOut)</th>
                                        <th>Expected Qty</th>
                                        <th>Gap</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($discrepancies) > 0){ ?>
                                        <?php foreach($discrepancies as $row){ ?>
                                            <?php $gap = (float)$row['expected_qty'] - (float)$row['actual_qty']; ?>
                                            <tr>
                                                <td><?php echo $row['item_name'] . ' (' . $row['sku_code'] . ')'; ?></td>
                                                <td><?php echo $row['warehouse_name']; ?></td>
                                                <td><?php echo app_format_number($row['actual_qty']); ?></td>
                                                <td>
                                                    <small>
                                                        Op: <?php echo (float)$row['opening_qty']; ?> | 
                                                        Rec: <?php echo (float)$row['receipt_qty']; ?> | 
                                                        Del: <?php echo (float)$row['delivery_qty']; ?> | 
                                                        Adj: <?php echo (float)$row['adjustment_qty'] >= 0 ? '+' . (float)$row['adjustment_qty'] : (float)$row['adjustment_qty']; ?> | 
                                                        TIn: <?php echo (float)$row['transfer_in_qty']; ?> | 
                                                        TOut: <?php echo (float)$row['transfer_out_qty']; ?>
                                                    </small>
                                                </td>
                                                <td><?php echo app_format_number($row['expected_qty']); ?></td>
                                                <td class="<?php echo $gap > 0 ? 'text-success' : 'text-danger'; ?>">
                                                    <b><?php echo app_format_number($gap); ?></b>
                                                </td>
                                                <td>
                                                    <button class="btn btn-warning btn-xs fix-item-btn" 
                                                            data-item-id="<?php echo $row['item_id']; ?>" 
                                                            data-warehouse-id="<?php echo $row['warehouse_id']; ?>">
                                                        <i class="fa fa-wrench"></i> Fix
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No inventory discrepancies found!</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function(){
        $('#fix_all_btn').on('click', function(){
            var btn = $(this);
            if(confirm('Are you sure you want to fix all gaps?')){
                btn.prop('disabled', true).text('Fixing...');
                $.post(admin_url + 'fix_inventory/fix_all').done(function(response){
                    try {
                        response = JSON.parse(response);
                        if(response.success){
                            alert_float('success', response.message);
                            setTimeout(function(){ location.reload(); }, 1500);
                        } else {
                            alert_float('danger', response.message);
                            btn.prop('disabled', false).text('Fix All Gaps');
                        }
                    } catch(e) {
                        alert("Error parsing response: " + response);
                        btn.prop('disabled', false).text('Fix All Gaps');
                    }
                }).fail(function(xhr, status, error){
                    alert("Ajax request failed: " + error + "\n" + xhr.responseText);
                    btn.prop('disabled', false).text('Fix All Gaps');
                });
            }
        });

        $('.fix-item-btn').on('click', function(){
            var btn = $(this);
            var item_id = btn.data('item-id');
            var warehouse_id = btn.data('warehouse-id');
            var original_html = btn.html();
            
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Fixing...');
            
            $.post(admin_url + 'fix_inventory/fix_item', {
                item_id: item_id,
                warehouse_id: warehouse_id
            }).done(function(response){
                try {
                    response = JSON.parse(response);
                    if(response.success){
                        alert_float('success', response.message);
                        btn.closest('tr').fadeOut(300, function(){ $(this).remove(); });
                    } else {
                        alert_float('danger', response.message);
                        btn.prop('disabled', false).html(original_html);
                    }
                } catch(e) {
                    alert("Error parsing response: " + response);
                    btn.prop('disabled', false).html(original_html);
                }
            }).fail(function(xhr, status, error){
                alert("Ajax request failed: " + error + "\n" + xhr.responseText);
                btn.prop('disabled', false).html(original_html);
            });
        });
    });
</script>
