<?php defined('BASEPATH') or exit('No direct script access allowed');

use app\services\zip\Unzip;

class System extends AdminController
{
    public $returnUrl;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->library('app_module_installer');
        $this->load->library('app_modules');

        $this->returnUrl = admin_url(PERFEX_SAAS_ROUTE_NAME . '/system');
    }

    /**
     * Display index page
     */
    public function index()
    {
        die("Feature disabled");
    }


    public function save_purchase_code()
    {
        die("Feature disabled");
    }

    public function get_module($module)
    {
        die("Feature disabled");
    }

    public function after_get_module($module)
    {
        die("Feature disabled");
    }

    public function activate($module_name)
    {
        $this->app_modules->activate($module_name);
        $this->app_modules->upgrade_database($module_name);
        return redirect($this->returnUrl);
    }

    public function deactivate($module_name)
    {
        $this->app_modules->deactivate($module_name);
        return redirect($this->returnUrl);
    }
}