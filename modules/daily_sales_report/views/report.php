<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-5">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php 
                        $id = isset($report) ? $report['id'] : '';
                        echo form_open(admin_url('daily_sales_report/report/' . $id), ['id' => 'daily_sales_report_form']); 
                        ?>
                        
                        <div class="form-group">
                            <label for="name"><?php echo _l('daily_sales_report_name'); ?></label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($report) ? $report['name'] : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="daily_sales_report_emails"><?php echo _l('daily_sales_report_emails'); ?></label>
                            <input type="text" id="daily_sales_report_emails" name="emails" class="form-control" value="<?php echo isset($report) ? $report['emails'] : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="frequency"><?php echo _l('daily_sales_report_frequency'); ?></label>
                            <select name="frequency" id="frequency" class="form-control selectpicker">
                                <?php $selected_freq = isset($report) ? $report['frequency'] : 'daily'; ?>
                                <option value="daily" <?php echo ($selected_freq == 'daily') ? 'selected' : ''; ?>><?php echo _l('daily_sales_report_daily'); ?></option>
                                <option value="weekly" <?php echo ($selected_freq == 'weekly') ? 'selected' : ''; ?>><?php echo _l('daily_sales_report_weekly'); ?></option>
                                <option value="monthly" <?php echo ($selected_freq == 'monthly') ? 'selected' : ''; ?>><?php echo _l('daily_sales_report_monthly'); ?></option>
                            </select>
                        </div>

                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="active" id="active" <?php if(isset($report)){if($report['active'] == 1){echo 'checked';}}else{echo 'checked';} ?>>
                            <label for="active"><?php echo _l('daily_sales_report_active'); ?></label>
                        </div>

                        <hr />
                        <h5><?php echo _l('daily_sales_report_select_all_filters'); ?></h5>

                        <div class="form-group">
                            <label for="filter_warehouse"><?php echo _l('daily_sales_report_warehouse'); ?></label>
                            <select id="filter_warehouse" class="form-control selectpicker" data-live-search="true" data-width="100%">
                                <option value=""></option>
                                <?php foreach($warehouses as $wh){ ?>
                                    <option value="<?php echo $wh['warehouse_id']; ?>"><?php echo $wh['warehouse_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="filter_commodity_type"><?php echo _l('daily_sales_report_commodity_type'); ?></label>
                            <select id="filter_commodity_type" class="form-control selectpicker" data-live-search="true" data-width="100%">
                                <option value=""></option>
                                <?php foreach($commodity_types as $ct){ ?>
                                    <option value="<?php echo $ct['commodity_type_id']; ?>"><?php echo $ct['commondity_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="filter_group"><?php echo _l('commodity_group'); ?></label>
                            <select id="filter_group" class="form-control selectpicker" data-live-search="true" data-width="100%">
                                <option value=""></option>
                                <?php foreach($commodity_groups as $g){ ?>
                                    <option value="<?php echo $g['id']; ?>"><?php echo $g['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="filter_sub_group"><?php echo _l('daily_sales_report_sub_group'); ?></label>
                            <select id="filter_sub_group" class="form-control selectpicker" data-live-search="true" data-width="100%">
                                <option value=""></option>
                                <?php foreach($sub_groups as $sg){ ?>
                                    <option value="<?php echo $sg['id']; ?>"><?php echo $sg['sub_group_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="filter_color"><?php echo _l('daily_sales_report_color'); ?></label>
                            <select id="filter_color" class="form-control selectpicker" data-live-search="true" data-width="100%">
                                <option value=""></option>
                                <?php foreach($colors as $c){ ?>
                                    <option value="<?php echo $c['color_id']; ?>"><?php echo $c['color_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="filter_size"><?php echo _l('daily_sales_report_size'); ?></label>
                            <select id="filter_size" class="form-control selectpicker" data-live-search="true" data-width="100%">
                                <option value=""></option>
                                <?php foreach($sizes as $s){ ?>
                                    <option value="<?php echo $s['size_type_id']; ?>"><?php echo $s['size_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="filter_style"><?php echo _l('daily_sales_report_style'); ?></label>
                            <select id="filter_style" class="form-control selectpicker" data-live-search="true" data-width="100%">
                                <option value=""></option>
                                <?php foreach($styles as $st){ ?>
                                    <option value="<?php echo $st['style_type_id']; ?>"><?php echo $st['style_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="filter_model"><?php echo _l('daily_sales_report_model'); ?></label>
                            <select id="filter_model" class="form-control selectpicker" data-live-search="true" data-width="100%">
                                <option value=""></option>
                                <?php foreach($models as $m){ ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo $m['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="select_item"><?php echo _l('daily_sales_report_select_item'); ?></label>
                            <div class="input-group">
                                <select id="select_item" multiple="multiple" data-actions-box="true" data-selected-text-format="count > 2" class="form-control selectpicker" data-live-search="true" data-width="100%">
                                </select>
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-success" id="btn_add_item"><?php echo _l('daily_sales_report_add_item'); ?></button>
                                </div>
                            </div>
                        </div>

                        <hr />
                        <h5><?php echo _l('daily_sales_report_tracked_items'); ?></h5>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tracked_items_table">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('daily_sales_report_product_name'); ?></th>
                                        <th><?php echo _l('daily_sales_report_supplier_id'); ?></th>
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Items will be appended here -->
                                </tbody>
                            </table>
                        </div>

                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('daily_sales_report_save'); ?></button>
                        
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="pull-left">
                            <h4 class="no-margin"><?php echo _l('daily_sales_report_preview_title'); ?></h4>
                        </div>
                        <div class="pull-right">
                            <?php if(isset($report)): ?>
                                <a href="<?php echo admin_url('daily_sales_report/send_manual/' . $report['id']); ?>" class="btn btn-success" <?php echo count($today_sales) == 0 ? 'disabled' : ''; ?>><?php echo _l('daily_sales_report_send_now'); ?></a>
                            <?php endif; ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        
                        <div class="table-responsive">
                            <table class="table dt-table">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('daily_sales_report_date'); ?></th>
                                        <th><?php echo _l('daily_sales_report_sku'); ?></th>
                                        <th><?php echo _l('daily_sales_report_product_name'); ?></th>
                                        <th><?php echo _l('daily_sales_report_format'); ?></th>
                                        <th><?php echo _l('daily_sales_report_quantity_sold'); ?></th>
                                        <th><?php echo _l('daily_sales_report_unit_price'); ?></th>
                                        <th><?php echo _l('daily_sales_report_total_amount'); ?></th>
                                        <th><?php echo _l('daily_sales_report_visit_id'); ?></th>
                                        <th><?php echo _l('daily_sales_report_customer_id'); ?></th>
                                        <th><?php echo _l('daily_sales_report_customer'); ?></th>
                                        <th><?php echo _l('daily_sales_report_phone'); ?></th>
                                        <th><?php echo _l('daily_sales_report_channel'); ?></th>
                                        <th><?php echo _l('daily_sales_report_shop_class'); ?></th>
                                        <th><?php echo _l('daily_sales_report_region'); ?></th>
                                        <th><?php echo _l('daily_sales_report_town'); ?></th>
                                        <th><?php echo _l('daily_sales_report_creator'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($today_sales as $sale){ ?>
                                    <tr>
                                        <td><?php echo $sale['date']; ?></td>
                                        <td><?php echo $sale['sku']; ?></td>
                                        <td><?php echo $sale['name']; ?></td>
                                        <td><?php echo $sale['format']; ?></td>
                                        <td><?php echo $sale['qty']; ?></td>
                                        <td><?php echo app_format_money($sale['unit_price'], get_base_currency()); ?></td>
                                        <td><?php echo app_format_money($sale['total'], get_base_currency()); ?></td>
                                        <td><?php echo $sale['invoice_no']; ?></td>
                                        <td><?php echo $sale['customer_id']; ?></td>
                                        <td><?php echo $sale['customer']; ?></td>
                                        <td><?php echo $sale['phone']; ?></td>
                                        <td><?php echo $sale['channel']; ?></td>
                                        <td><?php echo $sale['shop_class']; ?></td>
                                        <td><?php echo $sale['region']; ?></td>
                                        <td><?php echo $sale['town']; ?></td>
                                        <td><?php echo $sale['creator']; ?></td>
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
    var tracked_items = <?php echo json_encode(isset($tracked_item_details) ? $tracked_item_details : []); ?>;

    // Function to render tracked items table
    function renderTrackedItems() {
        var tbody = $('#tracked_items_table tbody');
        tbody.empty();

        if(tracked_items.length === 0) {
            tbody.append('<tr><td colspan="3" class="text-center"><?php echo _l('daily_sales_report_no_items_tracked'); ?></td></tr>');
            return;
        }

        $.each(tracked_items, function(index, item) {
            var tr = $('<tr></tr>');
            tr.append('<td>' + (item.commodity_code ? item.commodity_code + ' - ' : '') + item.description +
                      '<input type="hidden" name="items[]" value="' + item.id + '"></td>');
            var supplier_input = $('<input type="text" class="form-control input-sm supplier-id-input" placeholder="BOSS-' + item.id + '">')
                .attr('name', 'supplier_ids[' + item.id + ']')
                .attr('data-id', item.id)
                .val(item.supplier_id || '');
            tr.append($('<td></td>').append(supplier_input));
            tr.append('<td><button type="button" class="btn btn-danger btn-sm btn-remove-item" data-id="' + item.id + '"><i class="fa fa-times"></i> <?php echo _l('daily_sales_report_remove'); ?></button></td>');
            tbody.append(tr);
        });
    }

    // Initial render
    renderTrackedItems();

    // AJAX function to load items based on filters
    function loadItems() {
        var data = {
            warehouse_id: $('#filter_warehouse').val(),
            commodity_type_id: $('#filter_commodity_type').val(),
            group_id: $('#filter_group').val(),
            sub_group_id: $('#filter_sub_group').val(),
            color_id: $('#filter_color').val(),
            size_id: $('#filter_size').val(),
            style_id: $('#filter_style').val(),
            model_id: $('#filter_model').val(),
        };

        $.post(admin_url + 'daily_sales_report/ajax_get_items', data, function(response){
            var items = JSON.parse(response);
            var select = $('#select_item');
            select.empty();
            $.each(items, function(i, item) {
                select.append('<option value="' + item.id + '" data-name="' + item.description + '" data-code="' + (item.commodity_code || '') + '">' + (item.commodity_code ? item.commodity_code + ' - ' : '') + item.description + '</option>');
            });
            select.selectpicker('refresh');
        });
    }

    // Trigger load items when any filter changes
    $('#filter_warehouse, #filter_commodity_type, #filter_group, #filter_sub_group, #filter_color, #filter_size, #filter_style, #filter_model').on('change', function(){
        loadItems();
    });

    // Add item to track list
    $('#btn_add_item').on('click', function(){
        var select = $('#select_item');
        var selected_options = select.find('option:selected');
        
        if(selected_options.length === 0) return;

        selected_options.each(function() {
            var item_id = $(this).val();
            if(!item_id) return;
            
            var exists = false;
            $.each(tracked_items, function(i, item){
                if(item.id == item_id) exists = true;
            });

            if(!exists) {
                tracked_items.push({
                    id: item_id,
                    description: $(this).data('name'),
                    commodity_code: $(this).data('code')
                });
            }
        });
        
        renderTrackedItems();
        
        // Reset select
        select.val('');
        select.selectpicker('refresh');
    });

    // Keep typed supplier IDs when the table re-renders
    $(document).on('input', '.supplier-id-input', function(){
        var id = $(this).data('id');
        var value = $(this).val();
        $.each(tracked_items, function(i, item){
            if(item.id == id) item.supplier_id = value;
        });
    });

    // Remove item from track list
    $(document).on('click', '.btn-remove-item', function(){
        var id = $(this).data('id');
        tracked_items = tracked_items.filter(function(item) {
            return item.id != id;
        });
        renderTrackedItems();
    });

    // Load items initially so dropdown is populated if user skips filters
    loadItems();
});
</script>
</body>
</html>
