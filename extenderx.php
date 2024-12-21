<?php

/**
 * Plugin Name:       ExtenderX
 * Plugin URI:        https://wpxero.com/plugins/extenderx
 * Description:       Supercharge your Gutenberg blocks with ExtenderX! Enjoy advanced animations, dynamic effects, and responsive controls for stunning interactivity!
 * Version:           1.3.3
 * Author:            WPXERO
 * Author URI:        https://wpxero.com
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       extenderx
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
     * Freemius instance
     *
     * @var $freemius
     */
    private $freemius;

    /**
     * XERO\EXTENDERX constructor.
     */
    public function __construct() {
        /* We do nothing here! */
    }

    /**
     * Get the single class instance.
     *
     * @return XERO_EXTENDERX
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
        $this->extenderx_sdk_xero_plugin();
    }

    /**
     * Define Constants
     */
    public static function define_constants() {
        define('XERO_EXTENDERX_FILE', __FILE__);
        define('XERO_EXTENDERX_SLUG', 'extenderx');
        define('XERO_EXTENDERX_VERSION', '1.3.3');
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

        //library
        $asset_path = __DIR__ . '/dist/extenderx.asset.php';
        $args = require $asset_path;
        wp_enqueue_script('@extenderx/library', XERO_EXTENDERX_ADMIN_URL . 'dist/extenderx.js', $args['dependencies'], $args['version'], true);
        wp_enqueue_style('@extenderx/library', XERO_EXTENDERX_ADMIN_URL . 'dist/extenderx.css', array(), $args['version']);
        wp_enqueue_style('style-extenderx', plugins_url('dist/style-extenderx.css', __FILE__));
        wp_enqueue_style('xero-sdk-extenderx', plugins_url('data/assets/css/data.css', __FILE__), array(), '1.2.1', 'all');

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

    /**
     * SDK Integration
     */

    public  function extenderx_sdk_xero_plugin() {
        require_once dirname(__FILE__) . '/data/start.php';
        // wp_enqueue_style('xero-sdk-extenderx');
        data_dynamic_init(array(
            'sdk_version'   => '1.2.1',
            'product_id'    => 10,
            'plugin_name'   => 'ExtenderX',
            'plugin_title'  => 'Greetings from WPXERO! 🌟',
            'api_endpoint'  => 'https://wpxero.com/wp-json/dci/v1/data-insights',
            'slug'          => 'extenderx',
            'core_file'     => false,
            'plugin_deactivate_id' => false,
            'public_key'    => 'pk_ac3oLvWY8fey0tpDa9SE3ZO7OgmswHnA',
            'is_premium'    => false,
            'popup_notice'  => false,
            'deactivate_feedback' => true,
            'text_domain'  => 'extenderx',
            'plugin_msg'   => '<p>Make a big impact on WordPress by sharing non-sensitive plugin data. Get valuable updates and be part of something amazing!</p>',
        ));
    }
}


/**
 * Function works with the XERO\EXTENDERX class instance.
 *
 * @return object XERO_EXTENDERX
 */
function xero_extenderx() {
    return XERO_EXTENDERX::instance();
}
add_action('plugins_loaded', 'xero_extenderx');
