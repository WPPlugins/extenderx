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
 * Save & Go REST Controller
 *
 * The block editor never decides where it is going: it asks here, after the
 * save has actually succeeded, and this controller resolves a destination from
 * the post, the settings and the current user's capabilities. That is what
 * keeps the premium actions premium (a client that fakes its Pro flag still
 * gets a 403 from here) and what keeps the redirect targets safe (no URL from
 * the browser is ever echoed back as a destination).
 *
 * Registered only once both ModuleBase gates have passed — a disabled module
 * has no routes at all.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\SaveAndGo
 * @since 1.10.0
 */
class RestController {
    use Singleton;

    /** REST namespace shared with the rest of the plugin. */
    private const REST_NAMESPACE = 'wpxero/extenderx/v1';

    /** Route prefix. */
    private const ROUTE_BASE = 'save-and-go';

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
        register_rest_route(self::REST_NAMESPACE, '/' . self::ROUTE_BASE . '/destination', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_destination'],
            'permission_callback' => [$this, 'edit_post_permission'],
            'args'                => self::shared_args(),
        ]);

        register_rest_route(self::REST_NAMESPACE, '/' . self::ROUTE_BASE . '/duplicate', [
            'methods'             => 'POST',
            'callback'            => [$this, 'create_duplicate'],
            'permission_callback' => [$this, 'edit_post_permission'],
            'args'                => [
                'post_id' => self::shared_args()['post_id'],
            ],
        ]);
    }

    /**
     * Argument schema shared by the routes.
     *
     * @since 1.10.0
     * @return array
     */
    private static function shared_args(): array {
        return [
            'post_id' => [
                'type'              => 'integer',
                'required'          => true,
                'sanitize_callback' => 'absint',
            ],
            'action'   => [
                'type'              => 'string',
                'required'          => true,
                'sanitize_callback' => 'sanitize_key',
            ],
            'referrer' => [
                'type'              => 'string',
                'required'          => false,
                'default'           => '',
                // Kept raw-ish here; Navigator::validate_admin_url is what
                // decides whether it may be used at all.
                'sanitize_callback' => 'esc_url_raw',
            ],
        ];
    }

    /**
     * Baseline permission: the user must be able to edit the post in question.
     *
     * Per-action entitlement is checked in the callbacks, so an unavailable
     * action can return a message the editor can show rather than a bare 403.
     *
     * @since 1.10.0
     * @param WP_REST_Request $request Request object.
     * @return true|WP_Error
     */
    public function edit_post_permission(WP_REST_Request $request) {
        $post_id = (int) $request->get_param('post_id');

        if ($post_id <= 0 || !current_user_can('edit_post', $post_id)) {
            return new WP_Error(
                'extenderx_sag_forbidden',
                __('You are not allowed to edit this post.', 'extenderx'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Resolve where an action should send the user.
     *
     * @since 1.10.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_destination(WP_REST_Request $request) {
        $action = (string) $request->get_param('action');
        $guard  = $this->guard_action($action);

        if (is_wp_error($guard)) {
            return $guard;
        }

        $destination = Navigator::resolve(
            $action,
            (int) $request->get_param('post_id'),
            ['referrer' => (string) $request->get_param('referrer')]
        );

        if (is_wp_error($destination)) {
            return $destination;
        }

        return rest_ensure_response([
            'url'    => $destination['url'],
            'target' => $destination['target'],
        ]);
    }

    /**
     * Duplicate the post and hand back the editor URL of the copy.
     *
     * @since 1.10.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function create_duplicate(WP_REST_Request $request) {
        $guard = $this->guard_action('duplicate');

        if (is_wp_error($guard)) {
            return $guard;
        }

        $new_id = Duplicator::duplicate((int) $request->get_param('post_id'));

        if (is_wp_error($new_id)) {
            return $new_id;
        }

        $url = get_edit_post_link($new_id, 'raw');

        if (!$url) {
            return new WP_Error(
                'extenderx_sag_no_edit_link',
                __('The copy was created but could not be opened.', 'extenderx'),
                ['status' => 500]
            );
        }

        return rest_ensure_response([
            'post_id' => (int) $new_id,
            'url'     => $url,
            'target'  => 'self',
        ]);
    }

    /**
     * The server-side half of the free/pro boundary.
     *
     * Every entry point runs this before doing any work: the module has to be
     * on, the action has to be switched on, and — for a premium action — the
     * licence has to cover it. A free install that asks for `duplicate`
     * (however it asks) gets a 403 here, which is why the editor's own
     * lock icons are presentation rather than protection.
     *
     * @since 1.10.0
     * @param string $action Action id.
     * @return true|WP_Error
     */
    private function guard_action(string $action) {
        if (!SaveAndGo::is_active()) {
            return new WP_Error(
                'extenderx_sag_disabled',
                __('Save & Go is switched off for this site.', 'extenderx'),
                ['status' => 403]
            );
        }

        if (SaveAndGoCatalog::is_pro_action($action) && !SaveAndGo::can_use_action($action)) {
            return new WP_Error(
                'extenderx_sag_premium',
                __('That Save & Go action needs an active ExtenderX Pro licence.', 'extenderx'),
                ['status' => 403]
            );
        }

        if (!SaveAndGo::is_action_available($action)) {
            return new WP_Error(
                'extenderx_sag_unavailable',
                __('That Save & Go action is not available.', 'extenderx'),
                ['status' => 400]
            );
        }

        return true;
    }
}
