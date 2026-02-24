<?php

namespace WPXERO\ExtenderX\Core;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pro Features Manager
 *
 * Manages premium features, licensing, and upgrade prompts
 *
 * @package WPXERO\ExtenderX\Core
 * @since 1.5.0
 */
class ProFeatures {
    use Singleton;

    /**
     * Premium features configuration
     *
     * @var array
     */
    private const PRO_FEATURES = [
        // Appearance - Advanced Modes
        'high_contrast_mode' => [
            'title' => 'High Contrast Mode',
            'category' => 'appearance',
            'type' => 'premium'
        ],
        'reading_mode' => [
            'title' => 'Reading Mode',
            'category' => 'appearance',
            'type' => 'premium'
        ],
        'night_shift_mode' => [
            'title' => 'Night Shift Mode',
            'category' => 'appearance',
            'type' => 'premium'
        ],
        'glassmorphism' => [
            'title' => 'Glassmorphism',
            'category' => 'appearance',
            'type' => 'premium'
        ],
        'blur_background_on_scroll' => [
            'title' => 'Blur Background on Scroll',
            'category' => 'appearance',
            'type' => 'premium'
        ],
        'dim_background_on_idle' => [
            'title' => 'Dim Background on Idle',
            'category' => 'appearance',
            'type' => 'premium'
        ],
        'skeleton_loader' => [
            'title' => 'Skeleton Loader',
            'category' => 'appearance',
            'type' => 'premium'
        ],
        'preloader' => [
            'title' => 'Preloader',
            'category' => 'appearance',
            'type' => 'premium'
        ],

        // Animations - Advanced
        'parallax_effects' => [
            'title' => 'Parallax Effects',
            'category' => 'animations',
            'type' => 'premium'
        ],

        // All Seasonal Effects are Premium
        'christmas_lights' => [
            'title' => 'Christmas Lights',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'fireworks' => [
            'title' => 'Fireworks',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'autumn_leaves' => [
            'title' => 'Autumn Leaves',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'hearts_falling' => [
            'title' => 'Falling Hearts',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'halloween_bats' => [
            'title' => 'Halloween Bats',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'ramadan_lanterns' => [
            'title' => 'Ramadan Lanterns',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'weather' => [
            'title' => 'Weather Effects',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'particle_bg' => [
            'title' => 'Particle Background',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'fireflies' => [
            'title' => 'Fireflies',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'stars_twinkling' => [
            'title' => 'Twinkling Stars',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'floating_emojis' => [
            'title' => 'Floating Emojis',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'floating_shapes' => [
            'title' => 'Floating Shapes',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'light_beams' => [
            'title' => 'Light Beams',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'mouse_trail' => [
            'title' => 'Mouse Trail',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],

        // All Site Modes are Premium
        'maintenance_mode' => [
            'title' => 'Maintenance Mode',
            'category' => 'site_modes',
            'type' => 'premium'
        ],
        'coming_soon_mode' => [
            'title' => 'Coming Soon Mode',
            'category' => 'site_modes',
            'type' => 'premium'
        ],
        'age_verification' => [
            'title' => 'Age Verification',
            'category' => 'site_modes',
            'type' => 'premium'
        ],
        'cookie_consent' => [
            'title' => 'Cookie Consent',
            'category' => 'site_modes',
            'type' => 'premium'
        ],
        'gdpr_notice' => [
            'title' => 'GDPR Notice',
            'category' => 'site_modes',
            'type' => 'premium'
        ],
        'exit_intent' => [
            'title' => 'Exit Intent',
            'category' => 'site_modes',
            'type' => 'premium'
        ],

        // All Marketing Features are Premium
        'announcement_bar' => [
            'title' => 'Announcement Bar',
            'category' => 'marketing',
            'type' => 'premium'
        ],
        'floating_cta_button' => [
            'title' => 'Floating CTA Button',
            'category' => 'marketing',
            'type' => 'premium'
        ],
        'sticky_whatsapp_button' => [
            'title' => 'Sticky WhatsApp Button',
            'category' => 'marketing',
            'type' => 'premium'
        ],
        'cta_pulse_animation' => [
            'title' => 'CTA Pulse Animation',
            'category' => 'marketing',
            'type' => 'premium'
        ],
        'global_countdown_banner' => [
            'title' => 'Global Countdown Banner',
            'category' => 'marketing',
            'type' => 'premium'
        ],
        'cursor_effects' => [
            'title' => 'Custom Cursor',
            'category' => 'ui_enhancements',
            'type' => 'premium'
        ],
        'cursor_hover' => [
            'title' => 'Cursor Hover Effects',
            'category' => 'ui_enhancements',
            'type' => 'premium'
        ],
        'cursor_grab' => [
            'title' => 'Cursor Grab Mode',
            'category' => 'ui_enhancements',
            'type' => 'premium'
        ],
        'scrollbar_style' => [
            'title' => 'Scrollbar Styler',
            'category' => 'ui_enhancements',
            'type' => 'premium'
        ],

        // Gutenberg Extensions
        'custom_css_js' => [
            'title' => 'Custom CSS & JS',
            'category' => 'gutenberg',
            'type' => 'premium'
        ],
    ];

    /**
     * Constructor
     */
    public function __construct() {
        add_filter('extenderx_is_pro_active', [$this, 'is_pro_active']);
        add_filter('extenderx_has_pro_license', [$this, 'has_pro_license']);
        add_filter('extenderx_get_pro_features', [$this, 'get_pro_features_list']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    /**
     * Check if pro plugin is active
     *
     * @return bool
     */
    public function is_pro_active() {
        return defined('XERO_EXTENDERX_PRO_VERSION');
    }

    /**
     * Check if user has valid pro license
     *
     * @return bool
     */
    public function has_pro_license() {
        // First check if pro plugin is active
        if (!$this->is_pro_active()) {
            // If no pro plugin, check free plugin Freemius for premium plan
            if (function_exists('xero_extenderx_fs')) {
                $freemius = xero_extenderx_fs();
                if ($freemius && method_exists($freemius, 'can_use_premium_code')) {
                    return $freemius->can_use_premium_code();
                }
            }
            return false;
        }

        // Pro plugin is active, check its Freemius instance
        if (function_exists('xero_extenderx_pro_fs')) {
            $freemius = xero_extenderx_pro_fs();
            if ($freemius && method_exists($freemius, 'can_use_premium_code')) {
                return $freemius->can_use_premium_code();
            }
        }

        return false;
    }

    /**
     * Check if a specific feature is premium
     *
     * @param string $feature_key
     * @return bool
     */
    public function is_premium_feature($feature_key) {
        return isset(self::PRO_FEATURES[$feature_key]);
    }

    /**
     * Check if user can access a premium feature
     *
     * @param string $feature_key
     * @return bool
     */
    public function can_use_premium_feature($feature_key) {
        if (!$this->is_premium_feature($feature_key)) {
            return true; // Free feature
        }

        return $this->has_pro_license();
    }

    /**
     * Get all pro features list
     *
     * @return array
     */
    public function get_pro_features_list() {
        return self::PRO_FEATURES;
    }

    /**
     * Get pro features by category
     *
     * @param string $category
     * @return array
     */
    public function get_pro_features_by_category($category) {
        return array_filter(self::PRO_FEATURES, function ($feature) use ($category) {
            return $feature['category'] === $category;
        });
    }

    /**
     * Get upgrade URL
     *
     * @param string $source
     * @return string
     */
    public function get_upgrade_url($source = 'plugin') {
        $base_url = 'https://wpxero.com/plugins/extenderx/pricing/';
        return add_query_arg([
            'utm_source' => 'plugin',
            'utm_medium' => $source,
            'utm_campaign' => 'upgrade'
        ], $base_url);
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('wpxero/extenderx/v1', '/pro-status', [
            'methods' => 'GET',
            'callback' => [$this, 'get_pro_status'],
            'permission_callback' => [$this, 'check_permission']
        ]);
    }

    /**
     * Check permission for REST API endpoint
     *
     * @return bool
     */
    public function check_permission() {
        return current_user_can('manage_options');
    }

    /**
     * Get pro status via REST API
     *
     * @return WP_REST_Response
     */
    public function get_pro_status() {
        $is_pro_active = $this->is_pro_active();
        $has_license = $this->has_pro_license();

        $response_data = [
            'is_pro_active' => $is_pro_active,
            'has_license' => $has_license,
            'upgrade_url' => $this->get_upgrade_url('dashboard'),
            'pro_features' => self::PRO_FEATURES,
            'debug' => [
                'pro_constant_defined' => defined('XERO_EXTENDERX_PRO_VERSION'),
                'freemius_function_exists' => function_exists('xero_extenderx_pro_fs'),
                'timestamp' => current_time('mysql')
            ]
        ];

        return rest_ensure_response($response_data);
    }
}
