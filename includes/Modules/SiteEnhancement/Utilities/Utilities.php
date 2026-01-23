<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Utilities;

use WPXERO\ExtenderX\Core\ModuleBase;
use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Utilities\DisableRightClick;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Utilities\DisableCopy;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Utilities\DisableInspect;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Utilities\DisableImageDrag;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Utilities\HideAdminBar;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Site Enhancement Module
 *
 * Manages site security and protection features including right-click protection,
 * copy protection, and inspect element protection.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement
 * @since 1.0.0
 */
class Utilities extends ModuleBase {
    use Singleton;

    /**
     * Settings key prefix for utility protection features.
     *
     * @var string
     */
    private const SETTINGS_PREFIX = 'global.utility_protection';

    /**
     * Setting key for global configuration.
     *
     * @var string
     */
    private const MODULE_SETTINGS_KEY = 'global';

    /**
     * Protection feature mapping.
     *
     * @var array<string, class-string>
     */
    private const PROTECTION_FEATURES = [
        'disable_right_click.enabled' => DisableRightClick::class,
        'disable_text_copy.enabled'   => DisableCopy::class,
        'disable_inspect.enabled'     => DisableInspect::class,
        'disable_image_drag.enabled'  => DisableImageDrag::class,
        'hide_admin_bar.enabled'      => HideAdminBar::class,
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
     * Get the protection features mapping.
     *
     * @since 1.0.0
     * @return array<string, class-string>
     */
    protected function get_features(): array {
        return self::PROTECTION_FEATURES;
    }
}
