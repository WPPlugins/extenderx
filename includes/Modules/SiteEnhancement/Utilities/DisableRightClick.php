<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Utilities;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Disable Right Click
 *
 * Disable right click functionality
 */
class DisableRightClick {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'disable_right_click']);
    }

    public function disable_right_click() {
        wp_enqueue_script('xero-disable-right-click');
    }
}
