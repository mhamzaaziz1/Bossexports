<?php

defined('BASEPATH') or exit('No direct script access allowed');
include( __DIR__ . '/../vendor/autoload.php');
use Orhanerday\OpenAi\OpenAi;

class Client extends ClientsController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('aiwriter_model');
    }

    public function index()
    {
        if(get_option('aiwriter_allow_for_client') !='1'):
            access_denied('aiwriter');
        endif;

        if (!is_client_logged_in()) {
            if(get_option('aiwriter_allow_for_client_without_login') !='1'):
                redirect(site_url('authentication/login'));
            endif;
        }

        $data['title'] = _l('aiwriter');
        $this->view('client/writer');
        $this->data($data);
        $this->layout();
    }

    public function ajaxAiContent(){

        if(get_option('aiwriter_allow_for_client') !='1'):
            echo json_encode(['status'=>false, 'message'=>_l('something_went_wrong')]);
            exit();
        endif;

        if (!is_client_logged_in()) {
            if(get_option('aiwriter_allow_for_client_without_login') !='1'):
                echo json_encode(['status'=>false, 'message'=>_l('something_went_wrong')]);
                exit();
            endif;
        }

        $apiKey             = get_option('aiwriter_openai_api_key');
        $limitText          = get_option('aiwriter_openai_limit_text');
        $keyword            = $this->input->post('primary_keyword');
        $usage_case         = $this->input->post('usage_case');
        $numberVariant      = $this->input->post('no_of_varient');
        $usage_caseList     = $this->aiwriter_model->get_all_usage_case();
        $prompt = "Write $numberVariant " . ($usage_caseList[$usage_case] ?? '') . " About $keyword";

        $openAi = new OpenAi($apiKey);

        $result = $openAi->completion([
            'model'       => 'text-davinci-003',
            'prompt'      => $prompt,
            'max_tokens'  => (int)$limitText,
            'temperature' => 0
        ]);
        $result = json_decode($result, true);

        $text = '';
        if (array_key_exists("choices",$result)):
            foreach ($result['choices'] as $choice):
                $text .= $choice['text'];
            endforeach;
            echo json_encode(['status'=>true, 'message'=> _l('content_generated'),'data'=>$text]);
        else:
            echo json_encode(['status'=>false, 'message'=>_l('something_went_wrong')]);
        endif;

    }

    
}
