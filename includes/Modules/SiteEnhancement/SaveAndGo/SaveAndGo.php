<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo;

use WPXERO\ExtenderX\Core\ModuleBase;
use WPXERO\ExtenderX\Core\ProFeatures;
use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Save & Go Module
 *
 * Editor productivity: save the post you are on and go straight to the next
 * thing — a new post, the list, the neighbouring post, the front end.
 *
 * Like Accessibility (and unlike the effect categories, which only alias the
 * master switch), this module carries a real toggle at
 * `global.save_and_go.enabled`. Its two editor integrations and the premium
 * Bulk Add tool are loaded through the ModuleBase feature map, so Bulk Add is
 * never even instantiated on an unlicensed install and its REST route
 * therefore does not exist there.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo
 * @since 1.10.0
 */
class SaveAndGo extends ModuleBase {
    use Singleton;

    private const SETTINGS_PREFIX = 'global.save_and_go';
    private const MODULE_SETTINGS_KEY = 'global.save_and_go';

    /**
     * Feature map.
     *
     * `bulk_add` matches the premium key registered in Core\ProFeatures, so
     * ModuleBase's own licence gate covers it without a second check here.
     *
     * @var array<string, class-string>
     */
    private const SAVE_AND_GO_FEATURES = [
        'gutenberg.enabled' => EditorGutenberg::class,
        'classic.enabled'   => EditorClassic::class,
        'bulk_add.enabled'  => BulkAdd::class,
    ];

    /**
     * Per-request cache of the sanitized module settings.
     *
     * @var array|null
     */
    private static $config = null;

    /**
     * Get the settings key for checking if module is enabled.
     *
     * @since 1.10.0
     * @return string
     */
    protected function get_module_settings_key(): string {
        return self::MODULE_SETTINGS_KEY;
    }

    /**
     * Get the settings prefix for module features.
     *
     * @since 1.10.0
     * @return string
     */
    protected function get_settings_prefix(): string {
        return self::SETTINGS_PREFIX;
    }

    /**
     * Get the Save & Go features mapping.
     *
     * @since 1.10.0
     * @return array<string, class-string>
     */
    protected function get_features(): array {
        return self::SAVE_AND_GO_FEATURES;
    }

    /**
     * Boot the shared REST controller alongside the features.
     *
     * Both editors resolve their destinations through it, and it runs only
     * once both ModuleBase gates (site enhancements + this module) have
     * passed — so a disabled module registers no routes at all.
     *
     * @since 1.10.0
     * @return void
     */
    protected function initialize_features(): void {
        RestController::instance();

        parent::initialize_features();
    }

    /**
     * Whether a premium licence is active.
     *
     * @since 1.10.0
     * @return bool
     */
    public static function is_pro(): bool {
        return ProFeatures::instance()->has_pro_license();
    }

    /**
     * Whether the current install may run a premium action.
     *
     * @since 1.10.0
     * @param string $action_id Action id.
     * @return bool
     */
    public static function can_use_action(string $action_id): bool {
        if (!SaveAndGoCatalog::is_pro_action($action_id)) {
            return true;
        }

        $key = SaveAndGoCatalog::pro_feature_key($action_id);

        return '' !== $key && ProFeatures::instance()->can_use_premium_feature($key);
    }

    /**
     * The module's settings, sanitized against the live licence state.
     *
     * Reading through the catalog on the way out means a lapsed licence
     * degrades to free behaviour even if premium values are still sitting in
     * the database from when it was valid.
     *
     * @since 1.10.0
     * @return array
     */
    public static function config(): array {
        if (null === self::$config) {
            $stored = SettingsCache::instance()->get_setting(self::SETTINGS_PREFIX, []);
            $stored = is_array($stored) ? $stored : [];

            self::$config = SaveAndGoCatalog::sanitize($stored, self::is_pro());
        }

        return self::$config;
    }

    /**
     * Whether the module is switched on for this site.
     *
     * @since 1.10.0
     * @return bool
     */
    public static function is_active(): bool {
        $core = SettingsCache::instance();

        if (!$core->get_setting('global.enabled', true)) {
            return false;
        }

        $config = self::config();

        return !empty($config['enabled']);
    }

    /**
     * The actions this install may actually run, as flat definitions the
     * editors and the REST layer can hand straight to the UI.
     *
     * Premium actions stay in the list when unlicensed but are marked
     * `locked`, so the menu can show what upgrading adds without ever being
     * able to run it — the server rejects a locked action regardless.
     *
     * @since 1.10.0
     * @param bool $include_locked Whether to keep unavailable premium actions.
     * @return array<int, array>
     */
    public static function action_list(bool $include_locked = true): array {
        $config  = self::config();
        $enabled = isset($config['actions']) && is_array($config['actions']) ? $config['actions'] : [];
        $out     = [];

        foreach (SaveAndGoCatalog::actions() as $id => $action) {
            $locked = $action['pro'] && !self::can_use_action($id);

            if ($locked) {
                if (!$include_locked) {
                    continue;
                }

                $out[] = array_merge($action, ['locked' => true]);
                continue;
            }

            if (empty($enabled[$id])) {
                continue;
            }

            $out[] = array_merge($action, ['locked' => false]);
        }

        return $out;
    }

    /**
     * Whether an action may run right now: switched on, and covered by the
     * licence. This is the check every entry point shares.
     *
     * @since 1.10.0
     * @param string $action_id Action id.
     * @return bool
     */
    public static function is_action_available(string $action_id): bool {
        if (!self::is_active()) {
            return false;
        }

        if (!self::can_use_action($action_id)) {
            return false;
        }

        $config = self::config();

        if (!SaveAndGoCatalog::is_builtin_action($action_id)) {
            // A contributed action has no settings row of its own; it is
            // available whenever the module is.
            return array_key_exists($action_id, SaveAndGoCatalog::actions());
        }

        return !empty($config['actions'][$action_id]);
    }

    /**
     * The action a plain click runs.
     *
     * @since 1.10.0
     * @return string
     */
    public static function default_action(): string {
        return SaveAndGoCatalog::resolve_default_action(self::config(), self::is_pro());
    }
}
