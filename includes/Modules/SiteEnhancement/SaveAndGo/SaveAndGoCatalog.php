<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Save & Go Catalog
 *
 * Single source of truth for the `global.save_and_go` settings subtree: the
 * action registry, defaults, whitelists and the free/pro boundary. The REST
 * save path (Core\Settings), the editor integrations and the REST runtime all
 * read the rules from here, so a lapsed licence degrades to free behaviour
 * everywhere at once without any caller re-deriving the boundary.
 *
 * Mirrors the AccessibilityCatalog contract: `defaults()` + `sanitize()`, with
 * the licence state passed in rather than looked up, so the class stays a pure
 * data/validation layer.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo
 * @since 1.10.0
 */
class SaveAndGoCatalog {

    /**
     * Actions every install gets.
     *
     * @var string[]
     */
    private const FREE_ACTIONS = ['new', 'list', 'next', 'previous', 'view'];

    /**
     * Actions that require a premium licence.
     *
     * @var string[]
     */
    private const PRO_ACTIONS = ['duplicate', 'return', 'view_new_window'];

    /**
     * Action id → premium feature key registered in Core\ProFeatures.
     *
     * @var array<string, string>
     */
    private const PRO_ACTION_KEYS = [
        'duplicate'       => 'save_go_duplicate',
        'return'          => 'save_go_return',
        'view_new_window' => 'save_go_new_window',
    ];

    /**
     * Premium feature key gating the classic editor's button placements. The
     * block editor's are free — see resolve_gutenberg_mode().
     */
    public const PRO_KEY_BUTTON_MODES = 'save_go_button_modes';

    /**
     * Premium feature key gating the "choose the default action" setting.
     */
    public const PRO_KEY_DEFAULT_ACTION = 'save_go_default_action';

    /**
     * Premium feature key gating Bulk Add. Matches the `bulk_add` settings
     * subkey so ModuleBase derives it from the feature map on its own.
     */
    public const PRO_KEY_BULK_ADD = 'bulk_add';

    /**
     * Block editor button placements. The first is the default: the split
     * button in the editor header, beside the WordPress button, which is where
     * people look for it. All four are selectable on any install.
     *
     * @var string[]
     */
    private const GUTENBERG_MODES = ['header', 'sidebar', 'primary', 'replace'];

    /**
     * Classic editor button placements. `beside` puts the control in the
     * Publish box's action row next to Publish/Update; `submitbox` keeps it in
     * the box's misc section for people who want it out of the way.
     *
     * @var string[]
     */
    private const CLASSIC_MODES = ['beside', 'submitbox', 'primary', 'replace'];

    /** Placement a free install always uses in the block editor. */
    public const FREE_GUTENBERG_MODE = 'header';

    /** Placement a free install always uses in the classic editor. */
    public const FREE_CLASSIC_MODE = 'beside';

    /**
     * Sentinel `default_action` meaning "whichever action was used last".
     *
     * Stored in place of an action id, so the setting answers one question —
     * what the button opens with — instead of the editor quietly overriding a
     * chosen default with a remembered one.
     */
    public const LAST_USED_ACTION = 'last_used';

    /** Statuses a post may be saved with through Bulk Add. */
    private const BULK_STATUSES = ['draft', 'pending', 'private', 'publish'];

    /**
     * Default settings for the whole module.
     *
     * The module itself ships off, matching every other site-enhancement
     * feature — nothing changes in the editor until an admin opts in.
     * Free actions ship on within that so the feature works fully the
     * moment it is switched on. Premium actions ship off and are clamped
     * off for unlicensed installs anyway.
     *
     * @since 1.10.0
     * @return array
     */
    public static function defaults(): array {
        $actions = [];
        foreach (self::FREE_ACTIONS as $id) {
            $actions[$id] = true;
        }
        foreach (self::PRO_ACTIONS as $id) {
            $actions[$id] = false;
        }

        return [
            'enabled'        => false,
            'actions'        => $actions,
            'default_action' => 'new',
            'gutenberg'      => [
                'enabled'     => true,
                'button_mode' => self::FREE_GUTENBERG_MODE,
            ],
            'classic'        => [
                'enabled'     => true,
                'button_mode' => self::FREE_CLASSIC_MODE,
            ],
            'bulk_add'       => [
                'enabled' => false,
            ],
        ];
    }

    /**
     * The action registry.
     *
     * Each entry describes one Save & Go action end to end: what it is called,
     * whether it needs a licence, whether the post has to be saved first and
     * where the destination opens. Destinations themselves are resolved by
     * {@see Navigator}, keyed on the same ids.
     *
     * Filterable so a future ExtenderX feature can contribute an action
     * without touching the button, the settings screen or the REST layer.
     * Third-party entries are normalised and are always treated as free
     * (a plugin cannot mark its own action premium and thereby bypass the
     * licence check, nor gain one).
     *
     * @since 1.10.0
     * @return array<string, array>
     */
    public static function actions(): array {
        $actions = [
            'new' => [
                'id'          => 'new',
                'label'       => __('Save & New', 'extenderx'),
                /* translators: %s: the WordPress save verb — "Save" or "Update". */
                'button_label' => _x('%s & New', 'Save & Go button label', 'extenderx'),
                'description' => __('Save this post and start a new one of the same type.', 'extenderx'),
                'pro'         => false,
                'needs_save'  => true,
                'target'      => 'self',
            ],
            'list' => [
                'id'          => 'list',
                'label'       => __('Save & List', 'extenderx'),
                /* translators: %s: the WordPress save verb — "Save" or "Update". */
                'button_label' => _x('%s & List', 'Save & Go button label', 'extenderx'),
                'description' => __('Save this post and return to its post list.', 'extenderx'),
                'pro'         => false,
                'needs_save'  => true,
                'target'      => 'self',
            ],
            'next' => [
                'id'          => 'next',
                'label'       => __('Save & Next', 'extenderx'),
                /* translators: %s: the WordPress save verb — "Save" or "Update". */
                'button_label' => _x('%s & Next', 'Save & Go button label', 'extenderx'),
                'description' => __('Save this post and edit the next one in the list.', 'extenderx'),
                'pro'         => false,
                'needs_save'  => true,
                'target'      => 'self',
            ],
            'previous' => [
                'id'          => 'previous',
                'label'       => __('Save & Previous', 'extenderx'),
                /* translators: %s: the WordPress save verb — "Save" or "Update". */
                'button_label' => _x('%s & Previous', 'Save & Go button label', 'extenderx'),
                'description' => __('Save this post and edit the previous one in the list.', 'extenderx'),
                'pro'         => false,
                'needs_save'  => true,
                'target'      => 'self',
            ],
            'view' => [
                'id'          => 'view',
                'label'       => __('Save & View', 'extenderx'),
                /* translators: %s: the WordPress save verb — "Save" or "Update". */
                'button_label' => _x('%s & View', 'Save & Go button label', 'extenderx'),
                'description' => __('Save this post and open it on the front end.', 'extenderx'),
                'pro'         => false,
                'needs_save'  => true,
                'target'      => 'self',
            ],
            'duplicate' => [
                'id'          => 'duplicate',
                'label'       => __('Save & Duplicate', 'extenderx'),
                /* translators: %s: the WordPress save verb — "Save" or "Update". */
                'button_label' => _x('%s & Duplicate', 'Save & Go button label', 'extenderx'),
                'description' => __('Save this post, copy it to a new draft and edit the copy.', 'extenderx'),
                'pro'         => true,
                'needs_save'  => true,
                'target'      => 'self',
            ],
            'return' => [
                'id'          => 'return',
                'label'       => __('Save & Return', 'extenderx'),
                /* translators: %s: the WordPress save verb — "Save" or "Update". */
                'button_label' => _x('%s & Return', 'Save & Go button label', 'extenderx'),
                'description' => __('Save this post and go back where you came from.', 'extenderx'),
                'pro'         => true,
                'needs_save'  => true,
                'target'      => 'self',
            ],
            'view_new_window' => [
                'id'          => 'view_new_window',
                'label'       => __('Save & View in New Window', 'extenderx'),
                /* translators: %s: the WordPress save verb — "Save" or "Update". */
                'button_label' => _x('%s & View in New Window', 'Save & Go button label', 'extenderx'),
                'description' => __('Save this post and open it on the front end in a new tab.', 'extenderx'),
                'pro'         => true,
                'needs_save'  => true,
                'target'      => 'blank',
            ],
        ];

        $filtered = apply_filters('extenderx_save_and_go_actions', $actions);

        return self::normalize_actions(is_array($filtered) ? $filtered : $actions);
    }

    /**
     * Normalise a (possibly filtered) action registry.
     *
     * Built-in entries keep their own free/pro flag; anything added by a third
     * party is forced to free, since the premium boundary is defined by
     * Core\ProFeatures and not by whoever added the action.
     *
     * @since 1.10.0
     * @param array $actions Raw registry.
     * @return array<string, array>
     */
    private static function normalize_actions(array $actions): array {
        $clean = [];

        foreach ($actions as $id => $action) {
            $id = sanitize_key(is_array($action) && isset($action['id']) ? $action['id'] : $id);

            if ('' === $id || !is_array($action)) {
                continue;
            }

            $is_builtin = self::is_builtin_action($id);

            $clean[$id] = [
                'id'           => $id,
                'label'        => isset($action['label']) ? (string) $action['label'] : $id,
                // Falls back to the plain label when a contributed action
                // brings no "%s & …" pattern of its own.
                'button_label' => isset($action['button_label'])
                    ? (string) $action['button_label']
                    : (isset($action['label']) ? (string) $action['label'] : $id),
                'description' => isset($action['description']) ? (string) $action['description'] : '',
                'pro'         => $is_builtin ? in_array($id, self::PRO_ACTIONS, true) : false,
                'needs_save'  => isset($action['needs_save']) ? (bool) $action['needs_save'] : true,
                'target'      => isset($action['target']) && 'blank' === $action['target'] ? 'blank' : 'self',
            ];
        }

        return $clean;
    }

    /**
     * Whether an action id is one of the built-in ones.
     *
     * @since 1.10.0
     * @param string $id Action id.
     * @return bool
     */
    public static function is_builtin_action(string $id): bool {
        return in_array($id, self::FREE_ACTIONS, true) || in_array($id, self::PRO_ACTIONS, true);
    }

    /**
     * Whether an action requires a premium licence.
     *
     * @since 1.10.0
     * @param string $id Action id.
     * @return bool
     */
    public static function is_pro_action(string $id): bool {
        return in_array($id, self::PRO_ACTIONS, true);
    }

    /**
     * Premium feature key for an action, or an empty string for free actions.
     *
     * @since 1.10.0
     * @param string $id Action id.
     * @return string
     */
    public static function pro_feature_key(string $id): string {
        return self::PRO_ACTION_KEYS[$id] ?? '';
    }

    /**
     * Free action ids, in display order.
     *
     * @since 1.10.0
     * @return string[]
     */
    public static function free_actions(): array {
        return self::FREE_ACTIONS;
    }

    /**
     * Premium action ids, in display order.
     *
     * @since 1.10.0
     * @return string[]
     */
    public static function pro_actions(): array {
        return self::PRO_ACTIONS;
    }

    /**
     * Statuses Bulk Add may assign.
     *
     * @since 1.10.0
     * @return string[]
     */
    public static function bulk_statuses(): array {
        return self::BULK_STATUSES;
    }

    /**
     * Ids of the actions an install may actually run.
     *
     * An action counts as available when it is switched on in settings, the
     * licence covers it, and it still exists in the registry.
     *
     * @since 1.10.0
     * @param array $settings Sanitized module settings.
     * @param bool  $is_pro   Whether a premium licence is active.
     * @return string[]
     */
    public static function available_action_ids(array $settings, bool $is_pro): array {
        $enabled = isset($settings['actions']) && is_array($settings['actions']) ? $settings['actions'] : [];
        $ids     = [];

        foreach (self::actions() as $id => $action) {
            if (empty($enabled[$id])) {
                continue;
            }

            if ($action['pro'] && !$is_pro) {
                continue;
            }

            $ids[] = $id;
        }

        return $ids;
    }

    /**
     * The action a click runs when the user has not picked one.
     *
     * Choosing the default is a premium setting, so free installs always get
     * the first available free action — never a premium one, and never a
     * stored premium choice left behind by a lapsed licence.
     *
     * @since 1.10.0
     * @param array $settings Sanitized module settings.
     * @param bool  $is_pro   Whether a premium licence is active.
     * @return string
     */
    public static function resolve_default_action(array $settings, bool $is_pro): string {
        $available = self::available_action_ids($settings, $is_pro);

        if (empty($available)) {
            return '';
        }

        if ($is_pro) {
            $stored = isset($settings['default_action']) ? (string) $settings['default_action'] : '';

            if (self::LAST_USED_ACTION === $stored) {
                return self::LAST_USED_ACTION;
            }

            if (in_array($stored, $available, true)) {
                return $stored;
            }
        }

        $free = array_values(array_intersect(self::FREE_ACTIONS, $available));

        return $free[0] ?? $available[0];
    }

    /**
     * Effective block editor button placement.
     *
     * @since 1.10.0
     * @param array $settings Sanitized module settings.
     * @param bool  $is_pro   Whether a premium licence is active.
     * @return string
     */
    public static function resolve_gutenberg_mode(array $settings, bool $is_pro): string {
        // Free too: where the button sits — beside core's, in the sidebar, or
        // in place of Update on a published post — is a layout preference, not
        // a premium capability. `$is_pro` stays in the signature because the
        // classic resolver beside it still gates, and both are called the same
        // way from the editors.
        $mode = $settings['gutenberg']['button_mode'] ?? self::FREE_GUTENBERG_MODE;

        return in_array($mode, self::GUTENBERG_MODES, true) ? $mode : self::FREE_GUTENBERG_MODE;
    }

    /**
     * Effective classic editor button placement.
     *
     * @since 1.10.0
     * @param array $settings Sanitized module settings.
     * @param bool  $is_pro   Whether a premium licence is active.
     * @return string
     */
    public static function resolve_classic_mode(array $settings, bool $is_pro): string {
        if (!$is_pro) {
            return self::FREE_CLASSIC_MODE;
        }

        $mode = $settings['classic']['button_mode'] ?? self::FREE_CLASSIC_MODE;

        return in_array($mode, self::CLASSIC_MODES, true) ? $mode : self::FREE_CLASSIC_MODE;
    }

    /**
     * Sanitize the `global.save_and_go` subtree.
     *
     * Casts booleans, whitelists enums and forces every premium field back to
     * its free value when the install is unlicensed — so a site whose licence
     * lapsed cannot keep a premium action switched on in the database and have
     * the editor offer it.
     *
     * @since 1.10.0
     * @param array $in     Raw settings.
     * @param bool  $is_pro Whether a premium licence is active.
     * @return array
     */
    public static function sanitize(array $in, bool $is_pro): array {
        $d = self::defaults();

        $actions_in = self::sub($in, 'actions');
        $actions    = [];

        foreach (self::FREE_ACTIONS as $id) {
            $actions[$id] = isset($actions_in[$id]) ? (bool) $actions_in[$id] : $d['actions'][$id];
        }

        foreach (self::PRO_ACTIONS as $id) {
            $actions[$id] = $is_pro && !empty($actions_in[$id]);
        }

        $clean = [
            'enabled'   => isset($in['enabled']) ? (bool) $in['enabled'] : $d['enabled'],
            'actions'   => $actions,
            'gutenberg' => [
                'enabled'     => isset($in['gutenberg']['enabled'])
                    ? (bool) $in['gutenberg']['enabled']
                    : $d['gutenberg']['enabled'],
                'button_mode' => self::sanitize_mode(
                    $in['gutenberg']['button_mode'] ?? '',
                    self::GUTENBERG_MODES,
                    self::FREE_GUTENBERG_MODE,
                    // Selectable on every install, licensed or not.
                    true
                ),
            ],
            'classic'   => [
                'enabled'     => isset($in['classic']['enabled'])
                    ? (bool) $in['classic']['enabled']
                    : $d['classic']['enabled'],
                'button_mode' => self::sanitize_mode(
                    $in['classic']['button_mode'] ?? '',
                    self::CLASSIC_MODES,
                    self::FREE_CLASSIC_MODE,
                    $is_pro
                ),
            ],
            'bulk_add'  => [
                'enabled' => $is_pro && !empty($in['bulk_add']['enabled']),
            ],
        ];

        // Keep a stored free default across a licence lapse; only a premium
        // choice an unlicensed site could never run is rewritten.
        $default = isset($in['default_action']) ? sanitize_key((string) $in['default_action']) : $d['default_action'];
        $known   = array_merge(array_keys(self::actions()), [self::LAST_USED_ACTION]);

        // "Last used" is a premium choice like any other default: an unlicensed
        // install falls back to the first free action below.
        if (!in_array($default, $known, true)
            || (!$is_pro && (self::LAST_USED_ACTION === $default || self::is_pro_action($default)))) {
            $default = self::resolve_default_action(array_merge($clean, ['default_action' => '']), $is_pro);
        }

        $clean['default_action'] = '' !== $default ? $default : $d['default_action'];

        return $clean;
    }

    /**
     * Whitelist a button placement, pinning it to the default when this
     * install may not choose one.
     *
     * @since 1.10.0
     * @param mixed    $value       Raw placement.
     * @param string[] $allowed     Allowed placements.
     * @param string   $free        Placement to fall back to.
     * @param bool     $may_choose  Whether this install may pick a placement at
     *                              all; false pins it to `$free`.
     * @return string
     */
    private static function sanitize_mode($value, array $allowed, string $free, bool $may_choose): string {
        if (!$may_choose) {
            return $free;
        }

        $value = sanitize_key((string) $value);

        return in_array($value, $allowed, true) ? $value : $free;
    }

    /**
     * Fetch a nested array branch, guaranteeing an array back.
     *
     * @since 1.10.0
     * @param array  $in  Parent array.
     * @param string $key Branch key.
     * @return array
     */
    private static function sub(array $in, string $key): array {
        return isset($in[$key]) && is_array($in[$key]) ? $in[$key] : [];
    }
}
