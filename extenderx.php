<?php

/**
 * Plugin Name:       ExtenderX
 * Description:       ExtenderX is a plugin that extends the functionality of the WordPress block editor.
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Version:           1.1.0
 * Author:            WPXERO
 * Author URI:        https://wpxero.com
 * License:           GPL v2 or later
 * License URI:      https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:      extenderx
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class XERO_EXTENDERX {
    /**
     * The single class instance.
     *
     * @var $instance
     */
    private static $instance = null;

    /**
     * Path to the plugin directory
     *
     * @var $plugin_path
     */
    public $plugin_path;

    /**
     * URL to the plugin directory
     *
     * @var $plugin_url
     */
    public $plugin_url;

    /**
     * Plugin name
     *
     * @var $plugin_name
     */
    public $plugin_name;

    /**
     * Plugin version
     *
     * @var $plugin_version
     */
    public $plugin_version;

    /**
     * Plugin slug
     *
     * @var $plugin_slug
     */
    public $plugin_slug;

    /**
     * Plugin name sanitized
     *
     * @var $plugin_name_sanitized
     */
    public $plugin_name_sanitized;

    /**
     * XERO\EXTENDERX constructor.
     */
    public function __construct() {
        /* We do nothing here! */
    }

    /**
     * Get the single class instance.
     *
     * @return EXTENDERX
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->define_constants();
            self::$instance->init_hooks();
        }
        return self::$instance;
    }

    public function init_hooks() {
        $this->load_files();
        add_action('enqueue_block_assets', array($this, 'enqueue_editor_assets'));
        add_action('enqueue_block_assets', array($this, 'enqueue_front_scripts'));
    }

    /**
     * Define Constants
     */
    public function define_constants() {
        define('XERO_EXTENDERX_FILE', __FILE__);
        define('XERO_EXTENDERX_SLUG', 'extenderx');
        define('XERO_EXTENDERX_VERSION', '1.1.0');
        define('XERO_EXTENDERX_DIR_PATH', plugin_dir_path(__FILE__));
        define('XERO_EXTENDERX_ADMIN_URL', plugin_dir_url(__FILE__));
        define('XERO_EXTENDERX_WP_VERSION', (float) get_bloginfo('version'));
        define('XERO_EXTENDERX_PHP_VERSION', (float) phpversion());
    }

    public function load_files() {
        require_once XERO_EXTENDERX_DIR_PATH . 'includes/Utils.php';
        require_once XERO_EXTENDERX_DIR_PATH . 'includes/Register.php';
        require_once XERO_EXTENDERX_DIR_PATH . 'includes/Attributes.php';
    }


    public function enqueue_editor_assets() {

        if (!is_admin()) {
            return;
        }
        $extensions_dir =  new XERO\EXTENDERX\Register();
        $extensions_dir->add_extensions();
    }


    public function enqueue_front_scripts() {
        if (is_admin()) {
            return;
        }
        $extensions_dir =  new XERO\EXTENDERX\Register();
        $extensions_dir->add_frontend_assets();
    }
}



/**
 * Function works with the XERO\EXTENDERX class instance.
 *
 * @return object XERO\EXTENDERX
 */
function xero_extenderx() {
    return XERO_EXTENDERX::instance();
}
add_action('plugins_loaded', 'xero_extenderx');
