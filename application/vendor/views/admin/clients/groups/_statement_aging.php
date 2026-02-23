<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="col-md-7">
  <div>
    <h4 class="no-margin bold">Aging Summary</h4>
    <p class="text-muted">Outstanding invoices by age</p>
    <hr />
    <table class="table table-bordered">
      <thead>
        <tr>
          <th class="text-center">Current</th>
          <th class="text-center">1-30 Days</th>
          <th class="text-center">31-60 Days</th>
          <th class="text-center">61-90 Days</th>
          <th class="text-center">Over 90 Days</th>
          <th class="text-center">Total</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="text-center"><?php echo isset($statement['aging']) ? app_format_money($statement['aging']['current'], $statement['currency']) : app_format_money(0, $statement['currency']); ?></td>
          <td class="text-center"><?php echo isset($statement['aging']) ? app_format_money($statement['aging']['1_30'], $statement['currency']) : app_format_money(0, $statement['currency']); ?></td>
          <td class="text-center"><?php echo isset($statement['aging']) ? app_format_money($statement['aging']['31_60'], $statement['currency']) : app_format_money(0, $statement['currency']); ?></td>
          <td class="text-center"><?php echo isset($statement['aging']) ? app_format_money($statement['aging']['61_90'], $statement['currency']) : app_format_money(0, $statement['currency']); ?></td>
          <td class="text-center"><?php echo isset($statement['aging']) ? app_format_money($statement['aging']['over_90'], $statement['currency']) : app_format_money(0, $statement['currency']); ?></td>
          <td class="text-center"><strong><?php echo isset($statement['aging']) ? app_format_money($statement['aging']['total'], $statement['currency']) : app_format_money(0, $statement['currency']); ?></strong></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>