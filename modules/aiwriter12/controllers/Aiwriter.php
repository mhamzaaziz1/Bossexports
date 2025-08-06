<?php

defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: text/html; charset=utf-8');
include( __DIR__ . '/../vendor/autoload.php');
use Orhanerday\OpenAi\OpenAi;

class Aiwriter extends AdminController
{
        public function __construct()
        {
            parent::__construct();
            $this->load->model('aiwriter_model');
        }
        public function index()
        {
            if (!has_permission('spagreen', '', 'view')) {
                access_denied('spagreen');
            }

            $data['title']                 = _l('spagreen_dashboard');
            $this->load->view('dashboard', $data);
        }


    public function setting()
    {
        if (!has_permission('aiwriter', '', 'setting')) {
            access_denied('aiwriter');
        }

        //$this->load->model('epc_model');
        $data['title'] = _l('aiwriter_setting');
        $this->load->view('setting', $data);
    }

    public function writer()
    {
        if (!has_permission('aiwriter', '', 'use')) {
            access_denied('aiwriter');
        }

        //$this->load->model('epc_model');
        $data['title'] = _l('aiwriter');
        $this->load->view('writer', $data);
    }


    public function save_setting()
    {
        if((get_option('aiwriter_demo_mode') == '1') ):
            echo json_encode(['status'=>false, 'message'=>_l('change_not_allow_on_demo')]);
            exit();
        endif;
        if (!has_permission('aiwriter', '', 'setting')) {
            access_denied('aiwriter');
        }
        if($this->input->post()):
            update_option('aiwriter_openai_api_key',$this->input->post('aiwriter_openai_api_key'));
            update_option('aiwriter_openai_limit_text',$this->input->post('aiwriter_openai_limit_text'));
            update_option('aiwriter_allow_for_client',$this->input->post('aiwriter_allow_for_client'));
            update_option('aiwriter_allow_for_client_without_login',$this->input->post('aiwriter_allow_for_client_without_login'));
            update_option('aiwriter_autoreply_on_opening_ticket',$this->input->post('aiwriter_autoreply_on_opening_ticket'));
            update_option('aiwriter_replay_from_name',$this->input->post('aiwriter_replay_from_name'));
            update_option('aiwriter_autoreply_staffid',$this->input->post('aiwriter_autoreply_staffid'));
            echo json_encode(['status'=>true, 'message'=> _l('setting_updated')]);
        else:
            echo json_encode(['status'=>false, 'message'=>_l('something_went_wrong')]);
        endif;
    }

    public function ajaxAiContent(){
        if (!has_permission('aiwriter', '', 'use')) {
            access_denied('aiwriter');
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
