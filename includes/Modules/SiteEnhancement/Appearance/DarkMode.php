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
        'remember_preference' => true,
        'keyboard_shortcut' => true,
        'shortcut_key' => 'shift+d',
        'transition_speed' => 300,
        'schedule_enabled' => false,
        'schedule_start' => '20:00',
        'schedule_end' => '06:00',
        'exclude_pages' => '',
        'exclude_selectors' => '',
        'custom_css' => '',
        'wp_admin_dark_mode' => false,
        'floating_button_size' => 48,
    ];

    public function __construct() {
        $this->settings_cache = SettingsCache::instance();
        $this->load_settings();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_toggle_button']);
        add_shortcode('extenderx_dark_mode_toggle', [$this, 'render_shortcode_toggle']);

        if (!empty($this->settings['wp_admin_dark_mode'])) {
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_dark_mode'], 20);
        }
    }

    private function load_settings() {
        $dark_mode_settings = $this->settings_cache->get_setting('global.appearance.dark_mode', []);
        $this->settings = wp_parse_args($dark_mode_settings, $this->default_settings);
    }

    private function should_apply_on_current_page() {
        $exclude_pages = array_filter(array_map('trim', explode("\n", $this->settings['exclude_pages'])));
        if (empty($exclude_pages)) {
            return true;
        }

        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        $current_id = get_queried_object_id();

        foreach ($exclude_pages as $exclusion) {
            $exclusion = trim($exclusion);
            if (empty($exclusion)) continue;

            if (is_numeric($exclusion) && intval($exclusion) === $current_id) {
                return false;
            }

            if (strpos($exclusion, '/') === 0 || strpos($exclusion, 'http') === 0) {
                $pattern = str_replace(['*', '/'], ['.*', '\/'], $exclusion);
                if (preg_match('/^' . $pattern . '$/i', $current_url)) {
                    return false;
                }
            }

            if (function_exists('get_page_by_path')) {
                $page = get_page_by_path($exclusion);
                if ($page && $page->ID === $current_id) {
                    return false;
                }
            }
        }

        return true;
    }

    public function enqueue_assets() {
        $should_apply = $this->should_apply_on_current_page();

        // Add critical inline CSS to prevent FOUC
        if ($should_apply) {
            $this->add_critical_inline_css();
        }

        // Enqueue initialization script (registered in RegisterAssets)
        wp_enqueue_script('xero-dark-mode');

        // Enqueue toggle button styles (registered in RegisterAssets)
        wp_enqueue_style('xero-dark-mode');

        // Inject custom CSS for dark mode if provided
        if (!empty($this->settings['custom_css'])) {
            wp_add_inline_style('xero-dark-mode', $this->get_custom_dark_css());
        }

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
                'shouldApplyDarkMode' => $should_apply,
                'rememberPreference' => (bool)$this->settings['remember_preference'],
                'keyboardShortcut' => (bool)$this->settings['keyboard_shortcut'],
                'shortcutKey' => sanitize_text_field($this->settings['shortcut_key']),
                'transitionSpeed' => intval($this->settings['transition_speed']),
                'scheduleEnabled' => (bool)$this->settings['schedule_enabled'],
                'scheduleStart' => sanitize_text_field($this->settings['schedule_start']),
                'scheduleEnd' => sanitize_text_field($this->settings['schedule_end']),
                'excludeSelectors' => sanitize_textarea_field($this->settings['exclude_selectors']),
                'floatingButtonSize' => intval($this->settings['floating_button_size']),
                'showToggle' => (bool)$this->settings['show_toggle'],
            ]
        );
    }

    private function get_custom_dark_css() {
        $css = '';
        $custom = trim($this->settings['custom_css']);
        if (!empty($custom)) {
            $css .= "body.dark-mode {\n" . wp_strip_all_tags($custom) . "\n}\n";
        }
        return $css;
    }

    public function enqueue_admin_dark_mode() {
        wp_enqueue_script('darkreader');
        wp_enqueue_script('xero-dark-mode');
        wp_enqueue_style('xero-dark-mode');

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
                'darkSchemeBackgroundColor' => $this->settings['dark_scheme_background'],
                'darkSchemeTextColor' => $this->settings['dark_scheme_text'],
                'immediateModify' => true,
                'defaultDarkMode' => (bool)$this->settings['default_dark_mode'],
                'shouldApplyDarkMode' => true,
                'rememberPreference' => true,
                'keyboardShortcut' => (bool)$this->settings['keyboard_shortcut'],
                'shortcutKey' => sanitize_text_field($this->settings['shortcut_key']),
                'transitionSpeed' => intval($this->settings['transition_speed']),
                'scheduleEnabled' => false,
                'excludeSelectors' => '',
                'showToggle' => false,
                'isWpAdmin' => true,
            ]
        );
    }

    private function add_critical_inline_css() {
        $default_dark_mode = $this->settings['default_dark_mode'];
        $use_system_color_scheme = $this->settings['use_system_color_scheme'];
        $schedule_enabled = !empty($this->settings['schedule_enabled']);
        $schedule_start = $this->settings['schedule_start'];
        $schedule_end = $this->settings['schedule_end'];

        if (!$default_dark_mode && !$use_system_color_scheme && !$schedule_enabled) {
            return;
        }

        add_action('wp_head', function () use ($default_dark_mode, $use_system_color_scheme, $schedule_enabled, $schedule_start, $schedule_end) {
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
                        var scheduleEnabled = ' . ($schedule_enabled ? 'true' : 'false') . ';
                        var scheduleStart = "' . esc_js($schedule_start) . '";
                        var scheduleEnd = "' . esc_js($schedule_end) . '";
                        var shouldApply = false;

                        function isWithinSchedule() {
                            if (!scheduleEnabled) return null;
                            var now = new Date();
                            var currentMin = now.getHours() * 60 + now.getMinutes();
                            function parseTime(str) {
                                if (!str) return 0;
                                var p = str.split(":");
                                return parseInt(p[0], 10) * 60 + parseInt(p[1] || 0, 10);
                            }
                            var startMin = parseTime(scheduleStart);
                            var endMin = parseTime(scheduleEnd);
                            if (startMin <= endMin) {
                                return currentMin >= startMin && currentMin < endMin;
                            }
                            return currentMin >= startMin || currentMin < endMin;
                        }

                        var scheduleResult = isWithinSchedule();
                        var manualToggle = localStorage.getItem("extenderxDarkModeToggle");

                        if (manualToggle === "true") {
                            if (scheduleResult !== null) {
                                var savedState = localStorage.getItem("extenderxDarkModeScheduleState");
                                var currentState = scheduleResult ? "1" : "0";
                                if (savedState !== null && savedState !== currentState) {
                                    localStorage.removeItem("extenderxDarkModeToggle");
                                    localStorage.removeItem("extenderxDarkModeScheduleState");
                                    shouldApply = scheduleResult;
                                } else {
                                    var stored = localStorage.getItem("extenderxDarkModeEnabled");
                                    shouldApply = stored ? stored === "1" : defaultDark;
                                }
                            } else {
                                var stored = localStorage.getItem("extenderxDarkModeEnabled");
                                shouldApply = stored ? stored === "1" : defaultDark;
                            }
                        }
                        else if (scheduleResult !== null) {
                            shouldApply = scheduleResult;
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
        $size = intval($this->settings['floating_button_size']);
        $inline_style = '';

        // Apply custom button size if different from default (48px)
        if ($size && $size !== 48 && !in_array($style, ['switch', 'toggle'])) {
            $inline_style = sprintf('width:%dpx;height:%dpx;', $size, $size);
        } elseif ($size && $style === 'switch') {
            $inline_style = sprintf('width:%dpx;height:%dpx;', intval($size * 1.17), intval($size * 0.58));
        } elseif ($size && $style === 'toggle') {
            $inline_style = sprintf('width:%dpx;height:%dpx;', intval($size * 1.46), intval($size * 0.67));
        }

?>
        <div id="extenderx-dark-reader-toggle"
            class="extenderx-dark-reader-toggle <?php echo esc_attr($position); ?> style-<?php echo esc_attr($style); ?>"
            <?php if ($inline_style) : ?>style="<?php echo esc_attr($inline_style); ?>" <?php endif; ?>>
            <button aria-label="<?php echo esc_attr__('Toggle Dark Mode', 'extenderx'); ?>" aria-pressed="false" role="switch">
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

    /**
     * Shortcode: [extenderx_dark_mode_toggle]
     *
     * Attributes:
     *   style      – toggle style override (default|round|switch|toggle|minimal)
     *   size       – button size in px (default: 48)
     *   class      – extra CSS class(es)
     *   label      – custom aria-label text
     */
    public function render_shortcode_toggle($atts = []) {
        $atts = shortcode_atts([
            'style' => $this->settings['toggle_style'],
            'size'  => intval($this->settings['floating_button_size']),
            'class' => '',
            'label' => __('Toggle Dark Mode', 'extenderx'),
        ], $atts, 'extenderx_dark_mode_toggle');

        $style_name  = sanitize_text_field($atts['style']);
        $size        = intval($atts['size']);
        $extra_class = sanitize_html_class($atts['class']);
        $label       = esc_attr($atts['label']);

        $inline_style = '';
        if ($size && $size !== 48 && !in_array($style_name, ['switch', 'toggle'])) {
            $inline_style = sprintf('width:%dpx;height:%dpx;', $size, $size);
        } elseif ($size && $style_name === 'switch') {
            $inline_style = sprintf('width:%dpx;height:%dpx;', intval($size * 1.17), intval($size * 0.58));
        } elseif ($size && $style_name === 'toggle') {
            $inline_style = sprintf('width:%dpx;height:%dpx;', intval($size * 1.46), intval($size * 0.67));
        }

        ob_start();
        ?>
        <div class="extenderx-dark-reader-shortcode-toggle extenderx-dark-reader-toggle style-<?php echo esc_attr($style_name); ?> <?php echo esc_attr($extra_class); ?>"
            <?php if ($inline_style) : ?>style="<?php echo esc_attr($inline_style); ?>"<?php endif; ?>>
            <button aria-label="<?php echo $label; ?>" aria-pressed="false" role="switch">
                <?php $this->render_toggle_style($style_name); ?>
            </button>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_effect() {
        // Called by ModuleBase via wp_footer hook
        $this->render_toggle_button();
    }
}
