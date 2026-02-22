<?php
/**
 * CJC Recipe REST API
 *
 * @package CJC_Recipe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles REST API endpoints for recipes.
 */
class CJC_Recipe_REST_API {

    /**
     * REST API namespace.
     *
     * @var string
     */
    public static $namespace = 'cjc/v1';

    /**
     * Initialize REST API routes.
     */
    public static function init() {
        add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
    }

    /**
     * Register REST API routes.
     */
    public static function register_routes() {
        // Get single recipe
        register_rest_route(
            self::$namespace,
            '/recipes/(?P<id>\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( __CLASS__, 'get_recipe' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'id' => array(
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param );
                        },
                    ),
                ),
            )
        );

        // Get multiple recipes
        register_rest_route(
            self::$namespace,
            '/recipes',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( __CLASS__, 'get_recipes' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'per_page' => array(
                        'default'           => 10,
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param ) && $param > 0 && $param <= 100;
                        },
                    ),
                    'page'     => array(
                        'default'           => 1,
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param ) && $param > 0;
                        },
                    ),
                    'parent'   => array(
                        'default'           => 0,
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param );
                        },
                    ),
                ),
            )
        );

        // Get recipe by parent post ID
        register_rest_route(
            self::$namespace,
            '/recipes/by-parent/(?P<parent_id>\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( __CLASS__, 'get_recipe_by_parent' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'parent_id' => array(
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param );
                        },
                    ),
                ),
            )
        );
    }

    /**
     * Get a single recipe.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response or error.
     */
    public static function get_recipe( $request ) {
        $id = absint( $request['id'] );
        $data = CJC_Recipe_Post_Type::get_recipe_data( $id );

        if ( ! $data ) {
            return new WP_Error(
                'recipe_not_found',
                __( 'Recipe not found.', 'suspended-flavor-child' ),
                array( 'status' => 404 )
            );
        }

        return rest_ensure_response( $data );
    }

    /**
     * Get multiple recipes.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response.
     */
    public static function get_recipes( $request ) {
        $per_page = absint( $request['per_page'] );
        $page = absint( $request['page'] );
        $parent = absint( $request['parent'] );

        $args = array(
            'post_type'      => CJC_Recipe_Post_Type::$post_type,
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        if ( $parent > 0 ) {
            $args['meta_query'] = array(
                array(
                    'key'     => CJC_Recipe_Meta::$prefix . 'parent_post_id',
                    'value'   => $parent,
                    'compare' => '=',
                ),
            );
        }

        $query = new WP_Query( $args );
        $recipes = array();

        foreach ( $query->posts as $post ) {
            $recipes[] = CJC_Recipe_Post_Type::get_recipe_data( $post->ID );
        }

        $response = rest_ensure_response( $recipes );
        $response->header( 'X-WP-Total', $query->found_posts );
        $response->header( 'X-WP-TotalPages', $query->max_num_pages );

        return $response;
    }

    /**
     * Get recipe by parent post ID.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response or error.
     */
    public static function get_recipe_by_parent( $request ) {
        $parent_id = absint( $request['parent_id'] );

        $args = array(
            'post_type'      => CJC_Recipe_Post_Type::$post_type,
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'     => CJC_Recipe_Meta::$prefix . 'parent_post_id',
                    'value'   => $parent_id,
                    'compare' => '=',
                ),
            ),
        );

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            $data = CJC_Recipe_Post_Type::get_recipe_data( $query->posts[0]->ID );
            return rest_ensure_response( $data );
        }

        return new WP_Error(
            'recipe_not_found',
            __( 'No recipe found for this post.', 'suspended-flavor-child' ),
            array( 'status' => 404 )
        );
    }

    /**
     * Format recipe data for API response.
     *
     * @param array $recipe_data Raw recipe data.
     * @return array Formatted recipe data.
     */
    public static function format_for_response( $recipe_data ) {
        // Add computed fields
        $recipe_data['prep_time_iso'] = self::time_to_iso8601( $recipe_data['prep_time'] );
        $recipe_data['cook_time_iso'] = self::time_to_iso8601( $recipe_data['cook_time'] );
        $recipe_data['total_time_iso'] = self::time_to_iso8601( $recipe_data['total_time'] );

        return $recipe_data;
    }

    /**
     * Convert human-readable time to ISO 8601 duration.
     *
     * @param string $time Human-readable time (e.g., "30 minutes", "1 hour 30 minutes").
     * @return string ISO 8601 duration (e.g., "PT30M", "PT1H30M").
     */
    public static function time_to_iso8601( $time ) {
        if ( empty( $time ) ) {
            return '';
        }

        $time = strtolower( trim( $time ) );
        $hours = 0;
        $minutes = 0;

        // Match hours
        if ( preg_match( '/(\d+)\s*(?:hour|hr|h)/', $time, $matches ) ) {
            $hours = intval( $matches[1] );
        }

        // Match minutes
        if ( preg_match( '/(\d+)\s*(?:minute|min|m)(?!o)/', $time, $matches ) ) {
            $minutes = intval( $matches[1] );
        }

        // If just a number, assume minutes
        if ( $hours === 0 && $minutes === 0 && preg_match( '/^(\d+)$/', $time, $matches ) ) {
            $minutes = intval( $matches[1] );
        }

        if ( $hours === 0 && $minutes === 0 ) {
            return '';
        }

        $iso = 'PT';
        if ( $hours > 0 ) {
            $iso .= $hours . 'H';
        }
        if ( $minutes > 0 ) {
            $iso .= $minutes . 'M';
        }

        return $iso;
    }

    /**
     * Convert ISO 8601 duration to human-readable time.
     *
     * @param string $iso ISO 8601 duration.
     * @return string Human-readable time.
     */
    public static function iso8601_to_time( $iso ) {
        if ( empty( $iso ) ) {
            return '';
        }

        $hours = 0;
        $minutes = 0;

        if ( preg_match( '/(\d+)H/', $iso, $matches ) ) {
            $hours = intval( $matches[1] );
        }
        if ( preg_match( '/(\d+)M/', $iso, $matches ) ) {
            $minutes = intval( $matches[1] );
        }

        $parts = array();
        if ( $hours > 0 ) {
            $parts[] = $hours . ' ' . _n( 'hour', 'hours', $hours, 'suspended-flavor-child' );
        }
        if ( $minutes > 0 ) {
            $parts[] = $minutes . ' ' . _n( 'minute', 'minutes', $minutes, 'suspended-flavor-child' );
        }

        return implode( ' ', $parts );
    }
}
