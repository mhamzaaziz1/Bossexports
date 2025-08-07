<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-12">
        <?php if (isset($report_data) && !empty($report_data)) { ?>
            <div class="panel_s">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="no-margin"><?php echo _l('avg_purchase_aging'); ?></h4>
                            <hr class="hr-panel-heading" />
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="avg-purchase-aging-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo _l('item'); ?></th>
                                            <th><?php echo _l('avg_age_days'); ?></th>
                                            <th><?php echo _l('total_purchases'); ?></th>
                                            <th><?php echo _l('total_quantity'); ?></th>
                                            <th><?php echo _l('transaction_type'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data as $row) { ?>
                                            <tr>
                                                <td><?php echo $row['description']; ?></td>
                                                <td><?php echo $row['avg_age']; ?></td>
                                                <td><?php echo $row['total_purchases']; ?></td>
                                                <td><?php echo $row['total_quantity']; ?></td>
                                                <td>
                                                    <?php 
                                                    if ($row['type'] == 'purchase') {
                                                        echo _l('purchases');
                                                    } elseif ($row['type'] == 'sale') {
                                                        echo _l('sales');
                                                    } else {
                                                        echo _l('both_sales_and_purchases');
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                $(function() {
                    // Initialize DataTable
                    var avgPurchaseAgingTable = $('#avg-purchase-aging-table').DataTable({
                        "order": [[1, "desc"]], // Sort by avg_age by default
                        "pageLength": 25,
                        "columnDefs": [
                            { "type": "numeric", "targets": [1, 2, 3] }
                        ],
                        "dom": 'Bfrtip',
                        "buttons": [
                            'copyHtml5',
                            'excelHtml5',
                            'csvHtml5',
                            'pdfHtml5'
                        ]
                    });
                });
            </script>
        <?php } else { ?>
            <div class="alert alert-info">
                <?php echo isset($selected_transaction_type) ? _l('no_data_found_for_selected_criteria') : _l('generate_report_to_view_data'); ?>
            </div>
        <?php } ?>
    </div>
</div>