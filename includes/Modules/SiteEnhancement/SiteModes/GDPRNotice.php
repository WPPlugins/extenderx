<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * GDPR Notice
 *
 * Display GDPR compliance notice
 */
class GDPRNotice {
    use Singleton;

    public function __construct() {
        add_action('wp_footer', [$this, 'render_gdpr_notice']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_script('xero-gdpr-notice');
        wp_enqueue_style('xero-gdpr-notice');
    }

    public function render_gdpr_notice() {
        if (isset($_COOKIE['xero_gdpr_accepted'])) {
            return;
        }
?>
        <div id="xero-gdpr-notice-bar">
            <div class="xero-gdpr-content">
                <div class="xero-gdpr-icon">🔒</div>
                <div class="xero-gdpr-text">
                    <p><?php echo __('We care about your privacy. This website uses cookies and processes personal data in accordance with GDPR regulations. By continuing to use this site, you agree to our Privacy Policy.', 'extenderx'); ?></p>
                </div>
                <div class="xero-gdpr-actions">
                    <button id="xero-gdpr-accept"><?php echo __('I Understand', 'extenderx'); ?></button>
                    <a href="<?php echo get_privacy_policy_url(); ?>" class="xero-privacy-link" target="_blank"><?php echo __('Privacy Policy', 'extenderx'); ?></a>
                </div>
            </div>
        </div>
<?php
    }
}
