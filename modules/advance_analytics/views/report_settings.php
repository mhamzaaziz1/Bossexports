<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Report Configuration</h4>
                        <hr class="hr-panel-heading" />
                        
                        <?php echo form_open(admin_url('advance_analytics/save_settings')); ?>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Enabled</th>
                                        <th>Widget Name</th>
                                        <th>Category</th>
                                        <th>Width (Columns)</th>
                                        <th>Order</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Merge available with saved
                                    $widgets_to_show = $available_widgets;
                                    
                                    // Prepare saved lookup
                                    $saved_lookup = [];
                                    if(is_array($saved_settings)){
                                        foreach($saved_settings as $id => $conf){
                                            $saved_lookup[$id] = $conf;
                                        }
                                    }
                                    
                                    // Sort by saved order if exists, otherwise default
                                    uasort($widgets_to_show, function($a, $b) use ($saved_lookup){
                                        $order_a = isset($saved_lookup[$a['id']]['order']) ? $saved_lookup[$a['id']]['order'] : 99;
                                        $order_b = isset($saved_lookup[$b['id']]['order']) ? $saved_lookup[$b['id']]['order'] : 99;
                                        return $order_a - $order_b;
                                    });
                                    ?>
                                    
                                    <?php foreach($widgets_to_show as $widget): ?>
                                        <?php 
                                            $is_enabled = true; // Default to true
                                            $order = 10;
                                            
                                            if(isset($saved_lookup[$widget['id']])){
                                                $is_enabled = isset($saved_lookup[$widget['id']]['enabled']) && $saved_lookup[$widget['id']]['enabled'] == 1;
                                                $order = isset($saved_lookup[$widget['id']]['order']) ? $saved_lookup[$widget['id']]['order'] : 10;
                                            }
                                        ?>
                                        <tr>
                                            <td style="width:50px;">
                                                <div class="checkbox checkbox-primary">
                                                    <input type="checkbox" name="widgets[<?php echo $widget['id']; ?>][enabled]" value="1" <?php if($is_enabled) echo 'checked'; ?>>
                                                    <label></label>
                                                </div>
                                            </td>
                                            <td><?php echo $widget['title']; ?></td>
                                            <td><span class="label label-default"><?php echo ucfirst($widget['category']); ?></span></td>
                                            <td><?php echo $widget['col']; ?> / 12</td>
                                            <td style="width:100px;">
                                                <input type="number" name="widgets[<?php echo $widget['id']; ?>][order]" class="form-control" value="<?php echo $order; ?>">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-right">
                            <button type="submit" class="btn btn-info"><?php echo _l('save'); ?></button>
                        </div>
                        <?php echo form_close(); ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
