<?php
/**
 * Front Page Template
 * React-powered homepage
 */

get_header();

// Prepare data for React
$recipes = [];
$recent_posts = new WP_Query([
    'posts_per_page' => 6,
    'post_status' => 'publish',
]);

while ($recent_posts->have_posts()) {
    $recent_posts->the_post();
    $categories = get_the_category();
    $recipes[] = [
        'title' => get_the_title(),
        'link' => get_permalink(),
        'image' => get_the_post_thumbnail_url(get_the_ID(), 'large') ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600',
        'category' => !empty($categories) ? $categories[0]->name : 'Recipe',
        'time' => '20 min',
    ];
}
wp_reset_postdata();

// Get site images
$hero_image = function_exists('curtisjcooks_get_site_image') ? curtisjcooks_get_site_image('homepage-hero') : '';

$react_data = [
    'recipes' => $recipes,
    'images' => [
        'hero' => $hero_image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800',
    ],
    'stats' => [
        'recipes' => 50,
        'readers' => 12000,
        'rating' => 4.9,
    ],
    'siteUrl' => home_url(),
];

$asset_file = get_stylesheet_directory() . '/build/index.asset.php';
$asset = file_exists($asset_file) ? include($asset_file) : ['dependencies' => [], 'version' => '1.0.0'];
?>

<style>
/* Force scrolling - override Divi */
html,
body,
body.et-fb,
body.et_divi_theme,
#page-container,
#et-main-area,
#main-content,
#cjc-react-root,
.cjc-app {
    overflow: visible !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    height: auto !important;
    max-height: none !important;
    position: relative !important;
}
body {
    overflow: auto !important;
    min-height: 100vh;
}
.cjc-floating-particles {
    pointer-events: none;
    z-index: 0;
}
</style>

<script>
// Force scroll on body
document.addEventListener('DOMContentLoaded', function() {
    document.body.style.overflow = 'auto';
    document.documentElement.style.overflow = 'auto';
});
</script>

<div id="main-content">
    <div id="cjc-react-root">
        <p style="text-align: center; padding: 100px 20px; font-size: 1.2rem; color: #666;">Loading...</p>
    </div>
</div>

<script>
    window.cjcData = <?php echo json_encode($react_data); ?>;
</script>

<!-- Load React 18 from CDN -->
<script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
<script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>

<!-- Load our app CSS -->
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/build/index.css?v=<?php echo $asset['version']; ?>">

<!-- Load our app JS -->
<script src="<?php echo get_stylesheet_directory_uri(); ?>/build/index.js?v=<?php echo $asset['version']; ?>"></script>

<?php get_footer(); ?>
