<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo form_open($this->uri->uri_string()); ?>
        <div class="row">
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        
                        <?php $value = (isset($article) ? $article->title : ''); ?>
                        <?php echo render_input('title', 'sd_article_title', $value); ?>
                        
                        <div class="form-group">
                            <label for="content" class="control-label"><?php echo _l('sd_article_content'); ?></label>
                            <textarea id="content" name="content" class="form-control tinymce"><?php echo (isset($article) ? $article->content : ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                 <div class="panel_s">
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="section_id" class="control-label"><?php echo _l('sd_section'); ?></label>
                            <select name="section_id" class="selectpicker" id="section_id" data-width="100%" data-live-search="true">
                                <option value=""></option>
                                <?php foreach($categories as $category){ ?>
                                    <optgroup label="<?php echo $category['name']; ?>">
                                        <?php foreach($category['sections'] as $section){ ?>
                                            <option value="<?php echo $section['id']; ?>" <?php if(isset($article) && $article->section_id == $section['id']){echo 'selected';} ?>><?php echo $section['name']; ?></option>
                                        <?php } ?>
                                    </optgroup>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status" class="control-label"><?php echo _l('sd_status'); ?></label>
                            <select name="status" id="status" class="selectpicker" data-width="100%">
                                <option value="draft" <?php if(isset($article) && $article->status == 'draft'){echo 'selected';} ?>><?php echo _l('sd_draft'); ?></option>
                                <option value="review" <?php if(isset($article) && $article->status == 'review'){echo 'selected';} ?>><?php echo _l('sd_review'); ?></option>
                                <option value="published" <?php if(isset($article) && $article->status == 'published'){echo 'selected';} ?>><?php echo _l('sd_published'); ?></option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="visibility" class="control-label"><?php echo _l('sd_visibility'); ?></label>
                            <select name="visibility" id="visibility" class="selectpicker" data-width="100%">
                                <option value="staff" <?php if(isset($article) && $article->visibility == 'staff'){echo 'selected';} ?>><?php echo _l('sd_visibility_staff'); ?></option>
                                <option value="clients" <?php if(isset($article) && $article->visibility == 'clients'){echo 'selected';} ?>><?php echo _l('sd_visibility_clients'); ?></option>
                                <option value="public" <?php if(isset($article) && $article->visibility == 'public'){echo 'selected';} ?>><?php echo _l('sd_visibility_public'); ?></option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="role_visibility" class="control-label"><?php echo _l('sd_roles'); ?> <small class="text-muted"><?php echo _l('sd_roles_help'); ?></small></label>
                            <select name="role_visibility[]" id="role_visibility" class="selectpicker" multiple data-width="100%" data-actions-box="true">
                                <?php foreach($roles as $role){ ?>
                                    <option value="<?php echo $role['roleid']; ?>" <?php if(isset($article) && in_array($role['roleid'], explode(',', $article->role_visibility ?? ''))){echo 'selected';} ?>><?php echo $role['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <?php $value = (isset($article) ? $article->related_module : ''); ?>
                        <div class="form-group">
                             <label for="related_module" class="control-label"><?php echo _l('sd_related_module'); ?></label>
                             <select name="related_module" id="related_module" class="selectpicker" data-width="100%" data-live-search="true">
                                <option value=""></option>
                                <?php foreach($modules as $module){ ?>
                                     <option value="<?php echo $module['system_name']; ?>" <?php if($value == $module['system_name']){echo 'selected';} ?>><?php echo $module['headers']['module_name'] ?? $module['system_name']; ?></option>
                                <?php } ?>
                             </select>
                        </div>

                        <hr />
                        <button type="submit" class="btn btn-info pull-right display-block"><?php echo _l('submit'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function(){
        init_editor('.tinymce');
    });
</script>
</body>
</html>
