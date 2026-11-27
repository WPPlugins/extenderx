<?php

namespace WPXERO\ExtenderX\Core;

defined('ABSPATH') || exit;

/**
 * Shared page-rules evaluator (include / exclude lists).
 *
 * One list — `exclude_page_ids`, `exclude_post_ids`, `exclude_url_patterns` —
 * applied in one of two modes via `page_filter_mode`:
 *   - 'exclude' (default): the feature runs everywhere except the listed pages;
 *   - 'include': the feature runs only on the listed pages.
 * Empty lists mean no restriction in either mode, so switching modes without
 * selections can never blank the whole site.
 *
 * Single source of truth for every feature offering the admin page-rules panel
 * (marketing modules, site modes, seasonal effects, UI enhancements, preloader
 * / skeleton loader, Site-Wide Animations). Matching semantics: queried-object
 * ID match, `*`-wildcard match against REQUEST_URI.
 *
 * `page_filter_mode` intentionally falls back to 'exclude' when absent so
 * feature defaults arrays don't need the key — stored settings pass it through
 * once the user picks a mode.
 *
 * @package WPXERO\ExtenderX\Core
 * @since 1.10.0
 */
class PageFilter {

    /**
     * Whether the page rules block the feature on the current request.
     *
     * @param array $settings Flat feature settings holding the filter keys.
     * @return bool True when the feature should NOT run here.
     */
    public static function blocks(array $settings): bool {
        return self::blocks_by(static function ($key, $default) use ($settings) {
            return $settings[$key] ?? $default;
        });
    }

    /**
     * Same as {@see blocks()} but reads via a getter — for callers that
     * resolve settings lazily (e.g. SeasonalEffects\EffectTargeting).
     *
     * @param callable $get function (string $key, mixed $default): mixed
     * @return bool True when the feature should NOT run here.
     */
    public static function blocks_by(callable $get): bool {
        $page_ids = $get('exclude_page_ids', []);
        $post_ids = $get('exclude_post_ids', []);
        $patterns = $get('exclude_url_patterns', []);

        $page_ids = is_array($page_ids) ? $page_ids : [];
        $post_ids = is_array($post_ids) ? $post_ids : [];
        $patterns = is_array($patterns) ? $patterns : [];

        if (empty($page_ids) && empty($post_ids) && empty($patterns)) {
            return false;
        }

        $matched = self::matches($page_ids, $post_ids, $patterns);
        $mode    = $get('page_filter_mode', 'exclude');

        return 'include' === $mode ? !$matched : $matched;
    }

    /**
     * Whether the current request matches the filter list.
     *
     * @param array $page_ids
     * @param array $post_ids
     * @param array $patterns
     * @return bool
     */
    public static function matches(array $page_ids, array $post_ids, array $patterns): bool {
        $current_id = (int) get_queried_object_id();

        if ($current_id > 0) {
            foreach ([$page_ids, $post_ids] as $ids) {
                if (!empty($ids) && in_array((string) $current_id, array_map('strval', $ids), true)) {
                    return true;
                }
            }
        }

        if (!empty($patterns)) {
            $current_url = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
            foreach ($patterns as $pattern) {
                $pattern = trim((string) $pattern);
                if ('' === $pattern) {
                    continue;
                }
                $regex = str_replace(['*', '/'], ['.*', '\/'], $pattern);
                if (preg_match('/^' . $regex . '$/i', $current_url)) {
                    return true;
                }
            }
        }

        return false;
    }
}
