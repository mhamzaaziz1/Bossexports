<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Proforma_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Proforma Number',
                'key'       => '{proforma_number}',
                'available' => [
                    'proforma',
                ],
            ],
            [
                'name'      => 'Proforma Link',
                'key'       => '{proforma_link}',
                'available' => [
                    'proforma',
                ],
            ],
            [
                'name'      => 'Proforma Date',
                'key'       => '{proforma_date}',
                'available' => [
                    'proforma',
                ],
            ],
            [
                'name'      => 'Proforma Total',
                'key'       => '{proforma_total}',
                'available' => [
                    'proforma',
                ],
            ],
        ];
    }

    public function format($id)
    {
        $fields = [];
        $this->ci->load->model('proforma/proforma_model');
        $proforma = $this->ci->proforma_model->get($id);

        if (!$proforma) {
            return $fields;
        }

        $currency = get_currency($proforma->currency);

        $fields['{proforma_number}'] = format_proforma_number($id);
        $fields['{proforma_link}']   = admin_url('proforma/invoice/' . $id); // Or client link if available
        $fields['{proforma_date}']    = _d($proforma->date);
        $fields['{proforma_total}']   = app_format_money($proforma->total, $currency);

        return $fields;
    }
}
