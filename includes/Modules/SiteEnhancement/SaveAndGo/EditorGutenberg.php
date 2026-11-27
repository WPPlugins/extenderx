<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo;

use WPXERO\ExtenderX\Core\ProFeatures;
use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Save & Go — block editor integration
 *
 * Loads the editor bundle on post editing screens only, and only for post
 * types and users that may actually use it. Nothing is registered for the
 * front end, the classic editor, the site editor or the widgets screen.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo
 * @since 1.10.0
 */
class EditorGutenberg {
    use Singleton;

    /** Script/style handle. */
    private const HANDLE = 'extenderx-save-and-go';

    /** Bundle location inside dist/. */
    private const ASSET_DIR = 'modules/editor/save-and-go/gutenberg/';

    /**
     * Hook the editor asset enqueue.
     *
     * `enqueue_block_editor_assets` fires only inside the block editor, so the
     * classic editor never sees a line of this bundle.
     *
     * @since 1.10.0
     */
    public function __construct() {
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_assets']);
    }

    /**
     * Enqueue and configure the editor bundle.
     *
     * @since 1.10.0
     * @return void
     */
    public function enqueue_assets(): void {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        // The site editor and the widgets screen are block editors too, and
        // neither has a post to save and go from.
        if (!$screen || 'post' !== $screen->base || empty($screen->post_type)) {
            return;
        }

        if (!Navigator::is_supported_post_type($screen->post_type)) {
            return;
        }

        $post_id = (int) get_the_ID();

        if ($post_id <= 0 || !current_user_can('edit_post', $post_id)) {
            return;
        }

        $actions = SaveAndGo::action_list();

        if (empty($actions)) {
            return;
        }

        // Availability depends on the post being edited, so it is worked out
        // here rather than in the catalog: "next" has nowhere to go from the
        // last row of the list.
        $post = get_post($post_id);

        if (!$post instanceof \WP_Post) {
            return;
        }

        $actions = Navigator::annotate_availability($actions, $post);

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
        }

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations(self::HANDLE, 'extenderx', XERO_EXTENDERX_DIR_PATH . 'languages');
        }

        wp_localize_script(self::HANDLE, 'extenderxSaveAndGo', $this->get_config($actions));
    }

    /**
     * Build the editor configuration.
     *
     * The premium flag here drives presentation only — which entries wear a
     * lock. Whether an action may actually run is decided again by
     * RestController on every request.
     *
     * @since 1.10.0
     * @param array $actions Action definitions.
     * @return array
     */
    private function get_config(array $actions): array {
        $is_pro = SaveAndGo::is_pro();

        return [
            'restPath'      => '/wpxero/extenderx/v1/save-and-go',
            'actions'       => array_map(
                static function (array $action): array {
                    return [
                        'id'          => $action['id'],
                        'label'       => $action['label'],
                        // "%s & New" — the editor swaps %s for Save or Update
                        // so the button reads as one continuous sentence with
                        // whatever WordPress itself would have called the save.
                        'buttonLabel' => $action['button_label'],
                        'description' => $action['description'],
                        'pro'         => (bool) $action['pro'],
                        'locked'      => !empty($action['locked']),
                        // Empty when the action can run; otherwise the reason
                        // the menu shows on the greyed-out entry.
                        'unavailable' => isset($action['unavailable']) ? (string) $action['unavailable'] : '',
                        'target'      => $action['target'],
                        'needsSave'   => (bool) $action['needs_save'],
                    ];
                },
                $actions
            ),
            'defaultAction' => SaveAndGo::default_action(),
            'buttonMode'    => SaveAndGoCatalog::resolve_gutenberg_mode(SaveAndGo::config(), $is_pro),
            'isPro'         => $is_pro,
            'upgradeUrl'    => ProFeatures::instance()->get_upgrade_url('editor'),
            'labels'        => [
                // The verbs this button can honestly use. It saves the post at
                // its current status; it never publishes a draft, so core's
                // "Publish"/"Schedule"/"Submit for Review" wording would
                // promise something the click does not do.
                'save'            => __('Save', 'extenderx'),
                'update'          => __('Update', 'extenderx'),
                'saving'          => __('Saving…', 'extenderx'),
                'popupWait'       => __('Saving the post. This tab will open it once the save is done.', 'extenderx'),
                'menu'            => __('Choose a Save & Go action', 'extenderx'),
                /* translators: %s: the WordPress save verb — "Save" or "Update". */
                'saveOnly'        => _x('%s only', 'Save & Go button label', 'extenderx'),
            ],
        ];
    }
}
