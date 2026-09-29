/**
 * Vendor Sourcing — Warehouse Item Integration
 * Injects an "Assigned Vendors" tab into the warehouse item detail page.
 */
(function ($) {
    'use strict';

    $(function () {
        // Only run on the warehouse item detail page URL
        var match = window.location.href.match(/warehouse\/view_commodity_detail\/(\d+)/);
        if (!match) {
            return;
        }

        var itemId = match[1];

        // The tab nav in view_commodity_detail.php is:
        // <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
        // Target the ul directly — it has class 'nav-tabs-horizontal'
        var $tabUl = $('ul.nav-tabs-horizontal');
        if ($tabUl.length === 0) {
            $tabUl = $('ul.nav-tabs').last();
        }

        if ($tabUl.length === 0) {
            // Cannot find nav — do not inject
            return;
        }

        // Prevent double injection
        if ($('#vs_tab_li').length > 0) {
            return;
        }

        // 1. Inject the tab link
        $tabUl.append(
            '<li role="presentation" id="vs_tab_li">' +
            '  <a href="#vs_assigned_vendors" aria-controls="vs_assigned_vendors" role="tab" data-toggle="tab" id="tab_vs_assigned_vendors">' +
            '    <i class="fa fa-truck"></i> Assigned Vendors' +
            '  </a>' +
            '</li>'
        );

        // 2. Inject the tab pane.
        // Structure: td > div.horizontal-scrollable-tabs > ... > ul.nav-tabs-horizontal
        //            td > div.tab-content  (sibling of horizontal-scrollable-tabs, inside same td)
        var $tabContent = $tabUl.closest('td').find('.tab-content').first();
        if ($tabContent.length === 0) {
            // Ultimate fallback
            $tabContent = $('.tab-content').first();
        }

        $tabContent.append(
            '<div role="tabpanel" class="tab-pane row" id="vs_assigned_vendors" style="padding:15px;">' +
            '  <div class="col-md-12" id="vs_vendor_content">' +
            '    <p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Loading vendor mappings...</p>' +
            '  </div>' +
            '</div>'
        );

        // 3. Lazy-load via $.ajax when tab is first shown
        var loaded = false;
        $(document).on('shown.bs.tab', 'a[href="#vs_assigned_vendors"]', function () {
            if (loaded) return;
            loaded = true;

            $.ajax({
                url: admin_url + 'vendor_sourcing/warehouse_item_vendors_ui/' + itemId,
                type: 'GET',
                success: function (html, status, xhr) {
                    if (xhr.status === 200) {
                        $('#vs_vendor_content').html(html);
                        // Init selectpickers after content is in DOM
                        setTimeout(function () {
                            $('#vs_vendor_content .selectpicker').selectpicker('refresh');
                        }, 100);
                    } else {
                        loaded = false; // Allow retry
                        $('#vs_vendor_content').html('<div class="alert alert-danger">Unexpected response (HTTP ' + xhr.status + '). Please try again.</div>');
                    }
                },
                error: function (xhr) {
                    loaded = false; // Allow retry on click
                    var msg = 'Failed to load vendor mappings (HTTP ' + xhr.status + ').';
                    if (xhr.responseText) {
                        msg += '<br><small style="font-family:monospace;">' + $('<div>').text(xhr.responseText.substring(0, 300)).html() + '</small>';
                    }
                    $('#vs_vendor_content').html('<div class="alert alert-danger">' + msg + '</div>');
                }
            });
        });
    });

}(jQuery));
