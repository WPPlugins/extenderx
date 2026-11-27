<?php

namespace WPXERO\ExtenderX\Modules\Gutenberg;

use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\Settings;
use WPXERO\ExtenderX\Core\ProFeatures;

defined( 'ABSPATH' ) || exit;

/**
 * Handles registration of Gutenberg extensions assets
 */
class Register {

    /**
     * Extension key mapping for settings lookup.
     *
     * Maps the runtime extension slug (as used in block attrs and asset folder
     * names) to the corresponding settings key under
     * `builder_support.gutenberg.extensions`.
     *
     * @var array<string,string>
     */
    private static $extension_key_map = [
        'entrance'    => 'entrance_effects',
        'floating'    => 'floating_effects',
        'tilt'        => 'tilt_effects',
        'tooltip'     => 'tooltip',
        'visibility'  => 'visibility_rules',
        'wrapperlink' => 'wrapper_link',
    ];

    /**
     * Cached builder settings (per-request).
     *
     * @var array|null
     */
    private $builder_settings = null;

    /**
     * Cached extension directories (per-request).
     *
     * @var array|null
     */
    private static $extension_dirs = null;

    /**
     * Stored `builder_support` settings merged over the defaults.
     *
     * The dashboard's "Gutenberg Blocks" switch stores only
     * `gutenberg.enabled`, so reading the raw option left every extension
     * "off" here (no editor controls, no front-end output) while the Block
     * Editor page — which shows the defaults — said they were on.
     *
     * @return array
     */
    private static function builder_support_settings() {
        $stored   = SettingsCache::instance()->get_setting( 'builder_support', [] );
        $defaults = Settings::instance()->get_default_settings();
        $defaults = isset( $defaults['builder_support'] ) && is_array( $defaults['builder_support'] ) ? $defaults['builder_support'] : [];

        return array_replace_recursive( $defaults, is_array( $stored ) ? $stored : [] );
    }

    /**
     * Get builder-support settings via SettingsCache (no raw get_option).
     *
     * @return array
     */
    private function get_builder_settings() {

        if ( $this->builder_settings === null ) {
            $this->builder_settings = self::builder_support_settings();
        }

        return $this->builder_settings;
    }

    /**
     * Check if Gutenberg support is enabled
     *
     * @return bool
     */
    private function is_gutenberg_enabled() {
        $settings = $this->get_builder_settings();
        return !empty( $settings['gutenberg']['enabled'] );
    }

    /**
     * Get Gutenberg settings for localization
     *
     * @return array
     */
    private function get_gutenberg_settings() {
        $settings = $this->get_builder_settings();
        return $settings['gutenberg'] ?? [
            'enabled'    => false,
            'extensions' => [],
        ];
    }

    /**
     * Check if a specific extension is enabled
     *
     * @param string $extension_name Extension name
     * @return bool
     */
    private function is_extension_enabled( $extension_name ) {
        return self::is_extension_globally_enabled( $extension_name );
    }

    /**
     * Check whether an extension is globally enabled in plugin settings.
     *
     * Public/static so the runtime render path (Attributes) can reuse the
     * exact same gating used by asset registration, without duplicating the
     * settings lookup or coupling to a Register instance.
     *
     * @param string $extension_name Extension slug (e.g. "entrance", "visibility").
     * @return bool
     */
    public static function is_extension_globally_enabled( $extension_name ) {
        $settings      = self::builder_support_settings();
        $extension_key = self::$extension_key_map[$extension_name] ?? $extension_name;

        if ( empty( $settings['gutenberg']['enabled'] ) ) {
            return false;
        }

        if ( empty( $settings['gutenberg']['extensions'][$extension_key] ) ) {
            return false;
        }

        // Premium extensions additionally require a valid license.
        // can_use_premium_feature() returns true for free extensions,
        // so this is a no-op for them.
        return ProFeatures::instance()->can_use_premium_feature( $extension_key );
    }

    /**
     * Register and enqueue editor extensions
     *
     * @return void
     */
    public function add_extensions() {

        if ( !$this->is_gutenberg_enabled() ) {
            return;
        }

        $this->enqueue_editor_scripts();
    }

    /**
     * Enqueue editor scripts and styles
     *
     * @return void
     */
    private function enqueue_editor_scripts() {
        $script_path = XERO_EXTENDERX_DIR_PATH . 'dist/extensions/editor.js';
        $asset_path  = XERO_EXTENDERX_DIR_PATH . 'dist/extensions/editor.asset.php';

        if ( !file_exists( $asset_path ) || !file_exists( $script_path ) ) {
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
                'gutenberg'   => $this->get_gutenberg_settings(),
                // Pro state for editor-side gating (e.g. Motion preset/easing
                // library). Mirrors the REST /pro-status used by the admin SPA.
                'pro'         => ProFeatures::instance()->has_pro_license(),
                'upgrade_url' => ProFeatures::instance()->get_upgrade_url('editor'),
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

        if ( !$this->is_gutenberg_enabled() ) {
            return;
        }

        $extensions = $this->get_extension_dirs();

        foreach ( $extensions as $extension_dir ) {
            $extension_name = basename( $extension_dir );

            if ( !$this->is_extension_enabled( $extension_name ) ) {
                continue;
            }

            $this->register_extension_assets( $extension_dir, $extension_name );
        }

    }

    /**
     * Get extension directories with static caching (avoids repeated glob() calls).
     *
     * @return array
     */
    private function get_extension_dirs() {

        if ( self::$extension_dirs === null ) {
            $extensions_dir       = XERO_EXTENDERX_DIR_PATH . 'dist/extensions/';
            self::$extension_dirs = is_dir( $extensions_dir )
            ? glob( $extensions_dir . '*', GLOB_ONLYDIR )
            : [];

            if ( !is_array( self::$extension_dirs ) ) {
                self::$extension_dirs = [];
            }

        }

        return self::$extension_dirs;
    }

    /**
     * Register assets for a single extension
     *
     * @param string $extension_dir  Extension directory path
     * @param string $extension_name Extension name
     * @return void
     */
    private function register_extension_assets( $extension_dir, $extension_name ) {
        $this->register_extension_script( $extension_dir, $extension_name );
        $this->register_extension_style( $extension_dir, $extension_name );
    }

    /**
     * Register extension script
     *
     * @param string $extension_dir  Extension directory path
     * @param string $extension_name Extension name
     * @return void
     */
    private function register_extension_script( $extension_dir, $extension_name ) {
        $script_path = $extension_dir . '/view.js';
        $asset_path  = $extension_dir . '/view.asset.php';

        if ( !file_exists( $script_path ) || !file_exists( $asset_path ) ) {
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
        // Webpack outputs CSS as style-view.css (MiniCssExtractPlugin naming),
        // but check view.css as well for backward compatibility
        $style_file = null;
        if (file_exists($extension_dir . '/style-view.css')) {
            $style_file = 'style-view.css';
        } elseif (file_exists($extension_dir . '/view.css')) {
            $style_file = 'view.css';
        }

        if (!$style_file) {
            return;
        }

// Prefer the per-asset version emitted alongside the script so CSS

// changes bust caches between plugin releases. Fall back to the
        // plugin version when no asset file is present.
        $asset_path = $extension_dir . '/view.asset.php';
        $version    = XERO_EXTENDERX_VERSION;

        if ( file_exists( $asset_path ) ) {
            $args    = require $asset_path;
            $version = isset( $args['version'] ) ? $args['version'] : XERO_EXTENDERX_VERSION;
        }

        wp_register_style(
            "@extenderx/extensions/{$extension_name}/style",
            XERO_EXTENDERX_DIST_URL . "extensions/{$extension_name}/{$style_file}",
            [],
            $version
        );
    }

}
