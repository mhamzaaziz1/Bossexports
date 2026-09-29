<script>
  Dropzone.options.bookingFilesUpload = false;
  Dropzone.options.bookingExpenseForm = false;
  
  // Initing relation tours tables
function init_rel_tours_table(rel_id, rel_type, selector) {
    if (typeof (selector) == 'undefined') {
        selector = '.table-rel-tours';
    }
    var $selector = $("body").find(selector);
    if ($selector.length === 0) {
        return;
    }

    var ToursServerParams = {},
        toursRelationTableNotSortable = [0], // bulk actions
        ToursFilters;

    ToursFilters = $('body').find('._hidden_inputs._filters._tours_filters input');

    $.each(ToursFilters, function () {
        ToursServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
    });

    var url = admin_url + 'booking/tours/init_relation_tours/' + rel_id + '/' + rel_type;

    if ($selector.attr('data-new-rel-type') == 'booking') {
        url += '?bulk_actions=true';
    }

    initDataTable($selector, url, toursRelationTableNotSortable, toursRelationTableNotSortable, ToursServerParams, [$selector.find('th.duedate').index(), 'asc']);
}

  var expenseDropzone;
  $(function() {

      init_ajax_search('customer', '#clientid_copy_booking.ajax-search');

      // remove the divider for booking actions in case there is no other li except for pin booking
      $('ul.booking-actions li:first-child').next('li.divider').remove();

      var file_id = get_url_param('file_id');
      if (file_id) {
          view_booking_file(file_id, booking_id);
      }

      // Fix for shortcuts in discussions textarea/contenteditable - jquery-comments plugin
      var $discussionsContentEditable = $('#booking_file_data, #discussion-comments');
      $discussionsContentEditable.on('focus', '[contenteditable="true"]', function() {
          $.Shortcuts.stop();
      });

      $discussionsContentEditable.on('focusout', '[contenteditable="true"]', function() {
          $.Shortcuts.start();
      });

      $('body').on('show.bs.modal', '._booking_file', function() {
          discussion_comments('#booking-file-discussion', discussion_id, 'file')
      });

      $('body').on('shown.bs.modal', '._booking_file', function() {
          var content_height = ($('body').find('._booking_file .modal-content').height() - 165);
          var bookingFilePreviewIframe = $('.booking_file_area iframe');

          if(bookingFilePreviewIframe.length > 0){
            bookingFilePreviewIframe.css('height', content_height);
          }

          if(!is_mobile()){
           $('.booking_file_area,.booking_file_discusssions_area').css('height',content_height);
         }
      });

      $('body').on('shown.bs.modal', '#milestone', function() {
          $('#milestone').find('input[name="name"]').focus();
      });

      initDataTable('.table-credit-notes', admin_url + 'credit_notes/table?booking_id=' + booking_id, ['undefined'], ['undefined'], undefined, [0, 'desc']);

        var ContractsServerParams = {};
        $.each($('._hidden_inputs._filters input'),function(){
            ContractsServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
        });

      initDataTable('.table-contracts', admin_url+'contracts/table?booking_id='+booking_id, undefined, undefined, ContractsServerParams, [6, 'desc']);

      if ($('#timesheetsChart').length > 0 && typeof(booking_overview_chart) != 'undefined') {
          var chartOptions = {
              type: 'bar',
              data: {},
              options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  tooltips: {
                      enabled: true,
                      mode: 'single',
                      callbacks: {
                          label: function(tooltipItems, data) {
                              return decimalToHM(tooltipItems.yLabel);
                          }
                      }
                  },
                  scales: {
                      yAxes: [{
                          ticks: {
                              beginAtZero: true,
                              min: 0,
                              userCallback: function(label, index, labels) {
                                  return decimalToHM(label);
                              },
                          }
                      }],
                  },
              }
          };
          chartOptions.data = booking_overview_chart.data;
          var ctx = document.getElementById("timesheetsChart");
          timesheetsChart = new Chart(ctx, chartOptions);
      }
      milestones_kanban();

      $('#booking_top').on('change', function() {
          var val = $(this).val();
          var __booking_group = get_url_param('group');
          if (__booking_group) {
              __booking_group = '?group=' + __booking_group;
          } else {
              __booking_group = '';
          }
          window.location.href = admin_url + 'booking/bookings/view/' + val + __booking_group;
      });

      if (typeof(Dropbox) != 'undefined' && $('#dropbox-chooser').length > 0) {
          document.getElementById("dropbox-chooser").appendChild(Dropbox.createChooseButton({
              success: function(files) {
                  saveBookingExternalFile(files, 'dropbox');
              },
              linkType: "preview",
              extensions: app.options.allowed_files.split(','),
          }));
      }

      $('body').on('click', '.milestone-column .cpicker,.milestone-column .reset_milestone_color', function(e) {
          e.preventDefault();
          var color = $(this).data('color');
          var invoker = $(this);
          var milestone_id = invoker.parents('.milestone-column').data('col-status-id');
          $.post(admin_url + 'booking/bookings/change_milestone_color', {
              color: color,
              milestone_id: milestone_id
          }).done(function() {
              // Reset color needs reload
              if (color == '') {
                  window.location.reload();
              } else {
                  var $parent = invoker.parents('.milestone-column');
                  $parent.find('.reset_milestone_color').removeClass('hide');
                  $parent.find('.panel-heading').addClass('color-white').removeClass('tour-phase');
                  $parent.find('.edit-milestone-phase').addClass('color-white');
              }
          })
      });

      if ($('#booking-files-upload').length > 0) {
          new Dropzone('#booking-files-upload', appCreateDropzoneOptions({
              paramName: "file",
              uploadMultiple: true,
              parallelUploads: 20,
              maxFiles: 20,
              accept: function(file, done) {
                  done();
              },
              success: function(file, response) {
                  if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                      window.location.href = admin_url + 'booking/bookings/view/' + booking_id + '?group=booking_files';
                  }
              },
              sending: function(file, xhr, formData) {
                  formData.append("visible_to_customer", $('input[name="visible_to_customer"]').prop('checked'));
              }
          }));
      }

      if ($('#booking-expense-form').length > 0) {
          expenseDropzone = new Dropzone("#booking-expense-form", appCreateDropzoneOptions({
              autoProcessQueue: false,
              clickable: '#dropzoneDragArea',
              previewsContainer: '.dropzone-previews',
              addRemoveLinks: true,
              maxFiles: 1,
              success: function(file, response) {
                  if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                      window.location.reload();
                  }
              }
          }));
      }

      appValidateForm($('#booking-expense-form'), {
          category: 'required',
          date: 'required',
          amount: 'required',
          currency: 'required'
      }, bookingExpenseSubmitHandler);

      // Expenses additional server params
      var Expenses_ServerParams = {};
      $.each($('._hidden_inputs._filters input'), function() {
          Expenses_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
      });

      _table_api = initDataTable('.table-booking-expenses', admin_url + 'booking/bookings/expenses/' + booking_id, 'undefined', 'undefined', Expenses_ServerParams, [4, 'desc']);

      if (_table_api) {
          _table_api.column(0).visible(false, false).columns.adjust();
      }

      init_rel_tours_table(booking_id, 'booking');
      initDataTable('.table-notes', admin_url + 'booking/bookings/notes/' + booking_id, [4], [4], 'undefined', [1, 'desc']);

      var Timesheets_ServerParams = {};
      $.each($('._hidden_inputs._filters.timesheets_filters input'), function() {
          Timesheets_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
      });

      initDataTable('.table-timesheets', admin_url + 'booking/bookings/timesheets/' + booking_id, [8], [8], Timesheets_ServerParams, [3, 'desc']);
      initDataTable('.table-booking-discussions', admin_url + 'booking/bookings/discussions/' + booking_id, undefined, undefined, 'undefined', [1, 'desc']);

      appValidateForm($('#milestone_form'), {
          name: 'required',
          due_date: 'required'
      });

      appValidateForm($('#discussion_form'), {
          subject: 'required',
      }, manage_discussion);

      var timesheet_rules = {};
      var time_sheets_form_elements = $('#timesheet_form').find('select');
      $.each(time_sheets_form_elements, function() {
          var name = $(this).attr('name');
          timesheet_rules[name] = 'required';
      });

      var validation_timesheet_duration = {
          required: {
              depends: function(element) {
                  if ($('.timesheet-date-toggler-text').is(':visible')) {
                      return false;
                  }
                  var label = $('label[for="timesheet_duration"]');
                  if (label.length > 0 && label.find('.req').length == 0) {
                      label.prepend('<small class="req text-danger">* </small>');
                  }
                  return true;
              }
          }
      }
      timesheet_rules['start_time'] = validation_timesheet_duration;
      timesheet_rules['end_time'] = validation_timesheet_duration;
      timesheet_rules['timesheet_duration'] = {
          required: {
              depends: function(element) {
                  if (!$('.timesheet-date-toggler-text').is(':visible')) {
                      return false;
                  }
                  return true;
              }
          }
      }
      appValidateForm($('#timesheet_form'), timesheet_rules, manage_timesheets);

      $('#discussion').on('hidden.bs.modal', function(event) {
          var $d = $('#discussion');
          $d.find('input[name="subject"]').val('');
          $d.find('textarea[name="description"]').val('');
          $d.find('input[name="show_to_customer"]').prop('checked', true);
          $d.find('.add-title').removeClass('hide');
          $d.find('.edit-title').removeClass('hide');
      });

      $('#milestone').on('hidden.bs.modal', function(event) {
          $('#additional_milestone').html('');
          $('#milestone input[name="due_date"]').val('');
          $('#milestone input[name="name"]').val('');
          $('#milestone input[name="milestone_order"]').val($('.table-milestones tbody tr').length + 1);
          $('#milestone textarea[name="description"]').val('');
          $('#milestone input[name="description_visible_to_customer"]').prop('checked', false);
          $('#milestone .add-title').removeClass('hide');
          $('#milestone .edit-title').removeClass('hide');
      });

      $('#timesheet').on('hidden.bs.modal', function(event) {
          var $t = $('#timesheet');
          $t.find('select[name="timesheet_staff_id"]').removeAttr('data-staff_id');
          $t.find('select[name="timesheet_staff_id"]').empty();
          $t.find('select[name="timesheet_staff_id"]').selectpicker('refresh');
          $t.find('select[name="timesheet_tour_id"]').selectpicker('val', '');
          $t.find('textarea[name="note"]').val('');
          $t.find('#timesheet_duration').val('');
          $t.find('#tags').tagit('removeAll');
          $('input[name="timer_id"]').val('');
      });

      $('#timesheet select[name="timesheet_tour_id"]').on('change', function() {
          var select_staff = $('#timesheet select[name="timesheet_staff_id"]');
          var _tour_id = $(this).val();
          if (_tour_id == '') {
              select_staff.html('');
              select_staff.selectpicker('refresh');
              return;
          }
          var staff_id;
          if (select_staff.attr('data-staff_id')) {
              staff_id = select_staff.attr('data-staff_id');
          }
          requestGet('booking/bookings/timesheet_tour_assignees/' + _tour_id + '/' + booking_id + '/' + staff_id).done(function(response) {
              select_staff.html(response);
              select_staff.selectpicker('refresh');
          });
      });

      $('body').on('change', '#booking_invoice_select_all_tours,#booking_invoice_select_all_expenses', function() {
          var checked = $(this).prop('checked');
          var name_selector;
          if ($(this).hasClass('invoice_select_all_expenses')) {
              name_selector = 'input[name="expenses[]"]';
          } else {
              name_selector = 'input[name="tours[]"]';
          }
          if (checked == true) {
              $(name_selector).not(':disabled').prop('checked', true);
          } else {
              $(name_selector).not(':disabled').prop('checked', false);
          }
      });

      $('body').on('change', 'input[name="invoice_data_type"]', function() {
          var val = $(this).val();
          if (val == 'timesheets_individualy') {
              $('#timesheets_bill_include_notes').removeClass('hide');
          } else {
              $('#timesheets_bill_include_notes').addClass('hide');
          }
      });

      $('input[name="members"].copy').on('change', function() {
          var checked = $(this).prop('checked');
          var checked_tours = $('input[name="tours"].copy').prop('checked');
          if (!checked) {
              if (checked_tours) {
                  $('input[name="tour_include_assignees"]').prop('checked', false);
                  $('input[name="tour_include_followers"]').prop('checked', false);
              }
          } else {
              if (checked_tours) {
                  $('input[name="tour_include_assignees"]').prop('checked', true);
                  $('input[name="tour_include_followers"]').prop('checked', true);
              }
          }
      });
  });
  function bookingFileGoogleDriveSave(pickData) {
      saveBookingExternalFile(pickData, 'gdrive');
  }
  function saveBookingExternalFile(files, externalType) {
      $.post(admin_url + 'booking/bookings/add_external_file', {
          files: files,
          booking_id: booking_id,
          external: externalType,
          visible_to_customer: $('#pf_visible_to_customer').prop('checked')
      }).done(function() {
          var location = window.location.href;
          window.location.href = location.split('?')[0] + '?group=booking_files';
      });
  }

  function milestones_switch_view() {
      $('#milestones-table').toggleClass('hide');
      $('.booking-milestones-kanban').toggleClass('hide');
      if (!$.fn.DataTable.isDataTable('.table-milestones')) {
          initDataTable('.table-milestones', admin_url + 'booking/bookings/milestones/' + booking_id);
      }
  }

  function manage_discussion(form) {
      var data = $(form).serialize();
      var url = form.action;
      $.post(url, data).done(function(response) {
          response = JSON.parse(response);
          if (response.success == true) {
              alert_float('success', response.message);
          }
          $('.table-booking-discussions').DataTable().ajax.reload(null, false);
          $('#discussion').modal('hide');
          $('#discussion_form').find('button[type="submit"]').button('reset');
      });
      return false;
  }

  function manage_timesheets(form) {
      var data = $(form).serialize();
      var url = form.action;
      $.post(url, data).done(function(response) {
          response = JSON.parse(response);
          if (response.success == true) {
              alert_float('success', response.message);
          } else {
              alert_float('warning', response.message);
          }
          setTimeout(function() {
              window.location.reload();
          }, 1000);
      });
  }

  function edit_timesheet(invoker, id) {
      $('#timesheet select[name="timesheet_staff_id"]').attr('data-staff_id', $(invoker).attr('data-timesheet_staff_id'));
      $('select[name="timesheet_tour_id"]').selectpicker('val', $(invoker).attr('data-timesheet_tour_id'));
      $('input[name="timer_id"]').val(id);
      $('input[name="start_time"]').val($(invoker).attr('data-start_time'));
      $('input[name="end_time"]').val($(invoker).attr('data-end_time'));
      $('#timesheet textarea[name="note"]').val($(invoker).attr('data-note'));
      $('select[name="timesheet_tour_id"]').change();

      $('#timesheet').modal('show');
      // causing problems with ui dropdown goes to top left side when modal is shown
      setTimeout(function() {
          var timesheetTags = $(invoker).attr('data-tags').split(',');
          for (var i in timesheetTags) {
              $('#timesheet #tags').tagit('createTag', timesheetTags[i]);
          }
      }, 500);
  }

  function new_discussion() {
      $('#discussion').modal('show');
      $('#discussion .edit-title').addClass('hide');
  }

  function new_milestone() {
      $('#milestone').modal('show');
      $('#milestone .edit-title').addClass('hide');
  }

  function new_timesheet() {
      $('#timesheet').modal('show');
  }

  function edit_milestone(invoker, id) {

      var description_visible_to_customer = $(invoker).data('description-visible-to-customer');
      if (description_visible_to_customer == 1) {
          $('input[name="description_visible_to_customer"]').prop('checked', true);
      } else {
          $('input[name="description_visible_to_customer"]').prop('checked', false);
      }
      $('#additional_milestone').append(hidden_input('id', id));
      $('#milestone input[name="name"]').val($(invoker).data('name'));
      $('#milestone input[name="due_date"]').val($(invoker).data('due_date'));
      $('#milestone input[name="milestone_order"]').val($(invoker).data('order'));
      $('#milestone textarea[name="description"]').val($(invoker).data('description'));
      $('#milestone').modal('show');
      $('#milestone .add-title').addClass('hide');
  }

  function edit_discussion(invoker, id) {
      $('#additional_discussion').append(hidden_input('id', id));
      $('#discussion input[name="subject"]').val($(invoker).data('subject'));
      $('#discussion textarea[name="description"]').val($(invoker).data('description'));
      var checked = $(invoker).data('show-to-customer') == 0 ? false : true;
      $('#discussion input[name="show_to_customer"]').prop('checked', checked);
      $('#discussion').modal('show');
      $('#discussion .add-title').addClass('hide');
  }

  function mass_stop_timers(only_billable) {
      requestGetJSON('booking/bookings/mass_stop_timers/' + booking_id + '/' + only_billable).done(function(response) {
          alert_float(response.type, response.message);
          setTimeout(function() {
              $('body').find('.modal-backdrop').eq(0).remove();
              init_timers();
              reload_tours_tables();
              pre_invoice_booking();
          }, 500);
      });
  }

  function pre_invoice_booking() {
      requestGet('booking/bookings/get_pre_invoice_booking_info/' + booking_id).done(function(response) {
          $('#pre_invoice_booking').html(response);
          $('#pre_invoice_booking_settings').modal('show');
      });
  }

  function invoice_booking(booking_id) {
      $('#pre_invoice_booking_settings').modal('hide');
      var data = {};

      data.type = $('input[name="invoice_data_type"]:checked').val();
      data.timesheets_include_notes = $('input[name="timesheets_include_notes"]:checked').val();

      data.booking_id = booking_id;

      data.tours = $("#tours_who_will_be_billed input:checkbox:checked").map(function() {
          return $(this).val();
      }).get();

      data.expenses = $("#expenses_who_will_be_billed .expense-to-bill input:checkbox:checked").map(function() {
          return $(this).val();
      }).get();

      data.expenses_add_note = $("#expenses_who_will_be_billed .expense-add-note input:checkbox:checked").map(function() {
          return $(this).val();
      }).get();

      data.expenses_add_name = $("#expenses_who_will_be_billed .expense-add-name input:checkbox:checked").map(function() {
          return $(this).val();
      }).get();

      $.post(admin_url + 'booking/bookings/get_invoice_booking_data/', data).done(function(response) {
          $('#invoice_booking').html(response);
          $('#invoice-booking-modal').modal({
              show: true,
              backdrop: 'static'
          });
      });
  }

  function delete_booking_discussion(id) {
      if (confirm_delete()) {
          requestGetJSON('booking/bookings/delete_discussion/' + id).done(function(response) {
              alert_float(response.alert_type, response.message);
              $('.table-booking-discussions').DataTable().ajax.reload(null, false);
          });
      }
  }

  function bookingExpenseSubmitHandler(form) {
      $.post(form.action, $(form).serialize()).done(function(response) {
          response = JSON.parse(response);
          if (response.expenseid) {
              if (typeof(expenseDropzone) !== 'undefined') {
                  if (expenseDropzone.getQueuedFiles().length > 0) {
                      expenseDropzone.options.url = admin_url + 'expenses/add_expense_attachment/' + response.expenseid;
                      expenseDropzone.processQueue();
                  } else {
                      window.location.assign(response.url);
                  }
              } else {
                  window.location.assign(response.url);
              }
          } else {
              window.location.assign(response.url);
          }
      });
      return false;
  }

  function view_booking_file(id, $booking_id) {
      $('#booking_file_data').empty();
      $("#booking_file_data").load(admin_url + 'booking/bookings/file/' + id + '/' + booking_id, function(response, status, xhr) {
          if (status == "error") {
              alert_float('danger', xhr.statusText);
          }
      });
  }

  function update_file_data(id) {
      var data = {};
      data.id = id;
      data.subject = $('body input[name="file_subject"]').val();
      data.description = $('body textarea[name="file_description"]').val();
      $.post(admin_url + 'booking/bookings/update_file_data/', data);
  }

  function booking_mark_as_modal(status_id, $booking_id, target) {
      $('#mark_tours_finished_modal').modal('show');
      $('#booking_mark_status_confirm').attr('data-status-id', status_id);
      $('#booking_mark_status_confirm').attr('data-booking-id', booking_id);
      var $bookingMarkedAsFinishedInput = $('#booking_marked_as_finished_email_to_contacts');
      if (status_id == 4) {
          if ($bookingMarkedAsFinishedInput.length > 0) {
              $bookingMarkedAsFinishedInput.parents('.booking_marked_as_finished').removeClass('hide');
          }
      } else {
          if ($bookingMarkedAsFinishedInput.length > 0) {
              $bookingMarkedAsFinishedInput.prop('checked', false);
              $bookingMarkedAsFinishedInput.parents('.booking_marked_as_finished').addClass('hide');
          }
      }
      var noticeWrapper = $('.recurring-tours-notice');
      if (status_id == 4 || status_id == 5 || status_id == 3) {
          if (noticeWrapper.length) {
              var notice = noticeWrapper.data('notice-text');
              notice = notice.replace('{0}', $(target).data('name'));
              noticeWrapper.html(notice);
              noticeWrapper.append('<input type="hidden" name="cancel_recurring_tours" value="true">');
              noticeWrapper.removeClass('hide');
          }
          $('#mark_all_tours_as_completed').prop('checked', true);
      } else {
          noticeWrapper.html('').addClass('hide');
          $('#mark_all_tours_as_completed').prop('checked', false);
      }
  }

  function booking_files_bulk_action(e) {
      if (confirm_delete()) {
          var mass_delete = $('#mass_delete').prop('checked');
          var ids = [];
          var data = {};
          if (mass_delete == false || typeof(mass_delete) == 'undefined') {
              data.visible_to_customer = $('#bulk_pf_visible_to_customer').prop('checked');
          } else {
              data.mass_delete = true;
          }

          var rows = $('.table-booking-files').find('tbody tr');
          $.each(rows, function() {
              var checkbox = $($(this).find('td').eq(0)).find('input');
              if (checkbox.prop('checked') == true) {
                  ids.push(checkbox.val());
              }
          });

          data.ids = ids;
          $(e).addClass('disabled');

          setTimeout(function() {
              $.post(admin_url + 'booking/bookings/bulk_action_files', data).done(function() {
                  window.location.reload();
              });
          }, 200);
      }

  }

  function gantt_filter() {
      var status = $('select[name="gantt_tour_status"]').selectpicker('val');
      var gantt_type = $('select[name="gantt_type"]').selectpicker('val');
      var params = [];
      params['gantt_type'] = gantt_type;
      params['group'] = 'booking_gantt';
      if (status) {
          params['gantt_tour_status'] = status;
      }
      window.location.href = buildUrl(admin_url + 'booking/bookings/view/' + booking_id, params);
  }

  function confirm_booking_status_change(e) {
      var data = {};

      $(e).attr('disabled', true);

      data.booking_id = $(e).data('booking-id');
      data.status_id = $(e).data('status-id');

      if (data.status_id == 4) {
          var $bookingMarkedAsFinishedInput = $('#booking_marked_as_finished_email_to_contacts');
          if ($bookingMarkedAsFinishedInput.length > 0) {
              data.send_booking_marked_as_finished_email_to_contacts = $bookingMarkedAsFinishedInput.prop('checked') === true ? 1 : 0;
          }
      }

      data.mark_all_tours_as_completed = $('#mark_all_tours_as_completed').prop('checked') === true ? 1 : 0;
      data.cancel_recurring_tours = $('input[name="cancel_recurring_tours"]').val();

      if (!data.cancel_recurring_tours) {
          data.cancel_recurring_tours = false;
      } else {
          data.cancel_recurring_tours = true;
      }

      data.notify_booking_members_status_change = $('#notify_booking_members_status_change').prop('checked') === true ? 1 : 0;

      $.post(admin_url + 'booking/bookings/mark_as', data).done(function(response) {
          response = JSON.parse(response);
          alert_float(response.success === true ? 'success' : 'warning', response.message);
          setTimeout(function() {
              window.location.reload();
          }, 1500);
      }).fail(function(data) {
          window.location.reload();
      });
  }

  function milestones_kanban_update(ui, object) {
      if (object === ui.item.parent()[0]) {
          data = {};
          data.order = [];
          data.milestone_id = $(ui.item.parent()[0]).parents('.milestone-column').data('col-status-id');
          data.tour_id = $(ui.item).data('tour-id');
          var tours = $(ui.item.parent()[0]).parents('.milestone-column').find('.tour');

          var i = 0;
          $.each(tours, function() {
              data.order.push([$(this).data('tour-id'), i]);
              i++;
          });
          check_kanban_empty_col('[data-tour-id]');

          setTimeout(function() {
              $.post(admin_url + 'booking/bookings/update_tour_milestone', data)
          }, 50);
      }
  }

  function milestones_kanban() {
      init_kanban('booking/bookings/milestones_kanban', milestones_kanban_update, '.booking-milestone', 445, 360, after_milestones_kanban);
  }

  function after_milestones_kanban() {
      $("#kan-ban").sortable({
          helper: 'clone',
          item: '.kan-ban-col',
          cancel: '.milestone-not-sortable',
          update: function(event, ui) {
              var uncategorized_is_after = $(ui.item).next('ul.kan-ban-col[data-col-status-id="0"]');

              if (uncategorized_is_after.length) {
                  $(this).sortable('cancel');
                  return false;
              }

              var data = {}
              data.order = [];
              var status = $('.kan-ban-col');
              var i = 0;

              $.each(status, function() {
                  data.order.push([$(this).data('col-status-id'), i]);
                  i++;
              });

              $.post(admin_url + 'booking/bookings/update_milestones_order', data);
          }
      });

      for (var i = -10; i < $('.tour-phase').not('.color-not-auto-adjusted').length / 2; i++) {
          var r = 120;
          var g = 169;
          var b = 56;
          $('.tour-phase:eq(' + (i + 10) + ')').not('.color-not-auto-adjusted').css('background', color(r - (i * 13), g - (i * 13), b - (i * 13))).css('border', '1px solid ' + color(r - (i * 12), g - (i * 12), b - (i * 12)));
      };
  }

  // When marking tour as complete if the staff in on booking milestones area, remove this tour from milestone in case exists
  function _maybe_remove_tour_from_booking_milestone(tour_id) {
      var $milestonesToursWrappers = $('.milestone-column');
      if ($("body").hasClass('booking') && $milestonesToursWrappers.length > 0) {
          if ($('#exclude_completed_tours').prop('checked') == true) {
              $milestonesToursWrappers.find('[data-tour-id="' + tour_id + '"]').remove();
          }
      }
  }
</script>