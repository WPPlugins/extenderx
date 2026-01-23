<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Exit Intent Popup
 *
 * Show popup when user is about to leave the site
 */
class ExitIntent {
    use Singleton;

    public function __construct() {
        add_action('wp_footer', [$this, 'render_exit_popup']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_script('xero-exit-intent');
        wp_enqueue_style('xero-exit-intent');
    }

    public function render_exit_popup() {
?>
        
        <div id="xero-exit-intent-overlay">
            <div class="xero-exit-intent-popup">
                <button class="xero-exit-close" aria-label="<?php echo __('Close', 'extenderx'); ?>">&times;</button>
                <div class="xero-exit-icon">�</div>
                <h2><?php echo __('Wait! Don\'t Leave Yet!', 'extenderx'); ?></h2>
                <p><?php echo __('Before you go, check out our special offer just for you! We have something amazing that you don\'t want to miss.', 'extenderx'); ?></p>
                <div class="xero-exit-actions">
                    <button class="xero-btn-stay"><?php echo __('Show Me the Offer', 'extenderx'); ?></button>
                    <button class="xero-btn-leave"><?php echo __('No Thanks', 'extenderx'); ?></button>
                </div>
            </div>
        </div>
<?php
    }
}
