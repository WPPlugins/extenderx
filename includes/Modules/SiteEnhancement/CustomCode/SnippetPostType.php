<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Snippet Repository
 *
 * Read/normalise layer over the dedicated `extenderx_snippets` table (see
 * {@see SnippetTable}). Despite the historical class name, snippets are no
 * longer a custom post type: each snippet is a single table row, which keeps
 * the per-pageload "active snippets" lookup a single indexed query and keeps
 * snippet bodies out of `wp_posts`.
 *
 * Writes are owned by {@see SnippetRestController}; this class exposes the
 * stable read surface (`TYPES`, `LOCATIONS`, `defaults()`, `normalize()`,
 * `query_all()`, `query_active()`) consumed by the REST controller and the
 * frontend engine.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode
 * @since 1.7.0
 */
class SnippetPostType {

    /**
     * Supported snippet types.
     *
     * @var string[]
     */
    public const TYPES = ['html', 'css', 'js', 'php'];

    /**
     * Valid locations per snippet type.
     *
     * The frontend engine keys output groups by `{type}_{location}`, so these
     * values are the canonical contract between storage, REST and the engine.
     *
     * @var array<string, string[]>
     */
    public const LOCATIONS = [
        'html' => ['header', 'body_open', 'footer'],
        'css'  => ['head', 'footer'],
        'js'   => ['header', 'body_open', 'footer'],
        'php'  => ['everywhere', 'frontend', 'admin'],
    ];

    /**
     * Default snippet configuration.
     *
     * @return array
     */
    public static function defaults(): array {
        return [
            'type'       => 'html',
            'location'   => 'footer',
            'active'     => false,
            'priority'   => 10,
            'conditions' => [
                'enabled'  => false,
                'action'   => 'show',
                'relation' => 'and',
                'rules'    => [],
            ],
            'schedule'   => [
                'enabled' => false,
                'start'   => '',
                'end'     => '',
            ],
        ];
    }

    /**
     * Fetch and normalise a single snippet by ID.
     *
     * @param int $id Snippet ID.
     * @return array|null Normalised snippet, or null if not found.
     */
    public static function find(int $id): ?array {
        global $wpdb;

        if ($id <= 0) {
            return null;
        }

        $table = SnippetTable::table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id)
        );

        return $row ? self::normalize($row) : null;
    }

    /**
     * Normalise a snippet row into the plain array consumed by both the
     * frontend engine and the REST controller.
     *
     * Accepts either a row (object/array from the table) or a snippet ID, so
     * existing callers that pass an integer keep working.
     *
     * @param object|array|int $row Table row, or snippet ID.
     * @return array|null Normalised snippet, or null if invalid.
     */
    public static function normalize($row): ?array {
        if (is_int($row) || (is_string($row) && ctype_digit($row))) {
            return self::find((int) $row);
        }

        if (is_object($row)) {
            $row = (array) $row;
        }

        if (!is_array($row) || !isset($row['id'])) {
            return null;
        }

        $defaults = self::defaults();

        $type = (string) ($row['type'] ?? '');
        if (!in_array($type, self::TYPES, true)) {
            $type = $defaults['type'];
        }

        $location = (string) ($row['location'] ?? '');

        $conditions = isset($row['conditions']) ? json_decode((string) $row['conditions'], true) : null;
        $schedule   = isset($row['schedule']) ? json_decode((string) $row['schedule'], true) : null;

        return [
            'id'         => (int) $row['id'],
            'title'      => (string) ($row['title'] ?? ''),
            'type'       => $type,
            'location'   => $location !== '' ? $location : $defaults['location'],
            'active'     => (int) ($row['active'] ?? 0) === 1,
            'priority'   => isset($row['priority']) ? (int) $row['priority'] : $defaults['priority'],
            'code'       => (string) ($row['code'] ?? ''),
            'conditions' => is_array($conditions)
                ? wp_parse_args($conditions, $defaults['conditions'])
                : $defaults['conditions'],
            'schedule'   => is_array($schedule)
                ? wp_parse_args($schedule, $defaults['schedule'])
                : $defaults['schedule'],
            'modified'   => (string) ($row['modified_at'] ?? ''),
        ];
    }

    /**
     * Query every snippet, ordered by priority then title.
     *
     * @return array<int, array> List of normalised snippets.
     */
    public static function query_all(): array {
        global $wpdb;

        $table = SnippetTable::table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
        $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY priority ASC, title ASC");

        return self::normalize_rows($rows);
    }

    /**
     * Query only active snippets, ordered by priority.
     *
     * Used by the frontend engine so inactive snippets are never loaded on a
     * page request — the `active_priority` index serves this directly.
     *
     * @return array<int, array> List of normalised snippets.
     */
    public static function query_active(): array {
        global $wpdb;

        $table = SnippetTable::table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
        $rows = $wpdb->get_results("SELECT * FROM {$table} WHERE active = 1 ORDER BY priority ASC, title ASC");

        return self::normalize_rows($rows);
    }

    /**
     * Total snippet count (for admin display).
     *
     * @return int
     */
    public static function count(): int {
        global $wpdb;

        $table = SnippetTable::table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }

    /**
     * Normalise a list of rows, dropping any that fail validation.
     *
     * @param array|null $rows Raw rows.
     * @return array<int, array>
     */
    private static function normalize_rows($rows): array {
        $snippets = [];

        foreach ((array) $rows as $row) {
            $data = self::normalize($row);
            if ($data !== null) {
                $snippets[] = $data;
            }
        }

        return $snippets;
    }
}
