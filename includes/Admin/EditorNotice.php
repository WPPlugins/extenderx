<?php

namespace WPXERO\ExtenderX\Admin;

use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Traits\Singleton;

defined('ABSPATH') || exit;

/**
 * One-time notice on block editor screens pointing users to ExtenderX animations.
 */
class EditorNotice {
    use Singleton;

    private const DISMISS_OPTION = 'extenderx_editor_notice_dismissed';

    public function __construct() {
        add_action('admin_notices', [$this, 'render_notice']);
        add_action('admin_init', [$this, 'handle_dismiss']);
    }

    public function handle_dismiss() {
        if (!isset($_GET['extenderx_dismiss_editor_notice'], $_GET['_wpnonce'])) {
            return;
        }

        if (!current_user_can('edit_posts')) {
            return;
        }

        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'extenderx_dismiss_editor_notice')) {
            return;
        }

        update_option(self::DISMISS_OPTION, 1, false);
    }

    public function render_notice() {
        if (get_option(self::DISMISS_OPTION)) {
            return;
        }

        if (!current_user_can('edit_posts')) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || !$screen->is_block_editor()) {
            return;
        }

        $settings = SettingsCache::instance()->get_all_settings();
        if (empty($settings['builder_support']['gutenberg']['enabled'])) {
            return;
        }

        $dismiss_url = wp_nonce_url(
            add_query_arg('extenderx_dismiss_editor_notice', '1', admin_url()),
            'extenderx_dismiss_editor_notice'
        );
?>
        <div class="notice notice-success is-dismissible extenderx-editor-notice">
            <p>
                <strong><?php esc_html_e('ExtenderX block animations are active.', 'extenderx'); ?></strong>
                <?php esc_html_e('Select any block, open the right sidebar, and look for the ExtenderX panel to add entrance, floating, or tilt effects.', 'extenderx'); ?>
                <a href="<?php echo esc_url($dismiss_url); ?>">
                    <?php esc_html_e('Got it', 'extenderx'); ?>
                </a>
            </p>
        </div>
<?php
    }
}
