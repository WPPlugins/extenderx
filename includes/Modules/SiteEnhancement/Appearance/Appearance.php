<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\ModuleBase;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\DarkMode;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\LightModeToggle;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\AutoMode;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\HighContrastMode;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\ReadingMode;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\NightShiftMode;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\FadeInLoad;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\SmoothTransitions;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\SkeletonLoader;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\Preloader;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\Glassmorphism;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\BlurBackgroundOnScroll;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\DimBackgroundOnIdle;

defined('ABSPATH') || exit;

class Appearance extends ModuleBase {
    use Singleton;

    private const SETTINGS_PREFIX = 'global.appearance';
    private const MODULE_SETTINGS_KEY = 'global';

    private const APPEARANCE_FEATURES = [
        'dark_mode.enabled' => DarkMode::class,
        'light_mode_toggle.enabled' => LightModeToggle::class,
        'auto_mode.enabled' => AutoMode::class,
        'high_contrast_mode.enabled' => HighContrastMode::class,
        'reading_mode.enabled' => ReadingMode::class,
        'night_shift_mode.enabled' => NightShiftMode::class,
        'fade_in_load.enabled' => FadeInLoad::class,
        'smooth_transitions.enabled' => SmoothTransitions::class,
        'skeleton_loader.enabled' => SkeletonLoader::class,
        'preloader.enabled' => Preloader::class,
        'glassmorphism.enabled' => Glassmorphism::class,
        'blur_background_on_scroll.enabled' => BlurBackgroundOnScroll::class,
        'dim_background_on_idle.enabled' => DimBackgroundOnIdle::class,
    ];

    /**
     * Get the settings key for checking if module is enabled.
     *
     * @since 1.0.0
     * @return string
     */
    protected function get_module_settings_key(): string {
        return self::MODULE_SETTINGS_KEY;
    }

    /**
     * Get the settings prefix for module features.
     *
     * @since 1.0.0
     * @return string
     */
    protected function get_settings_prefix(): string {
        return self::SETTINGS_PREFIX;
    }

    /**
     * Get the appearance features mapping.
     *
     * @since 1.0.0
     * @return array<string, class-string>
     */
    protected function get_features(): array {
        return self::APPEARANCE_FEATURES;
    }
}
