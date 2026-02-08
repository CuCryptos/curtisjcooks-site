<?php
/**
 * Custom 404 Page Template
 */

get_header();

$image_url = function_exists('curtisjcooks_get_site_image') ? curtisjcooks_get_site_image('404-page-image') : '';
?>

<div id="main-content">
    <article id="post-0" class="post error404 not-found">
        <div class="entry-content">
            <div class="error-404-content">
                <?php if ($image_url): ?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="Page not found" class="error-404-image">
                <?php endif; ?>

                <h1>Uh oh! This page got lost at sea.</h1>
                <p>The page you're looking for might have been moved, deleted, or perhaps never existed.</p>

                <p><a href="<?php echo home_url('/'); ?>" class="button-404">Back to Homepage</a></p>

                <div class="error-404-search">
                    <p>Or try searching for what you need:</p>
                    <?php get_search_form(); ?>
                </div>

                <div class="error-404-suggestions">
                    <h3>Popular Recipes</h3>
                    <ul>
                        <?php
                        $popular = new WP_Query([
                            'posts_per_page' => 5,
                            'post_status' => 'publish',
                            'orderby' => 'comment_count',
                            'order' => 'DESC',
                        ]);
                        while ($popular->have_posts()): $popular->the_post();
                        ?>
                            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </ul>
                </div>
            </div>
        </div>
    </article>
</div>

<?php get_footer(); ?>
