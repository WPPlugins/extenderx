<?php

declare(strict_types=1);

namespace XERO\EXTENDERX;

defined('ABSPATH') || exit;

/**
 * Handles frontend attributes for blocks
 */
class Attributes {

    public function __construct() {
        add_filter('render_block', [$this, 'process_block_content'], 10, 3);
        add_filter('xero_extenderx_save_element_markup', [$this, 'add_extenderx_data_attributes'], 10, 3);
    }

    public function process_block_content($block_content, $parsed_block, $instance) {
        $tag_processor = new \WP_HTML_Tag_Processor($block_content);
        $tag_processor->next_tag();

        // Apply custom filter to the tag processor
        $tag_processor = apply_filters('xero_extenderx_save_element_markup', $tag_processor, $parsed_block, $instance);

        // Update block content if the method exists
        if (method_exists($tag_processor, 'get_updated_html')) {
            $block_content = $tag_processor->get_updated_html();
        }

        return sprintf('%1$s', $block_content);
    }

    public function add_extenderx_data_attributes($block_content, $parsed_block, $instance) {
        $extensions = $parsed_block['attrs']['extenderx'] ?? [];

        // If no extensions, return block content
        if (empty($extensions)) {
            return $block_content;
        }

        // Add visibility-related attributes first
        $block_content = $this->apply_visibility($block_content, $parsed_block);

        // Loop through other extensions
        foreach ($extensions as $key => $extension) {
            if ($key === 'visibility' || empty($extension['active'])) {
                continue;
            }

            //if tooltip then enqueue tippy
            if ($key === 'tooltip') {
                wp_enqueue_style('tippy');
            }

            // Enqueue styles and scripts for active extensions
            wp_enqueue_style("@extenderx/extensions/{$key}/style");
            wp_enqueue_script("@extenderx/extensions/{$key}/script");

            // Add data attribute and class for each extension
            $block_content->set_attribute('data-extenderx-' . strtolower($key), wp_json_encode($extension));
            $block_content->add_class('extenderx-' . strtolower($key));
        }

        return $block_content;
    }

    private function apply_visibility($block_content, $parsed_block) {
        $visibility = $parsed_block['attrs']['extenderx']['visibility'] ?? [];

        // Return if visibility is not active
        if (empty($visibility['active'])) {
            return $block_content;
        }

        // Visibility classes map
        $classes = [
            'hidden'  => 'extenderx-hidden',
            'desktop' => 'extenderx-hidden-desktop',
            'tablet'  => 'extenderx-hidden-tablet',
            'mobile'  => 'extenderx-hidden-mobile'
        ];

        // Add visibility classes based on attributes
        foreach ($classes as $key => $class) {
            if (!empty($visibility[$key])) {
                $block_content->add_class($class);
            }
        }

        wp_enqueue_style("@extenderx/extensions/visibility/style");


        return $block_content;
    }
}

new Attributes();
