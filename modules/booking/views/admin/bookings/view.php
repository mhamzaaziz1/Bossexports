<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <?php echo form_hidden('booking_id',$booking->id) ?>
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s booking-top-panel panel-full">
               <div class="panel-body _buttons">
                  <div class="row">
                     <div class="col-md-7 booking-heading">
                        <h3 class="hide booking-name"><?php echo $booking->name; ?></h3>
                        <div id="booking_view_name" class="pull-left">
                           <select class="selectpicker" id="booking_top" data-width="100%"<?php if(count($other_bookings) > 6){ ?> data-live-search="true" <?php } ?> onChange="document.location.href='<?php echo admin_url("booking/bookings/view/");?>' + this.value;">
                              <option value="<?php echo $booking->id; ?>" selected data-content="<?php echo $booking->name; ?> - <small><?php echo $booking->client_data->company; ?></small>">
                                <?php echo $booking->client_data->company; ?> <?php echo $booking->name; ?>
                              </option>
                              <?php foreach($other_bookings as $op){ ?>
                              <option value="<?php echo $op['id']; ?>" data-subtext="<?php echo $op['company']; ?>">#<?php echo $op['id']; ?> - <?php echo $op['name']; ?></option>
                              <?php } ?>
                           </select>
                        </div>
                        <div class="visible-xs">
                           <div class="clearfix"></div>
                        </div>
                        <?php echo '<div class="label pull-left mleft15 mtop8 p8 booking-status-label-'.$booking->status.'" style="background:'.$booking_status['color'].'">'.$booking_status['name'].'</div>'; ?>
                     </div>
                     <div class="col-md-5 text-right">
                        <?php if(has_permission('tours','','create')){ ?>
                        <a href="#" onclick="new_tour_from_relation(undefined,'booking',<?php echo $booking->id; ?>); return false;" class="btn btn-info"><?php echo _l('new_tour'); ?></a>
                        <?php } ?>
                        <?php
                           $invoice_func = 'pre_invoice_booking';
                           ?>
                        <?php if(has_permission('invoices','','create')){ ?>
                        <a href="#" onclick="<?php echo $invoice_func; ?>(<?php echo $booking->id; ?>); return false;" class="invoice-booking btn btn-info<?php if($booking->client_data->active == 0){echo ' disabled';} ?>"><?php echo _l('invoice_booking'); ?></a>
                        <?php } ?>
                        <?php
                           $booking_pin_tooltip = _l('pin_booking');
                           if(total_rows(db_prefix().'pinned_bookings',array('staff_id'=>get_staff_user_id(),'booking_id'=>$booking->id)) > 0){
                             $booking_pin_tooltip = _l('unpin_booking');
                           }
                           ?>
                        <div class="btn-group">
                           <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                           <?php echo _l('more'); ?> <span class="caret"></span>
                           </button>
                           <ul class="dropdown-menu dropdown-menu-right width200 booking-actions">
                              <li>
                                 <a href="<?php echo admin_url('booking/bookings/pin_action/'.$booking->id); ?>">
                                 <?php echo $booking_pin_tooltip; ?>
                                 </a>
                              </li>
                              <?php if(has_permission('bookings','','edit')){ ?>
                              <li>
                                 <a href="<?php echo admin_url('booking/bookings/booking/'.$booking->id); ?>">
                                 <?php echo _l('edit_booking'); ?>
                                 </a>
                              </li>
                              <?php } ?>
                              <?php if(has_permission('bookings','','create')){ ?>
                              <li>
                                 <a href="#" onclick="copy_booking(); return false;">
                                 <?php echo _l('copy_booking'); ?>
                                 </a>
                              </li>
                              <?php } ?>
                              <?php if(has_permission('bookings','','create') || has_permission('bookings','','edit')){ ?>
                              <li class="divider"></li>
                              <?php foreach($statuses as $status){
                                 if($status['id'] == $booking->status){continue;}
                                 ?>
                              <li>
                                 <a href="#" data-name="<?php echo _l('booking_status_'.$status['id']); ?>" onclick="booking_mark_as_modal(<?php echo $status['id']; ?>,<?php echo $booking->id; ?>, this); return false;"><?php echo _l('booking_mark_as',$status['name']); ?></a>
                              </li>
                              <?php } ?>
                              <?php } ?>
                              <li class="divider"></li>
                              <?php if(has_permission('bookings','','create')){ ?>
                              <li>
                                 <a href="<?php echo admin_url('booking/bookings/export_booking_data/'.$booking->id); ?>" target="_blank"><?php echo _l('export_booking_data'); ?></a>
                              </li>
                              <?php } ?>
                              <?php if(is_admin()){ ?>
                              <li>
                                 <a href="<?php echo admin_url('booking/bookings/view_booking_as_client/'.$booking->id .'/'.$booking->clientid); ?>" target="_blank"><?php echo _l('booking_view_as_client'); ?></a>
                              </li>
                              <?php } ?>
                              <?php if(has_permission('bookings','','delete')){ ?>
                              <li>
                                 <a href="<?php echo admin_url('booking/bookings/delete/'.$booking->id); ?>" class="_delete">
                                 <span class="text-danger"><?php echo _l('delete_booking'); ?></span>
                                 </a>
                              </li>
                              <?php } ?>
                           </ul>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="panel_s booking-menu-panel">
               <div class="panel-body">
                  <?php hooks()->do_action('before_render_booking_view', $booking->id); ?>
                  <?php $this->load->view('admin/bookings/booking_tabs'); ?>
               </div>
            </div>
            <?php
               if((has_permission('bookings','','create') || has_permission('bookings','','edit'))
                 && $booking->status == 1
                 && $this->bookings_model->timers_started_for_booking($booking->id)
                 && $tab['slug'] != 'booking_milestones') {
               ?>
            <div class="alert alert-warning booking-no-started-timers-found mbot15">
               <?php echo _l('booking_not_started_status_tours_timers_found'); ?>
            </div>
            <?php } ?>
            <?php
               if($booking->deadline && date('Y-m-d') > $booking->deadline
                && $booking->status == 2
                && $tab['slug'] != 'booking_milestones') {
               ?>
            <div class="alert alert-warning bold booking-due-notice mbot15">
               <?php echo _l('booking_due_notice', floor((abs(time() - strtotime($booking->deadline)))/(60*60*24))); ?>
            </div>
            <?php } ?>
            <?php
               if(!has_contact_permission('bookings',get_primary_contact_user_id($booking->clientid))
                 && total_rows(db_prefix().'contacts',array('userid'=>$booking->clientid)) > 0
                 && $tab['slug'] != 'booking_milestones') {
               ?>
            <div class="alert alert-warning booking-permissions-warning mbot15">
               <?php echo _l('booking_customer_permission_warning'); ?>
            </div>
            <?php } ?>
            <div class="panel_s">
               <div class="panel-body">
                  <?php $this->load->view(($tab ? $tab['view'] : 'admin/bookings/booking_overview')); ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
</div>
</div>
<?php if(isset($discussion)){
   echo form_hidden('discussion_id',$discussion->id);
   echo form_hidden('discussion_user_profile_image_url',$discussion_user_profile_image_url);
   echo form_hidden('current_user_is_admin',$current_user_is_admin);
   }
   echo form_hidden('booking_percent',$percent);
   ?>
<div id="invoice_booking"></div>
<div id="pre_invoice_booking"></div>
<?php $this->load->view('admin/bookings/milestone'); ?>
<?php $this->load->view('admin/bookings/copy_settings'); ?>
<?php $this->load->view('admin/bookings/_mark_tours_finished'); ?>
<?php init_tail(); ?>
<?php 
require('modules/booking/assets/js/bookings_js.php'); 
?>
<!-- For invoices table -->
<script>
   tourid = '<?php echo $this->input->get('tourid'); ?>';
</script>
<script>
   var gantt_data = {};
   <?php if(isset($gantt_data)){ ?>
   gantt_data = <?php echo json_encode($gantt_data); ?>;
   <?php } ?>
   var discussion_id = $('input[name="discussion_id"]').val();
   var discussion_user_profile_image_url = $('input[name="discussion_user_profile_image_url"]').val();
   var current_user_is_admin = $('input[name="current_user_is_admin"]').val();
   var booking_id = $('input[name="booking_id"]').val();
   if(typeof(discussion_id) != 'undefined'){
     discussion_comments('#discussion-comments',discussion_id,'regular');
   }
   $(function(){
    var booking_progress_color = '<?php echo hooks()->apply_filters('admin_booking_progress_color','#84c529'); ?>';
    var circle = $('.booking-progress').circleProgress({fill: {
     gradient: [booking_progress_color, booking_progress_color]
   }}).on('circle-animation-progress', function(event, progress, stepValue) {
     $(this).find('strong.booking-percent').html(parseInt(100 * stepValue) + '<i>%</i>');
   });
   });

   function discussion_comments(selector,discussion_id,discussion_type){
     var defaults = _get_jquery_comments_default_config(<?php echo json_encode(get_booking_discussions_language_array()); ?>);
     var options = {
      // https://github.com/Viima/jquery-comments/pull/169
      wysiwyg_editor: {
            opts: {
                enable: true,
                is_html: true,
                container_id: 'editor-container',
                comment_index: 0,
            },
            init: function (textarea, content) {
                var comment_index = textarea.data('comment_index');
                 var editorConfig = _simple_editor_config();
                 editorConfig.setup = function(ed) {
                      textarea.data('wysiwyg_editor', ed);

                      ed.on('change', function() {
                          var value = ed.getContent();
                          if (value !== ed._lastChange) {
                            ed._lastChange = value;
                            textarea.trigger('change');
                          }
                      });

                      ed.on('keyup', function() {
                        var value = ed.getContent();
                          if (value !== ed._lastChange) {
                            ed._lastChange = value;
                            textarea.trigger('change');
                          }
                      });

                      ed.on('Focus', function (e) {
                        textarea.trigger('click');
                      });

                      ed.on('init', function() {
                        if (content) ed.setContent(content);
                      })
                  }
                var containerId = this.get_container_id(comment_index);
                tinyMCE.remove('#'+containerId);

                setTimeout(function(){
                  init_editor('#'+ containerId, editorConfig)
                },100)
            },
            get_container: function (textarea) {
                if (!textarea.data('comment_index')) {
                    textarea.data('comment_index', ++this.opts.comment_index);
                }

                return $('<div/>', {
                    'id': this.get_container_id(this.opts.comment_index)
                });
            },
            get_contents: function(editor) {
               return editor.getContent();
            },
            on_post_comment: function(editor, evt) {
               editor.setContent('');
            },
            get_container_id: function(comment_index) {
              var container_id = this.opts.container_id;
              if (comment_index) container_id = container_id + "-" + comment_index;
              return container_id;
            }
        },
      currentUserIsAdmin:current_user_is_admin,
      getComments: function(success, error) {
        $.get(admin_url + 'bookings/get_discussion_comments/'+discussion_id+'/'+discussion_type,function(response){
          success(response);
        },'json');
      },
      postComment: function(commentJSON, success, error) {
        $.ajax({
          type: 'post',
          url: admin_url + 'bookings/add_discussion_comment/'+discussion_id+'/'+discussion_type,
          data: commentJSON,
          success: function(comment) {
            comment = JSON.parse(comment);
            success(comment)
          },
          error: error
        });
      },
      putComment: function(commentJSON, success, error) {
        $.ajax({
          type: 'post',
          url: admin_url + 'bookings/update_discussion_comment',
          data: commentJSON,
          success: function(comment) {
            comment = JSON.parse(comment);
            success(comment)
          },
          error: error
        });
      },
      deleteComment: function(commentJSON, success, error) {
        $.ajax({
          type: 'post',
          url: admin_url + 'bookings/delete_discussion_comment/'+commentJSON.id,
          success: success,
          error: error
        });
      },
      uploadAttachments: function(commentArray, success, error) {
        var responses = 0;
        var successfulUploads = [];
        var serverResponded = function() {
          responses++;
            // Check if all requests have finished
            if(responses == commentArray.length) {
                // Case: all failed
                if(successfulUploads.length == 0) {
                  error();
                // Case: some succeeded
              } else {
                successfulUploads = JSON.parse(successfulUploads);
                success(successfulUploads)
              }
            }
          }
          $(commentArray).each(function(index, commentJSON) {
            // Create form data
            var formData = new FormData();
            if(commentJSON.file.size && commentJSON.file.size > app.max_php_ini_upload_size_bytes){
             alert_float('danger',"<?php echo _l("file_exceeds_max_filesize"); ?>");
             serverResponded();
           } else {
            $(Object.keys(commentJSON)).each(function(index, key) {
              var value = commentJSON[key];
              if(value) formData.append(key, value);
            });

            if (typeof(csrfData) !== 'undefined') {
               formData.append(csrfData['token_name'], csrfData['hash']);
            }
            $.ajax({
              url: admin_url + 'bookings/add_discussion_comment/'+discussion_id+'/'+discussion_type,
              type: 'POST',
              data: formData,
              cache: false,
              contentType: false,
              processData: false,
              success: function(commentJSON) {
                successfulUploads.push(commentJSON);
                serverResponded();
              },
              error: function(data) {
               var error = JSON.parse(data.responseText);
               alert_float('danger',error.message);
               serverResponded();
             },
           });
          }
        });
        }
      }
      var settings = $.extend({}, defaults, options);
        $(selector).comments(settings);
       }
    table_invoices = $('table.table-invoices');
	table_estimates = $('table.table-estimates');
	console.log(table_invoices);

	if (table_invoices.length > 0 || table_estimates.length > 0) {

		// Invoices additional server params
		var Invoices_Estimates_ServerParams = {};
		var Invoices_Estimates_Filter = $('._hidden_inputs._filters input');

		$.each(Invoices_Estimates_Filter, function () {
			Invoices_Estimates_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
		});

		if (table_invoices.length) {
			// Invoices tables
			initDataTable(table_invoices, (admin_url + 'booking/invoices/table' + ($('body').hasClass('recurring') ? '?recurring=1' : '')), 'undefined', 'undefined', Invoices_Estimates_ServerParams, !$('body').hasClass('recurring') ? [
					[3, 'desc'],
					[0, 'desc']
			] : [table_invoices.find('th.next-recurring-date').index(), 'asc']);
		}

		if (table_estimates.length) {
			// Estimates table
			initDataTable(table_estimates, admin_url + 'estimates/table', 'undefined', 'undefined', Invoices_Estimates_ServerParams, [
					[3, 'desc'],
					[0, 'desc']
			]);
		}
	}
</script>
</body>
</html>
