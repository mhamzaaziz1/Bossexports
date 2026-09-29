var fnServerParams;
(function ($) {
  "use strict";

  fnServerParams = {
    "software": '[name="software"]',
    "status": '[name="status"]',
    "from_date": '[name="from_date"]',
    "to_date": '[name="to_date"]',
    "organization": '[name="organization"]',
  };

  init_payments_table();

  $('select[name="organization"]').on('change', function () {
    init_payments_table();
  });

  $('select[name="status"]').on('change', function () {
    init_payments_table();
  });

  $('input[name="from_date"]').on('change', function () {
    init_payments_table();
  });

  $('input[name="to_date"]').on('change', function () {
    init_payments_table();
  });

})(jQuery);

function init_payments_table() {
  "use strict";

  if ($.fn.DataTable.isDataTable('.table-payments')) {
    $('.table-payments').DataTable().destroy();
  }
  initDataTable('.table-payments', admin_url + 'zoho_integration/payments_table', [0], [0], fnServerParams, [1, 'desc']);
}

function bulk_sync_transaction(type) {
  "use strict";

  var data = {};
  data.type = type;
  data.software = $('input[name="software"]').val();
  data.organization_id = $('select[name="organization"]').val();

  var ids = [];
  $('.table-payments').find('tbody input[type="checkbox"]:checked').each(function () {
    ids.push($(this).val());
  });

  if (ids.length === 0) {
    alert_float('warning', 'Please select at least one record to sync');
    return;
  }

  data.ids = ids;

  var currentIndex = 0;
  function syncNextRow() {
    if (currentIndex >= ids.length) {
      $('#box-loadding').html('');
      alert_float('success', 'Bulk synchronization completed');
      init_payments_table();
      return;
    }

    var id = ids[currentIndex];
    var sync_data = {
      id: id,
      type: type,
      software: data.software,
      organization_id: data.organization_id
    };

    var statusSpan = $('.payment-status-' + id);
    var row = statusSpan.closest('tr');
    row.css('background-color', '#fff9c4');

    $.post(admin_url + 'zoho_integration/manual_sync', sync_data).done(function (response) {
      response = JSON.parse(response);
      var errorCell = statusSpan.closest('td').next();

      if (response.success === true || response.success == 'true') {
        statusSpan.removeClass('label-default label-danger').addClass('label-success').text('Synchronized');
        errorCell.text('');
      } else {
        statusSpan.removeClass('label-default label-success').addClass('label-danger').text('Failed');
        errorCell.html(response.message);
      }
      row.css('background-color', '');
      currentIndex++;
      syncNextRow();
    }).fail(function () {
      statusSpan.removeClass('label-default label-success').addClass('label-danger').text('Error');
      row.css('background-color', '');
      currentIndex++;
      syncNextRow();
    });
  }

  var html = '';
  html += '<div class="Box">';
  html += '<span>';
  html += '<span></span>';
  html += '</span>';
  html += '</div>';
  $('#box-loadding').html(html);

  syncNextRow();
}


function manual_sync(invoker) {
  "use strict";

  var data = {};
  data.id = $(invoker).data('id');
  data.type = $(invoker).data('type');
  data.software = $(invoker).data('software');
  data.organization_id = $(invoker).data('organization-id');

  var html = '';
  html += '<div class="Box">';
  html += '<span>';
  html += '<span></span>';
  html += '</span>';
  html += '</div>';
  $('#box-loadding').html(html);
  setTimeout(function () {
    $('#box-loadding').html('');
    alert_float('warning', 'The synchronization all process can take a long time to complete');
  }, 60 * 1000);

  $.post(admin_url + 'zoho_integration/manual_sync', data).done(function (response) {
    response = JSON.parse(response);
    if (response.success === true || response.success == 'true') {
      $('#box-loadding').html('');
      alert_float('success', response.message);
      init_payments_table();
    } else {
      $('#box-loadding').html('');
      alert_float('danger', response.message);
    }
  });
}


function sync_transaction(invoker) {
  "use strict";

  var data = {};
  data.type = 'payment';
  data.software = $('input[name="software"]').val();
  data.organization_id = $('select[name="organization"]').val();

  var html = '';
  html += '<div class="Box">';
  html += '<span id="sync-progress-text">Connecting to Zoho...</span>';
  html += '<span><span></span></span>';
  html += '</div>';
  $('#box-loadding').html(html);

  var page = 1;
  var totalItems = 0;

  function syncPage() {
    data.page = page;
    $.post(admin_url + 'zoho_integration/sync_transaction_from_accounting', data).done(function (response) {
      response = JSON.parse(response);
      if (response.success === true || response.success == 'true') {
        totalItems += response.count;
        $('#sync-progress-text').text('Processed ' + totalItems + ' items (Page ' + page + ')');

        if (response.has_more_page === true) {
          page++;
          syncPage();
        } else {
          $('#box-loadding').html('');
          alert_float('success', 'Sync from Zoho completed. Total items: ' + totalItems);
          init_payments_table();
        }
      } else {
        $('#box-loadding').html('');
        alert_float('danger', response.message);
      }
    }).fail(function () {
      $('#box-loadding').html('');
      alert_float('danger', 'Sync failed at page ' + page);
    });
  }

  syncPage();
}