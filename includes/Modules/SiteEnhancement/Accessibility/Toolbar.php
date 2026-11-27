<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Core\ProFeatures;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Accessibility Toolbar
 *
 * Floating trigger that opens a panel of display preferences (text size,
 * contrast, grayscale, link underlines, readable font, paused motion).
 * Preferences are applied as classes and custom properties on the root
 * element by the front-end bundle; nothing is written to arbitrary inline
 * styles and no request leaves the visitor's browser.
 *
 * Deliberately not page-targetable: unlike marketing widgets, these controls
 * are only useful if they are reachable from wherever a visitor happens to
 * land, so the only visibility option is the premium hide-on-mobile flag.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility
 * @since 1.9.0
 */
class Toolbar {
    use Singleton;

    /**
     * Sanitized toolbar settings.
     *
     * @var array
     */
    private $settings = [];

    /**
     * Whether a premium license is active.
     *
     * @var bool
     */
    private $is_pro = false;

    /**
     * Root font scale bounds, in percent.
     */
    private const SCALE_MIN = 90;
    private const SCALE_MAX = 160;
    private const SCALE_STEP = 10;

    /**
     * Wire the front-end hooks.
     *
     * @since 1.9.0
     */
    public function __construct() {
        $this->is_pro = ProFeatures::instance()->has_pro_license();
        $this->load_settings();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_toolbar']);
    }

    /**
     * Load and sanitize settings.
     *
     * Re-sanitizing on read (rather than trusting the stored option) means a
     * lapsed licence stops shipping premium tools immediately, without
     * waiting for the next settings save.
     *
     * @since 1.9.0
     * @return void
     */
    private function load_settings(): void {
        $stored = SettingsCache::instance()->get_setting('global.accessibility.toolbar', []);
        $stored = is_array($stored) ? $stored : [];

        $clean = AccessibilityCatalog::sanitize(['toolbar' => $stored], $this->is_pro);
        $this->settings = $clean['toolbar'];
    }

    /**
     * Toolbar tools that are switched on, in display order.
     *
     * @since 1.9.0
     * @return string[]
     */
    private function active_tools(): array {
        $order = array_merge(
            AccessibilityCatalog::free_tools(),
            AccessibilityCatalog::pro_tools()
        );

        return array_values(array_filter($order, function ($tool) {
            return !empty($this->settings['tools'][$tool]);
        }));
    }

    /**
     * Active tools that come from the premium tier.
     *
     * Settings are sanitized on load, so these are already empty for an
     * unlicensed install; the check is what keeps the premium bundle off the
     * page entirely.
     *
     * @since 1.9.0
     * @return string[]
     */
    /**
     * @font-face rules for the bundled OpenDyslexic (SIL OFL 1.1, see
     * assets/fonts/opendyslexic/OFL.txt).
     *
     * Served from the plugin itself — no third-party request. Browsers only
     * download a face once an element actually uses it, so the ~100 KB file
     * is fetched only after a visitor turns the Dyslexia font tool on.
     * Declared from PHP (not the SCSS) so the URL points at the plugin's
     * assets folder wherever WordPress is installed.
     *
     * @return string CSS.
     */
    private function dyslexia_font_face(): string {
        $base = XERO_EXTENDERX_ASSETS_URL . 'fonts/opendyslexic/';
        $ver  = rawurlencode(XERO_EXTENDERX_VERSION);
        $css  = '';
        foreach (['Regular' => 400, 'Bold' => 700] as $style => $weight) {
            $css .= sprintf(
                '@font-face{font-family:"OpenDyslexic";src:local("OpenDyslexic %1$s"),local("OpenDyslexic-%1$s"),url("%2$s") format("woff2");font-weight:%3$d;font-style:normal;font-display:swap;}',
                $style,
                esc_url_raw($base . 'OpenDyslexic-' . $style . '.woff2?ver=' . $ver),
                $weight
            );
        }
        return $css;
    }

    private function active_pro_tools(): array {
        return array_values(array_intersect(
            $this->active_tools(),
            AccessibilityCatalog::pro_tools()
        ));
    }

    /**
     * Profiles that can do something on this install.
     *
     * A profile is reduced to the tools actually available here, and dropped
     * entirely if none of them are. Offering a one-click preset that silently
     * changes nothing would be worse than not offering it.
     *
     * @since 1.9.0
     * @return array<string, array>
     */
    private function active_profiles(): array {
        if (empty($this->settings['profiles'])) {
            return [];
        }

        $available = $this->active_tools();
        $out       = [];

        foreach (AccessibilityCatalog::profiles() as $key => $profile) {
            $tools = array_values(array_intersect($profile['tools'], $available));

            if (empty($tools)) {
                continue;
            }

            $out[$key] = [
                'tools' => $tools,
                'scale' => isset($profile['scale']) ? (int) $profile['scale'] : 0,
            ];
        }

        return $out;
    }

    /**
     * Runtime configuration handed to the front-end bundle.
     *
     * Already license-filtered: premium tools are stripped by
     * AccessibilityCatalog::sanitize() before they reach this array.
     *
     * @since 1.9.0
     * @return array
     */
    public function get_config(): array {
        $config = [
            'tools'       => $this->active_tools(),
            'profiles'    => $this->active_profiles(),
            'shortcut'    => (bool) $this->settings['shortcut'],
            'persistence' => (bool) $this->settings['persistence'],
            'scale'       => [
                'min'  => self::SCALE_MIN,
                'max'  => self::SCALE_MAX,
                'step' => self::SCALE_STEP,
            ],
            'i18n' => [
                'textSize'    => __('Text size: %s%%', 'extenderx'),
                'toolOn'      => __('%s turned on', 'extenderx'),
                'toolOff'     => __('%s turned off', 'extenderx'),
                'resetDone'   => __('All accessibility preferences reset', 'extenderx'),
                'profileOn'   => __('%s profile applied', 'extenderx'),
                'profileOff'  => __('%s profile turned off', 'extenderx'),
                'activeCount' => __('%s settings active', 'extenderx'),
            ],
        ];

        /**
         * Filter the accessibility toolbar's front-end configuration.
         *
         * @since 1.9.0
         * @param array $config Runtime configuration.
         */
        return apply_filters('extenderx_a11y_toolbar_config', $config);
    }

    /**
     * Enqueue the toolbar bundle and its dynamic custom properties.
     *
     * @since 1.9.0
     * @return void
     */
    public function enqueue_assets(): void {
        if (empty($this->active_tools())) {
            return;
        }

        wp_enqueue_script('xero-a11y-toolbar');
        wp_enqueue_style('xero-a11y-toolbar');

        // The premium bundle ships separately and is only requested when a
        // licence is active and at least one premium tool is switched on, so
        // free installs never download it.
        if ($this->is_pro && !empty($this->active_pro_tools())) {
            wp_enqueue_script('xero-a11y-toolbar-pro');
            wp_enqueue_style('xero-a11y-toolbar-pro');

            if (in_array('dyslexia_font', $this->active_pro_tools(), true)) {
                wp_add_inline_style('xero-a11y-toolbar-pro', $this->dyslexia_font_face());
            }
        }

        $inline_css = $this->generate_inline_css();
        if ('' !== $inline_css) {
            wp_add_inline_style('xero-a11y-toolbar', $inline_css);
        }

        wp_localize_script('xero-a11y-toolbar', 'extenderxA11yToolbarSettings', $this->get_config());
    }

    /**
     * Build the custom properties that position and brand the widget.
     *
     * @since 1.9.0
     * @return string
     */
    private function generate_inline_css(): string {
        $branding = $this->settings['branding'];

        $offset_y = (int) $this->settings['offset_y'];
        $size     = (int) $branding['trigger_size'];
        $z_index  = (int) $branding['z_index'];
        $bg       = sanitize_hex_color($branding['trigger_bg']) ?: '#1d4ed8';
        $fg       = sanitize_hex_color($branding['trigger_icon_color']) ?: '#ffffff';

        $css = ":root {
            --xero-a11y-offset-y: {$offset_y}px;
            --xero-a11y-trigger-size: {$size}px;
            --xero-a11y-trigger-bg: {$bg};
            --xero-a11y-trigger-fg: {$fg};
            --xero-a11y-z-index: {$z_index};
        }";

        if (!empty($branding['hide_on_mobile'])) {
            $css .= '
            @media (max-width: 782px) {
                #xero-a11y-root { display: none !important; }
            }';
        }

        return $css;
    }

    /**
     * Render the trigger and panel.
     *
     * @since 1.9.0
     * @return void
     */
    public function render_toolbar(): void {
        $tools = $this->active_tools();

        if (empty($tools)) {
            return;
        }

        $position = 'left' === $this->settings['position'] ? 'left' : 'right';
        $labels   = self::tool_labels();
        ?>
        <?php
        // Colour filters are painted by this fixed overlay via backdrop-filter
        // rather than by a filter on <html> or <body>: a filtered ancestor
        // becomes the containing block for its fixed descendants, which
        // detaches sticky headers and floating widgets from the viewport.
        ?>
        <div class="xero-a11y-filter-layer" data-xero-a11y-filter aria-hidden="true"></div>

        <div id="xero-a11y-root" class="xero-a11y-root xero-a11y-pos-<?php echo esc_attr($position); ?>">
            <button
                type="button"
                id="xero-a11y-trigger"
                class="xero-a11y-trigger"
                aria-expanded="false"
                aria-controls="xero-a11y-panel"
            >
                <span class="xero-a11y-trigger__icon" aria-hidden="true"><?php
                    echo $this->get_trigger_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is hardcoded.
                ?></span>
                <span class="screen-reader-text"><?php
                    esc_html_e('Open accessibility tools', 'extenderx');
                ?></span>
            </button>

            <div
                id="xero-a11y-panel"
                class="xero-a11y-panel"
                role="dialog"
                aria-label="<?php esc_attr_e('Accessibility tools', 'extenderx'); ?>"
                hidden
            >
                <div class="xero-a11y-panel__header">
                    <h2 class="xero-a11y-panel__title"><?php
                        esc_html_e('Accessibility tools', 'extenderx');
                    ?></h2>
                    <button
                        type="button"
                        class="xero-a11y-close"
                        data-xero-a11y-close
                    >
                        <span aria-hidden="true">&times;</span>
                        <span class="screen-reader-text"><?php
                            esc_html_e('Close accessibility tools', 'extenderx');
                        ?></span>
                    </button>
                </div>

                <div class="xero-a11y-panel__body">
                    <?php
                    $profiles = $this->active_profiles();
                    if (!empty($profiles)) :
                        $profile_labels = self::profile_labels();
                        ?>
                        <section class="xero-a11y-section">
                            <h3 class="xero-a11y-section__title"><?php
                                esc_html_e('Quick profiles', 'extenderx');
                            ?></h3>
                            <div class="xero-a11y-profiles" role="group" aria-label="<?php
                                esc_attr_e('Quick profiles', 'extenderx');
                            ?>">
                                <?php foreach ($profiles as $key => $profile) : ?>
                                    <?php if (!isset($profile_labels[$key])) {
                                        continue;
                                    } ?>
                                    <button
                                        type="button"
                                        class="xero-a11y-profile"
                                        data-xero-a11y-profile="<?php echo esc_attr($key); ?>"
                                        aria-pressed="false"
                                    >
                                        <span class="xero-a11y-profile__icon" aria-hidden="true"><?php
                                            echo self::get_icon($key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is hardcoded.
                                        ?></span>
                                        <span class="xero-a11y-profile__label"><?php
                                            echo esc_html($profile_labels[$key]);
                                        ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if (in_array('font_size', $tools, true)) : ?>
                        <section class="xero-a11y-section">
                            <h3 class="xero-a11y-section__title"><?php
                                esc_html_e('Text size', 'extenderx');
                            ?></h3>
                            <div class="xero-a11y-stepper" role="group" aria-label="<?php
                                esc_attr_e('Text size', 'extenderx');
                            ?>">
                                <button type="button" class="xero-a11y-step" data-xero-a11y-scale="down">
                                    <span aria-hidden="true">A&minus;</span>
                                    <span class="screen-reader-text"><?php
                                        esc_html_e('Decrease text size', 'extenderx');
                                    ?></span>
                                </button>
                                <span class="xero-a11y-stepper__value" data-xero-a11y-scale-value>100%</span>
                                <button type="button" class="xero-a11y-step" data-xero-a11y-scale="up">
                                    <span aria-hidden="true">A+</span>
                                    <span class="screen-reader-text"><?php
                                        esc_html_e('Increase text size', 'extenderx');
                                    ?></span>
                                </button>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php foreach (AccessibilityCatalog::groups() as $group => $group_tools) : ?>
                        <?php
                        // Only the tools this install actually offers, minus
                        // font_size which has its own stepper above.
                        $shown = array_values(array_filter($group_tools, function ($tool) use ($tools, $labels) {
                            return 'font_size' !== $tool
                                && isset($labels[$tool])
                                && in_array($tool, $tools, true);
                        }));

                        if (empty($shown)) {
                            continue;
                        }
                        ?>
                        <section class="xero-a11y-section">
                            <h3 class="xero-a11y-section__title"><?php
                                echo esc_html(self::group_label($group));
                            ?></h3>
                            <div class="xero-a11y-tools">
                                <?php foreach ($shown as $tool) : ?>
                                    <button
                                        type="button"
                                        class="xero-a11y-tool"
                                        data-xero-a11y-tool="<?php echo esc_attr($tool); ?>"
                                        aria-pressed="false"
                                    >
                                        <span class="xero-a11y-tool__icon" aria-hidden="true"><?php
                                            echo self::get_icon($tool); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is hardcoded.
                                        ?></span>
                                        <span class="xero-a11y-tool__label"><?php
                                            echo esc_html($labels[$tool]);
                                        ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>

                <div class="xero-a11y-panel__footer">
                    <span class="xero-a11y-count" data-xero-a11y-count hidden></span>
                    <?php if (!empty($this->settings['shortcut'])) : ?>
                        <span class="xero-a11y-kbd"><kbd>Alt</kbd> + <kbd>A</kbd></span>
                    <?php endif; ?>
                    <button type="button" class="xero-a11y-reset" data-xero-a11y-reset>
                        <?php esc_html_e('Reset all', 'extenderx'); ?>
                    </button>
                </div>

                <p class="screen-reader-text" data-xero-a11y-live aria-live="polite"></p>
            </div>
        </div>
        <?php
    }

    /**
     * Human-readable label for every tool.
     *
     * @since 1.9.0
     * @return array<string, string>
     */
    private static function tool_labels(): array {
        return [
            'high_contrast'     => __('High contrast', 'extenderx'),
            'negative_contrast' => __('Invert colors', 'extenderx'),
            'grayscale'         => __('Grayscale', 'extenderx'),
            'light_background'  => __('Light background', 'extenderx'),
            'underline_links'   => __('Underline links', 'extenderx'),
            'highlight_titles'  => __('Highlight headings', 'extenderx'),
            'hide_images'       => __('Hide images', 'extenderx'),
            'readable_font'     => __('Readable font', 'extenderx'),
            'text_align_left'   => __('Align text left', 'extenderx'),
            'pause_animations'  => __('Pause animations', 'extenderx'),
            'reading_guide'     => __('Reading guide', 'extenderx'),
            'reading_mask'      => __('Reading mask', 'extenderx'),
            'text_spacing'      => __('Text spacing', 'extenderx'),
            'content_scaling'   => __('Zoom content', 'extenderx'),
            'dyslexia_font'     => __('Dyslexia font', 'extenderx'),
            'big_cursor'        => __('Large cursor', 'extenderx'),
            'saturation'        => __('Low saturation', 'extenderx'),
        ];
    }

    /**
     * Human-readable label for every profile.
     *
     * @since 1.9.0
     * @return array<string, string>
     */
    private static function profile_labels(): array {
        return [
            'vision_impaired' => __('Low vision', 'extenderx'),
            'dyslexia'        => __('Dyslexia', 'extenderx'),
            'adhd_focus'      => __('Focus', 'extenderx'),
            'seizure_safe'    => __('Seizure safe', 'extenderx'),
        ];
    }

    /**
     * Heading for a tool group.
     *
     * @since 1.9.0
     * @param string $group Group key.
     * @return string
     */
    private static function group_label(string $group): string {
        switch ($group) {
            case 'text':
                return __('Text', 'extenderx');
            case 'color':
                return __('Color', 'extenderx');
            case 'content':
                return __('Content', 'extenderx');
            case 'motion':
                return __('Motion & reading', 'extenderx');
        }

        return $group;
    }

    /**
     * Inline SVG for a tool or profile.
     *
     * Icons are drawn from a single 24-unit grid and inherit currentColor, so
     * a tile reads the same whether it is on or off.
     *
     * @since 1.9.0
     * @param string $key Tool or profile key.
     * @return string
     */
    private static function get_icon(string $key): string {
        $open = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">';

        $paths = [
            // Profiles.
            'vision_impaired' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
            'dyslexia'        => '<path d="M4 7V5h16v2"/><path d="M9 19h6"/><path d="M12 5v14"/>',
            'adhd_focus'      => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="M12 3v3M12 18v3M3 12h3M18 12h3"/>',
            'seizure_safe'    => '<path d="M12 2 4 13h7l-1 9 9-12h-7l1-8Z"/><path d="m3 3 18 18"/>',

            // Tools.
            'high_contrast'     => '<circle cx="12" cy="12" r="9"/><path d="M12 3v18a9 9 0 0 0 0-18Z" fill="currentColor" stroke="none"/>',
            'negative_contrast' => '<circle cx="12" cy="12" r="9"/><path d="M12 3a9 9 0 0 1 0 18Z" fill="currentColor" stroke="none"/><path d="M3 12h18"/>',
            'grayscale'         => '<circle cx="9" cy="12" r="7"/><circle cx="15" cy="12" r="7"/>',
            'saturation'        => '<path d="M12 3s6 6.4 6 10a6 6 0 0 1-12 0c0-3.6 6-10 6-10Z"/>',
            'light_background'  => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
            'underline_links'   => '<path d="M7 5v6a5 5 0 0 0 10 0V5"/><path d="M5 19h14"/>',
            'highlight_titles'  => '<path d="M6 4v16M18 4v16M6 12h12"/>',
            'hide_images'       => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="m3 16 5-5 4 4"/><path d="m2 2 20 20"/>',
            'readable_font'     => '<path d="M4 20 12 4l8 16"/><path d="M7 14h10"/>',
            'text_align_left'   => '<path d="M4 6h16M4 12h10M4 18h13"/>',
            'text_spacing'      => '<path d="M4 7h16M4 17h16"/><path d="m8 10 4-3 4 3M8 14l4 3 4-3"/>',
            'dyslexia_font'     => '<path d="M4 20 12 4l8 16"/><path d="M7 14h10"/><path d="M3 22h18"/>',
            'content_scaling'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/><path d="M8 11h6M11 8v6"/>',
            'pause_animations'  => '<circle cx="12" cy="12" r="9"/><path d="M10 9v6M14 9v6"/>',
            'reading_guide'     => '<path d="M3 12h18"/><path d="m9 8 3-3 3 3M9 16l3 3 3-3"/>',
            'reading_mask'      => '<rect x="2" y="9" width="20" height="6" rx="1"/><path d="M2 4h20M2 20h20"/>',
            'big_cursor'        => '<path d="M5 3v14l4-4 3 7 3-1-3-7h5Z"/>',
        ];

        $path = $paths[$key] ?? '<circle cx="12" cy="12" r="9"/>';

        return $open . $path . '</svg>';
    }

    /**
     * Trigger icon markup.
     *
     * @since 1.9.0
     * @return string
     */
    private function get_trigger_icon(): string {
        switch ($this->settings['branding']['icon']) {
            case 'universal':
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v4"/><path d="M8 10h8"/><path d="m9 18 3-6 3 6"/></svg>';
            case 'eye':
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
            case 'hand':
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 11V6a2 2 0 0 0-4 0v5"/><path d="M14 10V4a2 2 0 0 0-4 0v6"/><path d="M10 10.5V6a2 2 0 0 0-4 0v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/></svg>';
            case 'person':
            default:
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="4.5" r="2"/><path d="M5 9h14"/><path d="M12 9v6"/><path d="m9 21 3-6 3 6"/></svg>';
        }
    }
}
