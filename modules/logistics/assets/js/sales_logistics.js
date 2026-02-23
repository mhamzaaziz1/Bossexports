$(function () {
    // Only run on sales controllers where the items table exists
    if ($('.table.items').length === 0) {
        return;
    }

    // Add Headers to the table
    // We target the thead tr and insert before the last column (usually actions/cog) or just append if structure allows
    // Standard Perfex items table: Item | Description | Qty | Rate | Tax | Amount | <settings>
    // We want to insert after Qty ideally, or just before Rate.
    // Let's insert after Qty to be safe.
    var $headerRow = $('.table.items thead tr');
    var $qtyHeader = $headerRow.find('th').filter(function () {
        return $(this).text().trim().toLowerCase().includes('qty') || $(this).text().trim().toLowerCase().includes('quantity');
    });

    if ($qtyHeader.length > 0) {
        $qtyHeader.after('<th class="text-right">Volume</th><th class="text-right">Weight</th>');
    } else {
        // Fallback if Qty not found, append before Rate
        var $rateHeader = $headerRow.find('th').filter(function () {
            return $(this).text().trim().toLowerCase().includes('rate');
        });
        if ($rateHeader.length > 0) {
            $rateHeader.before('<th class="text-right">Weight</th><th class="text-right">Volume</th>');
        } else {
            // Fallback, append to end
            $headerRow.append('<th class="text-right">Weight</th><th class="text-right">Volume</th>');
        }
    }

    // Identify the main input row
    var $mainRow = $('.table.items tbody tr.main');
    if ($mainRow.length > 0) {
        var $mainQty = $mainRow.find('input[name="quantity"]');
        var $mainQtyCell = $mainQty.closest('td');

        if ($mainRow.find('.weight-col').length === 0) {
            var weightHtml = '<td class="weight-col"><input type="number" step="any" class="form-control" name="weight" placeholder="Weight"></td>';
            var volumeHtml = '<td class="volume-col"><input type="number" step="any" class="form-control" name="volume" placeholder="Volume"></td>';
            $mainQtyCell.after(volumeHtml + weightHtml);
        }
    }

    // Function to add inputs to a specific row
    function addLogisticsInputs($row, itemId) {
        // We need to insert 2 TDs.
        // We must find the Qty TD in this row to match the header injection.

        // Perfex rows usually have inputs like name="newitems[X][qty]"
        // We'll look for that input to identify the Qty cell.
        var $qtyInput = $row.find('input[name*="[qty]"]');
        var $qtyCell = $qtyInput.closest('td');

        var weightValue = 0;
        var volumeValue = 0;

        // If we have an itemId, we *might* check if we have data for it.
        // However, standard Perfex `add_item_to_table` does not pass the item object easily to the DOM event 
        // unless we intercept the AJAX call or if the item data is in the select option.

        // Strategy: 
        // 1. If it's a new row from "Add Item" dropdown, we might need to fetch data.
        // 2. Perfex stores item attributes in the option data-<key>.
        //    Let's check if the select picker has this data.

        // Wait, the row is already added when this runs.
        // If the user selected from dropdown, we might find the values in the textarea description or hidden fields? No.

        // Best approach: AJAX fetch or check if Perfex placed data somewhere.
        // Actually, Perfex's `add_item_to_table` functionality usually involves:
        // - User selects item -> `get_item_by_id` (ajax) -> returns JSON -> populates inputs.
        // We need to hook into that flow or trigger our own fetch.

        // Simpler for now: Check if we can find the hidden item_id input.
        var $idInput = $row.find('input[name*="[item_id]"]');
        var currentItemId = $idInput.val();

        // If we have an ID, we can try to fetch defaults.
        if (currentItemId) {
            $.get(admin_url + 'logistics/get_item_logistics/' + currentItemId, function (response) {
                if (response.success) {
                    $row.find('input[name*="[weight]"]').val(response.weight);
                    $row.find('input[name*="[volume]"]').val(response.volume);
                }
            }, 'json');
        }

        var inputNamePrefix = $qtyInput.attr('name').replace('[qty]', '');
        // e.g. newitems[123]

        var weightHtml = '<td class="weight-col"><input type="number" step="any" class="form-control" name="' + inputNamePrefix + '[weight]" value="' + weightValue + '" placeholder="Weight"></td>';
        var volumeHtml = '<td class="volume-col"><input type="number" step="any" class="form-control" name="' + inputNamePrefix + '[volume]" value="' + volumeValue + '" placeholder="Volume"></td>';

        if ($qtyCell.length > 0) {
            $qtyCell.after(volumeHtml + weightHtml);
            // Note: I swapped order in append from header order. 
            // Header: Qty | Volume | Weight ?? Wait.
            // Code above: $qtyHeader.after('<th class="text-right">Volume</th><th class="text-right">Weight</th>');
            // So Order is: Qty -> Volume -> Weight
            // My injection above: $qtyCell.after(volumeHtml + weightHtml);
            // appending "Volume" then "Weight" right after Qty results in: Qty | Weight | Volume (because `after` inserts sequentially? No.)
            // element.after(A, B) inserts A then B.
            // element.after(A + B) inserts the string.
            // If I do $qtyCell.after(VolumeHtml + WeightHtml) -> Qty | Volume | Weight
            // Yes that matches header.
        }
    }

    // Monitor for new rows
    // Perfex triggers 'item_added_to_table' event on the body or document usually.


    // Capture values from main row before they might be cleared?
    // Actually, listening to item_added_to_table... if manual add, the data object is usually undefined or empty.
    // We can read from the main row inputs if data is empty.
    // BUT, the core might clear the main row immediately.
    // Let's add a click listener to the add button to save state?
    // Or just change listeners on the main row.
    var manualLogisticsData = { weight: '', volume: '' };
    $(document).on('input', '.table.items tbody tr.main input[name="weight"]', function () {
        manualLogisticsData.weight = $(this).val();
    });
    $(document).on('input', '.table.items tbody tr.main input[name="volume"]', function () {
        manualLogisticsData.volume = $(this).val();
    });

    $(document).on('item_added_to_table', function (e, data) {
        var $row = $('.table.items tbody tr:last');
        if ($row.find('.weight-col').length > 0) return;

        addLogisticsInputs($row);

        // If data came from AJAX (dropdown select)
        if (data) {
            if (data.weight) $row.find('input[name*="[weight]"]').val(data.weight);
            if (data.volume) $row.find('input[name*="[volume]"]').val(data.volume);
        }

        // If no data (manual add) or data didn't have values, rely on manual input
        // But only if we successfully captured it.
        // Note: If data is present, it means it came from dropdown OR core constructed it.
        // If we typed manually, we want to use what we typed.

        // Logic: If the main row has values (or we captured them), use them.
        // Since core might clear main row, use captured variable.
        if (manualLogisticsData.weight !== '') {
            // Only apply if the row is empty? Or always overwrite?
            // If data.weight exists, it means it came from item fetch. Manual input should override? 
            // Usually manual input is for "new" items not in DB.
            // If user selected item, then typed over weight, we want typed value.
            // But wait, if user selected item, `add_item_to_table` fires immediately? 
            // No, standard Perfex behavior for dropdown is immediate add.
            // So `tr.main` inputs are NOT used for dropdown.
            // So `manualLogisticsData` is only valid if the user was typing in `tr.main`.

            // How to distinguish?
            // If `data` is passed, it's likely from dropdown/ajax.
            // If `data` is undefined or minimal, it's manual.
            // However, let's just check if we have a value in manualLogisticsData and use it if it seems appropriate.
            // Actually, simply: apply manual values if set.

            // BUT we must clear manualLogisticsData after use to avoid applying to subsequent dropdown adds?
            // YES.

            // Wait, if I select from dropdown, `tr.main` inputs are NOT touched, so manualLogisticsData should remain default/empty (unless I typed there before).
            // If I type in `tr.main`, `manualLogisticsData` gets values.
            // Then I click check. New row added.
            // We apply values.
            // We clear `manualLogisticsData`.

            if (!data || !data.weight) { // Only if not provided by data source?
                $row.find('input[name*="[weight]"]').val(manualLogisticsData.weight);
            }
            if (!data || !data.volume) {
                $row.find('input[name*="[volume]"]').val(manualLogisticsData.volume);
            }

            // Reset
            manualLogisticsData = { weight: '', volume: '' };
            $('.table.items tbody tr.main input[name="weight"]').val('');
            $('.table.items tbody tr.main input[name="volume"]').val('');
        }
    });

    // Also need to handle existing rows on Edit (e.g. Editing an invoice)
    // Existing rows are loaded by PHP. They won't have the TD cells for Weight/Volume yet because we didn't edit the view.
    // Wait... if we didn't edit the view, the columns are missing.
    // We must inject columns into EXISTING rows on page load.

    $('.table.items tbody tr').each(function () {
        var $row = $(this);
        // Skip current edit row or empty template rows if they exist differently
        if ($row.find('.weight-col').length > 0) return;

        var $qtyInput = $row.find('input[name*="[qty]"]');
        if ($qtyInput.length === 0) return; // Not an item row

        var $qtyCell = $qtyInput.closest('td');

        // Get existing values.
        // How? The existing rows are rendered by `invoice_items_table` function in helper.
        // But we didn't modify it. So the values `weight` and `volume` are NOT in the DOM.
        // They ARE in the `itemable` table in DB.
        // We need to fetch them? Or relies on the fact that `items` table in view loops over `$items`.
        // But the loop in `invoice_items_table` (application/helpers/invoices_helper.php) only outputs known columns.
        // It DOES output hidden input for item_id.

        // Issue: We need to populate the inputs with saved values.
        // Since we cannot edit the PHP file, we have to AJAX specific values for this invoice/estimate?
        // OR, we can assume this is "Edit" mode.
        // Actually, for existing items, `itemable` table has the data.

        // We can expose the itemable data via a JS object in the footer if we want, OR
        // Fetch it row by row? Row by row is slow.
        // Better: Hook `before_render_item_table`? No such hook.

        // Hacky but clean:
        // Use `get_item_logistics` for each row? No, too many requests.
        // Maybe fetch ALL items for this transaction in one go?
        // JS can grab the URI/ID.

        // Let's implement a batch fetch function in the controller.

        var itemId = $row.find('input[name*="[item_id]"]').val(); // This is the ITEM ID (Master), not the rel_id.
        // Wait, for saved items, we need the values saved in `tblitemable`, not `tblitems`.
        // The `tblitems` value is the default. The `tblitemable` value is the transaction specific one.

        // How to get `tblitemable` value?
        // We might be stuck here without a core edit or a smart hook.
        // Wait, `pdf_table_row` works because we have the data in the object passed to it.

        // Can we inject data attributes into the row? 
        // We cannot modify the row HTML generation in PHP (`invoice_items_table`).

        // idea: The `items` variable is usually available in the view (e.g. `$invoice->items`).
        // We can json_encode `$invoice->items` into a global JS variable in the `app_admin_footer` hook specific for this page.
        // Then we can look it up by `item_id` (or better, strict row match, but row match is hard).
        // Actually, `items` array has `id` which matches `item_id` in the `newitems[ID]` array?
        // No. `newitems` uses a random index or the `item_id` (record id in itemable).

        // Let's verify: In saved invoice, the input name is `items[<itemable_id>][qty]`.
        // So we can extract the `itemable_id` from the input name.

        var inputName = $qtyInput.attr('name');
        // items[1234][qty] -> 1234 is the ID in tblitemable.
        var itemableId = null;
        var match = inputName.match(/items\[(\d+)\]/);
        if (match) {
            itemableId = match[1];
        }

        // We inject the inputs first (empty or 0).
        var inputNamePrefix = inputName.replace('[qty]', '');
        var weightHtml = '<td class="weight-col"><input type="number" step="any" class="form-control" name="' + inputNamePrefix + '[weight]" value="" placeholder="Weight"></td>';
        var volumeHtml = '<td class="volume-col"><input type="number" step="any" class="form-control" name="' + inputNamePrefix + '[volume]" value="" placeholder="Volume"></td>';

        $qtyCell.after(volumeHtml + weightHtml);

        // Now populate if we have the global data.
        if (typeof logistics_items_data !== 'undefined' && itemableId) {
            var itemData = logistics_items_data.find(x => x.id == itemableId);
            if (itemData) {
                $row.find('input[name*="[weight]"]').val(itemData.weight);
                $row.find('input[name*="[volume]"]').val(itemData.volume);
            }
        }
    });

});
