<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('fix_inventory'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <div class="row">
                            <div class="col-md-3">
                                <label for="warehouse_filter"><?php echo _l('warehouse'); ?></label>
                                <select name="warehouse_filter" id="warehouse_filter" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('all_warehouses'); ?>" multiple data-live-search="true">
                                    <?php foreach($warehouses as $warehouse){ ?>
                                        <option value="<?php echo $warehouse['warehouse_id']; ?>"><?php echo $warehouse['warehouse_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="item_filter"><?php echo _l('items'); ?></label>
                                <select name="item_filter" id="item_filter" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('all_items'); ?>" multiple data-live-search="true">
                                    <?php foreach($items as $item){ ?>
                                        <option value="<?php echo $item['id']; ?>"><?php echo $item['description']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3" style="padding-top: 25px;">
                                <button type="button" class="btn btn-info" id="reconcile_btn"><?php echo _l('reconcile'); ?></button>
                                <button type="button" class="btn btn-danger" id="fix_all_btn"><?php echo _l('fix_all_gaps'); ?></button>
                            </div>
                        </div>
                        <hr />
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table dt-table table-fix-inventory" id="table-fix-inventory">
                                    <thead>
                                        <tr>
                                            <th><?php echo _l('item_name'); ?></th>
                                            <th><?php echo _l('warehouse'); ?></th>
                                            <th><?php echo _l('actual_qty'); ?></th>
                                            <th><?php echo _l('expected_qty_from_vouchers'); ?></th>
                                            <th><?php echo _l('gap'); ?></th>
                                            <th><?php echo _l('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
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
        initDataTable('.table-fix-inventory', admin_url + 'warehouse/table_fix_inventory', undefined, undefined, 'undefined', [0, 'asc']);

        $('#reconcile_btn').on('click', function(){
            $('.table-fix-inventory').DataTable().ajax.reload();
        });

        $('#fix_all_btn').on('click', function(){
            if(confirm('Are you sure you want to fix all gaps? This will update the inventory balance for all items with discrepancies.')){
                $.post(admin_url + 'warehouse/fix_all_inventory').done(function(response){
                    response = JSON.parse(response);
                    if(response.success){
                        alert_float('success', response.message);
                        $('.table-fix-inventory').DataTable().ajax.reload();
                    } else {
                        alert_float('danger', response.message);
                    }
                });
            }
        });

        $(document).on('click', '.fix-item-btn', function(){
            var item_id = $(this).data('item-id');
            var warehouse_id = $(this).data('warehouse-id');
            $.post(admin_url + 'warehouse/fix_item_inventory', {
                item_id: item_id,
                warehouse_id: warehouse_id
            }).done(function(response){
                response = JSON.parse(response);
                if(response.success){
                    alert_float('success', response.message);
                    $('.table-fix-inventory').DataTable().ajax.reload();
                } else {
                    alert_float('danger', response.message);
                }
            });
        });
    });
</script>
