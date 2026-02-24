<?php
/**
 * CJC Kadence Child Theme — Functions
 *
 * Modern Hawaiian Luxury design system for CurtisJCooks.com.
 */

defined('ABSPATH') || exit;

/* =============================================
   Constants
   ============================================= */

if ( ! defined( 'CJC_CHILD_VERSION' ) ) {
    define('CJC_CHILD_VERSION', '1.5.1');
}
if ( ! defined( 'CJC_CHILD_DIR' ) ) {
    define('CJC_CHILD_DIR', get_stylesheet_directory());
}
if ( ! defined( 'CJC_CHILD_URI' ) ) {
    define('CJC_CHILD_URI', get_stylesheet_directory_uri());
}

/* =============================================
   301 Redirects — Fix 404s from external links
   ============================================= */

add_action('template_redirect', function () {
    $redirects = [
        // Hawaiian 404 → existing content
        '/10-all-time-favorite-classic-dishes-you-need-to-try/' => '/hawaiian-foods-bucket-list/',
        // Non-Hawaiian 404s → homepage
        '/authentic-thai-dessert-recipes-you-can-make-at-home'  => '/',
        '/copycat-taco-bell-beefy-five-layer-burrito-recipe/'   => '/',
        '/ultimate-mac-and-cheese-recipe-how-to-make-the-best-comfort-food' => '/',
        '/risotto-recipe/'          => '/',
        '/homemade-german-recipes/' => '/',
        '/vegan-lasagna-recipe'     => '/',
        '/winter-comfort-foods'     => '/',
        '/1480-2'                   => '/',
        '/recipes-2/'               => '/',
    ];

    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = rtrim($path, '/') . '/';

    foreach ($redirects as $source => $dest) {
        $source_normalized = rtrim($source, '/') . '/';
        if ($path === $source_normalized) {
            wp_redirect(home_url($dest), 301);
            exit;
        }
    }
});

/* =============================================
   CJC Recipe System
   ============================================= */

// Full recipe system (CPT, blocks, migration UI) disabled during deployment.
// Register only the meta fields so single.php can read them and REST API can write them.
add_action('init', function () {
    $text_fields = [
        'prep_time', 'cook_time', 'total_time', 'yield', 'description',
        'author_name', 'keywords', 'category', 'cuisine', 'method', 'diet',
        'notes', 'video_url',
        'calories', 'fat', 'protein', 'carbohydrates', 'sugar', 'sodium',
        'fiber', 'cholesterol', 'saturated_fat', 'unsaturated_fat', 'trans_fat',
        'serving_size',
    ];
    foreach ($text_fields as $field) {
        register_post_meta('post', '_cjc_recipe_' . $field, [
            'show_in_rest'  => true,
            'single'        => true,
            'type'          => 'string',
            'auth_callback' => function () { return current_user_can('edit_posts'); },
        ]);
    }
    // JSON fields (ingredients, instructions)
    register_post_meta('post', '_cjc_recipe_ingredients', [
        'show_in_rest'  => true,
        'single'        => true,
        'type'          => 'string',
        'auth_callback' => function () { return current_user_can('edit_posts'); },
    ]);
    register_post_meta('post', '_cjc_recipe_instructions', [
        'show_in_rest'  => true,
        'single'        => true,
        'type'          => 'string',
        'auth_callback' => function () { return current_user_can('edit_posts'); },
    ]);
    // Numeric fields
    register_post_meta('post', '_cjc_recipe_yield_number', [
        'show_in_rest'  => true,
        'single'        => true,
        'type'          => 'number',
        'auth_callback' => function () { return current_user_can('edit_posts'); },
    ]);
});

/* =============================================
   Google Analytics GA4
   ============================================= */

add_action('wp_head', function () {
    if (is_user_logged_in()) return; // Don't track admin visits
    ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-7C8X9QJD7V"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','G-7C8X9QJD7V');</script>
    <?php
}, 1);

/* =============================================
   Styles & Scripts
   ============================================= */

add_action('wp_head', function () {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";

    if (is_front_page()) {
        $hero_url = file_exists( WP_CONTENT_DIR . '/uploads/site-images/homepage-hero.png' )
            ? content_url('/uploads/site-images/homepage-hero.png')
            : content_url('/uploads/2026/02/homepage-hero.png');
        echo '<link rel="preload" as="image" href="' . esc_url($hero_url) . '">' . "\n";
    } elseif (is_page() && !is_front_page() && has_post_thumbnail()) {
        $hero_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
        if ($hero_url) {
            echo '<link rel="preload" as="image" href="' . esc_url($hero_url) . '">' . "\n";
        }
    }
}, 1);

add_action('wp_enqueue_scripts', function () {
    $kadence_theme = wp_get_theme('kadence');
    $kadence_version = $kadence_theme->exists() ? $kadence_theme->get('Version') : null;

    wp_enqueue_style('kadence-parent', get_template_directory_uri() . '/style.css', [], $kadence_version);

    wp_enqueue_style('cjc-font-lora', 'https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;0,700;1,400&display=swap', [], null);
    wp_enqueue_style('cjc-font-source-sans', 'https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600&display=swap', [], null);
    wp_enqueue_style('cjc-font-playfair', 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap', [], null);

    wp_enqueue_style('cjc-tokens', CJC_CHILD_URI . '/assets/css/tokens.css', ['kadence-parent'], CJC_CHILD_VERSION);
    wp_enqueue_style('cjc-patterns', CJC_CHILD_URI . '/assets/css/patterns.css', ['cjc-tokens'], CJC_CHILD_VERSION);
    wp_enqueue_style('cjc-components', CJC_CHILD_URI . '/assets/css/components.css', ['cjc-tokens', 'cjc-patterns'], CJC_CHILD_VERSION);
    wp_enqueue_style('cjc-child', CJC_CHILD_URI . '/style.css', ['kadence-parent', 'cjc-tokens', 'cjc-patterns', 'cjc-components'], CJC_CHILD_VERSION);
});

add_action('wp_enqueue_scripts', function () {
    if (is_single()) {
        wp_enqueue_script('cjc-recipe-interactive', CJC_CHILD_URI . '/assets/js/recipe-interactive.js', [], CJC_CHILD_VERSION, true);
        wp_enqueue_script('cjc-scroll-observer', CJC_CHILD_URI . '/assets/js/scroll-observer.js', [], CJC_CHILD_VERSION, true);
        wp_enqueue_style('cjc-print', CJC_CHILD_URI . '/assets/css/print.css', [], CJC_CHILD_VERSION, 'print');
    }
});

add_action('wp_enqueue_scripts', function () {
    if (is_front_page()) {
        wp_enqueue_script('cjc-homepage', CJC_CHILD_URI . '/assets/js/homepage.js', [], CJC_CHILD_VERSION, true);

        $dark_logo_id = get_theme_mod('custom_logo');
        $white_logo_id = get_theme_mod('transparent_header_logo');
        wp_localize_script('cjc-homepage', 'cjcLogos', [
            'dark'  => $dark_logo_id ? wp_get_attachment_image_url($dark_logo_id, 'full') : '',
            'white' => $white_logo_id ? wp_get_attachment_image_url($white_logo_id, 'full') : '',
        ]);
    }
});

add_action('wp_enqueue_scripts', function () {
    if (is_page() && !is_front_page()) {
        wp_enqueue_script('cjc-page-scroll', CJC_CHILD_URI . '/assets/js/page-scroll.js', [], CJC_CHILD_VERSION, true);
    }
});

/* =============================================
   Performance Optimizations
   ============================================= */

add_action('init', function() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

    add_filter('tiny_mce_plugins', function($plugins) {
        return is_array($plugins) ? array_diff($plugins, ['wpemoji']) : [];
    });

    add_filter('wp_resource_hints', function($urls, $relation_type) {
        if ($relation_type === 'dns-prefetch') {
            $urls = array_filter($urls, function($url) {
                return strpos($url, 'https://s.w.org/images/core/emoji/') === false;
            });
        }
        return $urls;
    }, 10, 2);
});

add_action('wp_enqueue_scripts', function() {
    wp_deregister_script('wp-embed');
    if (!is_user_logged_in()) {
        wp_deregister_style('dashicons');
    }
}, 100);

add_action('wp_default_scripts', function($scripts) {
    if (!is_admin() && isset($scripts->registered['jquery'])) {
        $script = $scripts->registered['jquery'];
        if ($script->deps) {
            $script->deps = array_diff($script->deps, ['jquery-migrate']);
        }
    }
});

add_filter('script_loader_tag', function($tag, $handle, $src) {
    if (is_admin()) return $tag;
    if (in_array($handle, ['jquery-core', 'jquery'])) return $tag;
    if (strpos($tag, 'defer') !== false || strpos($tag, 'async') !== false) return $tag;
    return str_replace(' src=', ' defer src=', $tag);
}, 10, 3);

add_filter('style_loader_tag', function($tag, $handle, $src) {
    if (strpos($src, 'fonts.googleapis.com') !== false && strpos($src, 'display=') === false) {
        $tag = str_replace($src, add_query_arg('display', 'swap', $src), $tag);
    }
    return $tag;
}, 10, 3);

add_filter('style_loader_src', function($src) {
    if ($src && strpos($src, 'cjc-kadence-child') !== false) return $src;
    return $src ? remove_query_arg('ver', $src) : $src;
}, 10, 1);

add_filter('script_loader_src', function($src) {
    if ($src && strpos($src, 'cjc-kadence-child') !== false) return $src;
    return $src ? remove_query_arg('ver', $src) : $src;
}, 10, 1);

/* =============================================
   SEO: Noindex Non-Hawaiian Category Posts
   Uses Rank Math's filter to modify its robots tag
   instead of adding a duplicate meta tag.
   ============================================= */

add_filter('rank_math/frontend/robots', function ($robots) {
    if (!is_singular('post')) return $robots;

    $indexed_categories = [
        'island-comfort', 'island-drinks', 'poke-seafood', 'tropical-treats',
        'top-articles', 'hawaiian-breakfast', 'pupus-snacks', 'quick-easy',
        'kitchen-skills', 'kitchen-essentials',
    ];

    $in_indexed = false;
    foreach ($indexed_categories as $slug) {
        if (in_category($slug)) { $in_indexed = true; break; }
    }

    if (!$in_indexed) {
        $robots['index'] = 'noindex';
    }
    return $robots;
});

/* =============================================
   SEO: 301 Redirects for Old/Deleted URLs
   Preserves link equity from Google-indexed pages
   that no longer exist.
   ============================================= */

add_action('template_redirect', function () {
    $redirects = [
        '/1480-2/'           => '/kalua-pig-oven-roasted-hawaiian/',
        '/contact-2/'        => '/about/',
        '/contact/'          => '/about/',
        '/blog-2/'           => '/',
        '/blog/'             => '/',
        '/shop/'             => '/',
        '/copycat-taco-bell-beefy-five-layer-burrito-recipe/' => '/',
        '/make-this-easy-pad-thai-recipe-in-just-30-minutes/' => '/',
    ];

    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = rtrim($path, '/') . '/';

    if (isset($redirects[$path])) {
        wp_redirect(home_url($redirects[$path]), 301);
        exit;
    }
});

/* =============================================
   SEO: IndexNow Key File
   Creates the physical key file in the web root so
   nginx can serve it directly (static .txt files
   bypass WordPress on this host).
   ============================================= */

add_action('init', function () {
    $key = 'bdd2830e8bac489f98f79bb58afcdf77';
    $file = ABSPATH . $key . '.txt';
    if (!file_exists($file)) {
        @file_put_contents($file, $key);
    }
}, 1);

/* =============================================
   Navigation Menus
   ============================================= */

add_action('after_setup_theme', function() {
    register_nav_menus([
        'cjc-primary' => 'Primary Navigation',
        'cjc-footer'  => 'Footer Navigation',
    ]);
});

/* =============================================
   Footer Widget Areas
   ============================================= */

add_action('widgets_init', function() {
    $defaults = [
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-widget-title">',
        'after_title'   => '</h4>',
    ];
    register_sidebar(array_merge($defaults, ['name' => 'Hawaiian Footer - About', 'id' => 'hawaiian-footer-about', 'description' => 'Footer column 1']));
    register_sidebar(array_merge($defaults, ['name' => 'Hawaiian Footer - Explore', 'id' => 'hawaiian-footer-explore', 'description' => 'Footer column 2']));
    register_sidebar(array_merge($defaults, ['name' => 'Hawaiian Footer - Connect', 'id' => 'hawaiian-footer-connect', 'description' => 'Footer column 3']));
});

/* =============================================
   WP-CLI: Divi Migration Helper
   ============================================= */

if (defined('WP_CLI') && WP_CLI) {
    require_once CJC_CHILD_DIR . '/inc/migration.php';
}

/* =============================================
   Kadence Layout Overrides
   - Disable hero title on pages & posts (our templates own the title area)
   - Enable transparent header on ALL page types (pages, posts, archives)
     Kadence controls this via $layout['transparent'] in kadence_post_layout.
   ============================================= */

add_filter('kadence_post_layout', function ($layout) {
    if (is_page() || is_singular('post')) {
        $layout['title'] = 'normal';
    }
    $layout['transparent'] = 'enable';
    return $layout;
});

/* =============================================
   Archive Pages — 12 Posts Per Page
   ============================================= */

add_action('pre_get_posts', function ($query) {
    if (is_admin() || !$query->is_main_query()) return;
    if ($query->is_archive()) {
        $query->set('posts_per_page', 12);
    }
});

add_action('wp_head', function () {
    if (!is_archive()) return;

    $hero_url = '';
    if (is_category()) {
        $cat = get_queried_object();
        if ($cat) {
            $latest = get_posts(['category' => $cat->term_id, 'posts_per_page' => 1, 'post_status' => 'publish', 'fields' => 'ids']);
            if (!empty($latest)) $hero_url = get_the_post_thumbnail_url($latest[0], 'full');
        }
    } elseif (is_tag()) {
        $tag = get_queried_object();
        if ($tag) {
            $latest = get_posts(['tag_id' => $tag->term_id, 'posts_per_page' => 1, 'post_status' => 'publish', 'fields' => 'ids']);
            if (!empty($latest)) $hero_url = get_the_post_thumbnail_url($latest[0], 'full');
        }
    }

    if ($hero_url) {
        echo '<link rel="preload" as="image" href="' . esc_url($hero_url) . '">' . "\n";
    }
}, 1);

// Redirect all 404s to homepage (SEO: covers deleted posts, old slugs, ?p= IDs)
add_action('template_redirect', function () {
    if (is_404()) {
        wp_redirect(home_url('/'), 301);
        exit;
    }
});
