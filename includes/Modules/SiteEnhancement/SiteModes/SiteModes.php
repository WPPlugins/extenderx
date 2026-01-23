<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes;

use WPXERO\ExtenderX\Core\ModuleBase;
use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes\MaintenanceMode;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes\ComingSoonMode;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes\AgeVerification;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes\CookieConsent;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes\GDPRNotice;
use WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes\ExitIntent;

if (!defined('ABSPATH')) {
    exit;
}


/**
 * Site Modes Module
 *
 * Manages site modes and compliance features including maintenance mode,
 * coming soon, age verification, cookie consent, GDPR notices, and exit intent.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement
 * @since 1.0.0
 */
class SiteModes extends ModuleBase {
    use Singleton;

    /**
     * Settings key prefix for site modes features.
     * Note: Site modes use direct settings paths (e.g., 'maintenance_mode.enabled')
     * instead of nested paths like utilities
     *
     * @var string
     */
    private const SETTINGS_PREFIX = 'global.site_modes';

    /**
     * Setting key for global configuration.
     *
     * @var string
     */
    private const MODULE_SETTINGS_KEY = 'global';

    /**
     * Site modes feature mapping.
     * Settings format: {feature_key}.enabled (e.g., 'maintenance_mode.enabled')
     *
     * @var array<string, class-string>
     */
    private const SITE_MODE_FEATURES = [
        'maintenance_mode.enabled'  => MaintenanceMode::class,
        'coming_soon_mode.enabled'  => ComingSoonMode::class,
        'age_verification.enabled'  => AgeVerification::class,
        'cookie_consent.enabled'    => CookieConsent::class,
        'gdpr_notice.enabled'       => GDPRNotice::class,
        'exit_intent.enabled'       => ExitIntent::class,
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
     * Get the site mode features mapping.
     *
     * @since 1.0.0
     * @return array<string, class-string>
     */
    protected function get_features(): array {
        return self::SITE_MODE_FEATURES;
    }
}
