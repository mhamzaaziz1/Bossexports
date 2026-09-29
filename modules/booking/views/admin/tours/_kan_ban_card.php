<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<li data-tour-id="<?php echo $tour['id']; ?>" class="tour<?php if($tour['current_user_is_assigned']){echo ' current-user-tour';} if((!empty($tour['duedate']) && $tour['duedate'] < date('Y-m-d')) && $tour['status'] != Tours_model::STATUS_COMPLETE){ echo ' overdue-tour'; } ?><?php if(!$tour['current_user_is_assigned'] && $tour['current_user_is_creator'] == '0' && !is_admin()){echo ' not-sortable';} ?>">
  <div class="panel-body" style="border-top: 2px solid <?php echo tour_priority_color($tour['priority']); ?>;">
    <div class="row">
      <div class="col-md-12 tour-name">
        <a href="<?php echo admin_url('tours/view/' . $tour['id']); ?>" onclick="init_tour_modal(<?php echo $tour['id']; ?>);return false;">
          <span class="inline-block full-width mtop10"><?php echo $tour['name']; ?></span>
        </a>
        <?php
          if ($tour['rel_name']) {
              $relName = tour_rel_name($tour['rel_name'], $tour['rel_id'], $tour['rel_type']);
              $link = tour_rel_link($tour['rel_id'], $tour['rel_type']);

              echo '<a class="text-muted" data-toggle="tooltip" title="' . _l('tour_related_to') . '" href="' . $link . '">' . $relName . '</a>';
          }
        ?>
      </div>
      <div class="col-md-6 text-muted mtop10">
       <?php
       echo format_members_by_ids_and_names($tour['assignees_ids'],$tour['assignees'],false,'staff-profile-image-xs');
       ?>
     </div>
     <div class="col-md-6 text-right text-muted mtop10">
      <?php if($tour['total_checklist_items'] > 0){ ?>
        <span class="mright5 inline-block text-muted" data-toggle="tooltip" data-title="<?php echo _l('tour_checklist_items'); ?>">
          <i class="fa fa-check-square-o" aria-hidden="true"></i>
          <?php echo $tour['total_finished_checklist_items']; ?>
          /
          <?php echo $tour['total_checklist_items']; ?>
        </span>
      <?php } ?>
      <span class="mright5 inline-block text-muted" data-toggle="tooltip" data-title="<?php echo _l('tour_comments'); ?>">
        <i class="fa fa-comments"></i> <?php echo $tour['total_comments']; ?>
      </span>
      <span class="inline-block text-muted" data-toggle="tooltip" data-title="<?php echo _l('tour_view_attachments'); ?>">
       <i class="fa fa-paperclip"></i>
       <?php echo $tour['total_files']; ?>
     </span>
   </div>
   <?php $tags = get_tags_in($tour['id'],'tour');
   if(count($tags) > 0){ ?>
     <div class="col-md-12">
      <div class="mtop5 kanban-tags">
        <?php echo render_tags($tags); ?>
      </div>
    </div>
  <?php } ?>

</div>
</div>
</li>
