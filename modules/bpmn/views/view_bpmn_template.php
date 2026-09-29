<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-header task-single-header" data-task-single-id="<?php echo $bpmn->id; ?>" >
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?php echo $bpmn->title; ?></h4>
</div>
<div class="modal-body">

    <div class="row">
        <div class="col-md-8 task-single-col-left">
            <div class="bpmn_preview_cover"></div>
            <iframe src="<?php echo base_url();?>modules/bpmn/views/view_iframe.php?filename=<?php echo $bpmn->bpmn_content;?>" height="500" width="500" class="iframe_style" title="Iframe Example"></iframe>
            <div class="clearfix"></div>

        </div>
        <div class="col-md-4 task-single-col-right">
            <h4 class="task-info-heading"><?php echo _l('bpmn_info'); ?></h4>
            <div class="clearfix"></div>
            <h5 class="no-mtop task-info-created">
                <small class="text-dark"><?php echo _l('task_created_at','<span class="text-dark">'._dt($bpmn->dateadded).'</span>'); ?></small>
            </h5>

            <hr class="task-info-separator">

            <div class="task-info task-info-billable">
                <h5><i class="fa task-info-icon fa-fw fa-lg pull-left fa fa-user-o"></i>
                    <?php echo _l('bpmn_filter_staff'); ?>: <?php echo ($staff)?$staff->firstname.' '.$staff->lastname:'';?>
                </h5>
            </div>

            <div class="task-info task-info-billable">
                <h5><i class="fa task-info-icon fa-fw fa-lg pull-left fa fa-cog"></i>
                    <?php echo _l('bpmn_filter_group'); ?>: <?php echo ($group)?$group->name:'';?>
                </h5>
            </div>

        </div>
    </div>
</div>
 <link rel="stylesheet" href="<?php echo base_url();?>modules/bpmn/assets/css/template.css">
