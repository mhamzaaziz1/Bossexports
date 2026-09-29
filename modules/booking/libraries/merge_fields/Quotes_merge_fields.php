<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quotes_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
                [
                    'name'      => 'Quote ID',
                    'key'       => '{quote_id}',
                    'available' => [
                        'quotes',
                    ],
                ],
                [
                    'name'      => 'Quote Number',
                    'key'       => '{quote_number}',
                    'available' => [
                        'quotes',
                    ],
                ],
                [
                    'name'      => 'Subject',
                    'key'       => '{quote_subject}',
                    'available' => [
                        'quotes',
                    ],
                ],
                [
                    'name'      => 'Quote Total',
                    'key'       => '{quote_total}',
                    'available' => [
                        'quotes',
                    ],
                ],
                [
                    'name'      => 'Quote Subtotal',
                    'key'       => '{quote_subtotal}',
                    'available' => [
                        'quotes',
                    ],
                ],
                [
                    'name'      => 'Open Till',
                    'key'       => '{quote_open_till}',
                    'available' => [
                        'quotes',
                    ],
                ],
                [
                    'name'      => 'Quote Assigned',
                    'key'       => '{quote_assigned}',
                    'available' => [
                        'quotes',
                    ],
                ],
                [
                    'name'      => 'Quote To',
                    'key'       => '{quote_quote_to}',
                    'available' => [
                        'quotes',
                    ],
                ],
                [
                    'name'      => 'Address',
                    'key'       => '{quote_address}',
                    'available' => [
                        'quotes',
                    ],
                ],
                [
                    'name'      => 'City',
                    'key'       => '{quote_city}',
                    'available' => [
                        'quotes',
                    ],
                ],
                [
                    'name'      => 'State',
                    'key'       => '{quote_state}',
                    'available' => [
                        'quotes',
                    ],
                ],
                [
                    'name'      => 'Zip Code',
                    'key'       => '{quote_zip}',
                    'available' => [
                        'quotes',
                    ],
                ],
                [
                    'name'      => 'Country',
                    'key'       => '{quote_country}',
                    'available' => [
                        'quotes',
                    ],
                ],
                [
                    'name'      => 'Email',
                    'key'       => '{quote_email}',
                    'available' => [
                        'quotes',
                    ],
                ],
                [
                    'name'      => 'Phone',
                    'key'       => '{quote_phone}',
                    'available' => [
                        'quotes',
                    ],
                ],
                [
                    'name'      => 'Quote Link',
                    'key'       => '{quote_link}',
                    'available' => [
                        'quotes',
                    ],
                ],
            ];
    }

    /**
 * Merge fields for quotes
 * @param  mixed $quote_id quote id
 * @return array
 */
    public function format($quote_id)
    {
        $fields = [];
        $this->ci->db->where('id', $quote_id);
        $this->ci->db->join(db_prefix() . 'countries', db_prefix() . 'countries.country_id=' . db_prefix() . 'quotes.country', 'left');
        $quote = $this->ci->db->get(db_prefix() . 'quotes')->row();


        if (!$quote) {
            return $fields;
        }

        if ($quote->currency != 0) {
            $currency = get_currency($quote->currency);
        } else {
            $currency = get_base_currency();
        }

        $fields['{quote_id}']          = $quote_id;
        $fields['{quote_number}']      = format_quote_number($quote_id);
        $fields['{quote_link}']        = site_url('booking/quote/index/' . $quote_id . '/' . $quote->hash);
        $fields['{quote_subject}']     = $quote->subject;
        $fields['{quote_total}']       = app_format_money($quote->total, $currency);
        $fields['{quote_subtotal}']    = app_format_money($quote->subtotal, $currency);
        $fields['{quote_open_till}']   = _d($quote->open_till);
        $fields['{quote_quote_to}'] = $quote->quote_to;
        $fields['{quote_address}']     = $quote->address;
        $fields['{quote_email}']       = $quote->email;
        $fields['{quote_phone}']       = $quote->phone;

        $fields['{quote_city}']     = $quote->city;
        $fields['{quote_state}']    = $quote->state;
        $fields['{quote_zip}']      = $quote->zip;
        $fields['{quote_country}']  = $quote->short_name;
        $fields['{quote_assigned}'] = get_staff_full_name($quote->assigned);

        $custom_fields = get_custom_fields('quote');
        foreach ($custom_fields as $field) {
            $fields['{' . $field['slug'] . '}'] = get_custom_field_value($quote_id, $field['id'], 'quote');
        }

        return hooks()->apply_filters('quote_merge_fields', $fields, [
        'id'       => $quote_id,
        'quote' => $quote,
     ]);
    }
}
