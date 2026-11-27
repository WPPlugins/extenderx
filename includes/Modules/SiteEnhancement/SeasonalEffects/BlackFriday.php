<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SeasonalEffects;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Black Friday — premium sales-event effect.
 *
 * Shares the Sales Confetti engine bundle (xero-sales-confetti) with Cyber
 * Monday; the `data-theme` attribute selects the palette.
 */
class BlackFriday {
    use Singleton;

    /** @var SettingsCache */
    private $settings_cache;

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_effect']);
    }

    public function enqueue_assets() {
        if (!EffectTargeting::passes('black_friday')) {
            return;
        }
        wp_enqueue_style('xero-sales-confetti');
        wp_enqueue_script('xero-sales-confetti');
    }

    public function render_effect() {
        if (!EffectTargeting::passes('black_friday')) {
            return;
        }
        $intensity = $this->settings_cache->get_setting('global.seasonal_effects.black_friday.intensity', 60);
        $speed     = $this->settings_cache->get_setting('global.seasonal_effects.black_friday.speed', 50);
        $layer     = $this->settings_cache->get_setting('global.seasonal_effects.black_friday.layer', 'front');
        $layer     = ('behind' === $layer) ? 'behind' : 'front';
?>
        <canvas id="xero-sales-black-friday"
            class="xero-seasonal-effect xero-sales-confetti xero-layer-<?php echo esc_attr($layer); ?>"
            data-theme="blackfriday"
            data-intensity="<?php echo esc_attr($intensity); ?>"
            data-speed="<?php echo esc_attr($speed); ?>">
        </canvas>
<?php
    }
}
