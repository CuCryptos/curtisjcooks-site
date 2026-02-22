<?php
/**
 * CJC Recipe Meta Fields
 *
 * @package CJC_Recipe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles recipe meta field registration and management.
 */
class CJC_Recipe_Meta {

    /**
     * Meta key prefix.
     *
     * @var string
     */
    public static $prefix = '_cjc_recipe_';

    /**
     * Initialize meta registration.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_meta_fields' ) );
    }

    /**
     * Get all meta field definitions.
     *
     * @return array Meta field definitions.
     */
    public static function get_meta_fields() {
        return array(
            // General fields
            'description'       => array(
                'type'              => 'string',
                'sanitize_callback' => 'wp_kses_post',
                'default'           => '',
            ),
            'author_name'       => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),

            // Time fields (stored as human-readable, e.g., "30 minutes")
            'prep_time'         => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'cook_time'         => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'total_time'        => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'additional_time_label' => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'additional_time_value' => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),

            // Yield fields
            'yield'             => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'yield_number'      => array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 0,
            ),

            // Classification fields
            'category'          => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'cuisine'           => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'method'            => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'diet'              => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'keywords'          => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),

            // Structured content (JSON encoded)
            'ingredients'       => array(
                'type'              => 'string',
                'sanitize_callback' => array( __CLASS__, 'sanitize_json' ),
                'default'           => '[]',
            ),
            'instructions'      => array(
                'type'              => 'string',
                'sanitize_callback' => array( __CLASS__, 'sanitize_json' ),
                'default'           => '[]',
            ),

            // Notes
            'notes'             => array(
                'type'              => 'string',
                'sanitize_callback' => 'wp_kses_post',
                'default'           => '',
            ),

            // Video
            'video_url'         => array(
                'type'              => 'string',
                'sanitize_callback' => 'esc_url_raw',
                'default'           => '',
            ),

            // Nutrition fields
            'serving_size'      => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'calories'          => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'sugar'             => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'sodium'            => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'fat'               => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'saturated_fat'     => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'unsaturated_fat'   => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'trans_fat'         => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'carbohydrates'     => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'fiber'             => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'protein'           => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
            'cholesterol'       => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),

            // Rating fields
            'average_rating'    => array(
                'type'              => 'number',
                'sanitize_callback' => array( __CLASS__, 'sanitize_float' ),
                'default'           => 0,
            ),
            'total_reviews'     => array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 0,
            ),

            // Parent post reference
            'parent_post_id'    => array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 0,
            ),
        );
    }

    /**
     * Register all meta fields for the recipe post type.
     */
    public static function register_meta_fields() {
        $fields = self::get_meta_fields();

        foreach ( $fields as $key => $config ) {
            register_post_meta(
                CJC_Recipe_Post_Type::$post_type,
                self::$prefix . $key,
                array(
                    'type'              => $config['type'],
                    'single'            => true,
                    'show_in_rest'      => true,
                    'sanitize_callback' => $config['sanitize_callback'],
                    'default'           => $config['default'],
                    'auth_callback'     => function() {
                        return current_user_can( 'edit_posts' );
                    },
                )
            );
        }
    }

    /**
     * Sanitize float value.
     *
     * @param mixed $value The value to sanitize.
     * @return float Sanitized float value.
     */
    public static function sanitize_float( $value ) {
        return floatval( $value );
    }

    /**
     * Sanitize JSON string.
     *
     * @param string $value The JSON string to sanitize.
     * @return string Sanitized JSON string.
     */
    public static function sanitize_json( $value ) {
        if ( empty( $value ) ) {
            return '[]';
        }

        // If it's already a valid JSON string, return it
        $decoded = json_decode( $value, true );
        if ( json_last_error() === JSON_ERROR_NONE ) {
            return wp_json_encode( $decoded );
        }

        return '[]';
    }

    /**
     * Get a single meta value.
     *
     * @param int    $post_id Post ID.
     * @param string $key     Meta key (without prefix).
     * @return mixed Meta value.
     */
    public static function get_meta( $post_id, $key ) {
        $value = get_post_meta( $post_id, self::$prefix . $key, true );

        // Decode JSON fields
        if ( in_array( $key, array( 'ingredients', 'instructions' ), true ) ) {
            return json_decode( $value, true ) ?: array();
        }

        return $value;
    }

    /**
     * Set a single meta value.
     *
     * @param int    $post_id Post ID.
     * @param string $key     Meta key (without prefix).
     * @param mixed  $value   Meta value.
     * @return bool True on success, false on failure.
     */
    public static function set_meta( $post_id, $key, $value ) {
        // Encode arrays to JSON for storage
        if ( is_array( $value ) && in_array( $key, array( 'ingredients', 'instructions' ), true ) ) {
            $value = wp_json_encode( $value );
        }

        return update_post_meta( $post_id, self::$prefix . $key, $value );
    }

    /**
     * Get all meta values for a recipe.
     *
     * @param int $post_id Post ID.
     * @return array All meta values.
     */
    public static function get_all_meta( $post_id ) {
        $fields = self::get_meta_fields();
        $meta = array();

        foreach ( $fields as $key => $config ) {
            $meta[ $key ] = self::get_meta( $post_id, $key );
        }

        return $meta;
    }

    /**
     * Get nutrition field labels.
     *
     * @return array Nutrition labels.
     */
    public static function get_nutrition_labels() {
        return array(
            'serving_size'    => __( 'Serving Size', 'suspended-flavor-child' ),
            'calories'        => __( 'Calories', 'suspended-flavor-child' ),
            'sugar'           => __( 'Sugar', 'suspended-flavor-child' ),
            'sodium'          => __( 'Sodium', 'suspended-flavor-child' ),
            'fat'             => __( 'Fat', 'suspended-flavor-child' ),
            'saturated_fat'   => __( 'Saturated Fat', 'suspended-flavor-child' ),
            'unsaturated_fat' => __( 'Unsaturated Fat', 'suspended-flavor-child' ),
            'trans_fat'       => __( 'Trans Fat', 'suspended-flavor-child' ),
            'carbohydrates'   => __( 'Carbohydrates', 'suspended-flavor-child' ),
            'fiber'           => __( 'Fiber', 'suspended-flavor-child' ),
            'protein'         => __( 'Protein', 'suspended-flavor-child' ),
            'cholesterol'     => __( 'Cholesterol', 'suspended-flavor-child' ),
        );
    }

    /**
     * Get diet options.
     *
     * @return array Diet options.
     */
    public static function get_diet_options() {
        return array(
            ''            => __( 'N/A', 'suspended-flavor-child' ),
            'Diabetic'    => __( 'Diabetic', 'suspended-flavor-child' ),
            'Gluten Free' => __( 'Gluten Free', 'suspended-flavor-child' ),
            'Halal'       => __( 'Halal', 'suspended-flavor-child' ),
            'Hindu'       => __( 'Hindu', 'suspended-flavor-child' ),
            'Kosher'      => __( 'Kosher', 'suspended-flavor-child' ),
            'Low Calorie' => __( 'Low Calorie', 'suspended-flavor-child' ),
            'Low Fat'     => __( 'Low Fat', 'suspended-flavor-child' ),
            'Low Lactose' => __( 'Low Lactose', 'suspended-flavor-child' ),
            'Low Salt'    => __( 'Low Salt', 'suspended-flavor-child' ),
            'Vegan'       => __( 'Vegan', 'suspended-flavor-child' ),
            'Vegetarian'  => __( 'Vegetarian', 'suspended-flavor-child' ),
        );
    }
}
