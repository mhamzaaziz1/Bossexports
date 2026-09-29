<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <!-- Stats Cards -->
                <div class="row mbot15">
                    <div class="col-md-2">
                        <div class="panel_s text-center" style="border-left:4px solid #667eea;">
                            <div class="panel-body" style="padding:15px 10px;">
                                <h3 class="no-margin font-bold text-info"><?php echo $stats['total']; ?></h3>
                                <small class="text-muted">Total Inquiries</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel_s text-center" style="border-left:4px solid #3498db;">
                            <div class="panel-body" style="padding:15px 10px;">
                                <h3 class="no-margin font-bold" style="color:#3498db;"><?php echo $stats['emails']; ?></h3>
                                <small class="text-muted"><i class="fa fa-envelope"></i> Emails</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel_s text-center" style="border-left:4px solid #2ecc71;">
                            <div class="panel-body" style="padding:15px 10px;">
                                <h3 class="no-margin font-bold" style="color:#2ecc71;"><?php echo $stats['sms']; ?></h3>
                                <small class="text-muted"><i class="fa fa-comment"></i> SMS</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel_s text-center" style="border-left:4px solid #27ae60;">
                            <div class="panel-body" style="padding:15px 10px;">
                                <h3 class="no-margin font-bold" style="color:#27ae60;"><?php echo $stats['sent']; ?></h3>
                                <small class="text-muted"><i class="fa fa-check"></i> Sent</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel_s text-center" style="border-left:4px solid #e74c3c;">
                            <div class="panel-body" style="padding:15px 10px;">
                                <h3 class="no-margin font-bold" style="color:#e74c3c;"><?php echo $stats['failed']; ?></h3>
                                <small class="text-muted"><i class="fa fa-times"></i> Failed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel_s text-center" style="border-left:4px solid #f39c12;">
                            <div class="panel-body" style="padding:15px 10px;">
                                <h3 class="no-margin font-bold" style="color:#f39c12;"><?php echo $stats['today']; ?></h3>
                                <small class="text-muted"><i class="fa fa-calendar-check-o"></i> Today</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <i class="fa fa-history text-info mright5"></i> Inquiry Log
                        </h4>
                        <hr class="hr-panel-heading" />

                        <!-- Filter Row -->
                        <div class="row mbot15" id="il_filter_row">
                            <div class="col-md-2">
                                <label class="control-label">Channel</label>
                                <select id="filter_channel" class="selectpicker" data-width="100%" title="All Channels">
                                    <option value="">All</option>
                                    <option value="email">Email</option>
                                    <option value="sms">SMS</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="control-label">Vendor</label>
                                <select id="filter_vendor" class="selectpicker" data-width="100%" data-live-search="true" title="All Vendors">
                                    <option value="">All</option>
                                    <?php foreach ($filter_vendors as $v): ?>
                                        <option value="<?php echo $v['userid']; ?>"><?php echo htmlspecialchars($v['company']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="control-label">Status</label>
                                <select id="filter_status" class="selectpicker" data-width="100%" title="All Statuses">
                                    <option value="">All</option>
                                    <option value="sent">Sent</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="control-label">Date From</label>
                                <input type="text" id="filter_date_from" class="form-control datepicker" placeholder="Start date" autocomplete="off">
                            </div>
                            <div class="col-md-2">
                                <label class="control-label">Date To</label>
                                <input type="text" id="filter_date_to" class="form-control datepicker" placeholder="End date" autocomplete="off">
                            </div>
                            <div class="col-md-2" style="padding-top:24px;">
                                <button type="button" id="il_search_btn" class="btn btn-primary btn-sm">
                                    <i class="fa fa-search"></i> Search
                                </button>
                                <button type="button" id="il_reset_btn" class="btn btn-default btn-sm mleft5">
                                    <i class="fa fa-times"></i> Clear
                                </button>
                            </div>
                        </div>

                        <!-- DataTable -->
                        <div class="table-responsive">
                            <?php render_datatable([
                                'Date/Time',
                                'Product',
                                'Vendor',
                                'Channel',
                                'Subject',
                                'Status',
                                'Action',
                            ], 'inquiry-log'); ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<!-- Detail Modal -->
<div class="modal fade" id="il-detail-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:4px 4px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:0.8;"><span>&times;</span></button>
                <h4 class="modal-title">
                    <i class="fa fa-file-text mright5"></i>
                    Inquiry Details
                </h4>
            </div>
            <div class="modal-body" id="il-detail-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-condensed no-margin">
                            <tr><td class="text-muted" width="35%">Vendor</td><td id="detail-vendor" class="font-bold"></td></tr>
                            <tr><td class="text-muted">Email</td><td id="detail-email"></td></tr>
                            <tr><td class="text-muted">Phone</td><td id="detail-phone"></td></tr>
                            <tr><td class="text-muted">Channel</td><td id="detail-channel"></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-condensed no-margin">
                            <tr><td class="text-muted" width="35%">Status</td><td id="detail-status"></td></tr>
                            <tr><td class="text-muted">Sent By</td><td id="detail-staff"></td></tr>
                            <tr><td class="text-muted">Date/Time</td><td id="detail-date"></td></tr>
                            <tr id="detail-error-row" style="display:none;"><td class="text-muted">Error</td><td id="detail-error" class="text-danger"></td></tr>
                        </table>
                    </div>
                </div>
                <hr />
                <div id="detail-subject-section">
                    <label class="control-label text-muted"><i class="fa fa-tag mright5"></i>Subject</label>
                    <div class="well well-sm" id="detail-subject" style="word-break:break-all;"></div>
                </div>
                <label class="control-label text-muted"><i class="fa fa-file-text-o mright5"></i>Message Body</label>
                <div class="well" id="detail-body" style="max-height:300px;overflow-y:auto;word-break:break-all;"></div>
            </div>
        </div>
    </div>
</div>

<script>
$(function(){
    var activeFilters = {
        filter_channel: '',
        filter_vendor: '',
        filter_status: '',
        filter_date_from: '',
        filter_date_to: ''
    };

    var logTable = $('.table-inquiry-log').DataTable({
        serverSide: true,
        processing: true,
        retrieve: true,
        ajax: {
            url: admin_url + 'vendor_sourcing/inquiry_log_data',
            type: 'POST',
            data: function(d){
                $.extend(d, activeFilters);
                if(typeof(csrfData) !== 'undefined'){
                    d[csrfData.token_name] = csrfData.hash;
                }
            }
        },
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3, orderable: false },
            { data: 4 },
            { data: 5, orderable: false },
            { data: 6, orderable: false }
        ],
        drawCallback: function(){
            $('.table-inquiry-log').removeClass('dt-table-loading');
            $('.table-inquiry-log').parents('.table-loading').removeClass('table-loading');
        },
        pageLength: 25,
        order: [[0, 'desc']]
    });

    // Search
    $('#il_search_btn').on('click', function(){
        activeFilters.filter_channel   = $('#filter_channel').val() || '';
        activeFilters.filter_vendor    = $('#filter_vendor').val() || '';
        activeFilters.filter_status    = $('#filter_status').val() || '';
        activeFilters.filter_date_from = $('#filter_date_from').val() || '';
        activeFilters.filter_date_to   = $('#filter_date_to').val() || '';
        logTable.ajax.reload();
    });

    // Reset
    $('#il_reset_btn').on('click', function(){
        $('#filter_channel, #filter_vendor, #filter_status').val('').trigger('change');
        $('.selectpicker').selectpicker('refresh');
        $('#filter_date_from, #filter_date_to').val('');
        activeFilters = { filter_channel: '', filter_vendor: '', filter_status: '', filter_date_from: '', filter_date_to: '' };
        logTable.ajax.reload();
    });

    // Detail modal
    $(document).on('click', '.vs-log-detail-btn', function(){
        var $btn = $(this);
        $('#detail-vendor').text($btn.data('vendor'));
        $('#detail-email').text($btn.data('email') || '—');
        $('#detail-phone').text($btn.data('phone') || '—');

        var ch = $btn.data('channel');
        $('#detail-channel').html(
            ch === 'email'
                ? '<span class="label label-info"><i class="fa fa-envelope"></i> Email</span>'
                : '<span class="label label-success"><i class="fa fa-comment"></i> SMS</span>'
        );

        var st = $btn.data('status');
        $('#detail-status').html(
            st === 'sent'
                ? '<span class="label label-success">Sent</span>'
                : '<span class="label label-danger">Failed</span>'
        );

        $('#detail-staff').text($btn.data('staff') || '—');
        $('#detail-date').text($btn.data('date'));

        if(st === 'failed' && $btn.data('error')){
            $('#detail-error-row').show();
            $('#detail-error').text($btn.data('error'));
        } else {
            $('#detail-error-row').hide();
        }

        if(ch === 'email'){
            $('#detail-subject-section').show();
            $('#detail-subject').text($btn.data('subject'));
            $('#detail-body').html($btn.data('body'));
        } else {
            $('#detail-subject-section').hide();
            $('#detail-body').text($btn.data('body'));
        }

        $('#il-detail-modal').modal('show');
    });

    // Init datepickers
    if($.fn.datepicker){
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    }
});
</script>
</body>
</html>
