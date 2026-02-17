<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements;

use WPXERO\ExtenderX\Core\ModuleBase;
use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements\BackToTop;
use WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements\StickyHeader;
use WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements\StickyFooter;
use WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements\ScrollProgress;
use WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements\SmartScroll;
use WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements\CustomCursor;
use WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements\CursorHoverEffects;
use WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements\CursorGrabMode;
use WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements\ScrollbarStyler;
use WPXERO\ExtenderX\Modules\SiteEnhancement\UIEnhancements\SmoothScrolling;

if (!defined('ABSPATH')) {
    exit;
}

class UIEnhancements extends ModuleBase {
    use Singleton;

    private const SETTINGS_PREFIX = 'global.ui_enhancements';
    private const MODULE_SETTINGS_KEY = 'global';

    private const UI_ENHANCEMENT_FEATURES = [
        'back_to_top.enabled'          => BackToTop::class,
        'sticky_header.enabled'        => StickyHeader::class,
        'sticky_footer.enabled'        => StickyFooter::class,
        'scroll_progress.enabled'      => ScrollProgress::class,
        'smart_scroll.enabled'         => SmartScroll::class,
        'custom_cursor.enabled'        => CustomCursor::class,
        'cursor_hover_effects.enabled' => CursorHoverEffects::class,
        'cursor_grab_mode.enabled'     => CursorGrabMode::class,
        'scrollbar_styler.enabled'     => ScrollbarStyler::class,
        'smooth_scrolling.enabled'     => SmoothScrolling::class,
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
     * Get the UI enhancement features mapping.
     *
     * @since 1.0.0
     * @return array<string, class-string>
     */
    protected function get_features(): array {
        return self::UI_ENHANCEMENT_FEATURES;
    }
}
