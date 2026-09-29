<?php
// Warehouse Assigned Vendors Tab — vendor selection ONLY.
// Price/currency are auto-populated from purchase history via the pipeline hook.
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div id="warehouse_vendor_sourcing_panel">
    <h4 class="h4-color"><i class="fa fa-truck"></i> Assigned Vendors</h4>
    <hr class="hr-color">

    <?php echo form_open(admin_url('vendor_sourcing/warehouse_save_vendors'), ['id' => 'warehouse_vendor_sourcing_form']); ?>
    <?php echo form_hidden('item_id', $item_id); ?>

    <div id="wh_vendor_list">
        <?php foreach ($item_vendors as $i => $v) { ?>
        <div class="wh-vendor-row" style="display:flex;align-items:center;margin-bottom:8px;gap:10px;">
            <select name="vendors[<?php echo $i; ?>][vendor_id]" class="selectpicker" data-width="300px" data-live-search="true">
                <option value="">— Select Vendor —</option>
                <?php foreach ($vendors as $vendor) { ?>
                    <option value="<?php echo $vendor['userid']; ?>" <?php if ($vendor['userid'] == $v['vendor_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($vendor['company']); ?>
                    </option>
                <?php } ?>
            </select>
            <button type="button" class="btn btn-danger btn-xs wh-remove-vendor"><i class="fa fa-trash"></i></button>
        </div>
        <?php } ?>
    </div>

    <div style="margin-top:10px;">
        <button type="button" class="btn btn-default btn-sm" id="wh_add_vendor_row">
            <i class="fa fa-plus"></i> Add Vendor
        </button>
    </div>

    <div class="text-right" style="margin-top:15px;">
        <button type="submit" class="btn btn-info btn-sm"><i class="fa fa-save"></i> Save Vendor Mappings</button>
    </div>

    <?php echo form_close(); ?>
</div>

<script>
var wh_vs_idx = <?php echo count($item_vendors); ?>;

var wh_vendor_options = '<option value="">— Select Vendor —</option>';
<?php foreach ($vendors as $vendor) {
    echo "wh_vendor_options += '<option value=\"{$vendor['userid']}\">" . addslashes($vendor['company']) . "</option>';";
} ?>

$('#wh_add_vendor_row').on('click', function () {
    var row = '<div class="wh-vendor-row" style="display:flex;align-items:center;margin-bottom:8px;gap:10px;">'
        + '<select name="vendors[' + wh_vs_idx + '][vendor_id]" class="selectpicker" data-width="300px" data-live-search="true">'
        + wh_vendor_options
        + '</select>'
        + '<button type="button" class="btn btn-danger btn-xs wh-remove-vendor"><i class="fa fa-trash"></i></button>'
        + '</div>';
    $('#wh_vendor_list').append(row);
    $('#wh_vendor_list .selectpicker').selectpicker('refresh');
    wh_vs_idx++;
});

$('body').on('click', '.wh-remove-vendor', function () {
    $(this).closest('.wh-vendor-row').remove();
});

$('#wh_vendor_list .selectpicker').selectpicker('refresh');

$('#warehouse_vendor_sourcing_form').on('submit', function (e) {
    e.preventDefault();
    var formData = $(this).serializeArray().filter(function(field) {
        return field.name !== 'item_id'; // item_id goes in the URL, not POST body
    });
    $.post(
        admin_url + 'vendor_sourcing/warehouse_save_vendors/<?php echo (int)$item_id; ?>',
        $.param(formData),
        function (response) {
            try { response = JSON.parse(response); } catch(e) {}
            if (response && response.success) {
                alert_float('success', response.message);
            } else {
                alert_float('danger', response.message || 'Error saving');
            }
        }
    );
});
</script>
