<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<aside id="menu" class="sidebar">
    <ul class="nav metis-menu " id="side-menu">
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
                        <span class="fa arrow pleft5"></span>
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
</ul>
</aside>