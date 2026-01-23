<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Age Verification Popup
 *
 * Verify visitor age before allowing site access
 */
class AgeVerification {
    use Singleton;

    public function __construct() {
        add_action('wp_footer', [$this, 'render_popup']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_script('xero-age-verification');
        wp_enqueue_style('xero-age-verification');
    }

    public function render_popup() {
        if (isset($_COOKIE['xero_age_verified'])) {
            return;
        }
?>
        <div id="xero-age-verification-overlay">
            <div class="xero-age-verification-popup">
                <div class="xero-age-icon">🔞</div>
                <h2><?php echo __('Age Verification Required', 'extenderx'); ?></h2>
                <p><?php echo __('You must be 18 years or older to access this website. Please verify your age to continue.', 'extenderx'); ?></p>
                <div class="xero-age-buttons">
                    <button id="xero-age-confirm" class="xero-btn-confirm"><?php echo __('I am 18 or Older', 'extenderx'); ?></button>
                    <button id="xero-age-decline" class="xero-btn-decline"><?php echo __('I am Under 18', 'extenderx'); ?></button>
                </div>
            </div>
        </div>
<?php
    }
}
