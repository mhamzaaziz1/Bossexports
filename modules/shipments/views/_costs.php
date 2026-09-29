<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="mtop10">
             <button type="button" class="btn btn-info" data-toggle="modal" data-target="#add_cost_modal">
                 <i class="fa fa-plus"></i> <?php echo _l('add_cost'); ?>
             </button>
             <button type="button" class="btn btn-success" onclick="recalculate_costs()">
                 <i class="fa fa-calculator"></i> <?php echo _l('recalculate_preview'); ?>
             </button>
         </div>
         <div class="clearfix"></div>
         <br />
         
         <div class="table-responsive">
             <table class="table costs" data-type="shipment">
                 <thead>
                     <tr>
                         <th><?php echo _l('cost_name'); ?></th>
                         <th><?php echo _l('amount'); ?></th>
                         <th><?php echo _l('currency'); ?></th>
                         <th><?php echo _l('exchange_rate'); ?></th>
                         <th><?php echo _l('allocation_method'); ?></th>
                         <th><?php echo _l('options'); ?></th>
                     </tr>
                 </thead>
                 <tbody>
                     <?php 
                     if(isset($shipment->costs)){
                         foreach($shipment->costs as $cost){ 
                         ?>
                         <tr>
                             <td>
                                 <?php echo $cost['cost_name']; ?>
                                 <br />
                                 <small class="text-muted"><?php echo _l('label_layer'); ?>: <?php echo $cost['layer_level']; ?></small>
                             </td>
                             <td><?php echo app_format_money($cost['total_amount'], $cost['currency']); ?></td>
                             <td><?php echo $cost['currency']; ?></td>
                             <td><?php echo $cost['exchange_rate']; ?></td>
                             <td><?php echo $cost['allocation_method'] ? ucfirst($cost['allocation_method']) : _l('default'); ?></td>
                             <td>
                                 <a href="#" onclick="delete_cost(<?php echo $cost['id']; ?>); return false;" class="btn btn-danger btn-xs"><i class="fa fa-times"></i></a>
                             </td>
                         </tr>
                     <?php } 
                     } else { ?>
                         <tr>
                             <td colspan="6" class="text-center"><?php echo _l('no_costs'); ?></td>
                         </tr>
                     <?php } ?>
                 </tbody>
             </table>
         </div>
         
         <hr />
         <h4><?php echo _l('calculation_preview'); ?></h4>
         <div id="calculation_result" class="text-muted">
             <?php echo _l('click_recalculate_to_view'); ?>
         </div>
    </div>
</div>

<!-- Add Cost Modal -->
<div class="modal fade" id="add_cost_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('add_cost'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><?php echo _l('cost_name'); ?></label>
                    <select id="cost_def_select" class="form-control selectpicker" data-live-search="true">
                        <?php 
                        $defs = $this->shipments_model->get_cost_definitions(); 
                        foreach($defs as $def) {
                            echo '<option value="'.$def['id'].'">'.$def['name'] . ' (Layer '.$def['layer_level'].')</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                         <?php echo render_input('cost_amount', 'amount', '', 'number'); ?>
                    </div>
                    <div class="col-md-6">
                         <?php echo render_input('cost_currency', 'currency', 'USD'); ?>
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-6">
                         <?php echo render_input('cost_rate', 'exchange_rate', '1.0000', 'number', ['step'=>'0.0001']); ?>
                    </div>
                    <div class="col-md-6">
                         <div class="form-group">
                            <label><?php echo _l('allocation_method'); ?></label>
                            <select id="cost_method" class="form-control selectpicker">
                                <option value=""><?php echo _l('default'); ?></option>
                                <option value="value"><?php echo _l('allocation_value'); ?></option>
                                <option value="weight"><?php echo _l('allocation_weight'); ?></option>
                                <option value="volume"><?php echo _l('allocation_volume'); ?></option>
                                <option value="quantity"><?php echo _l('allocation_quantity'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-info" onclick="add_cost()"><?php echo _l('add'); ?></button>
            </div>
        </div>
    </div>
</div>
<!-- Calculation Result Modal -->
<div class="modal fade" id="calculation_result_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('calculation_preview'); ?></h4>
            </div>
            <div class="modal-body" id="calculation_modal_body">
                <!-- Content injected via JS -->
                 <p class="text-center"><i class="fa fa-spinner fa-spin"></i> Calculating...</p>
            </div>
            <div class="modal-footer">
                 <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
        </div>
    </div>
</div>
