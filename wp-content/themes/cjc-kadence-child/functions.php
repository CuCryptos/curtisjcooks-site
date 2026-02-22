<?php
/**
 * CJC Kadence Child Theme — Functions
 *
 * Modern Hawaiian Luxury design system for CurtisJCooks.com.
 */

defined('ABSPATH') || exit;

/* =============================================
   CJC Recipe System
   ============================================= */

// Load Recipe System Classes (skip if plugin is handling it)
if ( ! defined( 'CJC_RECIPE_VERSION' ) ) {
    require_once get_stylesheet_directory() . '/inc/recipe/class-cjc-recipe-post-type.php';
    require_once get_stylesheet_directory() . '/inc/recipe/class-cjc-recipe-meta.php';
    require_once get_stylesheet_directory() . '/inc/recipe/class-cjc-recipe-rest-api.php';
    require_once get_stylesheet_directory() . '/inc/recipe/class-cjc-recipe-schema.php';
    require_once get_stylesheet_directory() . '/inc/recipe/class-cjc-recipe-block.php';
    require_once get_stylesheet_directory() . '/inc/recipe/class-cjc-recipe-migration.php';

    // Initialize Recipe System
    CJC_Recipe_Post_Type::init();
    CJC_Recipe_Meta::init();
    CJC_Recipe_REST_API::init();
    CJC_Recipe_Schema::init();
    CJC_Recipe_Block::init();
    CJC_Recipe_Migration::init();
}

define('CJC_CHILD_VERSION', '1.0.0');
define('CJC_CHILD_DIR', get_stylesheet_directory());
define('CJC_CHILD_URI', get_stylesheet_directory_uri());

/**
 * Font preconnect + homepage hero preload for performance.
 */
add_action('wp_head', function () {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";

    if (is_front_page()) {
        $hero_url = content_url('/uploads/site-images/homepage-hero.png');
        echo '<link rel="preload" as="image" href="' . esc_url($hero_url) . '">' . "\n";
    }
}, 1);

/**
 * Enqueue parent + child styles, fonts, design tokens, and patterns.
 */
add_action('wp_enqueue_scripts', function () {
    // Parent theme
    wp_enqueue_style(
        'kadence-parent',
        get_template_directory_uri() . '/style.css',
        [],
        wp_get_theme('kadence')->get('Version')
    );

    // Google Fonts: Lora (heading), Source Sans 3 (body), Playfair Display (accent)
    // Enqueue separately to avoid WordPress esc_url() breaking multi-family &
    wp_enqueue_style(
        'cjc-font-lora',
        'https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;0,700;1,400&display=swap',
        [],
        null
    );
    wp_enqueue_style(
        'cjc-font-source-sans',
        'https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600&display=swap',
        [],
        null
    );
    wp_enqueue_style(
        'cjc-font-playfair',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap',
        [],
        null
    );

    // Design tokens
    wp_enqueue_style(
        'cjc-tokens',
        CJC_CHILD_URI . '/assets/css/tokens.css',
        ['kadence-parent'],
        CJC_CHILD_VERSION
    );

    // Kapa patterns
    wp_enqueue_style(
        'cjc-patterns',
        CJC_CHILD_URI . '/assets/css/patterns.css',
        ['cjc-tokens'],
        CJC_CHILD_VERSION
    );

    // Component styles
    wp_enqueue_style(
        'cjc-components',
        CJC_CHILD_URI . '/assets/css/components.css',
        ['cjc-tokens', 'cjc-patterns'],
        CJC_CHILD_VERSION
    );

    // Child theme stylesheet
    wp_enqueue_style(
        'cjc-child',
        CJC_CHILD_URI . '/style.css',
        ['kadence-parent', 'cjc-tokens', 'cjc-patterns', 'cjc-components'],
        CJC_CHILD_VERSION
    );
});

/**
 * Enqueue recipe page scripts (single posts only).
 */
add_action('wp_enqueue_scripts', function () {
    if (is_single()) {
        wp_enqueue_script(
            'cjc-recipe-interactive',
            CJC_CHILD_URI . '/assets/js/recipe-interactive.js',
            [],
            CJC_CHILD_VERSION,
            true
        );

        wp_enqueue_script(
            'cjc-scroll-observer',
            CJC_CHILD_URI . '/assets/js/scroll-observer.js',
            [],
            CJC_CHILD_VERSION,
            true
        );

        wp_enqueue_style(
            'cjc-print',
            CJC_CHILD_URI . '/assets/css/print.css',
            [],
            CJC_CHILD_VERSION,
            'print'
        );
    }
});

/**
 * Enqueue homepage scripts (front page only).
 */
add_action('wp_enqueue_scripts', function () {
    if (is_front_page()) {
        wp_enqueue_script(
            'cjc-homepage',
            CJC_CHILD_URI . '/assets/js/homepage.js',
            [],
            CJC_CHILD_VERSION,
            true
        );

        // Pass both logo URLs for scroll-based logo swap
        $dark_logo_id = get_theme_mod('custom_logo');
        $white_logo_id = get_theme_mod('transparent_header_logo');
        wp_localize_script('cjc-homepage', 'cjcLogos', [
            'dark'  => $dark_logo_id ? wp_get_attachment_image_url($dark_logo_id, 'full') : '',
            'white' => $white_logo_id ? wp_get_attachment_image_url($white_logo_id, 'full') : '',
        ]);
    }
});

/* =============================================
   Performance Optimizations
   ============================================= */

/**
 * Remove WordPress emoji scripts and styles.
 * Saves ~10KB and DNS lookup.
 */
add_action('init', function() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

    // Remove TinyMCE emoji plugin
    add_filter('tiny_mce_plugins', function($plugins) {
        return is_array($plugins) ? array_diff($plugins, ['wpemoji']) : [];
    });

    // Remove emoji DNS prefetch
    add_filter('wp_resource_hints', function($urls, $relation_type) {
        if ($relation_type === 'dns-prefetch') {
            $urls = array_filter($urls, function($url) {
                return strpos($url, 'https://s.w.org/images/core/emoji/') === false;
            });
        }
        return $urls;
    }, 10, 2);
});

/**
 * Remove other unnecessary WordPress default scripts.
 */
add_action('wp_enqueue_scripts', function() {
    // Remove WordPress embed script (if not using embeds)
    wp_deregister_script('wp-embed');

    // Remove dashicons on frontend for non-logged-in users
    if (!is_user_logged_in()) {
        wp_deregister_style('dashicons');
    }
}, 100);

/**
 * Remove jQuery migrate (not needed for modern jQuery).
 */
add_action('wp_default_scripts', function($scripts) {
    if (!is_admin() && isset($scripts->registered['jquery'])) {
        $script = $scripts->registered['jquery'];
        if ($script->deps) {
            $script->deps = array_diff($script->deps, ['jquery-migrate']);
        }
    }
});

/**
 * Add defer attribute to non-critical JavaScript.
 * Excludes jQuery and critical scripts from deferring.
 */
add_filter('script_loader_tag', function($tag, $handle, $src) {
    if (is_admin()) {
        return $tag;
    }

    $no_defer = [
        'jquery-core',
        'jquery',
    ];

    if (in_array($handle, $no_defer)) {
        return $tag;
    }

    if (strpos($tag, 'defer') !== false || strpos($tag, 'async') !== false) {
        return $tag;
    }

    return str_replace(' src=', ' defer src=', $tag);
}, 10, 3);

/**
 * Add font-display: swap to Google Fonts to prevent FOIT.
 */
add_filter('style_loader_tag', function($tag, $handle, $src) {
    if (strpos($src, 'fonts.googleapis.com') !== false) {
        if (strpos($src, 'display=') === false) {
            $src_new = add_query_arg('display', 'swap', $src);
            $tag = str_replace($src, $src_new, $tag);
        }
    }
    return $tag;
}, 10, 3);

/**
 * Remove WordPress version from scripts/styles (minor security).
 */
add_filter('style_loader_src', function($src) {
    return $src ? remove_query_arg('ver', $src) : $src;
}, 10, 1);

add_filter('script_loader_src', function($src) {
    return $src ? remove_query_arg('ver', $src) : $src;
}, 10, 1);

/* =============================================
   SEO: Noindex Non-Hawaiian Category Posts
   ============================================= */

/**
 * Add noindex meta tag to posts NOT in main Hawaiian categories.
 * This helps focus search engines on your core content.
 */
add_action('wp_head', function() {
    if (!is_singular('post')) {
        return;
    }

    // Categories that SHOULD be indexed (no noindex)
    $indexed_categories = [
        'island-comfort',
        'island-drinks',
        'poke-seafood',
        'tropical-treats',
        'top-articles',
        'hawaiian-breakfast',
        'pupus-snacks',
        'quick-easy',
        'kitchen-skills',
        'kitchen-essentials',
    ];

    $in_indexed_category = false;
    foreach ($indexed_categories as $category_slug) {
        if (in_category($category_slug)) {
            $in_indexed_category = true;
            break;
        }
    }

    if (!$in_indexed_category) {
        echo '<meta name="robots" content="noindex, follow">' . "\n";
    }
}, 1);

/* =============================================
   Navigation Menus
   ============================================= */

/**
 * Register custom navigation menus.
 */
add_action('after_setup_theme', function() {
    register_nav_menus([
        'cjc-primary'   => 'Primary Navigation',
        'cjc-footer'    => 'Footer Navigation',
    ]);
});

/* =============================================
   Footer Widget Areas
   ============================================= */

/**
 * Register custom footer widget areas.
 */
add_action('widgets_init', function() {
    register_sidebar([
        'name'          => 'Hawaiian Footer - About',
        'id'            => 'hawaiian-footer-about',
        'description'   => 'Footer column 1: About section',
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-widget-title">',
        'after_title'   => '</h4>',
    ]);

    register_sidebar([
        'name'          => 'Hawaiian Footer - Explore',
        'id'            => 'hawaiian-footer-explore',
        'description'   => 'Footer column 2: Category links',
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-widget-title">',
        'after_title'   => '</h4>',
    ]);

    register_sidebar([
        'name'          => 'Hawaiian Footer - Connect',
        'id'            => 'hawaiian-footer-connect',
        'description'   => 'Footer column 3: Social & newsletter',
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-widget-title">',
        'after_title'   => '</h4>',
    ]);
});

/* =============================================
   WP-CLI: Divi Migration Helper
   ============================================= */

if (defined('WP_CLI') && WP_CLI) {
    require_once CJC_CHILD_DIR . '/inc/migration.php';
}

/* =============================================
   Archive Pages — 12 Posts Per Page
   ============================================= */

/**
 * Set archive pages to 12 posts per page for our 3-col grid.
 */
add_action('pre_get_posts', function ($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    if ($query->is_archive()) {
        $query->set('posts_per_page', 12);
    }
});

/**
 * Preload the archive hero image (featured image from latest post in category).
 */
add_action('wp_head', function () {
    if (!is_archive()) {
        return;
    }

    $hero_url = '';

    if (is_category()) {
        $cat = get_queried_object();
        if ($cat) {
            $latest = get_posts([
                'category'       => $cat->term_id,
                'posts_per_page' => 1,
                'post_status'    => 'publish',
                'fields'         => 'ids',
            ]);
            if (!empty($latest)) {
                $hero_url = get_the_post_thumbnail_url($latest[0], 'full');
            }
        }
    } elseif (is_tag()) {
        $tag = get_queried_object();
        if ($tag) {
            $latest = get_posts([
                'tag_id'         => $tag->term_id,
                'posts_per_page' => 1,
                'post_status'    => 'publish',
                'fields'         => 'ids',
            ]);
            if (!empty($latest)) {
                $hero_url = get_the_post_thumbnail_url($latest[0], 'full');
            }
        }
    }

    if ($hero_url) {
        echo '<link rel="preload" as="image" href="' . esc_url($hero_url) . '">' . "\n";
    }
}, 1);
