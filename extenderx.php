<?php

/**
 * Plugin Name:       ExtenderX – Block Animation, Dark Mode, Content Protection & Security for WordPress
 * Plugin URI:        https://wpxero.com/extenderx
 * Description:       All-in-one WordPress plugin for block animations, dark mode, content protection, and website security.
 * Version:           1.5.0
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

// Check if vendor directory and autoload.php exist
$autoload_file = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload_file)) {
    // Load autoloader (vendor/autoload.php).
    require_once $autoload_file;
}

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\Register;
use WPXERO\ExtenderX\Core\Settings;
use WPXERO\ExtenderX\Core\Attributes;
use WPXERO\ExtenderX\Admin\Dashboard;
use WPXERO\ExtenderX\Modules\Gutenberg\Gutenberg;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SiteEnhancement;
use WPXERO\ExtenderX\Core\RegisterAssets;

class XERO_EXTENDERX {
    use Singleton;
    /**
     * XERO\EXTENDERX constructor.
     */
    public function __construct() {
        /* We do nothing here! */
        self::define_constants();
        self::init_hooks();
    }


    public function init_hooks() {
        $this->load_files();
    }


    /**
     * Define Constants
     */
    public static function define_constants() {
        define('XERO_EXTENDERX_FILE', __FILE__);
        define('XERO_EXTENDERX_SLUG', 'extenderx');
        define('XERO_EXTENDERX_VERSION', '1.5.0');
        define('XERO_EXTENDERX_DIR_PATH', plugin_dir_path(__FILE__));
        define('XERO_EXTENDERX_ADMIN_URL', plugin_dir_url(__FILE__));
        define('XERO_EXTENDERX_ASSETS_URL', plugin_dir_url(__FILE__) . 'assets/');
        define('XERO_EXTENDERX_DIST_URL', plugin_dir_url(__FILE__) . 'dist/');
        define('XERO_EXTENDERX_WP_VERSION', (float) get_bloginfo('version'));
        define('XERO_EXTENDERX_PHP_VERSION', (float) phpversion());
    }

    public function load_files() {
        Settings::instance();
        Dashboard::instance();
        RegisterAssets::instance();
        Gutenberg::instance();
        SiteEnhancement::instance();
    }

    public function xero_extenderx_activate() {
    }

    public function xero_extenderx_deactivate() {
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

$plugin = xero_extenderx();
// activate and deactivate
register_activation_hook(__FILE__, array($plugin, 'xero_extenderx_activate'));
register_deactivation_hook(__FILE__, array($plugin, 'xero_extenderx_deactivate'));
