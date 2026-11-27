<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Marketing;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\ProFeatures;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sticky WhatsApp Button
 *
 * Free: phone, prefilled message, position, simple tooltip, button colour.
 * Pro (advanced): greeting popup with agent identity, business-hours
 *                 availability (online/offline), scheduling, targeting.
 *
 * @since 1.0.0
 */
class StickyWhatsAppButton {
    use Singleton;
    use MarketingVisibility;

    private const PRO_FEATURE_KEY = 'sticky_whatsapp_button_advanced';

    /** @var SettingsCache */
    private $settings_cache;

    /** @var bool */
    private $is_pro = false;

    /** @var array<string, mixed> */
    private $settings = [];

    /** @var array<string, mixed> */
    private $default_settings = [
        // Free.
        'phone'           => '',
        'message'         => 'Hello!',
        'position'        => 'bottom-left',
        'button_color'    => '#25D366',
        'show_tooltip'    => true,
        'tooltip_text'    => 'Chat with us',
        // Pro — greeting popup.
        'greeting_enabled' => false,
        'greeting_text'    => 'Hi there! 👋 How can we help you?',
        'greeting_delay'   => 3,
        'agent_name'       => 'Support',
        'agent_role'       => 'Typically replies within minutes',
        'agent_avatar'     => '',
        // Pro — business hours.
        'hours_enabled' => false,
        'hours_start'   => '09:00',
        'hours_end'     => '17:00',
        'hours_days'    => ['mon', 'tue', 'wed', 'thu', 'fri'],
        'online_text'   => 'Online',
        'offline_text'  => 'Away — leave a message',
        'hide_when_offline' => false,
        // Pro — scheduling.
        'schedule_enabled' => false,
        'start_date'       => '',
        'end_date'         => '',
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
        add_action('wp_footer', [$this, 'render_effect']);
    }

    private function load_settings() {
        $stored = $this->settings_cache->get_setting('global.marketing.sticky_whatsapp_button', []);
        if (!is_array($stored)) {
            $stored = [];
        }
        $this->settings = wp_parse_args($stored, $this->default_settings);
    }

    private function should_render(): bool {
        if (trim((string) $this->settings['phone']) === '') {
            return false;
        }
        if (!$this->is_pro) {
            return true;
        }
        if ($this->is_offline() && !empty($this->settings['hide_when_offline'])) {
            return false;
        }
        return $this->passes_targeting();
    }

    /**
     * Whether the current site time is outside configured business hours.
     * Returns false (i.e. "online") when business hours are disabled.
     */
    private function is_offline(): bool {
        if (!$this->is_pro || empty($this->settings['hours_enabled'])) {
            return false;
        }

        $days = is_array($this->settings['hours_days']) ? $this->settings['hours_days'] : [];
        $today = strtolower(substr((string) current_time('D'), 0, 3));
        if (!in_array($today, $days, true)) {
            return true;
        }

        $now   = (int) current_time('Hi');
        $start = (int) str_replace(':', '', (string) $this->settings['hours_start']);
        $end   = (int) str_replace(':', '', (string) $this->settings['hours_end']);

        return $now < $start || $now > $end;
    }

    public function enqueue_assets() {
        if (!$this->should_render()) {
            return;
        }

        wp_enqueue_style('xero-sticky-whatsapp-button');
        wp_enqueue_script('xero-sticky-whatsapp-button');

        wp_localize_script('xero-sticky-whatsapp-button', 'extenderxWhatsApp', [
            'greeting'      => $this->is_pro && !empty($this->settings['greeting_enabled']),
            'greetingDelay' => max(0, (int) $this->settings['greeting_delay']),
        ]);
    }

    public function render_effect() {
        if (!$this->should_render()) {
            return;
        }

        $phone        = preg_replace('/[^0-9]/', '', (string) $this->settings['phone']);
        $message      = (string) $this->settings['message'];
        $position     = in_array($this->settings['position'], ['bottom-left', 'bottom-right'], true)
            ? $this->settings['position'] : 'bottom-left';
        $button_color = sanitize_hex_color((string) $this->settings['button_color']) ?: '#25D366';
        $show_tooltip = (bool) $this->settings['show_tooltip'];
        $tooltip_text = (string) $this->settings['tooltip_text'];

        $whatsapp_url = 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);

        $greeting_enabled = $this->is_pro && !empty($this->settings['greeting_enabled']);
        $hours_enabled    = $this->is_pro && !empty($this->settings['hours_enabled']);
        $is_offline       = $this->is_offline();
?>
        <div id="xero-whatsapp-widget"
            class="xero-marketing-effect xero-whatsapp-<?php echo esc_attr($position); ?>"
            data-position="<?php echo esc_attr($position); ?>">

            <?php if ($greeting_enabled): ?>
                <div class="xero-whatsapp-popup" hidden>
                    <button class="xero-whatsapp-popup-close" aria-label="<?php esc_attr_e('Close', 'extenderx'); ?>">&times;</button>
                    <div class="xero-whatsapp-popup-header">
                        <?php if (trim((string) $this->settings['agent_avatar']) !== ''): ?>
                            <img class="xero-whatsapp-avatar" src="<?php echo esc_url((string) $this->settings['agent_avatar']); ?>" alt="" />
                        <?php else: ?>
                            <span class="xero-whatsapp-avatar xero-whatsapp-avatar-fallback" style="background-color: <?php echo esc_attr($button_color); ?>;">
                                <?php echo esc_html(mb_substr((string) $this->settings['agent_name'], 0, 1)); ?>
                            </span>
                        <?php endif; ?>
                        <div class="xero-whatsapp-agent">
                            <span class="xero-whatsapp-agent-name"><?php echo esc_html((string) $this->settings['agent_name']); ?></span>
                            <?php if ($hours_enabled): ?>
                                <span class="xero-whatsapp-status xero-whatsapp-status-<?php echo $is_offline ? 'offline' : 'online'; ?>">
                                    <?php echo esc_html($is_offline ? (string) $this->settings['offline_text'] : (string) $this->settings['online_text']); ?>
                                </span>
                            <?php else: ?>
                                <span class="xero-whatsapp-agent-role"><?php echo esc_html((string) $this->settings['agent_role']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="xero-whatsapp-popup-body"><?php echo esc_html((string) $this->settings['greeting_text']); ?></div>
                    <a class="xero-whatsapp-popup-cta" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener noreferrer" style="background-color: <?php echo esc_attr($button_color); ?>;">
                        <?php esc_html_e('Start Chat', 'extenderx'); ?>
                    </a>
                </div>
            <?php endif; ?>

            <a href="<?php echo esc_url($whatsapp_url); ?>"
                id="xero-whatsapp-button"
                class="xero-whatsapp-launcher"
                target="_blank"
                rel="noopener noreferrer"
                data-tooltip="<?php echo esc_attr($show_tooltip ? $tooltip_text : ''); ?>"
                data-greeting="<?php echo esc_attr($greeting_enabled ? '1' : '0'); ?>"
                style="background-color: <?php echo esc_attr($button_color); ?>;">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                </svg>
                <?php if ($show_tooltip): ?>
                    <span class="xero-whatsapp-tooltip"><?php echo esc_html($tooltip_text); ?></span>
                <?php endif; ?>
            </a>
        </div>
<?php
    }
}
