<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Snippet Table
 *
 * Owns the dedicated `{prefix}extenderx_snippets` table that stores Custom Code
 * snippets (Insert Headers & Footers + Code Manager). A purpose-built table is
 * used instead of a custom post type so the per-pageload "active snippets"
 * lookup is a single indexed query, the security-sensitive snippet bodies stay
 * out of `wp_posts`, and no revision rows accrue.
 *
 * The schema is created via dbDelta on activation, during the standard
 * version-gated upgrade in {@see \WPXERO\ExtenderX\Core\Settings::maybe_upgrade()},
 * and — as a self-healing fallback — by {@see self::maybe_install()} on module
 * boot. dbDelta is idempotent, so running it repeatedly is safe.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode
 * @since 1.7.0
 */
class SnippetTable {

    /**
     * Unprefixed table name.
     *
     * @var string
     */
    private const TABLE = 'extenderx_snippets';

    /**
     * Current schema version. Bump when the table structure changes.
     *
     * @var int
     */
    private const SCHEMA_VERSION = 1;

    /**
     * Option that records the installed schema version.
     *
     * @var string
     */
    private const VERSION_OPTION = 'extenderx_snippets_db_version';

    /**
     * Ensure the table exists and is at the current schema version.
     *
     * Cheap, self-healing guard safe to call on every request: it only runs
     * dbDelta when the recorded schema version is stale (or the option is
     * missing), so existing installs get the table without needing a plugin
     * reactivation or a settings-version bump.
     *
     * @return void
     */
    public static function maybe_install(): void {
        if ((int) get_option(self::VERSION_OPTION, 0) === self::SCHEMA_VERSION) {
            return;
        }

        self::create();
    }

    /**
     * Fully-qualified (prefixed) table name.
     *
     * @return string
     */
    public static function table_name(): string {
        global $wpdb;

        return $wpdb->prefix . self::TABLE;
    }

    /**
     * Whether the snippets table exists.
     *
     * @return bool
     */
    public static function exists(): bool {
        global $wpdb;

        $table = self::table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Schema check.
        return (bool) $wpdb->get_var(
            $wpdb->prepare('SHOW TABLES LIKE %s', $table)
        );
    }

    /**
     * Create or update the snippets table via dbDelta.
     *
     * @return void
     */
    public static function create(): void {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table           = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        // dbDelta is whitespace- and format-sensitive: two spaces after PRIMARY
        // KEY, one field definition per line, KEY for indexes.
        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL DEFAULT '',
            type varchar(20) NOT NULL DEFAULT 'html',
            location varchar(40) NOT NULL DEFAULT '',
            active tinyint(1) NOT NULL DEFAULT 0,
            priority int(11) NOT NULL DEFAULT 10,
            code longtext NOT NULL,
            conditions longtext NULL,
            schedule longtext NULL,
            created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            modified_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY  (id),
            KEY active_priority (active, priority)
        ) {$charset_collate};";

        \dbDelta($sql);

        update_option(self::VERSION_OPTION, self::SCHEMA_VERSION, true);
    }
}
