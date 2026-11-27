<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility;

use WPXERO\ExtenderX\Core\ModuleBase;
use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility\Toolbar;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility\SkipLink;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility\FocusOutlines;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility\StatementGenerator;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility\AltTextAudit;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Accessibility Module
 *
 * Visitor-facing accessibility tools: a floating toolbar of display
 * preferences, a skip-to-content link, and a site-wide focus indicator.
 *
 * Unlike the other site-enhancement categories (which alias 'global' and so
 * only re-check the master switch), this module carries a real module-level
 * toggle at `global.accessibility.enabled`, following the CustomCode
 * precedent.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility
 * @since 1.9.0
 */
class Accessibility extends ModuleBase {
    use Singleton;

    private const SETTINGS_PREFIX = 'global.accessibility';
    private const MODULE_SETTINGS_KEY = 'global.accessibility';

    /**
     * Feature map. Premium features are additionally gated by ModuleBase
     * against Core\ProFeatures — an unlicensed install never instantiates
     * them, so their hooks and REST routes are never registered.
     *
     * @var array<string, class-string>
     */
    private const ACCESSIBILITY_FEATURES = [
        'toolbar.enabled'             => Toolbar::class,
        'skip_link.enabled'           => SkipLink::class,
        'focus_outlines.enabled'      => FocusOutlines::class,
        'statement_generator.enabled' => StatementGenerator::class,
        'alt_text_audit.enabled'      => AltTextAudit::class,
    ];

    /**
     * Get the settings key for checking if module is enabled.
     *
     * @since 1.9.0
     * @return string
     */
    protected function get_module_settings_key(): string {
        return self::MODULE_SETTINGS_KEY;
    }

    /**
     * Get the settings prefix for module features.
     *
     * @since 1.9.0
     * @return string
     */
    protected function get_settings_prefix(): string {
        return self::SETTINGS_PREFIX;
    }

    /**
     * Get the accessibility features mapping.
     *
     * @since 1.9.0
     * @return array<string, class-string>
     */
    protected function get_features(): array {
        return self::ACCESSIBILITY_FEATURES;
    }
}
