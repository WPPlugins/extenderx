<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\ModuleBase;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing\AnnouncementBar;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing\FloatingCtaButton;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing\StickyWhatsAppButton;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing\CtaPulseAnimation;
use WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing\GlobalCountdownBanner;

defined('ABSPATH') || exit;

class Marketing extends ModuleBase {
    use Singleton;

    private const SETTINGS_PREFIX = 'global.marketing';
    private const MODULE_SETTINGS_KEY = 'global';

    private const MARKETING_FEATURES = [
        'announcement_bar.enabled' => AnnouncementBar::class,
        'floating_cta_button.enabled' => FloatingCtaButton::class,
        'sticky_whatsapp_button.enabled' => StickyWhatsAppButton::class,
        'cta_pulse_animation.enabled' => CtaPulseAnimation::class,
        'global_countdown_banner.enabled' => GlobalCountdownBanner::class,
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
     * Get the marketing features mapping.
     *
     * @since 1.0.0
     * @return array<string, class-string>
     */
    protected function get_features(): array {
        return self::MARKETING_FEATURES;
    }
}
