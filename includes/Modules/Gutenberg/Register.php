<?php

namespace WPXERO\ExtenderX\Modules\Gutenberg;

defined('ABSPATH') || exit;

/**
 * Handles registration of Gutenberg extensions assets
 */
class Register {

    /**
     * Extension key mapping for settings lookup
     *
     * @var array
     */
    private $extension_key_map = [
        'entrance'    => 'entrance_effects',
        'floating'    => 'floating_effects',
        'tilt'        => 'tilt_effects',
        'tooltip'     => 'tooltip',
        'visibility'  => 'visibility_rules',
        'wrapperlink' => 'wrapper_link',
    ];

    /**
     * Get plugin settings
     *
     * @return array
     */
    private function get_settings() {
        return get_option('extenderx_settings', []);
    }

    /**
     * Check if Gutenberg support is enabled
     *
     * @param array $settings Plugin settings
     * @return bool
     */
    private function is_gutenberg_enabled($settings) {
        return !empty($settings['builder_support']['gutenberg']['enabled']);
    }

    /**
     * Get Gutenberg settings for localization
     *
     * @param array $settings Plugin settings
     * @return array
     */
    private function get_gutenberg_settings($settings) {
        return $settings['builder_support']['gutenberg'] ?? [
            'enabled'    => false,
            'extensions' => []
        ];
    }

    /**
     * Check if a specific extension is enabled
     *
     * @param array  $settings       Plugin settings
     * @param string $extension_name Extension name
     * @return bool
     */
    private function is_extension_enabled($settings, $extension_name) {
        $extension_key = $this->extension_key_map[$extension_name] ?? $extension_name;
        return !empty($settings['builder_support']['gutenberg']['extensions'][$extension_key]);
    }

    /**
     * Register and enqueue editor extensions
     *
     * @return void
     */
    public function add_extensions() {
        $settings = $this->get_settings();

        if (!$this->is_gutenberg_enabled($settings)) {
            return;
        }

        $this->enqueue_editor_scripts($settings);
    }

    /**
     * Enqueue editor scripts and styles
     *
     * @param array $settings Plugin settings
     * @return void
     */
    private function enqueue_editor_scripts($settings) {
        $script_path = XERO_EXTENDERX_DIR_PATH . 'dist/extensions/editor.js';
        $asset_path  = XERO_EXTENDERX_DIR_PATH . 'dist/extensions/editor.asset.php';

        if (!file_exists($asset_path) || !file_exists($script_path)) {
            return;
        }

        $args = require $asset_path;

        // Enqueue main extension script
        wp_enqueue_script(
            '@extenderx/extensions/editor',
            XERO_EXTENDERX_DIST_URL . 'extensions/editor.js',
            $args['dependencies'],
            $args['version'],
            true
        );

        // Localize script with settings
        wp_localize_script(
            '@extenderx/extensions/editor',
            'extenderxEditorSettings',
            [
                'gutenberg' => $this->get_gutenberg_settings($settings)
            ]
        );

        // Enqueue editor styles
        wp_enqueue_style(
            '@extenderx/extensions/editor',
            XERO_EXTENDERX_DIST_URL . 'extensions/style-editor.css',
            [],
            $args['version']
        );
    }

    /**
     * Register frontend assets for extensions
     *
     * @return void
     */
    public function add_frontend_assets() {
        $settings = $this->get_settings();

        if (!$this->is_gutenberg_enabled($settings)) {
            return;
        }

        $extensions_dir = XERO_EXTENDERX_DIR_PATH . 'dist/extensions/';
        $extensions     = glob($extensions_dir . '*', GLOB_ONLYDIR);

        foreach ($extensions as $extension_dir) {
            $extension_name = basename($extension_dir);

            if (!$this->is_extension_enabled($settings, $extension_name)) {
                continue;
            }

            $this->register_extension_assets($extension_dir, $extension_name);
        }
    }

    /**
     * Register assets for a single extension
     *
     * @param string $extension_dir  Extension directory path
     * @param string $extension_name Extension name
     * @return void
     */
    private function register_extension_assets($extension_dir, $extension_name) {
        $this->register_extension_script($extension_dir, $extension_name);
        $this->register_extension_style($extension_dir, $extension_name);
    }

    /**
     * Register extension script
     *
     * @param string $extension_dir  Extension directory path
     * @param string $extension_name Extension name
     * @return void
     */
    private function register_extension_script($extension_dir, $extension_name) {
        $script_path = $extension_dir . '/view.js';
        $asset_path  = $extension_dir . '/view.asset.php';

        if (!file_exists($script_path) || !file_exists($asset_path)) {
            return;
        }

        $args = require $asset_path;

        wp_register_script(
            "@extenderx/extensions/{$extension_name}/script",
            XERO_EXTENDERX_DIST_URL . "extensions/{$extension_name}/view.js",
            $args['dependencies'],
            $args['version'],
            true
        );
    }

    /**
     * Register extension style
     *
     * @param string $extension_dir  Extension directory path
     * @param string $extension_name Extension name
     * @return void
     */
    private function register_extension_style($extension_dir, $extension_name) {
        $style_path = $extension_dir . '/view.css';

        if (!file_exists($style_path)) {
            return;
        }

        wp_register_style(
            "@extenderx/extensions/{$extension_name}/style",
            XERO_EXTENDERX_DIST_URL . "extensions/{$extension_name}/view.css",
            [],
            XERO_EXTENDERX_VERSION
        );
    }
}
