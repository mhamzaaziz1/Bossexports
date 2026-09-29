<?php

if ( ! function_exists('zegaware_wmm_check_license'))
{
	function zegaware_wmm_check_license()
	{
		return;
		/**
		$request_uri = $_SERVER['REQUEST_URI'];

		if (str_contains($request_uri, '/admin/'.WEBSITE_MAINTENANCE_MODULE_NAME)
		    || str_contains($request_uri, '/admin/tickets')
		)
		{
			$is_activated = Zegaware_license::is_activated(WEBSITE_MAINTENANCE_MODULE_NAME);

			if ( ! $is_activated
			     && ! str_contains($request_uri, '/admin/'.WEBSITE_MAINTENANCE_MODULE_NAME.'/license')
			)
			{
				redirect(admin_url(WEBSITE_MAINTENANCE_MODULE_NAME.'/license'));
				exit();
			}

			if ($is_activated)
			{
				$last_validate = get_option(WEBSITE_MAINTENANCE_MODULE_NAME.'_last_validate');

				if (empty($last_validate))
				{
					validate_zegaware_wmm_license();
				} else
				{
					$last_validate = json_decode($last_validate);

					if ( ! isset($last_validate->date) || $last_validate->date !== date('Y-m-d'))
					{
						validate_zegaware_wmm_license();
					}
				}
			}
		}
		*/
	}

	hooks()->add_action('admin_init', 'zegaware_wmm_check_license');
}

if ( ! function_exists('validate_zegaware_wmm_license'))
{
	function validate_zegaware_wmm_license(): bool
	{
		$validated = Zegaware_license::validate_current_license(WEBSITE_MAINTENANCE_MODULE_NAME);


		return true;
	}
}
