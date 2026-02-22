<?php
/**
 * CJC Kadence Child — Archive / Category / Tag Template
 *
 * Modern Hawaiian Luxury archive page with immersive hero,
 * sub-category navigation pills, featured-card grid, and pagination.
 *
 * @package CJC_Kadence_Child
 */

defined('ABSPATH') || exit;

get_header();

// --- Archive context ---
$queried       = get_queried_object();
$is_category   = is_category();
$is_tag        = is_tag();
$archive_title = get_the_archive_title();
$archive_desc  = get_the_archive_description();
$total_posts   = $wp_query->found_posts;

// Strip "Category: " / "Tag: " prefix added by WordPress
$archive_title = preg_replace('/^(Category|Tag|Archives?):\s*/i', '', $archive_title);

// --- Hero image: featured image from most recent post in this archive ---
$hero_img_url = '';
if ($is_category && $queried) {
    $hero_query = new WP_Query([
        'category__in'   => [$queried->term_id],
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);
    if (!empty($hero_query->posts)) {
        $hero_img_url = get_the_post_thumbnail_url($hero_query->posts[0], 'full');
    }
    wp_reset_postdata();
} elseif ($is_tag && $queried) {
    $hero_query = new WP_Query([
        'tag_id'         => $queried->term_id,
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);
    if (!empty($hero_query->posts)) {
        $hero_img_url = get_the_post_thumbnail_url($hero_query->posts[0], 'full');
    }
    wp_reset_postdata();
}

// Count label
$count_label = '';
if ($total_posts > 0) {
    $count_label = $total_posts . ' ' . ($is_category ? esc_html($archive_title) : '') . ' Recipe' . ($total_posts !== 1 ? 's' : '');
    if (!$is_category) {
        $count_label = $total_posts . ' Recipe' . ($total_posts !== 1 ? 's' : '');
    }
}
?>

<!-- 1. Archive Hero -->
<section class="archive-hero <?php echo $hero_img_url ? '' : 'archive-hero--no-image'; ?>">
    <?php if ($hero_img_url) : ?>
        <img class="archive-hero__image"
             src="<?php echo esc_url($hero_img_url); ?>"
             alt="<?php echo esc_attr($archive_title); ?>"
             loading="eager">
    <?php endif; ?>
    <div class="archive-hero__overlay" aria-hidden="true"></div>
    <div class="archive-hero__content">
        <?php if ($count_label) : ?>
            <span class="archive-hero__count"><?php echo esc_html($count_label); ?></span>
        <?php endif; ?>
        <h1 class="archive-hero__title"><?php echo esc_html($archive_title); ?></h1>
        <?php if ($archive_desc) : ?>
            <p class="archive-hero__description"><?php echo wp_kses_post($archive_desc); ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- 2. Sub-Category Navigation Pills (categories only) -->
<?php if ($is_category && $queried) :
    $parent_id   = $queried->parent;
    $children    = get_categories(['parent' => $queried->term_id, 'hide_empty' => true]);
    $is_parent   = !empty($children);

    if ($is_parent) :
        // Parent category: show child pills
?>
<nav class="archive-pills" aria-label="Sub-categories">
    <?php foreach ($children as $child) : ?>
        <a class="archive-pill" href="<?php echo esc_url(get_category_link($child->term_id)); ?>">
            <?php echo esc_html($child->name); ?>
            <span class="archive-pill__count">(<?php echo esc_html($child->count); ?>)</span>
        </a>
    <?php endforeach; ?>
</nav>
<?php
    elseif ($parent_id) :
        // Child category: show parent back-link + sibling pills
        $parent_cat = get_category($parent_id);
        $siblings   = get_categories(['parent' => $parent_id, 'hide_empty' => true, 'exclude' => $queried->term_id]);
?>
<nav class="archive-pills" aria-label="Related categories">
    <a class="archive-pill archive-pill--parent" href="<?php echo esc_url(get_category_link($parent_id)); ?>">
        &larr; All <?php echo esc_html($parent_cat->name); ?>
    </a>
    <?php foreach ($siblings as $sibling) : ?>
        <a class="archive-pill" href="<?php echo esc_url(get_category_link($sibling->term_id)); ?>">
            <?php echo esc_html($sibling->name); ?>
            <span class="archive-pill__count">(<?php echo esc_html($sibling->count); ?>)</span>
        </a>
    <?php endforeach; ?>
</nav>
<?php
    endif;
endif; ?>

<!-- 3. Kapa Triangle Divider -->
<div class="kapa-divider kapa-divider--triangle" aria-hidden="true"></div>

<!-- 4. Post Grid -->
<?php if (have_posts()) : ?>
<div class="archive-grid">
    <?php while (have_posts()) : the_post(); ?>
        <a class="featured-card" href="<?php the_permalink(); ?>">
            <?php if (has_post_thumbnail()) : ?>
                <img class="featured-card__image"
                     src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'medium_large')); ?>"
                     alt="<?php echo esc_attr(get_the_title()); ?>"
                     loading="lazy">
            <?php else : ?>
                <div class="featured-card__placeholder">
                    <span class="featured-card__placeholder-icon" aria-hidden="true">&#127860;</span>
                </div>
            <?php endif; ?>
            <div class="featured-card__body">
                <?php
                $cats = get_the_category();
                if (!empty($cats)) :
                ?>
                    <div class="featured-card__category"><?php echo esc_html($cats[0]->name); ?></div>
                <?php endif; ?>
                <h2 class="featured-card__title"><?php the_title(); ?></h2>
                <?php if (has_excerpt()) : ?>
                    <p class="featured-card__excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
                <?php endif; ?>
            </div>
        </a>
    <?php endwhile; ?>
</div>

<!-- 5. Pagination -->
<nav class="archive-pagination" aria-label="Archive pagination">
    <?php
    the_posts_pagination([
        'mid_size'  => 2,
        'prev_text' => '&larr; Prev',
        'next_text' => 'Next &rarr;',
    ]);
    ?>
</nav>
<?php else : ?>
<div class="archive-grid" style="text-align: center; padding: var(--cjc-space-4xl) var(--cjc-space-lg);">
    <p style="font-family: var(--cjc-font-heading); font-size: var(--cjc-text-h3); color: var(--cjc-reef-gray);">
        No recipes found. Check back soon!
    </p>
</div>
<?php endif; ?>

<!-- 6. Footer -->
<footer class="cjc-footer lava-rock-bg">
    <div class="cjc-footer__wave-border" aria-hidden="true"></div>
    <p>&copy; <?php echo esc_html(date('Y')); ?>
        <a href="<?php echo esc_url(home_url('/')); ?>">CurtisJCooks.com</a>
        &mdash; Authentic Hawaiian Recipes &amp; Island Flavors
    </p>
</footer>

<?php get_footer(); ?>
