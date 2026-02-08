<?php
/**
 * Template Name: Recipes Page
 * Custom recipes archive page with Hawaiian hero
 */

get_header();

// Get hero image - Hawaiian themed fallback
$hero_image = function_exists('curtisjcooks_get_site_image')
    ? curtisjcooks_get_site_image('homepage-hero')
    : '';
// Use Hawaiian poke bowl as fallback if no custom image
if (!$hero_image) {
    $hero_image = 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=1200'; // Poke bowl
}

// Pagination
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

// Get all recipes
$recipes = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 12,
    'paged' => $paged,
    'post_status' => 'publish',
]);
?>

<style>
/* Recipes Page Styles */
.cjc-recipes-page-hero {
    position: relative;
    height: 400px;
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cjc-recipes-page-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.8) 0%, rgba(234, 88, 12, 0.9) 100%);
}

.cjc-recipes-hero-content {
    position: relative;
    z-index: 10;
    text-align: center;
    color: white;
    padding: 20px;
}

.cjc-recipes-hero-content h1 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 3.5rem;
    margin: 0 0 16px 0;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.cjc-recipes-hero-content p {
    font-size: 1.25rem;
    opacity: 0.95;
    max-width: 600px;
    margin: 0 auto;
}

.cjc-recipes-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 60px 24px;
}

.cjc-recipes-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
}

.cjc-recipe-card-link {
    text-decoration: none;
    display: block;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.cjc-recipe-card-link:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.cjc-recipe-card-image {
    aspect-ratio: 4/3;
    overflow: hidden;
}

.cjc-recipe-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.cjc-recipe-card-link:hover .cjc-recipe-card-image img {
    transform: scale(1.08);
}

.cjc-recipe-card-body {
    padding: 20px;
}

.cjc-recipe-card-category {
    display: inline-block;
    background: #fff7ed;
    color: #ea580c;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}

.cjc-recipe-card-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.25rem;
    color: #1f2937;
    margin: 0 0 8px 0;
    line-height: 1.3;
}

.cjc-recipe-card-excerpt {
    font-size: 0.9rem;
    color: #6b7280;
    line-height: 1.5;
    margin: 0;
}

.cjc-pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 48px;
}

.cjc-pagination a,
.cjc-pagination span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    height: 44px;
    padding: 0 16px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.cjc-pagination a {
    background: white;
    color: #374151;
    border: 2px solid #e5e7eb;
}

.cjc-pagination a:hover {
    background: #f97316;
    color: white;
    border-color: #f97316;
}

.cjc-pagination .current {
    background: #f97316;
    color: white;
    border: 2px solid #f97316;
}

.cjc-no-recipes {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

@media (max-width: 1024px) {
    .cjc-recipes-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .cjc-recipes-grid {
        grid-template-columns: 1fr;
    }

    .cjc-recipes-hero-content h1 {
        font-size: 2.5rem;
    }

    .cjc-recipes-page-hero {
        height: 300px;
    }
}
</style>

<div id="main-content">

    <!-- Hero Section -->
    <div class="cjc-recipes-page-hero" style="background-image: url('<?php echo esc_url($hero_image); ?>');">
        <div class="cjc-recipes-hero-content">
            <h1>🌺 Hawaiian Recipes</h1>
            <p>Authentic island flavors from my family's kitchen to yours. Ono grindz guaranteed!</p>
        </div>
    </div>

    <!-- Recipes Grid -->
    <div class="cjc-recipes-container">
        <?php if ($recipes->have_posts()): ?>
            <div class="cjc-recipes-grid">
                <?php while ($recipes->have_posts()): $recipes->the_post(); ?>
                    <?php
                    $categories = get_the_category();
                    $category_name = !empty($categories) ? $categories[0]->name : 'Recipe';
                    $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'large')
                        ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600';
                    ?>
                    <a href="<?php the_permalink(); ?>" class="cjc-recipe-card-link">
                        <div class="cjc-recipe-card-image">
                            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title_attribute(); ?>">
                        </div>
                        <div class="cjc-recipe-card-body">
                            <span class="cjc-recipe-card-category"><?php echo esc_html($category_name); ?></span>
                            <h2 class="cjc-recipe-card-title"><?php the_title(); ?></h2>
                            <p class="cjc-recipe-card-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <div class="cjc-pagination">
                <?php
                echo paginate_links([
                    'total' => $recipes->max_num_pages,
                    'current' => $paged,
                    'prev_text' => '← Prev',
                    'next_text' => 'Next →',
                ]);
                ?>
            </div>

            <?php wp_reset_postdata(); ?>
        <?php else: ?>
            <div class="cjc-no-recipes">
                <h2>No recipes found</h2>
                <p>Check back soon for delicious Hawaiian recipes!</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php get_footer(); ?>
