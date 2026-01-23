<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SiteModes;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\Utils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Maintenance Mode
 *
 * Display maintenance page to visitors while site is being updated
 */
class MaintenanceMode {
    use Singleton;

    protected Utils $utils;

    public function __construct() {
        $this->utils = Utils::instance();
        add_action('template_redirect', [$this, 'show_maintenance_page']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function show_maintenance_page() {
        if (is_user_logged_in() && current_user_can('manage_options')) {
            return;
        }

        wp_die(
            $this->get_maintenance_html(),
            __('Maintenance Mode', 'extenderx'),
            ['response' => 503]
        );
    }

    public function enqueue_assets() {
        wp_enqueue_script('xero-maintenance-mode');
        wp_enqueue_style('xero-maintenance-mode');
    }

    private function get_maintenance_html() {
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
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    overflow: hidden;
                    position: relative;
                }

                .maintenance-container {
                    text-align: center;
                    color: white;
                    padding: 3rem;
                    max-width: 600px;
                    animation: fadeInUp 0.8s ease-out;
                }

                .maintenance-icon {
                    font-size: 6rem;
                    margin-bottom: 2rem;
                    animation: bounce 2s infinite;
                    display: inline-block;
                }

                h1 {
                    font-size: 3rem;
                    font-weight: 700;
                    margin-bottom: 1.5rem;
                    letter-spacing: -0.5px;
                }

                p {
                    font-size: 1.25rem;
                    line-height: 1.7;
                    opacity: 0.95;
                    margin-bottom: 2rem;
                }

                .progress-bar {
                    width: 100%;
                    height: 4px;
                    background: rgba(255, 255, 255, 0.2);
                    border-radius: 10px;
                    overflow: hidden;
                    margin-top: 2rem;
                }

                .progress-fill {
                    height: 100%;
                    background: white;
                    border-radius: 10px;
                    animation: loading 2s ease-in-out infinite;
                }

                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(30px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                @keyframes bounce {

                    0%,
                    100% {
                        transform: translateY(0);
                    }

                    50% {
                        transform: translateY(-20px);
                    }
                }

                @keyframes loading {
                    0% {
                        width: 0%;
                    }

                    50% {
                        width: 70%;
                    }

                    100% {
                        width: 100%;
                    }
                }
            </style>
        </head>

        <body>
            <div class="maintenance-container">
                <div class="maintenance-icon">🚧</div>
                <h1><?php echo __('Under Maintenance', 'extenderx'); ?></h1>
                <p><?php echo __('We are currently performing scheduled maintenance to improve your experience. We\'ll be back soon!', 'extenderx'); ?></p>
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
            </div>
        </body>

        </html>
<?php
        return ob_get_clean();
    }
}
