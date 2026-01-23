<?php

namespace WPXERO\ExtenderX\Core;

use WPXERO\ExtenderX\Traits\Singleton;

defined('ABSPATH') || exit;

class Utils {
    use Singleton;


    public function __construct() {
    }


    /**
     * Get ExtenderX setting value by path
     *
     * @param string $path Dot notation path (e.g., 'global.appearance.dark_mode')
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function extenderx_get_setting($path, $default = null) {
        $settings = get_option('extenderx_settings', []);
        $keys = explode('.', $path);

        foreach ($keys as $key) {
            if (isset($settings[$key])) {
                $settings = $settings[$key];
            } else {
                return $default;
            }
        }

        return $settings;
    }

    // Usage examples:
    // $dark_mode = extenderx_get_setting('global.appearance.dark_mode', false);
    // $entrance_fx = extenderx_get_setting('global.animations.entrance_effects', false);
    // $elementor = extenderx_get_setting('builder_support.elementor.enabled', true);
}
