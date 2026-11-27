<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode;

use WPXERO\ExtenderX\Core\ModuleBase;
use WPXERO\ExtenderX\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Custom Code Module
 *
 * "Insert Headers and Footers + Custom Code Snippets" — a WordPress code
 * manager for HTML/header-footer, CSS, JavaScript and PHP snippets with
 * conditional logic and scheduling.
 *
 * The management layer (custom post type + REST API) is always available so
 * administrators can author snippets; the frontend injection engine is gated
 * behind the standard site-enhancement toggles (global master switch + this
 * module's own `enabled` flag), so flipping the module off cleanly stops all
 * output without losing stored snippets.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\CustomCode
 * @since 1.7.0
 */
class CustomCode extends ModuleBase {
    use Singleton;

    /**
     * Setting key used by ModuleBase to check the module toggle.
     *
     * @var string
     */
    private const MODULE_SETTINGS_KEY = 'global.custom_code';

    /**
     * Boot the module.
     *
     * Registers the always-on management layer before delegating the
     * enable-gating to ModuleBase.
     */
    public function __construct() {
        // Self-healing schema guard: ensure the snippets table exists before any
        // read/write. Cheap (one option compare) and covers installs that
        // upgraded without a reactivation or settings-version bump.
        SnippetTable::maybe_install();

        // Always available: management REST API.
        SnippetRestController::instance();

        // ModuleBase runs the enable gates and then calls initialize_features().
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function get_module_settings_key(): string {
        return self::MODULE_SETTINGS_KEY;
    }

    /**
     * Whether the module's frontend output is enabled.
     *
     * Overridden to default to enabled when the setting has never been
     * persisted. The base implementation defaults a missing value to false,
     * which would silently disable header/footer output on installs where the
     * v3 settings migration has not yet run. Output is only suppressed when
     * the toggle has been explicitly switched off.
     *
     * @return bool
     */
    protected function is_module_enabled(): bool {
        return (bool) $this->utils->extenderx_get_setting(
            self::MODULE_SETTINGS_KEY . '.enabled',
            true
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function get_settings_prefix(): string {
        return self::MODULE_SETTINGS_KEY;
    }

    /**
     * {@inheritDoc}
     *
     * The module boots its components in {@see self::initialize_features()}
     * rather than the per-feature toggle map, so this is empty.
     */
    protected function get_features(): array {
        return [];
    }

    /**
     * Boot the frontend injection engine once the enable gates pass.
     *
     * @return void
     */
    protected function initialize_features(): void {
        SnippetEngine::instance();
    }
}
