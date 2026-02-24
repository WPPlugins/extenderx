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
        add_filter('show_admin_bar', '__return_false');
    }
}
