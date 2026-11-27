<?php

namespace WPXERO\ExtenderX\Core;

defined('ABSPATH') || exit;

// Import WordPress classes and functions
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode\SnippetTable;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Animations\SiteWide\SiteWideCatalog;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility\AccessibilityCatalog;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo\SaveAndGoCatalog;
use WPXERO\ExtenderX\Core\ProFeatures;

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
     * SettingsCache instance.
     *
     * @var SettingsCache
     */
    private $cache;

    /**
     * Settings constructor.
     */
    public function __construct() {
        $this->cache = SettingsCache::instance();
        \add_action('rest_api_init', [$this, 'register_routes']);
        \add_action('admin_init', [$this, 'maybe_upgrade']);
    }

    /**
     * Run migration and ensure defaults on first install or plugin upgrade.
     * Only fires on admin requests and only when the stored DB version differs.
     *
     * @return void
     */
    public function maybe_upgrade() {
        $installed = \get_option('extenderx_db_version', '0');

        // The settings schema carries its own version, so check it separately:
        // a site whose stored db_version is already current (a downgrade, or a
        // hand-edited option) would otherwise never run a pending schema
        // migration and would read defaults for the missing subtree forever.
        $schema_stale = (int) \get_option(SettingsCache::MIGRATION_KEY, 0) < SettingsCache::SETTINGS_VERSION;

        if (!$schema_stale && version_compare($installed, XERO_EXTENDERX_VERSION, '>=')) {
            return;
        }

        // Migrate legacy single-option → per-module options
        $this->cache->maybe_migrate();

        // Ensure default settings exist for any new modules/features
        $this->ensure_default_settings();

        // Ensure the Custom Code snippets table exists / is up to date.
        // dbDelta is idempotent, so this is safe to run on every upgrade.
        SnippetTable::create();

        \update_option('extenderx_db_version', XERO_EXTENDERX_VERSION, true);
    }

    /**
     * Ensure default settings exist without overwriting user values.
     * Uses array_replace_recursive so nested defaults are filled in properly.
     *
     * @return void
     */
    public function ensure_default_settings() {
        $current  = $this->cache->get_all_settings();
        $defaults = $this->get_default_settings();
        $merged   = $this->array_replace_recursive_safe($defaults, $current);

        if ($current !== $merged) {
            $this->cache->save_all_settings($merged);
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
                'custom_code' => [
                    'enabled' => true,
                ],
                'animations' => [
                    'scroll_effects'   => [
                        'enabled'      => false,
                        'type'         => 'parallax',
                        'intensity'    => 30,
                        'desktop_only' => true,
                        'selectors'    => '',
                    ],
                    'site_wide'        => SiteWideCatalog::defaults(),
                ],
                'accessibility' => AccessibilityCatalog::defaults(),
                'save_and_go' => SaveAndGoCatalog::defaults(),
            ],
            'builder_support' => [
                'gutenberg' => [
                    'enabled' => true,
                    'extensions' => [
                        'entrance_effects'   => true,
                        'tilt_effects'       => true,
                        'floating_effects'   => true,
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
            $current_settings = $this->cache->get_all_settings();
            $updated_settings = $this->array_replace_recursive_safe($current_settings, $new_settings);

            // Validate settings before saving
            $validated_settings = $this->validate_settings($updated_settings);

            $this->cache->save_all_settings($validated_settings);

            // Clear targeted transients
            \delete_transient('extenderx_builder_cache');
            \delete_transient('extenderx_site_data');

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
        $settings = $this->cache->get_all_settings();
        $defaults = $this->get_default_settings();

        // Merge with defaults to ensure all settings exist
        $complete_settings = $this->array_replace_recursive_safe($defaults, $settings);

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

        // Sanitize Motion & Animations settings against the catalog + pro tier.
        if (isset($settings['global']['animations']) && is_array($settings['global']['animations'])) {
            $settings['global']['animations'] = $this->sanitize_animations($settings['global']['animations']);
        }

        // Sanitize Accessibility settings against the catalog + pro tier.
        if (isset($settings['global']['accessibility']) && is_array($settings['global']['accessibility'])) {
            $settings['global']['accessibility'] = AccessibilityCatalog::sanitize(
                $settings['global']['accessibility'],
                ProFeatures::instance()->has_pro_license()
            );
        }

        // Sanitize Save & Go settings against the catalog + pro tier.
        if (isset($settings['global']['save_and_go']) && is_array($settings['global']['save_and_go'])) {
            $settings['global']['save_and_go'] = SaveAndGoCatalog::sanitize(
                $settings['global']['save_and_go'],
                ProFeatures::instance()->has_pro_license()
            );
        }

        return $settings;
    }

    /**
     * Sanitize the `global.animations` subtree: whitelist presets/easings,
     * clamp numerics, cast booleans, and enforce the free/pro boundary
     * server-side (defense-in-depth on top of the admin UI gating).
     *
     * @param array $animations Raw animations settings.
     * @return array
     */
    private function sanitize_animations(array $animations) {
        $is_pro = ProFeatures::instance()->has_pro_license();

        $clean = [];

        // Scroll-linked engine (pro only).
        $sin = isset($animations['scroll_effects']) && is_array($animations['scroll_effects'])
            ? $animations['scroll_effects']
            : [];
        $scroll_types = ['parallax', 'scrub'];
        $clean['scroll_effects'] = [
            'enabled'      => $is_pro ? !empty($sin['enabled']) : false,
            'type'         => in_array($sin['type'] ?? 'parallax', $scroll_types, true)
                ? $sin['type']
                : 'parallax',
            'intensity'    => $this->clamp_int($sin['intensity'] ?? 30, 0, 100),
            'desktop_only' => isset($sin['desktop_only']) ? (bool) $sin['desktop_only'] : true,
            'selectors'    => $is_pro ? sanitize_text_field((string) ($sin['selectors'] ?? '')) : '',
        ];

        // Site-wide reveal engine.
        $swin = isset($animations['site_wide']) && is_array($animations['site_wide'])
            ? $animations['site_wide']
            : [];
        $clean['site_wide'] = $this->sanitize_site_wide($swin, $is_pro);

        return $clean;
    }

    /**
     * Sanitize the `global.animations.site_wide` subtree (rebuilt engine).
     *
     * Whitelists the style/easing against {@see SiteWideCatalog}, clamps
     * numerics, casts booleans, and forces every pro-only field to its default
     * for free installs (defense-in-depth on top of the admin UI gating).
     *
     * @param array $in     Raw site_wide settings.
     * @param bool  $is_pro License flag.
     * @return array
     */
    private function sanitize_site_wide(array $in, $is_pro) {
        $d = SiteWideCatalog::defaults();

        $clean = [
            'enabled'     => !empty($in['enabled']),
            'style'       => SiteWideCatalog::sanitize_style($in['style'] ?? $d['style'], $is_pro),
            'easing'      => SiteWideCatalog::sanitize_easing($in['easing'] ?? $d['easing'], $is_pro),
            'duration'    => $this->clamp_int($in['duration'] ?? $d['duration'], 0, 3000),
            'delay'       => $this->clamp_int($in['delay'] ?? $d['delay'], 0, 3000),
            'distance'    => $this->clamp_int($in['distance'] ?? $d['distance'], 0, 200),
            'threshold'   => $this->clamp_float($in['threshold'] ?? $d['threshold'], 0, 1),
            'target_mode' => 'specific' === ($in['target_mode'] ?? $d['target_mode']) ? 'specific' : 'auto',
            'targets'     => SiteWideCatalog::sanitize_targets($in['targets'] ?? $d['targets']),

            // Pro-only — forced to defaults for free installs.
            'offset'    => $is_pro ? $this->clamp_int($in['offset'] ?? $d['offset'], 0, 1000) : $d['offset'],
            'stagger'   => $is_pro ? $this->clamp_int($in['stagger'] ?? $d['stagger'], 0, 2000) : $d['stagger'],
            'replay'    => $is_pro ? !empty($in['replay']) : false,
            'selectors' => $is_pro ? sanitize_text_field((string) ($in['selectors'] ?? '')) : '',
            'mobile'    => $this->sanitize_site_wide_mobile($in['mobile'] ?? null, $is_pro),
            'page_filter_mode'     => 'include' === ($in['page_filter_mode'] ?? 'exclude') ? 'include' : 'exclude',
            'exclude_page_ids'     => $is_pro ? $this->sanitize_id_list($in['exclude_page_ids'] ?? []) : [],
            'exclude_post_ids'     => $is_pro ? $this->sanitize_id_list($in['exclude_post_ids'] ?? []) : [],
            'exclude_url_patterns' => $is_pro ? $this->sanitize_pattern_list($in['exclude_url_patterns'] ?? []) : [],
        ];

        return $clean;
    }

    /**
     * Sanitize a list of post/page IDs: positive integers only, de-duplicated.
     *
     * @param mixed $raw
     * @return int[]
     */
    private function sanitize_id_list($raw) {
        if (!is_array($raw)) {
            return [];
        }
        $ids = array_filter(array_map('intval', $raw), static function ($id) {
            return $id > 0;
        });
        return array_values(array_unique($ids));
    }

    /**
     * Sanitize a list of URL exclusion patterns (plain strings, `*` wildcard).
     *
     * @param mixed $raw
     * @return string[]
     */
    private function sanitize_pattern_list($raw) {
        if (!is_array($raw)) {
            return [];
        }
        $patterns = array_filter(array_map(static function ($p) {
            return sanitize_text_field((string) $p);
        }, $raw), static function ($p) {
            return '' !== trim($p);
        });
        return array_values(array_unique($patterns));
    }

    /**
     * Sanitize the site-wide `mobile` sub-config. `enabled` (run on mobile at
     * all) is free; `reduce` (shorter/faster on small screens) is pro.
     *
     * @param mixed $raw
     * @param bool  $is_pro
     * @return array
     */
    private function sanitize_site_wide_mobile($raw, $is_pro) {
        $d   = SiteWideCatalog::defaults()['mobile'];
        $raw = is_array($raw) ? $raw : [];
        return [
            'enabled' => isset($raw['enabled']) ? (bool) $raw['enabled'] : $d['enabled'],
            'reduce'  => $is_pro ? !empty($raw['reduce']) : false,
        ];
    }

    /**
     * Clamp to an integer within [min, max].
     *
     * @param mixed $value
     * @param int   $min
     * @param int   $max
     * @return int
     */
    private function clamp_int($value, $min, $max) {
        return max($min, min($max, (int) $value));
    }

    /**
     * Clamp to a float within [min, max].
     *
     * @param mixed $value
     * @param float $min
     * @param float $max
     * @return float
     */
    private function clamp_float($value, $min, $max) {
        return max($min, min($max, (float) $value));
    }

    /**
     * Recursively merge two arrays, using $override values where they exist,
     * but preserving $base defaults for keys not present in $override.
     *
     * @param array $base     Base/default array.
     * @param array $override Override values.
     * @return array
     */
    private function array_replace_recursive_safe(array $base, array $override) {
        $merged = $base;

        foreach ($override as $key => $value) {
            if (
                is_array($value) && isset($merged[$key]) && is_array($merged[$key])
                && !$this->is_list_array($value) && !$this->is_list_array($merged[$key])
            ) {
                $merged[$key] = $this->array_replace_recursive_safe($merged[$key], $value);
            } else {
                // Scalars and LIST arrays replace wholly. Recursing into lists
                // merges them index-wise, which makes it impossible to ever
                // remove an item: stored [2] merged with incoming [] stays [2]
                // (exclusion lists could not be cleared, unchecked `targets`
                // resurrected, etc.).
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Whether an array is a sequential list (0..n-1 keys). Empty arrays count
     * as lists. Polyfill for array_is_list() (PHP 8.1+); plugin supports 7.4.
     *
     * @param array $arr
     * @return bool
     */
    private function is_list_array(array $arr) {
        if (function_exists('array_is_list')) {
            return array_is_list($arr);
        }
        return $arr === [] || array_keys($arr) === range(0, count($arr) - 1);
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
