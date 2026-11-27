<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Conditional Logic Evaluator
 *
 * Decides whether a snippet should be displayed on the current request based
 * on a set of user-defined rules (page type, post type, post, user state,
 * role, device and URL).
 *
 * Rule set shape:
 *   [
 *     'enabled'  => bool,
 *     'action'   => 'show' | 'hide',
 *     'relation' => 'and' | 'or',
 *     'rules'    => [
 *       ['type' => 'page_type', 'operator' => 'is'|'is_not', 'value' => 'front_page'],
 *       ...
 *     ],
 *   ]
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode
 * @since 1.7.0
 */
class ConditionalLogic {

    /**
     * Determine whether a snippet should display given its condition config.
     *
     * When conditions are disabled or empty the snippet always displays.
     *
     * @param array $conditions Normalised condition config.
     * @return bool
     */
    public static function should_display(array $conditions): bool {
        $enabled = !empty($conditions['enabled']);
        $rules   = isset($conditions['rules']) && is_array($conditions['rules'])
            ? $conditions['rules']
            : [];

        if (!$enabled || empty($rules)) {
            return true;
        }

        $relation = ($conditions['relation'] ?? 'and') === 'or' ? 'or' : 'and';
        $action   = ($conditions['action'] ?? 'show') === 'hide' ? 'hide' : 'show';

        $matched = self::evaluate_rules($rules, $relation);

        // 'show' → display when the rules match; 'hide' → display when they don't.
        return $action === 'show' ? $matched : !$matched;
    }

    /**
     * Combine the result of all rules using the given relation.
     *
     * @param array  $rules    List of rule definitions.
     * @param string $relation 'and' | 'or'.
     * @return bool
     */
    private static function evaluate_rules(array $rules, string $relation): bool {
        foreach ($rules as $rule) {
            if (!is_array($rule) || empty($rule['type'])) {
                continue;
            }

            $result   = self::match_rule($rule);
            $operator = ($rule['operator'] ?? 'is') === 'is_not' ? 'is_not' : 'is';

            if ($operator === 'is_not') {
                $result = !$result;
            }

            if ($relation === 'or' && $result) {
                return true;
            }

            if ($relation === 'and' && !$result) {
                return false;
            }
        }

        // AND with no failures → true; OR with no successes → false.
        return $relation === 'and';
    }

    /**
     * Evaluate a single rule against the current request.
     *
     * @param array $rule Rule definition.
     * @return bool Whether the rule's subject matches (before operator inversion).
     */
    private static function match_rule(array $rule): bool {
        $type  = (string) $rule['type'];
        $value = $rule['value'] ?? '';

        switch ($type) {
            case 'page_type':
                return self::match_page_type((string) $value);

            case 'post_type':
                return is_singular((string) $value);

            case 'post_id':
                return is_singular() && (int) get_queried_object_id() === (int) $value;

            case 'user_status':
                return $value === 'logged_in' ? is_user_logged_in() : !is_user_logged_in();

            case 'user_role':
                return self::user_has_role((string) $value);

            case 'device':
                return $value === 'mobile' ? wp_is_mobile() : !wp_is_mobile();

            case 'url':
                return self::current_url_contains((string) $value);
        }

        return false;
    }

    /**
     * Match WordPress conditional tags for page-type rules.
     *
     * @param string $value Page type identifier.
     * @return bool
     */
    private static function match_page_type(string $value): bool {
        switch ($value) {
            case 'front_page':
                return is_front_page();
            case 'blog':
                return is_home();
            case 'single':
                return is_single();
            case 'page':
                return is_page();
            case 'singular':
                return is_singular();
            case 'archive':
                return is_archive();
            case 'search':
                return is_search();
            case 'category':
                return is_category();
            case 'tag':
                return is_tag();
            case 'author':
                return is_author();
            case '404':
                return is_404();
        }

        return false;
    }

    /**
     * Check whether the current user has a given role.
     *
     * @param string $role Role slug.
     * @return bool
     */
    private static function user_has_role(string $role): bool {
        if (!is_user_logged_in() || $role === '') {
            return false;
        }

        $user = wp_get_current_user();
        return in_array($role, (array) $user->roles, true);
    }

    /**
     * Check whether the current request URI contains the given fragment.
     *
     * @param string $needle Substring to look for.
     * @return bool
     */
    private static function current_url_contains(string $needle): bool {
        if ($needle === '' || empty($_SERVER['REQUEST_URI'])) {
            return false;
        }

        $request_uri = esc_url_raw(wp_unslash($_SERVER['REQUEST_URI']));
        return strpos($request_uri, $needle) !== false;
    }
}
