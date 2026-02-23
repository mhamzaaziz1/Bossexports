<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<script>
/**
 * Sales Tax Breakdown Module
 * Displays per-line tax amount in sales documents
 */
(function($) {
    "use strict";

    $(document).ready(function() {
        // Log to confirm script is running
        console.log('Tax Breakdown Module: Script loaded');
        
        // Always try to init if table exists
        if ($('table.items').length > 0) {
            console.log('Tax Breakdown Module: Table found, initializing...');
            initTaxBreakdown();
        } else {
             console.log('Tax Breakdown Module: Table NOT found on load');
        }

        // Bind to Perfex's item-added-to-table event
        $(document).on('item-added-to-table', function() {
            console.log('Tax Breakdown Module: Item added event triggered');
            setTimeout(initTaxBreakdown, 500);
        });

        // Bind to input changes for real-time calculation
        $(document).on('change keyup blur', 'select.tax, input[data-quantity], input[name*="[qty]"], input[name="quantity"], td.rate input, input[name*="[rate]"]', function() {
            var $row = $(this).closest('tr');
            calculateLineTaxAmount($row);
        });
    });

    function initTaxBreakdown() {
        var $itemsTable = $('table.items');

        if ($itemsTable.length === 0) {
            return;
        }

        // Add tax amount header if not exists
        addTaxAmountHeader($itemsTable);

        // Add tax amount display to all item rows
        addTaxAmountToAllRows($itemsTable);
    }

    function addTaxAmountHeader($table) {
        var $headerRow = $table.find('thead tr:first, tr:first');

        if ($headerRow.length === 0) {
            console.log('Tax Breakdown Module: Header row not found');
            return;
        }

        // Check if tax amount header already exists
        if ($headerRow.find('.tax-amount-header').length > 0) {
            return;
        }

        // Find tax column header - try multiple common names/classes
        var $taxHeader = $headerRow.find('th').filter(function() {
            var text = $(this).text().toLowerCase();
            return text.includes('tax') || text.includes('impôt') || text.includes('steuer') || $(this).hasClass('th-tax');
        });

        if ($taxHeader.length > 0) {
            console.log('Tax Breakdown Module: Tax header found, inserting Tax Amount header');
            // Insert tax amount header after tax header
            var $taxAmountHeader = $('<th class="tax-amount-header" align="right" width="10%" style="font-weight:bold; color: #333;">Tax Amount</th>');
            $taxAmountHeader.insertAfter($taxHeader);
        } else {
             console.log('Tax Breakdown Module: Tax header NOT found');
             // Fallback: Insert before Amount column if Tax column not found
             var $amountHeader = $headerRow.find('th').filter(function() {
                 return $(this).text().toLowerCase().includes('amount');
             });
             if($amountHeader.length > 0) {
                 $('<th class="tax-amount-header" align="right" width="10%">Tax Amount</th>').insertBefore($amountHeader);
             }
        }
    }

    function addTaxAmountToAllRows($table) {
        var $bodyRows = $table.find('tbody tr, tr').not(':first');

        $bodyRows.each(function() {
            var $row = $(this);

            // Skip if this is not an item row
            if ($row.find('input[name*="qty"], input[name*="description"], textarea[name*="description"]').length === 0) {
                return;
            }

            // Skip main preview row
            if ($row.hasClass('main')) {
                return;
            }

            // Add tax amount cell if it doesn't exist
            addTaxAmountToRow($row);
        });
    }

    function addTaxAmountToRow($row) {
        // Skip if tax amount cell already exists
        if ($row.find('.line-tax-amount-cell').length > 0) {
            return;
        }

        // Find tax cell using class 'taxrate' (standard in Perfex)
        var $taxCell = $row.find('td.taxrate');
        
        // Fallback: Find tax cell containing tax select
        if ($taxCell.length === 0) {
             $taxCell = $row.find('td').filter(function() {
                return $(this).find('select[name*="tax"]').length > 0;
            });
        }

        if ($taxCell.length > 0) {
            // Insert tax amount cell after tax cell
            var $taxAmountCell = $('<td class="line-tax-amount-cell" align="right"><span class="tax-amount-display">0.00</span></td>');
            $taxAmountCell.insertAfter($taxCell);

            // Calculate initial value
            setTimeout(function() {
                calculateLineTaxAmount($row);
            }, 100);
        } else {
            console.log('Tax Breakdown Module: Could not find tax cell for row', $row);
        }
    }

    function calculateLineTaxAmount($row) {
        var $taxAmountDisplay = $row.find('.tax-amount-display');
        
        if ($taxAmountDisplay.length === 0) {
            return;
        }

        // Find inputs
        var $rateInput = $row.find('td.rate input, input[name*="[rate]"]');
        var $qtyInput = $row.find('input[data-quantity], input[name*="[qty]"], input[name="quantity"]');
        var $taxSelect = $row.find('select.tax');

        if (!$rateInput.length || !$taxSelect.length) {
            return;
        }

        var rate = parseFloat($rateInput.val()) || 0;
        var qty = parseFloat($qtyInput.val()) || 1;
        var subtotal = rate * qty;
        var totalTax = 0;

        // Calculate total tax from all selected taxes
        $taxSelect.find('option:selected').each(function() {
            var taxPercent = parseFloat($(this).data('taxrate')) || 0;
            totalTax += (subtotal * taxPercent) / 100;
        });

        // Format and display tax amount
        var formattedTax = safeFormatMoney(totalTax);
        $taxAmountDisplay.html('<strong>' + formattedTax + '</strong>');
    }

    function safeFormatMoney(amount) {
        if (typeof format_money === 'function') {
            return format_money(amount, true);
        }
        if (typeof accounting !== 'undefined' && accounting.formatMoney) {
            return accounting.formatMoney(amount);
        }
        return amount.toFixed(2);
    }

})(jQuery);
</script>
