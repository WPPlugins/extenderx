<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Skip to Content Link
 *
 * Injects a link at the very top of the document that is visually hidden
 * until it receives keyboard focus, letting keyboard and screen-reader users
 * jump past the header and navigation.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility
 * @since 1.9.0
 */
class SkipLink {
    use Singleton;

    /**
     * Sanitized skip-link settings.
     *
     * @var array
     */
    private $settings = [];

    /**
     * Wire the front-end hooks.
     *
     * Priority 1 on wp_body_open so the link precedes any other injected
     * markup — it is only useful as the first focusable element.
     *
     * @since 1.9.0
     */
    public function __construct() {
        $this->load_settings();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_body_open', [$this, 'render_link'], 1);
    }

    /**
     * Load and sanitize settings.
     *
     * @since 1.9.0
     * @return void
     */
    private function load_settings(): void {
        $stored = SettingsCache::instance()->get_setting('global.accessibility.skip_link', []);
        $stored = is_array($stored) ? $stored : [];

        // The skip link has no premium fields; the license flag is irrelevant
        // to this branch of the catalog.
        $clean = AccessibilityCatalog::sanitize(['skip_link' => $stored], false);
        $this->settings = $clean['skip_link'];
    }

    /**
     * Enqueue the stylesheet and its resolver script.
     *
     * The script is not decoration: browsers only move focus to a skip
     * target if that target is focusable, so it applies tabindex="-1" on
     * activation and falls back to a sensible landmark when the configured
     * selector matches nothing.
     *
     * @since 1.9.0
     * @return void
     */
    public function enqueue_assets(): void {
        wp_enqueue_style('xero-a11y-skip-link');
        wp_enqueue_script('xero-a11y-skip-link');
    }

    /**
     * Render the link.
     *
     * The stored target is a fragment or selector; anything that is not a
     * plain `#id` is resolved client-side by the toolbar bundle, so the href
     * written here is always a safe fragment.
     *
     * @since 1.9.0
     * @return void
     */
    public function render_link(): void {
        $target = (string) $this->settings['target'];
        $href   = '#' === substr($target, 0, 1) ? $target : '#' . ltrim($target, '.');
        ?>
        <a
            class="xero-a11y-skip-link"
            href="<?php echo esc_attr($href); ?>"
            data-xero-a11y-skip-target="<?php echo esc_attr($target); ?>"
        ><?php echo esc_html($this->settings['label']); ?></a>
        <?php
    }
}
