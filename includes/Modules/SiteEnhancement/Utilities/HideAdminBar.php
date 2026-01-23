<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Utilities;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hide Admin Bar
 *
 * Hide WordPress admin bar on frontend for all users
 */
class HideAdminBar {
    use Singleton;

    public function __construct() {
        add_action('after_setup_theme', [$this, 'hide_admin_bar']);
    }

    public function hide_admin_bar() {
        if (!is_admin()) {
            show_admin_bar(false);
        }
    }
}
