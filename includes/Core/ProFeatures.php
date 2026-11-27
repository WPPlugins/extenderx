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
        // Site-wide scroll-linked engine (parallax / scrub / sticky). Distinct
        // key from the gutenberg-category `scroll_effects` to avoid collision.
        'scroll_animations' => [
            'title' => 'Scroll-Linked Effects',
            'category' => 'animations',
            'type' => 'premium'
        ],

        // Seasonal Effects tiering:
        //   FREE  → winter_snow, autumn_leaves, hearts_falling (NOT listed here;
        //           unlisted keys are free via can_use_premium_feature()).
        //   PREMIUM → every other effect below, plus the *_advanced control
        //           bundles (scheduling / page targeting / theme) that gate the
        //           pro controls on the free effects.
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

        // New seasonal effects (all premium).
        'spring_petals' => [
            'title' => 'Spring Petals',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'summer_sun' => [
            'title' => 'Summer Bubbles',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'black_friday' => [
            'title' => 'Black Friday Confetti',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'cyber_monday' => [
            'title' => 'Cyber Monday Confetti',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],

        // Advanced control bundles for the FREE seasonal effects. The effect
        // itself is free; scheduling, page targeting and theme/color
        // customization are gated on these keys (mirrors the Marketing
        // *_advanced convention above).
        'winter_snow_advanced' => [
            'title' => 'Winter Snow — Advanced',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'autumn_leaves_advanced' => [
            'title' => 'Autumn Leaves — Advanced',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],
        'hearts_falling_advanced' => [
            'title' => 'Falling Hearts — Advanced',
            'category' => 'seasonal_effects',
            'type' => 'premium'
        ],

        // Site Modes — base modules are FREE; advanced option bundles are gated
        // in-class on the global license (ProFeatures::has_pro_license()), so no
        // per-feature premium keys are registered here.

        // Marketing — base modules are FREE except the two below, which are
        // premium in full. (The *_advanced keys gate option bundles on the
        // remaining free modules.)
        'sticky_whatsapp_button' => [
            'title' => 'Sticky WhatsApp Button',
            'category' => 'marketing',
            'type' => 'premium'
        ],
        'global_countdown_banner' => [
            'title' => 'Global Countdown Banner',
            'category' => 'marketing',
            'type' => 'premium'
        ],
        'announcement_bar_advanced' => [
            'title' => 'Announcement Bar — Advanced',
            'category' => 'marketing',
            'type' => 'premium'
        ],
        'floating_cta_button_advanced' => [
            'title' => 'Floating CTA Button — Advanced',
            'category' => 'marketing',
            'type' => 'premium'
        ],
        'sticky_whatsapp_button_advanced' => [
            'title' => 'Sticky WhatsApp Button — Advanced',
            'category' => 'marketing',
            'type' => 'premium'
        ],
        'cta_pulse_animation_advanced' => [
            'title' => 'CTA Pulse Animation — Advanced',
            'category' => 'marketing',
            'type' => 'premium'
        ],
        'global_countdown_banner_advanced' => [
            'title' => 'Global Countdown Banner — Advanced',
            'category' => 'marketing',
            'type' => 'premium'
        ],
        'scrollbar_style' => [
            'title' => 'Scrollbar Styler',
            'category' => 'ui_enhancements',
            'type' => 'premium'
        ],
        'smooth_scrolling' => [
            'title' => 'Smooth Scrolling (Lenis)',
            'category' => 'ui_enhancements',
            'type' => 'premium'
        ],
        // Premium counterpart of the FREE Back to Top feature — same options,
        // scrolls the other way.
        'scroll_to_bottom' => [
            'title' => 'Scroll to Bottom Button',
            'category' => 'ui_enhancements',
            'type' => 'premium'
        ],

        // Site Modes — Maintenance & Coming Soon modes are FREE; the
        // compliance / engagement modes below are premium.
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

        // Gutenberg Extensions — advanced scroll motion (entrance animations are free)
        'scroll_effects' => [
            'title' => 'Advanced Scroll Effects',
            'category' => 'gutenberg',
            'type' => 'premium'
        ],

        // Accessibility — the toolbar itself, the skip link and the focus
        // indicator are FREE. The extra visitor tools, the widget branding
        // bundle, preference persistence and the two admin tools below are
        // premium. Keys match AccessibilityCatalog::PRO_TOOLS.
        'reading_guide' => [
            'title' => 'Reading Guide',
            'category' => 'accessibility',
            'type' => 'premium'
        ],
        'reading_mask' => [
            'title' => 'Reading Mask',
            'category' => 'accessibility',
            'type' => 'premium'
        ],
        'text_spacing' => [
            'title' => 'Text Spacing Controls',
            'category' => 'accessibility',
            'type' => 'premium'
        ],
        'content_scaling' => [
            'title' => 'Content Zoom',
            'category' => 'accessibility',
            'type' => 'premium'
        ],
        'dyslexia_font' => [
            'title' => 'Dyslexia-Friendly Font',
            'category' => 'accessibility',
            'type' => 'premium'
        ],
        'big_cursor' => [
            'title' => 'Large Cursor',
            'category' => 'accessibility',
            'type' => 'premium'
        ],
        'saturation' => [
            'title' => 'Saturation Controls',
            'category' => 'accessibility',
            'type' => 'premium'
        ],
        'toolbar_advanced' => [
            'title' => 'Accessibility Toolbar — Branding',
            'category' => 'accessibility',
            'type' => 'premium'
        ],
        'statement_generator' => [
            'title' => 'Accessibility Statement Generator',
            'category' => 'accessibility',
            'type' => 'premium'
        ],
        'alt_text_audit' => [
            'title' => 'Alt Text Audit',
            'category' => 'accessibility',
            'type' => 'premium'
        ],

        // Save & Go — the five navigation actions, both editor integrations
        // and every supported post type are FREE. The premium tier adds the
        // three actions below, the advanced button placements, choosing the
        // default action, and Bulk Add. Keys match
        // SaveAndGoCatalog::pro_feature_key() / its PRO_KEY_* constants.
        'save_go_duplicate' => [
            'title' => 'Save & Duplicate',
            'category' => 'save_and_go',
            'type' => 'premium'
        ],
        'save_go_return' => [
            'title' => 'Save & Return',
            'category' => 'save_and_go',
            'type' => 'premium'
        ],
        'save_go_new_window' => [
            'title' => 'Save & View in New Window',
            'category' => 'save_and_go',
            'type' => 'premium'
        ],
        'save_go_button_modes' => [
            'title' => 'Save & Go — Classic Editor Button Placement',
            'category' => 'save_and_go',
            'type' => 'premium'
        ],
        'save_go_default_action' => [
            'title' => 'Save & Go — Default Action',
            'category' => 'save_and_go',
            'type' => 'premium'
        ],
        // Matches the `bulk_add` settings subkey so ModuleBase derives this
        // key from the Save & Go feature map on its own.
        'bulk_add' => [
            'title' => 'Save & Go — Bulk Add',
            'category' => 'save_and_go',
            'type' => 'premium'
        ],

        // Custom Code — HTML/CSS/JS header & footer snippets are FREE.
        // Advanced capabilities are premium.
        'custom_code_php' => [
            'title' => 'PHP Code Snippets',
            'category' => 'custom_code',
            'type' => 'premium'
        ],
        'custom_code_conditions' => [
            'title' => 'Conditional Logic',
            'category' => 'custom_code',
            'type' => 'premium'
        ],
        'custom_code_scheduling' => [
            'title' => 'Snippet Scheduling',
            'category' => 'custom_code',
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
        // The constant is the signal: extenderx-pro defines it at load, so it
        // is present exactly while that plugin is active. Note this method is
        // itself the callback registered on the `extenderx_is_pro_active`
        // filter, so it must never apply that filter here — doing so would
        // recurse into itself.
        return defined('XERO_EXTENDERX_PRO_VERSION');
    }

    /**
     * Check if user has valid pro license
     *
     * Premium requires BOTH the pro plugin and a valid license. The pro
     * plugin is the gate: deactivating it locks premium features, which is
     * the contract the dashboard and the docs describe.
     *
     * This deliberately does not fall back to the free plugin's own Freemius
     * license. That fallback unlocked every premium feature on installs where
     * the pro plugin was absent or deactivated, which made deactivating pro
     * change nothing at all.
     *
     * @return bool
     */
    public function has_pro_license() {
        if (!$this->is_pro_active()) {
            return false;
        }

        // Pro is active — ask its Freemius instance (which reuses the free
        // plugin's instance when one exists, see extenderx-pro/loader.php).
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
