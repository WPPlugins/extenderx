<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo;

use WP_Error;
use WP_Post;
use WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Save & Go Navigator
 *
 * Resolves where a Save & Go action should send the user. Every destination is
 * built here, on the server, from WordPress APIs and the current user's
 * capabilities — no URL ever comes in from the browser, which is what keeps
 * the feature free of open redirects.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo
 * @since 1.10.0
 */
class Navigator {

    /**
     * How many candidate rows an adjacent-post lookup inspects.
     *
     * The query already returns strictly the neighbouring side of the current
     * post, newest candidate first, so the first row is normally the answer.
     * The rest of the window only exists so rows this particular user may not
     * edit can be skipped without a second round trip.
     */
    private const ADJACENT_SCAN = 10;

    /**
     * Post types that have an admin UI but are not content anyone edits on
     * post.php — templates, patterns, navigation menus and the font/style
     * records the site editor keeps. Save & Go's destinations (a post list, a
     * new-post screen, a permalink) mean nothing for these.
     *
     * @var string[]
     */
    private const EXCLUDED_POST_TYPES = [
        'attachment',
        'wp_block',
        'wp_navigation',
        'wp_template',
        'wp_template_part',
        'wp_global_styles',
        'wp_font_family',
        'wp_font_face',
    ];

    /**
     * Per-request memo of {@see supported_post_types()}, keyed by user id.
     *
     * The classic Publish box asks whether the post type is supported once per
     * control and again for the body class, and every answer walks the whole
     * post type registry running capability checks. Nothing it reads can change
     * inside one request.
     *
     * @var array<int, string[]>
     */
    private static $post_type_cache = [];

    /**
     * Per-request memo of {@see find_adjacent()}, keyed by "postID:direction".
     *
     * Both editors ask twice — once to grey the menu entry out, once to resolve
     * the destination — and each answer is a WP_Query.
     *
     * @var array<string, WP_Post|null>
     */
    private static $adjacent_cache = [];

    /**
     * Post types Save & Go supports: anything with an admin UI and the normal
     * editor, which the current user may edit.
     *
     * @since 1.10.0
     * @return string[]
     */
    public static function supported_post_types(): array {
        $user = get_current_user_id();

        if (isset(self::$post_type_cache[$user])) {
            return self::$post_type_cache[$user];
        }

        $types = get_post_types(['show_ui' => true], 'objects');
        $out   = [];

        foreach ($types as $slug => $object) {
            if (in_array($slug, self::EXCLUDED_POST_TYPES, true)) {
                continue;
            }

            if (!post_type_supports($slug, 'editor') && !post_type_supports($slug, 'title')) {
                continue;
            }

            if (empty($object->cap->edit_posts) || !current_user_can($object->cap->edit_posts)) {
                continue;
            }

            $out[] = $slug;
        }

        /**
         * Filter the post types Save & Go is offered on.
         *
         * @since 1.10.0
         * @param string[] $out Post type slugs.
         */
        self::$post_type_cache[$user] = (array) apply_filters('extenderx_save_and_go_post_types', $out);

        return self::$post_type_cache[$user];
    }

    /**
     * Whether Save & Go runs on a post type.
     *
     * @since 1.10.0
     * @param string $post_type Post type slug.
     * @return bool
     */
    public static function is_supported_post_type(string $post_type): bool {
        return in_array($post_type, self::supported_post_types(), true);
    }

    /**
     * Why an action cannot run on this post right now, if it cannot.
     *
     * Availability, unlike the licence check, depends on the post in front of
     * the user: "next" has nowhere to go from the last row of the list, and
     * nothing can be viewed on the front end of a post type that has none.
     * The editors use this to grey the entry out with the reason attached,
     * rather than letting the click through to an error.
     *
     * @since 1.10.0
     * @param string  $action Action id.
     * @param WP_Post $post   Post being edited.
     * @return string Reason, or an empty string when the action is fine.
     */
    public static function unavailable_reason(string $action, WP_Post $post): string {
        switch ($action) {
            case 'next':
            case 'previous':
                $older = 'next' === $action;

                if (self::find_adjacent($post, $older)) {
                    return '';
                }

                return $older
                    ? __('You are at the last post.', 'extenderx')
                    : __('You are at the first post.', 'extenderx');

            case 'view':
            case 'view_new_window':
                if (is_post_type_viewable(get_post_type_object($post->post_type))) {
                    return '';
                }

                return __('This post type has no front end to open.', 'extenderx');
        }

        return '';
    }

    /**
     * Annotate a list of actions with their availability for one post.
     *
     * @since 1.10.0
     * @param array   $actions Action definitions.
     * @param WP_Post $post    Post being edited.
     * @return array Same list, each entry carrying an `unavailable` reason.
     */
    public static function annotate_availability(array $actions, WP_Post $post): array {
        return array_map(
            static function (array $action) use ($post): array {
                $action['unavailable'] = empty($action['locked'])
                    ? self::unavailable_reason($action['id'], $post)
                    : '';

                return $action;
            },
            $actions
        );
    }

    /**
     * Resolve the destination for an action.
     *
     * @since 1.10.0
     * @param string $action  Action id.
     * @param int    $post_id Post being edited.
     * @param array  $args    Optional. `referrer` — the admin screen the user
     *                        came from, used by Save & Return and to preserve
     *                        list filters. Validated, never trusted.
     * @return array|WP_Error `['url' => string, 'target' => 'self'|'blank']`.
     */
    public static function resolve(string $action, int $post_id, array $args = []) {
        $post = get_post($post_id);

        if (!$post instanceof WP_Post) {
            return new WP_Error(
                'extenderx_sag_no_post',
                __('That post could not be found.', 'extenderx'),
                ['status' => 404]
            );
        }

        if (!current_user_can('edit_post', $post->ID)) {
            return new WP_Error(
                'extenderx_sag_forbidden',
                __('You are not allowed to edit this post.', 'extenderx'),
                ['status' => 403]
            );
        }

        if (!self::is_supported_post_type($post->post_type)) {
            return new WP_Error(
                'extenderx_sag_post_type',
                __('Save & Go is not available for this post type.', 'extenderx'),
                ['status' => 400]
            );
        }

        $referrer = isset($args['referrer']) ? (string) $args['referrer'] : '';

        switch ($action) {
            case 'new':
                return self::resolve_new($post);

            case 'list':
                return self::resolve_list($post, $referrer);

            case 'next':
                return self::resolve_adjacent($post, true);

            case 'previous':
                return self::resolve_adjacent($post, false);

            case 'view':
                return self::resolve_view($post, 'self');

            case 'view_new_window':
                return self::resolve_view($post, 'blank');

            case 'return':
                return self::resolve_return($post, $referrer);
        }

        /**
         * Resolve the destination of an action contributed by another feature.
         *
         * Return `['url' => …, 'target' => 'self'|'blank']` or a WP_Error.
         * Anything else is treated as "no destination".
         *
         * @since 1.10.0
         * @param null    $destination Destination, or null when unhandled.
         * @param string  $action      Action id.
         * @param WP_Post $post        Post being edited.
         * @param array   $args        Resolver arguments.
         */
        $custom = apply_filters('extenderx_save_and_go_destination', null, $action, $post, $args);

        if (is_wp_error($custom)) {
            return $custom;
        }

        if (is_array($custom) && !empty($custom['url'])) {
            return [
                'url'    => esc_url_raw((string) $custom['url']),
                'target' => isset($custom['target']) && 'blank' === $custom['target'] ? 'blank' : 'self',
            ];
        }

        return new WP_Error(
            'extenderx_sag_unknown_action',
            __('That Save & Go action is not available.', 'extenderx'),
            ['status' => 400]
        );
    }

    /**
     * Save & New — the new-post screen for the same post type.
     *
     * @since 1.10.0
     * @param WP_Post $post Current post.
     * @return array|WP_Error
     */
    private static function resolve_new(WP_Post $post) {
        $object = get_post_type_object($post->post_type);

        if (!$object || empty($object->cap->create_posts) || !current_user_can($object->cap->create_posts)) {
            return new WP_Error(
                'extenderx_sag_cannot_create',
                __('You are not allowed to create posts of this type.', 'extenderx'),
                ['status' => 403]
            );
        }

        $url = admin_url('post-new.php');

        if ('post' !== $post->post_type) {
            $url = add_query_arg('post_type', $post->post_type, $url);
        }

        return ['url' => $url, 'target' => 'self'];
    }

    /**
     * Save & List — the post list for this post type.
     *
     * When the user arrived from that very list, its own URL is reused so the
     * filters, search and page number they had are still there when they get
     * back. Anything else falls back to the plain list screen.
     *
     * @since 1.10.0
     * @param WP_Post $post     Current post.
     * @param string  $referrer Unvalidated referrer.
     * @return array|WP_Error
     */
    private static function resolve_list(WP_Post $post, string $referrer) {
        $object = get_post_type_object($post->post_type);

        if (!$object || empty($object->cap->edit_posts) || !current_user_can($object->cap->edit_posts)) {
            return new WP_Error(
                'extenderx_sag_cannot_list',
                __('You are not allowed to browse posts of this type.', 'extenderx'),
                ['status' => 403]
            );
        }

        $list = self::list_url($post->post_type);
        $safe = self::validate_admin_url($referrer);

        if ('' !== $safe && self::is_list_url_for($safe, $post->post_type)) {
            $list = $safe;
        }

        return ['url' => $list, 'target' => 'self'];
    }

    /**
     * Save & Next / Save & Previous.
     *
     * "Next" means the next row in the post list as WordPress orders it
     * (newest first), so it walks towards older posts; "previous" walks back
     * towards newer ones.
     *
     * @since 1.10.0
     * @param WP_Post $post  Current post.
     * @param bool    $older Whether to move towards older posts.
     * @return array|WP_Error
     */
    private static function resolve_adjacent(WP_Post $post, bool $older) {
        $neighbour = self::find_adjacent($post, $older);

        if (!$neighbour) {
            return new WP_Error(
                $older ? 'extenderx_sag_no_next' : 'extenderx_sag_no_previous',
                $older
                    ? __('There is no next post to edit.', 'extenderx')
                    : __('There is no previous post to edit.', 'extenderx'),
                ['status' => 404]
            );
        }

        $url = get_edit_post_link($neighbour->ID, 'raw');

        if (!$url) {
            return new WP_Error(
                'extenderx_sag_no_edit_link',
                __('That post cannot be opened in the editor.', 'extenderx'),
                ['status' => 403]
            );
        }

        return ['url' => $url, 'target' => 'self'];
    }

    /**
     * Save & View — the post on the front end.
     *
     * Published (and privately published) posts resolve to their permalink;
     * anything still unpublished resolves to WordPress's own nonce-protected
     * preview link, which is the only front-end URL that actually works for a
     * draft. A post type with no front end gets a clear message instead of a
     * broken URL.
     *
     * @since 1.10.0
     * @param WP_Post $post   Current post.
     * @param string  $target Window target.
     * @return array|WP_Error
     */
    private static function resolve_view(WP_Post $post, string $target) {
        if (!is_post_type_viewable(get_post_type_object($post->post_type))) {
            return new WP_Error(
                'extenderx_sag_not_viewable',
                __('This post type has no front-end page to open.', 'extenderx'),
                ['status' => 400]
            );
        }

        $public_statuses = ['publish', 'private'];
        $url             = in_array($post->post_status, $public_statuses, true)
            ? get_permalink($post)
            : get_preview_post_link($post);

        if (!$url) {
            return new WP_Error(
                'extenderx_sag_no_permalink',
                __('This post does not have a front-end address yet.', 'extenderx'),
                ['status' => 400]
            );
        }

        return ['url' => $url, 'target' => 'blank' === $target ? 'blank' : 'self'];
    }

    /**
     * Save & Return — back to wherever the user opened the editor from.
     *
     * The browser-supplied location is validated against this site's admin and
     * discarded if it is anything else, so the action can never be turned into
     * an open redirect. With nothing usable it falls back to the post list.
     *
     * @since 1.10.0
     * @param WP_Post $post     Current post.
     * @param string  $referrer Unvalidated referrer.
     * @return array|WP_Error
     */
    private static function resolve_return(WP_Post $post, string $referrer) {
        $safe = self::validate_admin_url($referrer);

        if ('' === $safe) {
            return self::resolve_list($post, '');
        }

        return ['url' => $safe, 'target' => 'self'];
    }

    /**
     * The plain list URL for a post type.
     *
     * @since 1.10.0
     * @param string $post_type Post type slug.
     * @return string
     */
    public static function list_url(string $post_type): string {
        $url = admin_url('edit.php');

        if ('post' !== $post_type) {
            $url = add_query_arg('post_type', $post_type, $url);
        }

        return $url;
    }

    /**
     * Whether a validated admin URL is the list screen for a post type.
     *
     * @since 1.10.0
     * @param string $url       Validated admin URL.
     * @param string $post_type Post type slug.
     * @return bool
     */
    private static function is_list_url_for(string $url, string $post_type): bool {
        $path = wp_parse_url($url, PHP_URL_PATH);

        if (!$path || 'edit.php' !== basename($path)) {
            return false;
        }

        $query = (string) wp_parse_url($url, PHP_URL_QUERY);
        $args  = [];
        wp_parse_str($query, $args);

        $requested = isset($args['post_type']) ? sanitize_key((string) $args['post_type']) : 'post';

        return $requested === $post_type;
    }

    /**
     * Validate a browser-supplied location as an admin URL on this site.
     *
     * Returns an empty string for anything off-site, outside wp-admin, or
     * pointing back at an editor screen (returning to the screen you just left
     * is never what "return" means).
     *
     * @since 1.10.0
     * @param string $raw Raw URL.
     * @return string Validated URL, or an empty string.
     */
    public static function validate_admin_url(string $raw): string {
        $raw = trim($raw);

        if ('' === $raw) {
            return '';
        }

        // A path-only referrer is resolved against this site before validation
        // so the host check below has something to check.
        if ('/' === $raw[0] && (!isset($raw[1]) || '/' !== $raw[1])) {
            $raw = home_url($raw);
        }

        $url = wp_validate_redirect(esc_url_raw($raw), '');

        if ('' === $url) {
            return '';
        }

        $admin = admin_url();

        if (0 !== strpos($url, $admin)) {
            return '';
        }

        $file = basename((string) wp_parse_url($url, PHP_URL_PATH));

        if (in_array($file, ['post.php', 'post-new.php'], true)) {
            return '';
        }

        return $url;
    }

    /**
     * Find the neighbouring post of the same type.
     *
     * Deliberately a query, not arithmetic on the post ID: IDs are shared by
     * every post type and have gaps, so `ID ± 1` lands on unrelated content or
     * nothing at all. The window is bounded and ordering is resolved in PHP
     * only for exact-timestamp ties.
     *
     * @since 1.10.0
     * @param WP_Post $post  Current post.
     * @param bool    $older Whether to look towards older posts.
     * @return WP_Post|null
     */
    private static function find_adjacent(WP_Post $post, bool $older) {
        $memo = $post->ID . ':' . ($older ? 'older' : 'newer');

        if (array_key_exists($memo, self::$adjacent_cache)) {
            return self::$adjacent_cache[$memo];
        }

        self::$adjacent_cache[$memo] = self::query_adjacent($post, $older);

        return self::$adjacent_cache[$memo];
    }

    /**
     * The uncached half of {@see find_adjacent()}.
     *
     * @since 1.10.0
     * @param WP_Post $post  Current post.
     * @param bool    $older Whether to look towards older posts.
     * @return WP_Post|null
     */
    private static function query_adjacent(WP_Post $post, bool $older) {
        global $wpdb;

        $direction = $older ? 'DESC' : 'ASC';
        // Fixed pair, never interpolated from input.
        $operator  = $older ? '<' : '>';

        // Ordering alone cannot answer "which post comes after this one" when
        // posts share an exact timestamp — a bulk import or generated content
        // gives every row the same `post_date`, and a window of rows ordered by
        // date then ID can be filled entirely by posts on the wrong side of the
        // current one. The comparison belongs in the query: a candidate is a
        // neighbour when its date is past the current one, or the dates match
        // and its ID is. That is the same rule core's own get_adjacent_post()
        // uses, applied to a post list's ordering rather than a permalink's.
        $where = static function ($sql) use ($wpdb, $post, $operator) {
            return $sql . $wpdb->prepare(
                " AND ( {$wpdb->posts}.post_date {$operator} %s"
                . " OR ( {$wpdb->posts}.post_date = %s AND {$wpdb->posts}.ID {$operator} %d ) )",
                $post->post_date,
                $post->post_date,
                $post->ID
            );
        };

        add_filter('posts_where', $where);

        $query = new WP_Query([
            'post_type'           => $post->post_type,
            'post_status'         => self::editable_statuses(),
            // More than one row only so posts this user may not edit can be
            // skipped; the first row is the neighbour in the normal case.
            'posts_per_page'      => self::ADJACENT_SCAN,
            'orderby'             => ['date' => $direction, 'ID' => $direction],
            'perm'                => 'editable',
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        remove_filter('posts_where', $where);

        foreach ($query->posts as $candidate) {
            if (!current_user_can('edit_post', $candidate->ID)) {
                continue;
            }

            return $candidate;
        }

        return null;
    }

    /**
     * Statuses that appear in the post list's "All" view — everything an
     * author can actually open in the editor, and nothing trashed, auto-draft
     * or attached.
     *
     * @since 1.10.0
     * @return string[]
     */
    private static function editable_statuses(): array {
        $statuses = array_keys(get_post_stati(['show_in_admin_all_list' => true]));

        if (empty($statuses)) {
            $statuses = ['publish', 'future', 'draft', 'pending', 'private'];
        }

        return array_values(array_diff($statuses, ['trash', 'auto-draft', 'inherit']));
    }
}
