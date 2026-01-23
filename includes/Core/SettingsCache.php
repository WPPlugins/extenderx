<?php

namespace WPXERO\ExtenderX\Core;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings Cache Manager
 *
 * Centralized settings caching to prevent excessive get_option() calls
 * and reduce database queries.
 *
 * @package WPXERO\ExtenderX\Core
 * @since 1.5.1
 */
class SettingsCache {
    use Singleton;

    /**
     * Cached settings
     *
     * @var array|null
     */
    private static $cached_settings = null;

    /**
     * Cache hit counter for debugging
     *
     * @var int
     */
    private static $cache_hits = 0;

    /**
     * Constructor
     */
    public function __construct() {
        // Clear cache when settings are updated
        add_action('update_option_extenderx_settings', [$this, 'clear_cache'], 10, 0);
    }

    /**
     * Get all settings with caching
     *
     * @return array
     */
    public function get_all_settings() {
        if (self::$cached_settings === null) {
            self::$cached_settings = get_option('extenderx_settings', []);
        } else {
            self::$cache_hits++;
        }

        return self::$cached_settings;
    }

    /**
     * Get specific setting by path with caching
     *
     * @param string $path Dot notation path (e.g., 'global.appearance.dark_mode')
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get_setting($path, $default = null) {
        $settings = $this->get_all_settings();
        $keys = explode('.', $path);

        foreach ($keys as $key) {
            if (isset($settings[$key])) {
                $settings = $settings[$key];
            } else {
                return $default;
            }
        }

        return $settings;
    }

    /**
     * Clear the settings cache
     *
     * @return void
     */
    public function clear_cache() {
        self::$cached_settings = null;
        self::$cache_hits = 0;
    }

    /**
     * Get cache statistics for debugging
     *
     * @return array
     */
    public function get_cache_stats() {
        return [
            'is_cached' => self::$cached_settings !== null,
            'cache_hits' => self::$cache_hits,
        ];
    }
}
