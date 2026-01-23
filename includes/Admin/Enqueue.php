<?php

namespace WPXERO\ExtenderX\Admin;

use WPXERO\ExtenderX\Traits\Singleton;

defined('ABSPATH') || exit;
/**
 * Class Enqueue
 */
class Enqueue {
    use Singleton;

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }


    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook_suffix The current admin page.
     */
    public function enqueue_scripts($hook_suffix) {
        // Only load on Extenderx admin pages
        if (strpos($hook_suffix, 'extenderx') === false) {
            return;
        }

        $admin_assets = XERO_EXTENDERX_DIR_PATH . 'dist/admin/dependencies.php';
        if (file_exists($admin_assets)) {
            $admin_assets = require $admin_assets;

            wp_enqueue_script(
                'extenderx-admin',
                XERO_EXTENDERX_DIST_URL . 'admin/admin.js',
                $admin_assets['dependencies'],
                $admin_assets['version'],
                true
            );

            // Get current settings
            $settings = get_option('extenderx_settings', array());

            // Ensure we have the complete settings structure
            $default_settings = [];

            $complete_settings = array_merge($default_settings, $settings);

            global $wpdb;
            $theme = wp_get_theme();

            $system_info = [
                'wordpress_environment' => [
                    'wordpress_version' => get_bloginfo('version'),
                    'multisite' => is_multisite() ? 'Yes' : 'No',
                    'language' => get_locale(),
                    'timezone' => wp_timezone_string(),
                ],
                'server_environment' => [
                    'php_version' => phpversion(),
                    'mysql_version' => $wpdb->db_version(),
                    'web_server' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
                    'max_execution_time' => ini_get('max_execution_time') . 's',
                    'memory_limit' => ini_get('memory_limit'),
                ],
                'theme_information' => [
                    'active_theme' => $theme->get('Name'),
                    'version' => $theme->get('Version'),
                    'author' => $theme->get('Author'),
                ],
                'extenderx_plugin' => [
                    'version' => XERO_EXTENDERX_VERSION,
                    'active_installation' => is_plugin_active('extenderX/extenderx.php') ? 'Yes' : 'No',
                ],
                'active_plugins' => $this->get_active_plugins_info(),
                'debug' => [
                    'wp_debug' => defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled',
                    'script_debug' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? 'Enabled' : 'Disabled',
                    'wp_debug_log' => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? 'Enabled' : 'Disabled',
                ]
            ];

            wp_localize_script(
                'extenderx-admin',
                'extenderxAdminData',
                [
                    'settings' => $complete_settings,
                    'version' => XERO_EXTENDERX_VERSION,
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('extenderx-admin-nonce'),
                    'rest_url' => rest_url('wpxero/extenderx/v1'),
                    'system_info' => $system_info,
                ]
            );

            wp_enqueue_style(
                'extenderx-admin',
                XERO_EXTENDERX_DIST_URL . 'admin/style-admin.css',
                $admin_assets['version']
            );
        }
    }
    /**
     * Get active plugins information
     *
     * @return array Array of active plugins with their details
     */
    private function get_active_plugins_info() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins', array());
        $active_plugins_info = [];

        foreach ($active_plugins as $plugin_path) {
            if (isset($all_plugins[$plugin_path])) {
                $plugin = $all_plugins[$plugin_path];
                $active_plugins_info[] = [
                    'name' => $plugin['Name'],
                    'version' => $plugin['Version'],
                ];
            }
        }

        return $active_plugins_info;
    }
}
