<?php

defined('BASEPATH') or exit('No direct script access allowed');

function proforma_pdf($proforma, $tag = '')
{
    $CI = &get_instance();

    $custom_pdf_path = hooks()->apply_filters('proforma_pdf_class_path', '');

    if ($custom_pdf_path && file_exists($custom_pdf_path)) {
        include_once($custom_pdf_path);
        
        // We assume the class name is Proforma_pdf based on Custom PDF module convention
        if (class_exists('Proforma_pdf')) {
            $pdf = new Proforma_pdf($proforma, $tag);
            return $pdf->prepare();
        }
    }

    $CI->load->library('proforma/pdf/proforma_invoice_pdf', ['proforma' => $proforma, 'tag' => $tag]);

    return $CI->proforma_invoice_pdf->prepare();
}

function get_proforma_item_taxes($itemid)
{
    $CI = &get_instance();
    $CI->db->where('itemid', $itemid);
    $CI->db->where('rel_type', 'proforma');
    $taxes = $CI->db->get(db_prefix() . 'item_tax')->result_array();

    return $taxes;
}

function format_proforma_number($id)
{
    $CI = &get_instance();
    $CI->db->select('number,prefix,number_format,date');
    $CI->db->where('id', $id);
    $invoice = $CI->db->get(db_prefix() . 'proformainvoices')->row();

    if (!$invoice) {
        return '';
    }

    $number = sales_number_format($invoice->number, $invoice->number_format, $invoice->prefix, $invoice->date);

    return $number;
}

function format_proforma_status($status, $classes = '', $label = true)
{
    $id          = $status;
    $label_class = 'default';
    
    // Define statuses (Draft=1, Sent=2, Declined=3, Accepted=4, Expired=5 - assuming standard or similar to Estimate)
    // Actually Proforma statuses in Model: 1=Draft, 2=Sent, 3=Open/Unpaid? 
    // Let's assume standard Perfex colors/logic: 
    // 1=Draft (Gray), 2=Sent (Blue), 3=Declined (Red), 4=Accepted (Green), 5=Expired (Orange), 6=Converted (Info)
    
    if ($status == 1) {
        $status      = _l('proforma_status_unpaid');
        $label_class = 'danger'; // Unpaid usually red/danger
    } elseif ($status == 2) {
        $status      = _l('proforma_status_paid');
        $label_class = 'success';
    } elseif ($status == 3) {
        $status      = _l('proforma_status_partially');
        $label_class = 'warning';
    } elseif ($status == 4) {
        $status      = _l('proforma_status_overdue');
        $label_class = 'warning';
    } elseif ($status == 5) {
        $status      = _l('proforma_status_cancelled');
        $label_class = 'danger';
    } elseif ($status == 6) {
        $status      = _l('proforma_status_draft');
        $label_class = 'default';
    }

    if ($label == true) {
        return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status">' . $status . '</span>';
    }

    return $status;
}

function proforma_status_color_pdf($status_id)
{
    $statusColor = '';

    if ($status_id == 1) {
        $statusColor = '119, 119, 119'; // Draft - Gray
    } elseif ($status_id == 2) {
        $statusColor = '3, 169, 244'; // Sent - Blue
    } elseif ($status_id == 3) {
        $statusColor = '252, 45, 66'; // Declined - Red
    } elseif ($status_id == 4) {
        $statusColor = '132, 197, 41'; // Accepted - Green
    } elseif ($status_id == 5) {
        $statusColor = '255, 111, 0'; // Expired - Orange
    } elseif ($status_id == 6) {
        $statusColor = '132, 197, 41'; // Converted - Green
    }

    return $statusColor;
}


