<?php

namespace WPXERO\ExtenderX\Modules\Gutenberg;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Modules\Gutenberg\Enqueue;
use WPXERO\ExtenderX\Modules\Gutenberg\Attributes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Singleton Trait
 *
 * Provides singleton functionality to classes
 */
class Gutenberg {
    use Singleton;

    public function __construct() {
        Enqueue::instance();
        Attributes::instance();
    }
}
