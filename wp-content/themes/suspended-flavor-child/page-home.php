<?php
/**
 * Template Name: CurtisJCooks Homepage
 *
 * React-powered homepage template.
 */

get_header();

// Check if build files exist
$build_dir = get_stylesheet_directory() . '/build/';
$asset_file = $build_dir . 'index.asset.php';
$js_file = $build_dir . 'index.js';
$css_file = $build_dir . 'index.css';

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
?>

<!-- Debug Info -->
<div style="background: #fffbe6; padding: 20px; margin: 20px; border: 2px solid #f5c542; border-radius: 8px;">
    <h3>Debug Info:</h3>
    <ul>
        <li>Asset file exists: <?php echo file_exists($asset_file) ? '✅ Yes' : '❌ No'; ?></li>
        <li>JS file exists: <?php echo file_exists($js_file) ? '✅ Yes' : '❌ No'; ?></li>
        <li>CSS file exists: <?php echo file_exists($css_file) ? '✅ Yes' : '❌ No'; ?></li>
        <li>Template: page-home.php ✅</li>
        <li>Recipes found: <?php echo count($recipes); ?></li>
    </ul>
    <p><strong>Check browser console (F12) for JavaScript errors.</strong></p>
</div>

<div id="main-content">
    <div id="cjc-react-root">
        <p style="text-align: center; padding: 40px;">Loading React app...</p>
    </div>
</div>

<script>
    window.cjcData = <?php echo json_encode($react_data); ?>;
    console.log('CJC Data loaded:', window.cjcData);
</script>

<?php
// Manually enqueue if not already done
$asset = file_exists($asset_file) ? include($asset_file) : ['dependencies' => [], 'version' => '1.0.0'];
?>

<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/build/index.css?v=<?php echo $asset['version']; ?>">
<script src="<?php echo get_stylesheet_directory_uri(); ?>/build/index.js?v=<?php echo $asset['version']; ?>" defer></script>

<?php get_footer(); ?>
