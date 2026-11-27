<?php

namespace WPXERO\ExtenderX\Core;

use WPXERO\ExtenderX\Traits\Singleton;

defined('ABSPATH') || exit;

class RegisterAssets {
    use Singleton;


    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        add_action('admin_enqueue_scripts', [$this, 'register_admin_assets']);
    }

    /**
     * Cache-busting version for a dist asset: plugin version + file mtime, so
     * every rebuild produces a new URL and browsers can't serve stale copies.
     *
     * @param string $relative Path relative to the dist directory.
     * @return string
     */
    private function dist_version(string $relative): string {
        $file = XERO_EXTENDERX_DIR_PATH . 'dist/' . $relative;
        $mtime = file_exists($file) ? (string) filemtime($file) : '';
        return $mtime ? XERO_EXTENDERX_VERSION . '.' . $mtime : XERO_EXTENDERX_VERSION;
    }

    public function register_admin_assets() {
        wp_register_script('darkreader', XERO_EXTENDERX_ASSETS_URL . 'vendor/darkreader/darkreader.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-dark-mode', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/dark-mode/index.js', array('darkreader'), XERO_EXTENDERX_VERSION, true);
        $this->register_style_with_rtl('xero-dark-mode', 'modules/global/appearance/dark-mode/style-style.css');
    }

    public function register_assets() {
        // Utility protection scripts
        wp_register_script('xero-disable-copy', XERO_EXTENDERX_DIST_URL . 'modules/global/utilities/disable-copy/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-disable-right-click', XERO_EXTENDERX_DIST_URL . 'modules/global/utilities/disable-right-click/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-disable-inspect', XERO_EXTENDERX_DIST_URL . 'modules/global/utilities/disable-inspect/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-disable-image-drag', XERO_EXTENDERX_DIST_URL . 'modules/global/utilities/disable-image-drag/index.js', array(), XERO_EXTENDERX_VERSION, true);

        // Site modes scripts
        wp_register_script('xero-age-verification', XERO_EXTENDERX_DIST_URL . 'modules/global/site-modes/age-verification/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-cookie-consent', XERO_EXTENDERX_DIST_URL . 'modules/global/site-modes/cookie-consent/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-gdpr-notice', XERO_EXTENDERX_DIST_URL . 'modules/global/site-modes/gdpr-notice/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-exit-intent', XERO_EXTENDERX_DIST_URL . 'modules/global/site-modes/exit-intent/index.js', array(), XERO_EXTENDERX_VERSION, true);

        // UI enhancement scripts
        wp_register_script('xero-back-to-top', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/back-to-top/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-scroll-to-bottom', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/scroll-to-bottom/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-sticky-header', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/sticky-header/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-sticky-footer', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/sticky-footer/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-scroll-progress', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/scroll-progress/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-smooth-scrolling', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/smooth-scrolling/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-scrollspy', XERO_EXTENDERX_DIST_URL . 'modules/global/ui-enhancements/scrollspy/index.js', array(), XERO_EXTENDERX_VERSION, true);

        // Seasonal effects scripts
        wp_register_script('xero-winter-snow', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/winter-snow/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-spring-petals', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/spring-petals/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-summer-sun', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/summer-sun/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-sales-confetti', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/sales-confetti/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-christmas-lights', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/christmas-lights/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-fireworks', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/fireworks/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-autumn-leaves', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/autumn-leaves/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-falling-hearts', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/falling-hearts/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-halloween-bats', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/halloween-bats/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-ramadan-lanterns', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/ramadan-lanterns/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-weather-effects', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/weather-effects/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-particle-background', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/particle-background/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-fireflies', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/fireflies/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-stars-twinkling', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/stars-twinkling/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-floating-emojis', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/floating-emojis/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-floating-shapes', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/floating-shapes/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-light-beams', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/light-beams/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-mouse-trail', XERO_EXTENDERX_DIST_URL . 'modules/global/seasonal/mouse-trail/index.js', array(), XERO_EXTENDERX_VERSION, true);

        // Appearance scripts
        wp_register_script('xero-dark-mode', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/dark-mode/index.js', array('darkreader'), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-auto-mode', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/auto-mode/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-skeleton-loader', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/skeleton-loader/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-preloader', XERO_EXTENDERX_DIST_URL . 'modules/global/appearance/preloader/index.js', array(), XERO_EXTENDERX_VERSION, true);

        // Animation scripts. Versioned by file mtime — the static plugin
        // version left browsers holding stale cached runtimes across updates
        // (the animation engines change often and a stale copy breaks the
        // reveal contract with the PHP-injected critical CSS/bootstrap).
        wp_register_script('xero-scroll-effects', XERO_EXTENDERX_DIST_URL . 'modules/global/animations/scroll-effects/index.js', array(), $this->dist_version('modules/global/animations/scroll-effects/index.js'), true);
        wp_register_script('xero-site-wide', XERO_EXTENDERX_DIST_URL . 'modules/global/animations/site-wide/index.js', array(), $this->dist_version('modules/global/animations/site-wide/index.js'), true);

        // Accessibility scripts. Versioned by file mtime — the toolbar's JS
        // and its PHP-rendered markup share an ARIA contract, so a stale
        // cached bundle would leave the widget half-wired.
        wp_register_script('xero-a11y-toolbar', XERO_EXTENDERX_DIST_URL . 'modules/global/accessibility/toolbar/index.js', array(), $this->dist_version('modules/global/accessibility/toolbar/index.js'), true);
        wp_register_script('xero-a11y-toolbar-pro', XERO_EXTENDERX_DIST_URL . 'modules/global/accessibility/toolbar-pro/index.js', array('xero-a11y-toolbar'), $this->dist_version('modules/global/accessibility/toolbar-pro/index.js'), true);
        wp_register_script('xero-a11y-skip-link', XERO_EXTENDERX_DIST_URL . 'modules/global/accessibility/skip-link/index.js', array(), $this->dist_version('modules/global/accessibility/skip-link/index.js'), true);

        // Marketing scripts
        wp_register_script('xero-announcement-bar', XERO_EXTENDERX_DIST_URL . 'modules/global/marketing/announcement-bar/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-floating-cta-button', XERO_EXTENDERX_DIST_URL . 'modules/global/marketing/floating-cta-button/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-sticky-whatsapp-button', XERO_EXTENDERX_DIST_URL . 'modules/global/marketing/sticky-whatsapp-button/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-cta-pulse-animation', XERO_EXTENDERX_DIST_URL . 'modules/global/marketing/cta-pulse-animation/index.js', array(), XERO_EXTENDERX_VERSION, true);
        wp_register_script('xero-global-countdown-banner', XERO_EXTENDERX_DIST_URL . 'modules/global/marketing/global-countdown-banner/index.js', array(), XERO_EXTENDERX_VERSION, true);

        //vender script register
        wp_register_script('darkreader', XERO_EXTENDERX_ASSETS_URL . 'vendor/darkreader/darkreader.js', array(), XERO_EXTENDERX_VERSION, true);

        // Site modes styles with RTL support
        $this->register_style_with_rtl('xero-age-verification', 'modules/global/site-modes/age-verification/style-style.css');
        $this->register_style_with_rtl('xero-cookie-consent', 'modules/global/site-modes/cookie-consent/style-style.css');
        $this->register_style_with_rtl('xero-gdpr-notice', 'modules/global/site-modes/gdpr-notice/style-style.css');
        $this->register_style_with_rtl('xero-exit-intent', 'modules/global/site-modes/exit-intent/style-style.css');

        // UI enhancement styles with RTL support
        $this->register_style_with_rtl('xero-back-to-top', 'modules/global/ui-enhancements/back-to-top/style-style.css');
        $this->register_style_with_rtl('xero-scroll-to-bottom', 'modules/global/ui-enhancements/scroll-to-bottom/style-style.css');
        $this->register_style_with_rtl('xero-sticky-header', 'modules/global/ui-enhancements/sticky-header/style-style.css');
        $this->register_style_with_rtl('xero-sticky-footer', 'modules/global/ui-enhancements/sticky-footer/style-style.css');
        $this->register_style_with_rtl('xero-scroll-progress', 'modules/global/ui-enhancements/scroll-progress/style-style.css');
        $this->register_style_with_rtl('xero-scrollbar-styler', 'modules/global/ui-enhancements/scrollbar-styler/style-style.css');
        $this->register_style_with_rtl('xero-smooth-scrolling', 'modules/global/ui-enhancements/smooth-scrolling/style-style.css');
        $this->register_style_with_rtl('xero-scrollspy', 'modules/global/ui-enhancements/scrollspy/style-style.css');

        // Seasonal effects styles with RTL support
        $this->register_style_with_rtl('xero-winter-snow', 'modules/global/seasonal/winter-snow/style-style.css');
        $this->register_style_with_rtl('xero-spring-petals', 'modules/global/seasonal/spring-petals/style-style.css');
        $this->register_style_with_rtl('xero-summer-sun', 'modules/global/seasonal/summer-sun/style-style.css');
        $this->register_style_with_rtl('xero-sales-confetti', 'modules/global/seasonal/sales-confetti/style-style.css');
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
        $this->register_style_with_rtl('xero-auto-mode', 'modules/global/appearance/auto-mode/style-style.css');
        $this->register_style_with_rtl('xero-skeleton-loader', 'modules/global/appearance/skeleton-loader/style-style.css');
        $this->register_style_with_rtl('xero-preloader', 'modules/global/appearance/preloader/style-style.css');

        // Animation styles with RTL support
        $this->register_style_with_rtl('xero-scroll-effects', 'modules/global/animations/scroll-effects/style-style.css');
        $this->register_style_with_rtl('xero-site-wide', 'modules/global/animations/site-wide/style-style.css');

        // Accessibility styles with RTL support
        $this->register_style_with_rtl('xero-a11y-toolbar', 'modules/global/accessibility/toolbar/style-style.css');
        $this->register_style_with_rtl('xero-a11y-toolbar-pro', 'modules/global/accessibility/toolbar-pro/style-style.css');
        $this->register_style_with_rtl('xero-a11y-skip-link', 'modules/global/accessibility/skip-link/style-style.css');
        $this->register_style_with_rtl('xero-a11y-focus', 'modules/global/accessibility/focus-outlines/style-style.css');

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

        // A null version printed no ?ver at all, so browsers and page caches
        // kept serving a module's old CSS after a plugin update.
        if (null === $ver) {
            $ver = $this->dist_version($src_path);
        }

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
