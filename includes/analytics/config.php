<?php

/**
 * Ultimate Cursor Analytics Configuration
 *
 * @package UltimateCursor
 * @since 1.3.7
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get analytics configuration parameters
 *
 * @return array Analytics configuration
 */
function uc_get_analytics_config() {
    return array(
        'sdk_version'   => '1.0.0',
        'product_id'    => '2',
        'plugin_name'   => 'EXTENDERX',
        'plugin_title'  => 'Greetings from WPXERO! 🌟',
        'api_endpoint'  => 'https://wpxero.com/wp-json/dci/v1/data-insights',
        'slug'          => 'extenderx',
        'core_file'     => false,
        'plugin_deactivate_id' => false,
        'public_key'    => 'pk_IcDBGpoYi3RobKBJ2WuRkEWnBIOfccmd',
        'is_premium'    => false,
        'popup_notice'  => false,
        'deactivate_feedback' => true,
        'text_domain'   => 'extenderx',
        'plugin_msg'    => 'Make a big impact on WordPress by sharing non-sensitive plugin data. Get valuable updates and be part of something amazing!',
    );
}
