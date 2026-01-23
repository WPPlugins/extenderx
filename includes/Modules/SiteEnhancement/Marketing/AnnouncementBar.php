<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class AnnouncementBar {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('xero-announcement-bar');
        wp_enqueue_script('xero-announcement-bar');
    }

    public function render_effect() {
        $settings = get_option('extenderx_settings', []);
        $message = $settings['global']['marketing']['announcement_bar']['message'] ?? 'Important announcement!';
        $bg_color = $settings['global']['marketing']['announcement_bar']['bg_color'] ?? '#667eea';
        $text_color = $settings['global']['marketing']['announcement_bar']['text_color'] ?? '#ffffff';
        $position = $settings['global']['marketing']['announcement_bar']['position'] ?? 'top';
        $dismissible = $settings['global']['marketing']['announcement_bar']['dismissible'] ?? true;
?>
        <div id="xero-announcement-bar"
            class="xero-marketing-effect xero-announcement-<?php echo esc_attr($position); ?>"
            data-position="<?php echo esc_attr($position); ?>"
            data-dismissible="<?php echo esc_attr($dismissible ? '1' : '0'); ?>"
            style="background-color: <?php echo esc_attr($bg_color); ?>; color: <?php echo esc_attr($text_color); ?>;">
            <div class="xero-announcement-content">
                <span class="xero-announcement-message"><?php echo wp_kses_post($message); ?></span>
                <?php if ($dismissible): ?>
                    <button class="xero-announcement-close" aria-label="Close">×</button>
                <?php endif; ?>
            </div>
        </div>
<?php
    }
}
