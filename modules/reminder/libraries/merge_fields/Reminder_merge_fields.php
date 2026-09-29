<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Reminder_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Message',
                'key'       => '{item_description}',
                'available' => [
                ],
            ],
            [
                'name'      => 'Contact Name',
                'key'       => '{contact_name}',
                'available' => [
                ],
            ],
        ];
    }
    public function format($reminder_id, $data)
    {
        $fields = [];
        $fields['{item_description}']               = $data['description'];
        $fields['{contact_name}']                  = $data['firstname'].' '.$data['lastname'];   
        $fields['{reminder_link}']                  = admin_url('reminder#'.$reminder_id);
        return hooks()->apply_filters('reminder_merge_fields', $fields, [
            'data'      => $data,
        ]);
    }
    public function name()
    {
        if (is_null($this->for)) {
            $this->for = strtolower(strbefore(get_class($this), '_merge_fields'));
        }
        return $this->for;
    }
}
