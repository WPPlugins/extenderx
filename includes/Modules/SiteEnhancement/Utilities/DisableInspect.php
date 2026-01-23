<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Utilities;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Disable Inspect
 *
 * Disable inspect element functionality
 */
class DisableInspect {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'disable_inspect']);
    }

    public function disable_inspect() {
        wp_enqueue_script('xero-disable-inspect');
    }
}
