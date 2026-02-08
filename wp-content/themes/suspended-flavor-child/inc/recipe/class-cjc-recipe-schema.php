<?php
/**
 * CJC Recipe JSON-LD Schema
 *
 * @package CJC_Recipe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Generates JSON-LD schema markup for recipes.
 */
class CJC_Recipe_Schema {

    /**
     * Initialize schema output.
     */
    public static function init() {
        add_action( 'wp_head', array( __CLASS__, 'output_schema' ), 20 );
    }

    /**
     * Output JSON-LD schema in the head.
     */
    public static function output_schema() {
        if ( ! is_singular( 'post' ) && ! is_singular( 'page' ) ) {
            return;
        }

        global $post;
        $recipe_ids = self::get_recipes_in_content( $post->post_content );

        if ( empty( $recipe_ids ) ) {
            return;
        }

        foreach ( $recipe_ids as $recipe_id ) {
            $schema = self::generate_schema( $recipe_id, $post->ID );
            if ( $schema ) {
                echo '<script type="application/ld+json">' . "\n";
                echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
                echo "\n</script>\n";
            }
        }
    }

    /**
     * Find recipe IDs in post content.
     *
     * @param string $content Post content.
     * @return array Recipe IDs found.
     */
    public static function get_recipes_in_content( $content ) {
        $recipe_ids = array();

        // Match cjc/recipe blocks
        if ( preg_match_all( '/<!-- wp:cjc\/recipe \{"recipeId":(\d+)\}/', $content, $matches ) ) {
            $recipe_ids = array_merge( $recipe_ids, array_map( 'intval', $matches[1] ) );
        }

        // Also check for shortcode format [cjc_recipe id="123"]
        if ( preg_match_all( '/\[cjc_recipe[^\]]*id=["\']?(\d+)["\']?/', $content, $matches ) ) {
            $recipe_ids = array_merge( $recipe_ids, array_map( 'intval', $matches[1] ) );
        }

        return array_unique( $recipe_ids );
    }

    /**
     * Generate JSON-LD schema for a recipe.
     *
     * @param int $recipe_id Recipe post ID.
     * @param int $parent_id Parent post ID (the post containing the recipe).
     * @return array|false Schema array or false on failure.
     */
    public static function generate_schema( $recipe_id, $parent_id = 0 ) {
        $data = CJC_Recipe_Post_Type::get_recipe_data( $recipe_id );
        if ( ! $data ) {
            return false;
        }

        $parent_post = $parent_id ? get_post( $parent_id ) : null;
        $author = $parent_post ? get_the_author_meta( 'display_name', $parent_post->post_author ) : '';

        $schema = array(
            '@context'    => 'https://schema.org/',
            '@type'       => 'Recipe',
            'name'        => $data['title'],
            'description' => wp_strip_all_tags( $data['description'] ),
        );

        // Image
        if ( ! empty( $data['image_url'] ) ) {
            $schema['image'] = array( $data['image_url'] );
        }

        // Author
        if ( ! empty( $data['author_name'] ) || ! empty( $author ) ) {
            $schema['author'] = array(
                '@type' => 'Person',
                'name'  => ! empty( $data['author_name'] ) ? $data['author_name'] : $author,
            );
        }

        // Date
        if ( $parent_post ) {
            $schema['datePublished'] = get_the_date( 'c', $parent_post );
        }

        // Times (convert to ISO 8601)
        if ( ! empty( $data['prep_time'] ) ) {
            $iso = CJC_Recipe_REST_API::time_to_iso8601( $data['prep_time'] );
            if ( $iso ) {
                $schema['prepTime'] = $iso;
            }
        }
        if ( ! empty( $data['cook_time'] ) ) {
            $iso = CJC_Recipe_REST_API::time_to_iso8601( $data['cook_time'] );
            if ( $iso ) {
                $schema['cookTime'] = $iso;
            }
        }
        if ( ! empty( $data['total_time'] ) ) {
            $iso = CJC_Recipe_REST_API::time_to_iso8601( $data['total_time'] );
            if ( $iso ) {
                $schema['totalTime'] = $iso;
            }
        }

        // Yield
        if ( ! empty( $data['yield'] ) ) {
            $schema['recipeYield'] = $data['yield'];
        }

        // Category, Cuisine, Method
        if ( ! empty( $data['category'] ) ) {
            $schema['recipeCategory'] = $data['category'];
        }
        if ( ! empty( $data['cuisine'] ) ) {
            $schema['recipeCuisine'] = $data['cuisine'];
        }
        if ( ! empty( $data['method'] ) ) {
            $schema['cookingMethod'] = $data['method'];
        }

        // Diet
        if ( ! empty( $data['diet'] ) ) {
            $schema['suitableForDiet'] = 'https://schema.org/' . str_replace( ' ', '', $data['diet'] ) . 'Diet';
        }

        // Keywords
        if ( ! empty( $data['keywords'] ) ) {
            $schema['keywords'] = $data['keywords'];
        }

        // Ingredients
        $ingredients = self::format_ingredients_for_schema( $data['ingredients'] );
        if ( ! empty( $ingredients ) ) {
            $schema['recipeIngredient'] = $ingredients;
        }

        // Instructions
        $instructions = self::format_instructions_for_schema( $data['instructions'] );
        if ( ! empty( $instructions ) ) {
            $schema['recipeInstructions'] = $instructions;
        }

        // Nutrition
        $nutrition = self::format_nutrition_for_schema( $data['nutrition'] );
        if ( ! empty( $nutrition ) ) {
            $schema['nutrition'] = $nutrition;
        }

        // Rating
        if ( ! empty( $data['rating']['average'] ) && ! empty( $data['rating']['count'] ) ) {
            $schema['aggregateRating'] = array(
                '@type'       => 'AggregateRating',
                'ratingValue' => round( $data['rating']['average'], 1 ),
                'ratingCount' => $data['rating']['count'],
            );
        }

        // Video
        if ( ! empty( $data['video_url'] ) ) {
            $video_data = self::get_video_object( $data['video_url'] );
            if ( $video_data ) {
                $schema['video'] = $video_data;
            }
        }

        return $schema;
    }

    /**
     * Format ingredients for schema.
     *
     * @param array $ingredients Ingredients array.
     * @return array Formatted ingredients.
     */
    private static function format_ingredients_for_schema( $ingredients ) {
        if ( empty( $ingredients ) || ! is_array( $ingredients ) ) {
            return array();
        }

        $formatted = array();

        foreach ( $ingredients as $group ) {
            if ( isset( $group['items'] ) && is_array( $group['items'] ) ) {
                foreach ( $group['items'] as $item ) {
                    $text = '';
                    if ( ! empty( $item['amount'] ) ) {
                        $text .= $item['amount'] . ' ';
                    }
                    if ( ! empty( $item['unit'] ) ) {
                        $text .= $item['unit'] . ' ';
                    }
                    if ( ! empty( $item['name'] ) ) {
                        $text .= $item['name'];
                    }
                    if ( ! empty( $item['notes'] ) ) {
                        $text .= ', ' . $item['notes'];
                    }
                    $text = trim( $text );
                    if ( ! empty( $text ) ) {
                        $formatted[] = $text;
                    }
                }
            }
        }

        return $formatted;
    }

    /**
     * Format instructions for schema.
     *
     * @param array $instructions Instructions array.
     * @return array Formatted instructions.
     */
    private static function format_instructions_for_schema( $instructions ) {
        if ( empty( $instructions ) || ! is_array( $instructions ) ) {
            return array();
        }

        $formatted = array();

        foreach ( $instructions as $group ) {
            // If there are multiple groups with titles, use HowToSection
            if ( count( $instructions ) > 1 && ! empty( $group['title'] ) ) {
                $section = array(
                    '@type'           => 'HowToSection',
                    'name'            => $group['title'],
                    'itemListElement' => array(),
                );

                if ( isset( $group['steps'] ) && is_array( $group['steps'] ) ) {
                    foreach ( $group['steps'] as $step ) {
                        $section['itemListElement'][] = array(
                            '@type' => 'HowToStep',
                            'text'  => wp_strip_all_tags( $step['text'] ?? '' ),
                        );
                    }
                }

                if ( ! empty( $section['itemListElement'] ) ) {
                    $formatted[] = $section;
                }
            } else {
                // Single group or no title - just output steps
                if ( isset( $group['steps'] ) && is_array( $group['steps'] ) ) {
                    foreach ( $group['steps'] as $step ) {
                        $formatted[] = array(
                            '@type' => 'HowToStep',
                            'text'  => wp_strip_all_tags( $step['text'] ?? '' ),
                        );
                    }
                }
            }
        }

        return $formatted;
    }

    /**
     * Format nutrition data for schema.
     *
     * @param array $nutrition Nutrition data.
     * @return array|null Formatted nutrition or null if empty.
     */
    private static function format_nutrition_for_schema( $nutrition ) {
        if ( empty( $nutrition ) || ! is_array( $nutrition ) ) {
            return null;
        }

        $schema_nutrition = array(
            '@type' => 'NutritionInformation',
        );

        $has_data = false;
        $mapping = array(
            'serving_size'    => 'servingSize',
            'calories'        => 'calories',
            'sugar'           => 'sugarContent',
            'sodium'          => 'sodiumContent',
            'fat'             => 'fatContent',
            'saturated_fat'   => 'saturatedFatContent',
            'unsaturated_fat' => 'unsaturatedFatContent',
            'trans_fat'       => 'transFatContent',
            'carbohydrates'   => 'carbohydrateContent',
            'fiber'           => 'fiberContent',
            'protein'         => 'proteinContent',
            'cholesterol'     => 'cholesterolContent',
        );

        foreach ( $mapping as $key => $schema_key ) {
            if ( ! empty( $nutrition[ $key ] ) ) {
                $value = $nutrition[ $key ];

                // Ensure units for nutritional values
                if ( $key === 'calories' && is_numeric( $value ) ) {
                    $value = $value . ' calories';
                } elseif ( in_array( $key, array( 'cholesterol', 'sodium' ), true ) && is_numeric( $value ) ) {
                    $value = $value . ' mg';
                } elseif ( $key !== 'serving_size' && $key !== 'calories' && is_numeric( $value ) ) {
                    $value = $value . ' g';
                }

                $schema_nutrition[ $schema_key ] = $value;
                $has_data = true;
            }
        }

        return $has_data ? $schema_nutrition : null;
    }

    /**
     * Get video object data for schema.
     *
     * @param string $video_url Video URL.
     * @return array|null Video object or null.
     */
    private static function get_video_object( $video_url ) {
        if ( empty( $video_url ) ) {
            return null;
        }

        // Try to extract YouTube video ID
        $youtube_id = null;
        if ( preg_match( '/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $video_url, $matches ) ) {
            $youtube_id = $matches[1];
        }

        if ( $youtube_id ) {
            return array(
                '@type'        => 'VideoObject',
                'contentUrl'   => $video_url,
                'embedUrl'     => 'https://www.youtube.com/embed/' . $youtube_id,
                'thumbnailUrl' => 'https://img.youtube.com/vi/' . $youtube_id . '/maxresdefault.jpg',
            );
        }

        // Generic video object
        return array(
            '@type'      => 'VideoObject',
            'contentUrl' => $video_url,
        );
    }
}
