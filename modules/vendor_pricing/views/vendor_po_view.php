<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo $title; ?></title>
    <link href="<?php echo base_url('assets/css/reset.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo base_url('assets/plugins/font-awesome/css/font-awesome.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo base_url('assets/css/style.min.css'); ?>" rel="stylesheet">
    <style>
        @media print {
            .btn { display: none !important; }
            input[type="number"] { border: none; box-shadow: none; outline: none; background: transparent; padding: 0; margin: 0; -moz-appearance: textfield; }
            input[type="number"]::-webkit-inner-spin-button, input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
            #wrapper { margin: 0 !important; padding: 0 !important; }
            .panel_s { border: none !important; box-shadow: none !important; }
            body { background: #fff; }
        }
    </style>
</head>
<body class="clients">
    <div id="wrapper">
        <div id="content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel_s">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <td width="40%" class="bg-light">Purchase order number</td>
                                                    <td><?php echo html_entity_decode($pur_order->pur_order_number); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light">Purchase order name</td>
                                                    <td><?php echo html_entity_decode($pur_order->pur_order_name); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light">Approve Status</td>
                                                    <td><span class="label label-primary"><?php echo get_status_approve($pur_order->approve_status); ?></span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <td width="40%" class="bg-light">Order Date</td>
                                                    <td><?php echo _d($pur_order->order_date); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light">Delivery Date</td>
                                                    <td>
                                                        <div class="form-group mb-0">
                                                            <div class="input-group date">
                                                                <input type="text" class="form-control" value="<?php echo _d($pur_order->delivery_date); ?>" disabled>
                                                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light">Total</td>
                                                    <td><span id="top_total">0.00</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="text-right mtop10">
                                            <a href="#" onclick="window.print(); return false;" class="btn btn-default"><i class="fa fa-print"></i> <?php echo _l('print'); ?></a>
                                            <a href="<?php echo site_url('vendor_pricing/vendor_po/pdf/'.$pur_order->id.'/'.$pur_order->hash); ?>" class="btn btn-default"><i class="fa fa-file-pdf-o"></i> <?php echo _l('download_pdf', 'Download PDF'); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <?php if($status == 'pending'){ ?>
                                    <div class="alert alert-info">Price quote submitted and pending approval.</div>
                                <?php } elseif($status == 'accepted') { ?>
                                    <div class="alert alert-success">Prices accepted.</div>
                                <?php } ?>
                                
                                <?php echo form_open(site_url('vendor_pricing/vendor_po/view/'.$pur_order->id.'/'.$pur_order->hash)); ?>
                                <div class="table-responsive mtop15">
                                <table class="table items table-bordered">
                                    <thead>
                                        <tr class="bg-light">
                                            <th width="2%">#</th>
                                            <th width="15%">Items</th>
                                            <th width="5%">Quantity</th>
                                            <th width="8%">Unit Price</th>
                                            <th width="8%">Into money</th>
                                            <th width="8%">Tax</th>
                                            <th width="8%"><b>Tax Amount</b></th>
                                            <th width="8%">Sub total</th>
                                            <th width="8%">Discount</th>
                                            <th width="8%">Discount (money)</th>
                                            <th width="8%">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $i = 1;
                                        foreach ($pur_order_detail as $item) { 
                                            // Handle Tax display
                                            $tax_names = [];
                                            $tax_arr_json = [];
                                            if($item['tax'] != '' && $item['tax'] != null) {
                                                $tax_arr = explode('|', $item['tax']);
                                                $tax_rate_arr = explode('|', $item['tax_rate']);
                                                foreach($tax_arr as $k => $tn) {
                                                    $rate = isset($tax_rate_arr[$k]) ? $tax_rate_arr[$k] : 0;
                                                    $tax_names[] = $tn . ' (' . $rate . '%)';
                                                    $tax_arr_json[] = ['name' => $tn . ' (' . $rate . '%)', 'rate' => $rate];
                                                }
                                            } else {
                                                if(isset($item['tax_name']) && $item['tax_name'] != '') {
                                                    $tax_names[] = $item['tax_name'] . ' (' . $item['tax_rate'] . '%)';
                                                    $tax_arr_json[] = ['name' => $item['tax_name'] . ' (' . $item['tax_rate'] . '%)', 'rate' => $item['tax_rate']];
                                                }
                                            }

                                            $tax_display = empty($tax_names) ? '' : implode('<br>', $tax_names);
                                            $tax_data_json = htmlspecialchars(json_encode($tax_arr_json), ENT_QUOTES, 'UTF-8');
                                            
                                            $val = isset($vendor_prices[$item['item_code']]) ? $vendor_prices[$item['item_code']] : '';
                                            $readonly = ($status == 'accepted' || $status == 'rejected') ? 'readonly' : '';
                                            // Ensure discount exists
                                            $discount_percent = isset($item['discount_%']) ? $item['discount_%'] : 0;
                                        ?>
                                            <tr>
                                                <td><?php echo $i++; ?></td>
                                                <td>
                                                    <b><?php echo $item['description'] ? $item['description'] : $item['item_name']; ?></b>
                                                    <?php if(isset($item['long_description']) && $item['long_description'] != ''){ ?>
                                                        <br><span class="text-muted"><?php echo $item['long_description']; ?></span>
                                                    <?php } ?>
                                                </td>
                                                <td><?php echo number_format($item['quantity'], 2); ?></td>
                                                <td>
                                                    <input type="number" step="any" name="vendor_price[<?php echo $item['item_code']; ?>]" value="<?php echo $val; ?>" class="form-control vendor-price-input" data-item-code="<?php echo $item['item_code']; ?>" data-qty="<?php echo $item['quantity']; ?>" data-taxes='<?php echo $tax_data_json; ?>' data-discount-percent="<?php echo $discount_percent; ?>" <?php echo $readonly; ?> required>
                                                </td>
                                                <td class="text-right"><span class="into-money" id="into_money_<?php echo $item['item_code']; ?>">0.00</span></td>
                                                <td class="text-right"><span class="tax-value" id="tax_value_<?php echo $item['item_code']; ?>">0.00</span></td>
                                                <td class="text-right"><span class="tax-amount" id="tax_amount_<?php echo $item['item_code']; ?>">0.00</span></td>
                                                <td class="text-right"><span class="sub-total" id="sub_total_<?php echo $item['item_code']; ?>"></span></td>
                                                <td><?php echo number_format($discount_percent, 2); ?>%</td>
                                                <td><span class="discount-money" id="discount_money_<?php echo $item['item_code']; ?>">0.00</span></td>
                                                <td><span class="line-total" id="line_total_<?php echo $item['item_code']; ?>">0.00</span></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-md-offset-7">
                                        <table class="table text-right table-bordered" id="totals_table">
                                            <tbody>
                                                <tr id="subtotal_row">
                                                    <td><span class="bold">Subtotal</span></td>
                                                    <td width="35%" class="subtotal"><b id="po_subtotal">0.00</b></td>
                                                </tr>
                                                <?php if($pur_order->discount_percent > 0) { ?>
                                                <tr>
                                                    <td><span class="bold">Discount (<?php echo app_format_money($pur_order->discount_percent, ''); ?>%)</span></td>
                                                    <td class="subtotal"><b id="po_discount_money">0.00</b></td>
                                                </tr>
                                                <?php } ?>
                                                <tr id="total_row">
                                                    <td><span class="bold">Total</span></td>
                                                    <td class="total"><b id="po_total">0.00</b></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <?php if($status != 'accepted' && $status != 'rejected'){ ?>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                                </div>
                                <?php } ?>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js'); ?>"></script>
    <script>
        $(document).ready(function() {
            var po_discount_percent = <?php echo ($pur_order->discount_percent > 0) ? $pur_order->discount_percent : 0; ?>;

            function calculateTotals() {
                var global_subtotal = 0;
                var taxes_totals = {};

                $('.vendor-price-input').each(function() {
                    var $row = $(this);
                    var item_code = $row.data('item-code');
                    var qty = parseFloat($row.data('qty')) || 0;
                    var price = parseFloat($row.val()) || 0;
                    
                    var taxesStr = $row.attr('data-taxes') || '[]';
                    var taxes = [];
                    try { taxes = JSON.parse(taxesStr); } catch(e){}
                    
                    var discount_percent = parseFloat($row.data('discount-percent')) || 0;
                    
                    // Into Money
                    var into_money = qty * price;
                    $('#into_money_' + item_code).text(into_money.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    
                    // Tax Calculation
                    var row_tax_amount = 0;
                    if(taxes.length > 0) {
                        $.each(taxes, function(i, taxObj) {
                            var t_rate = parseFloat(taxObj.rate) || 0;
                            var t_name = taxObj.name || '';
                            var t_amt = (into_money * (t_rate / 100));
                            row_tax_amount += t_amt;
                            
                            if(!taxes_totals[t_name]) {
                                taxes_totals[t_name] = 0;
                            }
                            taxes_totals[t_name] += t_amt;
                        });
                    }
                    // Perfex "Tax" value is just row_tax_amount (e.g. Dhs21.00 in screenshot)
                    $('#tax_value_' + item_code).text(row_tax_amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    
                    // Perfex "Tax Amount" corresponds to Into Money + Tax (Sub total)
                    var tax_amount_col = into_money + row_tax_amount;
                    $('#tax_amount_' + item_code).text(tax_amount_col.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                    // Sub total is blank in screenshot, but we'll leave it empty to match
                    $('#sub_total_' + item_code).text('');
                    // Discount Money
                    var discount_money = tax_amount_col * (discount_percent / 100);
                    $('#discount_money_' + item_code).text(discount_money.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                    // Line Total
                    var line_total = tax_amount_col - discount_money;
                    $('#line_total_' + item_code).text(line_total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                    // Global subtotal sums "Into Money" according to screenshot
                    global_subtotal += into_money;
                });
                
                $('#po_subtotal').text(global_subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                
                // Process Global Discount
                var total_discount = 0;
                if(po_discount_percent > 0) {
                    total_discount = global_subtotal * (po_discount_percent / 100);
                    $('#po_discount_money').text('-' + total_discount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                }

                // Render dynamic tax rows right before the Total row
                $('.dynamic-tax-row').remove();
                var global_tax_total = 0;
                for (const [taxName, taxAmt] of Object.entries(taxes_totals)) {
                    global_tax_total += taxAmt;
                    var taxRow = '<tr class="dynamic-tax-row"><td><span class="bold">' + taxName + '</span></td><td class="subtotal">' + taxAmt.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td></tr>';
                    $(taxRow).insertBefore('#total_row');
                }

                // Grand total
                var grand_total = (global_subtotal - total_discount) + global_tax_total;
                $('#po_total').text(grand_total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#top_total').text(grand_total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            }

            $('.vendor-price-input').on('input', calculateTotals);
            calculateTotals();
        });
    </script>
</body>
</html>
