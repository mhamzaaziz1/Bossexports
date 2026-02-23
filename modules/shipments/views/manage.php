<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <?php if (has_permission('shipments', '', 'create')) { ?>
                            <a href="<?php echo admin_url('shipments/shipment'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_shipment'); ?></a>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <table class="table dt-table" data-order-col="0" data-order-type="desc">
                            <thead>
                                <tr>
                                    <th><?php echo _l('shipment_number'); ?></th>
                                    <th><?php echo _l('carrier'); ?></th>
                                    <th><?php echo _l('etd'); ?></th>
                                    <th><?php echo _l('eta'); ?></th>
                                    <th><?php echo _l('status'); ?></th>
                                    <th><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shipments as $shipment) { ?>
                                <tr>
                                    <td><a href="<?php echo admin_url('shipments/shipment/' . $shipment->id); ?>"><?php echo $shipment->shipment_number; ?></a></td>
                                    <td><?php echo $shipment->carrier; ?></td>
                                    <td><?php echo _d($shipment->etd); ?></td>
                                    <td><?php echo _d($shipment->eta); ?></td>
                                    <td>
                                        <span class="label label-<?php if($shipment->status == 'Closed') echo 'success'; elseif($shipment->status == 'Draft') echo 'default'; else echo 'info'; ?>">
                                            <?php echo $shipment->status; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo admin_url('shipments/shipment/' . $shipment->id); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>
                                        <?php if (has_permission('shipments', '', 'delete')) { ?>
                                        <a href="<?php echo admin_url('shipments/delete/' . $shipment->id); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                                        <?php } ?>
                                    </td>
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
<?php init_tail(); ?>
