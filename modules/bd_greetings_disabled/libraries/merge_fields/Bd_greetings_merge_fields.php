<?php defined('BASEPATH') or exit('No direct script access allowed');

class Bd_greetings_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Contact Firstname',
                'key'       => '{contact_firstname}',
                'available' => ['bd_greetings'],
            ],
			[
                'name'      => 'Contact Lastname',
                'key'       => '{contact_lastname}',
                'available' => ['bd_greetings'],
            ],
            [
                'name'      => 'Client Company',
                'key'       => '{client_company}',
                'available' => ['bd_greetings'],
            ],
        ];
    }

    /**
     * Merge field for bd_greetings
     * @param  mixed $contact_id 
     * @return array
     */
    public function format($contact_id='')
    {
        $fields = [];
		$fields['{contact_firstname}']      = '';
        $fields['{contact_lastname}']       = '';
		$fields['{client_company}']        	= '';
		
        $this->ci->db->where('id', $contact_id);
       	$contact = $this->ci->db->get(db_prefix().'contacts')->row();

        if (!$contact) {
            return $fields;
        }

        if ($contact) {
            $fields['{contact_firstname}']          = $contact->firstname;
            $fields['{contact_lastname}']           = $contact->lastname;
        }
		
		$client = $this->ci->clients_model->get($contact->userid);
		if ($client) {
			$fields['{client_company}']             = $client->company;
		}	

        return $fields;
    }
}