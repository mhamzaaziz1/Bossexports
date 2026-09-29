<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<ul class="nav nav-tabs no-margin" role="tablist">

    <li role="presentation" class="active booking_tab_overview">
        <a data-group="booking_overview" href="<?php echo site_url('booking/bookings_public/booking/'.$booking->id.'?group=booking_overview'); ?>" role="tab"><i class="fa fa-th" aria-hidden="true"></i> <?php echo _l('booking_overview'); ?></a>
    </li>

    <?php if($booking->settings->view_tours == 1 && $booking->settings->available_features['booking_tours'] == 1){ ?>
    <li role="presentation" class="booking_tab_tours">
        <a data-group="booking_tours" href="<?php echo site_url('booking/bookings_public/booking/'.$booking->id.'?group=booking_tours'); ?>" role="tab"><i class="fa fa-check-circle" aria-hidden="true"></i> <?php echo _l('tours'); ?></a>
    </li>
    <?php } ?>

    <?php if($booking->settings->view_timesheets == 1 && $booking->settings->available_features['booking_timesheets'] == 1){ ?>
    <li role="presentation" class="booking_tab_timesheets">
        <a data-group="booking_timesheets" href="<?php echo site_url('booking/bookings_public/booking/'.$booking->id.'?group=booking_timesheets'); ?>" role="tab"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo _l('booking_timesheets'); ?></a>
    </li>
    <?php } ?>

    <?php if($booking->settings->view_milestones == 1 && $booking->settings->available_features['booking_milestones'] == 1){ ?>
    <li role="presentation" class="booking_tab_milestones">
        <a data-group="booking_milestones" href="<?php echo site_url('booking/bookings_public/booking/'.$booking->id.'?group=booking_milestones'); ?>" role="tab"><i class="fa fa-rocket" aria-hidden="true"></i> <?php echo _l('booking_milestones'); ?></a>
    </li>
    <?php } ?>

    <?php if($booking->settings->available_features['booking_files'] == 1) { ?>
    <li role="presentation" class="booking_tab_files">
        <a data-group="booking_files" href="<?php echo site_url('booking/bookings_public/booking/'.$booking->id.'?group=booking_files'); ?>" role="tab"><i class="fa fa-files-o" aria-hidden="true"></i> <?php echo _l('booking_files'); ?></a>
    </li>
    <?php } ?>

    <?php if($booking->settings->available_features['booking_discussions'] == 1) { ?>
    <li role="presentation" class="booking_tab_discussions">
        <a data-group="booking_discussions" href="<?php echo site_url('booking/bookings_public/booking/'.$booking->id.'?group=booking_discussions'); ?>" role="tab"><i class="fa fa-commenting" aria-hidden="true"></i> <?php echo _l('booking_discussions'); ?></a>
    </li>
    <?php } ?>

    <?php if($booking->settings->view_gantt == 1 && $booking->settings->available_features['booking_gantt'] == 1){ ?>
    <li role="presentation" class="booking_tab_gantt">
        <a data-group="booking_gantt" href="<?php echo site_url('booking/bookings_public/booking/'.$booking->id.'?group=booking_gantt'); ?>" role="tab"><i class="fa fa-align-left" aria-hidden="true"></i> <?php echo _l('booking_gant'); ?></a>
    </li>
    <?php } ?>

    <?php if(has_contact_permission('support') && $booking->settings->available_features['booking_tickets'] == 1){ ?>
    <li role="presentation" class="booking_tab_tickets">
        <a data-group="booking_tickets" href="<?php echo site_url('booking/bookings_public/booking/'.$booking->id.'?group=booking_tickets'); ?>" role="tab"><i class="fa fa-life-ring" aria-hidden="true"></i> <?php echo _l('booking_tickets'); ?></a>
    </li>
    <?php } ?>

    <?php if(has_contact_permission('contracts') && $booking->settings->available_features['booking_contracts'] == 1){ ?>
    <li role="presentation" class="booking_tab_contracts">
        <a data-group="booking_contracts" href="<?php echo site_url('booking/bookings_public/booking/'.$booking->id.'?group=booking_contracts'); ?>" role="tab"><i class="fa fa-life-ring" aria-hidden="true"></i> <?php echo _l('contracts'); ?></a>
    </li>
    <?php } ?>

    <?php if(has_contact_permission('estimates') && $booking->settings->available_features['booking_estimates'] == 1){ ?>
    <li role="presentation" class="booking_tab_estimates">
        <a data-group="booking_estimates" href="<?php echo site_url('booking/bookings_public/booking/'.$booking->id.'?group=booking_estimates'); ?>" role="tab"><i class="fa fa-sun-o" aria-hidden="true"></i> <?php echo _l('estimates'); ?></a>
    </li>
    <?php } ?>

    <?php if(has_contact_permission('invoices') && $booking->settings->available_features['booking_invoices'] == 1){ ?>
    <li role="presentation" class="booking_tab_invoices">
        <a data-group="booking_invoices" href="<?php echo site_url('booking/bookings_public/booking/'.$booking->id.'?group=booking_invoices'); ?>" role="tab"><i class="fa fa-sun-o" aria-hidden="true"></i> <?php echo _l('booking_invoices'); ?></a>
    </li>
    <?php } ?>

    <?php if($booking->settings->view_activity_log == 1 && $booking->settings->available_features['booking_activity'] == 1){ ?>
    <li role="presentation" class="booking_tab_activity">
        <a data-group="booking_activity" href="<?php echo site_url('booking/bookings_public/booking/'.$booking->id.'?group=booking_activity'); ?>" role="tab"><i class="fa fa-exclamation" aria-hidden="true"></i> <?php echo _l('booking_activity'); ?></a>
    </li>
    <?php } ?>

</ul>
