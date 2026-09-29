<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('vendor_pricing'); ?> - <?php echo $pur_order->pur_order_number; ?></h4>
                        <hr class="hr-panel-heading" />
                        
                        <?php if($status == 'pending') { ?>
                            <div class="alert alert-info">Prices are pending review.</div>
                        <?php } elseif($status == 'accepted') { ?>
                            <div class="alert alert-success">Prices have been accepted.</div>
                        <?php } elseif($status == 'rejected') { ?>
                            <div class="alert alert-danger">Prices have been rejected.</div>
                        <?php } ?>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th><?php echo _l('item'); ?></th>
                                    <th><?php echo _l('quantity'); ?></th>
                                    <th><?php echo _l('original_price'); ?></th>
                                    <th><?php echo _l('vendor_price'); ?></th>
                                    <th><?php echo _l('difference'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pur_order_detail as $item) { 
                                    $vp = isset($vendor_prices[$item['item_code']]) ? $vendor_prices[$item['item_code']] : 0;
                                    $orig = $item['unit_price'];
                                    $diff = $vp - $orig;
                                ?>
                                    <tr>
                                        <td><?php echo $item['description'] ? $item['description'] : $item['item_name']; ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo app_format_money($orig, ''); ?></td>
                                        <td><?php echo $vp ? app_format_money($vp, '') : '-'; ?></td>
                                        <td>
                                            <?php if($vp) { ?>
                                                <span class="text-<?php echo $diff > 0 ? 'danger' : ($diff < 0 ? 'success' : 'muted'); ?>">
                                                    <?php echo $diff > 0 ? '+' : ''; ?><?php echo app_format_money($diff, ''); ?>
                                                </span>
                                            <?php } else { echo '-'; } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <?php if($status == 'pending'){ ?>
                            <div class="btn-bottom-toolbar text-right">
                                <a href="<?php echo admin_url('vendor_pricing/reject/'.$pur_order->id); ?>" class="btn btn-danger"><?php echo _l('reject_vendor_price'); ?></a>
                                <a href="<?php echo admin_url('vendor_pricing/accept/'.$pur_order->id); ?>" class="btn btn-success"><?php echo _l('accept_vendor_price'); ?></a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
