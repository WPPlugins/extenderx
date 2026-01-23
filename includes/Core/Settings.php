<?php

namespace WPXERO\ExtenderX\Core;

defined('ABSPATH') || exit;

// Import WordPress classes and functions
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WPXERO\ExtenderX\Traits\Singleton;

/**
 * Class Settings
 */
class Settings extends \WP_REST_Controller {
    use Singleton;
    /**
     * Namespace.
     *
     * @var string
     */
    protected $namespace = 'wpxero/extenderx/v';

    /**
     * Version.
     *
     * @var string
     */
    protected $version = '1';

    /**
     * Settings constructor.
     */
    public function __construct() {
        \add_action('rest_api_init', [$this, 'register_routes']);
        \add_action('init', [$this, 'init_default_settings']);
    }

    /**
     * Initialize default settings
     */
    public function init_default_settings() {
        $current_settings = \get_option('extenderx_settings', []);
        $default_settings = $this->get_default_settings();

        // Merge with existing settings, keeping existing values
        $merged_settings = array_merge($default_settings, $current_settings);

        if ($current_settings !== $merged_settings) {
            \update_option('extenderx_settings', $merged_settings);
        }
    }

    /**
     * Get default settings
     *
     * @return array
     */
    public function get_default_settings() {
        return [
            'global' => [
                'enabled' => true,
            ],
            'builder_support' => [
                'gutenberg' => [
                    'enabled' => true,
                    'extensions' => [
                        'entrance_effects'   => true,
                        'tilt_effects'       => true,
                        'floating_effects'   => true,
                        'custom_css_js'      => false,
                        'visibility_rules'   => true,
                        'tooltip'            => true,
                        'wrapper_link'       => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * Register rest routes.
     */
    public function register_routes() {
        $namespace = $this->namespace . $this->version;

        // Update Settings.
        \register_rest_route(
            $namespace,
            '/update_settings/',
            [
                'methods'             => ['POST'],
                'callback'            => [$this, 'update_settings'],
                'permission_callback' => [$this, 'update_settings_permission'],
            ]
        );

        // Get Settings.
        \register_rest_route(
            $namespace,
            '/get_settings/',
            [
                'methods'             => ['GET'],
                'callback'            => [$this, 'get_settings'],
                'permission_callback' => [$this, 'get_settings_permission'],
            ]
        );
    }

    /**
     * Get edit options permissions.
     *
     * @return bool
     */
    public function update_settings_permission() {
        if (! \current_user_can('manage_options')) {
            return $this->error('user_dont_have_permission', \__('User don\'t have permissions to change options.', 'extenderx'), true);
        }

        return true;
    }

    /**
     * Get settings permissions.
     *
     * @return bool
     */
    public function get_settings_permission() {
        if (! \current_user_can('manage_options')) {
            return $this->error('user_dont_have_permission', \__('User don\'t have permissions to view options.', 'extenderx'), true);
        }

        return true;
    }

    /**
     * Update Settings.
     *
     * @param WP_REST_Request $req  request object.
     *
     * @return mixed
     */
    public function update_settings(WP_REST_Request $req) {
        $new_settings = $req->get_param('settings');

        if (is_array($new_settings)) {
            $current_settings = get_option('extenderx_settings', []);
            $updated_settings = array_merge($current_settings, $new_settings);

            // Validate settings before saving
            $validated_settings = $this->validate_settings($updated_settings);

            update_option('extenderx_settings', $validated_settings);

            // Clear any cached data
            $this->clear_cache();

            return $this->success([
                'message' => __('Settings updated successfully.', 'extenderx'),
                'settings' => $validated_settings
            ]);
        }

        return $this->error('invalid_settings', __('Invalid settings data provided.', 'extenderx'));
    }

    /**
     * Get Settings.
     *
     * @param WP_REST_Request $req  request object.
     *
     * @return mixed
     */
    public function get_settings(WP_REST_Request $req) {
        $settings = get_option('extenderx_settings', []);
        $default_settings = $this->get_default_settings();

        // Merge with defaults to ensure all settings exist
        $complete_settings = array_merge($default_settings, $settings);

        return $this->success($complete_settings);
    }

    /**
     * Validate settings before saving
     *
     * @param array $settings
     * @return array
     */
    private function validate_settings($settings) {
        $default_settings = $this->get_default_settings();

        // Ensure all required keys exist
        foreach ($default_settings as $key => $default_value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $default_value;
            }
        }

        // Validate builder support settings
        if (isset($settings['builder_support'])) {
            foreach ($settings['builder_support'] as $builder => $config) {
                if (isset($config['enabled']) && !is_bool($config['enabled'])) {
                    $settings['builder_support'][$builder]['enabled'] = (bool) $config['enabled'];
                }
            }
        }


        return $settings;
    }

    /**
     * Clear cache when settings are updated
     */
    private function clear_cache() {
        // Clear any cached CSS
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        // Clear any transients
        delete_transient('extenderx_builder_cache');
    }

    /**
     * Success rest.
     *
     * @param mixed $response response data.
     * @return mixed
     */
    public function success($response) {
        return new WP_REST_Response(
            [
                'success'  => true,
                'response' => $response,
            ],
            200
        );
    }

    /**
     * Error rest.
     *
     * @param mixed   $code       error code.
     * @param mixed   $response   response data.
     * @param boolean $true_error use true error response to stop the code processing.
     * @return mixed
     */
    public function error($code, $response, $true_error = false) {
        if ($true_error) {
            return new WP_Error($code, $response, ['status' => 401]);
        }

        return new WP_REST_Response(
            [
                'error'      => true,
                'success'    => false,
                'error_code' => $code,
                'response'   => $response,
            ],
            401
        );
    }
}
