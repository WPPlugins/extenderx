<?php

namespace WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility;

use WPXERO\ExtenderX\Traits\Singleton;
use WPXERO\ExtenderX\Core\SettingsCache;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Accessibility Statement Generator
 *
 * Creates a draft page from an editable template describing the steps a site
 * takes on accessibility and how to get in touch about problems.
 *
 * The template deliberately makes no claim of conformance with any standard
 * or law. Only the site owner can assess that, and a generated page asserting
 * it on their behalf would be a liability rather than a feature.
 *
 * Premium: the class is only instantiated when ModuleBase's licence gate
 * passes, so the route below simply does not exist otherwise.
 *
 * @package WPXERO\ExtenderX\Modules\SiteEnhancement\Accessibility
 * @since 1.9.0
 */
class StatementGenerator {
    use Singleton;

    /**
     * Register the REST route.
     *
     * @since 1.9.0
     */
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register routes.
     *
     * @since 1.9.0
     * @return void
     */
    public function register_routes(): void {
        register_rest_route('wpxero/extenderx/v1', '/a11y/statement', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_template'],
                'permission_callback' => [$this, 'check_permission'],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'create_page'],
                'permission_callback' => [$this, 'check_permission'],
                'args'                => [
                    'title' => [
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'content' => [
                        'type' => 'string',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Creating pages is an administrator action.
     *
     * @since 1.9.0
     * @return bool
     */
    public function check_permission(): bool {
        return current_user_can('manage_options') && current_user_can('publish_pages');
    }

    /**
     * Return the default statement title and body.
     *
     * @since 1.9.0
     * @return WP_REST_Response
     */
    public function get_template(): WP_REST_Response {
        return rest_ensure_response([
            'title'   => __('Accessibility Statement', 'extenderx'),
            'content' => self::default_template(),
        ]);
    }

    /**
     * Create the statement as a draft page.
     *
     * Always a draft: the text needs a human to check it describes what the
     * site actually does before anyone publishes it.
     *
     * @since 1.9.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function create_page(WP_REST_Request $request) {
        $title = (string) $request->get_param('title');
        $title = '' !== trim($title) ? $title : __('Accessibility Statement', 'extenderx');

        $content = (string) $request->get_param('content');
        $content = '' !== trim($content) ? $content : self::default_template();

        $page_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => wp_kses_post($content),
            'post_status'  => 'draft',
            'post_type'    => 'page',
        ], true);

        if (is_wp_error($page_id)) {
            return $page_id;
        }

        return rest_ensure_response([
            'id'      => $page_id,
            'editUrl' => get_edit_post_link($page_id, 'raw'),
        ]);
    }

    /**
     * List items for the tools that are actually switched on.
     *
     * The statement used to list the toolbar, the skip link and focus outlines
     * whatever the settings, so a published statement could describe tools
     * the site doesn't have.
     *
     * @return string `<li>` markup.
     */
    private static function tool_list_items(): string {
        $cache = SettingsCache::instance();
        $on    = static function (string $tool) use ($cache): bool {
            return (bool) $cache->get_setting('global.enabled', true)
                && (bool) $cache->get_setting('global.accessibility.enabled', true)
                && (bool) $cache->get_setting('global.accessibility.' . $tool . '.enabled', false);
        };

        $items = [];
        if ($on('toolbar')) {
            $items[] = esc_html__(
                'A toolbar that lets you change text size, contrast and colours, and pause motion.',
                'extenderx'
            );
        }
        if ($on('skip_link')) {
            $items[] = esc_html__('A link that lets keyboard users skip straight to the main content.', 'extenderx');
        }
        if ($on('focus_outlines')) {
            $items[] = esc_html__('A visible outline showing which element the keyboard is on.', 'extenderx');
        }
        if (!$items) {
            $items[] = esc_html__('List the accessibility tools or features your site offers, or remove this section.', 'extenderx');
        }

        return '<li>' . implode('</li><li>', $items) . '</li>';
    }

    /**
     * The default statement body.
     *
     * Written as prompts for the owner to complete rather than assertions on
     * their behalf, and carrying no conformance claim.
     *
     * @since 1.9.0
     * @return string
     */
    public static function default_template(): string {
        $email = get_bloginfo('admin_email');

        $template = sprintf(
            /* translators: 1: site name, 2: admin email address */
            '<!-- wp:paragraph --><p>%1$s</p><!-- /wp:paragraph -->'
            . '<!-- wp:heading --><h2>%2$s</h2><!-- /wp:heading -->'
            . '<!-- wp:paragraph --><p>%3$s</p><!-- /wp:paragraph -->'
            . '<!-- wp:heading --><h2>%4$s</h2><!-- /wp:heading -->'
            . '<!-- wp:list --><ul>%5$s</ul><!-- /wp:list -->'
            . '<!-- wp:heading --><h2>%8$s</h2><!-- /wp:heading -->'
            . '<!-- wp:paragraph --><p>%9$s</p><!-- /wp:paragraph -->'
            . '<!-- wp:heading --><h2>%10$s</h2><!-- /wp:heading -->'
            . '<!-- wp:paragraph --><p>%11$s</p><!-- /wp:paragraph -->',
            sprintf(
                /* translators: %s: site name */
                esc_html__(
                    'We want %s to be usable by as many people as possible, and we treat accessibility as ongoing work rather than a finished task.',
                    'extenderx'
                ),
                esc_html(get_bloginfo('name'))
            ),
            esc_html__('What we are doing', 'extenderx'),
            esc_html__(
                'Describe here the steps you take — for example writing alternative text for images, checking colour contrast, testing with a keyboard, or reviewing new pages before they go live. Replace this paragraph with what is true of your site.',
                'extenderx'
            ),
            esc_html__('Tools available on this site', 'extenderx'),
            self::tool_list_items(),
            '', // %6$s and %7$s were fixed list items; kept so later numbers don't shift.
            '',
            esc_html__('Known limitations', 'extenderx'),
            esc_html__(
                'List anything you know is not yet accessible, and when you expect to address it. Being specific here is more useful to visitors than a general assurance.',
                'extenderx'
            ),
            esc_html__('Contact us', 'extenderx'),
            sprintf(
                /* translators: %s: admin email address */
                esc_html__(
                    'If you run into a barrier on this site, please tell us at %s and describe what you were trying to do. We will do our best to help and to fix the underlying problem.',
                    'extenderx'
                ),
                esc_html($email)
            )
        );

        /**
         * Filter the default accessibility statement template.
         *
         * @since 1.9.0
         * @param string $template Block-markup template.
         */
        return apply_filters('extenderx_a11y_statement_template', $template);
    }
}
