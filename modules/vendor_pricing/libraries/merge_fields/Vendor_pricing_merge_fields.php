<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Vendor_pricing_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Vendor Name',
                'key'       => '{vendor_name}',
                'available' => [
                    'vendor_pricing',
                ],
            ],
            [
                'name'      => 'PO Number',
                'key'       => '{pur_order_number}',
                'available' => [
                    'vendor_pricing',
                ],
            ],
            [
                'name'      => 'Pricing Link',
                'key'       => '{vendor_pricing_link}',
                'available' => [
                    'vendor_pricing',
                ],
            ]
        ];
    }

    /**
     * Merge fields
     * @param  mixed $data 
     * @return array
     */
    public function format($data)
    {        
         $fields['{vendor_name}'] = isset($data->vendor_name) ? $data->vendor_name : '';
         $fields['{pur_order_number}'] = isset($data->pur_order_number) ? $data->pur_order_number : '';
         $fields['{vendor_pricing_link}'] = isset($data->vendor_pricing_link) ? $data->vendor_pricing_link : '';
         
         return $fields;
    }
}
