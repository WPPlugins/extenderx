<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility;

use WPXERO\ExtenderX\Traits\Singleton;
use WP_REST_Request;
use WP_REST_Response;
use WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Alt Text Audit
 *
 * Lists image attachments with no alt text, so an author can fix them from
 * one screen. Premium: the class is only instantiated when ModuleBase's
 * licence gate passes, so the route below simply does not exist otherwise.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility
 * @since 1.9.0
 */
class AltTextAudit {
    use Singleton;

    /**
     * Results per page. Bounded so the query never walks the whole library.
     */
    private const PER_PAGE = 20;
    private const MAX_PER_PAGE = 50;

    /**
     * Register the REST route.
     *
     * @since 1.9.0
     */
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register routes.
     *
     * @since 1.9.0
     * @return void
     */
    public function register_routes(): void {
        register_rest_route('wpxero/extenderx/v1', '/a11y/alt-audit', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_items'],
            'permission_callback' => [$this, 'check_permission'],
            'args'                => [
                'page' => [
                    'type'              => 'integer',
                    'default'           => 1,
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'type'              => 'integer',
                    'default'           => self::PER_PAGE,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
    }

    /**
     * Only administrators may enumerate the media library this way.
     *
     * @since 1.9.0
     * @return bool
     */
    public function check_permission(): bool {
        return current_user_can('manage_options');
    }

    /**
     * Return one page of images missing alt text.
     *
     * @since 1.9.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_items(WP_REST_Request $request): WP_REST_Response {
        $page     = max(1, (int) $request->get_param('page'));
        $per_page = (int) $request->get_param('per_page');
        $per_page = max(1, min(self::MAX_PER_PAGE, $per_page ?: self::PER_PAGE));

        // Always paginated — an unbounded query would load the entire media
        // library into memory on sites that need this tool most.
        $query = new WP_Query([
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'date',
            'order'          => 'DESC',
            // Treat empty strings and a missing row alike: both mean no alt
            // text was ever entered.
            'meta_query'     => [
                'relation' => 'OR',
                [
                    'key'     => '_wp_attachment_image_alt',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => '_wp_attachment_image_alt',
                    'value'   => '',
                    'compare' => '=',
                ],
            ],
        ]);

        $items = [];
        foreach ($query->posts as $post) {
            $items[] = [
                'id'        => $post->ID,
                'title'     => $post->post_title,
                'thumbnail' => wp_get_attachment_image_url($post->ID, 'thumbnail'),
                'editUrl'   => get_edit_post_link($post->ID, 'raw'),
                'fileName'  => wp_basename(get_attached_file($post->ID)),
            ];
        }

        // How many images the library holds at all, so an empty library isn't
        // reported as "every image has alt text".
        $library_total = 0;
        foreach ((array) wp_count_attachments('image') as $mime => $count) {
            if (0 === strpos((string) $mime, 'image/')) {
                $library_total += (int) $count;
            }
        }

        return rest_ensure_response([
            'items'        => $items,
            'libraryTotal' => $library_total,
            'total'        => (int) $query->found_posts,
            'totalPages' => (int) $query->max_num_pages,
            'page'       => $page,
        ]);
    }
}
