<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="_hidden_inputs _filters _tours_filters">
    <?php
    $tours_filter_assignees = $this->misc_model->get_tours_distinct_assignees();
    $tour_statuses = $this->tours_model->get_statuses();
    hooks()->do_action('tours_filters_hidden_html');
    echo form_hidden('my_tours',(!has_permission('tours','','view') ? 'true' : ''));
    echo form_hidden('my_following_tours');
    echo form_hidden('billable');
    echo form_hidden('billed');
    echo form_hidden('not_billed');
    echo form_hidden('not_assigned');
    echo form_hidden('due_date_passed');
    echo form_hidden('upcoming_tours');
    echo form_hidden('recurring_tours');
    echo form_hidden('today_tours');

    /* Related tour filter - used in customer profile */
    echo form_hidden('tours_related_to');

    if(has_permission('tours','','view')){
        foreach($tours_filter_assignees as $tf_assignee){
            echo form_hidden('tour_assigned_'.$tf_assignee['assigneeid']);
        }
    }
    foreach($tour_statuses as $status){
        $val = 'true';
        if($status['filter_default'] == false){
            $val = '';
        }
        echo form_hidden('tour_status_'.$status['id'],$val);
    }

    ?>
</div>

<div class="btn-group pull-right mleft4 mbot25 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
       <i class="fa fa-filter" aria-hidden="true"></i>
   </button>
   <ul class="dropdown-menu width300">
    <li>
        <a href="#" data-cview="all" onclick="dt_custom_view('','<?php echo $view_table_name; ?>',''); return false;">
            <?php echo _l('tour_list_all'); ?>
        </a>
    </li>
    <li class="divider"></li>
    <?php foreach($tour_statuses as $status){ ?>
    <li class="clear-all-prevent<?php if($status['filter_default'] == true){echo ' active';} ?>">
        <a href="#" data-cview="tour_status_<?php echo $status['id']; ?>" onclick="dt_custom_view('tour_status_<?php echo $status['id']; ?>','<?php echo $view_table_name; ?>','tour_status_<?php echo $status['id']; ?>'); return false;">
            <?php echo $status['name']; ?>
        </a>
    </li>
    <?php } ?>
    <li class="divider"></li>
    <li class="filter-group" data-filter-group="group-date">
        <a href="#" data-cview="today_tours" onclick="dt_custom_view('today_tours','<?php echo $view_table_name; ?>','today_tours'); return false;">
            <?php echo _l('todays_tours'); ?>
        </a>
    </li>
      <li class="filter-group" data-filter-group="group-date">
        <a href="#" data-cview="due_date_passed" onclick="dt_custom_view('due_date_passed','<?php echo $view_table_name; ?>','due_date_passed'); return false;">
            <?php echo _l('tour_list_duedate_passed'); ?>
        </a>
    </li>
    <li class="filter-group" data-filter-group="group-date">
        <a href="#" data-cview="upcoming_tours" onclick="dt_custom_view('upcoming_tours','<?php echo $view_table_name; ?>','upcoming_tours'); return false;">
            <?php echo _l('upcoming_tours'); ?>
        </a>
    </li>
    <li class="divider"></li>
    <li class="filter-group <?php echo (!has_permission('tours','','view') ? ' active' : ''); ?>" data-filter-group="assigned-follower-not-assigned">
        <a href="#" data-cview="my_tours" onclick="dt_custom_view('my_tours','<?php echo $view_table_name; ?>','my_tours'); return false;">
            <?php echo _l('tours_view_assigned_to_user'); ?>
        </a>
    </li>
    <li class="filter-group" data-filter-group="assigned-follower-not-assigned">
        <a href="#" data-cview="my_following_tours" onclick="dt_custom_view('my_following_tours','<?php echo $view_table_name; ?>','my_following_tours'); return false;">
            <?php echo _l('tours_view_follower_by_user'); ?>
        </a>
    </li>
    <?php if(has_permission('tours','','view')){ ?>
    <li class="filter-group" data-filter-group="assigned-follower-not-assigned">
        <a href="#" data-cview="not_assigned" onclick="dt_custom_view('not_assigned','<?php echo $view_table_name; ?>','not_assigned'); return false;">
            <?php echo _l('tour_list_not_assigned'); ?>
        </a>
    </li>
    <?php } ?>
    <?php if(has_permission('tours','','create') || has_permission('tours','','edit')){ ?>
    <li>
        <a href="#" data-cview="recurring_tours" onclick="dt_custom_view('recurring_tours','<?php echo $view_table_name; ?>','recurring_tours'); return false;">
            <?php echo _l('recurring_tours'); ?>
        </a>
    </li>
    <?php } ?>
    <?php if(has_permission('invoices','','create')){ ?>
    <li class="divider"></li>
    <li class="filter-group" data-filter-group="group-billable">
        <a href="#" data-cview="billable" onclick="dt_custom_view('billable','<?php echo $view_table_name; ?>','billable'); return false;">
            <?php echo _l('tour_billable'); ?>
        </a>
    </li>
     <li class="filter-group" data-filter-group="group-billable">
        <a href="#" data-cview="billed" onclick="dt_custom_view('billed','<?php echo $view_table_name; ?>','billed'); return false;">
            <?php echo _l('tour_billed'); ?>
        </a>
    </li>
     <li class="filter-group" data-filter-group="group-billable">
        <a href="#" data-cview="not_billed" onclick="dt_custom_view('not_billed','<?php echo $view_table_name; ?>','not_billed'); return false;">
            <?php echo _l('tour_billed_no'); ?>
        </a>
    </li>
    <?php } ?>
    <?php if(has_permission('tours','','view')){ ?>
    <?php if(count($tours_filter_assignees)){ ?>
    <div class="clearfix"></div>
    <li class="divider"></li>
    <li class="dropdown-submenu pull-left">
       <a href="#" tabindex="-1"><?php echo _l('filter_by_assigned'); ?></a>
       <ul class="dropdown-menu dropdown-menu-left">
        <?php foreach($tours_filter_assignees as $as){ ?>
        <li>
            <a href="#" data-cview="tour_assigned_<?php echo $as['assigneeid']; ?>" onclick="dt_custom_view(<?php echo $as['assigneeid']; ?>,'<?php echo $view_table_name; ?>','tour_assigned_<?php echo $as['assigneeid']; ?>'); return false;"><?php echo $as['full_name']; ?></a>
        </li>
        <?php } ?>
    </ul>
</li>
<?php } ?>

<?php } ?>

</ul>
</div>
