<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\ModuleBase;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\DarkMode;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\SkeletonLoader;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance\Preloader;

defined('ABSPATH') || exit;

class Appearance extends ModuleBase {
    use Singleton;

    private const SETTINGS_PREFIX = 'global.appearance';
    private const MODULE_SETTINGS_KEY = 'global';

    private const APPEARANCE_FEATURES = [
        'dark_mode.enabled' => DarkMode::class,
        'skeleton_loader.enabled' => SkeletonLoader::class,
        'preloader.enabled' => Preloader::class,
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
