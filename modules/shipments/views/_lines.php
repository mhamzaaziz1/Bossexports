<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
     <div class="col-md-12">
         <div class="mtop10">
             <button type="button" class="btn btn-info" data-toggle="modal" data-target="#add_line_modal">
                 <i class="fa fa-plus"></i> <?php echo _l('add_item'); ?>
             </button>
             <button type="button" class="btn btn-default" data-toggle="modal" data-target="#add_from_po_modal">
                 <i class="fa fa-download"></i> <?php echo _l('pull_from_po'); ?>
             </button>
         </div>
         
         <div class="clearfix"></div>
         <br />
         
         <div class="table-responsive">
             <table class="table items items-preview invoice-items-preview" data-type="shipment">
                 <thead>
                     <tr>
                         <th>#</th>
                         <th><?php echo _l('item'); ?></th>
                         <th><?php echo _l('qty'); ?></th>
                         <th><?php echo _l('unit_fob_price'); ?></th>
                         <th><?php echo _l('net_weight_kg'); ?> (Total)</th>
                         <th><?php echo _l('volume_cbm'); ?> (Total)</th>
                         <th><?php echo _l('duty_percent'); ?></th>
                         <th><?php echo _l('landed_cost'); ?></th>
                         <th><?php echo _l('options'); ?></th>
                     </tr>
                 </thead>
                 <tbody>
                     <?php 
                     if(isset($shipment->lines)){
                         foreach($shipment->lines as $line){ 
                            // Fetch item name details
                            $item = $this->db->get_where(db_prefix() . 'items', ['id' => $line['item_id']])->row();
                            $item_name = $item ? $item->description : 'Unknown Item';
                         ?>
                         <tr data-id="<?php echo $line['id']; ?>">
                             <td><?php echo $line['id']; ?></td>
                             <td><?php echo $item_name; ?><br /><small class="text-muted"><?php echo _l('po_ref'); ?>: <?php echo $line['po_ref_id'] ? $line['po_ref_id'] : 'N/A'; ?></small></td>
                             <td><?php echo $line['qty_shipped']; ?></td>
                             <td><?php echo app_format_money($line['unit_fob_price'], $shipment->currency_base); ?></td>
                             <td><?php echo $line['net_weight_kg']; ?></td>
                             <td><?php echo $line['volume_cbm']; ?></td>
                             <td><?php echo $line['duty_percent']; ?>%</td>
                             <td><?php echo app_format_money($line['landed_cost'], $shipment->currency_base); ?></td>
                             <td>
                                 <a href="#" onclick="delete_shipment_line(<?php echo $line['id']; ?>); return false;" class="btn btn-danger btn-xs"><i class="fa fa-times"></i></a>
                             </td>
                         </tr>
                     <?php } 
                     } else { ?>
                         <tr>
                             <td colspan="9" class="text-center"><?php echo _l('no_items'); ?></td>
                         </tr>
                     <?php } ?>
                 </tbody>
             </table>
         </div>
     </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="add_line_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('add_item'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><?php echo _l('item'); ?></label>
                    <select id="item_select" class="form-control ajax-search" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-live-search="true">
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                         <?php echo render_input('new_qty', 'qty', '1', 'number'); ?>
                    </div>
                    <div class="col-md-6">
                         <?php echo render_input('new_fob', 'unit_fob_price', '0', 'number'); ?>
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-4">
                         <?php echo render_input('new_weight', 'net_weight_kg', '0', 'number'); ?>
                    </div>
                    <div class="col-md-4">
                         <?php echo render_input('new_volume', 'volume_cbm', '0', 'number'); ?>
                    </div>
                    <div class="col-md-4">
                         <?php echo render_input('new_duty', 'duty_percent', '0', 'number'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-info" onclick="add_manual_item()"><?php echo _l('add'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Add From PO Modal -->
<div class="modal fade" id="add_from_po_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('pull_from_quotation'); ?></h4>
            </div>
            <div class="modal-body">
                 <div class="form-group">
                    <label><?php echo _l('quotation'); ?></label>
                    <select id="po_select" class="form-control">
                        <option value=""></option>
                    </select>
                </div>
                <div id="po_items_container"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-info" onclick="add_po_items()"><?php echo _l('add_selected'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="edit_line_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('edit_item'); ?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_line_id">
                <div class="form-group">
                    <label><?php echo _l('label_item'); ?></label>
                    <input type="text" id="edit_item_name" class="form-control" disabled>
                </div>
                <div class="row">
                    <div class="col-md-6">
                         <?php echo render_input('edit_qty', 'qty', '', 'number'); ?>
                    </div>
                    <div class="col-md-6">
                         <?php echo render_input('edit_fob', 'unit_fob_price', '', 'number'); ?>
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-4">
                         <?php echo render_input('edit_weight', 'net_weight_kg', '', 'number'); ?>
                    </div>
                    <div class="col-md-4">
                         <?php echo render_input('edit_volume', 'volume_cbm', '', 'number'); ?>
                    </div>
                    <div class="col-md-4">
                         <?php echo render_input('edit_duty', 'duty_percent', '', 'number'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-info" onclick="save_line_edit()"><?php echo _l('save'); ?></button>
            </div>
        </div>
    </div>
</div>
