<?php

namespace WPXERO\ExtenderX\Modules\Gutenberg;

use WPXERO\ExtenderX\Modules\Gutenberg\Register;
use WPXERO\ExtenderX\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * Handles frontend attributes for blocks
 */
class Attributes {
    use Singleton;
    public function __construct() {
        add_filter( 'render_block', [$this, 'process_block_content'], 10, 3 );
        add_filter( 'xero_extenderx_save_element_markup', [$this, 'add_extenderx_data_attributes'], 10, 3 );
    }

    public function process_block_content( $block_content, $parsed_block, $instance ) {

// Fast path: skip allocating the tag processor for blocks that

// don't carry any ExtenderX attrs. `render_block` fires for every

// block on every render, so this short-circuit matters site-wide.
        if ( empty( $parsed_block['attrs']['extenderx'] ) ) {
            return $block_content;
        }

// WP_HTML_Tag_Processor was introduced in WordPress 6.2. Guard against
// older installs so the plugin degrades gracefully instead of fataling
// when a block carries ExtenderX attributes.
        if ( ! class_exists( '\WP_HTML_Tag_Processor' ) ) {
            return $block_content;
        }

        $tag_processor = new \WP_HTML_Tag_Processor( $block_content );

// If there's no top-level tag (text-only or empty markup) there's

// nothing we can mutate; bail before invoking downstream filters.
        if ( !$tag_processor->next_tag() ) {
            return $block_content;
        }

        /**
         * Filters the tag processor for the current block before ExtenderX
         * writes data attributes. Receives a `WP_HTML_Tag_Processor`
         * positioned on the block's outer tag.
         *
         * @param \WP_HTML_Tag_Processor $tag_processor Positioned processor.
         * @param array                  $parsed_block  Parsed block data.
         * @param mixed                  $instance      Block instance.
         */
        $tag_processor = apply_filters( 'xero_extenderx_save_element_markup', $tag_processor, $parsed_block, $instance );

        if ( method_exists( $tag_processor, 'get_updated_html' ) ) {
            return $tag_processor->get_updated_html();
        }

        return $block_content;
    }

    public function add_extenderx_data_attributes( $tag_processor, $parsed_block, $instance ) {
        $extensions = $parsed_block['attrs']['extenderx'] ?? [];

        if ( empty( $extensions ) ) {
            return $tag_processor;
        }

// Visibility is applied first (and gated separately) so disabling
        // it globally also stops legacy block attrs from hiding content.
        $tag_processor = $this->apply_visibility( $tag_processor, $parsed_block );

        foreach ( $extensions as $key => $extension ) {

            if ( $key === 'visibility' || empty( $extension['active'] ) ) {
                continue;
            }

// Mirror the gating used by asset registration: an extension

// disabled in plugin settings must not produce data attrs or

// attempt to enqueue handles that were never registered.
            if ( !Register::is_extension_globally_enabled( $key ) ) {
                continue;
            }

            if ( $key === 'tooltip' ) {
                wp_enqueue_style( 'tippy' );
            }

            wp_enqueue_style( "@extenderx/extensions/{$key}/style" );
            wp_enqueue_script( "@extenderx/extensions/{$key}/script" );

            $slug = strtolower( $key );
            $tag_processor->set_attribute( 'data-extenderx-' . $slug, wp_json_encode( $extension ) );
            $tag_processor->add_class( 'extenderx-' . $slug );
        }

        return $tag_processor;
    }

    private function apply_visibility( $tag_processor, $parsed_block ) {
        $visibility = $parsed_block['attrs']['extenderx']['visibility'] ?? [];

        if ( empty( $visibility['active'] ) ) {
            return $tag_processor;
        }

// Honour the global Visibility extension toggle. Without this

// check, disabling the extension in settings still leaves legacy

// block attrs hiding content on the frontend.
        if ( !Register::is_extension_globally_enabled( 'visibility' ) ) {
            return $tag_processor;
        }

        $classes = [
            'hidden'  => 'extenderx-hidden',
            'desktop' => 'extenderx-hidden-desktop',
            'tablet'  => 'extenderx-hidden-tablet',
            'mobile'  => 'extenderx-hidden-mobile',
        ];

        foreach ( $classes as $key => $class ) {
            if ( !empty( $visibility[$key] ) ) {
                $tag_processor->add_class( $class );
            }

        }

        wp_enqueue_style( '@extenderx/extensions/visibility/style' );

        return $tag_processor;
    }

}
