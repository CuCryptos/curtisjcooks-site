<?php
/**
 * Single Post Template
 * Custom recipe post layout with proper scrolling
 */

get_header();

if (have_posts()): while (have_posts()): the_post();

$thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'full')
    ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=1200';
$categories = get_the_category();
$category_name = !empty($categories) ? $categories[0]->name : 'Recipe';
$category_link = !empty($categories) ? get_category_link($categories[0]->term_id) : '#';
?>

<style>
/* Single Recipe Post Styles */
.cjc-single-hero {
    position: relative;
    height: 500px;
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: flex-end;
}

.cjc-single-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.6) 40%, rgba(0,0,0,0.3) 70%, transparent 100%);
}

.cjc-single-hero-content {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
    padding: 40px 24px;
    color: white;
}

.cjc-single-category {
    display: inline-block;
    background: #f97316;
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-decoration: none;
    margin-bottom: 16px;
    transition: background 0.3s ease;
}

.cjc-single-category:hover {
    background: #ea580c;
    color: white;
}

.cjc-single-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 3rem;
    margin: 0 0 16px 0;
    line-height: 1.2;
    color: #ffffff !important;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.cjc-single-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    font-size: 0.95rem;
    opacity: 0.9;
}

.cjc-single-meta span {
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Content Area */
.cjc-single-content-wrapper {
    max-width: 800px;
    margin: 0 auto;
    padding: 60px 24px;
}

.cjc-single-content {
    font-size: 1.125rem;
    line-height: 1.8;
    color: #374151;
}

.cjc-single-content p {
    margin-bottom: 24px;
}

.cjc-single-content h2 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 2rem;
    color: #1f2937;
    margin: 48px 0 24px 0;
}

.cjc-single-content h3 {
    font-size: 1.5rem;
    color: #1f2937;
    margin: 36px 0 16px 0;
}

.cjc-single-content ul,
.cjc-single-content ol {
    margin-bottom: 24px;
    padding-left: 24px;
}

.cjc-single-content li {
    margin-bottom: 8px;
}

.cjc-single-content img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    margin: 32px 0;
}

.cjc-single-content blockquote {
    background: linear-gradient(135deg, #fff7ed 0%, #fef3c7 100%);
    border-left: 4px solid #f97316;
    padding: 24px;
    margin: 32px 0;
    border-radius: 0 12px 12px 0;
    font-style: italic;
    color: #92400e;
}

.cjc-single-content blockquote p:last-child {
    margin-bottom: 0;
}

/* Author Box */
.cjc-author-box {
    display: flex;
    gap: 24px;
    align-items: center;
    background: #f9fafb;
    padding: 32px;
    border-radius: 16px;
    margin-top: 48px;
}

.cjc-author-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #f97316;
}

.cjc-author-info h4 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.25rem;
    color: #1f2937;
    margin: 0 0 8px 0;
}

.cjc-author-info p {
    color: #6b7280;
    margin: 0;
    font-size: 0.95rem;
}

/* Related Posts */
.cjc-related-posts {
    background: #f9fafb;
    padding: 60px 24px;
    margin-top: 60px;
}

.cjc-related-posts h3 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 2rem;
    color: #1f2937;
    text-align: center;
    margin: 0 0 40px 0;
}

.cjc-related-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    max-width: 1000px;
    margin: 0 auto;
}

.cjc-related-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    text-decoration: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.cjc-related-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.cjc-related-card img {
    width: 100%;
    aspect-ratio: 16/10;
    object-fit: cover;
}

.cjc-related-card-body {
    padding: 16px;
}

.cjc-related-card-title {
    font-weight: 600;
    color: #1f2937;
    font-size: 1rem;
    margin: 0;
    line-height: 1.4;
}

/* Responsive */
@media (max-width: 768px) {
    .cjc-single-hero {
        height: 400px;
    }

    .cjc-single-title {
        font-size: 2rem;
    }

    .cjc-author-box {
        flex-direction: column;
        text-align: center;
    }

    .cjc-related-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div id="main-content">

    <!-- Hero -->
    <div class="cjc-single-hero" style="background-image: url('<?php echo esc_url($thumbnail); ?>');">
        <div class="cjc-single-hero-content">
            <a href="<?php echo esc_url($category_link); ?>" class="cjc-single-category"><?php echo esc_html($category_name); ?></a>
            <h1 class="cjc-single-title"><?php the_title(); ?></h1>
            <div class="cjc-single-meta">
                <span>📅 <?php echo get_the_date(); ?></span>
                <span>👤 <?php the_author(); ?></span>
                <span>⏱️ <?php echo get_post_meta(get_the_ID(), 'prep_time', true) ?: '20 min'; ?></span>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="cjc-single-content-wrapper">
        <article class="cjc-single-content">
            <?php the_content(); ?>
        </article>

        <!-- Author Box -->
        <div class="cjc-author-box">
            <?php
            $author_image = function_exists('curtisjcooks_get_site_image')
                ? curtisjcooks_get_site_image('author-photo-curtis')
                : get_avatar_url(get_the_author_meta('ID'), ['size' => 160]);
            ?>
            <img src="<?php echo esc_url($author_image); ?>" alt="<?php the_author(); ?>" class="cjc-author-avatar">
            <div class="cjc-author-info">
                <h4>About <?php the_author(); ?></h4>
                <p>Sharing authentic Hawaiian recipes and island cooking traditions. Mahalo for visiting!</p>
            </div>
        </div>
    </div>

    <!-- Related Posts -->
    <?php
    $related = new WP_Query([
        'posts_per_page' => 3,
        'post__not_in' => [get_the_ID()],
        'category__in' => wp_get_post_categories(get_the_ID()),
    ]);

    if ($related->have_posts()):
    ?>
    <div class="cjc-related-posts">
        <h3>You Might Also Like</h3>
        <div class="cjc-related-grid">
            <?php while ($related->have_posts()): $related->the_post(); ?>
                <a href="<?php the_permalink(); ?>" class="cjc-related-card">
                    <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400'; ?>" alt="<?php the_title_attribute(); ?>">
                    <div class="cjc-related-card-body">
                        <h4 class="cjc-related-card-title"><?php the_title(); ?></h4>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
    <?php
    wp_reset_postdata();
    endif;
    ?>

</div>

<?php
endwhile; endif;
get_footer();
?>
