<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo;

use WP_Error;
use WP_Post;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Save & Go Duplicator
 *
 * Copies a post into a fresh draft for the premium "Save & Duplicate" action.
 * Everything a user would expect to carry over is copied — title, content,
 * excerpt, parent, menu order, taxonomy terms, featured image and the post's
 * own custom fields — while WordPress's internal bookkeeping (edit locks,
 * revision pointers, Freemius/ACF-style system rows) is deliberately left
 * behind so the copy starts clean.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo
 * @since 1.10.0
 */
class Duplicator {

    /**
     * Protected meta keys that are always safe to copy because WordPress
     * itself treats them as post content rather than internal state.
     *
     * @var string[]
     */
    private const ALLOWED_PROTECTED_META = [
        '_thumbnail_id',
        '_wp_page_template',
    ];

    /**
     * Internal meta keys that must never be copied even if they look public.
     *
     * @var string[]
     */
    private const BLOCKED_META = [
        '_edit_lock',
        '_edit_last',
        '_wp_old_slug',
        '_wp_old_date',
        '_wp_trash_meta_status',
        '_wp_trash_meta_time',
        '_wp_desired_post_slug',
    ];

    /**
     * Duplicate a post as a draft.
     *
     * @since 1.10.0
     * @param int $post_id Source post ID.
     * @return int|WP_Error New post ID, or an error.
     */
    public static function duplicate(int $post_id) {
        $source = get_post($post_id);

        if (!$source instanceof WP_Post) {
            return new WP_Error(
                'extenderx_sag_no_post',
                __('That post could not be found.', 'extenderx'),
                ['status' => 404]
            );
        }

        if (!current_user_can('edit_post', $source->ID)) {
            return new WP_Error(
                'extenderx_sag_forbidden',
                __('You are not allowed to edit this post.', 'extenderx'),
                ['status' => 403]
            );
        }

        $object = get_post_type_object($source->post_type);

        if (!$object || empty($object->cap->create_posts) || !current_user_can($object->cap->create_posts)) {
            return new WP_Error(
                'extenderx_sag_cannot_create',
                __('You are not allowed to create posts of this type.', 'extenderx'),
                ['status' => 403]
            );
        }

        if (!Navigator::is_supported_post_type($source->post_type)) {
            return new WP_Error(
                'extenderx_sag_post_type',
                __('Save & Go is not available for this post type.', 'extenderx'),
                ['status' => 400]
            );
        }

        // The copy belongs to whoever made it unless they are allowed to
        // assign authorship, in which case the original author is kept.
        $author = get_current_user_id();
        if (!empty($object->cap->edit_others_posts) && current_user_can($object->cap->edit_others_posts)) {
            $author = (int) $source->post_author;
        }

        $new_id = wp_insert_post(
            [
                'post_type'      => $source->post_type,
                'post_status'    => 'draft',
                'post_title'     => self::copy_title($source),
                'post_content'   => $source->post_content,
                'post_excerpt'   => $source->post_excerpt,
                'post_parent'    => $source->post_parent,
                'menu_order'     => $source->menu_order,
                'comment_status' => $source->comment_status,
                'ping_status'    => $source->ping_status,
                'post_author'    => $author,
                'post_password'  => '',
            ],
            true
        );

        if (is_wp_error($new_id)) {
            return $new_id;
        }

        self::copy_taxonomies($source, (int) $new_id);
        self::copy_meta($source, (int) $new_id);

        /**
         * Fires after Save & Duplicate has created a copy.
         *
         * @since 1.10.0
         * @param int     $new_id New draft ID.
         * @param WP_Post $source Source post.
         */
        do_action('extenderx_save_and_go_duplicated', (int) $new_id, $source);

        return (int) $new_id;
    }

    /**
     * Title for the copy.
     *
     * @since 1.10.0
     * @param WP_Post $source Source post.
     * @return string
     */
    private static function copy_title(WP_Post $source): string {
        $title = '' !== trim($source->post_title)
            ? $source->post_title
            : __('(no title)', 'extenderx');

        return sprintf(
            /* translators: %s: original post title. */
            __('%s (copy)', 'extenderx'),
            $title
        );
    }

    /**
     * Copy taxonomy terms the current user is allowed to assign.
     *
     * @since 1.10.0
     * @param WP_Post $source Source post.
     * @param int     $new_id New post ID.
     * @return void
     */
    private static function copy_taxonomies(WP_Post $source, int $new_id): void {
        $taxonomies = get_object_taxonomies($source->post_type, 'objects');

        foreach ($taxonomies as $taxonomy) {
            if (!empty($taxonomy->cap->assign_terms) && !current_user_can($taxonomy->cap->assign_terms)) {
                continue;
            }

            $terms = wp_get_object_terms($source->ID, $taxonomy->name, ['fields' => 'ids']);

            if (is_wp_error($terms) || empty($terms)) {
                continue;
            }

            wp_set_object_terms($new_id, array_map('intval', $terms), $taxonomy->name, false);
        }
    }

    /**
     * Copy custom fields, skipping WordPress's internal bookkeeping.
     *
     * @since 1.10.0
     * @param WP_Post $source Source post.
     * @param int     $new_id New post ID.
     * @return void
     */
    private static function copy_meta(WP_Post $source, int $new_id): void {
        $meta = get_post_meta($source->ID);

        if (!is_array($meta)) {
            return;
        }

        /**
         * Filter which meta keys Save & Duplicate copies.
         *
         * @since 1.10.0
         * @param string[] $blocked Keys never copied.
         * @param WP_Post  $source  Source post.
         */
        $blocked = (array) apply_filters('extenderx_save_and_go_blocked_meta', self::BLOCKED_META, $source);

        foreach ($meta as $key => $values) {
            if (in_array($key, $blocked, true)) {
                continue;
            }

            // Protected keys are WordPress internals by convention; only the
            // handful that genuinely describe the post's content come along.
            if (is_protected_meta($key, 'post') && !in_array($key, self::ALLOWED_PROTECTED_META, true)) {
                continue;
            }

            foreach ((array) $values as $value) {
                add_post_meta($new_id, $key, maybe_unserialize($value));
            }
        }
    }
}
