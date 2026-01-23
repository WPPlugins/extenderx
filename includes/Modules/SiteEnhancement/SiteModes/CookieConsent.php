<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cookie Consent Bar
 *
 * Display cookie consent notice for compliance
 */
class CookieConsent {
    use Singleton;

    public function __construct() {
        add_action('wp_footer', [$this, 'render_consent_bar']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_script('xero-cookie-consent');
        wp_enqueue_style('xero-cookie-consent');
    }

    public function render_consent_bar() {
        if (isset($_COOKIE['xero_cookie_consent'])) {
            return;
        }
?>
        <div id="xero-cookie-consent-bar">
            <div class="xero-cookie-content">
                <div class="xero-cookie-icon">🍪</div>
                <div class="xero-cookie-text">
                    <p><?php esc_html_e('We use cookies to enhance your browsing experience and analyze our traffic. By clicking "Accept", you consent to our use of cookies.', 'extenderx'); ?></p>
                </div>
                <div class="xero-cookie-actions">
                    <button id="xero-cookie-accept"><?php esc_html_e('Accept All', 'extenderx'); ?></button>
                    <button id="xero-cookie-decline"><?php esc_html_e('Decline', 'extenderx'); ?></button>
                </div>
            </div>
        </div>
<?php
    }
}
