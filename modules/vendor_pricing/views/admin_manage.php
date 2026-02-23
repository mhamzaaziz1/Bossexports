<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('vendor_pricing'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <table class="table dt-table">
                            <thead>
                                <tr>
                                    <th><?php echo _l('po_number'); ?></th>
                                    <th><?php echo _l('vendor'); ?></th>
                                    <th><?php echo _l('date'); ?></th>
                                    <th><?php echo _l('status'); ?></th>
                                    <th><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pos as $po){ ?>
                                <tr>
                                    <td><a href="<?php echo admin_url('purchase/purchase_order/'.$po['pur_order_id']); ?>"><?php echo $po['pur_order_number'] . ' - ' . $po['pur_order_name']; ?></a></td>
                                    <td><?php echo $po['vendor_name']; ?></td>
                                    <td><?php echo _dt($po['date_submitted']); ?></td>
                                    <td><?php echo '<span class="label label-'.($po['status'] == 'accepted' ? 'success' : ($po['status'] == 'rejected' ? 'danger' : 'info')).'">'.ucfirst($po['status']).'</span>'; ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('vendor_pricing/view/'.$po['pur_order_id']); ?>" class="btn btn-default btn-icon"><i class="fa fa-eye"></i></a>
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
