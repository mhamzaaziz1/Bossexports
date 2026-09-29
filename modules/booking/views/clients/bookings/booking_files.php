<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if($booking->settings->upload_files == 1){ ?>
  <?php echo form_open_multipart(site_url('booking/bookings_public/booking/'.$booking->id),array('class'=>'dropzone mbot15','id'=>'booking-files-upload')); ?>
  <input type="file" name="file" multiple class="hide"/>
  <?php echo form_close(); ?>
  <div class="pull-left mbot20">
    <a href="<?php echo site_url('booking/bookings_public/download_all_booking_files/'.$booking->id); ?>" class="btn btn-info">
      <?php echo _l('download_all'); ?>
    </a>
  </div>
  <div class="pull-right mbot20">
   <button class="gpicker" data-on-pick="bookingFileGoogleDriveSave">
    <i class="fa fa-google" aria-hidden="true"></i>
    <?php echo _l('choose_from_google_drive'); ?>
  </button>
  <div id="dropbox-chooser-booking-files"></div>
</div>
<?php } ?>
<table class="table dt-table" data-order-col="4" data-order-type="desc">
  <thead>
    <tr>
      <th><?php echo _l('booking_file_filename'); ?></th>
      <th><?php echo _l('booking_file__filetype'); ?></th>
      <th><?php echo _l('booking_discussion_last_activity'); ?></th>
      <th><?php echo _l('booking_discussion_total_comments'); ?></th>
      <th><?php echo _l('booking_file_dateadded'); ?></th>
      <?php if(get_option('allow_contact_to_delete_files') == 1){ ?>
        <th><?php echo _l('options'); ?></th>
      <?php } ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach($files as $file){
      $path = get_upload_path_by_type('booking') . $booking->id . '/'. $file['file_name'];
      ?>
      <tr>
       <td data-order="<?php echo $file['file_name']; ?>">
        <a href="#" onclick="view_booking_file(<?php echo $file['id']; ?>,<?php echo $file['booking_id']; ?>); return false;">
         <?php if(is_image(PROJECT_ATTACHMENTS_FOLDER .$booking->id.'/'.$file['file_name']) || (!empty($file['external']) && !empty($file['thumbnail_link']))){
          echo '<div class="text-left"><i class="fa fa-spinner fa-spin mtop30"></i></div>';
          echo '<img class="booking-file-image img-table-loading" src="#" data-orig="'.booking_file_url($file,true).'" width="100">';
          echo '</div>';
        }
        echo $file['subject']; ?></a>
      </td>
      <td data-order="<?php echo $file['filetype']; ?>"><?php echo $file['filetype']; ?></td>
      <td data-order="<?php echo $file['last_activity']; ?>">
        <?php
        if(!is_null($file['last_activity'])){
          echo time_ago($file['last_activity']);
        } else {
          echo _l('booking_discussion_no_activity');
        }
        ?>
      </td>
      <?php $total_file_comments = total_rows(db_prefix().'bookingdiscussioncomments',array('discussion_id'=>$file['id'],'discussion_type'=>'file')); ?>
      <td data-order="<?php echo $total_file_comments; ?>">
        <?php echo $total_file_comments; ?>
      </td>
      <td data-order="<?php echo $file['dateadded']; ?>">
       <?php echo _dt($file['dateadded']); ?>
     </td>
     <?php if(get_option('allow_contact_to_delete_files') == 1) { ?>
       <td>
        <?php if($file['contact_id'] == get_contact_user_id()){ ?>
          <a href="<?php echo site_url('booking/bookings_public/delete_file/'.$file['id'].'/booking'); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
        <?php } ?>
      </td>
    <?php } ?>
  </tr>
<?php } ?>
</tbody>
</table>
<div id="booking_file_data"></div>
