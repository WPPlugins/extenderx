<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shared renderer for the full-page site modes (Maintenance & Coming Soon).
 *
 * Produces a complete, self-contained HTML document with inlined CSS and JS,
 * because these pages are emitted through wp_die() where enqueued front-end
 * assets are not output.
 *
 * @since 1.0.0
 */
class SiteModeFullPage {
    use Singleton;

    /** @var string Admin-bar label for the active mode, '' when none. */
    private $active_label = '';

    /**
     * Remind logged-in owners that a site mode is on. They bypass the
     * takeover and see the normal site, so without this it is easy to forget
     * that every visitor is getting the maintenance / coming-soon page.
     *
     * @param string $label Short label, e.g. "Maintenance mode on".
     */
    public function add_admin_bar_notice(string $label): void {
        if ('' !== $this->active_label) {
            return; // One reminder is enough; Maintenance wins (registered first).
        }
        $this->active_label = $label;

        add_action('admin_bar_menu', [$this, 'render_admin_bar_notice'], 100);
        add_action('wp_head', [$this, 'print_admin_bar_style']);
        add_action('admin_head', [$this, 'print_admin_bar_style']);
    }

    /**
     * @param \WP_Admin_Bar $admin_bar
     */
    public function render_admin_bar_notice($admin_bar): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $admin_bar->add_node([
            'id'     => 'extenderx-site-mode',
            'parent' => 'top-secondary',
            'title'  => esc_html($this->active_label),
            'href'   => admin_url('admin.php?page=extenderx#global-effects?tab=site-modes'),
        ]);
    }

    public function print_admin_bar_style(): void {
        if (!is_admin_bar_showing() || !current_user_can('manage_options')) {
            return;
        }
        echo '<style id="extenderx-site-mode-admin-bar">#wpadminbar #wp-admin-bar-extenderx-site-mode > .ab-item{background:#b32d2e;color:#fff}#wpadminbar #wp-admin-bar-extenderx-site-mode:hover > .ab-item{background:#8a2424;color:#fff}</style>';
    }

    /**
     * Emit the full-page document as the entire HTTP response and stop execution.
     *
     * Unlike wp_die(), this does NOT wrap the markup in WordPress's default
     * error-page shell — so the self-contained document fills the viewport
     * edge-to-edge instead of sitting inside a narrow, light-grey box.
     *
     * @param array<string, mixed> $args   Render arguments (already sanitized by caller).
     * @param int                  $status HTTP status code (e.g. 503 maintenance, 200 coming soon).
     */
    public function render_and_exit(array $args, int $status = 200): void {
        $html = $this->render($args);

        if (!headers_sent()) {
            nocache_headers();
            status_header($status);
            header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
            if ($status === 503) {
                header('Retry-After: 3600');
            }
        }

        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput — complete, already-escaped document.
        exit;
    }

    /**
     * Build the full-page HTML.
     *
     * @param array<string, mixed> $args Render arguments (already sanitized by caller).
     */
    public function render(array $args): string {
        $args = wp_parse_args($args, [
            'body_class'    => 'xero-fullpage',
            'preset'        => 'gradient-purple',
            'icon'          => '🚀',
            'heading'       => '',
            'message'       => '',
            'footer_text'   => '',
            'bg_start'      => '#667eea',
            'bg_end'        => '#764ba2',
            'text_color'    => '#ffffff',
            'accent_color'  => '#ffffff',
            'logo_url'      => '',
            'bg_image_url'  => '',
            'countdown'     => '',
            'social_links'  => [],
            'custom_css'    => '',
            'show_progress' => false,
        ]);

        $countdown_ts = $args['countdown'] !== '' ? (int) strtotime((string) $args['countdown']) : 0;

        ob_start();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo esc_html(wp_strip_all_tags((string) $args['heading'])); ?></title>
    <style>
        :root {
            --xero-bg-start: <?php echo esc_html($args['bg_start']); ?>;
            --xero-bg-end: <?php echo esc_html($args['bg_end']); ?>;
            --xero-text: <?php echo esc_html($args['text_color']); ?>;
            --xero-accent: <?php echo esc_html($args['accent_color']); ?>;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            position: relative;
            overflow: hidden;
            color: var(--xero-text);
            background: linear-gradient(135deg, var(--xero-bg-start) 0%, var(--xero-bg-end) 100%);
            <?php if ($args['bg_image_url'] !== ''): ?>
            background-image: linear-gradient(135deg, rgba(0,0,0,.55), rgba(0,0,0,.55)), url('<?php echo esc_url($args['bg_image_url']); ?>');
            background-size: cover;
            background-position: center;
            <?php endif; ?>
        }
        <?php if ($args['bg_image_url'] === ''): ?>
        /* Ambient depth: soft accent glows + faint dot grid. */
        body::before {
            content: ''; position: absolute; inset: 0; z-index: 0; pointer-events: none;
            background:
                radial-gradient(900px 600px at 12% 8%, color-mix(in srgb, var(--xero-accent) 22%, transparent), transparent 60%),
                radial-gradient(900px 600px at 88% 92%, color-mix(in srgb, var(--xero-accent) 16%, transparent), transparent 60%);
        }
        body::after {
            content: ''; position: absolute; inset: 0; z-index: 0; pointer-events: none; opacity: .35;
            background-image: radial-gradient(rgba(255,255,255,.08) 1px, transparent 1px);
            background-size: 26px 26px;
            -webkit-mask-image: radial-gradient(ellipse 80% 70% at 50% 50%, #000 30%, transparent 75%);
                    mask-image: radial-gradient(ellipse 80% 70% at 50% 50%, #000 30%, transparent 75%);
        }
        <?php endif; ?>
        .xero-fp-container {
            position: relative; z-index: 1;
            width: 100%;
            max-width: 600px;
            text-align: center;
            animation: xeroFpIn .9s cubic-bezier(.16,1,.3,1) both;
        }
        .xero-fp-logo { max-height: 76px; width: auto; margin: 0 auto 34px; display: block; }
        .xero-fp-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 116px; height: 116px; margin-bottom: 34px;
            font-size: 3.4rem; line-height: 1;
            border-radius: 28px;
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.14);
            box-shadow: 0 20px 60px -20px rgba(0,0,0,.55), inset 0 1px 0 rgba(255,255,255,.12);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            animation: xeroFpFloat 5s ease-in-out infinite;
        }
        .xero-fp-container h1 {
            font-size: clamp(2.1rem, 5.6vw, 3.4rem); font-weight: 800; letter-spacing: -.025em;
            line-height: 1.08; color: var(--xero-text); margin: 0;
        }
        .xero-fp-divider {
            width: 64px; height: 3px; margin: 24px auto 26px; border: 0; border-radius: 999px;
            background: linear-gradient(90deg, transparent, var(--xero-accent), transparent);
            opacity: .9;
        }
        .xero-fp-container p.xero-fp-message {
            font-size: clamp(1rem, 2.2vw, 1.18rem); line-height: 1.7; opacity: .82;
            max-width: 30em; margin: 0 auto;
        }
        .xero-fp-countdown { display: flex; justify-content: center; gap: 14px; margin-top: 42px; flex-wrap: wrap; }
        .xero-fp-unit { min-width: 84px; }
        .xero-fp-unit .v {
            display: block; font-size: clamp(1.7rem, 5vw, 2.7rem); font-weight: 700;
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.16);
            border-radius: 16px; padding: 16px 12px; backdrop-filter: blur(8px); font-variant-numeric: tabular-nums;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.1);
        }
        .xero-fp-unit .l { display: block; margin-top: 10px; font-size: .68rem; letter-spacing: .16em; text-transform: uppercase; opacity: .7; }
        .xero-fp-progress {
            position: relative; width: 100%; max-width: 320px; height: 6px; margin: 42px auto 0;
            background: rgba(255,255,255,.14); border-radius: 999px; overflow: hidden;
        }
        .xero-fp-progress span {
            position: absolute; top: 0; left: 0; height: 100%; width: 38%; border-radius: 999px;
            background: linear-gradient(90deg, color-mix(in srgb, var(--xero-accent) 55%, transparent), var(--xero-accent));
            box-shadow: 0 0 18px color-mix(in srgb, var(--xero-accent) 60%, transparent);
            animation: xeroFpLoad 1.8s ease-in-out infinite;
        }
        .xero-fp-social { display: flex; gap: 18px; justify-content: center; margin-top: 34px; flex-wrap: wrap; }
        .xero-fp-social a { color: var(--xero-text); opacity: .8; text-decoration: none; font-weight: 600; font-size: .9rem; border-bottom: 1px solid transparent; transition: opacity .15s ease, border-color .15s ease; }
        .xero-fp-social a:hover { opacity: 1; border-bottom-color: currentColor; }
        .xero-fp-footer { margin-top: 44px; font-size: .8rem; opacity: .6; letter-spacing: .01em; }
        @keyframes xeroFpIn { from { opacity: 0; transform: translateY(26px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes xeroFpFloat { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        @keyframes xeroFpLoad { 0% { left: -38%; } 100% { left: 100%; } }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation: none !important; } }
        <?php if ($args['custom_css'] !== ''): ?>
        <?php echo wp_strip_all_tags((string) $args['custom_css']); // phpcs:ignore — custom CSS is admin-authored ?>
        <?php endif; ?>
    </style>
</head>
<body class="<?php echo esc_attr($args['body_class'] . ' xero-preset-' . sanitize_html_class((string) $args['preset'])); ?>">
    <div class="xero-fp-container">
        <?php if ($args['logo_url'] !== ''): ?>
            <img class="xero-fp-logo" src="<?php echo esc_url($args['logo_url']); ?>" alt="" />
        <?php elseif ($args['icon'] !== ''): ?>
            <div class="xero-fp-icon" aria-hidden="true"><?php echo esc_html((string) $args['icon']); ?></div>
        <?php endif; ?>

        <?php if ($args['heading'] !== ''): ?>
            <h1><?php echo esc_html((string) $args['heading']); ?></h1>
        <?php endif; ?>

        <?php if ($args['heading'] !== '' && $args['message'] !== ''): ?>
            <hr class="xero-fp-divider" />
        <?php endif; ?>

        <?php if ($args['message'] !== ''): ?>
            <p class="xero-fp-message"><?php echo wp_kses_post((string) $args['message']); ?></p>
        <?php endif; ?>

        <?php if ($countdown_ts > 0): ?>
            <div class="xero-fp-countdown" data-deadline="<?php echo esc_attr((string) $countdown_ts); ?>">
                <div class="xero-fp-unit"><span class="v" data-days>00</span><span class="l"><?php esc_html_e('Days', 'extenderx'); ?></span></div>
                <div class="xero-fp-unit"><span class="v" data-hours>00</span><span class="l"><?php esc_html_e('Hours', 'extenderx'); ?></span></div>
                <div class="xero-fp-unit"><span class="v" data-minutes>00</span><span class="l"><?php esc_html_e('Minutes', 'extenderx'); ?></span></div>
                <div class="xero-fp-unit"><span class="v" data-seconds>00</span><span class="l"><?php esc_html_e('Seconds', 'extenderx'); ?></span></div>
            </div>
        <?php elseif (!empty($args['show_progress'])): ?>
            <div class="xero-fp-progress"><span></span></div>
        <?php endif; ?>

        <?php if (!empty($args['social_links']) && is_array($args['social_links'])): ?>
            <div class="xero-fp-social">
                <?php foreach ($args['social_links'] as $link):
                    $url   = isset($link['url']) ? esc_url((string) $link['url']) : '';
                    $label = isset($link['label']) ? esc_html((string) $link['label']) : '';
                    if ($url === '' || $label === '') { continue; }
                ?>
                    <a href="<?php echo $url; // already escaped ?>" target="_blank" rel="noopener noreferrer"><?php echo $label; ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($args['footer_text'] !== ''): ?>
            <div class="xero-fp-footer"><?php echo wp_kses_post((string) $args['footer_text']); ?></div>
        <?php endif; ?>
    </div>

    <?php if ($countdown_ts > 0): ?>
    <script>
        (function () {
            var box = document.querySelector('.xero-fp-countdown');
            if (!box) return;
            var deadline = parseInt(box.getAttribute('data-deadline'), 10) * 1000;
            var els = {
                days: box.querySelector('[data-days]'),
                hours: box.querySelector('[data-hours]'),
                minutes: box.querySelector('[data-minutes]'),
                seconds: box.querySelector('[data-seconds]')
            };
            function pad(n) { return (n < 10 ? '0' : '') + n; }
            function tick() {
                var diff = Math.max(0, deadline - Date.now());
                var s = Math.floor(diff / 1000);
                els.days.textContent = pad(Math.floor(s / 86400));
                els.hours.textContent = pad(Math.floor((s % 86400) / 3600));
                els.minutes.textContent = pad(Math.floor((s % 3600) / 60));
                els.seconds.textContent = pad(s % 60);
                if (diff <= 0) { clearInterval(timer); }
            }
            tick();
            var timer = setInterval(tick, 1000);
        })();
    </script>
    <?php endif; ?>
</body>
</html>
<?php
        return (string) ob_get_clean();
    }
}
