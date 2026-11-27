<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Animations;

use WPXERO\ExtenderX\Traits\Singleton;

defined('ABSPATH') || exit;

/**
 * Per-page animation control.
 *
 * Registers the `_extenderx_disable_animations` post meta used by the
 * page-level "disable animations on this page" toggle. The editor UI
 * (a PluginDocumentSettingPanel) is added in Phase 5; the meta is exposed to
 * REST here so the panel and the server-side AssetResolver share one source.
 *
 * Registered independently of the feature toggles so the meta (and its REST
 * exposure) exists whenever the plugin is active.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\Animations
 * @since 1.9.0
 */
class PageControl {
    use Singleton;

    /**
     * Post meta key for the per-page disable flag.
     *
     * @var string
     */
    public const META_KEY = '_extenderx_disable_animations';

    /**
     * Constructor — register the meta. Animations boots on `init`, so this runs
     * at the correct time; guard with a did_action check for any other caller.
     */
    public function __construct() {
        if (did_action('init')) {
            $this->register_meta();
        } else {
            add_action('init', [$this, 'register_meta']);
        }
    }

    /**
     * Register the per-page disable post meta for all public post types.
     *
     * @return void
     */
    public function register_meta() {
        register_post_meta('', self::META_KEY, [
            'type'              => 'boolean',
            'single'            => true,
            'default'           => false,
            'show_in_rest'      => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
            'auth_callback'     => static function () {
                return current_user_can('edit_posts');
            },
        ]);
    }
}
