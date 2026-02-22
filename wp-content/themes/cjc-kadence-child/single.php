<?php
/**
 * CJC Kadence Child — Single Post (Immersive Recipe Page)
 *
 * @package CJC_Kadence_Child
 */

defined('ABSPATH') || exit;

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();

        // --- Featured image ---
        $featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');

        // --- Category ---
        $categories   = get_the_category();
        $category     = !empty($categories) ? $categories[0] : null;
        $cat_name     = $category ? $category->name : '';
        $cat_link     = $category ? get_category_link($category->term_id) : '';

        // --- Recipe meta (prefix: _cjc_recipe_) ---
        $post_id      = get_the_ID();
        $prep_time    = get_post_meta($post_id, '_cjc_recipe_prep_time', true);
        $cook_time    = get_post_meta($post_id, '_cjc_recipe_cook_time', true);
        $total_time   = get_post_meta($post_id, '_cjc_recipe_total_time', true);
        $yield        = get_post_meta($post_id, '_cjc_recipe_yield', true);
        $yield_number = get_post_meta($post_id, '_cjc_recipe_yield_number', true);
        $ingredients  = get_post_meta($post_id, '_cjc_recipe_ingredients', true);
        $instructions = get_post_meta($post_id, '_cjc_recipe_instructions', true);
        $notes        = get_post_meta($post_id, '_cjc_recipe_notes', true);
        $calories     = get_post_meta($post_id, '_cjc_recipe_calories', true);
        $fat          = get_post_meta($post_id, '_cjc_recipe_fat', true);
        $protein      = get_post_meta($post_id, '_cjc_recipe_protein', true);
        $carbs        = get_post_meta($post_id, '_cjc_recipe_carbohydrates', true);

        // Decode JSON fields
        $ingredients_data  = is_string($ingredients) ? json_decode($ingredients, true) : $ingredients;
        $instructions_data = is_string($instructions) ? json_decode($instructions, true) : $instructions;

        if (!is_array($ingredients_data)) {
            $ingredients_data = [];
        }
        if (!is_array($instructions_data)) {
            $instructions_data = [];
        }

        $has_recipe = !empty($ingredients_data) || !empty($instructions_data);
        $has_nutrition = $calories || $fat || $protein || $carbs;

        $servings = $yield_number ? intval($yield_number) : 4;
?>

<!-- 1. Reading Progress Bar -->
<div class="reading-progress" aria-hidden="true"></div>

<!-- 2. Hero Section -->
<section class="recipe-hero">
    <?php if ($featured_img_url) : ?>
        <img class="recipe-hero__image"
             src="<?php echo esc_url($featured_img_url); ?>"
             alt="<?php echo esc_attr(get_the_title()); ?>">
    <?php endif; ?>
    <div class="recipe-hero__overlay" aria-hidden="true"></div>
    <div class="recipe-hero__content">
        <?php if ($cat_name) : ?>
            <a class="recipe-hero__category" href="<?php echo esc_url($cat_link); ?>">
                <?php echo esc_html($cat_name); ?>
            </a>
        <?php endif; ?>
        <h1 class="recipe-hero__title"><?php the_title(); ?></h1>
        <div class="recipe-hero__meta">
            <span><?php echo esc_html(get_the_date()); ?></span>
            <span>by <?php the_author(); ?></span>
            <?php if ($total_time) : ?>
                <span><?php echo esc_html($total_time); ?></span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 3. Kapa Triangle Divider -->
<div class="kapa-divider kapa-divider--triangle" aria-hidden="true"></div>

<!-- 4. Sticky Recipe Nav -->
<nav class="recipe-sticky-nav" aria-label="Recipe sections">
    <div class="recipe-sticky-nav__links">
        <a class="recipe-sticky-nav__link" href="#recipe-story" data-section="recipe-story">Story</a>
        <?php if ($has_recipe) : ?>
            <a class="recipe-sticky-nav__link" href="#recipe-ingredients" data-section="recipe-ingredients">Ingredients</a>
            <a class="recipe-sticky-nav__link" href="#recipe-instructions" data-section="recipe-instructions">Steps</a>
        <?php endif; ?>
        <?php if ($notes) : ?>
            <a class="recipe-sticky-nav__link" href="#recipe-notes" data-section="recipe-notes">Notes</a>
        <?php endif; ?>
    </div>
</nav>

<!-- 5. Story Section + Floating Sidebar -->
<div class="recipe-layout">
    <article id="recipe-story" class="recipe-story">
        <?php the_content(); ?>
    </article>

    <!-- Floating Sidebar -->
    <?php
    if ($category) :
        $sidebar_args = [
            'post_type'      => 'post',
            'posts_per_page' => 5,
            'post__not_in'   => [$post_id],
            'cat'            => $category->term_id,
            'orderby'        => 'rand',
            'post_status'    => 'publish',
        ];
        $sidebar_query = new WP_Query($sidebar_args);
        if ($sidebar_query->have_posts()) :
    ?>
    <aside class="recipe-sidebar" aria-label="More recipes">
        <div class="recipe-sidebar__inner">
            <h4 class="recipe-sidebar__title">Also in <?php echo esc_html($cat_name); ?></h4>
            <ul class="recipe-sidebar__list">
                <?php while ($sidebar_query->have_posts()) : $sidebar_query->the_post(); ?>
                <li class="recipe-sidebar__item">
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <img class="recipe-sidebar__thumb"
                                 src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'thumbnail')); ?>"
                                 alt="<?php echo esc_attr(get_the_title()); ?>"
                                 loading="lazy">
                        <?php endif; ?>
                        <span class="recipe-sidebar__link-text"><?php the_title(); ?></span>
                    </a>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </aside>
    <?php
        endif;
        wp_reset_postdata();
    endif;
    ?>
</div><!-- .recipe-layout -->

<!-- 6. Jump to Recipe Button -->
<?php if ($has_recipe) : ?>
    <button class="jump-to-recipe" aria-label="Jump to recipe card">
        &#x2193; Jump to Recipe
    </button>
<?php endif; ?>

<!-- 7. Kapa Wave Divider -->
<div class="kapa-divider kapa-divider--wave" aria-hidden="true"></div>

<?php if ($has_recipe) : ?>
<!-- 8. Recipe Card -->
<section id="recipe-card" class="recipe-card">

    <!-- Card Header (Koa Wood) -->
    <div class="recipe-card__header koa-wood-bg">
        <h2><?php the_title(); ?></h2>
        <div class="recipe-card__actions">
            <button class="recipe-card__action-btn recipe-card__action-btn--save" aria-label="Save recipe">
                &#9825; Save
            </button>
            <button class="recipe-card__action-btn recipe-card__action-btn--print" aria-label="Print recipe" onclick="window.print()">
                &#128424; Print
            </button>
        </div>
    </div>

    <!-- Meta Row -->
    <div class="recipe-card__meta">
        <?php if ($prep_time) : ?>
            <div class="recipe-card__meta-item">
                <span class="recipe-card__meta-label">Prep</span>
                <span class="recipe-card__meta-value"><?php echo esc_html($prep_time); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($cook_time) : ?>
            <div class="recipe-card__meta-item">
                <span class="recipe-card__meta-label">Cook</span>
                <span class="recipe-card__meta-value"><?php echo esc_html($cook_time); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($total_time) : ?>
            <div class="recipe-card__meta-item">
                <span class="recipe-card__meta-label">Total</span>
                <span class="recipe-card__meta-value"><?php echo esc_html($total_time); ?></span>
            </div>
        <?php endif; ?>
        <div class="recipe-card__meta-item">
            <span class="recipe-card__meta-label">Servings</span>
            <div class="recipe-scaler">
                <button class="recipe-scaler__btn recipe-scaler__btn--down" aria-label="Decrease servings">&minus;</button>
                <span class="recipe-scaler__value"><?php echo esc_html($servings); ?></span>
                <button class="recipe-scaler__btn recipe-scaler__btn--up" aria-label="Increase servings">&plus;</button>
            </div>
        </div>
    </div>

    <!-- Two-Column Body -->
    <div class="recipe-card__body">

        <!-- Ingredients Column -->
        <div id="recipe-ingredients" class="recipe-card__ingredients">
            <h3 class="recipe-card__section-title">Ingredients</h3>
            <?php
            foreach ($ingredients_data as $group) :
                if (!empty($group['title'])) :
            ?>
                <h4 class="recipe-card__section-title" style="margin-top: var(--cjc-space-lg);">
                    <?php echo esc_html($group['title']); ?>
                </h4>
            <?php
                endif;
                if (!empty($group['items']) && is_array($group['items'])) :
                    foreach ($group['items'] as $item) :
                        $amount = isset($item['amount']) ? $item['amount'] : '';
                        $unit   = isset($item['unit']) ? $item['unit'] : '';
                        $name   = isset($item['name']) ? $item['name'] : '';
                        $item_notes = isset($item['notes']) ? $item['notes'] : '';
                        $display_text = trim($amount . ' ' . $unit . ' ' . $name);
                        if ($item_notes) {
                            $display_text .= ', ' . $item_notes;
                        }
            ?>
                <label class="recipe-ingredient">
                    <input type="checkbox" class="recipe-ingredient__checkbox">
                    <span class="recipe-ingredient__text"
                          data-amount="<?php echo esc_attr($amount); ?>"
                          data-unit="<?php echo esc_attr($unit); ?>">
                        <strong><?php echo esc_html($amount); ?></strong>
                        <?php echo esc_html(trim($unit . ' ' . $name . ($item_notes ? ', ' . $item_notes : ''))); ?>
                    </span>
                </label>
            <?php
                    endforeach;
                endif;
            endforeach;
            ?>

            <button class="recipe-card__shopping-btn">
                &#128203; Copy Shopping List
            </button>
        </div>

        <!-- Instructions Column -->
        <div id="recipe-instructions" class="recipe-card__instructions">
            <h3 class="recipe-card__section-title">Instructions</h3>
            <?php
            $step_num = 1;
            foreach ($instructions_data as $section) :
                if (!empty($section['title'])) :
            ?>
                <h4 class="recipe-card__section-title" style="margin-top: var(--cjc-space-lg);">
                    <?php echo esc_html($section['title']); ?>
                </h4>
            <?php
                endif;
                if (!empty($section['steps']) && is_array($section['steps'])) :
                    foreach ($section['steps'] as $step) :
                        $step_text = isset($step['text']) ? $step['text'] : '';
            ?>
                <div class="recipe-step">
                    <span class="recipe-step__number"><?php echo esc_html($step_num); ?></span>
                    <p class="recipe-step__text"><?php echo wp_kses_post($step_text); ?></p>
                </div>
            <?php
                        $step_num++;
                    endforeach;
                endif;
            endforeach;
            ?>
        </div>

    </div><!-- .recipe-card__body -->

    <?php if ($notes) : ?>
    <!-- Notes -->
    <div id="recipe-notes" class="recipe-card__notes">
        <h3>Chef's Notes</h3>
        <p><?php echo wp_kses_post($notes); ?></p>
    </div>
    <?php endif; ?>

    <!-- Nutrition Facts (inside recipe card) -->
    <?php if ($has_nutrition) : ?>
    <div class="recipe-card__nutrition" aria-label="Nutrition facts">
        <button class="recipe-nutrition__toggle" aria-expanded="false">
            <span>Nutrition Facts</span>
            <span class="recipe-nutrition__toggle-icon" aria-hidden="true">&#9660;</span>
        </button>
        <div class="recipe-nutrition__content">
            <?php if ($calories) : ?>
                <div class="recipe-nutrition__item">
                    <span class="recipe-nutrition__value"><?php echo esc_html($calories); ?></span>
                    <span class="recipe-nutrition__label">Calories</span>
                </div>
            <?php endif; ?>
            <?php if ($fat) : ?>
                <div class="recipe-nutrition__item">
                    <span class="recipe-nutrition__value"><?php echo esc_html($fat); ?>g</span>
                    <span class="recipe-nutrition__label">Fat</span>
                </div>
            <?php endif; ?>
            <?php if ($protein) : ?>
                <div class="recipe-nutrition__item">
                    <span class="recipe-nutrition__value"><?php echo esc_html($protein); ?>g</span>
                    <span class="recipe-nutrition__label">Protein</span>
                </div>
            <?php endif; ?>
            <?php if ($carbs) : ?>
                <div class="recipe-nutrition__item">
                    <span class="recipe-nutrition__value"><?php echo esc_html($carbs); ?>g</span>
                    <span class="recipe-nutrition__label">Carbs</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</section><!-- .recipe-card -->
<?php endif; ?>

<!-- 9. Kapa Zigzag Divider -->
<div class="kapa-divider kapa-divider--zigzag" aria-hidden="true"></div>

<!-- 11. Related Recipes -->
<?php
if ($category) :
    $related_args = [
        'post_type'      => 'post',
        'posts_per_page' => 3,
        'post__not_in'   => [$post_id],
        'cat'            => $category->term_id,
        'orderby'        => 'rand',
        'post_status'    => 'publish',
    ];
    $related_query = new WP_Query($related_args);

    if ($related_query->have_posts()) :
?>
<section class="related-recipes">
    <h2 class="related-recipes__title">More Island Recipes</h2>
    <div class="related-recipes__grid">
        <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
            <a class="related-card" href="<?php the_permalink(); ?>">
                <?php if (has_post_thumbnail()) : ?>
                    <img class="related-card__image"
                         src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'medium_large')); ?>"
                         alt="<?php echo esc_attr(get_the_title()); ?>">
                <?php endif; ?>
                <div class="related-card__body">
                    <?php
                    $rel_cats = get_the_category();
                    if (!empty($rel_cats)) :
                    ?>
                        <div class="related-card__category"><?php echo esc_html($rel_cats[0]->name); ?></div>
                    <?php endif; ?>
                    <h3 class="related-card__title"><?php the_title(); ?></h3>
                </div>
            </a>
        <?php endwhile; ?>
    </div>
</section>
<?php
    endif;
    wp_reset_postdata();
endif;
?>

<!-- 12. Footer -->
<footer class="cjc-footer lava-rock-bg">
    <div class="cjc-footer__wave-border" aria-hidden="true"></div>
    <p>&copy; <?php echo esc_html(date('Y')); ?>
        <a href="<?php echo esc_url(home_url('/')); ?>">CurtisJCooks.com</a>
        &mdash; Authentic Hawaiian Recipes &amp; Island Flavors
    </p>
</footer>

<?php
    endwhile;
endif;

get_footer();
?>
