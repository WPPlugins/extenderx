<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Bulk Add (premium)
 *
 * Creates a batch of posts from a list of titles — optionally nested, for
 * hierarchical post types such as pages.
 *
 * Premium: ModuleBase only instantiates this class when the licence gate
 * passes, so on a free install the routes below are never registered at all.
 * The permission callback re-checks anyway, because a licence can lapse inside
 * the life of a cached page.
 *
 * Every row is inserted through `wp_insert_post()` after its own capability,
 * status and parent checks — there is no mass-insert shortcut.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo
 * @since 1.10.0
 */
class BulkAdd {
    use Singleton;

    /** REST namespace shared with the rest of the plugin. */
    private const REST_NAMESPACE = 'wpxero/extenderx/v1';

    /** Route prefix. */
    private const ROUTE_BASE = 'save-and-go';

    /** Hard ceiling on rows per request, so one call can't run away with the site. */
    private const MAX_ITEMS = 100;

    /** Deepest nesting level accepted for hierarchical types. */
    private const MAX_DEPTH = 5;

    /**
     * Hook route registration.
     *
     * @since 1.10.0
     */
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register routes.
     *
     * @since 1.10.0
     * @return void
     */
    public function register_routes(): void {
        register_rest_route(self::REST_NAMESPACE, '/' . self::ROUTE_BASE . '/post-types', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_post_types'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route(self::REST_NAMESPACE, '/' . self::ROUTE_BASE . '/bulk-add', [
            'methods'             => 'POST',
            'callback'            => [$this, 'create_items'],
            'permission_callback' => [$this, 'check_permission'],
            'args'                => [
                'post_type' => [
                    'type'              => 'string',
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_key',
                ],
                'items'     => [
                    'type'     => 'array',
                    'required' => true,
                ],
            ],
        ]);
    }

    /**
     * Only a licensed install with an editor behind the keyboard.
     *
     * @since 1.10.0
     * @return true|WP_Error
     */
    public function check_permission() {
        if (!SaveAndGo::is_active()) {
            return new WP_Error(
                'extenderx_sag_disabled',
                __('Save & Go is switched off for this site.', 'extenderx'),
                ['status' => 403]
            );
        }

        if (!SaveAndGo::is_pro()) {
            return new WP_Error(
                'extenderx_sag_premium',
                __('Bulk Add needs an active ExtenderX Pro licence.', 'extenderx'),
                ['status' => 403]
            );
        }

        $config = SaveAndGo::config();

        if (empty($config['bulk_add']['enabled'])) {
            return new WP_Error(
                'extenderx_sag_unavailable',
                __('Bulk Add is switched off for this site.', 'extenderx'),
                ['status' => 403]
            );
        }

        if (!current_user_can('edit_posts')) {
            return new WP_Error(
                'extenderx_sag_forbidden',
                __('You are not allowed to create content.', 'extenderx'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Post types this user may bulk-create into, with the statuses they may
     * assign — so the panel never offers something the save would refuse.
     *
     * @since 1.10.0
     * @return WP_REST_Response
     */
    public function get_post_types(): WP_REST_Response {
        $types = [];

        foreach (Navigator::supported_post_types() as $slug) {
            $object = get_post_type_object($slug);

            if (!$object || empty($object->cap->create_posts) || !current_user_can($object->cap->create_posts)) {
                continue;
            }

            $types[] = [
                'slug'         => $slug,
                'label'        => $object->labels->name,
                'singular'     => $object->labels->singular_name,
                'hierarchical' => (bool) $object->hierarchical,
                'statuses'     => $this->allowed_statuses($slug),
            ];
        }

        return rest_ensure_response(['post_types' => $types]);
    }

    /**
     * Create the batch.
     *
     * Rows arrive in display order with a `depth`; a row's parent is the most
     * recent row one level shallower, which is how the panel's drag-to-nest
     * list is expressed without the client having to invent IDs. A row can
     * also name an existing `parent_id` instead.
     *
     * @since 1.10.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function create_items(WP_REST_Request $request) {
        $post_type = (string) $request->get_param('post_type');
        $items     = $request->get_param('items');

        if (!is_array($items) || empty($items)) {
            return new WP_Error(
                'extenderx_sag_no_items',
                __('Add at least one title.', 'extenderx'),
                ['status' => 400]
            );
        }

        if (count($items) > self::MAX_ITEMS) {
            return new WP_Error(
                'extenderx_sag_too_many',
                sprintf(
                    /* translators: %d: maximum number of posts per batch. */
                    __('Bulk Add creates up to %d posts at a time.', 'extenderx'),
                    self::MAX_ITEMS
                ),
                ['status' => 400]
            );
        }

        $object = get_post_type_object($post_type);

        if (
            !$object
            || !Navigator::is_supported_post_type($post_type)
            || empty($object->cap->create_posts)
            || !current_user_can($object->cap->create_posts)
        ) {
            return new WP_Error(
                'extenderx_sag_cannot_create',
                __('You are not allowed to create posts of this type.', 'extenderx'),
                ['status' => 403]
            );
        }

        $allowed_statuses = $this->allowed_statuses($post_type);
        $hierarchical     = (bool) $object->hierarchical;

        $created = [];
        $failed  = [];
        // Most recent created post ID at each depth, so a child can find its
        // parent without the client inventing identifiers.
        $parents = [];

        foreach (array_values($items) as $index => $raw) {
            $item = $this->sanitize_item($raw, $allowed_statuses, $hierarchical);

            if (is_wp_error($item)) {
                $failed[] = [
                    'index'   => $index,
                    'message' => $item->get_error_message(),
                ];
                continue;
            }

            $parent = 0;

            if ($hierarchical) {
                if ($item['parent_id'] > 0) {
                    $parent = $this->validate_parent($item['parent_id'], $post_type);

                    if (is_wp_error($parent)) {
                        $failed[] = ['index' => $index, 'message' => $parent->get_error_message()];
                        continue;
                    }
                } elseif ($item['depth'] > 0) {
                    $parent = (int) ($parents[$item['depth'] - 1] ?? 0);
                }
            }

            $postarr = [
                'post_type'   => $post_type,
                'post_title'  => $item['title'],
                'post_status' => $item['status'],
                'post_parent' => $parent,
                'post_author' => get_current_user_id(),
            ];

            if ('' !== $item['slug']) {
                $postarr['post_name'] = $item['slug'];
            }

            $new_id = wp_insert_post($postarr, true);

            if (is_wp_error($new_id)) {
                $failed[] = ['index' => $index, 'message' => $new_id->get_error_message()];
                continue;
            }

            $new_id = (int) $new_id;

            if ($hierarchical) {
                $parents[$item['depth']] = $new_id;
                // A new branch invalidates anything deeper that came before.
                foreach (array_keys($parents) as $depth) {
                    if ($depth > $item['depth']) {
                        unset($parents[$depth]);
                    }
                }
            }

            $created[] = [
                'id'      => $new_id,
                'title'   => get_the_title($new_id),
                'status'  => get_post_status($new_id),
                'editUrl' => get_edit_post_link($new_id, 'raw'),
            ];
        }

        return rest_ensure_response([
            'created' => $created,
            'failed'  => $failed,
            'listUrl' => Navigator::list_url($post_type),
        ]);
    }

    /**
     * Validate and normalise one row.
     *
     * @since 1.10.0
     * @param mixed    $raw              Raw row.
     * @param string[] $allowed_statuses Statuses this user may assign.
     * @param bool     $hierarchical     Whether the post type nests.
     * @return array|WP_Error
     */
    private function sanitize_item($raw, array $allowed_statuses, bool $hierarchical) {
        if (!is_array($raw)) {
            return new WP_Error('extenderx_sag_bad_item', __('That row could not be read.', 'extenderx'));
        }

        $title = isset($raw['title']) ? sanitize_text_field((string) $raw['title']) : '';

        if ('' === trim($title)) {
            return new WP_Error('extenderx_sag_no_title', __('A title is required.', 'extenderx'));
        }

        $status = isset($raw['status']) ? sanitize_key((string) $raw['status']) : 'draft';

        if (!in_array($status, $allowed_statuses, true)) {
            return new WP_Error(
                'extenderx_sag_bad_status',
                __('You are not allowed to use that status.', 'extenderx')
            );
        }

        $slug = isset($raw['slug']) ? sanitize_title((string) $raw['slug']) : '';

        $depth = 0;
        if ($hierarchical && isset($raw['depth'])) {
            $depth = max(0, min(self::MAX_DEPTH, (int) $raw['depth']));
        }

        $parent_id = 0;
        if ($hierarchical && isset($raw['parent_id'])) {
            $parent_id = absint($raw['parent_id']);
        }

        return [
            'title'     => $title,
            'status'    => $status,
            'slug'      => $slug,
            'depth'     => $depth,
            'parent_id' => $parent_id,
        ];
    }

    /**
     * Check an explicitly named parent: it has to exist, be the same post
     * type, and be something this user may edit.
     *
     * @since 1.10.0
     * @param int    $parent_id Parent post ID.
     * @param string $post_type Post type slug.
     * @return int|WP_Error
     */
    private function validate_parent(int $parent_id, string $post_type) {
        $parent = get_post($parent_id);

        if (!$parent || $parent->post_type !== $post_type) {
            return new WP_Error('extenderx_sag_bad_parent', __('That parent does not exist.', 'extenderx'));
        }

        if (!current_user_can('edit_post', $parent->ID)) {
            return new WP_Error('extenderx_sag_bad_parent', __('You are not allowed to use that parent.', 'extenderx'));
        }

        return (int) $parent->ID;
    }

    /**
     * Statuses this user may assign on a post type.
     *
     * @since 1.10.0
     * @param string $post_type Post type slug.
     * @return string[]
     */
    private function allowed_statuses(string $post_type): array {
        $object = get_post_type_object($post_type);
        $out    = [];

        foreach (SaveAndGoCatalog::bulk_statuses() as $status) {
            if (in_array($status, ['publish', 'private'], true)) {
                if (!$object || empty($object->cap->publish_posts) || !current_user_can($object->cap->publish_posts)) {
                    continue;
                }
            }

            $out[] = $status;
        }

        return $out;
    }
}
