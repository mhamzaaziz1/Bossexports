<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<table class="table dt-table dt-report" data-order-col="0" data-order-type="desc">
    <thead>
        <tr>
            <?php foreach($headers as $h){ ?>
                <th><?php echo $h; ?></th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach($rows as $row){ ?>
            <tr>
                <?php foreach($row as $cell){ ?>
                    <td><?php echo $cell; ?></td>
                <?php } ?>
            </tr>
        <?php } ?>
    </tbody>
</table>
