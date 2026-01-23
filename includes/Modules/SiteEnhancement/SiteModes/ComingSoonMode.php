<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\Utils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Coming Soon Mode
 *
 * Display coming soon page to visitors
 */
class ComingSoonMode {
    use Singleton;

    protected Utils $utils;

    public function __construct() {
        $this->utils = Utils::instance();
        add_action('template_redirect', [$this, 'show_coming_soon_page']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function show_coming_soon_page() {
        if (is_user_logged_in() && current_user_can('manage_options')) {
            return;
        }

        wp_die(
            $this->get_coming_soon_html(),
            __('Coming Soon', 'extenderx'),
            ['response' => 200]
        );
    }

    public function enqueue_assets() {
        wp_enqueue_script('xero-coming-soon-mode');
        wp_enqueue_style('xero-coming-soon-mode');
    }

    private function get_coming_soon_html() {
        ob_start();
?>
        <!DOCTYPE html>
        <html>

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    overflow: hidden;
                }

                .coming-soon-container {
                    text-align: center;
                    color: white;
                    padding: 3rem;
                    max-width: 700px;
                    animation: fadeIn 1s ease-out;
                }

                .coming-soon-icon {
                    font-size: 7rem;
                    margin-bottom: 2rem;
                    animation: pulse 2s ease-in-out infinite;
                    display: inline-block;
                    filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
                }

                h1 {
                    font-size: 4rem;
                    font-weight: 800;
                    margin-bottom: 1.5rem;
                    letter-spacing: -1px;
                    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
                }

                p {
                    font-size: 1.4rem;
                    line-height: 1.8;
                    opacity: 0.95;
                    font-weight: 300;
                }

                .countdown {
                    display: flex;
                    justify-content: center;
                    gap: 2rem;
                    margin-top: 3rem;
                }

                .countdown-item {
                    text-align: center;
                }

                .countdown-value {
                    font-size: 3rem;
                    font-weight: 700;
                    display: block;
                    background: rgba(255, 255, 255, 0.2);
                    padding: 1rem 1.5rem;
                    border-radius: 12px;
                    backdrop-filter: blur(10px);
                    min-width: 80px;
                }

                .countdown-label {
                    font-size: 0.9rem;
                    opacity: 0.9;
                    margin-top: 0.5rem;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }

                @keyframes fadeIn {
                    from {
                        opacity: 0;
                        transform: scale(0.95);
                    }

                    to {
                        opacity: 1;
                        transform: scale(1);
                    }
                }

                @keyframes pulse {

                    0%,
                    100% {
                        transform: scale(1);
                    }

                    50% {
                        transform: scale(1.1);
                    }
                }
            </style>
        </head>

        <body>
            <div class="coming-soon-container">
                <div class="coming-soon-icon">🚀</div>
                <h1><?php echo __('Coming Soon', 'extenderx'); ?></h1>
                <p><?php echo __('Something amazing is on the horizon. Get ready for an extraordinary experience!', 'extenderx'); ?></p>
                <div class="countdown">
                    <div class="countdown-item">
                        <span class="countdown-value">00</span>
                        <span class="countdown-label"><?php echo __('Days', 'extenderx'); ?></span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-value">00</span>
                        <span class="countdown-label"><?php echo __('Hours', 'extenderx'); ?></span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-value">00</span>
                        <span class="countdown-label"><?php echo __('Minutes', 'extenderx'); ?></span>
                    </div>
                </div>
            </div>
        </body>

        </html>
<?php
        return ob_get_clean();
    }
}
