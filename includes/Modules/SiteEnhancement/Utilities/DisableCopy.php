<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Utilities;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Disable Copy
 *
 * Disable text copy functionality
 */
class DisableCopy {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'disable_copy']);
    }

    public function disable_copy() {
        wp_enqueue_script('xero-disable-copy');
    }
}
