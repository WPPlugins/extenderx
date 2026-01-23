<?php

namespace WPXERO\ExtenderX\Admin;

use WPXERO\ExtenderX\Traits\Singleton;

defined('ABSPATH') || exit;

class Menu {
    use Singleton;
    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_head', [$this, 'render_menu_icon']);
    }

    public function register_menu() {
        $icon_url = XERO_EXTENDERX_ADMIN_URL . 'assets/img/icon.svg';
        // Add main menu
        add_menu_page(
            __('ExtenderX', 'extenderx'),
            __('ExtenderX', 'extenderx'),
            'manage_options',
            'extenderx',
            [$this, 'render_menu_page'],
            $icon_url,
            30
        );
        // add_submenu_page(
        //     'extenderx',
        //     '',
        //     esc_html__('Welcome', 'extenderx'),
        //     'manage_options',
        //     'extenderx'
        // );
        // add_submenu_page(
        //     'extenderx',
        //     '',
        //     esc_html__('Settings', 'extenderx'),
        //     'manage_options',
        //     'extenderx-settings',
        //     [$this, 'render_menu_page']
        // );
    }

    public function render_menu_page() {
        echo '<div id="extenderx-admin-page"></div>';
    }
    public function render_menu_icon() {
        $icon_url = XERO_EXTENDERX_ADMIN_URL . 'assets/img/icon.svg';
        echo '<style>
         #toplevel_page_extenderx .wp-menu-image img { display: none; }
        #toplevel_page_extenderx .wp-menu-image {
            background-repeat: no-repeat;
            background-position: center;
            background-size: 22px 22px;
            width: 22px; height: 22px;
            background-image: url("' . esc_url($icon_url) . '");
        }
        </style>';
    }
}
