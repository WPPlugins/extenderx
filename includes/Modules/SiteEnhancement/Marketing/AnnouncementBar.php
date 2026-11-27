<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\ProFeatures;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Announcement Bar
 *
 * Free: single message, colors, position, dismissible, font size.
 * Pro (advanced): scheduling, rotating messages, CTA button, animation style,
 *                 dismiss persistence, device/role/page targeting.
 *
 * @since 1.0.0
 */
class AnnouncementBar {
    use Singleton;
    use MarketingVisibility;

    private const PRO_FEATURE_KEY = 'announcement_bar_advanced';

    /** @var SettingsCache */
    private $settings_cache;

    /** @var bool */
    private $is_pro = false;

    /** @var array<string, mixed> */
    private $settings = [];

    /** @var array<string, mixed> */
    private $default_settings = [
        // Free.
        'message'      => 'Important announcement!',
        'bg_color'     => '#667eea',
        'text_color'   => '#ffffff',
        'position'     => 'top',
        'dismissible'  => true,
        'font_size'    => 14,
        // Pro — scheduling.
        'schedule_enabled' => false,
        'start_date'       => '',
        'end_date'         => '',
        // Pro — rotating messages.
        'rotate_enabled'  => false,
        'messages'        => [],
        'rotate_interval' => 5,
        // Pro — call to action.
        'cta_enabled' => false,
        'cta_text'    => 'Learn More',
        'cta_url'     => '',
        // Pro — presentation.
        'animation'    => 'slide',
        'dismiss_days' => 0,
        // Pro — targeting.
        'show_on_mobile'       => true,
        'show_on_tablet'       => true,
        'show_on_desktop'      => true,
        'visible_to_roles'     => [],
        'exclude_page_ids'     => [],
        'exclude_url_patterns' => [],
    ];

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        $this->is_pro         = ProFeatures::instance()->can_use_premium_feature(self::PRO_FEATURE_KEY);
        $this->load_settings();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_body_open', [$this, 'render_effect']);
    }

    private function load_settings() {
        $stored = $this->settings_cache->get_setting('global.marketing.announcement_bar', []);
        if (!is_array($stored)) {
            $stored = [];
        }
        $this->settings = wp_parse_args($stored, $this->default_settings);
    }

    /**
     * Whether the bar should render on the current request.
     * Scheduling and targeting only apply for Pro users.
     */
    private function should_render(): bool {
        if (trim((string) $this->settings['message']) === '' && empty($this->settings['messages'])) {
            return false;
        }

        if (!$this->is_pro) {
            return true;
        }

        return $this->passes_targeting();
    }

    /**
     * Build the list of messages to display.
     *
     * @return string[]
     */
    private function get_messages(): array {
        $messages = [];
        $primary  = trim((string) $this->settings['message']);
        if ($primary !== '') {
            $messages[] = $primary;
        }

        if ($this->is_pro && !empty($this->settings['rotate_enabled']) && is_array($this->settings['messages'])) {
            foreach ($this->settings['messages'] as $extra) {
                $extra = trim((string) $extra);
                if ($extra !== '') {
                    $messages[] = $extra;
                }
            }
        }

        return $messages ?: [$primary];
    }

    public function enqueue_assets() {
        if (!$this->should_render()) {
            return;
        }

        wp_enqueue_style('xero-announcement-bar');
        wp_enqueue_script('xero-announcement-bar');

        wp_localize_script('xero-announcement-bar', 'extenderxAnnouncementBar', [
            'rotate'         => $this->is_pro && !empty($this->settings['rotate_enabled']),
            'rotateInterval' => max(2, (int) $this->settings['rotate_interval']),
            'dismissDays'    => $this->is_pro ? max(0, (int) $this->settings['dismiss_days']) : 0,
            'animation'      => $this->is_pro ? sanitize_key((string) $this->settings['animation']) : 'slide',
        ]);
    }

    public function render_effect() {
        if (!$this->should_render()) {
            return;
        }

        $messages    = $this->get_messages();
        $bg_color    = sanitize_hex_color((string) $this->settings['bg_color']) ?: '#667eea';
        $text_color  = sanitize_hex_color((string) $this->settings['text_color']) ?: '#ffffff';
        $position    = in_array($this->settings['position'], ['top', 'bottom'], true) ? $this->settings['position'] : 'top';
        $dismissible = (bool) $this->settings['dismissible'];
        $font_size   = max(10, min(28, (int) $this->settings['font_size']));
        $animation   = $this->is_pro ? sanitize_key((string) $this->settings['animation']) : 'slide';

        $cta_enabled = $this->is_pro && !empty($this->settings['cta_enabled']) && trim((string) $this->settings['cta_url']) !== '';
?>
        <div id="xero-announcement-bar"
            class="xero-marketing-effect xero-announcement-<?php echo esc_attr($position); ?> xero-anim-<?php echo esc_attr($animation); ?>"
            data-position="<?php echo esc_attr($position); ?>"
            data-dismissible="<?php echo esc_attr($dismissible ? '1' : '0'); ?>"
            style="background-color: <?php echo esc_attr($bg_color); ?>; color: <?php echo esc_attr($text_color); ?>; font-size: <?php echo esc_attr($font_size); ?>px;">
            <div class="xero-announcement-content">
                <span class="xero-announcement-messages">
                    <?php foreach ($messages as $index => $msg): ?>
                        <span class="xero-announcement-message<?php echo 0 === $index ? ' is-active' : ''; ?>"><?php echo wp_kses_post($msg); ?></span>
                    <?php endforeach; ?>
                </span>
                <?php if ($cta_enabled): ?>
                    <a class="xero-announcement-cta" href="<?php echo esc_url((string) $this->settings['cta_url']); ?>" style="color: <?php echo esc_attr($text_color); ?>;">
                        <?php echo esc_html((string) $this->settings['cta_text']); ?>
                    </a>
                <?php endif; ?>
                <?php if ($dismissible): ?>
                    <button class="xero-announcement-close" aria-label="<?php esc_attr_e('Close', 'extenderx'); ?>">&times;</button>
                <?php endif; ?>
            </div>
        </div>
<?php
    }
}
