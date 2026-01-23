<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Animations;

use WPXERO\ExtenderX\Traits\Singleton;

defined('ABSPATH') || exit;

class Animations {
    use Singleton;

    const SETTINGS_PREFIX = 'global.animations';
    const MODULE_SETTINGS_KEY = 'animations';

    const ANIMATION_FEATURES = [
        'entrance_effects' => EntranceEffects::class,
        'parallax_effects' => ParallaxEffects::class,
        'floating_effects' => FloatingElements::class,
    ];

    public function __construct() {
        $this->init_features();
    }

    private function init_features() {
        $settings = get_option('extenderx_settings', []);
        $animation_settings = $settings['global']['animations'] ?? [];

        foreach (self::ANIMATION_FEATURES as $feature_key => $feature_class) {
            $feature_settings = $animation_settings[$feature_key] ?? [];
            $enabled = $feature_settings['enabled'] ?? false;

            if ($enabled && class_exists($feature_class)) {
                $instance = $feature_class::instance();

                if (method_exists($instance, 'render_effect')) {
                    add_action('wp_footer', [$instance, 'render_effect'], 9999);
                }
            }
        }
    }
}
