<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-6">
                       <!-- Header removed -->
                    </div>
                    <div class="col-md-6 text-right">
                        <?php echo form_open(admin_url('advance_analytics/full_report'), ['class'=>'form-inline']); ?>
                            <div class="form-group">
                                <label for="report_year" class="control-label">Year: </label>
                                <select class="form-control" name="report_year" onchange="this.form.submit()">
                                    <?php 
                                    $current_year = date('Y');
                                    for($y = $current_year; $y >= $current_year - 4; $y--){
                                        $selected = ($y == $selected_year) ? 'selected' : '';
                                        echo "<option value='$y' $selected>$y</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php if(has_permission('reports', '', 'edit')){ ?>
                                <a href="<?php echo admin_url('advance_analytics/settings'); ?>" class="btn btn-default mleft10" data-toggle="tooltip" title="Configure Widgets"><i class="fa fa-cog"></i></a>
                            <?php } ?>
                        <?php echo form_close(); ?>
                    </div>
                </div>
                <hr class="hr-panel-heading" />
            </div>
        </div>
        
        <div class="row mtop20">
            <?php foreach($widgets as $widget): ?>
                <div class="col-md-<?php echo $widget['col']; ?> col-sm-12">
                   <?php 
                        // Load widget view
                        // Pass 'widget_data' and 'widget_meta'
                        $this->load->view($widget['view'], [
                            'widget_data' => $widget['data'],
                            'widget_meta' => $widget
                        ]); 
                   ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function(){
        Chart.defaults.global.defaultFontFamily = 'Roboto';
        // Charts will initialize themselves via their collected scripts or inline scripts
    });
</script>
