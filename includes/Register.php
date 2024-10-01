<?php

declare(strict_types=1);

namespace XERO\EXTENDERX;

defined('ABSPATH') || exit;


/**
 * Creates a new instance of the Register class
 */
class Register {

    /**
     * The single class instance.
     *
     * @var $instance
     */
    public function add_extensions() {

        $extensions_dir = plugin_dir_path(__DIR__) . 'dist/extensions/index.js'; // Get the extensions directory path
        $extension_asset_path = plugin_dir_path(__DIR__) . 'dist/extensions/index.asset.php';
        // Check if the required file exists for this extension
        if (file_exists($extension_asset_path)) {
            $args = require $extension_asset_path;
            // check if the style file exists
            if (file_exists($extensions_dir)) {
                // Enqueue style
                wp_enqueue_script(
                    '@extenderx/extensions/index',
                    XERO_EXTENDERX_ADMIN_URL . 'dist/extensions/index.js',
                    $args['dependencies'],
                    $args['version'],
                    'all'
                );
                // Enqueue style
                wp_enqueue_style(
                    '@extenderx/extensions/style',
                    XERO_EXTENDERX_ADMIN_URL . 'dist/extensions/editor.css',
                    [],
                    $args['version'],
                    'all'
                );
            }
        }
    }

    /**
     * The single class instance.
     *
     * @var $instance
     */
    public function add_frontend_assets() {

        $extensions_dir = plugin_dir_path(__DIR__) . 'dist/extensions/'; // Get the extensions directory path

        $extensions = glob($extensions_dir . '*', GLOB_ONLYDIR); // Get all subdirectories
        foreach ($extensions as $extension_dir) {
            $extension_name = basename($extension_dir); // Get the extension name from the directory path
            $extension_asset_path = $extension_dir . '/view.asset.php';
            // Check if the required file exists for this extension
            if (file_exists($extension_asset_path)) {
                $args = require $extension_asset_path;

                // check if the style file exists
                if (file_exists($extension_dir . '/view.js')) {
                    // Enqueue style
                    wp_register_script(
                        '@extenderx/extensions/' . $extension_name . '' . '/script',
                        XERO_EXTENDERX_ADMIN_URL . 'dist/extensions/' . $extension_name . '/view.js',
                        $args['dependencies'],
                        $args['version'],
                        true
                    );
                }
            }
            // check if the style file exists
            if (file_exists($extension_dir . '/view.css')) {
                // Enqueue style
                wp_register_style(
                    '@extenderx/extensions/' . $extension_name . '' . '/style',
                    XERO_EXTENDERX_ADMIN_URL . 'dist/extensions/' . $extension_name . '/view.css',
                    [],
                    XERO_EXTENDERX_VERSION,
                    'all'
                );
            }
        }
    }
}
