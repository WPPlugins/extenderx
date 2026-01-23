<?php

namespace WPXERO\ExtenderX\Admin;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Admin\Enqueue;
use WPXERO\ExtenderX\Admin\Menu;

defined('ABSPATH') || exit;

class Dashboard {
    use Singleton;


    public function __construct() {
        // add addmin body class
        add_filter('admin_body_class', [$this, 'extenderx_editor_custom_body_class']);
        add_action('admin_body_class', [$this, 'add_custom_body_class']);
        add_action('admin_init', [$this, 'redirect_to_welcome_screen']);
        add_filter('plugin_action_links_extenderx/extenderx.php', [$this, 'extenderx_settings_link']);
        add_action('in_admin_header', [$this, 'disable_admin_notices'], PHP_INT_MAX);
        Enqueue::instance();
        Menu::instance();
    }
    public function disable_admin_notices() {
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'toplevel_page_extenderx') {
            return;
        }
        \remove_all_actions('admin_notices');
        \remove_all_actions('all_admin_notices');
        \remove_all_actions('network_admin_notices');
?>
        <style>
            .biggopti {
                display: none !important;
            }
        </style>
<?php
    }
    /**
     * Redirect to Welcome page after activation.
     */
    public function redirect_to_welcome_screen() {
        // Bail if no activation redirect.
        if (! get_transient('_extenderx_welcome_screen_activation_redirect')) {
            return;
        }

        // Delete the redirect transient.
        delete_transient('_extenderx_welcome_screen_activation_redirect');

        // Bail if activating from network, or bulk.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (is_network_admin() || isset($_GET['activate-multi'])) {
            return;
        }

        // Redirect to welcome page.
        wp_safe_redirect(admin_url('admin.php?page=extenderx'));
    }

    public function extenderx_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=extenderx&sub_page=settings') . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     *
     * Add custom body class to admin pages for Extenderx plugin.
     *
     * @param array $classes Existing body classes.
     * @return array Modified body classes.
     */
    // add body class for extenderx
    public function add_custom_body_class($classes) {
        // Check if we are on editing screen in WordPress admin
        if (is_admin() && isset($_GET['action']) && $_GET['action'] === 'edit') { // phpcs:ignore
            $classes .= ' extenderx-admin-page';
        }
        return $classes;
    }


    // add body class for extenderx
    public function extenderx_editor_custom_body_class($classes) {
        // Check if we are on editing screen in WordPress admin
        if (is_admin() && isset($_GET['action']) && $_GET['action'] === 'edit') { // phpcs:ignore
            $classes .= ' extenderx-editor';
        }
        return $classes;
    }
}
