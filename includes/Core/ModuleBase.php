<?php

namespace WPXERO\ExtenderX\Core;

use WPXERO\ExtenderX\Core\Utils;
use WPXERO\ExtenderX\Core\ProFeatures;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base Module Class
 *
 * Abstract base class for all modules. Provides common functionality
 * for feature initialization and settings management.
 *
 * @package WPXERO\ExtenderX\Core
 * @since 1.0.0
 */
abstract class ModuleBase {

    /**
     * Utils instance.
     *
     * @var Utils
     */
    protected Utils $utils;

    /**
     * Pro Features instance.
     *
     * @var ProFeatures
     */
    protected ProFeatures $pro_features;

    /**
     * Initialize the module.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->utils = Utils::instance();
        $this->pro_features = ProFeatures::instance();

        if (!$this->is_site_enhancement_enabled()) {
            return;
        }

        if (!$this->is_module_enabled()) {
            return;
        }

        $this->initialize_features();
    }

    /**
     * Get the settings key for checking if module is enabled.
     *
     * @since 1.0.0
     * @return string
     */
    abstract protected function get_module_settings_key(): string;

    /**
     * Get the settings prefix for module features.
     *
     * @since 1.0.0
     * @return string
     */
    abstract protected function get_settings_prefix(): string;

    /**
     * Get the feature mapping array.
     *
     * @since 1.0.0
     * @return array<string, class-string> Array of feature_key => class_name pairs.
     */
    abstract protected function get_features(): array;

    /**
     * Check if the module is enabled.
     *
     * @since 1.0.0
     * @return bool True if module is enabled, false otherwise.
     */
    protected function is_module_enabled(): bool {
        $module_key = $this->get_module_settings_key();
        $setting_path = $module_key . '.enabled';
        return (bool) $this->utils->extenderx_get_setting($setting_path);
    }

    /**
     * Initialize all enabled features.
     *
     * @since 1.0.0
     * @return void
     */
    protected function initialize_features(): void {
        foreach ($this->get_features() as $feature_key => $feature_class) {
            if ($this->is_feature_enabled($feature_key) && $this->can_use_feature($feature_key)) {
                $feature_class::instance();
            }
        }
    }

    /**
     * Check if user can use a feature (considering pro restrictions)
     *
     * @since 1.5.0
     * @param string $feature_key The feature identifier key.
     * @return bool True if user can use the feature, false otherwise.
     */
    protected function can_use_feature(string $feature_key): bool {
        $clean_key = str_replace('.enabled', '', $feature_key);
        return $this->pro_features->can_use_premium_feature($clean_key);
    }

    /**
     * Check if a specific feature is enabled.
     *
     * @since 1.0.0
     * @param string $feature_key The feature identifier key.
     * @return bool True if feature is enabled, false otherwise.
     */
    protected function is_feature_enabled(string $feature_key): bool {
        $setting_key = sprintf('%s.%s', $this->get_settings_prefix(), $feature_key);
        return (bool) $this->utils->extenderx_get_setting($setting_key);
    }

    /**
     * Check if site enhancement is enabled.
     *
     * @since 1.0.0
     * @return bool True if global is enabled, false otherwise.
     */
    protected function is_site_enhancement_enabled(): bool {
        return (bool) $this->utils->extenderx_get_setting('global.enabled');
    }
}
