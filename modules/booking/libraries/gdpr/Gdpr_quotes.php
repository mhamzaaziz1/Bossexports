<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Gdpr_quotes
{
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function export($rel_id, $rel_type)
    {
        // $readQuotesDir = '';
        // $tmpDir           = get_temp_dir();


        if (!class_exists('quotes_model')) {
            $this->ci->load->model('quotes_model');
        }

        $this->ci->db->where('rel_id', $rel_id);
        $this->ci->db->where('rel_type', $rel_type);

        $quotes = $this->ci->db->get(db_prefix().'quotes')->result_array();

        $this->ci->db->where('show_on_client_portal', 1);
        $this->ci->db->where('fieldto', 'quote');
        $this->ci->db->order_by('field_order', 'asc');
        $custom_fields = $this->ci->db->get(db_prefix().'customfields')->result_array();
        /*
            if (count($quotes) > 0) {
                $uniqueIdentifier = $tmpDir . $rel_id . time() . '-quotes';
                $readQuotesDir = $uniqueIdentifier;
            }*/
        $this->ci->load->model('currencies_model');
        foreach ($quotes as $proposaArrayKey => $quote) {

        // $quote['attachments'] = _prepare_attachments_array_for_export($this->ci->quotes_model->get_attachments($quote['id']));

            // $quotes[$proposaArrayKey] = parse_quote_content_merge_fields($quote);

            $quotes[$proposaArrayKey]['country'] = get_country($quote['country']);

            $quotes[$proposaArrayKey]['currency'] = $this->ci->currencies_model->get($quote['currency']);

            $quotes[$proposaArrayKey]['items'] = _prepare_items_array_for_export(get_items_by_type('quote', $quote['id']), 'quote');

            $quotes[$proposaArrayKey]['comments'] = $this->ci->quotes_model->get_comments($quote['id']);

            $quotes[$proposaArrayKey]['views'] = get_views_tracking('quote', $quote['id']);

            $quotes[$proposaArrayKey]['tracked_emails'] = get_tracked_emails($quote['id'], 'quote');

            $quotes[$proposaArrayKey]['additional_fields'] = [];
            foreach ($custom_fields as $cf) {
                $quotes[$proposaArrayKey]['additional_fields'][] = [
                    'name'  => $cf['name'],
                    'value' => get_custom_field_value($quote['id'], $cf['id'], 'quote'),
                ];
            }

            /*  $tmpQuotesDirName = $uniqueIdentifier;
              if (!is_dir($tmpQuotesDirName)) {
                  mkdir($tmpQuotesDirName, 0755);
              }

              $tmpQuotesDirName = $tmpQuotesDirName . '/' . $quote['id'];

              mkdir($tmpQuotesDirName, 0755);*/

/*        if (count($quote['attachments']) > 0 || !empty($quote['signature'])) {
            $attachmentsDir = $tmpQuotesDirName . '/attachments';
            mkdir($attachmentsDir, 0755);

            foreach ($quote['attachments'] as $att) {
                xcopy(get_upload_path_by_type('quote') . $quote['id'] . '/' . $att['file_name'], $attachmentsDir . '/' . $att['file_name']);
            }

            if (!empty($quote['signature'])) {
                xcopy(get_upload_path_by_type('quote') . $quote['id'] . '/' . $quote['signature'], $attachmentsDir . '/' . $quote['signature']);
            }
        }*/

        // unset($quote['id']);

        // $fp = fopen($tmpQuotesDirName . '/quote.json', 'w');
        // fwrite($fp, json_encode($quote, JSON_PRETTY_PRINT));
        // fclose($fp);
        }

        return $quotes;
    }
}
