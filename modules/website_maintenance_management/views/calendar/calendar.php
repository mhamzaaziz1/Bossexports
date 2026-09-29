<?php
defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="no-margin">
                                    <i class="fa fa-calendar tw-mr-2"></i>
									<?php
									echo _l('wmm_calendar'); ?>
                                </h4>
                                <hr class="hr-panel-heading"/>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="staff_filter"><?php
										echo _l('wmm_filter_by_assignee'); ?></label>
                                    <select name="staff_filter" id="staff_filter" class="selectpicker" data-width="100%" data-none-selected-text="<?php
									echo _l('wmm_all_staff'); ?>">
                                        <option value=""><?php
											echo _l('wmm_all_staff'); ?></option>
										<?php
										foreach ($staff_members as $staff) { ?>
                                            <option value="<?php
											echo $staff['staffid']; ?>">
												<?php
												echo html_escape($staff['firstname'].' '.$staff['lastname']); ?>
                                            </option>
										<?php
										} ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-9 text-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default" id="calendar-prev">
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-default" id="calendar-today">
										<?php
										echo _l('today'); ?>
                                    </button>
                                    <button type="button" class="btn btn-default" id="calendar-next">
                                        <i class="fa fa-chevron-right"></i>
                                    </button>
                                </div>
                                <div class="btn-group mleft5">
                                    <button type="button" class="btn btn-default" id="calendar-month-view">
										<?php
										echo _l('calendar_month'); ?>
                                    </button>
                                    <button type="button" class="btn btn-default" id="calendar-week-view">
										<?php
										echo _l('calendar_week'); ?>
                                    </button>
                                    <button type="button" class="btn btn-default" id="calendar-day-view">
										<?php
										echo _l('calendar_day'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row mtop15">
                            <div class="col-md-12">
                                <div id="maintenance-calendar"></div>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="row mtop15">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <strong><?php
										echo _l('wmm_priority'); ?>:</strong>
                                    <span class="label" style="background-color: #64748b; margin-left: 10px;"><?php
										echo _l('wmm_priority_low'); ?></span>
                                    <span class="label" style="background-color: #3b82f6; margin-left: 5px;"><?php
										echo _l('wmm_priority_medium'); ?></span>
                                    <span class="label" style="background-color: #f59e0b; margin-left: 5px;"><?php
										echo _l('wmm_priority_high'); ?></span>
                                    <span class="label" style="background-color: #ef4444; margin-left: 5px;"><?php
										echo _l('wmm_priority_urgent'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
init_tail(); ?>

<!-- FullCalendar CSS & JS -->
<link href="<?php
echo base_url('assets/plugins/fullcalendar/lib/main.min.css'); ?>" rel="stylesheet">
<script src="<?php
echo base_url('assets/plugins/fullcalendar/lib/main.min.js'); ?>"></script>

<script>
    $(function () {
        var calendarEl = document.getElementById('maintenance-calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: false,
            editable: false,
            selectable: false,
            selectMirror: true,
            dayMaxEvents: true,
            events: function (info, successCallback, failureCallback) {
                var staff_id = $('#staff_filter').val();
                $.ajax({
                    url: admin_url + 'website_maintenance_management/calendar/events',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        start: info.startStr,
                        end: info.endStr,
                        staff_id: staff_id
                    },
                    success: function (events) {
                        successCallback(events);
                    },
                    error: function () {
                        failureCallback();
                    }
                });
            },
            eventClick: function (info) {
                info.jsEvent.preventDefault();
                if (info.event.url) {
                    window.location.href = info.event.url;
                }
            },
            eventDidMount: function (info) {
                $(info.el).tooltip({
                    title: info.event.title + ' - ' + info.event.extendedProps.category,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            }
        });

        calendar.render();

        // Navigation buttons
        $('#calendar-prev').on('click', function () {
            calendar.prev();
        });

        $('#calendar-today').on('click', function () {
            calendar.today();
        });

        $('#calendar-next').on('click', function () {
            calendar.next();
        });

        // View buttons
        $('#calendar-month-view').on('click', function () {
            calendar.changeView('dayGridMonth');
            $('.btn-group button').removeClass('active');
            $(this).addClass('active');
        });

        $('#calendar-week-view').on('click', function () {
            calendar.changeView('timeGridWeek');
            $('.btn-group button').removeClass('active');
            $(this).addClass('active');
        });

        $('#calendar-day-view').on('click', function () {
            calendar.changeView('timeGridDay');
            $('.btn-group button').removeClass('active');
            $(this).addClass('active');
        });

        // Set initial active view
        $('#calendar-month-view').addClass('active');

        // Filter by staff
        $('#staff_filter').on('change', function () {
            calendar.refetchEvents();
        });
    });
</script>

<style>
    #maintenance-calendar {
        max-width: 100%;
        margin: 0 auto;
    }

    .fc-event {
        cursor: pointer;
    }

    .fc-daygrid-event {
        white-space: normal !important;
        align-items: normal !important;
    }
</style>

</body>
</html>