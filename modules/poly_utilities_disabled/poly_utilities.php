<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: PolyUtilities
Description: Integrated utility features have been added to Perfex CRM to enhance operations and optimize workflow. These include a widget area for displaying custom content, customizable table list titles, a tool for creating quick-access menus, a search sidebar menu, a collapsible menu, comprehensive support, custom/embedded JavaScript/CSS, and additional operational functions.
Version: 1.0.7
Requires at least: 3.0.0
Author: PolyXGO
Author URI: https://codecanyon.net/user/polyxgo
*/

define('POLY_UTILITIES_MODULE_NAME', 'poly_utilities');
define('POLY_UTILITIES_MODULE_FOLDER', module_dir_path(POLY_UTILITIES_MODULE_NAME));
define('POLY_UTILITIES_MODULE_UPLOAD_FOLDER', module_dir_path(POLY_UTILITIES_MODULE_NAME, 'uploads'));
define('POLY_UTILITIES_SETTINGS', 'poly_utilities_settings');
define('POLY_WIDGETS', 'poly_utilities_widgets');
define('POLY_QUICK_ACCESS_MENU', 'poly_utilities_global_quick_access_menu');
define('POLY_SUPPORTS', 'poly_utilities_global_supports');
define('POLY_SCRIPTS', 'poly_utilities_global_scripts');
define('POLY_TABLE_FILTERS', 'poly_utilities_table_filters');
define('POLY_STYLES', 'poly_utilities_global_styles');

class POLYUTILITIES
{
    private $CI;
    private $poly_utilities_settings;
    private $quick_access_menu;
    public function __construct()
    {
        $this->CI = &get_instance();
        $this->poly_utilities_settings = clear_textarea_breaks(get_option(POLY_UTILITIES_SETTINGS));

        if ($this->poly_utilities_settings) {
            $this->poly_utilities_settings = json_decode($this->poly_utilities_settings);

            $dataFilters = get_option(POLY_TABLE_FILTERS);
            if (!empty($dataFilters)) {
                $this->poly_utilities_settings->data_filters = json_decode($dataFilters, true);
            }
        } else {
            $this->poly_utilities_settings = new stdClass();
        }

        $this->quick_access_menu = clear_textarea_breaks(get_option(POLY_QUICK_ACCESS_MENU));

        register_activation_hook(POLY_UTILITIES_MODULE_NAME, array($this, 'poly_utilities_module_activation_hook'));

        hooks()->add_action('admin_init', [$this, 'poly_utilities_common']);/* language, define */

        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_widget');
        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_common');
        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_ajax_response');

        hooks()->add_action('admin_init', [$this, 'poly_utilities_module_init_menu_items']);
        hooks()->add_action('admin_init', [$this, 'poly_utilities_permissions']);


        hooks()->add_action('app_admin_head', [$this, 'assets_head'], 1);
        hooks()->add_action('app_admin_footer', [$this, 'assets_footer'], 1);

        hooks()->add_action('app_admin_head', [$this, 'poly_utilities_scripts_styles_admin_header'], 1);

        /**
         * Admin | Customers | Both => scripts, styles.
         */
        hooks()->add_action('app_customers_head', [$this, 'poly_utilities_admin_head']);

        hooks()->add_action('app_admin_footer', [$this, 'poly_utilities_scripts_styles_admin_footer']);
        hooks()->add_action('app_customers_footer', [$this, 'poly_utilities_scripts_styles_customers_footer']);

        /**
         * Admin login form
         */
        hooks()->add_action('app_admin_authentication_head', [$this, 'poly_utilities_hook_widgets_clients']);

        /**
         * Register language files, must be registered if the module is using languages
         */
        register_language_files(POLY_UTILITIES_MODULE_NAME, [POLY_UTILITIES_MODULE_NAME]);
    }

    public function poly_utilities_hook_widgets_clients()
    {
        $this->poly_utilities_styles_customers();
        echo '<script src="' . site_url() . '/assets/plugins/jquery/jquery.min.js"></script>';

        $this->poly_utilities_settings_scripts(true);

        $this->poly_utilities_scripts_customers_public_head();
        $this->poly_utilities_scripts_customers_public_scripts();
    }

    public function poly_utilities_settings_scripts($is_widget = false)
    {
?>
        <script>
            var poly_utilities_settings = <?php echo (!empty($this->poly_utilities_settings) ? json_encode($this->poly_utilities_settings) : []) ?>;
            <?php
            if ($is_widget == true) {
            ?>
                poly_utilities_settings.widgets = <?php echo $this->widgets_generate_content_poly_utilities(true) ?>;
                poly_utilities_settings.widgets_hook = <?php echo $this->widgets_generate_content_poly_utilities() ?>;
            <?php
            }
            ?>
        </script>
        <?php
    }

    public function poly_utilities_admin_head()
    {
        $this->poly_utilities_settings_scripts();
        $this->poly_utilities_settings('customers');
        $this->poly_utilities_scripts_customers_public_head();
    }

    public function poly_utilities_scripts_customers_public_head()
    {
        echo '<script src="' . base_url('modules/poly_utilities/assets/js/public/head.js') . '"></script>';
    }

    public function poly_utilities_scripts_customers_public_scripts()
    {
        echo '<script src="' . base_url('modules/poly_utilities/assets/js/public/script.js') . '"></script>';
    }

    public function poly_utilities_module_activation_hook()
    {
        require_once(__DIR__ . '/install.php');
    }

    /**
     * Enqueues scripts and styles common Admin & Clients.
     * @return void
     */
    public function poly_utilities_settings($mode)
    {
        $poly_utilities_aio_supports = clear_textarea_breaks(get_option(POLY_SUPPORTS));
        $poly_utilities_aio_supports = !empty($poly_utilities_aio_supports) ? json_decode($poly_utilities_aio_supports, true) : [];
        if (($poly_utilities_aio_supports && $poly_utilities_aio_supports['is_admin'] === 'true' && $mode == 'admin') || ($poly_utilities_aio_supports && $poly_utilities_aio_supports['is_clients'] === 'true' && $mode == 'customers')) {
        ?>
            <script>
                poly_utilities_settings.aio_supports = <?php echo json_encode($poly_utilities_aio_supports), false ?>; //Widgets
                poly_utilities_settings.widgets = <?php echo $this->widgets_generate_content_poly_utilities(true) ?>;
                poly_utilities_settings.widgets_hook = <?php echo $this->widgets_generate_content_poly_utilities() ?>;
            </script>
        <?php
        }
    }

    public function poly_utilities_scripts_styles_admin_header()
    {
        $this->poly_utilities_settings('admin');
        $this->poly_utilities_resource_css_files('admin', 'header');
        $this->poly_utilities_resource_js_files('admin', 'header');
    }

    /**
     * Enqueues scripts and styles for Admin (Footer).
     * @return void
     */
    public function poly_utilities_scripts_styles_admin_footer()
    {
        $this->poly_utilities_resource_css_files('admin', 'footer');
        $this->poly_utilities_resource_js_files('admin', 'footer');
    }

    /**
     * Enqueues scripts and styles for Clients (Footer)).
     * @return void
     */
    public function poly_utilities_scripts_styles_customers_footer()
    {
        $this->poly_utilities_resource_css_files('customers', 'footer');
        $this->poly_utilities_resource_js_files('customers');

        $this->poly_utilities_styles_customers();

        $this->poly_utilities_js_library();

        $this->poly_utilities_scripts_customers();
    }

    public function poly_utilities_js_library()
    {
        echo '<script src="' . base_url('modules/poly_utilities/assets/js/lib/sweetalert2/11.7.31/sweetalert2.min.js') . '"></script>';
        echo '<script src="' . base_url('modules/poly_utilities/assets/js/lib/clipboardjs/2.0.11/clipboard.min.js') . '"></script>';
    }

    public function poly_utilities_styles_customers()
    {
        echo '<link rel="stylesheet" href="' . base_url('modules/poly_utilities/assets/css/public/style.css') . '"/>';
    }
    public function poly_utilities_scripts_customers()
    {
        echo '<script src="' . base_url('modules/poly_utilities/assets/js/public/script.js') . '"></script>';
    }

    /**
     * Enqueues JavaScript files based on the specified mode area.
     * @param string $mode_area The area mode to load scripts for. Value: admin or customers.
     * @return void
     */
    public function poly_utilities_resource_js_files($mode_area, $position = 'footer')
    {
        if (property_exists($this->poly_utilities_settings, 'is_active_scripts') && $this->poly_utilities_settings->is_active_scripts !== 'true') return;

        $obj_storage = clear_textarea_breaks(get_option(POLY_SCRIPTS));
        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage);
            foreach ($obj_old_data as $resource) {
                if ($resource->mode === $mode_area || $resource->mode === 'admin_customers') {
                    if ($resource->is_embed_position === $position) {
                        if ($resource->is_embed === 'true') {
                            echo poly_utilities_common_helper::readFile($resource->file . '.js', POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/js');
                        } else {
                            echo '<script src="' . base_url('modules/poly_utilities/uploads/js/' . $resource->file) . '.js' . poly_utilities_common_helper::getParamDoNotCacheFile() . '"></script>';
                        }
                    }
                }
            }
        }
    }

    /**
     * Enqueues Cascading Style Sheet files based on the specified mode area.
     * @param string $mode_area The area mode to load scripts for. Value: admin or customers.
     * @return void
     */
    public function poly_utilities_resource_css_files($mode_area, $position = 'header')
    {
        if (property_exists($this->poly_utilities_settings, 'is_active_styles') && $this->poly_utilities_settings->is_active_styles !== 'true') return;

        $obj_storage = clear_textarea_breaks(get_option(POLY_STYLES));
        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage);
            foreach ($obj_old_data as $resource) {
                if ($resource->mode === $mode_area || $resource->mode === 'admin_customers') {
                    if ($resource->is_embed_position === $position) {
                        if ($resource->is_embed === 'true') {
                            echo poly_utilities_common_helper::readFile($resource->file . '.css', POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/css');
                        } else {
                            echo '<link rel="stylesheet" href="' . base_url('modules/poly_utilities/uploads/css/' . $resource->file) . '.css' . poly_utilities_common_helper::getParamDoNotCacheFile() . '"/>';
                        }
                    }
                }
            }
        }
    }

    /**
     * Load CSS/JS assets in the head
     * @return void
     */
    public function assets_head()
    {
        // Handle for data filter
        if ($this->CI->session->staff_user_id) {
            $this->poly_utilities_settings->uid = $this->CI->session->staff_user_id;
            $this->poly_utilities_settings->segments = $this->CI->uri->segments;
            $this->poly_utilities_settings->tab = $this->CI->load->_ci_cached_vars['tab'];
        }

        echo '<link rel="stylesheet" href="' . base_url('modules/poly_utilities/assets/css/admin/style.css') . '"/>';
        echo '<link rel="stylesheet" href="' . base_url('modules/poly_utilities/assets/css/public/style.css') . '"/>';
        ?>
        <script>
            <?php
            $confirmPopup = _l('poly_utilities_delete_object');
            if ($confirmPopup === 'poly_utilities_delete_object') {
                $decodedPopup = '{}';
            } else {
                $decodedPopup = html_entity_decode($confirmPopup);
            }

            $modalObjects = _l('poly_utilities_modals');
            if ($modalObjects === 'poly_utilities_modals') {
                $decodedModals = '[]';
            } else {
                $decodedModals = html_entity_decode($modalObjects);
            }

            $tableOfContents = _l('poly_utilities_table_of_contents_header');
            $tableOfContents = ($tableOfContents === 'poly_utilities_table_of_contents_header') ? '' : html_entity_decode($tableOfContents);

            $favicon = get_option('favicon');
            $favicon_path = (!empty($favicon)) ? base_url('uploads/company/' . $favicon) : '';
            ?>
            var poly_utilities_settings = <?php echo (!empty($this->poly_utilities_settings) ? json_encode($this->poly_utilities_settings) : []) ?>;
            var poly_quick_access_menu = <?php echo (!empty($this->quick_access_menu) ? $this->quick_access_menu : []) ?>;

            poly_utilities_settings.favicon_path = '<?php echo $favicon_path ?>';
            poly_utilities_settings.popup_delete = <?php echo $decodedPopup ?>;
            poly_utilities_settings.modals = <?php echo $decodedModals ?>;
            poly_utilities_settings.table_of_content_header = '<?php echo $tableOfContents ?>';

            poly_utilities_settings.alphabet = <?php echo json_encode(poly_utilities_common_helper::$alphabet, true) ?>;
            poly_utilities_settings.numbers = <?php echo json_encode(poly_utilities_common_helper::$numbers, true) ?>;
            poly_utilities_settings.targets = <?php echo json_encode(poly_utilities_common_helper::$targets, true) ?>;
            poly_utilities_settings.rels = <?php echo json_encode(poly_utilities_common_helper::$rels, true) ?>;

            //Widgets
            poly_utilities_settings.widgets = <?php echo $this->widgets_generate_content_poly_utilities(true) ?>;
            poly_utilities_settings.widgets_hook = <?php echo $this->widgets_generate_content_poly_utilities() ?>;
        </script>

    <?php
        echo '<script src="' . base_url('modules/poly_utilities/assets/js/public/head.js') . '"></script>';
    }

    public function widgets_generate_content_poly_utilities($is_default = false)
    {
        $widget_objects = json_decode(clear_textarea_breaks(get_option(POLY_WIDGETS)));

        $result = [];
        if ($is_default == true) {
            foreach ($widget_objects as $value) {
                if ($value->default == 'true') {
                    $result[] = $value;
                }
            }
        } else {
            foreach ($widget_objects as $value) {
                if ($value->default == 'false') {
                    $result[] = $value;
                }
            }
        }

        $objs = [];
        foreach ($result as $value) {
            $content = [];
            foreach ($value->widgets as $item) {
                if ($item->roles[0]) {
                    if ($item->roles[0]->name === 'active' && $item->roles[0]->value === 'true') {
                        foreach ($item->fields as $item2) {
                            if ($item2->name == 'description') {
                                $content[] = $item2->value;
                            }
                        }
                    }
                }
                $objs[$value->id] = $content;
            }
        }
        return json_encode($objs, true);
    }

    /**
     * Load CSS/JS assets in the footer
     * @return void
     */
    public function assets_footer()
    {
        $this->poly_utilities_js_library();

        echo '<script src="' . base_url('modules/poly_utilities/assets/js/public/script.js') . '"></script>';
        echo '<script src="' . base_url('modules/poly_utilities/assets/js/admin/script.js') . '"></script>';
    }

    /**
     * Render the quick access menu into the main menu bar.
     * @return void
     */
    public function before_render_aside_menu_poly_utilities()
    {
        if ($this->poly_utilities_settings->is_quick_access_menu !== 'true') return;
        $obj_storage = clear_textarea_breaks(get_option(POLY_QUICK_ACCESS_MENU));
        $obj_old_data = [];
    ?>
        <div id="poly_utilities_quick_access_menu" class="poly-absolute poly-hide">
            <div class="poly_utilities_quick_access_menu">
                <span class="menu-items" data-toggle="dropdown"><i class="fas fa-bars"></i></span>
                <ul class="dropdown-menu dropdown-menu-right animated fadeIn tw-text-base">
                    <li class="dropdown-header tw-mb-1">Quick Access Menu</li>
                    <?php
                    if (!empty($obj_storage)) {
                        $obj_old_data = json_decode($obj_storage);
                        foreach ($obj_old_data as $key => $item) {
                            $icon = $item->icon ? $item->icon : 'fas fa-link';
                    ?>
                            <li>
                                <a href="<?php echo $item->link ?>" target="<?php echo (!empty($item->target) ? $item->target : '_self') ?>" rel="<?php echo (!empty($item->rel) ? $item->rel : 'nofollow') ?>" class="tw-group tw-inline-flex tw-space-x-0.5 tw-text-neutral-700">
                                    <i class="<?php echo $icon ?>"></i>&nbsp;<span><?php echo $item->title . ($item->shortcut_key ? "&nbsp;<span class='poly-quick-access-shortcut-key pull-right' data-toggle='tooltip' data-title='Shortcut key'>{$item->shortcut_key}</span>" : '') ?></span>
                                </a>
                            </li>
                        <?php
                        }
                    }
                    if (has_permission('poly_utilities_shortcut_menu_extend', '', 'create')) {
                        ?>
                        <li>
                            <hr class="hr" />
                            <a href="<?php echo admin_url('poly_utilities/quick_access') ?>"><i class="fas fa-plus"></i>&nbsp;<?php echo _l('poly_utilities_quick_access_menu_mini_add') ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
<?php
    }

    /**
     * Init goals module menu items in setup in app_init hook
     * @return void
     */
    public function poly_utilities_module_init_menu_items()
    {
        // ==== Menu sidebar ==== //
        hooks()->add_action('admin_navbar_start', [$this, 'before_render_aside_menu_poly_utilities'], 10);

        if (has_permission('poly_utilities', '', 'view')) {
            $this->CI->app_menu->add_sidebar_menu_item('poly_utilities', [
                'name'     => _l('poly_utilities_name'),
                'icon'     => 'fas fa-user-clock',
                'position' => 3,
            ]);
        }

        // ==== Quick Access Menu ==== //
        if (has_permission('poly_utilities_shortcut_menu_extend', '', 'view') || has_permission('poly_utilities_shortcut_menu_extend', '', 'edit') || has_permission('poly_utilities_shortcut_menu_extend', '', 'create') || has_permission('poly_utilities_shortcut_menu_extend', '', 'delete')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_shortcut_menu_extend',
                'name'     => _l('poly_utilities_shortcut_menu_extend'),
                'icon'     => 'fa-solid fa-list-check',
                'href'     => admin_url('poly_utilities/quick_access'),
                'position' => 1,
            ]);
        }

        // ==== Custom Menu ==== //
        if (has_permission('poly_utilities_custom_menu_extend', '', 'view') || has_permission('poly_utilities_custom_menu_extend', '', 'edit') || has_permission('poly_utilities_custom_menu_extend', '', 'create') || has_permission('poly_utilities_custom_menu_extend', '', 'delete')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_custom_menu_extend',
                'name'     => _l('poly_utilities_custom_menu_extend'),
                'icon'     => 'fa-solid fa-list-ul fa-fw',
                'href'     => admin_url('poly_utilities/custom_menu'),
                'position' => 2,
            ]);
        }

        // ==== Widgets ==== //
        if (has_permission('poly_utilities_widgets_extend', '', 'view') || has_permission('poly_utilities_widgets_extend', '', 'edit') || has_permission('poly_utilities_widgets_extend', '', 'create') || has_permission('poly_utilities_widgets_extend', '', 'delete')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_widgets_extend',
                'name'     => _l('poly_utilities_widgets_extend'),
                'icon'     => 'fa-solid fa-palette fa-fw',
                'href'     => admin_url('poly_utilities/widgets'),
                'position' => 3,
            ]);
        }

        // ==== Scripts ==== //
        if (has_permission('poly_utilities_scripts_extend', '', 'view') || has_permission('poly_utilities_scripts_extend', '', 'delete')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_scripts_extend',
                'name'     => _l('poly_utilities_scripts_extend'),
                'icon'     => 'fas fa-file-code',
                'href'     => admin_url('poly_utilities/scripts'),
                'position' => 4,
            ]);
        }

        if (has_permission('poly_utilities_scripts_extend', '', 'view') || has_permission('poly_utilities_scripts_extend', '', 'create') || has_permission('poly_utilities_scripts_extend', '', 'edit')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_scripts_add_extend',
                'name'     => _l('poly_utilities_scripts_extend'),
                'icon'     => 'fas fa-file-code',
                'href'     => admin_url('poly_utilities/scripts_add'),
                'position' => 5,
            ]);
        }

        // ==== Styles ==== //
        if (has_permission('poly_utilities_styles_extend', '', 'view') || has_permission('poly_utilities_styles_extend', '', 'delete')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_styles_extend',
                'name'     => _l('poly_utilities_styles_extend'),
                'icon'     => 'fas fa-file-alt',
                'href'     => admin_url('poly_utilities/styles'),
                'position' => 6,
            ]);
        }
        if (has_permission('poly_utilities_styles_extend', '', 'view') || has_permission('poly_utilities_styles_extend', '', 'create') || has_permission('poly_utilities_styles_extend', '', 'edit')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_styles_add_extend',
                'name'     => _l('poly_utilities_styles_extend'),
                'icon'     => 'fas fa-file-alt',
                'href'     => admin_url('poly_utilities/styles_add'),
                'position' => 7,
            ]);
        }

        // ==== Support ==== //
        if (has_permission('poly_utilities_supports', '', 'view') || has_permission('poly_utilities_supports', '', 'edit') || has_permission('poly_utilities_supports', '', 'create') || has_permission('poly_utilities_supports', '', 'delete')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_supports',
                'name'     => _l('poly_utilities_support'),
                'icon'     => 'fa-solid fa-headset',
                'href'     => admin_url('poly_utilities/support'),
                'position' => 8,
            ]);
        }

        // ==== Settings ==== //
        if (has_permission('poly_utilities_settings', '', 'view') || has_permission('poly_utilities_settings', '', 'edit')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_settings',
                'name'     => _l('poly_utilities_settings'),
                'icon'     => 'fa fa-cog',
                'href'     => admin_url('poly_utilities/settings'),
                'position' => 9,
            ]);
        }
    }

    public function poly_utilities_common()
    {
        poly_utilities_widget_helper::init();
    }

    /**
     * Init module permissions in setup in admin_init hook
     * @return void
     */
    public function poly_utilities_permissions()
    {
        // ==== PolyUtilities ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view')
        ];
        register_staff_capabilities('poly_utilities', $capabilities, _l('poly_utilities'));

        // ==== JavaScripts ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ];
        register_staff_capabilities('poly_utilities_scripts_extend', $capabilities, _l('poly_utilities_scripts_extend') . ' (' . _l('poly_utilities') . ')');

        // ==== Custom menu ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ];
        register_staff_capabilities('poly_utilities_custom_menu_extend', $capabilities, _l('poly_utilities_custom_menu_extend') . ' (' . _l('poly_utilities') . ')');

        // ==== Widgets ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ];
        register_staff_capabilities('poly_utilities_widgets_extend', $capabilities, _l('poly_utilities_widgets_extend') . ' (' . _l('poly_utilities') . ')');


        // ==== Styles ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ];
        register_staff_capabilities('poly_utilities_styles_extend', $capabilities, _l('poly_utilities_styles_extend') . ' (' . _l('poly_utilities') . ')');

        // ==== Quick Access Menu ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ];
        register_staff_capabilities('poly_utilities_shortcut_menu_extend', $capabilities, _l('poly_utilities_shortcut_menu_extend') . ' (' . _l('poly_utilities') . ')');

        // ==== AIO Supports ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
            'edit'   => _l('permission_edit'),
            'create'   => _l('permission_create'),
            'delete' => _l('permission_delete'),
        ];
        register_staff_capabilities('poly_utilities_supports', $capabilities, _l('poly_utilities_support') . ' (' . _l('poly_utilities') . ')');

        // ==== Settings ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
            'edit'   => _l('permission_edit'),
        ];
        register_staff_capabilities('poly_utilities_settings', $capabilities, _l('poly_utilities_settings') . ' (' . _l('poly_utilities') . ')');
    }
}
new POLYUTILITIES();
?>