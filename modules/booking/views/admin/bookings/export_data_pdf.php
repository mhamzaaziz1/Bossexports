<?php

defined('BASEPATH') or exit('No direct script access allowed');


$dimensions    = $pdf->getPageDimensions();
$custom_fields = get_custom_fields('bookings');

$divide_document_overview = 3;
// If custom fields found divide the overview in 4 parts not in 3 to include the custom fields too
if (count($custom_fields) > 0) {
    $divide_document_overview = 4;
}

// Like heading booking name
$html = '<h1>' . _l('booking_name') . ': ' . $booking->name . '</h1>';
// booking overview heading
$html .= '<h3>' . ucwords(_l('booking_overview')) . '</h3>';
if (!empty($booking->description)) {
    // Booking description
    $html .= '<p><b style="background-color:#f0f0f0;">' . _l('booking_description') . '</b><br /><br /> ' . $booking->description . '</p>';
}

$pdf->writeHTML($html, true, false, false, false, '');
$pdf->Ln(10);
$html = '';
// Booking overview

$html .= '<b style="background-color:#f0f0f0;">' . _l('booking_overview') . '</b><br /><br />';

if ($booking->billing_type == 1 || $booking->billing_type == 2) {
    if ($booking->billing_type == 1) {
        $html .= '<b>' . _l('booking_total_cost') . ': </b>' . app_format_money($booking->booking_cost, $booking->currency_data) . '<br />';
    } else {
        $html .= '<b>' . _l('booking_rate_per_hour') . ': </b>' . app_format_money($booking->booking_rate_per_hour, $booking->currency_data) . '<br />';
    }
}
$status = get_booking_status_by_id($booking->status);
// Booking status
$html .= '<b>' . _l('booking_status') . ': </b>' . $status['name'] . '<br />';
// Start date
$html .= '<b>' . _l('booking_start_date') . ': </b>' . _d($booking->start_date) . '<br />';
// Write booking overview data
$pdf->MultiCell(($dimensions['wk'] / $divide_document_overview) - $dimensions['lm'], 0, $html, 0, 'L', 0, 0, '', '', true, 0, true);

$html = '';
$html .= '<b style="background-color:#f0f0f0;">' . ucwords(_l('finance_overview')) . '</b><br /><br />';
$html .= '<b>' . _l('bookings_total_invoices_created') . ' </b>' . $total_invoices . '<br />';
// Not paid invoices total
$html .= '<b>' . _l('outstanding_invoices') . ' </b>' . app_format_money($invoices_total_data['due'], $booking->currency_data) . '<br />';
// Due invoices total
$html .= '<b>' . _l('past_due_invoices') . ' </b>' . app_format_money($invoices_total_data['overdue'], $booking->currency_data) . '<br />';
// Paid invoices
$html .= '<b>' . _l('paid_invoices') . ' </b>' . app_format_money($invoices_total_data['paid'], $booking->currency_data) . '<br /><br />';

// Finance Overview
if ($booking->billing_type == 2 || $booking->billing_type == 3) {
    // Total logged time + money
    $logged_time_data = $this->ci->bookings_model->total_logged_time_by_billing_type($booking->id);
    $html .= '<b>' . _l('booking_overview_logged_hours') . ' </b>' . $logged_time_data['logged_time'] . ' - ' . app_format_money($logged_time_data['total_money'], $booking->currency_data) . '<br />';
    // Total billable time + money
    $logged_time_data = $this->ci->bookings_model->data_billable_time($booking->id);
    $html .= '<b>' . _l('booking_overview_billable_hours') . ' </b>' . $logged_time_data['logged_time'] . ' - ' . app_format_money($logged_time_data['total_money'], $booking->currency_data) . '<br />';
    // Total billed time + money
    $logged_time_data = $this->ci->bookings_model->data_billed_time($booking->id);
    $html .= '<b>' . _l('booking_overview_billed_hours') . ' </b>' . $logged_time_data['logged_time'] . ' - ' . app_format_money($logged_time_data['total_money'], $booking->currency_data) . '<br />';
    // Total unbilled time + money
    $logged_time_data = $this->ci->bookings_model->data_unbilled_time($booking->id);
    $html .= '<b>' . _l('booking_overview_unbilled_hours') . ' </b>' . $logged_time_data['logged_time'] . ' - ' . app_format_money($logged_time_data['total_money'], $booking->currency_data) . '<br /><br/>';
}

$pdf->MultiCell(($dimensions['wk'] / $divide_document_overview) - $dimensions['lm'], 0, $html, 0, 'L', 0, 0, '', '', true, 0, true);

// Custom fields
// Check for custom fields
if (count($custom_fields) > 0) {
    $html = '';
    $html .= '<b style="background-color:#f0f0f0;">' . ucwords(_l('booking_custom_fields')) . '</b><br /><br />';

    foreach ($custom_fields as $field) {
        $value = get_custom_field_value($booking->id, $field['id'], 'bookings');
        $value = $value === '' ? '/' : $value;
        $html .= '<b>' . ucfirst($field['name']) . ': </b>' . $value . '<br />';
    }

    // Write custom fields
    $pdf->MultiCell(($dimensions['wk'] / $divide_document_overview) - $dimensions['lm'], 0, $html, 0, 'L', 0, 0, '', '', true, 0, true);
}

$html = '';
// Customer Info
$html .= '<b style="background-color:#f0f0f0;">' . ucwords(_l('booking_customer')) . '</b><br /><br /><b>' . $booking->client_data->company . '</b><br />';
$html .= $booking->client_data->address . '<br />';

if (!empty($booking->client_data->city)) {
    $html .= $booking->client_data->city;
}
if (!empty($booking->client_data->state)) {
    $html .= ', ' . $booking->client_data->state;
}
$country = get_country_short_name($booking->client_data->country);
if (!empty($country)) {
    $html .= '<br />' . $country;
}
if (!empty($booking->client_data->zip)) {
    $html .= ', ' . $booking->client_data->zip;
}
if (!empty($booking->client_data->phonenumber)) {
    $html .= '<br />' . $booking->client_data->phonenumber;
}
if (!empty($booking->client_data->vat)) {
    $html .= '<br />' . _l('client_vat_number') . ': ' . $booking->client_data->vat;
}

// Write custom info
$pdf->MultiCell(($dimensions['wk'] / $divide_document_overview) - $dimensions['lm'], 0, $html, 0, 'L', 0, 0, '', '', true, 0, true);

// Set new lines to prevent overlaping the content
$pdf->Ln(80);

if (ob_get_length() > 0 && ENVIRONMENT == 'production') {
    ob_end_clean();
}

// Output PDF to user
$pdf->output('#' . $booking->id . '_' . $booking->name . '_' . _d(date('Y-m-d')) . '.pdf', 'I');
