<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Accessibility Catalog
 *
 * Single source of truth for the `global.accessibility` settings subtree:
 * defaults, whitelists, and the free/pro boundary. Both the REST save path
 * (Core\Settings) and the runtime (Toolbar::get_config) sanitize through
 * here, so an expired license degrades to free behaviour in both directions
 * without either caller re-deriving the rules.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility
 * @since 1.9.0
 */
class AccessibilityCatalog {

    /**
     * Toolbar tools that are free for everyone.
     *
     * @var string[]
     */
    private const FREE_TOOLS = [
        'font_size',
        'readable_font',
        'text_align_left',
        'high_contrast',
        'negative_contrast',
        'grayscale',
        'light_background',
        'underline_links',
        'highlight_titles',
        'hide_images',
        'pause_animations',
    ];

    /**
     * Toolbar tools gated behind a premium license. Keys match the entries
     * registered in Core\ProFeatures::PRO_FEATURES.
     *
     * @var string[]
     */
    private const PRO_TOOLS = [
        'reading_guide',
        'reading_mask',
        'text_spacing',
        'content_scaling',
        'dyslexia_font',
        'big_cursor',
        'saturation',
    ];

    /**
     * Trigger icon styles available to premium users.
     *
     * @var string[]
     */
    private const ICONS = ['person', 'universal', 'eye', 'hand'];

    /**
     * Display order and grouping of the tools inside the panel. A flat list of
     * a dozen toggles is hard to scan; grouping by what the tool changes lets
     * a visitor find the one they need without reading every label.
     *
     * @var array<string, string[]>
     */
    private const GROUPS = [
        'text'    => ['font_size', 'readable_font', 'dyslexia_font', 'text_spacing', 'text_align_left'],
        'color'   => ['high_contrast', 'negative_contrast', 'grayscale', 'saturation', 'light_background'],
        'content' => ['underline_links', 'highlight_titles', 'hide_images', 'content_scaling'],
        'motion'  => ['pause_animations', 'reading_guide', 'reading_mask', 'big_cursor'],
    ];

    /**
     * One-click presets.
     *
     * Each names the tools it switches on and an optional text scale. Tools a
     * site has switched off — or that the licence does not cover — are skipped
     * when the profile is applied, so a profile always does as much as the
     * install allows rather than nothing at all.
     *
     * @var array<string, array{tools: string[], scale?: int}>
     */
    private const PROFILES = [
        'vision_impaired' => [
            'tools' => ['high_contrast', 'underline_links', 'highlight_titles'],
            'scale' => 130,
        ],
        'dyslexia' => [
            'tools' => ['dyslexia_font', 'readable_font', 'text_align_left', 'text_spacing'],
            'scale' => 110,
        ],
        'adhd_focus' => [
            'tools' => ['reading_mask', 'pause_animations', 'hide_images'],
        ],
        'seizure_safe' => [
            'tools' => ['pause_animations', 'saturation', 'grayscale'],
        ],
    ];

    /**
     * Tool groups, in display order.
     *
     * @since 1.9.0
     * @return array<string, string[]>
     */
    public static function groups(): array {
        return self::GROUPS;
    }

    /**
     * Profile definitions.
     *
     * @since 1.9.0
     * @return array<string, array>
     */
    public static function profiles(): array {
        return self::PROFILES;
    }

    /**
     * Default settings for the whole module.
     *
     * @since 1.9.0
     * @return array
     */
    public static function defaults(): array {
        $tools = [];
        foreach (self::FREE_TOOLS as $tool) {
            $tools[$tool] = true;
        }
        foreach (self::PRO_TOOLS as $tool) {
            $tools[$tool] = false;
        }

        return [
            'enabled' => true,
            'toolbar' => [
                'enabled'     => false,
                'position'    => 'right',
                'offset_y'    => 30,
                'tools'       => $tools,
                'profiles'    => true,
                'shortcut'    => true,
                // On by default and free: a widget that forgets a visitor's
                // contrast or text size on every page view makes them redo it
                // on every page, which defeats the point of offering it.
                'persistence' => true,
                'branding'    => [
                    'trigger_bg'         => '#1d4ed8',
                    'trigger_icon_color' => '#ffffff',
                    'icon'               => 'person',
                    'trigger_size'       => 48,
                    'hide_on_mobile'     => false,
                    'z_index'            => 99990,
                ],
            ],
            'skip_link' => [
                'enabled' => false,
                'target'  => '#main',
                'label'   => __('Skip to content', 'extenderx'),
            ],
            'focus_outlines' => [
                'enabled' => false,
                'color'   => '#1d4ed8',
                'width'   => 3,
                'offset'  => 2,
            ],
            'statement_generator' => [
                'enabled' => true,
            ],
            'alt_text_audit' => [
                'enabled' => true,
            ],
        ];
    }

    /**
     * Free toolbar tool keys.
     *
     * @since 1.9.0
     * @return string[]
     */
    public static function free_tools(): array {
        return self::FREE_TOOLS;
    }

    /**
     * Premium toolbar tool keys.
     *
     * @since 1.9.0
     * @return string[]
     */
    public static function pro_tools(): array {
        return self::PRO_TOOLS;
    }

    /**
     * Sanitize the `global.accessibility` subtree.
     *
     * Whitelists enums, clamps numerics, casts booleans, and forces every
     * pro-only field back to its default when the install is unlicensed.
     *
     * @since 1.9.0
     * @param array $in     Raw settings.
     * @param bool  $is_pro Whether a premium license is active.
     * @return array
     */
    public static function sanitize(array $in, bool $is_pro): array {
        $d = self::defaults();

        return [
            'enabled'             => isset($in['enabled']) ? (bool) $in['enabled'] : $d['enabled'],
            'toolbar'             => self::sanitize_toolbar(self::sub($in, 'toolbar'), $is_pro),
            'skip_link'           => self::sanitize_skip_link(self::sub($in, 'skip_link')),
            'focus_outlines'      => self::sanitize_focus_outlines(self::sub($in, 'focus_outlines')),
            'statement_generator' => [
                'enabled' => isset($in['statement_generator']['enabled'])
                    ? (bool) $in['statement_generator']['enabled']
                    : $d['statement_generator']['enabled'],
            ],
            'alt_text_audit' => [
                'enabled' => isset($in['alt_text_audit']['enabled'])
                    ? (bool) $in['alt_text_audit']['enabled']
                    : $d['alt_text_audit']['enabled'],
            ],
        ];
    }

    /**
     * Sanitize the toolbar sub-tree.
     *
     * @since 1.9.0
     * @param array $in     Raw toolbar settings.
     * @param bool  $is_pro Whether a premium license is active.
     * @return array
     */
    private static function sanitize_toolbar(array $in, bool $is_pro): array {
        $d = self::defaults()['toolbar'];

        $tools_in = self::sub($in, 'tools');
        $tools    = [];

        foreach (self::FREE_TOOLS as $tool) {
            $tools[$tool] = isset($tools_in[$tool])
                ? (bool) $tools_in[$tool]
                : $d['tools'][$tool];
        }

        // Pro tools are forced off without a license, so a downgraded site
        // can't keep shipping premium controls in the localized config.
        foreach (self::PRO_TOOLS as $tool) {
            $tools[$tool] = $is_pro && !empty($tools_in[$tool]);
        }

        return [
            'enabled'     => !empty($in['enabled']),
            'position'    => 'left' === ($in['position'] ?? $d['position']) ? 'left' : 'right',
            'offset_y'    => self::clamp_int($in['offset_y'] ?? $d['offset_y'], 0, 600),
            'tools'       => $tools,
            'profiles'    => isset($in['profiles']) ? (bool) $in['profiles'] : $d['profiles'],
            'shortcut'    => isset($in['shortcut']) ? (bool) $in['shortcut'] : $d['shortcut'],
            'persistence' => isset($in['persistence']) ? (bool) $in['persistence'] : $d['persistence'],
            'branding'    => self::sanitize_branding(self::sub($in, 'branding'), $is_pro),
        ];
    }

    /**
     * Sanitize the premium branding sub-tree. Every field falls back to its
     * default for unlicensed installs.
     *
     * @since 1.9.0
     * @param array $in     Raw branding settings.
     * @param bool  $is_pro Whether a premium license is active.
     * @return array
     */
    private static function sanitize_branding(array $in, bool $is_pro): array {
        $d = self::defaults()['toolbar']['branding'];

        if (!$is_pro) {
            return $d;
        }

        $icon = isset($in['icon']) ? (string) $in['icon'] : $d['icon'];

        return [
            'trigger_bg'         => sanitize_hex_color($in['trigger_bg'] ?? '') ?: $d['trigger_bg'],
            'trigger_icon_color' => sanitize_hex_color($in['trigger_icon_color'] ?? '') ?: $d['trigger_icon_color'],
            'icon'               => in_array($icon, self::ICONS, true) ? $icon : $d['icon'],
            'trigger_size'       => self::clamp_int($in['trigger_size'] ?? $d['trigger_size'], 36, 72),
            'hide_on_mobile'     => !empty($in['hide_on_mobile']),
            'z_index'            => self::clamp_int($in['z_index'] ?? $d['z_index'], 1000, 2147483000),
        ];
    }

    /**
     * Sanitize the skip-link sub-tree.
     *
     * The target is a CSS selector written into a link href, so characters
     * that could break out of the attribute or the selector are stripped
     * rather than escaped — the front end resolves it with querySelector and
     * falls back when it matches nothing.
     *
     * @since 1.9.0
     * @param array $in Raw skip-link settings.
     * @return array
     */
    private static function sanitize_skip_link(array $in): array {
        $d = self::defaults()['skip_link'];

        $target = isset($in['target']) ? (string) $in['target'] : $d['target'];
        $target = trim(str_replace(['<', '>', '"', "'", '{', '}', '\\'], '', $target));
        $target = mb_substr(sanitize_text_field($target), 0, 100);

        $label = isset($in['label']) ? (string) $in['label'] : $d['label'];
        $label = mb_substr(sanitize_text_field($label), 0, 80);

        return [
            'enabled' => !empty($in['enabled']),
            'target'  => '' !== $target ? $target : $d['target'],
            'label'   => '' !== $label ? $label : $d['label'],
        ];
    }

    /**
     * Sanitize the focus-outline sub-tree.
     *
     * @since 1.9.0
     * @param array $in Raw focus-outline settings.
     * @return array
     */
    private static function sanitize_focus_outlines(array $in): array {
        $d = self::defaults()['focus_outlines'];

        return [
            'enabled' => !empty($in['enabled']),
            'color'   => sanitize_hex_color($in['color'] ?? '') ?: $d['color'],
            'width'   => self::clamp_int($in['width'] ?? $d['width'], 1, 6),
            'offset'  => self::clamp_int($in['offset'] ?? $d['offset'], 0, 6),
        ];
    }

    /**
     * Fetch a nested array branch, guaranteeing an array back.
     *
     * @since 1.9.0
     * @param array  $in  Parent array.
     * @param string $key Branch key.
     * @return array
     */
    private static function sub(array $in, string $key): array {
        return isset($in[$key]) && is_array($in[$key]) ? $in[$key] : [];
    }

    /**
     * Clamp to an integer within [min, max].
     *
     * @since 1.9.0
     * @param mixed $value Raw value.
     * @param int   $min   Lower bound.
     * @param int   $max   Upper bound.
     * @return int
     */
    private static function clamp_int($value, int $min, int $max): int {
        return max($min, min($max, (int) $value));
    }
}
