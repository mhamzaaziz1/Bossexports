<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_buttons tw-mb-2 sm:tw-mb-4">
                    <a href="<?php echo admin_url('approvify/manage_requests'); ?>"
                       class="btn btn-primary mright5 pull-left display-block">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('approvify_request'); ?>
                    </a>
                    <div class="row">
                        <div class="col-sm-4 col-xs-12 pull-right leads-search">
                                <div>
                                    <?php echo render_input('search', '', '', 'search', ['data-name' => 'search', 'onkeyup' => 'myRequestsKanBan();', 'placeholder' => _l('approvify_search_based_on_request_title')], [], 'no-margin') ?>
                                </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="<?php echo $isKanBan ? '' : 'panel_s' ; ?>">
                    <div class="<?php echo $isKanBan ? '' : 'panel-body' ; ?>">
                        <div class="tab-content">
                            <?php
                            if ($isKanBan) { ?>
                                <div class="active kan-ban-tab" id="kan-ban-tab" style="overflow:auto;">
                                    <div class="row">
                                        <div class="container-fluid leads-kan-ban">
                                            <div id="kan-ban"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    "use strict";

    $(function() {
        myRequestsKanBan();
    });

    function myRequestsKanBan(search) {
        init_kanban(
            "approvify/kanban",
            '',
            ".leads-status",
            290,
            360,
            ''
        );
    }

</script>
</body>

</html>