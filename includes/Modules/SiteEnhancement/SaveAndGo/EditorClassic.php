<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo;

use WP_Post;
use WPXERO\ExtenderX\Core\ProFeatures;
use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Save & Go — classic editor integration
 *
 * Adds a "Save & Go" split-button control to the Publish box — the same
 * button-plus-dropdown pattern the block editor uses (see EditorGutenberg),
 * so the feature looks and behaves the same in both editors. It deliberately
 * owns no saving logic of its own: the control's main button forwards a real
 * click to WordPress's own Publish/Update button, so the post is saved by the
 * same `post.php` request that button always used — with autosaves,
 * revisions, scheduling, visibility and pre-publish behaviour untouched. The
 * only thing Save & Go changes is where the browser lands afterwards, through
 * the core `redirect_post_location` filter, and only once the save has
 * succeeded.
 *
 * Because the destination is resolved server-side from the post itself, the
 * request never carries a URL that could be redirected to.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo
 * @since 1.10.0
 */
class EditorClassic {
    use Singleton;

    /** Script/style handle. */
    private const HANDLE = 'extenderx-save-and-go-classic';

    /** Bundle location inside dist/. */
    private const ASSET_DIR = 'modules/editor/save-and-go/classic/';

    /**
     * Chosen action field. Always present once the control has rendered
     * (an empty hidden input), which is what lets `filter_redirect()` tell a
     * Save & Go submission apart from a plain click on WordPress's own
     * button without a second marker field: an empty or missing value simply
     * resolves to no action, and this filter no-ops.
     */
    private const ACTION_FIELD = 'extenderx_save_and_go_action';

    /** Captured referrer field, used by Save & Return and list filters. */
    private const RETURN_FIELD = 'extenderx_save_and_go_return';

    /** Nonce field. */
    private const NONCE_FIELD = 'extenderx_save_and_go_nonce';

    /** Transient prefix for the post-redirect notice. */
    private const NOTICE_PREFIX = 'extenderx_sag_notice_';

    /**
     * Local storage key the control remembers the last-used action under.
     * Shared verbatim with the block editor bundle, so "last used" means the
     * same thing regardless of which editor ran it last.
     */
    private const REMEMBER_KEY = 'extenderx-save-and-go-action';

    /**
     * Picker value meaning "save, and stay where you are".
     *
     * Only offered when Save & Go has taken WordPress's Update button over,
     * because that is the only configuration in which the plain save would
     * otherwise have nowhere to be clicked — the same escape hatch the block
     * editor's menu carries.
     */
    private const SAVE_ONLY = '__save_only';

    /**
     * Per-request memo of {@see context()}.
     *
     * @var array|null
     */
    private $context;

    /**
     * Wire the classic editor hooks.
     *
     * `post_submitbox_misc_actions` and `post_submitbox_start` only fire in
     * the classic Publish box, so none of this reaches the block editor.
     *
     * @since 1.10.0
     */
    public function __construct() {
        add_action('post_submitbox_misc_actions', [$this, 'render_misc_control']);
        add_action('post_submitbox_start', [$this, 'render_major_control']);
        add_filter('redirect_post_location', [$this, 'filter_redirect'], 10, 2);
        add_action('admin_notices', [$this, 'render_notice']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('admin_body_class', [$this, 'add_body_class']);
    }

    /**
     * Build (and memoize) everything the control needs for the current post:
     * the usable actions and the effective placement. Both render callbacks
     * and the asset enqueue read from here, so the actions are only resolved
     * once per request no matter how many of them run.
     *
     * @since 1.10.0
     * @return array|null Null when there is nothing to show on this screen.
     */
    private function context(): ?array {
        if (null !== $this->context) {
            return $this->context ?: null;
        }

        $post = get_post();

        if (!$this->is_classic_editor_screen() || !$post instanceof WP_Post) {
            $this->context = [];
            return null;
        }

        $actions = $this->usable_actions($post);

        if (empty($actions)) {
            $this->context = [];
            return null;
        }

        $mode          = SaveAndGoCatalog::resolve_classic_mode(SaveAndGo::config(), SaveAndGo::is_pro());
        $replaces_core = 'replace' === $mode && 'publish' === $post->post_status;

        $this->context = [
            'post'          => $post,
            'actions'       => $actions,
            'mode'          => $mode,
            'placement'     => 'submitbox' === $mode ? 'submitbox' : 'major',
            // Primary styling follows the chosen mode, not the post's current
            // status — the button reads as "this is the way you save now"
            // from the first draft, matching the block editor bundle
            // (isPrimary = 'primary' === mode || 'replace' === mode) rather
            // than waiting for the one-time switch-over that only concerns
            // whether core's own button gets hidden.
            'is_primary'    => 'primary' === $mode || 'replace' === $mode,
            'replaces_core' => $replaces_core,
        ];

        return $this->context;
    }

    /**
     * Enqueue the classic editor bundle, once there is something for it to
     * render.
     *
     * @since 1.10.0
     * @return void
     */
    public function enqueue_assets(): void {
        $context = $this->context();

        if (null === $context) {
            return;
        }

        $asset_file = XERO_EXTENDERX_DIR_PATH . 'dist/' . self::ASSET_DIR . 'index.asset.php';

        if (!file_exists($asset_file)) {
            return;
        }

        $asset = require $asset_file;

        wp_enqueue_script(
            self::HANDLE,
            XERO_EXTENDERX_DIST_URL . self::ASSET_DIR . 'index.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        $style_file = XERO_EXTENDERX_DIR_PATH . 'dist/' . self::ASSET_DIR . 'style-style.css';

        if (file_exists($style_file)) {
            wp_enqueue_style(
                self::HANDLE,
                XERO_EXTENDERX_DIST_URL . self::ASSET_DIR . 'style-style.css',
                [],
                $asset['version']
            );

            wp_style_add_data(self::HANDLE, 'rtl', 'replace');
        }

        wp_localize_script(self::HANDLE, 'extenderxSaveAndGoClassic', $this->build_config($context));
    }

    /**
     * The configuration object the classic bundle boots from — the same
     * shape the block editor bundle uses (see EditorGutenberg::get_config()),
     * so both scripts share one mental model.
     *
     * @since 1.10.0
     * @param array $context {@see context()}.
     * @return array
     */
    private function build_config(array $context): array {
        $is_pro = SaveAndGo::is_pro();

        return [
            'formSelector'     => '#post',
            'publishButtonId'  => 'publish',
            'actionField'      => self::ACTION_FIELD,
            'saveOnlyValue'    => self::SAVE_ONLY,
            'withSaveOnly'     => $context['replaces_core'],
            'defaultAction'    => SaveAndGo::default_action(),
            'lastUsedSentinel' => SaveAndGoCatalog::LAST_USED_ACTION,
            'rememberKey'      => self::REMEMBER_KEY,
            'isPro'            => $is_pro,
            'upgradeUrl'       => ProFeatures::instance()->get_upgrade_url('editor'),
            'labels'           => [
                'menu'     => __('Choose a Save & Go action', 'extenderx'),
                /* translators: %s: the WordPress save verb — "Save", "Update"… */
                'saveOnly' => _x('%s only', 'Save & Go button label', 'extenderx'),
                'saving'   => __('Saving…', 'extenderx'),
                // Fallback verb only: WordPress's own button always carries a
                // real one, this covers the split second before it does.
                'save'     => __('Save', 'extenderx'),
                /* translators: %s: a Save & Go action name, e.g. "Save & Duplicate". */
                'pro'      => _x('%s (Pro)', 'Save & Go action name with a Pro suffix', 'extenderx'),
            ],
            'actions'          => array_map(
                static function (array $action): array {
                    return [
                        'id'          => $action['id'],
                        'label'       => $action['label'],
                        'buttonLabel' => $action['button_label'],
                        'locked'      => !empty($action['locked']),
                        'unavailable' => isset($action['unavailable']) ? (string) $action['unavailable'] : '',
                    ];
                },
                $context['actions']
            ),
        ];
    }

    /**
     * Render the mount point inside the misc-publishing area (free
     * placement — "lower down in the Publish box").
     *
     * @since 1.10.0
     * @param WP_Post $post Post being edited.
     * @return void
     */
    public function render_misc_control($post): void {
        $context = $this->context();

        if (null === $context || 'submitbox' !== $context['placement']) {
            return;
        }
        ?>
        <div class="misc-pub-section extenderx-sag-misc">
            <span class="extenderx-sag-classic-label"><?php esc_html_e('Save & Go', 'extenderx'); ?></span>
            <span
                data-extenderx-sag-classic-root
                data-extenderx-sag-placement="submitbox"
                data-extenderx-sag-primary="false"
            ></span>
            <?php $this->render_hidden_fields($post); ?>
        </div>
        <?php
    }

    /**
     * Render the mount point next to the Publish/Update button (premium
     * placements: beside, primary or replace).
     *
     * @since 1.10.0
     * @param WP_Post $post Post being edited.
     * @return void
     */
    public function render_major_control($post): void {
        $context = $this->context();

        if (null === $context || 'major' !== $context['placement']) {
            return;
        }

        $classes = 'extenderx-sag-major';
        if ($context['replaces_core']) {
            $classes .= ' extenderx-sag-replacing';
        }
        ?>
        <div class="<?php echo esc_attr($classes); ?>">
            <span
                data-extenderx-sag-classic-root
                data-extenderx-sag-placement="major"
                data-extenderx-sag-primary="<?php echo $context['is_primary'] ? 'true' : 'false'; ?>"
            ></span>
            <?php $this->render_hidden_fields($post); ?>
        </div>
        <?php
    }

    /**
     * Enqueue the control's stylesheet and mark the screen when Save & Go
     * has taken over the Update button, so it can hide core's.
     *
     * @since 1.10.0
     * @param string $classes Existing body classes.
     * @return string
     */
    public function add_body_class($classes) {
        $context = $this->context();

        if (null === $context || !$context['replaces_core']) {
            return $classes;
        }

        return $classes . ' extenderx-sag-classic-hide-core';
    }

    /**
     * Render the nonce and the captured referrer.
     *
     * The referrer is read from the request that loaded the edit screen, so
     * Save & Return knows where the user came from. It is validated against
     * this site's admin before it is ever used.
     *
     * @since 1.10.0
     * @param WP_Post $post Post being edited.
     * @return void
     */
    private function render_hidden_fields($post): void {
        wp_nonce_field('extenderx_save_and_go_' . (int) $post->ID, self::NONCE_FIELD, false);

        $referrer = Navigator::validate_admin_url((string) wp_get_referer());

        if ('' !== $referrer) {
            printf(
                '<input type="hidden" name="%1$s" value="%2$s" />',
                esc_attr(self::RETURN_FIELD),
                esc_attr($referrer)
            );
        }
    }

    /**
     * Send the user to the Save & Go destination once WordPress has saved.
     *
     * Runs on core's own post-save redirect, so by the time it is reached the
     * save has already happened and succeeded — a failed save never gets this
     * far, and the user stays in the editor with their content.
     *
     * @since 1.10.0
     * @param string $location Default redirect location.
     * @param int    $post_id  Post ID.
     * @return string
     */
    public function filter_redirect($location, $post_id) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- verified immediately below.
        $action = isset($_POST[self::ACTION_FIELD]) ? sanitize_key(wp_unslash($_POST[self::ACTION_FIELD])) : '';

        // Not our control (or a plain click straight through to WordPress's
        // own button): nothing to do.
        if ('' === $action) {
            return $location;
        }

        $post_id = (int) $post_id;
        $nonce   = isset($_POST[self::NONCE_FIELD]) ? sanitize_text_field(wp_unslash($_POST[self::NONCE_FIELD])) : '';

        if (!wp_verify_nonce($nonce, 'extenderx_save_and_go_' . $post_id)) {
            return $location;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return $location;
        }

        $referrer = isset($_POST[self::RETURN_FIELD]) ? esc_url_raw(wp_unslash($_POST[self::RETURN_FIELD])) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        // "Update only" — WordPress has already saved the post by the time this
        // filter runs, so leaving the location alone is the whole behaviour:
        // the user stays in the editor with core's own "Post updated" notice.
        if (self::SAVE_ONLY === $action) {
            return $location;
        }

        if (!SaveAndGo::is_action_available($action)) {
            $this->set_notice([
                'type'    => 'error',
                'message' => __('That Save & Go action is not available.', 'extenderx'),
            ]);

            return $location;
        }

        if ('duplicate' === $action) {
            return $this->redirect_to_duplicate($post_id, $location);
        }

        $destination = Navigator::resolve($action, $post_id, ['referrer' => $referrer]);

        if (is_wp_error($destination)) {
            $this->set_notice([
                'type'    => 'error',
                'message' => $destination->get_error_message(),
            ]);

            return $location;
        }

        // A new window cannot be opened from a redirect, and a popup opened on
        // page load would be blocked. The link in the notice is a real user
        // action, so it always opens.
        if ('blank' === $destination['target']) {
            $this->set_notice([
                'type'    => 'success',
                'message' => __('Saved. Open this post on the front end:', 'extenderx'),
                'url'     => $destination['url'],
                'link'    => __('Open in a new tab', 'extenderx'),
            ]);

            return $location;
        }

        return $destination['url'];
    }

    /**
     * Duplicate and redirect to the copy.
     *
     * @since 1.10.0
     * @param int    $post_id  Source post.
     * @param string $location Fallback location.
     * @return string
     */
    private function redirect_to_duplicate(int $post_id, string $location): string {
        $new_id = Duplicator::duplicate($post_id);

        if (is_wp_error($new_id)) {
            $this->set_notice([
                'type'    => 'error',
                'message' => $new_id->get_error_message(),
            ]);

            return $location;
        }

        $url = get_edit_post_link($new_id, 'raw');

        return $url ? $url : $location;
    }

    /**
     * Show the notice left behind by the redirect, if any.
     *
     * @since 1.10.0
     * @return void
     */
    public function render_notice(): void {
        if (!$this->is_classic_editor_screen()) {
            return;
        }

        $key    = self::NOTICE_PREFIX . get_current_user_id();
        $notice = get_transient($key);

        if (!is_array($notice) || empty($notice['message'])) {
            return;
        }

        delete_transient($key);

        $class = 'error' === ($notice['type'] ?? 'error') ? 'notice-error' : 'notice-success';
        ?>
        <div class="notice <?php echo esc_attr($class); ?> is-dismissible">
            <p>
                <?php echo esc_html($notice['message']); ?>
                <?php if (!empty($notice['url'])) : ?>
                    <a href="<?php echo esc_url($notice['url']); ?>" target="_blank" rel="noopener noreferrer">
                        <?php echo esc_html($notice['link'] ?? __('Open', 'extenderx')); ?>
                    </a>
                <?php endif; ?>
            </p>
        </div>
        <?php
    }

    /**
     * Store a one-shot notice for the redirect target.
     *
     * @since 1.10.0
     * @param array $notice Notice payload.
     * @return void
     */
    private function set_notice(array $notice): void {
        set_transient(self::NOTICE_PREFIX . get_current_user_id(), $notice, MINUTE_IN_SECONDS);
    }

    /**
     * The actions this post can actually offer, locked premium ones included
     * — the classic dropdown has room to show what upgrading adds, the same
     * way the block editor's does.
     *
     * @since 1.10.0
     * @param mixed $post Post being edited.
     * @return array
     */
    private function usable_actions($post): array {
        if (!$post instanceof WP_Post) {
            return [];
        }

        if (!Navigator::is_supported_post_type($post->post_type)) {
            return [];
        }

        if (!current_user_can('edit_post', $post->ID)) {
            return [];
        }

        return Navigator::annotate_availability(SaveAndGo::action_list(true), $post);
    }

    /**
     * Whether the current screen is a classic post editor.
     *
     * @since 1.10.0
     * @return bool
     */
    private function is_classic_editor_screen(): bool {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        if (!$screen || 'post' !== $screen->base) {
            return false;
        }

        return !(method_exists($screen, 'is_block_editor') && $screen->is_block_editor());
    }
}
