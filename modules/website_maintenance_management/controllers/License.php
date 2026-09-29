<?php

defined('BASEPATH') or exit('No direct script access allowed');

class License extends AdminController {

	protected const BASE_PATH = '/license';

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		if ($this->input->post())
		{
			$posted_data = $this->input->post();
			$type        = $posted_data['license_type'] ?? '';

			switch ($type)
			{
				case 'remove':
					Zegaware_License::remove_license(WEBSITE_MAINTENANCE_MODULE_NAME);

					set_alert('success', _l('zegaware_removed_success'));
					redirect(admin_url(WEBSITE_MAINTENANCE_MODULE_NAME.self::BASE_PATH.'/index?remove=1'));
					exit();

				case 'active':
					Zegaware_License::activate_license(WEBSITE_MAINTENANCE_MODULE_NAME, $posted_data);

					redirect(admin_url(WEBSITE_MAINTENANCE_MODULE_NAME.self::BASE_PATH.'/index?active=1'));
					exit();
			}
		}

		$data = [
			'title'          => _l('wmm_zegaware_license_title'),
			'module_slug'    => WEBSITE_MAINTENANCE_MODULE_NAME,
			'is_activated'   => Zegaware_License::is_activated(WEBSITE_MAINTENANCE_MODULE_NAME),
			'activated_date' => Zegaware_License::get_activated_date(WEBSITE_MAINTENANCE_MODULE_NAME),
			'license_name'   => get_option(WEBSITE_MAINTENANCE_MODULE_NAME.'_customer_name'),
			'license_email'  => get_option(WEBSITE_MAINTENANCE_MODULE_NAME.'_customer_email'),
			'license_key'    => Zegaware_License::get_license_key(WEBSITE_MAINTENANCE_MODULE_NAME),
		];

		$this->load->view('license/index', $data);
	}

}
