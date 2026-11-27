<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\ProFeatures;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Snippet Engine
 *
 * Loads active snippets and injects them into the page at the correct
 * locations (wp_head / wp_body_open / wp_footer) and executes PHP snippets in
 * the requested run context.
 *
 * Premium gating is applied here, not at the storage layer, so free users keep
 * their snippets but advanced behaviour (PHP, conditional logic, scheduling)
 * simply no-ops until Pro is active.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode
 * @since 1.7.0
 */
class SnippetEngine {
    use Singleton;

    /**
     * Pro features instance.
     *
     * @var ProFeatures
     */
    private ProFeatures $pro;

    /**
     * Per-request cache of candidate snippets (active + type/schedule gated).
     *
     * @var array<int, array>|null
     */
    private ?array $candidates = null;

    /**
     * Constructor — wire output hooks.
     */
    public function __construct() {
        $this->pro = ProFeatures::instance();

        // Frontend markup injection.
        add_action('wp_head', [$this, 'render_head'], 100);
        add_action('wp_body_open', [$this, 'render_body_open'], 10);
        add_action('wp_footer', [$this, 'render_footer'], 100);

        // PHP execution contexts.
        add_action('wp', [$this, 'run_frontend_php']);
        add_action('admin_init', [$this, 'run_admin_php']);
        $this->run_global_php();
    }

    // ------------------------------------------------------------------
    //  Markup output (wp_head / wp_body_open / wp_footer)
    // ------------------------------------------------------------------

    /**
     * Output snippets destined for the document head.
     *
     * @return void
     */
    public function render_head(): void {
        $this->render_location_group(['html_header', 'css_head', 'js_header']);
    }

    /**
     * Output snippets destined for the opening <body> tag.
     *
     * @return void
     */
    public function render_body_open(): void {
        $this->render_location_group(['html_body_open', 'js_body_open']);
    }

    /**
     * Output snippets destined for the document footer.
     *
     * @return void
     */
    public function render_footer(): void {
        $this->render_location_group(['html_footer', 'css_footer', 'js_footer']);
    }

    /**
     * Render every candidate snippet whose type+location key is in the group.
     *
     * @param string[] $location_keys Allowed `{type}_{location}` keys.
     * @return void
     */
    private function render_location_group(array $location_keys): void {
        foreach ($this->get_candidates() as $snippet) {
            if ($snippet['type'] === 'php') {
                continue;
            }

            $key = $snippet['type'] . '_' . $snippet['location'];
            if (!in_array($key, $location_keys, true)) {
                continue;
            }

            if (!$this->passes_conditions($snippet)) {
                continue;
            }

            echo $this->render_markup($snippet); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Admin-authored raw code.
        }
    }

    /**
     * Wrap a snippet's code in the appropriate markup for its type.
     *
     * @param array $snippet Normalised snippet.
     * @return string
     */
    private function render_markup(array $snippet): string {
        $code = (string) $snippet['code'];

        if (trim($code) === '') {
            return '';
        }

        $id = (int) $snippet['id'];

        switch ($snippet['type']) {
            case 'css':
                return sprintf(
                    "\n<style id=\"extenderx-snippet-%d\">\n%s\n</style>\n",
                    $id,
                    $code
                );

            case 'js':
                return sprintf(
                    "\n<script id=\"extenderx-snippet-%d\">\n%s\n</script>\n",
                    $id,
                    $code
                );

            case 'html':
            default:
                return sprintf(
                    "\n<!-- ExtenderX snippet #%d -->\n%s\n",
                    $id,
                    $code
                );
        }
    }

    // ------------------------------------------------------------------
    //  PHP execution
    // ------------------------------------------------------------------

    /**
     * Run PHP snippets scoped to "everywhere".
     *
     * Fired during module boot (the `init` hook), so query-dependent
     * conditions are not yet available — those snippets should target run
     * context rather than page conditions.
     *
     * @return void
     */
    private function run_global_php(): void {
        $this->run_php_for_location('everywhere', false);
    }

    /**
     * Run frontend-scoped PHP snippets once the main query is available.
     *
     * @return void
     */
    public function run_frontend_php(): void {
        if (is_admin()) {
            return;
        }
        $this->run_php_for_location('frontend', true);
    }

    /**
     * Run admin-scoped PHP snippets.
     *
     * @return void
     */
    public function run_admin_php(): void {
        $this->run_php_for_location('admin', false);
    }

    /**
     * Execute PHP snippets matching a run location.
     *
     * @param string $location          Run context (everywhere|frontend|admin).
     * @param bool   $evaluate_conditions Whether the query is available for conditions.
     * @return void
     */
    private function run_php_for_location(string $location, bool $evaluate_conditions): void {
        if (!$this->pro->can_use_premium_feature('custom_code_php')) {
            return;
        }

        if (!SnippetExecutor::is_execution_allowed()) {
            return;
        }

        foreach ($this->get_candidates() as $snippet) {
            if ($snippet['type'] !== 'php' || $snippet['location'] !== $location) {
                continue;
            }

            if ($evaluate_conditions && !$this->passes_conditions($snippet)) {
                continue;
            }

            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Snippet may intentionally output markup.
            echo SnippetExecutor::execute($snippet['code'], (int) $snippet['id']);
        }
    }

    // ------------------------------------------------------------------
    //  Candidate selection (active + premium + schedule gated)
    // ------------------------------------------------------------------

    /**
     * Get the per-request list of snippets eligible to run.
     *
     * Filters out inactive snippets, premium-gated PHP for free users, and
     * snippets outside their scheduled window. Conditional logic is evaluated
     * later (at output time) because it depends on the main query.
     *
     * @return array<int, array>
     */
    private function get_candidates(): array {
        if ($this->candidates !== null) {
            return $this->candidates;
        }

        $can_php = $this->pro->can_use_premium_feature('custom_code_php');

        $this->candidates = array_values(array_filter(
            SnippetPostType::query_active(),
            function (array $snippet) use ($can_php) {
                if (!$snippet['active']) {
                    return false;
                }

                if ($snippet['type'] === 'php' && !$can_php) {
                    return false;
                }

                return $this->passes_schedule($snippet);
            }
        ));

        return $this->candidates;
    }

    /**
     * Whether a snippet is within its scheduled window.
     *
     * Scheduling is a premium capability; for free users the schedule is
     * ignored and the snippet is always considered in-window.
     *
     * @param array $snippet Normalised snippet.
     * @return bool
     */
    private function passes_schedule(array $snippet): bool {
        $schedule = $snippet['schedule'];

        if (empty($schedule['enabled']) || !$this->pro->can_use_premium_feature('custom_code_scheduling')) {
            return true;
        }

        $now = current_time('timestamp');

        if (!empty($schedule['start'])) {
            $start = strtotime($schedule['start']);
            if ($start && $now < $start) {
                return false;
            }
        }

        if (!empty($schedule['end'])) {
            $end = strtotime($schedule['end']);
            if ($end && $now > $end) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether a snippet passes its conditional-logic rules.
     *
     * Conditional logic is a premium capability; for free users it is ignored
     * and the snippet always displays.
     *
     * @param array $snippet Normalised snippet.
     * @return bool
     */
    private function passes_conditions(array $snippet): bool {
        if (empty($snippet['conditions']['enabled'])) {
            return true;
        }

        if (!$this->pro->can_use_premium_feature('custom_code_conditions')) {
            return true;
        }

        return ConditionalLogic::should_display($snippet['conditions']);
    }
}
