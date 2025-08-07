<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="genz-theme-settings">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="gradient-text">GenZ Theme Settings</h4>
                </div>
                <div class="card-body">
                    <?php echo form_open(admin_url('genz_theme/save_settings')); ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel_s">
                                <div class="panel-heading">
                                    <h4>General Settings</h4>
                                </div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label for="genz_theme_staff" class="control-label">
                                            <div class="checkbox checkbox-primary">
                                                <input type="checkbox" name="genz_theme_staff" id="genz_theme_staff" <?php if (get_option('genz_theme_staff') == '1') { echo 'checked'; } ?>>
                                                <label for="genz_theme_staff">Enable theme for staff</label>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="genz_theme_customers" class="control-label">
                                            <div class="checkbox checkbox-primary">
                                                <input type="checkbox" name="genz_theme_customers" id="genz_theme_customers" <?php if (get_option('genz_theme_customers') == '1') { echo 'checked'; } ?>>
                                                <label for="genz_theme_customers">Enable theme for customers</label>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="genz_theme_dark_mode" class="control-label">
                                            <div class="checkbox checkbox-primary">
                                                <input type="checkbox" name="genz_theme_dark_mode" id="genz_theme_dark_mode" <?php if (get_option('genz_theme_dark_mode') == '1') { echo 'checked'; } ?>>
                                                <label for="genz_theme_dark_mode">Enable dark mode by default</label>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="genz_theme_animations" class="control-label">
                                            <div class="checkbox checkbox-primary">
                                                <input type="checkbox" name="genz_theme_animations" id="genz_theme_animations" <?php if (get_option('genz_theme_animations') == '1') { echo 'checked'; } ?>>
                                                <label for="genz_theme_animations">Enable animations</label>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="panel_s">
                                <div class="panel-heading">
                                    <h4>Color Settings</h4>
                                </div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label for="genz_theme_accent_color" class="control-label">Accent Color</label>
                                        <div class="input-group colorpicker-input">
                                            <input type="text" name="genz_theme_accent_color" id="genz_theme_accent_color" class="form-control" value="<?php echo get_option('genz_theme_accent_color'); ?>">
                                            <div class="input-group-addon">
                                                <div class="color-preview" style="background-color: <?php echo get_option('genz_theme_accent_color'); ?>"></div>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Primary accent color for buttons, links, and highlights</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="genz_theme_secondary_color" class="control-label">Secondary Color</label>
                                        <div class="input-group colorpicker-input">
                                            <input type="text" name="genz_theme_secondary_color" id="genz_theme_secondary_color" class="form-control" value="<?php echo get_option('genz_theme_secondary_color'); ?>">
                                            <div class="input-group-addon">
                                                <div class="color-preview" style="background-color: <?php echo get_option('genz_theme_secondary_color'); ?>"></div>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Secondary color for gradients and alternate elements</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="panel_s">
                                <div class="panel-heading">
                                    <h4>Theme Preview</h4>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Light Mode</h5>
                                            <div class="theme-preview light-mode">
                                                <div class="preview-header" style="background-color: #fff; border-bottom: 1px solid #E2E8F0; padding: 10px;">
                                                    <div class="preview-logo" style="background-color: var(--genz-accent-color); color: white; display: inline-block; padding: 5px 10px; border-radius: 5px;">GenZ</div>
                                                </div>
                                                <div class="preview-sidebar" style="background-color: #F8F9FE; width: 30%; float: left; height: 150px; padding: 10px;">
                                                    <div class="preview-menu-item" style="padding: 5px; margin-bottom: 5px; border-radius: 5px;">Dashboard</div>
                                                    <div class="preview-menu-item" style="padding: 5px; margin-bottom: 5px; border-radius: 5px; background-color: rgba(255, 94, 122, 0.1); color: var(--genz-accent-color);">Customers</div>
                                                    <div class="preview-menu-item" style="padding: 5px; margin-bottom: 5px; border-radius: 5px;">Projects</div>
                                                </div>
                                                <div class="preview-content" style="width: 70%; float: right; height: 150px; padding: 10px;">
                                                    <div class="preview-card" style="background-color: #fff; border-radius: 10px; padding: 10px; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);">
                                                        <h6 style="margin: 0 0 5px 0; color: #2D3748;">Card Title</h6>
                                                        <p style="margin: 0; font-size: 12px; color: #718096;">Card content goes here</p>
                                                    </div>
                                                    <button class="preview-button" style="background-color: var(--genz-accent-color); color: white; border: none; padding: 5px 10px; border-radius: 5px; font-size: 12px;">Button</button>
                                                </div>
                                                <div style="clear: both;"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Dark Mode</h5>
                                            <div class="theme-preview dark-mode">
                                                <div class="preview-header" style="background-color: #1E1E1E; border-bottom: 1px solid #2D3748; padding: 10px;">
                                                    <div class="preview-logo" style="background-color: var(--genz-accent-color); color: white; display: inline-block; padding: 5px 10px; border-radius: 5px;">GenZ</div>
                                                </div>
                                                <div class="preview-sidebar" style="background-color: #1E1E1E; width: 30%; float: left; height: 150px; padding: 10px;">
                                                    <div class="preview-menu-item" style="padding: 5px; margin-bottom: 5px; border-radius: 5px; color: #E2E8F0;">Dashboard</div>
                                                    <div class="preview-menu-item" style="padding: 5px; margin-bottom: 5px; border-radius: 5px; background-color: rgba(255, 94, 122, 0.1); color: var(--genz-accent-color);">Customers</div>
                                                    <div class="preview-menu-item" style="padding: 5px; margin-bottom: 5px; border-radius: 5px; color: #E2E8F0;">Projects</div>
                                                </div>
                                                <div class="preview-content" style="background-color: #121212; width: 70%; float: right; height: 150px; padding: 10px;">
                                                    <div class="preview-card" style="background-color: #1E1E1E; border-radius: 10px; padding: 10px; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);">
                                                        <h6 style="margin: 0 0 5px 0; color: #E2E8F0;">Card Title</h6>
                                                        <p style="margin: 0; font-size: 12px; color: #A0AEC0;">Card content goes here</p>
                                                    </div>
                                                    <button class="preview-button" style="background-color: var(--genz-accent-color); color: white; border: none; padding: 5px 10px; border-radius: 5px; font-size: 12px;">Button</button>
                                                </div>
                                                <div style="clear: both;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                            <button type="button" class="btn btn-default" onclick="resetThemeSettings()">Reset to Defaults</button>
                        </div>
                    </div>
                    
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize colorpicker
    if (typeof $.fn.colorpicker !== 'undefined') {
        $('.colorpicker-input').colorpicker({
            format: 'hex'
        });
        
        // Update color preview on change
        $('.colorpicker-input').on('colorpickerChange', function(e) {
            $(this).find('.color-preview').css('background-color', e.color.toString());
            updateThemePreview();
        });
    }
    
    // Update theme preview on input change
    $('#genz_theme_accent_color, #genz_theme_secondary_color').on('input', function() {
        updateThemePreview();
    });
    
    // Update theme preview
    function updateThemePreview() {
        const accentColor = $('#genz_theme_accent_color').val();
        const secondaryColor = $('#genz_theme_secondary_color').val();
        
        document.documentElement.style.setProperty('--genz-accent-color', accentColor);
        document.documentElement.style.setProperty('--genz-secondary-color', secondaryColor);
    }
});

// Reset theme settings to defaults
function resetThemeSettings() {
    if (confirm('Are you sure you want to reset theme settings to defaults?')) {
        $('#genz_theme_staff').prop('checked', true);
        $('#genz_theme_customers').prop('checked', false);
        $('#genz_theme_dark_mode').prop('checked', true);
        $('#genz_theme_animations').prop('checked', true);
        $('#genz_theme_accent_color').val('#FF5E7A').trigger('input');
        $('#genz_theme_secondary_color').val('#6C63FF').trigger('input');
        
        if (typeof $.fn.colorpicker !== 'undefined') {
            $('#genz_theme_accent_color, #genz_theme_secondary_color').colorpicker('setValue');
        }
    }
}
</script>

<style>
.genz-theme-settings .card {
    border-radius: var(--genz-border-radius-lg);
    box-shadow: var(--genz-shadow-sm);
    transition: all 0.3s ease;
    border: none;
    margin-bottom: 20px;
}

.genz-theme-settings .card:hover {
    box-shadow: var(--genz-shadow-md);
}

.genz-theme-settings .card-header {
    background-color: transparent;
    border-bottom: 1px solid var(--genz-border-color);
    padding: 15px 20px;
}

.genz-theme-settings .card-body {
    padding: 20px;
}

.genz-theme-settings .panel_s {
    border-radius: var(--genz-border-radius-md);
    box-shadow: var(--genz-shadow-sm);
    border: 1px solid var(--genz-border-color);
    margin-bottom: 20px;
}

.genz-theme-settings .panel-heading {
    padding: 15px;
    border-bottom: 1px solid var(--genz-border-color);
}

.genz-theme-settings .panel-body {
    padding: 15px;
}

.genz-theme-settings .form-group {
    margin-bottom: 20px;
}

.genz-theme-settings .color-preview {
    width: 20px;
    height: 20px;
    border-radius: 3px;
}

.genz-theme-settings .theme-preview {
    border: 1px solid var(--genz-border-color);
    border-radius: var(--genz-border-radius-md);
    overflow: hidden;
    margin-bottom: 20px;
}

.genz-theme-settings .gradient-text {
    background: linear-gradient(90deg, var(--genz-accent-color), var(--genz-secondary-color));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.genz-theme-settings .mt-4 {
    margin-top: 1.5rem;
}
</style>