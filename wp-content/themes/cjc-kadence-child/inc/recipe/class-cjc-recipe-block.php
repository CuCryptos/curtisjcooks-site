<?php
/**
 * CJC Recipe Gutenberg Block
 *
 * @package CJC_Recipe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registers and handles the Gutenberg recipe block.
 */
class CJC_Recipe_Block {

    /**
     * Initialize block registration.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_block' ) );
        add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_assets' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_assets' ) );
        add_shortcode( 'cjc_recipe', array( __CLASS__, 'render_shortcode' ) );
    }

    /**
     * Register the block.
     */
    public static function register_block() {
        register_block_type( 'cjc/recipe', array(
            'api_version'     => 2,
            'editor_script'   => 'cjc-recipe-editor',
            'render_callback' => array( __CLASS__, 'render_block' ),
            'attributes'      => array(
                'recipeId' => array(
                    'type'    => 'number',
                    'default' => 0,
                ),
            ),
        ) );
    }

    /**
     * Enqueue editor assets.
     */
    public static function enqueue_editor_assets() {
        $asset_file = get_stylesheet_directory() . '/build/recipe-editor.asset.php';

        if ( ! file_exists( $asset_file ) ) {
            return;
        }

        $asset = include $asset_file;

        wp_enqueue_script(
            'cjc-recipe-editor',
            get_stylesheet_directory_uri() . '/build/recipe-editor.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_enqueue_style(
            'cjc-recipe-editor-style',
            get_stylesheet_directory_uri() . '/build/recipe-editor.css',
            array(),
            $asset['version']
        );

        // Localize script with data
        wp_localize_script( 'cjc-recipe-editor', 'cjcRecipeEditor', array(
            'restUrl'       => rest_url( CJC_Recipe_REST_API::$namespace ),
            'nonce'         => wp_create_nonce( 'wp_rest' ),
            'postType'      => CJC_Recipe_Post_Type::$post_type,
            'dietOptions'   => CJC_Recipe_Meta::get_diet_options(),
            'nutritionLabels' => CJC_Recipe_Meta::get_nutrition_labels(),
        ) );
    }

    /**
     * Enqueue frontend assets.
     */
    public static function enqueue_frontend_assets() {
        if ( ! is_singular() ) {
            return;
        }

        global $post;
        if ( ! $post || ! has_block( 'cjc/recipe', $post ) && ! has_shortcode( $post->post_content, 'cjc_recipe' ) ) {
            return;
        }

        $asset_file = get_stylesheet_directory() . '/build/recipe.asset.php';

        if ( ! file_exists( $asset_file ) ) {
            return;
        }

        $asset = include $asset_file;

        wp_enqueue_script(
            'cjc-recipe-frontend',
            get_stylesheet_directory_uri() . '/build/recipe.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_enqueue_style(
            'cjc-recipe-style',
            get_stylesheet_directory_uri() . '/build/recipe.css',
            array(),
            $asset['version']
        );
    }

    /**
     * Render the block on the frontend.
     *
     * @param array $attributes Block attributes.
     * @return string Block HTML.
     */
    public static function render_block( $attributes ) {
        $recipe_id = absint( $attributes['recipeId'] ?? 0 );
        return self::render_recipe( $recipe_id );
    }

    /**
     * Render the shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Shortcode HTML.
     */
    public static function render_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts, 'cjc_recipe' );

        $recipe_id = absint( $atts['id'] );
        return self::render_recipe( $recipe_id );
    }

    /**
     * Render a recipe card.
     *
     * @param int $recipe_id Recipe post ID.
     * @return string Recipe HTML.
     */
    public static function render_recipe( $recipe_id ) {
        if ( ! $recipe_id ) {
            return '';
        }

        $data = CJC_Recipe_Post_Type::get_recipe_data( $recipe_id );
        if ( ! $data ) {
            return '';
        }

        // Enqueue frontend assets if not already done
        $asset_file = get_stylesheet_directory() . '/build/recipe.asset.php';
        if ( file_exists( $asset_file ) ) {
            $asset = include $asset_file;
            wp_enqueue_script(
                'cjc-recipe-frontend',
                get_stylesheet_directory_uri() . '/build/recipe.js',
                $asset['dependencies'],
                $asset['version'],
                true
            );
            wp_enqueue_style(
                'cjc-recipe-style',
                get_stylesheet_directory_uri() . '/build/recipe.css',
                array(),
                $asset['version']
            );
        }

        // Output recipe data for React
        $json_data = wp_json_encode( $data );

        ob_start();
        ?>
        <div
            id="cjc-recipe-<?php echo esc_attr( $recipe_id ); ?>"
            class="cjc-recipe-card-container"
            data-recipe-id="<?php echo esc_attr( $recipe_id ); ?>"
        >
            <!-- Server-side rendered fallback for SEO and no-JS -->
            <div class="cjc-recipe-card cjc-recipe-noscript">
                <?php echo self::render_static_recipe( $data ); ?>
            </div>
        </div>
        <script type="application/json" id="cjc-recipe-data-<?php echo esc_attr( $recipe_id ); ?>">
            <?php echo $json_data; ?>
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Render static HTML for recipe (SEO/no-JS fallback).
     *
     * @param array $data Recipe data.
     * @return string Static HTML.
     */
    private static function render_static_recipe( $data ) {
        ob_start();
        ?>
        <article class="cjc-recipe-static" itemscope itemtype="https://schema.org/Recipe">
            <?php if ( ! empty( $data['image_url'] ) ) : ?>
                <div class="cjc-recipe-image">
                    <img src="<?php echo esc_url( $data['image_url'] ); ?>" alt="<?php echo esc_attr( $data['title'] ); ?>" itemprop="image" />
                </div>
            <?php endif; ?>

            <header class="cjc-recipe-header">
                <h2 class="cjc-recipe-title" itemprop="name"><?php echo esc_html( $data['title'] ); ?></h2>

                <?php if ( ! empty( $data['description'] ) ) : ?>
                    <div class="cjc-recipe-description" itemprop="description">
                        <?php echo wp_kses_post( $data['description'] ); ?>
                    </div>
                <?php endif; ?>
            </header>

            <div class="cjc-recipe-meta">
                <?php if ( ! empty( $data['prep_time'] ) ) : ?>
                    <span class="cjc-recipe-prep-time">
                        <strong><?php esc_html_e( 'Prep:', 'suspended-flavor-child' ); ?></strong>
                        <span itemprop="prepTime" content="<?php echo esc_attr( CJC_Recipe_REST_API::time_to_iso8601( $data['prep_time'] ) ); ?>">
                            <?php echo esc_html( $data['prep_time'] ); ?>
                        </span>
                    </span>
                <?php endif; ?>

                <?php if ( ! empty( $data['cook_time'] ) ) : ?>
                    <span class="cjc-recipe-cook-time">
                        <strong><?php esc_html_e( 'Cook:', 'suspended-flavor-child' ); ?></strong>
                        <span itemprop="cookTime" content="<?php echo esc_attr( CJC_Recipe_REST_API::time_to_iso8601( $data['cook_time'] ) ); ?>">
                            <?php echo esc_html( $data['cook_time'] ); ?>
                        </span>
                    </span>
                <?php endif; ?>

                <?php if ( ! empty( $data['total_time'] ) ) : ?>
                    <span class="cjc-recipe-total-time">
                        <strong><?php esc_html_e( 'Total:', 'suspended-flavor-child' ); ?></strong>
                        <span itemprop="totalTime" content="<?php echo esc_attr( CJC_Recipe_REST_API::time_to_iso8601( $data['total_time'] ) ); ?>">
                            <?php echo esc_html( $data['total_time'] ); ?>
                        </span>
                    </span>
                <?php endif; ?>

                <?php if ( ! empty( $data['yield'] ) ) : ?>
                    <span class="cjc-recipe-yield">
                        <strong><?php esc_html_e( 'Servings:', 'suspended-flavor-child' ); ?></strong>
                        <span itemprop="recipeYield"><?php echo esc_html( $data['yield'] ); ?></span>
                    </span>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $data['ingredients'] ) ) : ?>
                <section class="cjc-recipe-ingredients">
                    <h3><?php esc_html_e( 'Ingredients', 'suspended-flavor-child' ); ?></h3>
                    <?php foreach ( $data['ingredients'] as $group ) : ?>
                        <?php if ( ! empty( $group['title'] ) ) : ?>
                            <h4><?php echo esc_html( $group['title'] ); ?></h4>
                        <?php endif; ?>
                        <?php if ( ! empty( $group['items'] ) ) : ?>
                            <ul>
                                <?php foreach ( $group['items'] as $item ) : ?>
                                    <li itemprop="recipeIngredient">
                                        <?php
                                        $text = '';
                                        if ( ! empty( $item['amount'] ) ) {
                                            $text .= esc_html( $item['amount'] ) . ' ';
                                        }
                                        if ( ! empty( $item['unit'] ) ) {
                                            $text .= esc_html( $item['unit'] ) . ' ';
                                        }
                                        if ( ! empty( $item['name'] ) ) {
                                            $text .= esc_html( $item['name'] );
                                        }
                                        if ( ! empty( $item['notes'] ) ) {
                                            $text .= ', ' . esc_html( $item['notes'] );
                                        }
                                        echo trim( $text );
                                        ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <?php if ( ! empty( $data['instructions'] ) ) : ?>
                <section class="cjc-recipe-instructions">
                    <h3><?php esc_html_e( 'Instructions', 'suspended-flavor-child' ); ?></h3>
                    <?php foreach ( $data['instructions'] as $group ) : ?>
                        <?php if ( ! empty( $group['title'] ) ) : ?>
                            <h4><?php echo esc_html( $group['title'] ); ?></h4>
                        <?php endif; ?>
                        <?php if ( ! empty( $group['steps'] ) ) : ?>
                            <ol>
                                <?php foreach ( $group['steps'] as $step ) : ?>
                                    <li itemprop="recipeInstructions" itemscope itemtype="https://schema.org/HowToStep">
                                        <span itemprop="text"><?php echo wp_kses_post( $step['text'] ?? '' ); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <?php if ( ! empty( $data['notes'] ) ) : ?>
                <section class="cjc-recipe-notes">
                    <h3><?php esc_html_e( 'Notes', 'suspended-flavor-child' ); ?></h3>
                    <?php echo wp_kses_post( $data['notes'] ); ?>
                </section>
            <?php endif; ?>

            <?php
            $has_nutrition = false;
            foreach ( $data['nutrition'] as $value ) {
                if ( ! empty( $value ) ) {
                    $has_nutrition = true;
                    break;
                }
            }
            ?>
            <?php if ( $has_nutrition ) : ?>
                <section class="cjc-recipe-nutrition" itemprop="nutrition" itemscope itemtype="https://schema.org/NutritionInformation">
                    <h3><?php esc_html_e( 'Nutrition', 'suspended-flavor-child' ); ?></h3>
                    <dl>
                        <?php
                        $nutrition_labels = CJC_Recipe_Meta::get_nutrition_labels();
                        $schema_props = array(
                            'serving_size'    => 'servingSize',
                            'calories'        => 'calories',
                            'sugar'           => 'sugarContent',
                            'sodium'          => 'sodiumContent',
                            'fat'             => 'fatContent',
                            'saturated_fat'   => 'saturatedFatContent',
                            'carbohydrates'   => 'carbohydrateContent',
                            'fiber'           => 'fiberContent',
                            'protein'         => 'proteinContent',
                            'cholesterol'     => 'cholesterolContent',
                        );
                        foreach ( $nutrition_labels as $key => $label ) :
                            if ( ! empty( $data['nutrition'][ $key ] ) ) :
                                $schema_prop = isset( $schema_props[ $key ] ) ? $schema_props[ $key ] : '';
                        ?>
                            <dt><?php echo esc_html( $label ); ?>:</dt>
                            <dd <?php if ( $schema_prop ) : ?>itemprop="<?php echo esc_attr( $schema_prop ); ?>"<?php endif; ?>>
                                <?php echo esc_html( $data['nutrition'][ $key ] ); ?>
                            </dd>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </dl>
                </section>
            <?php endif; ?>
        </article>
        <?php
        return ob_get_clean();
    }
}
