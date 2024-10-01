<?php


declare(strict_types=1);

namespace XERO\EXTENDERX;

defined('ABSPATH') || exit;

class Utils {


    public function __construct() {
        // add addmin body class
        add_filter('admin_body_class', [$this, 'extenderx_editor_custom_body_class']);
    }


    // add body class for extenderx
    public function extenderx_editor_custom_body_class($classes) {
        // Check if we are on editing screen in WordPress admin
        if (is_admin() && isset($_GET['action']) && $_GET['action'] === 'edit') { // phpcs:ignore
            $classes .= ' extenderx-editor';
        }
        return $classes;
    }
}


if (class_exists('XERO\EXTENDERX\Utils')) {
    new Utils();
}
