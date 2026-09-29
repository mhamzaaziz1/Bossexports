<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<aside id="menu" class="sidebar">
    <?php $isSidebarDark = function_exists('is_admin_sidebar_background_light') ?
            is_admin_sidebar_background_light() :
            false; ?>
    <div class="dropdown sidebar-user-profile tw-mt-[80px] tw-mx-1.5 ">
        <?php
            $roleName = '';
            if(isset($current_user->admin) && $current_user->admin == 1){
                $roleName = _l('administrator'); 
            }
            if(empty($roleName) && !empty($current_user->role)){
                 $CI =& get_instance();
                 if(!class_exists('roles_model')){
                    $CI->load->model('roles_model');
                 }
                 $role = $CI->roles_model->get($current_user->role);
                 if($role){
                     $roleName = $role->name;
                 }
            }
            if(empty($roleName)){
                $roleName = _l('staff_member');
            }
        ?>
        <a href="#"
            class="dropdown-toggle profile -tw-mt-1 tw-border tw-border-solid tw-rounded-lg tw-bg-transparent tw-text-white tw-border-white tw-py-3 tw-px-3 tw-block tw-shadow-sm hover:tw-bg-white/5 hover:tw-text-white"
            data-toggle="dropdown" aria-expanded="false" 
            style="white-space: normal; height: auto;">
            
            <div class="tw-flex tw-items-center tw-gap-x-3 tw-mb-3">
                <?= staff_profile_image($current_user->staffid, ['img', 'img-responsive', 'staff-profile-image-small', 'tw-w-12', 'tw-h-12', 'tw-rounded-md', 'tw-object-cover']); ?>
                <div class="tw-overflow-hidden">
                     <span class="tw-truncate tw-block tw-font-bold tw-text-base"><?= get_staff_full_name(); ?></span>
                     <span class="tw-truncate tw-block tw-text-xs tw-text-neutral-300"><?= $current_user->email; ?></span>
                </div>
            </div>

            <div class="tw-border-t tw-border-solid tw-border-white tw-my-2"></div>

            <div class="tw-text-sm tw-space-y-1">
                <div class="tw-block">
                    <span class="tw-text-xs tw-font-bold tw-text-neutral-300">Employee ID:</span> <span class="tw-text-xs"><?= $current_user->staffid; ?></span>
                </div>
                <div class="tw-block">
                    <span class="tw-text-xs tw-font-bold tw-text-neutral-300">Role:</span> <span class="tw-text-xs"><?= $roleName; ?></span>
                </div>
            </div>

            <div class="tw-border-t tw-border-solid tw-border-white tw-my-2"></div>

            <div class="tw-text-xs tw-space-y-1 tw-text-neutral-400">
                 <div class="tw-flex tw-justify-between tw-items-center">
                    <span>User IP: <?= $this->input->ip_address(); ?></span>
                     <i class="fa fa-copy tw-cursor-pointer hover:tw-text-white" onclick="navigator.clipboard.writeText('<?= $this->input->ip_address(); ?>').then(()=>{ alert_float('success', 'IP Address Copied'); }); event.stopPropagation();" title="Copy IP"></i>
                </div>
                <div class="tw-flex tw-justify-between tw-items-center">
                    <?php $macAddress = get_device_mac_address($this->input->ip_address()); ?>
                    <span>MAC Address: <?= $macAddress; ?></span>
                     <i class="fa fa-copy tw-cursor-pointer hover:tw-text-white" onclick="navigator.clipboard.writeText('<?= $macAddress; ?>').then(()=>{ alert_float('success', 'MAC Address Copied'); }); event.stopPropagation();" title="Copy MAC"></i>
                </div>
            </div>
        </a>
        <ul class="dropdown-menu tw-w-full">
            <li class="header-my-profile"><a
                    href="<?= admin_url('profile'); ?>"><?= _l('nav_my_profile'); ?></a>
            </li>
            <li class="header-my-timesheets"><a
                    href="<?= admin_url('staff/timesheets'); ?>"><?= _l('my_timesheets'); ?></a>
            </li>
            <li class="header-edit-profile"><a
                    href="<?= admin_url('staff/edit_profile'); ?>"><?= _l('nav_edit_profile'); ?>
                </a>
            </li>
            <?php if (! is_language_disabled()) { ?>
            <li class="dropdown-submenu pull-left header-languages">
                <a href="#"
                    tabindex="-1"><?= _l('language'); ?></a>
                <ul class="dropdown-menu dropdown-menu">
                    <li
                        class="<?= $current_user->default_language == '' ? 'active' : ''; ?>">
                        <a
                            href="<?= admin_url('staff/change_language'); ?>">
                            <?= _l('system_default_string'); ?>
                        </a>
                    </li>
                    <?php foreach ($this->app->get_available_languages() as $user_lang) { ?>
                    <li
                        class="<?= $current_user->default_language == $user_lang ? 'active' : ''; ?>">
                        <a
                            href="<?= admin_url('staff/change_language/' . $user_lang); ?>">
                            <?= e(ucfirst($user_lang)); ?>
                        </a>
                        <?php } ?>
                </ul>
            </li>
            <?php } ?>
            <li class="header-logout">
                <a href="#"
                    onclick="logout(); return false;"><?= _l('nav_logout'); ?></a>
            </li>
        </ul>
    </div>
    <ul class="nav metis-menu tw-mt-[15px]" id="side-menu">

        <?php
 hooks()->do_action('before_render_aside_menu');
?>
        <?php foreach ($sidebar_menu as $key => $item) {
            if ((isset($item['collapse']) && $item['collapse']) && count($item['children']) === 0) {
                continue;
            } ?>
        <li class="menu-item-<?= e($item['slug']); ?>"
            <?= _attributes_to_string($item['li_attributes'] ?? []); ?>>
            <a href="<?= count($item['children']) > 0 ? '#' : $item['href']; ?>"
                aria-expanded="false"
                <?= _attributes_to_string($item['href_attributes'] ?? []); ?>>
                <i
                    class="<?= e($item['icon']); ?> menu-icon"></i>
                <span class="menu-text">
                    <?= e(_l($item['name'], '', false)); ?>
                </span>
                <?php if (count($item['children']) > 0) { ?>
                <span class="fa arrow pleft5 fa-sm tw-mt-1.5"></span>
                <?php } ?>
                <?php if (isset($item['badge'], $item['badge']['value']) && ! empty($item['badge'])) {?>
                <span
                    class="badge pull-right
               <?= isset($item['badge']['type']) && $item['badge']['type'] != '' ? "bg-{$item['badge']['type']}" : 'bg-info' ?>"
                    <?= (isset($item['badge']['type']) && $item['badge']['type'] == '')
                       || isset($item['badge']['color']) ? "style='background-color: {$item['badge']['color']}'" : '' ?>>
                    <?= e($item['badge']['value']) ?>
                </span>
                <?php } ?>
            </a>
            <?php if (count($item['children']) > 0) { ?>
            <ul class="nav nav-second-level collapse" aria-expanded="false">
                <?php foreach ($item['children'] as $submenu) { ?>
                <li class="sub-menu-item-<?= e($submenu['slug']); ?>"
                    <?= _attributes_to_string($submenu['li_attributes'] ?? []); ?>>
                    <a href="<?= e($submenu['href']); ?>"
                        <?= _attributes_to_string($submenu['href_attributes'] ?? []); ?>>
                        <?php if (! empty($submenu['icon'])) { ?>
                        <i
                            class="<?= e($submenu['icon']); ?> menu-icon"></i>
                        <?php } ?>
                        <span class="sub-menu-text">
                            <?= _l($submenu['name'], '', false); ?>
                        </span>
                    </a>
                    <?php if (isset($submenu['badge'], $submenu['badge']['value']) && ! empty($submenu['badge'])) {?>
                    <span
                        class="badge pull-right
               <?= isset($submenu['badge']['type']) && $submenu['badge']['type'] != '' ? "bg-{$submenu['badge']['type']}" : 'bg-info' ?>"
                        <?= (isset($submenu['badge']['type']) && $submenu['badge']['type'] == '')
               || isset($submenu['badge']['color']) ? "style='background-color: {$submenu['badge']['color']}'" : '' ?>>
                        <?= e($submenu['badge']['value']) ?>
                    </span>
                    <?php } ?>
                </li>
                <?php } ?>
            </ul>
            <?php } ?>
        </li>
        <?php hooks()->do_action('after_render_single_aside_menu', $item); ?>
        <?php
        } ?>
        <?php if ($this->app->show_setup_menu() == true && (is_staff_member() || is_admin())) { ?>
        <li<?php if (get_option('show_setup_menu_item_only_on_hover') == 1) {
            echo ' style="display:none;"';
        } ?> id="setup-menu-item">
            <a href="#" class="open-customizer"><i class="fa fa-cog menu-icon"></i>
                <span class="menu-text">
                    <?= _l('setting_bar_heading'); ?>
                    <?php
               if ($modulesNeedsUpgrade = $this->app_modules->number_of_modules_that_require_database_upgrade()) {
                   echo '<span class="badge menu-badge !tw-bg-warning-600">' . $modulesNeedsUpgrade . '</span>';
               }
            ?>
                </span>
            </a>
            <?php } ?>
            </li>
            <?php hooks()->do_action('after_render_aside_menu'); ?>
            <?php $this->load->view('admin/projects/pinned'); ?>
    </ul>
</aside>