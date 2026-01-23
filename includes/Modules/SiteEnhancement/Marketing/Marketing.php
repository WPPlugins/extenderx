<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing;

use WPXERO\ExtenderX\Traits\Singleton;

defined('ABSPATH') || exit;
class Marketing {
    use Singleton;

    const SETTINGS_PREFIX = 'global.marketing';
    const MODULE_SETTINGS_KEY = 'marketing';

    const MARKETING_FEATURES = [
        'announcement_bar' => AnnouncementBar::class,
        'floating_cta_button' => FloatingCtaButton::class,
        'sticky_whatsapp_button' => StickyWhatsAppButton::class,
        'cta_pulse_animation' => CtaPulseAnimation::class,
        'global_countdown_banner' => GlobalCountdownBanner::class,
    ];

    public function __construct() {
        $this->init_features();
    }

    private function init_features() {
        $settings = get_option('extenderx_settings', []);
        $marketing_settings = $settings['global']['marketing'] ?? [];

        foreach (self::MARKETING_FEATURES as $feature_key => $feature_class) {
            $feature_settings = $marketing_settings[$feature_key] ?? [];
            $enabled = $feature_settings['enabled'] ?? false;

            if ($enabled && class_exists($feature_class)) {
                $instance = $feature_class::instance();

                if (method_exists($instance, 'render_effect')) {
                    add_action('wp_footer', [$instance, 'render_effect'], 9999);
                }
            }
        }
    }
}
