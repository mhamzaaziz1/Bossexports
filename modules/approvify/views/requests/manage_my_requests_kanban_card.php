<?php defined('BASEPATH') or exit('No direct script access allowed');
if ($lead['status'] == $status['id']) { ?>
    <li data-lead-id="<?php echo $lead['id']; ?>" class="lead-kan-ban not-sortable">
        <div class="panel-body lead-body">
            <div class="row">
                <div class="col-md-12 lead-name">
                    <?php if (true) { ?>
                        <a href="<?php echo admin_url('profile/' . $lead['requester_id']); ?>" data-placement="right"
                           data-toggle="tooltip" title="<?php echo get_staff_full_name($lead['requester_id']); ?>"
                           class="pull-left mtop8 mright5">
                            <?php echo staff_profile_image($lead['requester_id'], [
                                'staff-profile-image-xs',
                            ]); ?></a>
                    <?php  } ?>
                    <a href="<?php echo admin_url('approvify/view_request/' . $lead['id']); ?>" class="pull-left">
                    <span
                        class="inline-block mtop10 mbot10">#<?php echo $lead['id'] . ' - ' . $lead['request_title']; ?></span>
                    </a>
                </div>
                <div class="col-md-12">
                    <div class="tw-flex">
                        <div class="tw-grow">
                            <small class="text-dark tw-text-sm"><?php echo _l('approvify_request_category'); ?> <span
                                        class="bold">
                                <span class="text-has-action">
                                    <?php echo $lead['category_name']; ?>
                                </span>
                            </span>
                            </small><br />
                        </div>
                        <div class="tw-shrink-0 text-right">
                            <small class="text-dark"><?php echo _l('lead_created'); ?>: <span class="bold">
                                <span class="text-has-action" data-toggle="tooltip"
                                      data-title="<?php echo _dt($lead['created_at']); ?>">
                                    <?php echo time_ago($lead['created_at']); ?>
                                </span>
                            </span>
                            </small><br />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </li>
<?php }