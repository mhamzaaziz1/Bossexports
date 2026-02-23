<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="invoice_to_delivery_note pull-right">
    <?php if (empty($invoice->delivery_noteid) && empty($invoice->invoiceid)) { ?>
        <?php if ((int)get_option('delivery_note_allow_creating_from_invoice') && staff_can('create', 'delivery_notes') && !empty($invoice->clientid)) { ?>
            <div class="btn-group pull-right mleft5" data-toggle="tooltip" data-title="<?= _l('estimate_convert_to_delivery_note_full'); ?>">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php echo _l('estimate_convert_to_delivery_note'); ?> <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo admin_url('delivery_notes/convert_from_invoice/' . $invoice->id); ?>"><?php echo _l('convert_and_save_as_delivered'); ?></a>
                    </li>
                    <li class="divider"></li>

                    <li><a href="<?php echo admin_url('delivery_notes/convert_from_invoice/' . $invoice->id . '?save_as_new=true'); ?>"><?php echo _l('convert'); ?></a>
                    </li>

                </ul>
            </div>
        <?php } ?>
        <?php } else if (!empty($invoice->delivery_noteid)) {
        $formated_dnid = format_delivery_note_number($invoice->delivery_noteid);
        if (!empty($formated_dnid)) { ?>
            <a href="<?php echo admin_url('delivery_notes/list_delivery_notes/' . $invoice->delivery_noteid); ?>" class="btn btn-primary mleft10 pull-right"><?php echo $formated_dnid; ?></a>
    <?php }
    } ?>
</div>

<script>
    document.querySelector('.panel-body ._buttons .pull-right').appendChild(document.querySelector(
        '.invoice_to_delivery_note'));
</script>