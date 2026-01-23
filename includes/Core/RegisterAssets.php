<?php

namespace WPXERO\ExtenderX\Core;

use WPXERO\ExtenderX\Traits\Singleton;

defined('ABSPATH') || exit;

class RegisterAssets {
    use Singleton;


    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
    }

    public function register_assets() {
        // Utility protection scripts
        wp_register_script('xero-disable-copy', XERO_EXTENDERX_DIST_URL . 'modules/global/utilities/disable-copy/index.js', array(), null, true);
        wp_register_script('xero-disable-right-click', XERO_EXTENDERX_DIST_URL . 'modules/global/utilities/disable-right-click/index.js', array(), null, true);
        wp_register_script('xero-disable-inspect', XERO_EXTENDERX_DIST_URL . 'modules/global/utilities/disable-inspect/index.js', array(), null, true);
        wp_register_script('xero-disable-image-drag', XERO_EXTENDERX_DIST_URL . 'modules/global/utilities/disable-image-drag/index.js', array(), null, true);

        // Site modes scripts
        wp_register_script('xero-age-verification', XERO_EXTENDERX_DIST_URL . 'modules/global/site-modes/age-verification/index.js', array(), null, true);
        wp_register_script('xero-cookie-consent', XERO_EXTENDERX_DIST_URL . 'modules/global/site-modes/cookie-consent/index.js', array(), null, true);
        wp_register_script('xero-gdpr-notice', XERO_EXTENDERX_DIST_URL . 'modules/global/site-modes/gdpr-notice/index.js', array(), null, true);
        wp_register_script('xero-exit-intent', XERO_EXTENDERX_DIST_URL . 'modules/global/site-modes/exit-intent/index.js', array(), null, true);

        // UI enhancement scripts
        wp_register_script('xero-back-to-top', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/back-to-top/index.js', array(), null, true);
        wp_register_script('xero-sticky-header', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/sticky-header/index.js', array(), null, true);
        wp_register_script('xero-sticky-footer', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/sticky-footer/index.js', array(), null, true);
        wp_register_script('xero-scroll-progress', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/scroll-progress/index.js', array(), null, true);
        wp_register_script('xero-smart-scroll', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/smart-scroll/index.js', array(), null, true);
        wp_register_script('xero-custom-cursor', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/custom-cursor/index.js', array(), null, true);
        wp_register_script('xero-cursor-hover-effects', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/cursor-hover-effects/index.js', array(), null, true);
        wp_register_script('xero-cursor-grab-mode', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/cursor-grab-mode/index.js', array(), null, true);
        wp_register_script('xero-smooth-scrolling', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/smooth-scrolling/index.js', array(), null, true);

        // Seasonal effects scripts
        wp_register_script('xero-christmas-lights', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/christmas-lights/index.js', array(), null, true);
        wp_register_script('xero-fireworks', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/fireworks/index.js', array(), null, true);
        wp_register_script('xero-autumn-leaves', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/autumn-leaves/index.js', array(), null, true);
        wp_register_script('xero-falling-hearts', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/falling-hearts/index.js', array(), null, true);
        wp_register_script('xero-halloween-bats', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/halloween-bats/index.js', array(), null, true);
        wp_register_script('xero-ramadan-lanterns', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/ramadan-lanterns/index.js', array(), null, true);
        wp_register_script('xero-weather-effects', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/weather-effects/index.js', array(), null, true);
        wp_register_script('xero-particle-background', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/particle-background/index.js', array(), null, true);
        wp_register_script('xero-fireflies', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/fireflies/index.js', array(), null, true);
        wp_register_script('xero-stars-twinkling', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/stars-twinkling/index.js', array(), null, true);
        wp_register_script('xero-floating-emojis', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/floating-emojis/index.js', array(), null, true);
        wp_register_script('xero-floating-shapes', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/floating-shapes/index.js', array(), null, true);
        wp_register_script('xero-light-beams', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/light-beams/index.js', array(), null, true);
        wp_register_script('xero-mouse-trail', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/mouse-trail/index.js', array(), null, true);

        // Appearance scripts
        wp_register_script('xero-dark-mode', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/dark-mode/index.js', array('darkreader'), null, true);
        wp_register_script('xero-light-mode-toggle', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/light-mode-toggle/index.js', array(), null, true);
        wp_register_script('xero-auto-mode', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/auto-mode/index.js', array(), null, true);
        wp_register_script('xero-high-contrast-mode', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/high-contrast-mode/index.js', array(), null, true);
        wp_register_script('xero-reading-mode', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/reading-mode/index.js', array(), null, true);
        wp_register_script('xero-night-shift-mode', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/night-shift-mode/index.js', array(), null, true);
        wp_register_script('xero-fade-in-load', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/fade-in-load/index.js', array(), null, true);
        wp_register_script('xero-smooth-transitions', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/smooth-transitions/index.js', array(), null, true);
        wp_register_script('xero-skeleton-loader', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/skeleton-loader/index.js', array(), null, true);
        wp_register_script('xero-preloader', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/preloader/index.js', array(), null, true);
        wp_register_script('xero-glassmorphism', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/glassmorphism/index.js', array(), null, true);
        wp_register_script('xero-blur-background-on-scroll', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/blur-background-on-scroll/index.js', array(), null, true);
        wp_register_script('xero-dim-background-on-idle', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/dim-background-on-idle/index.js', array(), null, true);

        // Animation scripts
        wp_register_script('xero-entrance-effects', XERO_EXTENDERX_DIST_URL . 'modules/global/animations/entrance-effects/index.js', array(), null, true);
        wp_register_script('xero-parallax-effects', XERO_EXTENDERX_DIST_URL . 'modules/global/animations/parallax-effects/index.js', array(), null, true);
        wp_register_script('xero-floating-elements', XERO_EXTENDERX_DIST_URL . 'modules/global/animations/floating-elements/index.js', array(), null, true);

        // Marketing scripts
        wp_register_script('xero-announcement-bar', XERO_EXTENDERX_DIST_URL . 'modules/global/marketing/announcement-bar/index.js', array(), null, true);
        wp_register_script('xero-floating-cta-button', XERO_EXTENDERX_DIST_URL . 'modules/global/marketing/floating-cta-button/index.js', array(), null, true);
        wp_register_script('xero-sticky-whatsapp-button', XERO_EXTENDERX_DIST_URL . 'modules/global/marketing/sticky-whatsapp-button/index.js', array(), null, true);
        wp_register_script('xero-cta-pulse-animation', XERO_EXTENDERX_DIST_URL . 'modules/global/marketing/cta-pulse-animation/index.js', array(), null, true);
        wp_register_script('xero-global-countdown-banner', XERO_EXTENDERX_DIST_URL . 'modules/global/marketing/global-countdown-banner/index.js', array(), null, true);

        //vender script register
        wp_register_script('darkreader', XERO_EXTENDERX_ASSETS_URL . 'vendor/darkreader/darkreader.js', array(), null, true);

        // Site modes styles with RTL support
        $this->register_style_with_rtl('xero-age-verification', 'modules/global/site-modes/age-verification/style-style.css');
        $this->register_style_with_rtl('xero-cookie-consent', 'modules/global/site-modes/cookie-consent/style-style.css');
        $this->register_style_with_rtl('xero-gdpr-notice', 'modules/global/site-modes/gdpr-notice/style-style.css');
        $this->register_style_with_rtl('xero-exit-intent', 'modules/global/site-modes/exit-intent/style-style.css');

        // UI enhancement styles with RTL support
        $this->register_style_with_rtl('xero-back-to-top', 'modules/global/ui-enhancements/back-to-top/style-style.css');
        $this->register_style_with_rtl('xero-sticky-header', 'modules/global/ui-enhancements/sticky-header/style-style.css');
        $this->register_style_with_rtl('xero-sticky-footer', 'modules/global/ui-enhancements/sticky-footer/style-style.css');
        $this->register_style_with_rtl('xero-scroll-progress', 'modules/global/ui-enhancements/scroll-progress/style-style.css');
        $this->register_style_with_rtl('xero-smart-scroll', 'modules/global/ui-enhancements/smart-scroll/style-style.css');
        $this->register_style_with_rtl('xero-custom-cursor', 'modules/global/ui-enhancements/custom-cursor/style-style.css');
        $this->register_style_with_rtl('xero-cursor-hover-effects', 'modules/global/ui-enhancements/cursor-hover-effects/style-style.css');
        $this->register_style_with_rtl('xero-cursor-grab-mode', 'modules/global/ui-enhancements/cursor-grab-mode/style-style.css');
        $this->register_style_with_rtl('xero-scrollbar-styler', 'modules/global/ui-enhancements/scrollbar-styler/style-style.css');
        $this->register_style_with_rtl('xero-smooth-scrolling', 'modules/global/ui-enhancements/smooth-scrolling/style-style.css');

        // Seasonal effects styles with RTL support
        $this->register_style_with_rtl('xero-christmas-lights', 'modules/global/seasonal/christmas-lights/style-style.css');
        $this->register_style_with_rtl('xero-fireworks', 'modules/global/seasonal/fireworks/style-style.css');
        $this->register_style_with_rtl('xero-autumn-leaves', 'modules/global/seasonal/autumn-leaves/style-style.css');
        $this->register_style_with_rtl('xero-falling-hearts', 'modules/global/seasonal/falling-hearts/style-style.css');
        $this->register_style_with_rtl('xero-halloween-bats', 'modules/global/seasonal/halloween-bats/style-style.css');
        $this->register_style_with_rtl('xero-ramadan-lanterns', 'modules/global/seasonal/ramadan-lanterns/style-style.css');
        $this->register_style_with_rtl('xero-weather-effects', 'modules/global/seasonal/weather-effects/style-style.css');
        $this->register_style_with_rtl('xero-particle-background', 'modules/global/seasonal/particle-background/style-style.css');
        $this->register_style_with_rtl('xero-fireflies', 'modules/global/seasonal/fireflies/style-style.css');
        $this->register_style_with_rtl('xero-stars-twinkling', 'modules/global/seasonal/stars-twinkling/style-style.css');
        $this->register_style_with_rtl('xero-floating-emojis', 'modules/global/seasonal/floating-emojis/style-style.css');
        $this->register_style_with_rtl('xero-floating-shapes', 'modules/global/seasonal/floating-shapes/style-style.css');
        $this->register_style_with_rtl('xero-light-beams', 'modules/global/seasonal/light-beams/style-style.css');
        $this->register_style_with_rtl('xero-mouse-trail', 'modules/global/seasonal/mouse-trail/style-style.css');

        // Appearance styles with RTL support
        $this->register_style_with_rtl('xero-dark-mode', 'modules/global/appearance/dark-mode/style-style.css');
        $this->register_style_with_rtl('xero-light-mode-toggle', 'modules/global/appearance/light-mode-toggle/style-style.css');
        $this->register_style_with_rtl('xero-auto-mode', 'modules/global/appearance/auto-mode/style-style.css');
        $this->register_style_with_rtl('xero-high-contrast-mode', 'modules/global/appearance/high-contrast-mode/style-style.css');
        $this->register_style_with_rtl('xero-reading-mode', 'modules/global/appearance/reading-mode/style-style.css');
        $this->register_style_with_rtl('xero-night-shift-mode', 'modules/global/appearance/night-shift-mode/style-style.css');
        $this->register_style_with_rtl('xero-fade-in-load', 'modules/global/appearance/fade-in-load/style-style.css');
        $this->register_style_with_rtl('xero-smooth-transitions', 'modules/global/appearance/smooth-transitions/style-style.css');
        $this->register_style_with_rtl('xero-skeleton-loader', 'modules/global/appearance/skeleton-loader/style-style.css');
        $this->register_style_with_rtl('xero-preloader', 'modules/global/appearance/preloader/style-style.css');
        $this->register_style_with_rtl('xero-glassmorphism', 'modules/global/appearance/glassmorphism/style-style.css');
        $this->register_style_with_rtl('xero-blur-background-on-scroll', 'modules/global/appearance/blur-background-on-scroll/style-style.css');
        $this->register_style_with_rtl('xero-dim-background-on-idle', 'modules/global/appearance/dim-background-on-idle/style-style.css');

        // Animation styles with RTL support
        $this->register_style_with_rtl('xero-entrance-effects', 'modules/global/animations/entrance-effects/style-style.css');
        $this->register_style_with_rtl('xero-parallax-effects', 'modules/global/animations/parallax-effects/style-style.css');
        $this->register_style_with_rtl('xero-floating-elements', 'modules/global/animations/floating-elements/style-style.css');

        // Marketing styles with RTL support
        $this->register_style_with_rtl('xero-announcement-bar', 'modules/global/marketing/announcement-bar/style-style.css');
        $this->register_style_with_rtl('xero-floating-cta-button', 'modules/global/marketing/floating-cta-button/style-style.css');
        $this->register_style_with_rtl('xero-sticky-whatsapp-button', 'modules/global/marketing/sticky-whatsapp-button/style-style.css');
        $this->register_style_with_rtl('xero-cta-pulse-animation', 'modules/global/marketing/cta-pulse-animation/style-style.css');
        $this->register_style_with_rtl('xero-global-countdown-banner', 'modules/global/marketing/global-countdown-banner/style-style.css');
    }

    /**
     * Register a stylesheet with RTL support
     *
     * @param string $handle    The stylesheet handle.
     * @param string $src_path  The relative path to the stylesheet.
     * @param array  $deps      Optional. An array of registered stylesheet handles.
     * @param string $ver       Optional. String specifying stylesheet version number.
     * @param string $media     Optional. The media for which this stylesheet has been defined.
     */
    private function register_style_with_rtl($handle, $src_path, $deps = array(), $ver = null, $media = 'all') {
        $src = XERO_EXTENDERX_DIST_URL . $src_path;

        wp_register_style($handle, $src, $deps, $ver, $media);

        // Add RTL stylesheet if available
        if (is_rtl()) {
            $rtl_path = str_replace('.css', '-rtl.css', $src_path);
            $rtl_file = XERO_EXTENDERX_DIST_PATH . $rtl_path;

            if (file_exists($rtl_file)) {
                wp_style_add_data($handle, 'rtl', 'replace');
                wp_style_add_data($handle, 'suffix', '-rtl');
            }
        }
    }
}
