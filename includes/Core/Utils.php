<?php

namespace WPXERO\ExtenderX\Core;

use WPXERO\ExtenderX\Traits\Singleton;

defined('ABSPATH') || exit;

class Utils {
    use Singleton;

    /**
     * Settings cache instance
     *
     * @var SettingsCache
     */
    private $settings_cache;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
    }

    /**
     * Get ExtenderX setting value by path
     * Uses centralized caching to prevent excessive database queries
     *
     * @param string $path Dot notation path (e.g., 'global.appearance.dark_mode')
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function extenderx_get_setting($path, $default = null) {
        return $this->settings_cache->get_setting($path, $default);
    }

    // Usage examples:
    // $dark_mode = extenderx_get_setting('global.appearance.dark_mode', false);
    // $entrance_fx = extenderx_get_setting('global.animations.entrance_effects', false);
    // $elementor = extenderx_get_setting('builder_support.elementor.enabled', true);
}
