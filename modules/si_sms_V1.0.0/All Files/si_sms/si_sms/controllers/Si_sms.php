<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Si_sms extends AdminController 
{
	public function __construct()
	{
		parent::__construct(); 
		if (!is_admin() && !has_permission('settings', '', 'view') && !has_permission('si_sms_custom_send', '', 'create')) {
			access_denied(_l('si_sms'));
		}
	}
	function index()
	{
		redirect(admin_url('si_sms/custom_sms'));
	}
	function custom_sms()
	{
		if(!get_option(SI_SMS_MODULE_NAME.'_activated') || get_option(SI_SMS_MODULE_NAME.'_activation_code')=='')
			access_denied(_l('si_sms'));
		if (!has_permission('si_sms_custom_send', '', 'create')) {
			access_denied(_l('si_sms'));
		}
		if ($this->input->post()) {
			
			$custom_trigger_name = 'si_sms_custom_sms';
			$filter_by = $this->input->post('filter_by');
			$message = $this->input->post('sms_content');
			$contacts = array();
			if($filter_by=='customer'){
				$clients  = $this->input->post('si_clients');
				if(!empty($clients)){
					$contacts = $this->si_sms_model->get_client_contacts($clients);
				}
			}
			elseif($filter_by=='lead'){
				$leads  = $this->input->post('si_leads');
				if(!empty($leads)){
					$contacts = $this->si_sms_model->get_leads_contacts($leads);
				}
			}
			elseif($filter_by=='staff'){
				$staffs  = $this->input->post('si_staffs');
				if(!empty($staffs)){
					$contacts = $this->si_sms_model->get_staffs_contacts($staffs);
				}
			}
			try{
				if(!empty($contacts)){
					$oc_name = 'sms-trigger-' . $custom_trigger_name . '-value';
					$this->app_object_cache->add($oc_name, $message);
					update_option('sms_trigger_' . $custom_trigger_name,$message);
					foreach($contacts as $contact)
					{
						$merge_fields = ['{name}'=>$contact['name']];
						$response = $this->app_sms->trigger($custom_trigger_name, $contact['phonenumber'], $merge_fields);
					}
					update_option('sms_trigger_'.$custom_trigger_name,'');
					echo json_encode(['success' => true,'message'=> _l('si_sms_sent_message')]);
					die();
				}
				echo json_encode(['success' => false,'message'=> _l('si_sms_sent_error_message')]);
				die();
				
			}
			catch(Exception $e){
				echo json_encode(['success' => false,'message'=>$e->getMessage()]);
			}
		}
		$data['merge_fields']= '{name}';
		$data['staff_list']  = $this->staff_model->get('',['is_not_staff' => 0, 'active' => 1]);
		$data['templates'] = $this->si_sms_model->get();
		$data['title'] = _l('si_sms_custom_send_title');
		$this->load->view('custom_sms_send', $data);
	}
	
	function get_clients_leads()
	{
		if (!has_permission('si_sms_custom_send', '', 'create')) {
			 ajax_access_denied();
		}
		if ($this->input->is_ajax_request()) {
			$clients = $this->si_sms_model->get_clients();
			$leads = $this->si_sms_model->get_leads();
			echo json_encode(array('clients'=>$clients,'leads'=>$leads));
			die();
		}
	}
	
	function list_templates()
	{
		$data=array();
		$data['title']    = _l('si_sms_templates_menu');
		$data['filter_templates'] = $this->si_sms_model->get();
		$this->load->view('sms_templates_list', $data);
	}
	# get template
	public function get_template($id)
	{
		if(is_numeric($id)){
			$template              = $this->si_sms_model->get($id);
			$template->content = clear_textarea_breaks($template->content);
			echo json_encode($template);
		}
	}
	# Add or Update sms template 
	public function save_template()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$data['staff_id'] = get_staff_user_id();
				#Add template
				$id = $this->si_sms_model->add($data);
				if ($id) {
					echo json_encode([
						'success' => $id ? true : false,
						'message' => $id ? _l('added_successfully', _l('si_sms_templates')) : '',
						'id'      => $id,
						'name'    => $this->input->post('category_name'),
					]);
				}
			} else {
				$id = $data['id'];
				unset($data['id']);
				$success = $this->si_sms_model->update($id, $data);
				if ($success) {
					set_alert('success', _l('updated_successfully', _l('si_sms_templates')));
				}
			}
			redirect($_SERVER['HTTP_REFERER']);
		}
	}
	function del_sms_template($id)
	{
		$current_user_id = get_staff_user_id();
		$this->si_sms_model->delete($id,$current_user_id);
		redirect('si_sms/list_templates');
	}
	
	public function validate()
	{
		if (!is_admin() && !has_permission('settings', '', 'view')) {
			ajax_access_denied();
		}
		try{
			$purchase_key   = trim($this->input->post('purchase_key', false));
			$curl = curl_init();
			curl_setopt_array($curl, [
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_USERAGENT      => 'curl',
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_URL            => VALIDATION_URL,
				CURLOPT_POST           => 1,
				CURLOPT_POSTFIELDS     => [
					'url' => site_url(),
					'module'     => SI_SMS_KEY,
					'purchase_key'    => $purchase_key,
				],
			]);
			$result = curl_exec($curl);
			$error  = '';
			if (!$curl || !$result) {
				$error = 'Curl Error - Contact your hosting provider with the following error as reference: Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl);
			}
			$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if($code==404)
				$error = 'Server request unavailable, try after sometime.';
				
			curl_close($curl);
			if ($error != '') {
				echo json_encode([
					'success' => false,
					'message'=>$error,
				]);
				die();
			}
			echo ($result);
		}
		catch (Exception $e) {
			echo json_encode(array('success'=>false,'message'=>$e->getMessage()));
		}
	}
}