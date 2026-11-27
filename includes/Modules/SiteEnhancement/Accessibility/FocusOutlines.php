<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced Focus Outlines
 *
 * Gives every keyboard-focused element a visible indicator, for themes whose
 * styles remove or wash out the browser default. Scoped to :focus-visible so
 * mouse users don't see outlines on click.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility
 * @since 1.9.0
 */
class FocusOutlines {
    use Singleton;

    /**
     * Sanitized focus-outline settings.
     *
     * @var array
     */
    private $settings = [];

    /**
     * Wire the front-end hooks.
     *
     * @since 1.9.0
     */
    public function __construct() {
        $this->load_settings();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Load and sanitize settings.
     *
     * @since 1.9.0
     * @return void
     */
    private function load_settings(): void {
        $stored = SettingsCache::instance()->get_setting('global.accessibility.focus_outlines', []);
        $stored = is_array($stored) ? $stored : [];

        // No premium fields on this branch; the license flag is irrelevant.
        $clean = AccessibilityCatalog::sanitize(['focus_outlines' => $stored], false);
        $this->settings = $clean['focus_outlines'];
    }

    /**
     * Enqueue the stylesheet with its custom properties.
     *
     * @since 1.9.0
     * @return void
     */
    public function enqueue_assets(): void {
        wp_enqueue_style('xero-a11y-focus');

        $color  = sanitize_hex_color($this->settings['color']) ?: '#1d4ed8';
        $width  = (int) $this->settings['width'];
        $offset = (int) $this->settings['offset'];

        wp_add_inline_style('xero-a11y-focus', ":root {
            --xero-a11y-focus-color: {$color};
            --xero-a11y-focus-width: {$width}px;
            --xero-a11y-focus-offset: {$offset}px;
        }");
    }
}
