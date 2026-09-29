<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('format_aging_pdf_money')) {
    function format_aging_pdf_money($amount) {
        if (!is_numeric($amount)) $amount = 0;
        return number_format($amount, 2, get_option('decimal_separator'), get_option('thousand_separator'));
    }
}

ob_start();
?>
<h1 style="text-align:center;"><?php echo _l('aging_report'); ?> - <?php echo date('Y-m-d'); ?></h1>
<hr />

<?php if ($type == 'items'): ?>
<h2><?php echo _l('item_aging'); ?></h2>
<table width="100%" border="1" cellpadding="5">
    <thead>
        <tr style="background-color:#f2f2f2;">
            <th><?php echo _l('item_code'); ?></th>
            <th><?php echo _l('item_description'); ?></th>
            <th><?php echo _l('quantity_on_hand'); ?></th>
            <th><?php echo _l('days_since_purchase'); ?></th>
            <th><?php echo _l('aging_category'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><?php echo $item['code']; ?></td>
            <td><?php echo $item['description']; ?></td>
            <td><?php echo $item['qty']; ?></td>
            <td><?php echo $item['days_since_purchase']; ?></td>
            <td><?php echo $item['aging_category']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php if ($type == 'customers'): ?>
<br />
<h2><?php echo _l('customer_aging'); ?></h2>
<table width="100%" border="1" cellpadding="5">
    <thead>
        <tr style="background-color:#f2f2f2;">
            <th><?php echo _l('company'); ?></th>
            <th><?php echo _l('current'); ?></th>
            <th>1-7 <?php echo _l('days'); ?></th>
            <th>8-14 <?php echo _l('days'); ?></th>
            <th>15-21 <?php echo _l('days'); ?></th>
            <th>22-30 <?php echo _l('days'); ?></th>
            <th>31-60 <?php echo _l('days'); ?></th>
            <th>61-90 <?php echo _l('days'); ?></th>
            <th>91-120 <?php echo _l('days'); ?></th>
            <th><?php echo _l('total_unpaid'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($customers as $c): ?>
        <tr>
            <td><?php echo $c['company']; ?></td>
            <td><?php echo format_aging_pdf_money($c['current']); ?></td>
            <td><?php echo format_aging_pdf_money($c['1_7']); ?></td>
            <td><?php echo format_aging_pdf_money($c['8_14']); ?></td>
            <td><?php echo format_aging_pdf_money($c['15_21']); ?></td>
            <td><?php echo format_aging_pdf_money($c['22_30']); ?></td>
            <td><?php echo format_aging_pdf_money($c['31_60']); ?></td>
            <td><?php echo format_aging_pdf_money($c['61_90']); ?></td>
            <td><?php echo format_aging_pdf_money($c['over_90']); ?></td>
            <td><strong><?php echo format_aging_pdf_money($c['total_unpaid']); ?></strong></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php if ($type == 'vendors'): ?>
<br />
<h2><?php echo _l('vendor_aging'); ?></h2>
<table width="100%" border="1" cellpadding="5">
    <thead>
        <tr style="background-color:#f2f2f2;">
            <th><?php echo _l('vendor'); ?></th>
            <th><?php echo _l('current'); ?></th>
            <th>1-7 <?php echo _l('days'); ?></th>
            <th>8-14 <?php echo _l('days'); ?></th>
            <th>15-21 <?php echo _l('days'); ?></th>
            <th>22-30 <?php echo _l('days'); ?></th>
            <th>31-60 <?php echo _l('days'); ?></th>
            <th>61-90 <?php echo _l('days'); ?></th>
            <th>91-120 <?php echo _l('days'); ?></th>
            <th>120+ <?php echo _l('days'); ?></th>
            <th><?php echo _l('total_unpaid'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($vendors as $v): ?>
        <tr>
            <td><?php echo $v['company']; ?></td>
            <td><?php echo format_aging_pdf_money($v['current']); ?></td>
            <td><?php echo format_aging_pdf_money($v['1_7']); ?></td>
            <td><?php echo format_aging_pdf_money($v['8_14']); ?></td>
            <td><?php echo format_aging_pdf_money($v['15_21']); ?></td>
            <td><?php echo format_aging_pdf_money($v['22_30']); ?></td>
            <td><?php echo format_aging_pdf_money($v['31_60']); ?></td>
            <td><?php echo format_aging_pdf_money($v['61_90']); ?></td>
            <td><?php echo format_aging_pdf_money($v['91_120']); ?></td>
            <td><?php echo format_aging_pdf_money($v['over_120']); ?></td>
            <td><strong><?php echo format_aging_pdf_money($v['total_unpaid']); ?></strong></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html, true, false, true, false, '');
?>
