<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-mtop"><?php echo _l('bdm_report_engine'); ?></h4>
            <p class="text-muted"><?php echo _l('bdm_reports'); ?></p>
            <hr class="hr-panel-heading" />

            <div class="row">
              <?php
                $reports = [
                    ['id' => 'brands',           'title' => 'Master Brand Report',         'desc' => _l('bdm_report_brands_desc'),       'icon' => 'fa-tags'],
                    ['id' => 'distributors',     'title' => 'Vendor Directory',            'desc' => _l('bdm_report_distributors_desc'), 'icon' => 'fa-address-book'],
                    ['id' => 'national_dists',   'title' => 'National Vendors',            'desc' => _l('bdm_report_national_desc'),     'icon' => 'fa-map-marker'],
                    ['id' => 'intl_dists',       'title' => 'International Vendors',       'desc' => _l('bdm_report_intl_desc'),         'icon' => 'fa-globe'],
                    ['id' => 'registrations',    'title' => 'VR Pipeline Summary',         'desc' => _l('bdm_report_pipeline_desc'),     'icon' => 'fa-tasks'],
                    ['id' => 'overdue',          'title' => 'Overdue Follow-ups',          'desc' => _l('bdm_report_overdue_desc'),      'icon' => 'fa-bell'],
                    ['id' => 'approved_vrs',     'title' => 'Registered Vendors',          'desc' => _l('bdm_report_registered_desc'),   'icon' => 'fa-check-circle'],
                    ['id' => 'rejected_vrs',     'title' => 'Rejected/Archived Referrals', 'desc' => _l('bdm_report_rejected_desc'),     'icon' => 'fa-times-circle'],
                    ['id' => 'partners',         'title' => 'Partner Directory',           'desc' => _l('bdm_report_partners_desc'),     'icon' => 'fa-handshake-o'],
                    ['id' => 'commissions',      'title' => 'Commission Ledger',           'desc' => _l('bdm_report_commissions_desc'),  'icon' => 'fa-money'],
                    ['id' => 'tenders',          'title' => 'Tender Pipeline',             'desc' => _l('bdm_report_tenders_desc'),      'icon' => 'fa-file-text-o'],
                    ['id' => 'campaigns',        'title' => 'Campaign Performance',        'desc' => _l('bdm_report_campaigns_desc'),    'icon' => 'fa-bullhorn'],
                    ['id' => 'activity',         'title' => 'Full BDM Activity Log',       'desc' => _l('bdm_report_activity_desc'),     'icon' => 'fa-history'],
                    ['id' => 'master_companies', 'title' => 'Master Company Registry',     'desc' => _l('bdm_report_master_desc'),       'icon' => 'fa-building'],
                ];
                foreach ($reports as $report):
              ?>
              <div class="col-md-4">
                <div class="panel_s" style="border:1px solid #ddd; border-radius:8px;">
                  <div class="panel-body">
                    <div class="media">
                      <div class="media-left">
                        <i class="fa <?php echo $report['icon']; ?> fa-2x text-primary"></i>
                      </div>
                      <div class="media-body">
                        <h4 class="media-heading bold"><?php echo $report['title']; ?></h4>
                        <p class="text-muted small"><?php echo $report['desc']; ?></p>
                        <div class="btn-group">
                          <a href="<?php echo admin_url('bdm_operations/export/' . $report['id']); ?>" class="btn btn-info btn-xs"><i class="fa fa-download"></i> <?php echo _l('download_csv'); ?></a>
                          <a href="#" onclick="view_live_report('<?php echo $report['id']; ?>', '<?php echo $report['title']; ?>'); return false;" class="btn btn-default btn-xs"><i class="fa fa-eye"></i> View Live</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div id="report_live_view" style="display: none;">
      <div class="panel_s">
        <div class="panel-body">
          <div class="pull-right">
            <button class="btn btn-danger btn-xs" onclick="$('#report_live_view').hide();">Close</button>
          </div>
          <h4 id="report_title_display" class="no-mtop">Live Report Data</h4>
          <hr />
          <div id="report_table_holder"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
function view_live_report(type, title) {
    $('#report_title_display').text(title);
    $('#report_live_view').show();
    $('html, body').animate({ scrollTop: $("#report_live_view").offset().top }, 600);
    $('#report_table_holder').html('<div class="text-center mtop20"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading…</div>');
    $.get(admin_url + 'bdm_operations/get_report_data/' + type, function (response) {
        $('#report_table_holder').html(response);
    });
}
</script>
