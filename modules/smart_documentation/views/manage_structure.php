<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" onclick="new_category(); return false;" class="btn btn-info pull-left display-block"><?php echo _l('sd_new_category'); ?></a>
                            <div class="clearfix"></div>
                        </div>
                        <hr class="hr-panel-heading" />
                        
                        <div class="clearfix"></div>
                        
                        <?php if(count($categories) == 0){ ?>
                            <p class="text-muted"><?php echo _l('no_entries_found'); ?></p>
                        <?php } ?>

                        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                            <?php foreach($categories as $category){ ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading" role="tab" id="heading_<?php echo $category['id']; ?>">
                                        <h4 class="panel-title">
                                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_<?php echo $category['id']; ?>" aria-expanded="true" aria-controls="collapse_<?php echo $category['id']; ?>">
                                                <?php echo $category['name']; ?> 
                                                <?php if($category['icon']){ ?><i class="fa <?php echo $category['icon']; ?>"></i><?php } ?>
                                            </a>
                                            <div class="pull-right">
                                                <a href="#" onclick="edit_category(<?php echo $category['id']; ?>, '<?php echo $category['name']; ?>', '<?php echo $category['description']; ?>', '<?php echo $category['sort_order']; ?>', '<?php echo $category['icon']; ?>'); return false;" class="btn btn-default btn-xs"><i class="fa fa-pencil-square-o"></i></a>
                                                <a href="<?php echo admin_url('smart_documentation/delete_category/'.$category['id']); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-remove"></i></a>
                                            </div>
                                        </h4>
                                    </div>
                                    <div id="collapse_<?php echo $category['id']; ?>" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading_<?php echo $category['id']; ?>">
                                        <div class="panel-body">
                                            <p><?php echo $category['description']; ?></p>
                                            <div class="text-right">
                                                 <a href="#" onclick="new_section(<?php echo $category['id']; ?>); return false;" class="btn btn-success btn-xs"><?php echo _l('sd_new_section'); ?></a>
                                            </div>
                                            <hr />
                                            <?php if(count($category['sections']) == 0){ ?>
                                                <p class="text-muted"><?php echo _l('sd_no_sections'); ?></p>
                                            <?php } else { ?>
                                                <table class="table dt-table" data-order-col="1" data-order-type="asc">
                                                    <thead>
                                                        <tr>
                                                            <th><?php echo _l('sd_section_name'); ?></th>
                                                            <th><?php echo _l('sd_description'); ?></th>
                                                            <th><?php echo _l('options'); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach($category['sections'] as $section){ ?>
                                                            <tr>
                                                                <td><a href="<?php echo admin_url('smart_documentation/articles/'.$section['id']); ?>"><?php echo $section['name']; ?></a></td>
                                                                <td><?php echo $section['description']; ?></td>
                                                                <td>
                                                                    <a href="#" onclick="edit_section(<?php echo $section['id']; ?>, '<?php echo $section['name']; ?>', '<?php echo $section['description']; ?>', '<?php echo $section['sort_order']; ?>', <?php echo $category['id']; ?>); return false;" class="btn btn-default btn-xs"><i class="fa fa-pencil-square-o"></i></a>
                                                                    <a href="<?php echo admin_url('smart_documentation/delete_section/'.$section['id']); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-remove"></i></a>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="category_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open(admin_url('smart_documentation/category')); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('sd_new_category'); ?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="cat_id">
                <?php echo render_input('name', 'sd_category_name'); ?>
                <?php echo render_textarea('description', 'sd_description'); ?>
                <?php echo render_input('icon', 'sd_icon', '', 'text', ['placeholder' => 'fa fa-book']); ?>
                <?php echo render_input('sort_order', 'sd_sort_order', '', 'number'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<!-- Section Modal -->
<div class="modal fade" id="section_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open(admin_url('smart_documentation/section')); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('sd_new_section'); ?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="sec_id">
                <input type="hidden" name="category_id" id="sec_category_id">
                <?php echo render_input('name', 'sd_section_name'); ?>
                <?php echo render_textarea('description', 'sd_description'); ?>
                <?php echo render_input('sort_order', 'sd_sort_order', '', 'number'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    function new_category(){
        $('#category_modal').modal('show');
        $('#category_modal .modal-title').html('<?php echo _l('sd_new_category'); ?>');
        $('#category_modal input[name="name"]').val('');
        $('#category_modal textarea[name="description"]').val('');
        $('#category_modal input[name="icon"]').val('');
        $('#category_modal input[name="sort_order"]').val('');
        $('#category_modal input[name="id"]').val('');
        $('#category_modal form').attr('action', '<?php echo admin_url('smart_documentation/category'); ?>');
    }

    function edit_category(id, name, description, sort_order, icon){
        $('#category_modal').modal('show');
        $('#category_modal .modal-title').html('<?php echo _l('sd_edit_category'); ?>');
        $('#category_modal input[name="name"]').val(name);
        $('#category_modal textarea[name="description"]').val(description);
        $('#category_modal input[name="icon"]').val(icon);
        $('#category_modal input[name="sort_order"]').val(sort_order);
        $('#category_modal input[name="id"]').val(id);
        $('#category_modal form').attr('action', '<?php echo admin_url('smart_documentation/category/'); ?>' + id);
    }

    function new_section(category_id){
        $('#section_modal').modal('show');
        $('#section_modal .modal-title').html('<?php echo _l('sd_new_section'); ?>');
        $('#section_modal input[name="name"]').val('');
        $('#section_modal textarea[name="description"]').val('');
        $('#section_modal input[name="sort_order"]').val('');
        $('#section_modal input[name="id"]').val('');
        $('#section_modal input[name="category_id"]').val(category_id);
        $('#section_modal form').attr('action', '<?php echo admin_url('smart_documentation/section'); ?>');
    }

    function edit_section(id, name, description, sort_order, category_id){
        $('#section_modal').modal('show');
        $('#section_modal .modal-title').html('<?php echo _l('sd_edit_section'); ?>');
        $('#section_modal input[name="name"]').val(name);
        $('#section_modal textarea[name="description"]').val(description);
        $('#section_modal input[name="sort_order"]').val(sort_order);
        $('#section_modal input[name="id"]').val(id);
        $('#section_modal input[name="category_id"]').val(category_id);
        $('#section_modal form').attr('action', '<?php echo admin_url('smart_documentation/section/'); ?>' + id);
    }
</script>
</body>
</html>
