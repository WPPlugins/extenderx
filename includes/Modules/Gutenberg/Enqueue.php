<?php

namespace WPXERO\ExtenderX\Modules\Gutenberg;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Modules\Gutenberg\Register;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Singleton Trait
 *
 * Provides singleton functionality to classes
 */
class Enqueue {
    use Singleton;

    public function __construct() {
        add_action('enqueue_block_assets', array($this, 'enqueue_editor_assets'));
        add_action('enqueue_block_assets', array($this, 'enqueue_front_scripts'));
    }


    public function enqueue_editor_assets() {
        wp_register_style('tippy', XERO_EXTENDERX_DIST_URL . 'vendor/tippy.js/tippy.min.css', array(), XERO_EXTENDERX_VERSION);

        if (!is_admin()) {
            return;
        }

        //library
        $asset_path = XERO_EXTENDERX_DIR_PATH . '/dist/library/index.asset.php';
        $args = require $asset_path;
        wp_enqueue_script('@extenderx/library', XERO_EXTENDERX_DIST_URL . 'library/index.js', $args['dependencies'], $args['version'], true);
        wp_enqueue_style('@extenderx/library', XERO_EXTENDERX_DIST_URL . 'library/index.css', array(), $args['version']);
        wp_enqueue_style('style-extenderx', XERO_EXTENDERX_DIST_URL . 'library/style-index.css', array(), $args['version']);
        // Enqueue tippy
        wp_enqueue_style('tippy');
        $extensions_dir =  new Register();
        $extensions_dir->add_extensions();
    }


    public function enqueue_front_scripts() {
        if (is_admin()) {
            return;
        }
        $extensions_dir =  new Register();
        $extensions_dir->add_frontend_assets();
    }
}
