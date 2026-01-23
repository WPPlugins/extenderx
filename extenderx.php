<?php

/**
 * Plugin Name:       ExtenderX – Block Animation, Dark Mode & Content Protection
 * Plugin URI:        https://wpxero.com/extenderx
 * Description:       All-in-one WordPress plugin for block animations, dark mode, content protection, and website security.
 * Version:           1.5.1
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
$xero_extenderx_autoload_file = __DIR__ . '/vendor/autoload.php';
if (file_exists($xero_extenderx_autoload_file)) {
    // Load autoloader (vendor/autoload.php).
    require_once $xero_extenderx_autoload_file;
}

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\Settings;
use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\ProFeatures;
use WPXERO\ExtenderX\Admin\Dashboard;
use WPXERO\ExtenderX\Modules\Gutenberg\Gutenberg;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SiteEnhancement;
use WPXERO\ExtenderX\Core\RegisterAssets;

class XERO_EXTENDERX {
    use Singleton;

    /**
     * Freemius instance
     * @var object
     */
    private $freemius;

    /**
     * XERO\EXTENDERX constructor.
     */
    public function __construct() {
        /* We do nothing here! */
        self::define_constants();
        $this->init_freemius();
        self::init_hooks();
    }


    public function init_hooks() {
        $this->load_files();
    }


    /**
     * Define Constants
     */
    public static function define_constants() {
        if (!defined('XERO_EXTENDERX_FILE')) {
            define('XERO_EXTENDERX_FILE', __FILE__);
        }
        if (!defined('XERO_EXTENDERX_SLUG')) {
            define('XERO_EXTENDERX_SLUG', 'extenderx');
        }
        if (!defined('XERO_EXTENDERX_VERSION')) {
            define('XERO_EXTENDERX_VERSION', '1.5.1');
        }
        if (!defined('XERO_EXTENDERX_DIR_PATH')) {
            define('XERO_EXTENDERX_DIR_PATH', plugin_dir_path(__FILE__));
        }
        if (!defined('XERO_EXTENDERX_ADMIN_URL')) {
            define('XERO_EXTENDERX_ADMIN_URL', plugin_dir_url(__FILE__));
        }
        if (!defined('XERO_EXTENDERX_ASSETS_URL')) {
            define('XERO_EXTENDERX_ASSETS_URL', plugin_dir_url(__FILE__) . 'assets/');
        }
        if (!defined('XERO_EXTENDERX_DIST_URL')) {
            define('XERO_EXTENDERX_DIST_URL', plugin_dir_url(__FILE__) . 'dist/');
        }
        if (!defined('XERO_EXTENDERX_WP_VERSION')) {
            define('XERO_EXTENDERX_WP_VERSION', (float) get_bloginfo('version'));
        }
        if (!defined('XERO_EXTENDERX_PHP_VERSION')) {
            define('XERO_EXTENDERX_PHP_VERSION', (float) phpversion());
        }
    }

    public function load_files() {
        SettingsCache::instance();
        ProFeatures::instance();
        Settings::instance();
        Dashboard::instance();
        RegisterAssets::instance();
        Gutenberg::instance();
        SiteEnhancement::instance();
    }

    /**
     * Initialize Freemius SDK
     */
    private function init_freemius() {
        if (!isset($this->freemius)) {
            // Include Freemius SDK
            if (file_exists(dirname(__FILE__) . '/vendor/freemius/wordpress-sdk/start.php')) {
                require_once dirname(__FILE__) . '/vendor/freemius/wordpress-sdk/start.php';

                try {
                    $this->freemius = fs_dynamic_init(array(
                        'id'                  => '16704',
                        'slug'                => 'extenderx',
                        'premium_slug'        => 'extenderx-pro',
                        'type'                => 'plugin',
                        'public_key'          => 'pk_cbf9e70aec51eac31d0122b43fd2c',
                        'is_premium'          => false,
                        'is_premium_only'     => false,
                        'has_paid_plans'      => true,
                        'is_live'             => true,
                        'is_org_compliant'    => true,
                        'parallel_activation' => array(
                            'enabled'                  => true,
                            'premium_version_basename' => 'extenderx-pro/extenderx-pro.php',
                        ),
                        'menu'                => array(
                            'slug'        => 'extenderx',
                            'first-path'  => 'admin.php?page=extenderx',
                            'support'     => false,
                            'contact'     => false,
                            'pricing'     => true,
                        ),
                    ));

                    // Signal that Freemius SDK is initiated
                    do_action('xero_extenderx_fs_loaded');
                } catch (Exception $e) {
                    // Log error but don't break the plugin
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('ExtenderX Freemius Error: ' . $e->getMessage());
                    }
                }
            }
        }

        return $this->freemius;
    }

    /**
     * Get Freemius instance
     *
     * @return object|null
     */
    public function get_freemius() {
        return $this->freemius;
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

/**
 * Get Freemius instance for free plugin
 *
 * @return object|null
 */
function xero_extenderx_fs() {
    $plugin = xero_extenderx();
    return $plugin ? $plugin->get_freemius() : null;
}

$plugin = xero_extenderx();
// activate and deactivate
register_activation_hook(__FILE__, array($plugin, 'xero_extenderx_activate'));
register_deactivation_hook(__FILE__, array($plugin, 'xero_extenderx_deactivate'));
