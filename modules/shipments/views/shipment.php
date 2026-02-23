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

                                <?php $value = (isset($shipment) ? $shipment->currency_base : 'USD'); ?>
                                <?php echo render_input('currency_base', 'currency_base', $value); ?>
                                
                                <?php $value = (isset($shipment) ? $shipment->exchange_rate_fixed : '1.0000'); ?>
                                <?php echo render_input('exchange_rate_fixed', 'exchange_rate_fixed', $value, 'number', ['step' => '0.0001']); ?>
                            </div>
                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                            <?php if(isset($shipment)) { ?>
                            <a href="<?php echo admin_url('shipments/pdf/' . $shipment->id); ?>" class="btn btn-default" target="_blank"><i class="fa fa-file-pdf-o"></i> <?php echo _l('download_pdf'); ?></a>
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
