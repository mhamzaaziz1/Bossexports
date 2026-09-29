<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
if (!empty($invoice->deposit_from_estimate_id)) {
    $estimate = $this->estimates_model->db->where('id', $invoice->deposit_from_estimate_id)->get(db_prefix() . 'estimates')->row();
?>
<div class="estimate_to_deposit pull-right">
    <?php if (!empty($estimate->id)) { ?>
    <a href="<?php echo admin_url('estimates/list_estimates/' . $estimate->id); ?>"
        class="btn btn-primary mleft10"><?php echo format_estimate_number($estimate->id); ?></a>
    <?php } ?>
</div>

<script>
document.querySelector('.panel-body ._buttons .pull-right')?.appendChild(document.querySelector(
    '.estimate_to_deposit'));
</script>
<?php } ?>