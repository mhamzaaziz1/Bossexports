<hr />
<h4><?php echo _l('vendor_sourcing'); ?></h4>
<div class="table-responsive">
    <table class="table vendor-sourcing-table">
        <thead>
            <tr>
                <th>Vendor</th>
                <th>Vendor Item Code</th>
                <th>Last Purchase Price</th>
                <th>Currency</th>
                <th>Preferred</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="vendor_sourcing_tbody">
            <?php 
            $i = 0;
            if (isset($item_vendors) && count($item_vendors) > 0) {
                foreach($item_vendors as $v) { ?>
                    <tr class="vendor-sourcing-row">
                        <td>
                            <select name="vendors[<?php echo $i; ?>][vendor_id]" class="selectpicker" data-width="100%" data-live-search="true">
                                <option value=""></option>
                                <?php foreach($vendors as $vendor) { ?>
                                    <option value="<?php echo $vendor['userid']; ?>" <?php if($vendor['userid'] == $v['vendor_id']) { echo 'selected'; } ?>><?php echo $vendor['company']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="vendors[<?php echo $i; ?>][vendor_item_code]" class="form-control" value="<?php echo htmlspecialchars($v['vendor_item_code']); ?>">
                        </td>
                        <td>
                            <input type="number" step="any" name="vendors[<?php echo $i; ?>][last_purchase_price]" class="form-control" value="<?php echo $v['last_purchase_price']; ?>">
                        </td>
                        <td>
                            <select name="vendors[<?php echo $i; ?>][currency]" class="selectpicker" data-width="100%">
                                <option value=""></option>
                                <?php foreach($currencies as $currency) { ?>
                                    <option value="<?php echo $currency['id']; ?>" <?php if($currency['id'] == $v['currency']) { echo 'selected'; } ?>><?php echo $currency['name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td class="text-center">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="vendors[<?php echo $i; ?>][is_preferred]" value="1" <?php if($v['is_preferred'] == 1) { echo 'checked'; } ?>>
                                <label></label>
                            </div>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-icon remove-vendor-sourcing-row"><i class="fa fa-remove"></i></button>
                        </td>
                    </tr>
            <?php 
                $i++;
                }
            } 
            ?>
        </tbody>
    </table>
</div>
<button type="button" class="btn btn-info btn-sm" id="add_vendor_sourcing_row">Add Vendor</button>

<script>
    var vendor_sourcing_index = <?php echo $i; ?>;
    var vendor_options = '<option value=""></option><?php foreach($vendors as $vendor) { echo '<option value="'.$vendor['userid'].'">'.addslashes($vendor['company']).'</option>'; } ?>';
    var currency_options = '<option value=""></option><?php foreach($currencies as $currency) { echo '<option value="'.$currency['id'].'">'.$currency['name'].'</option>'; } ?>';

    $('#add_vendor_sourcing_row').on('click', function() {
        var html = '<tr class="vendor-sourcing-row">';
        html += '<td><select name="vendors[' + vendor_sourcing_index + '][vendor_id]" class="selectpicker" data-width="100%" data-live-search="true">' + vendor_options + '</select></td>';
        html += '<td><input type="text" name="vendors[' + vendor_sourcing_index + '][vendor_item_code]" class="form-control"></td>';
        html += '<td><input type="number" step="any" name="vendors[' + vendor_sourcing_index + '][last_purchase_price]" class="form-control"></td>';
        html += '<td><select name="vendors[' + vendor_sourcing_index + '][currency]" class="selectpicker" data-width="100%">' + currency_options + '</select></td>';
        html += '<td class="text-center"><div class="checkbox checkbox-primary"><input type="checkbox" name="vendors[' + vendor_sourcing_index + '][is_preferred]" value="1"><label></label></div></td>';
        html += '<td><button type="button" class="btn btn-danger btn-icon remove-vendor-sourcing-row"><i class="fa fa-remove"></i></button></td>';
        html += '</tr>';
        
        $('#vendor_sourcing_tbody').append(html);
        $('#vendor_sourcing_tbody').find('.selectpicker').selectpicker('refresh');
        vendor_sourcing_index++;
    });

    $('body').on('click', '.remove-vendor-sourcing-row', function() {
        $(this).parents('tr').remove();
    });
    
    // Initialize pickers for existing rows newly loaded
    $('#vendor_sourcing_tbody').find('.selectpicker').selectpicker('refresh');

    // --- Auto-fill Last Purchase Price & Currency when vendor changes ---
    function vs_fetch_last_price($row, item_id, vendor_id) {
        if (!vendor_id || !item_id) return;

        var $priceInput    = $row.find('input[name*="[last_purchase_price]"]');
        var $currencySelect = $row.find('select[name*="[currency]"]');

        $priceInput.prop('disabled', true).val('').attr('placeholder', '...');

        $.post(admin_url + 'vendor_sourcing/get_last_vendor_price', {
            item_id:   item_id,
            vendor_id: vendor_id
        }, function(response) {
            $priceInput.prop('disabled', false).attr('placeholder', '');
            if (response && response.found) {
                $priceInput.val(response.price);
                if (response.currency_id) {
                    $currencySelect.selectpicker('val', response.currency_id);
                }
            }
        }, 'json').fail(function() {
            $priceInput.prop('disabled', false).attr('placeholder', '');
        });
    }

    // Delegate on the container so it catches both pre-loaded and dynamically added rows
    $(document).on('change', '#vendor_sourcing_tbody select[name*="[vendor_id]"]', function() {
        var $row       = $(this).closest('tr');
        var vendor_id  = $(this).val();
        // Try to get item_id from the hidden field in the modal or from a data attribute
        var item_id    = $('input[name="id"]').val() || $('input#item_id_vs').val() || '';
        vs_fetch_last_price($row, item_id, vendor_id);
    });
</script>
