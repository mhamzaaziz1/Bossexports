<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo $title; ?></title>
    <?php echo app_compile_css(); ?>
</head>
<body class="clients">
    <div id="wrapper">
        <div id="content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel_s">
                            <div class="panel-body">
                                <h3><?php echo _l('pur_order') . ' ' . $pur_order->pur_order_number; ?> - <?php echo $pur_order->pur_order_name; ?></h3>
                                <?php if($status == 'pending'){ ?>
                                    <div class="alert alert-info">Price quote submitted and pending approval.</div>
                                <?php } elseif($status == 'accepted') { ?>
                                    <div class="alert alert-success">Prices accepted.</div>
                                <?php } ?>
                                
                                <?php echo form_open(site_url('vendor_pricing/vendor_po/view/'.$pur_order->id.'/'.$pur_order->hash)); ?>
                                <table class="table items">
                                    <thead>
                                        <tr>
                                            <th><?php echo _l('item'); ?></th>
                                            <th><?php echo _l('quantity'); ?></th>
                                            <th><?php echo _l('vendor_price'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pur_order_detail as $item) { ?>
                                            <tr>
                                                <td><?php echo $item['description'] ? $item['description'] : $item['item_name']; ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td>
                                                    <?php 
                                                        $val = isset($vendor_prices[$item['item_code']]) ? $vendor_prices[$item['item_code']] : '';
                                                        $readonly = ($status == 'accepted' || $status == 'rejected') ? 'readonly' : '';
                                                    ?>
                                                    <input type="number" step="any" name="vendor_price[<?php echo $item['item_code']; ?>]" value="<?php echo $val; ?>" class="form-control" <?php echo $readonly; ?> required>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                                
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
</body>
</html>
