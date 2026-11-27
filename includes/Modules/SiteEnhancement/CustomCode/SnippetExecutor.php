<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Snippet PHP Executor
 *
 * Safely evaluates PHP snippet code with error trapping so a faulty snippet
 * can never fatally crash the site. Execution is only reached after the engine
 * has confirmed the premium gate and the kill-switch constant.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode
 * @since 1.7.0
 */
class SnippetExecutor {

    /**
     * Whether PHP snippet execution is allowed on this site.
     *
     * Site owners can hard-disable execution by defining the kill-switch
     * constant, regardless of stored snippets or licence state.
     *
     * @return bool
     */
    public static function is_execution_allowed(): bool {
        if (defined('XERO_EXTENDERX_DISABLE_PHP_SNIPPETS') && XERO_EXTENDERX_DISABLE_PHP_SNIPPETS) {
            return false;
        }

        /**
         * Filter whether PHP code snippets may execute.
         *
         * @param bool $allowed Default true.
         */
        return (bool) apply_filters('extenderx_allow_php_execution', true);
    }

    /**
     * Execute a PHP snippet and return any output it echoes.
     *
     * The snippet is run inside an output buffer; thrown errors/exceptions are
     * caught and (when WP_DEBUG is on) logged, but never bubble up.
     *
     * @param string $code     Raw snippet code (with or without PHP tags).
     * @param int    $snippet_id Snippet post ID, used only for error context.
     * @return string Captured output.
     */
    public static function execute(string $code, int $snippet_id = 0): string {
        if (!self::is_execution_allowed()) {
            return '';
        }

        $code = self::strip_php_tags($code);

        if (trim($code) === '') {
            return '';
        }

        ob_start();

        try {
            // phpcs:ignore Squiz.PHP.Eval.Discouraged -- Intentional: admin-authored snippets, gated by capability + premium + kill-switch.
            eval($code);
        } catch (\Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(
                    sprintf(
                        'ExtenderX: PHP snippet #%d error: %s in %s on line %d',
                        $snippet_id,
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    )
                );
            }
        }

        return (string) ob_get_clean();
    }

    /**
     * Remove a leading PHP open tag and trailing close tag so the code is
     * suitable for eval(), which expects raw PHP without an opening tag.
     *
     * @param string $code Raw code.
     * @return string
     */
    private static function strip_php_tags(string $code): string {
        $code = preg_replace('/^\s*<\?(php)?/i', '', $code, 1);
        $code = preg_replace('/\?>\s*$/', '', $code, 1);
        return (string) $code;
    }
}
