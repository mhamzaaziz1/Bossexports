<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Fast Load & Caching Settings</h4>
                        <hr class="hr-panel-heading" />

                        <?php echo form_open(admin_url('fast_load/settings')); ?>
                        
                        <div class="form-group">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="fast_load_minify_html" id="fast_load_minify_html" value="1" <?php if(get_option('fast_load_minify_html') == '1'){echo 'checked';} ?>>
                                <label for="fast_load_minify_html">Enable HTML Minification (Removes spaces, comments, and newlines to reduce page size)</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="fast_load_browser_cache" id="fast_load_browser_cache" value="1" <?php if(get_option('fast_load_browser_cache') == '1'){echo 'checked';} ?>>
                                <label for="fast_load_browser_cache">Add Cache-Control Headers (Leverages browser caching for certain outputs)</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="fast_load_enable_db_cache" id="fast_load_enable_db_cache" value="1" <?php if(get_option('fast_load_enable_db_cache') == '1'){echo 'checked';} ?>>
                                <label for="fast_load_enable_db_cache">Enable Basic DB Caching (WARNING: Might deliver stale data in some CRM parts)</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-info pull-right">Save Settings</button>
                        <?php echo form_close(); ?>

                        <div class="clearfix"></div>
                        <hr />
                        <h4>Cache Management</h4>
                        <p>If you made changes to settings, templates, or feel the system is serving old content, clear the system cache here.</p>
                        <a href="<?php echo admin_url('fast_load/clear_cache'); ?>" class="btn btn-warning"><i class="fa fa-trash"></i> Clear Application Cache</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
