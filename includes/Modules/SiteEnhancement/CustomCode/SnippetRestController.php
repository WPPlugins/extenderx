<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\ProFeatures;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Snippet REST Controller
 *
 * CRUD endpoints for managing Custom Code snippets, consumed by the React
 * admin. Lives under the shared `wpxero/extenderx/v1` namespace.
 *
 *   GET    /snippets                 List snippets + UI config.
 *   POST   /snippets                 Create a snippet.
 *   GET    /snippets/{id}            Read a snippet.
 *   POST   /snippets/{id}            Update a snippet.
 *   DELETE /snippets/{id}            Delete a snippet.
 *   POST   /snippets/{id}/toggle     Toggle active state.
 *   POST   /snippets/{id}/duplicate  Duplicate a snippet.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode
 * @since 1.7.0
 */
class SnippetRestController {
    use Singleton;

    /**
     * REST namespace.
     *
     * @var string
     */
    private const NAMESPACE = 'wpxero/extenderx/v1';

    /**
     * Maximum number of snippets a free (non-pro) user may create.
     *
     * @var int
     */
    private const FREE_SNIPPET_LIMIT = 2;

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register routes.
     *
     * @return void
     */
    public function register_routes(): void {
        register_rest_route(self::NAMESPACE, '/snippets', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'list_snippets'],
                'permission_callback' => [$this, 'read_permission'],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'create_snippet'],
                'permission_callback' => [$this, 'write_permission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/snippets/(?P<id>\d+)', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_snippet'],
                'permission_callback' => [$this, 'read_permission'],
                'args'                => $this->id_arg(),
            ],
            [
                'methods'             => 'POST, PUT, PATCH',
                'callback'            => [$this, 'update_snippet'],
                'permission_callback' => [$this, 'write_permission'],
                'args'                => $this->id_arg(),
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [$this, 'delete_snippet'],
                'permission_callback' => [$this, 'write_permission'],
                'args'                => $this->id_arg(),
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/snippets/(?P<id>\d+)/toggle', [
            'methods'             => 'POST',
            'callback'            => [$this, 'toggle_snippet'],
            'permission_callback' => [$this, 'write_permission'],
            'args'                => $this->id_arg(),
        ]);

        register_rest_route(self::NAMESPACE, '/snippets/(?P<id>\d+)/duplicate', [
            'methods'             => 'POST',
            'callback'            => [$this, 'duplicate_snippet'],
            'permission_callback' => [$this, 'write_permission'],
            'args'                => $this->id_arg(),
        ]);
    }

    // ------------------------------------------------------------------
    //  Permissions
    // ------------------------------------------------------------------

    /**
     * Read permission — any site administrator.
     *
     * @return bool|WP_Error
     */
    public function read_permission() {
        return current_user_can('manage_options')
            ? true
            : new WP_Error('extenderx_forbidden', __('You are not allowed to view snippets.', 'extenderx'), ['status' => 403]);
    }

    /**
     * Write permission — any site administrator.
     *
     * PHP-type payloads are additionally checked inside the handlers via
     * {@see self::can_manage_php()} because the type is request-dependent.
     *
     * @return bool|WP_Error
     */
    public function write_permission() {
        return current_user_can('manage_options')
            ? true
            : new WP_Error('extenderx_forbidden', __('You are not allowed to manage snippets.', 'extenderx'), ['status' => 403]);
    }

    /**
     * Whether the current user may create/edit PHP snippets.
     *
     * Mirrors WordPress' own protection for executable code: the user must be
     * able to edit plugin files and file editing must not be disabled.
     *
     * @return bool
     */
    private function can_manage_php(): bool {
        if (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT) {
            return false;
        }

        $allowed = current_user_can('manage_options') && current_user_can('edit_plugins');

        /**
         * Filter whether the current user may save PHP snippets.
         *
         * @param bool $allowed
         */
        return (bool) apply_filters('extenderx_user_can_manage_php_snippets', $allowed);
    }

    // ------------------------------------------------------------------
    //  Handlers
    // ------------------------------------------------------------------

    /**
     * List all snippets and UI configuration.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function list_snippets(WP_REST_Request $request): WP_REST_Response {
        return new WP_REST_Response([
            'success'  => true,
            'snippets' => SnippetPostType::query_all(),
            'config'   => $this->get_config(),
        ], 200);
    }

    /**
     * Get a single snippet.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function get_snippet(WP_REST_Request $request) {
        $snippet = SnippetPostType::normalize((int) $request['id']);

        if ($snippet === null) {
            return $this->not_found();
        }

        return new WP_REST_Response(['success' => true, 'snippet' => $snippet], 200);
    }

    /**
     * Create a snippet.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function create_snippet(WP_REST_Request $request) {
        if ($this->at_free_limit()) {
            return $this->limit_error();
        }

        $data = $this->sanitize_payload($request->get_json_params() ?: $request->get_params());

        if (is_wp_error($data)) {
            return $data;
        }

        $id = $this->insert($data);

        if ($id === 0) {
            return new WP_Error(
                'extenderx_snippet_create_failed',
                __('Could not create the snippet.', 'extenderx'),
                ['status' => 500]
            );
        }

        return new WP_REST_Response([
            'success' => true,
            'snippet' => SnippetPostType::find($id),
            'message' => __('Snippet created.', 'extenderx'),
        ], 201);
    }

    /**
     * Update a snippet.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function update_snippet(WP_REST_Request $request) {
        $id = (int) $request['id'];

        if (SnippetPostType::find($id) === null) {
            return $this->not_found();
        }

        $data = $this->sanitize_payload($request->get_json_params() ?: $request->get_params());

        if (is_wp_error($data)) {
            return $data;
        }

        $this->update($id, $data);

        return new WP_REST_Response([
            'success' => true,
            'snippet' => SnippetPostType::find($id),
            'message' => __('Snippet updated.', 'extenderx'),
        ], 200);
    }

    /**
     * Delete a snippet.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function delete_snippet(WP_REST_Request $request) {
        global $wpdb;

        $id = (int) $request['id'];

        if (SnippetPostType::find($id) === null) {
            return $this->not_found();
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
        $wpdb->delete(SnippetTable::table_name(), ['id' => $id], ['%d']);

        return new WP_REST_Response([
            'success' => true,
            'id'      => $id,
            'message' => __('Snippet deleted.', 'extenderx'),
        ], 200);
    }

    /**
     * Toggle a snippet's active state.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function toggle_snippet(WP_REST_Request $request) {
        global $wpdb;

        $id      = (int) $request['id'];
        $snippet = SnippetPostType::find($id);

        if ($snippet === null) {
            return $this->not_found();
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
        $wpdb->update(
            SnippetTable::table_name(),
            [
                'active'      => $snippet['active'] ? 0 : 1,
                'modified_at' => current_time('mysql'),
            ],
            ['id' => $id],
            ['%d', '%s'],
            ['%d']
        );

        return new WP_REST_Response([
            'success' => true,
            'snippet' => SnippetPostType::find($id),
        ], 200);
    }

    /**
     * Duplicate a snippet (created inactive to avoid accidental output).
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function duplicate_snippet(WP_REST_Request $request) {
        if ($this->at_free_limit()) {
            return $this->limit_error();
        }

        $id     = (int) $request['id'];
        $source = SnippetPostType::find($id);

        if ($source === null) {
            return $this->not_found();
        }

        // Duplicates are created inactive to avoid accidental output.
        /* translators: %s: original snippet title. */
        $source['title']  = sprintf(__('%s (copy)', 'extenderx'), $source['title']);
        $source['active'] = false;

        $new_id = $this->insert($source);

        if ($new_id === 0) {
            return new WP_Error(
                'extenderx_snippet_duplicate_failed',
                __('Could not duplicate the snippet.', 'extenderx'),
                ['status' => 500]
            );
        }

        return new WP_REST_Response([
            'success' => true,
            'snippet' => SnippetPostType::find($new_id),
            'message' => __('Snippet duplicated.', 'extenderx'),
        ], 201);
    }

    // ------------------------------------------------------------------
    //  Persistence helpers
    // ------------------------------------------------------------------

    /**
     * Insert a new snippet row.
     *
     * @param array $data Sanitized (or normalised) snippet payload.
     * @return int New snippet ID, or 0 on failure.
     */
    private function insert(array $data): int {
        global $wpdb;

        $now = current_time('mysql');

        $row                = $this->row_data($data);
        $row['created_at']  = $now;
        $row['modified_at'] = $now;

        $formats   = $this->row_formats();
        $formats[] = '%s'; // created_at
        $formats[] = '%s'; // modified_at

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
        $result = $wpdb->insert(SnippetTable::table_name(), $row, $formats);

        return $result ? (int) $wpdb->insert_id : 0;
    }

    /**
     * Update an existing snippet row.
     *
     * @param int   $id   Snippet ID.
     * @param array $data Sanitized snippet payload.
     * @return void
     */
    private function update(int $id, array $data): void {
        global $wpdb;

        $row                = $this->row_data($data);
        $row['modified_at'] = current_time('mysql');

        $formats   = $this->row_formats();
        $formats[] = '%s'; // modified_at

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
        $wpdb->update(SnippetTable::table_name(), $row, ['id' => $id], $formats, ['%d']);
    }

    /**
     * Map a sanitized/normalised payload to table columns.
     *
     * Order must match {@see self::row_formats()}.
     *
     * @param array $data Snippet payload.
     * @return array<string, mixed>
     */
    private function row_data(array $data): array {
        return [
            'title'      => $data['title'],
            'type'       => $data['type'],
            'location'   => $data['location'],
            'active'     => !empty($data['active']) ? 1 : 0,
            'priority'   => (int) $data['priority'],
            'code'       => $data['code'],
            'conditions' => wp_json_encode($data['conditions']),
            'schedule'   => wp_json_encode($data['schedule']),
        ];
    }

    /**
     * Column format specifiers matching {@see self::row_data()}.
     *
     * @return string[]
     */
    private function row_formats(): array {
        return ['%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s'];
    }

    // ------------------------------------------------------------------
    //  Sanitization
    // ------------------------------------------------------------------

    /**
     * Sanitize and validate an incoming snippet payload.
     *
     * @param array $raw Raw request data.
     * @return array|WP_Error Sanitized payload or error.
     */
    private function sanitize_payload($raw) {
        $raw      = is_array($raw) ? $raw : [];
        $defaults = SnippetPostType::defaults();

        $type = isset($raw['type']) ? sanitize_key($raw['type']) : $defaults['type'];
        if (!in_array($type, SnippetPostType::TYPES, true)) {
            return new WP_Error('extenderx_invalid_type', __('Invalid snippet type.', 'extenderx'), ['status' => 400]);
        }

        if ($type === 'php' && !$this->can_manage_php()) {
            return new WP_Error(
                'extenderx_php_forbidden',
                __('You do not have permission to save PHP snippets on this site.', 'extenderx'),
                ['status' => 403]
            );
        }

        $valid_locations = SnippetPostType::LOCATIONS[$type];
        $location = isset($raw['location']) ? sanitize_key($raw['location']) : '';
        if (!in_array($location, $valid_locations, true)) {
            $location = $valid_locations[0];
        }

        $title = isset($raw['title']) ? sanitize_text_field($raw['title']) : '';
        if ($title === '') {
            $title = __('Untitled snippet', 'extenderx');
        }

        // Code is stored verbatim — these are trusted, administrator-authored
        // snippets. Only unslash the transport escaping.
        $code = isset($raw['code']) ? (string) wp_unslash($raw['code']) : '';

        // A PHP snippet with a syntax error never runs (SnippetExecutor traps
        // the ParseError), yet it used to save and show as active. Refuse it
        // instead and say where the error is. token_get_all() only parses —
        // nothing is executed.
        if ($type === 'php') {
            $syntax_error = self::php_syntax_error($code);
            if ($syntax_error !== null) {
                return new WP_Error(
                    'extenderx_php_syntax',
                    sprintf(
                        /* translators: 1: line number, 2: PHP parser message */
                        __('PHP syntax error on line %1$d: %2$s. The snippet was not saved.', 'extenderx'),
                        $syntax_error['line'],
                        $syntax_error['message']
                    ),
                    ['status' => 400]
                );
            }
        }

        $priority = isset($raw['priority']) ? (int) $raw['priority'] : $defaults['priority'];
        $priority = max(-99999, min(99999, $priority));

        return [
            'title'      => $title,
            'type'       => $type,
            'location'   => $location,
            'code'       => $code,
            'active'     => !empty($raw['active']),
            'priority'   => $priority,
            'conditions' => $this->sanitize_conditions($raw['conditions'] ?? []),
            'schedule'   => $this->sanitize_schedule($raw['schedule'] ?? []),
        ];
    }

    /**
     * Parse (never run) PHP snippet code and report its first syntax error.
     *
     * Accepts code with or without an opening <?php tag, like the executor.
     *
     * @param string $code Snippet code.
     * @return array{line:int,message:string}|null Null when the code parses.
     */
    private static function php_syntax_error(string $code): ?array {
        $code = (string) preg_replace('/^\s*<\?(php)?/i', '', $code, 1);
        if (trim($code) === '') {
            return null;
        }

        try {
            token_get_all('<?php ' . $code, TOKEN_PARSE);
        } catch (\ParseError $e) {
            return [
                'line'    => (int) $e->getLine(),
                'message' => $e->getMessage(),
            ];
        }

        return null;
    }

    /**
     * Sanitize conditional-logic config.
     *
     * @param mixed $raw Raw conditions.
     * @return array
     */
    private function sanitize_conditions($raw): array {
        $defaults = SnippetPostType::defaults()['conditions'];
        $raw      = is_array($raw) ? $raw : [];

        $rules = [];
        if (!empty($raw['rules']) && is_array($raw['rules'])) {
            foreach ($raw['rules'] as $rule) {
                if (!is_array($rule) || empty($rule['type'])) {
                    continue;
                }
                $rules[] = [
                    'type'     => sanitize_key($rule['type']),
                    'operator' => (isset($rule['operator']) && $rule['operator'] === 'is_not') ? 'is_not' : 'is',
                    'value'    => isset($rule['value']) ? sanitize_text_field($rule['value']) : '',
                ];
            }
        }

        return [
            'enabled'  => !empty($raw['enabled']),
            'action'   => (isset($raw['action']) && $raw['action'] === 'hide') ? 'hide' : 'show',
            'relation' => (isset($raw['relation']) && $raw['relation'] === 'or') ? 'or' : 'and',
            'rules'    => $rules,
        ];
    }

    /**
     * Sanitize schedule config.
     *
     * @param mixed $raw Raw schedule.
     * @return array
     */
    private function sanitize_schedule($raw): array {
        $raw = is_array($raw) ? $raw : [];

        return [
            'enabled' => !empty($raw['enabled']),
            'start'   => isset($raw['start']) ? sanitize_text_field($raw['start']) : '',
            'end'     => isset($raw['end']) ? sanitize_text_field($raw['end']) : '',
        ];
    }

    // ------------------------------------------------------------------
    //  Misc
    // ------------------------------------------------------------------

    /**
     * UI configuration shipped alongside the snippet list.
     *
     * @return array
     */
    private function get_config(): array {
        return [
            'types'          => SnippetPostType::TYPES,
            'locations'      => SnippetPostType::LOCATIONS,
            'can_manage_php' => $this->can_manage_php(),
            'is_pro'         => ProFeatures::instance()->has_pro_license(),
            'free_limit'     => self::FREE_SNIPPET_LIMIT,
            'snippet_count'  => SnippetPostType::count(),
        ];
    }

    /**
     * Whether a free user has reached the snippet limit.
     *
     * Pro users are never limited.
     *
     * @return bool
     */
    private function at_free_limit(): bool {
        if (ProFeatures::instance()->has_pro_license()) {
            return false;
        }

        return SnippetPostType::count() >= self::FREE_SNIPPET_LIMIT;
    }

    /**
     * Error returned when the free snippet limit is reached.
     *
     * @return WP_Error
     */
    private function limit_error(): WP_Error {
        return new WP_Error(
            'extenderx_snippet_limit',
            sprintf(
                /* translators: %d: maximum number of snippets allowed on the free plan. */
                __('The free plan is limited to %d snippets. Upgrade to Pro to add unlimited snippets.', 'extenderx'),
                self::FREE_SNIPPET_LIMIT
            ),
            ['status' => 403]
        );
    }

    /**
     * 404 helper.
     *
     * @return WP_Error
     */
    private function not_found(): WP_Error {
        return new WP_Error('extenderx_snippet_not_found', __('Snippet not found.', 'extenderx'), ['status' => 404]);
    }

    /**
     * Shared `id` route argument schema.
     *
     * @return array
     */
    private function id_arg(): array {
        return [
            'id' => [
                'validate_callback' => static function ($param) {
                    return is_numeric($param);
                },
            ],
        ];
    }
}
