<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Appearance;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;

if (!defined('ABSPATH')) {
    exit;
}

class DarkMode {
    use Singleton;

    private $settings = [];
    private $settings_cache;
    private $default_settings = [
        'brightness' => 100,
        'contrast' => 90,
        'sepia' => 10,
        'grayscale' => 0,
        'text_stroke' => 0,
        'filter_mode' => 1,
        'default_dark_mode' => false,
        'use_system_color_scheme' => false,
        'enable_for_pdfs' => false,
        'immediate_modify' => true,
        'dark_scheme_background' => '',
        'dark_scheme_text' => '',
        'toggle_position' => 'bottom-right',
        'toggle_style' => 'default',
        'show_toggle' => true,
    ];

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        $this->load_settings();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_toggle_button']);
    }

    private function load_settings() {
        $dark_mode_settings = $this->settings_cache->get_setting('global.appearance.dark_mode', []);
        $this->settings = wp_parse_args($dark_mode_settings, $this->default_settings);
    }

    public function enqueue_assets() {

        // Add critical inline CSS to prevent FOUC
        $this->add_critical_inline_css();

        // Enqueue Dark Reader vendor library
        // wp_enqueue_script(
        //     'extenderx-darkreader',
        //     XERO_EXTENDERX_ASSETS_URL . 'vendor/' . (defined('WP_DEBUG') && WP_DEBUG ? 'darkreader.js' : 'darkreader.min.js'),
        //     [],
        //     XERO_EXTENDERX_VERSION,
        //     false // Load in head
        // );

        // Enqueue initialization script (registered in RegisterAssets)
        wp_enqueue_script('xero-dark-mode');

        // Enqueue toggle button styles (registered in RegisterAssets)
        wp_enqueue_style('xero-dark-mode');

        // Pass settings to JavaScript
        wp_localize_script(
            'xero-dark-mode',
            'extenderxDarkModeSettings',
            [
                'brightness' => intval($this->settings['brightness']),
                'contrast' => intval($this->settings['contrast']),
                'sepia' => intval($this->settings['sepia']),
                'grayscale' => intval($this->settings['grayscale']),
                'textStroke' => floatval($this->settings['text_stroke']),
                'mode' => intval($this->settings['filter_mode']),
                'useSystemColorScheme' => (bool)$this->settings['use_system_color_scheme'],
                'enableForPDFs' => (bool)$this->settings['enable_for_pdfs'],
                'darkSchemeBackgroundColor' => $this->settings['dark_scheme_background'],
                'darkSchemeTextColor' => $this->settings['dark_scheme_text'],
                'immediateModify' => (bool)$this->settings['immediate_modify'],
                'defaultDarkMode' => (bool)$this->settings['default_dark_mode'],
                'toggleBtnPosition' => $this->settings['toggle_position'],
                'toggleBtnStyle' => $this->settings['toggle_style'],
                'shouldApplyDarkMode' => true,
            ]
        );
    }

    private function add_critical_inline_css() {
        $default_dark_mode = $this->settings['default_dark_mode'];
        $use_system_color_scheme = $this->settings['use_system_color_scheme'];

        if (!$default_dark_mode && !$use_system_color_scheme) {
            return;
        }

        add_action('wp_head', function () use ($default_dark_mode, $use_system_color_scheme) {
            $critical_css = '
            <style id="extenderx-dark-reader-critical-fallback">
                html.dark-reader-critical-loading {
                    filter: invert(1) hue-rotate(180deg) !important;
                    transition: filter 0.1s ease !important;
                }
                html.dark-reader-critical-loading img,
                html.dark-reader-critical-loading video,
                html.dark-reader-critical-loading iframe,
                html.dark-reader-critical-loading svg,
                html.dark-reader-critical-loading [style*="background-image"],
                html.dark-reader-critical-loading canvas,
                html.dark-reader-critical-loading embed,
                html.dark-reader-critical-loading object {
                    filter: invert(1) hue-rotate(180deg) !important;
                }
            </style>
            <script>
                (function() {
                    try {
                        var defaultDark = ' . ($default_dark_mode ? 'true' : 'false') . ';
                        var useSystemColorScheme = ' . ($use_system_color_scheme ? 'true' : 'false') . ';
                        var shouldApply = false;

                        var manualToggle = localStorage.getItem("extenderxDarkModeToggle");
                        if (manualToggle === "true") {
                            var stored = localStorage.getItem("extenderxDarkModeEnabled");
                            shouldApply = stored ? stored === "1" : defaultDark;
                        }
                        else if (useSystemColorScheme && window.matchMedia) {
                            if (window.matchMedia("(prefers-color-scheme: dark)").matches) {
                                shouldApply = true;
                            } else if (window.matchMedia("(prefers-color-scheme: light)").matches) {
                                shouldApply = false;
                            } else {
                                var stored = localStorage.getItem("extenderxDarkModeEnabled");
                                shouldApply = stored ? stored === "1" : defaultDark;
                            }
                        }
                        else {
                            var stored = localStorage.getItem("extenderxDarkModeEnabled");
                            shouldApply = stored ? stored === "1" : defaultDark;
                        }

                        if (shouldApply) {
                            document.documentElement.classList.add("dark-reader-critical-loading");
                        }
                    } catch (e) {
                        var shouldFallback = false;
                        if (' . ($use_system_color_scheme ? 'true' : 'false') . ' && window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
                            shouldFallback = true;
                        } else if (' . ($default_dark_mode ? 'true' : 'false') . ') {
                            shouldFallback = true;
                        }

                        if (shouldFallback) {
                            document.documentElement.classList.add("dark-reader-critical-loading");
                        }
                    }
                })();
            </script>';

            echo wp_kses($critical_css, [
                'style' => ['id' => []],
                'script' => [],
            ]);
        }, 1);
    }

    public function render_toggle_button() {
        if (!$this->settings['show_toggle']) {
            return;
        }

        $position = $this->settings['toggle_position'];
        $style = $this->settings['toggle_style'];

?>
        <div id="extenderx-dark-reader-toggle"
            class="extenderx-dark-reader-toggle <?php echo esc_attr($position); ?> style-<?php echo esc_attr($style); ?>">
            <button aria-label="<?php echo esc_attr__('Toggle Dark Mode', 'extenderx'); ?>">
                <?php $this->render_toggle_style($style); ?>
            </button>
        </div>
    <?php
    }

    private function render_toggle_style($style) {
        switch ($style) {
            case 'default':
            case 'round':
                $this->render_default_style();
                break;
            case 'switch':
                $this->render_switch_style();
                break;
            case 'toggle':
                $this->render_toggle_style_icons();
                break;
            case 'minimal':
                $this->render_minimal_style();
                break;
        }
    }

    private function render_default_style() {
        echo '<span class="sun-icon">☀️</span>';
        echo '<span class="moon-icon">🌙</span>';
    }

    private function render_switch_style() {
        echo '<span class="switch-track"></span>';
        echo '<span class="switch-thumb"></span>';
    }

    private function render_toggle_style_icons() {
    ?>
        <span class="toggle-track">
            <span class="toggle-sun">☀️</span>
            <span class="toggle-moon">🌙</span>
        </span>
        <span class="toggle-thumb"></span>
<?php
    }

    private function render_minimal_style() {
        echo '<span class="minimal-icon"></span>';
    }

    public function render_effect() {
        // Called by ModuleBase via wp_footer hook
        $this->render_toggle_button();
    }
}
