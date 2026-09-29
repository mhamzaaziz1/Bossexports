<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo form_open($this->uri->uri_string(), ['id' => 'shipment-form']); ?>
        <input type="hidden" id="shipment_id" name="shipment_id" value="<?php echo isset($shipment) ? $shipment->id : ''; ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />

                        <div class="row">
                            <div class="col-md-6">
                                <?php $value = (isset($shipment) ? $shipment->shipment_number : ''); ?>
                                <?php echo render_input('shipment_number', 'shipment_number', $value); ?>

                                <?php $value = (isset($shipment) ? $shipment->carrier : ''); ?>
                                <?php echo render_input('carrier', 'carrier', $value); ?>

                                <div class="row">
                                    <div class="col-md-6">
                                        <?php $value = (isset($shipment) ? _d($shipment->etd) : ''); ?>
                                        <?php echo render_date_input('etd', 'etd', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($shipment) ? _d($shipment->eta) : ''); ?>
                                        <?php echo render_date_input('eta', 'eta', $value); ?>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php $value = (isset($shipment) ? $shipment->country_of_origin : ''); ?>
                                        <?php echo render_input('country_of_origin', 'Country of Origin (ISO)', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($shipment) ? $shipment->incoterm : ''); ?>
                                        <?php echo render_input('incoterm', 'Incoterm (e.g. FOB, CIF)', $value); ?>
                                    </div>
                                </div>
                                
                                <?php $value = (isset($shipment) ? $shipment->transport_mode : ''); ?>
                                <?php echo render_select('transport_mode', [['id'=>'Ocean', 'name'=>'Ocean'],['id'=>'Air', 'name'=>'Air'],['id'=>'Road', 'name'=>'Road']], ['id', 'name'], 'Transport Mode', $value); ?>
                            </div>
                            <div class="col-md-6">
                                <?php 
                                    $statuses = [
                                        ['id'=>'Draft', 'name'=>_l('shipment_status_draft')],
                                        ['id'=>'On Water', 'name'=>_l('shipment_status_on_water')],
                                        ['id'=>'Customs', 'name'=>_l('shipment_status_customs')],
                                        ['id'=>'Landed', 'name'=>_l('shipment_status_landed')],
                                        ['id'=>'Closed', 'name'=>_l('shipment_status_closed')],
                                    ];
                                    $value = (isset($shipment) ? $shipment->status : 'Draft');
                                    echo render_select('status', $statuses, ['id', 'name'], 'status', $value);
                                ?>

                                <?php if(isset($shipment)) { ?>
                                <div class="form-group">
                                    <label>AI Compliance Risk Check</label><br/>
                                    <?php 
                                        $comp_class = 'default';
                                        if($shipment->compliance_status == 'Pass') $comp_class = 'success';
                                        elseif(strpos($shipment->compliance_status, 'Failed') !== false) $comp_class = 'danger';
                                        elseif($shipment->compliance_status == 'Pending') $comp_class = 'warning';
                                    ?>
                                    <span class="label label-<?php echo $comp_class; ?>" style="font-size:14px; padding: 6px;"><?php echo $shipment->compliance_status; ?></span>
                                </div>
                                <?php } ?>

                                <?php $value = (isset($shipment) ? $shipment->currency_base : 'USD'); ?>
                                <?php echo render_input('currency_base', 'currency_base', $value); ?>
                                
                                <?php $value = (isset($shipment) ? $shipment->exchange_rate_fixed : '1.0000'); ?>
                                <?php echo render_input('exchange_rate_fixed', 'exchange_rate_fixed', $value, 'number', ['step' => '0.0001']); ?>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php $value = (isset($shipment) ? $shipment->port_of_loading : ''); ?>
                                        <?php echo render_input('port_of_loading', 'Port of Loading', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($shipment) ? $shipment->port_of_discharge : ''); ?>
                                        <?php echo render_input('port_of_discharge', 'Port of Discharge', $value); ?>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php $value = (isset($shipment) ? $shipment->bl_awb_number : ''); ?>
                                        <?php echo render_input('bl_awb_number', 'BL / AWB Number', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($shipment) ? $shipment->vessel_flight : ''); ?>
                                        <?php echo render_input('vessel_flight', 'Vessel / Flight', $value); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                            <?php if(isset($shipment)) { ?>
                            
                            <div class="btn-group dropup">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa-regular fa-file-pdf"></i> Generate Docs <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="<?php echo admin_url('shipments/generate_compliance_pack/' . $shipment->id . '/commercial_invoice'); ?>" target="_blank">Commercial Invoice</a></li>
                                    <li><a href="<?php echo admin_url('shipments/generate_compliance_pack/' . $shipment->id . '/packing_list'); ?>" target="_blank">Packing List</a></li>
                                    <li><a href="<?php echo admin_url('shipments/generate_compliance_pack/' . $shipment->id . '/certificate_of_origin'); ?>" target="_blank">Certificate of Origin</a></li>
                                    <li role="separator" class="divider"></li>
                                    <li><a href="<?php echo admin_url('shipments/pdf/' . $shipment->id); ?>" target="_blank">Shipment Overview (Legacy)</a></li>
                                </ul>
                            </div>
                            
                            <?php if($shipment->status != 'Closed') { ?>
                            <a href="<?php echo admin_url('shipments/commit/' . $shipment->id); ?>" class="btn btn-success" onclick="return confirm('<?php echo _l('are_you_sure_commit'); ?>');"><?php echo _l('commit_shipment'); ?></a>
                            <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if(isset($shipment)) { ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="panel_s">
                    <div class="panel-body">
                         <div class="horizontal-scrollable-tabs preview-tabs-top">
                             <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                             <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                             <div class="horizontal-tabs">
                                 <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                                     <li role="presentation" class="active">
                                         <a href="#tab_lines" aria-controls="tab_lines" role="tab" data-toggle="tab">
                                             <?php echo _l('shipment_lines'); ?>
                                         </a>
                                     </li>
                                     <li role="presentation">
                                         <a href="#tab_costs" aria-controls="tab_costs" role="tab" data-toggle="tab">
                                             <?php echo _l('shipment_costs'); ?>
                                         </a>
                                     </li>
                                     <li role="presentation">
                                         <a href="#tab_milestones" aria-controls="tab_milestones" role="tab" data-toggle="tab">
                                             Milestones
                                         </a>
                                     </li>
                                     <li role="presentation">
                                         <a href="#tab_documents" aria-controls="tab_documents" role="tab" data-toggle="tab">
                                             Documents
                                         </a>
                                     </li>
                                 </ul>
                             </div>
                         </div>
                         
                         <div class="tab-content">
                             <div role="tabpanel" class="tab-pane active" id="tab_lines">
                                 <?php $this->load->view('_lines'); ?>
                             </div>
                             <div role="tabpanel" class="tab-pane" id="tab_costs">
                                 <?php $this->load->view('_costs'); ?>
                             </div>
                             <div role="tabpanel" class="tab-pane" id="tab_milestones">
                                 <?php $this->load->view('_milestones'); ?>
                             </div>
                             <div role="tabpanel" class="tab-pane" id="tab_documents">
                                 <?php $this->load->view('_documents'); ?>
                             </div>
                         </div>
                    </div>
                 </div>
            </div>
        </div>
        <?php } ?>
        
        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
