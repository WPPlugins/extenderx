<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Utilities;

use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Disable Image Drag
 *
 * Disable image dragging functionality
 */
class DisableImageDrag {
    use Singleton;

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'disable_image_drag']);
    }

    public function disable_image_drag() {
        wp_enqueue_script('xero-disable-image-drag');
    }
}
