<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s no-margin">
                    <div class="panel-body _buttons">
                        <div class="row">
                            <div class="col-md-8">
                                <?php if(has_permission('quotes','','create')){ ?>
                                <a href="<?php echo admin_url('booking/quotes/quote'); ?>" class="btn btn-info pull-left new"><?php echo _l('new_quote'); ?></a>
                                <?php } ?>
                                <a href="<?php echo admin_url('booking/quotes/pipeline/'.$switch_pipeline); ?>" class="btn btn-default mleft5 pull-left"><?php echo _l('switch_to_list_view'); ?></a>
                            </div>
                            <div class="col-md-4" data-toggle="tooltip" data-placement="bottom" data-title="<?php echo _l('search_by_tags'); ?>">
                                <?php echo render_input('search','','','search',array('data-name'=>'search','onkeyup'=>'quotes_pipeline();'),array(),'no-margin') ?>
                                <?php echo form_hidden('sort_type'); ?>
                                <?php echo form_hidden('sort',(get_option('default_quotes_pipeline_sort') != '' ? get_option('default_quotes_pipeline_sort_type') : '')); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel_s animated mtop5 fadeIn">
                    <?php echo form_hidden('quoteid',$quoteid); ?>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="kanban-leads-sort">
                                    <span class="bold"><?php echo _l('quotes_pipeline_sort'); ?>: </span>
                                    <a href="#" onclick="quote_pipeline_sort('datecreated'); return false" class="datecreated">
                                        <?php if(get_option('default_quotes_pipeline_sort') == 'datecreated'){echo '<i class="kanban-sort-icon fa fa-sort-amount-'.strtolower(get_option('default_quotes_pipeline_sort_type')).'"></i> ';} ?><?php echo _l('quotes_sort_datecreated'); ?>
                                        </a>
                                    |
                                    <a href="#" onclick="quote_pipeline_sort('date'); return false" class="date">
                                       <?php if(get_option('default_quotes_pipeline_sort') == 'date'){echo '<i class="kanban-sort-icon fa fa-sort-amount-'.strtolower(get_option('default_quotes_pipeline_sort_type')).'"></i> ';} ?><?php echo _l('quotes_sort_quote_date'); ?>
                                        </a>
                                    |
                                    <a href="#" onclick="quote_pipeline_sort('pipeline_order');return false;" class="pipeline_order">
                                        <?php if(get_option('default_quotes_pipeline_sort') == 'pipeline_order'){echo '<i class="kanban-sort-icon fa fa-sort-amount-'.strtolower(get_option('default_quotes_pipeline_sort_type')).'"></i> ';} ?><?php echo _l('quotes_sort_pipeline'); ?>
                                        </a>
                                    |
                                    <a href="#" onclick="quote_pipeline_sort('open_till');return false;" class="open_till">
                                       <?php if(get_option('default_quotes_pipeline_sort') == 'open_till'){echo '<i class="kanban-sort-icon fa fa-sort-amount-'.strtolower(get_option('default_quotes_pipeline_sort_type')).'"></i> ';} ?><?php echo _l('quotes_sort_open_till'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div id="quotes-pipeline" style="overflow-x: scroll;">
                                <div class="container-fluid">
                                    <div id="kan-ban"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="quote">
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<?php init_tail(); ?>
<div id="convert_helper"></div>
<script>

    // Used when quote is updated from pipeline. eq changed order or moved to another status
    function quotes_pipeline_update(ui, object) {
        if (object === ui.item.parent()[0]) {
            var data = {};
            data.order = [];
            data.quoteid = $(ui.item).data('quote-id');
            data.status = $(ui.item.parent()[0]).data('status-id');
            var status = $(ui.item).parents('.pipeline-status').find('li');

            var i = 1;
            $.each(status, function () {
                data.order.push([$(this).data('quote-id'), i]);
                i++;
            });

            check_kanban_empty_col('[data-quote-id]');
            $.post(admin_url + 'booking/quotes/update_pipeline', data);
        }
    }

    // Init quotes pipeline
    function quotes_pipeline() {
        init_kanban('booking/quotes/get_pipeline', quotes_pipeline_update, '.pipeline-status', 347, 360);
    }

    // Open single quote in pipeline
    function quote_pipeline_open(id) {
        if (id === '') {
            return;
        }
        requestGet('booking/quotes/pipeline_open/' + id).done(function (response) {
            var visible = $('.quote-pipeline-modal:visible').length > 0;
            $('#quote').html(response);
            if (!visible) {
                $('.quote-pipeline-modal').modal({
                    show: true,
                    backdrop: 'static',
                    keyboard: false
                });
            } else {
                $('#quote').find('.modal.quote-pipeline-modal')
                    .removeClass('fade')
                    .addClass('in')
                    .css('display', 'block');
            }
        });
    }
    // Sort quotes in the pipeline view / switching sort type by click
    function quote_pipeline_sort(type) {
        kan_ban_sort(type, quotes_pipeline);
    }
    $(function(){
        quotes_pipeline();
    });
</script>
</body>
</html>
