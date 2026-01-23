<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;

defined('ABSPATH') || exit;

class Appearance {
    use Singleton;

    const SETTINGS_PREFIX = 'global.appearance';
    const MODULE_SETTINGS_KEY = 'appearance';

    const APPEARANCE_FEATURES = [
        'dark_mode' => DarkMode::class,
        'light_mode_toggle' => LightModeToggle::class,
        'auto_mode' => AutoMode::class,
        'high_contrast_mode' => HighContrastMode::class,
        'reading_mode' => ReadingMode::class,
        'night_shift_mode' => NightShiftMode::class,
        'fade_in_load' => FadeInLoad::class,
        'smooth_transitions' => SmoothTransitions::class,
        'skeleton_loader' => SkeletonLoader::class,
        'preloader' => Preloader::class,
        'glassmorphism' => Glassmorphism::class,
        'blur_background_on_scroll' => BlurBackgroundOnScroll::class,
        'dim_background_on_idle' => DimBackgroundOnIdle::class,
    ];

    public function __construct() {
        $this->init_features();
    }

    private function init_features() {
        $settings = get_option('extenderx_settings', []);
        $appearance_settings = $settings['global']['appearance'] ?? [];

        foreach (self::APPEARANCE_FEATURES as $feature_key => $feature_class) {
            $feature_settings = $appearance_settings[$feature_key] ?? [];
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
