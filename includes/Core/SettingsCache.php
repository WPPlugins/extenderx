<?php

namespace WPXERO\ExtenderX\Core;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings Cache Manager
 *
 * Centralized settings caching with module-based storage.
 * Each module category is stored as a separate wp_option for scalability:
 *   - extenderx_core           (autoload=yes, tiny — global enabled state)
 *   - extenderx_mod_appearance (autoload=no)
 *   - extenderx_mod_animations (autoload=no)
 *   - extenderx_mod_seasonal   (autoload=no)
 *   - extenderx_mod_site_modes (autoload=no)
 *   - extenderx_mod_ui         (autoload=no)
 *   - extenderx_mod_utilities  (autoload=no)
 *   - extenderx_mod_marketing  (autoload=no)
 *   - extenderx_mod_accessibility (autoload=no)
 *   - extenderx_mod_save_and_go (autoload=no)
 *   - extenderx_mod_builders   (autoload=no)
 *
 * The REST API and React admin panel continue to work with the full
 * nested settings object — this class transparently splits/merges.
 *
 * @package WPXERO\ExtenderX\Core
 * @since 1.7.0
 */
class SettingsCache {
    use Singleton;

    /**
     * Module-category → option-key mapping.
     * These keys correspond to the second level inside 'global'.
     *
     * @var array<string, string>
     */
    private static $module_map = [
        'appearance'         => 'extenderx_mod_appearance',
        'animations'         => 'extenderx_mod_animations',
        'seasonal_effects'   => 'extenderx_mod_seasonal',
        'site_modes'         => 'extenderx_mod_site_modes',
        'ui_enhancements'    => 'extenderx_mod_ui',
        'utility_protection' => 'extenderx_mod_utilities',
        'marketing'          => 'extenderx_mod_marketing',
        'custom_code'        => 'extenderx_mod_custom_code',
        'accessibility'      => 'extenderx_mod_accessibility',
        'save_and_go'        => 'extenderx_mod_save_and_go',
    ];

    /** Core settings option (autoloaded — small) */
    const CORE_OPTION = 'extenderx_core';

    /** Builder support option */
    const BUILDERS_OPTION = 'extenderx_mod_builders';

    /** Legacy single option key (pre-1.7.0) */
    const LEGACY_OPTION = 'extenderx_settings';

    /** DB schema version option key */
    const MIGRATION_KEY = 'extenderx_settings_version';

    /** Current settings schema version */
    const SETTINGS_VERSION = 6;

    /**
     * Per-module lazy-loaded cache.
     *
     * @var array<string, array>
     */
    private static $module_cache = [];

    /**
     * Cached core settings.
     *
     * @var array|null
     */
    private static $core_cache = null;

    /**
     * Cached builder settings.
     *
     * @var array|null
     */
    private static $builders_cache = null;

    /**
     * Cache hit counter for debugging.
     *
     * @var int
     */
    private static $cache_hits = 0;

    /**
     * Constructor — hooks targeted cache invalidation.
     */
    public function __construct() {
        add_action('update_option_' . self::CORE_OPTION, [$this, 'clear_cache'], 10, 0);
        add_action('update_option_' . self::BUILDERS_OPTION, [$this, 'clear_cache'], 10, 0);

        foreach (self::$module_map as $option_key) {
            add_action("update_option_{$option_key}", [$this, 'clear_cache'], 10, 0);
        }
    }

    // ------------------------------------------------------------------
    //  Migration
    // ------------------------------------------------------------------

    /**
     * Run migration from legacy single-option storage if needed.
     *
     * @return void
     */
    public function maybe_migrate() {
        $version = (int) get_option(self::MIGRATION_KEY, 0);

        if ($version >= self::SETTINGS_VERSION) {
            return;
        }

        // v0/v1 → v2: split the legacy single option into per-module options.
        // Only runs for installs that have never been sharded. Re-running this
        // on an already-migrated site would wipe settings, so it is version-gated.
        if ($version < 2) {
            $this->migrate_from_legacy();
        }

        // v2 → v3: introduce the Custom Code module option.
        if ($version < 3) {
            $this->migrate_to_v3();
        }

        // v3 → v4: carry the legacy site-wide entrance engine settings over to
        // the rebuilt `site_wide` engine, so upgrading users keep their reveal.
        if ($version < 4) {
            $this->migrate_to_v4();
        }

        // v4 → v5: introduce the Accessibility module option.
        if ($version < 5) {
            $this->migrate_to_v5();
        }

        // v5 → v6: introduce the Save & Go module option.
        if ($version < 6) {
            $this->migrate_to_v6();
        }

        update_option(self::MIGRATION_KEY, self::SETTINGS_VERSION, true);
        $this->clear_cache();
    }

    /**
     * v3 → v4 migration: legacy `entrance_effects` → rebuilt `site_wide`.
     *
     * Only carries settings across when the user was actually running the old
     * engine (`entrance_effects.enabled`) and has not already adopted the new
     * one (`site_wide.enabled`), preserving their live front-end reveal on the
     * rebuilt engine. The legacy subtree is then disabled (its code is removed
     * in this release anyway). Values are not license-clamped here — the runtime
     * (`SiteWide::get_config`) re-enforces the free/pro boundary at render.
     *
     * @return void
     */
    private function migrate_to_v4() {
        $all  = $this->get_all_settings();
        $anim = $all['global']['animations'] ?? null;
        if (!is_array($anim)) {
            return;
        }

        $ent = isset($anim['entrance_effects']) && is_array($anim['entrance_effects'])
            ? $anim['entrance_effects'] : [];
        $sw  = isset($anim['site_wide']) && is_array($anim['site_wide'])
            ? $anim['site_wide'] : [];

        if (empty($ent['enabled']) || !empty($sw['enabled'])) {
            return;
        }

        // Map legacy preset keys → rebuilt style keys (unknown → fadeUp).
        $style_map = [
            'fadeInUp' => 'fadeUp', 'fadeInDown' => 'fadeDown',
            'fadeInLeft' => 'fadeLeft', 'fadeInRight' => 'fadeRight',
            'slideInUp' => 'slideUp', 'slideInDown' => 'slideDown',
            'slideInLeft' => 'slideLeft', 'slideInRight' => 'slideRight',
            'scaleIn' => 'scaleIn', 'fadeScaleUp' => 'fadeScale',
            'rotateInLeft' => 'rotateIn', 'rotateInRight' => 'rotateIn',
            'flipYIn' => 'flipY', 'flipXIn' => 'flipX', 'zoomInRight' => 'zoomIn',
        ];
        $old_preset = isset($ent['preset']) ? (string) $ent['preset'] : 'fadeInUp';
        $style      = $style_map[$old_preset] ?? 'fadeUp';

        $stagger = 0;
        if (!empty($ent['stagger']['enabled'])) {
            $stagger = (int) ($ent['stagger']['amount'] ?? 100);
        }

        $migrated = [
            'enabled'     => true,
            'style'       => $style,
            'duration'    => isset($ent['duration']) ? (int) $ent['duration'] : 500,
            'delay'       => isset($ent['delay']) ? (int) $ent['delay'] : 0,
            'easing'      => isset($ent['easing']) ? (string) $ent['easing'] : 'ease-out',
            'distance'    => isset($ent['distance']) ? (int) $ent['distance'] : 24,
            'threshold'   => isset($ent['threshold']) ? (float) $ent['threshold'] : 0.15,
            'target_mode' => (isset($ent['target_mode']) && 'specific' === $ent['target_mode']) ? 'specific' : 'auto',
            'targets'     => (isset($ent['targets']) && is_array($ent['targets'])) ? $ent['targets'] : ($sw['targets'] ?? []),
            // Offset semantics differ (legacy px threshold vs rootMargin) — keep
            // the new default rather than mis-map.
            'offset'      => $sw['offset'] ?? 120,
            'stagger'     => $stagger,
            'replay'      => isset($ent['animate_once']) ? !$ent['animate_once'] : false,
            'selectors'   => isset($ent['selectors']) ? (string) $ent['selectors'] : '',
            'mobile'      => [
                'enabled' => isset($ent['mobile_enabled']) ? (bool) $ent['mobile_enabled'] : true,
                'reduce'  => $sw['mobile']['reduce'] ?? false,
            ],
        ];

        // Merge onto any existing site_wide so unmapped keys keep their defaults.
        $all['global']['animations']['site_wide']                  = array_merge($sw, $migrated);
        $all['global']['animations']['entrance_effects']['enabled'] = false;

        $this->save_all_settings($all);
    }

    /**
     * v4 → v5 migration: ensure the Accessibility module option exists.
     *
     * The module master switch defaults to enabled so its features are ready
     * to turn on, but every individual feature ships off — nothing changes on
     * the front end until an admin opts in.
     *
     * @return void
     */
    private function migrate_to_v5() {
        if (get_option('extenderx_mod_accessibility', null) === null) {
            update_option('extenderx_mod_accessibility', ['enabled' => true], false);
        }
    }

    /**
     * v5 → v6 migration: ensure the Save & Go module option exists.
     *
     * The module master switch defaults to enabled and its free actions ship
     * on, so the editor control is useful the moment someone looks for it.
     * Nothing on the front end changes — Save & Go has no front-end side.
     *
     * @return void
     */
    private function migrate_to_v6() {
        if (get_option('extenderx_mod_save_and_go', null) === null) {
            update_option(
                'extenderx_mod_save_and_go',
                \WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo\SaveAndGoCatalog::defaults(),
                false
            );
        }
    }

    /**
     * v2 → v3 migration: ensure the Custom Code module option exists.
     *
     * Snippets themselves live in the dedicated `extenderx_snippets` table
     * (see SnippetTable); this option only stores the module's master enabled
     * flag. Defaults to enabled so the header/footer injector is ready to use
     * once a snippet is added.
     *
     * @return void
     */
    private function migrate_to_v3() {
        if (get_option('extenderx_mod_custom_code', null) === null) {
            update_option('extenderx_mod_custom_code', ['enabled' => true], false);
        }
    }

    /**
     * Split legacy extenderx_settings into per-module options.
     *
     * @return void
     */
    private function migrate_from_legacy() {
        $legacy = get_option(self::LEGACY_OPTION, []);

        if (empty($legacy) || !is_array($legacy)) {
            // Fresh install — just write the core option.
            update_option(self::CORE_OPTION, ['enabled' => true], true);
            return;
        }

        $global = $legacy['global'] ?? [];

        // Core (autoload = yes)
        $core = ['enabled' => !empty($global['enabled'])];
        update_option(self::CORE_OPTION, $core, true);

        // Per-module (autoload = no)
        foreach (self::$module_map as $module => $option_key) {
            $module_data = $global[$module] ?? [];
            update_option($option_key, $module_data, false);
        }

        // Builder support (autoload = no)
        $builders = $legacy['builder_support'] ?? [];
        update_option(self::BUILDERS_OPTION, $builders, false);

        // Remove legacy blob
        delete_option(self::LEGACY_OPTION);
    }

    // ------------------------------------------------------------------
    //  Full-object API  (used by REST endpoints & admin panel)
    // ------------------------------------------------------------------

    /**
     * Assemble the full nested settings object from all module options.
     *
     * @return array
     */
    public function get_all_settings() {
        $global = $this->get_core_settings();

        foreach (self::$module_map as $module => $option_key) {
            $global[$module] = $this->get_module_settings($module);
        }

        return [
            'global'          => $global,
            'builder_support' => $this->get_builder_settings(),
        ];
    }

    /**
     * Save the full nested settings object by splitting into per-module options.
     *
     * @param array $settings Full settings array from the admin panel.
     * @return void
     */
    public function save_all_settings(array $settings) {
        $global = $settings['global'] ?? [];

        // Core
        $core = ['enabled' => !empty($global['enabled'])];
        update_option(self::CORE_OPTION, $core, true);

        // Per-module
        foreach (self::$module_map as $module => $option_key) {
            if (isset($global[$module])) {
                update_option($option_key, $global[$module], false);
            }
        }

        // Builder support
        if (isset($settings['builder_support'])) {
            update_option(self::BUILDERS_OPTION, $settings['builder_support'], false);
        }

        $this->clear_cache();
    }

    // ------------------------------------------------------------------
    //  Dot-notation path API  (used by modules on the frontend)
    // ------------------------------------------------------------------

    /**
     * Get a specific setting by dot-notation path.
     *
     * Examples:
     *   'global.enabled'
     *   'global.appearance.dark_mode.brightness'
     *   'builder_support.gutenberg.enabled'
     *
     * @param string $path    Dot notation path.
     * @param mixed  $default Default value if not found.
     * @return mixed
     */
    public function get_setting($path, $default = null) {
        $keys = explode('.', $path);

        if (empty($keys)) {
            return $default;
        }

        $first = $keys[0];

        // ── builder_support.* ──
        if ($first === 'builder_support') {
            $data = $this->get_builder_settings();
            return $this->traverse(array_slice($keys, 1), $data, $default);
        }

        // ── global.* ──
        if ($first === 'global') {
            $second = $keys[1] ?? null;

            // global.<module_category>.* → load only that module option
            if ($second && isset(self::$module_map[$second])) {
                $data = $this->get_module_settings($second);
                return $this->traverse(array_slice($keys, 2), $data, $default);
            }

            // global.enabled (or other core keys)
            $data = $this->get_core_settings();
            return $this->traverse(array_slice($keys, 1), $data, $default);
        }

        return $default;
    }

    // ------------------------------------------------------------------
    //  Internal data loaders (lazy, per-request cached)
    // ------------------------------------------------------------------

    /**
     * Get core settings (autoloaded, always fast).
     *
     * @return array
     */
    private function get_core_settings() {
        if (self::$core_cache === null) {
            self::$core_cache = get_option(self::CORE_OPTION, ['enabled' => true]);
        } else {
            self::$cache_hits++;
        }
        return self::$core_cache;
    }

    /**
     * Get settings for a specific module category (lazy-loaded).
     *
     * @param string $module Module category key.
     * @return array
     */
    private function get_module_settings($module) {
        if (!isset(self::$module_cache[$module])) {
            $option_key = self::$module_map[$module] ?? '';
            self::$module_cache[$module] = $option_key
                ? get_option($option_key, [])
                : [];
        } else {
            self::$cache_hits++;
        }
        return self::$module_cache[$module];
    }

    /**
     * Get builder support settings (lazy-loaded).
     *
     * @return array
     */
    private function get_builder_settings() {
        if (self::$builders_cache === null) {
            self::$builders_cache = get_option(self::BUILDERS_OPTION, []);
        } else {
            self::$cache_hits++;
        }
        return self::$builders_cache;
    }

    // ------------------------------------------------------------------
    //  Helpers
    // ------------------------------------------------------------------

    /**
     * Traverse an array by key sequence.
     *
     * @param array $keys    Remaining keys to traverse.
     * @param mixed $data    Current data node.
     * @param mixed $default Default value.
     * @return mixed
     */
    private function traverse(array $keys, $data, $default = null) {
        foreach ($keys as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                return $default;
            }
        }
        return $data;
    }

    /**
     * Clear all in-memory caches.
     *
     * @return void
     */
    public function clear_cache() {
        self::$core_cache     = null;
        self::$builders_cache = null;
        self::$module_cache   = [];
        self::$cache_hits     = 0;
    }

    /**
     * Get cache statistics for debugging.
     *
     * @return array
     */
    public function get_cache_stats() {
        return [
            'is_cached'      => self::$core_cache !== null,
            'cache_hits'     => self::$cache_hits,
            'loaded_modules' => array_keys(self::$module_cache),
        ];
    }

    /**
     * Get the module-to-option mapping (used by Settings for defaults).
     *
     * @return array<string, string>
     */
    public static function get_module_map() {
        return self::$module_map;
    }
}
