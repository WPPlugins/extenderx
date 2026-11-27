<?php

namespace WPXERO\ExtenderX\Admin;

use WPXERO\ExtenderX\Core\SettingsCache;
use WPXERO\ExtenderX\Traits\Singleton;

defined('ABSPATH') || exit;

/**
 * Encourages satisfied users to leave a WordPress.org review.
 */
class ReviewPrompt {
    use Singleton;

    private const DISMISS_OPTION = 'extenderx_review_prompt_dismissed';
    private const ACTIVATED_OPTION = 'extenderx_activated_at';
    private const REVIEW_URL = 'https://wordpress.org/support/plugin/extenderx/reviews/?filter=5#new-post';
    private const MIN_DAYS = 7;

    public function __construct() {
        add_action('admin_notices', [$this, 'render_notice']);
        add_action('admin_init', [$this, 'handle_dismiss']);
    }

    /**
     * Dismiss the review prompt for 90 days.
     */
    public function handle_dismiss() {
        if (!isset($_GET['extenderx_dismiss_review'], $_GET['_wpnonce'])) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'extenderx_dismiss_review')) {
            return;
        }

        update_option(self::DISMISS_OPTION, time() + (90 * DAY_IN_SECONDS), false);
    }

    /**
     * Show review notice when the user has engaged with the accessibility
     * toolbar or block animations.
     */
    public function render_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if ($this->is_dismissed()) {
            return;
        }

        if (!$this->has_waited_enough()) {
            return;
        }

        if (!$this->has_engagement()) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || !in_array($screen->base, ['dashboard', 'plugins', 'toplevel_page_extenderx'], true)) {
            return;
        }

        $dismiss_url = wp_nonce_url(
            add_query_arg('extenderx_dismiss_review', '1', admin_url()),
            'extenderx_dismiss_review'
        );
?>
        <div class="notice notice-info is-dismissible extenderx-review-notice">
            <p>
                <strong><?php esc_html_e('Enjoying ExtenderX?', 'extenderx'); ?></strong>
                <?php esc_html_e('A quick WordPress.org review helps other site owners find a free, private accessibility toolbar.', 'extenderx'); ?>
                <a href="<?php echo esc_url(self::REVIEW_URL); ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Leave a review', 'extenderx'); ?>
                </a>
                ·
                <a href="<?php echo esc_url($dismiss_url); ?>">
                    <?php esc_html_e('Maybe later', 'extenderx'); ?>
                </a>
            </p>
        </div>
<?php
    }

    private function is_dismissed() {
        $dismissed_until = (int) get_option(self::DISMISS_OPTION, 0);
        return $dismissed_until > time();
    }

    private function has_waited_enough() {
        $activated_at = (int) get_option(self::ACTIVATED_OPTION, 0);
        if (!$activated_at) {
            return false;
        }

        return (time() - $activated_at) >= (self::MIN_DAYS * DAY_IN_SECONDS);
    }

    private function has_engagement() {
        $settings = SettingsCache::instance()->get_all_settings();

        return $this->has_accessibility_engagement($settings) || $this->has_animation_engagement($settings);
    }

    private function has_accessibility_engagement(array $settings) {
        $global = $settings['global'] ?? [];

        return !empty($global['enabled'])
            && !empty($global['accessibility']['enabled'])
            && !empty($global['accessibility']['toolbar']['enabled']);
    }

    private function has_animation_engagement(array $settings) {
        if (empty($settings['builder_support']['gutenberg']['enabled'])) {
            return false;
        }

        $extensions = $settings['builder_support']['gutenberg']['extensions'] ?? [];
        $animation_keys = ['entrance_effects', 'floating_effects', 'tilt_effects'];

        foreach ($animation_keys as $key) {
            if (!empty($extensions[$key])) {
                return true;
            }
        }

        return false;
    }
}
