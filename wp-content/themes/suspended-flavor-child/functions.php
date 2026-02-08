<?php

/* =============================================
   CJC Recipe System
   ============================================= */

// Load Recipe System Classes
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

/* =============================================
   Hide Divi Header (Using Custom Header Instead)
   ============================================= */

// Remove Divi header via output buffering
add_action('template_redirect', function() {
    ob_start(function($html) {
        // Remove Divi's main header
        $html = preg_replace('/<header id="main-header"[^>]*>.*?<\/header>\s*<!--\s*#main-header\s*-->/s', '', $html);
        // Remove top header if exists
        $html = preg_replace('/<header id="top-header"[^>]*>.*?<\/header>/s', '', $html);
        return $html;
    });
});

// Add early CSS as backup
add_action('wp_head', function() {
    ?>
    <style>
    /* HIDE DIVI HEADER - backup CSS */
    #main-header,
    #top-header,
    header#main-header {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        overflow: hidden !important;
    }

    /* Remove Divi page padding, add space for custom nav */
    .et_fixed_nav #page-container,
    .et_fixed_nav.et_show_nav #page-container {
        padding-top: 70px !important;
    }
    @media (max-width: 980px) {
        .et_fixed_nav #page-container,
        .et_fixed_nav.et_show_nav #page-container {
            padding-top: 60px !important;
        }
    }
    </style>
    <?php
}, 1);

/* Scroll fix moved to CSS only - see style.css */

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // Google Fonts: Playfair Display (700) and Lato (400, 600)
    wp_enqueue_style(
        'google-fonts-hawaiian',
        'https://fonts.googleapis.com/css2?family=Lato:wght@400;600&family=Playfair+Display:wght@700&display=swap',
        [],
        null
    );

    // Load React app on homepage template
    if (is_page_template('page-home.php')) {
        $asset_file = get_stylesheet_directory() . '/build/index.asset.php';

        if (file_exists($asset_file)) {
            $asset = include $asset_file;

            wp_enqueue_script(
                'cjc-react-app',
                get_stylesheet_directory_uri() . '/build/index.js',
                $asset['dependencies'],
                $asset['version'],
                true
            );

            wp_enqueue_style(
                'cjc-react-app-style',
                get_stylesheet_directory_uri() . '/build/index.css',
                [],
                $asset['version']
            );
        }
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
    // Don't defer in admin or for logged-in users editing
    if (is_admin()) {
        return $tag;
    }

    // Scripts that should NOT be deferred (critical for page render)
    $no_defer = [
        'jquery-core',
        'jquery',
        'et-builder-modules-global-functions-script',
        'divi-custom-script',
    ];

    if (in_array($handle, $no_defer)) {
        return $tag;
    }

    // Don't double-add defer
    if (strpos($tag, 'defer') !== false || strpos($tag, 'async') !== false) {
        return $tag;
    }

    return str_replace(' src=', ' defer src=', $tag);
}, 10, 3);

/**
 * Preconnect to Google Fonts and optimize font loading.
 */
add_action('wp_head', function() {
    ?>
    <!-- Preconnect to Google Fonts for faster font loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php
}, 1);

/**
 * Add font-display: swap to Google Fonts to prevent FOIT.
 */
add_filter('style_loader_tag', function($tag, $handle, $src) {
    // Add font-display parameter to Google Fonts URLs
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
   Custom Navigation Header
   ============================================= */

/**
 * Register custom navigation menu.
 */
add_action('after_setup_theme', function() {
    register_nav_menus([
        'cjc-primary' => 'Primary Navigation',
    ]);
});

/**
 * Render custom navigation header via wp_body_open hook.
 * This replaces the hidden Divi header on all pages.
 */
add_action('wp_body_open', function() {
    $home_url = esc_url(home_url('/'));
    ?>
    <nav id="cjc-nav" class="cjc-nav" role="navigation" aria-label="Main navigation">
        <div class="cjc-nav-inner">
            <a href="<?php echo $home_url; ?>" class="cjc-nav-logo">CurtisJCooks</a>

            <div class="cjc-nav-links" id="cjc-nav-links">
                <div class="cjc-nav-dropdown">
                    <a href="<?php echo esc_url(home_url('/category/recipes/')); ?>" class="cjc-nav-link cjc-nav-has-dropdown">Recipes</a>
                    <div class="cjc-nav-dropdown-menu">
                        <a href="<?php echo esc_url(home_url('/category/island-comfort/')); ?>">Island Comfort</a>
                        <a href="<?php echo esc_url(home_url('/category/poke-seafood/')); ?>">Poke &amp; Seafood</a>
                        <a href="<?php echo esc_url(home_url('/category/hawaiian-breakfast/')); ?>">Hawaiian Breakfast</a>
                        <a href="<?php echo esc_url(home_url('/category/tropical-treats/')); ?>">Tropical Treats</a>
                        <a href="<?php echo esc_url(home_url('/category/island-drinks/')); ?>">Island Drinks</a>
                        <a href="<?php echo esc_url(home_url('/category/pupus-snacks/')); ?>">Pupus &amp; Snacks</a>
                        <a href="<?php echo esc_url(home_url('/category/quick-easy/')); ?>">Quick &amp; Easy</a>
                    </div>
                </div>
                <a href="<?php echo esc_url(home_url('/category/kitchen-skills/')); ?>" class="cjc-nav-link">Kitchen Skills</a>
                <a href="<?php echo esc_url(home_url('/category/top-articles/')); ?>" class="cjc-nav-link">Culture</a>
                <a href="<?php echo esc_url(home_url('/category/kitchen-essentials/')); ?>" class="cjc-nav-link">Kitchen Gear</a>
            </div>

            <div class="cjc-nav-actions">
                <button class="cjc-nav-search-btn" id="cjc-nav-search-btn" aria-label="Search recipes">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </button>
                <button class="cjc-nav-hamburger" id="cjc-nav-hamburger" aria-label="Toggle menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>

        <div class="cjc-nav-search" id="cjc-nav-search">
            <form role="search" method="get" action="<?php echo $home_url; ?>">
                <input type="search" name="s" placeholder="Search recipes..." value="<?php echo esc_attr(get_search_query()); ?>" aria-label="Search recipes">
            </form>
        </div>
    </nav>
    <?php
}, 5);

/**
 * Add navigation JavaScript in footer.
 */
add_action('wp_footer', function() {
    ?>
    <script>
    (function() {
        var hamburger = document.getElementById('cjc-nav-hamburger');
        var navLinks = document.getElementById('cjc-nav-links');
        var searchBtn = document.getElementById('cjc-nav-search-btn');
        var searchBar = document.getElementById('cjc-nav-search');
        var nav = document.getElementById('cjc-nav');

        if (!hamburger || !navLinks) return;

        // Hamburger toggle
        hamburger.addEventListener('click', function() {
            var isOpen = navLinks.classList.toggle('cjc-nav-open');
            hamburger.classList.toggle('cjc-nav-hamburger-active');
            hamburger.setAttribute('aria-expanded', isOpen);
            if (isOpen && searchBar) searchBar.classList.remove('cjc-nav-search-open');
        });

        // Search toggle
        if (searchBtn && searchBar) {
            searchBtn.addEventListener('click', function() {
                searchBar.classList.toggle('cjc-nav-search-open');
                if (searchBar.classList.contains('cjc-nav-search-open')) {
                    searchBar.querySelector('input').focus();
                    navLinks.classList.remove('cjc-nav-open');
                    hamburger.classList.remove('cjc-nav-hamburger-active');
                    hamburger.setAttribute('aria-expanded', 'false');
                }
            });
        }

        // Close mobile menu on link click
        navLinks.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                navLinks.classList.remove('cjc-nav-open');
                hamburger.classList.remove('cjc-nav-hamburger-active');
                hamburger.setAttribute('aria-expanded', 'false');
            });
        });

        // Scroll shadow
        var scrolled = false;
        window.addEventListener('scroll', function() {
            var shouldBeScrolled = window.scrollY > 10;
            if (shouldBeScrolled !== scrolled) {
                scrolled = shouldBeScrolled;
                nav.classList.toggle('cjc-nav-scrolled', scrolled);
            }
        }, { passive: true });
    })();
    </script>
    <?php
}, 99);

/* =============================================
   SEO: Noindex Non-Hawaiian Category Posts
   ============================================= */

/**
 * Add noindex meta tag to posts NOT in main Hawaiian categories.
 * This helps focus search engines on your core content.
 */
add_action('wp_head', function() {
    // Only apply to single posts
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
    ];

    // Check if current post is in any of the indexed categories
    $in_indexed_category = false;
    foreach ($indexed_categories as $category_slug) {
        if (in_category($category_slug)) {
            $in_indexed_category = true;
            break;
        }
    }

    // If NOT in an indexed category, add noindex
    if (!$in_indexed_category) {
        echo '<meta name="robots" content="noindex, follow">' . "\n";
    }
}, 1);

/**
 * Hide duplicate posts on the homepage when multiple Divi blog modules
 * display overlapping content.
 */
add_action('wp_footer', function() {
    if (!is_front_page()) {
        return;
    }
    ?>
    <script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            const seenPosts = new Set();

            // Find all Divi blog post articles (they have class like "post-123")
            const posts = document.querySelectorAll('.et_pb_post, .et_pb_blog_grid .post, article[class*="post-"]');

            posts.forEach(function(post) {
                // Extract post ID from class list (e.g., "post-123")
                const postId = Array.from(post.classList)
                    .find(cls => /^post-\d+$/.test(cls));

                if (postId) {
                    if (seenPosts.has(postId)) {
                        // Hide duplicate post
                        post.style.display = 'none';
                    } else {
                        seenPosts.add(postId);
                    }
                }
            });
        });
    })();
    </script>
    <?php
}, 99);

/**
 * Hide WP Tasty recipe total time field when set to 0.
 */
add_action('wp_footer', function() {
    if (!is_singular('post')) {
        return;
    }
    ?>
    <script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            // WP Tasty time selectors
            const timeSelectors = [
                '.tasty-recipes-total-time',
                '.tasty-recipes-details .total-time',
                '[class*="total-time"]'
            ];

            timeSelectors.forEach(function(selector) {
                document.querySelectorAll(selector).forEach(function(el) {
                    const text = el.textContent.trim();
                    // Hide if time is 0, "0 minutes", "0 mins", etc.
                    if (/^(total\s*time[:\s]*)?0(\s*(minutes?|mins?|hours?|hrs?))?$/i.test(text) ||
                        text === '0' ||
                        /:\s*0\s*(minutes?|mins?)?$/i.test(text)) {
                        el.style.display = 'none';
                    }
                });
            });
        });
    })();
    </script>
    <?php
}, 99);

/**
 * Recipe Search Shortcode
 * Usage: [recipe_search] or [recipe_search placeholder="Find a recipe..."]
 * Searches only posts in the 'recipes' category.
 */
add_shortcode('recipe_search', function($atts) {
    $atts = shortcode_atts([
        'placeholder' => 'Search recipes...',
        'button_text' => 'Search',
    ], $atts);

    $placeholder = esc_attr($atts['placeholder']);
    $button_text = esc_html($atts['button_text']);
    $home_url = esc_url(home_url('/'));

    return <<<HTML
    <div class="recipe-search-widget">
        <form role="search" method="get" action="{$home_url}" class="recipe-search-form">
            <input type="hidden" name="category_name" value="recipes">
            <div class="recipe-search-input-wrap">
                <span class="recipe-search-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </span>
                <input type="search" name="s" placeholder="{$placeholder}" class="recipe-search-input" required>
                <button type="submit" class="recipe-search-button">{$button_text}</button>
            </div>
        </form>
    </div>
HTML;
});

/**
 * Sticky "Jump to Recipe" button for recipe posts.
 * Appears on scroll when a WP Tasty recipe card is present.
 */
add_action('wp_footer', function() {
    if (!is_singular('post')) {
        return;
    }
    ?>
    <button id="jump-to-recipe" class="jump-to-recipe-btn" aria-label="Jump to Recipe" style="display: none;">
        <span class="jump-to-recipe-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 8l4 4-4 4"/>
                <path d="M3 12h18"/>
            </svg>
        </span>
        <span class="jump-to-recipe-text">Jump to Recipe</span>
    </button>
    <script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('jump-to-recipe');
            if (!btn) return;

            // Find WP Tasty recipe card
            const recipeCard = document.querySelector('.tasty-recipes, .tasty-recipes-entry, [class*="tasty-recipes"]');
            if (!recipeCard) {
                btn.remove();
                return;
            }

            let scrollTimeout;
            const scrollThreshold = 200;

            // Show/hide button based on scroll position
            function handleScroll() {
                const scrollY = window.scrollY || window.pageYOffset;
                const recipeTop = recipeCard.getBoundingClientRect().top + scrollY;
                const viewportBottom = scrollY + window.innerHeight;

                // Show button after scrolling down, hide when recipe is in view
                if (scrollY > scrollThreshold && viewportBottom < recipeTop + 100) {
                    btn.classList.add('visible');
                    btn.style.display = 'flex';
                } else {
                    btn.classList.remove('visible');
                }
            }

            // Smooth scroll to recipe
            btn.addEventListener('click', function() {
                recipeCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });

            // Throttled scroll listener
            window.addEventListener('scroll', function() {
                if (scrollTimeout) return;
                scrollTimeout = setTimeout(function() {
                    handleScroll();
                    scrollTimeout = null;
                }, 100);
            }, { passive: true });

            // Initial check
            handleScroll();
        });
    })();
    </script>
    <?php
}, 99);

/* =============================================
   Pillar Page Template Shortcodes
   ============================================= */

/**
 * Pillar Hero Section
 * Usage: [pillar_hero title="Your Pillar Title" subtitle="Optional subtitle"]
 */
add_shortcode('pillar_hero', function($atts) {
    $atts = shortcode_atts([
        'title' => get_the_title(),
        'subtitle' => '',
        'background' => '',
    ], $atts);

    $title = esc_html($atts['title']);
    $subtitle = esc_html($atts['subtitle']);
    $bg_style = $atts['background'] ? 'background-image: url(' . esc_url($atts['background']) . ');' : '';

    $subtitle_html = $subtitle ? '<p class="pillar-hero-subtitle">' . $subtitle . '</p>' : '';

    return <<<HTML
    <div class="pillar-hero" style="{$bg_style}">
        <div class="pillar-hero-overlay"></div>
        <div class="pillar-hero-content">
            <h1 class="pillar-hero-title">{$title}</h1>
            {$subtitle_html}
        </div>
    </div>
HTML;
});

/**
 * Auto-generating Table of Contents
 * Usage: [pillar_toc title="In This Guide"]
 * Automatically finds H2 headings on the page via JavaScript.
 */
add_shortcode('pillar_toc', function($atts) {
    $atts = shortcode_atts([
        'title' => 'In This Guide',
    ], $atts);

    $title = esc_html($atts['title']);

    return <<<HTML
    <div class="pillar-toc" id="pillar-toc">
        <h3 class="pillar-toc-title">{$title}</h3>
        <nav class="pillar-toc-nav">
            <ul class="pillar-toc-list" id="pillar-toc-list">
                <!-- Generated by JavaScript -->
            </ul>
        </nav>
    </div>
    <script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            const tocList = document.getElementById('pillar-toc-list');
            const toc = document.getElementById('pillar-toc');
            if (!tocList) return;

            const headings = document.querySelectorAll('.entry-content h2, .et_pb_text h2, article h2');

            if (headings.length === 0) {
                toc.style.display = 'none';
                return;
            }

            headings.forEach(function(heading, index) {
                // Add ID to heading if it doesn't have one
                if (!heading.id) {
                    heading.id = 'section-' + (index + 1);
                }

                const li = document.createElement('li');
                li.className = 'pillar-toc-item';

                const link = document.createElement('a');
                link.href = '#' + heading.id;
                link.className = 'pillar-toc-link';
                link.textContent = heading.textContent;

                // Smooth scroll on click
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    history.pushState(null, null, '#' + heading.id);
                });

                li.appendChild(link);
                tocList.appendChild(li);
            });
        });
    })();
    </script>
HTML;
});

/**
 * Recipe Card Grid
 * Usage: [recipe_grid category="island-comfort" count="6" columns="3"]
 */
add_shortcode('recipe_grid', function($atts) {
    $atts = shortcode_atts([
        'category' => '',
        'count' => 6,
        'columns' => 3,
        'orderby' => 'date',
        'order' => 'DESC',
    ], $atts);

    if (empty($atts['category'])) {
        return '<p class="recipe-grid-error">Please specify a category: [recipe_grid category="your-category"]</p>';
    }

    $args = [
        'post_type' => 'post',
        'posts_per_page' => intval($atts['count']),
        'category_name' => sanitize_text_field($atts['category']),
        'orderby' => sanitize_text_field($atts['orderby']),
        'order' => sanitize_text_field($atts['order']),
    ];

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p class="recipe-grid-empty">No recipes found in this category.</p>';
    }

    $columns = intval($atts['columns']);
    $output = '<div class="recipe-grid recipe-grid-cols-' . $columns . '">';

    while ($query->have_posts()) {
        $query->the_post();

        $thumbnail = get_the_post_thumbnail(get_the_ID(), 'medium', ['class' => 'recipe-card-image']);
        if (!$thumbnail) {
            $thumbnail = '<div class="recipe-card-no-image">No Image</div>';
        }

        $title = esc_html(get_the_title());
        $permalink = esc_url(get_permalink());
        $excerpt = wp_trim_words(get_the_excerpt(), 15, '...');

        $output .= <<<HTML
        <article class="recipe-card">
            <a href="{$permalink}" class="recipe-card-link">
                <div class="recipe-card-thumbnail">
                    {$thumbnail}
                </div>
                <div class="recipe-card-content">
                    <h3 class="recipe-card-title">{$title}</h3>
                    <p class="recipe-card-excerpt">{$excerpt}</p>
                </div>
            </a>
        </article>
HTML;
    }

    wp_reset_postdata();

    $output .= '</div>';

    return $output;
});

/**
 * Internal Links Section
 * Usage: [internal_links title="Related Guides"]
 * Add links manually in content, or use links attribute
 * [internal_links links="Page One|/page-one,Page Two|/page-two"]
 */
add_shortcode('internal_links', function($atts, $content = null) {
    $atts = shortcode_atts([
        'title' => 'Continue Exploring',
        'links' => '',
    ], $atts);

    $title = esc_html($atts['title']);

    $links_html = '';

    // If links provided via attribute
    if (!empty($atts['links'])) {
        $links_array = explode(',', $atts['links']);
        foreach ($links_array as $link) {
            $parts = explode('|', trim($link));
            if (count($parts) === 2) {
                $link_text = esc_html(trim($parts[0]));
                $link_url = esc_url(trim($parts[1]));
                $links_html .= '<a href="' . $link_url . '" class="internal-link-item">';
                $links_html .= '<span class="internal-link-text">' . $link_text . '</span>';
                $links_html .= '<span class="internal-link-arrow">→</span>';
                $links_html .= '</a>';
            }
        }
    }

    // If content provided between tags
    if ($content) {
        $links_html .= '<div class="internal-links-content">' . do_shortcode($content) . '</div>';
    }

    if (empty($links_html)) {
        return '';
    }

    return <<<HTML
    <div class="internal-links-section">
        <h3 class="internal-links-title">{$title}</h3>
        <div class="internal-links-grid">
            {$links_html}
        </div>
    </div>
HTML;
});

/* =============================================
   Related Recipes Section
   ============================================= */

/**
 * Display 3 related recipes at the bottom of each post.
 * Pulls only from Hawaiian recipe categories.
 */
add_filter('the_content', function($content) {
    // Only on single posts, not in admin, not in feeds
    if (!is_singular('post') || is_admin() || is_feed()) {
        return $content;
    }

    // Don't add if we're in a REST API request
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return $content;
    }

    // Hawaiian recipe categories
    $hawaiian_categories = [
        'island-comfort',
        'island-drinks',
        'poke-seafood',
        'tropical-treats',
        'top-articles',
    ];

    $current_post_id = get_the_ID();
    $current_categories = wp_get_post_categories($current_post_id, ['fields' => 'slugs']);

    // Find matching Hawaiian categories for current post
    $matching_cats = array_intersect($current_categories, $hawaiian_categories);

    // Build query args
    $args = [
        'post_type' => 'post',
        'posts_per_page' => 3,
        'post__not_in' => [$current_post_id],
        'orderby' => 'rand',
    ];

    // If current post is in Hawaiian categories, prioritize those
    if (!empty($matching_cats)) {
        $args['category_name'] = implode(',', $matching_cats);
    } else {
        // Otherwise, pull from all Hawaiian categories
        $args['category_name'] = implode(',', $hawaiian_categories);
    }

    $related_query = new WP_Query($args);

    // If not enough posts found, try all Hawaiian categories
    if ($related_query->post_count < 3 && !empty($matching_cats)) {
        $args['category_name'] = implode(',', $hawaiian_categories);
        $related_query = new WP_Query($args);
    }

    if (!$related_query->have_posts()) {
        return $content;
    }

    $related_html = '<section class="island-favorites-section">';
    $related_html .= '<h3 class="island-favorites-title">More Island Favorites</h3>';
    $related_html .= '<div class="island-favorites-grid">';

    while ($related_query->have_posts()) {
        $related_query->the_post();

        $thumbnail = get_the_post_thumbnail(get_the_ID(), 'medium_large', ['class' => 'island-favorite-image']);
        if (!$thumbnail) {
            $thumbnail = '<div class="island-favorite-no-image"></div>';
        }

        $title = esc_html(get_the_title());
        $permalink = esc_url(get_permalink());

        // Get the first Hawaiian category for this post
        $post_categories = wp_get_post_categories(get_the_ID(), ['fields' => 'all']);
        $category_tag = '';
        foreach ($post_categories as $cat) {
            if (in_array($cat->slug, $hawaiian_categories)) {
                $category_tag = '<span class="island-favorite-category" data-category="' . esc_attr($cat->slug) . '">' . esc_html($cat->name) . '</span>';
                break;
            }
        }

        $related_html .= <<<HTML
        <article class="island-favorite-card">
            <a href="{$permalink}" class="island-favorite-link">
                <div class="island-favorite-thumbnail">
                    {$thumbnail}
                    {$category_tag}
                </div>
                <div class="island-favorite-content">
                    <h4 class="island-favorite-title">{$title}</h4>
                </div>
            </a>
        </article>
HTML;
    }

    wp_reset_postdata();

    $related_html .= '</div></section>';

    return $content . $related_html;
}, 20);

/* =============================================
   Category Header Styling
   ============================================= */

/**
 * Add category-specific body classes for styling.
 */
add_filter('body_class', function($classes) {
    if (is_category()) {
        $category = get_queried_object();
        if ($category) {
            $classes[] = 'category-archive';
            $classes[] = 'category-' . $category->slug;
        }
    }
    return $classes;
});

/**
 * Custom category header with wave divider.
 * Hook into the archive title area.
 */
add_action('get_header', function() {
    if (is_category()) {
        add_filter('get_the_archive_title', 'suspended_flavor_category_header', 10, 1);
    }
});

/**
 * Generate custom category header HTML.
 */
function suspended_flavor_category_header($title) {
    $category = get_queried_object();
    if (!$category) {
        return $title;
    }

    $cat_name = esc_html($category->name);
    $cat_description = $category->description ? '<p class="category-header-description">' . esc_html($category->description) . '</p>' : '';
    $cat_slug = $category->slug;

    // Wave SVG divider
    $wave_svg = '<div class="category-wave-divider">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 100" preserveAspectRatio="none">
            <path fill="currentColor" fill-opacity="0.15" d="M0,40 C150,80 350,0 500,40 C650,80 800,20 1000,50 C1200,80 1350,30 1440,50 L1440,100 L0,100 Z"></path>
            <path fill="currentColor" fill-opacity="0.3" d="M0,60 C200,20 400,80 600,50 C800,20 1000,70 1200,40 C1350,20 1400,50 1440,40 L1440,100 L0,100 Z"></path>
        </svg>
    </div>';

    $header_html = sprintf(
        '<div class="category-header category-header-%s">
            <div class="category-header-content">
                <h1 class="category-header-title">%s</h1>
                %s
            </div>
            %s
        </div>',
        $cat_slug,
        $cat_name,
        $cat_description,
        $wave_svg
    );

    return $header_html;
}

/* =============================================
   Custom Hawaiian Footer
   ============================================= */

/**
 * Register custom footer widget areas.
 */
add_action('widgets_init', function() {
    register_sidebar([
        'name' => 'Hawaiian Footer - About',
        'id' => 'hawaiian-footer-about',
        'description' => 'Footer column 1: About section',
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="footer-widget-title">',
        'after_title' => '</h4>',
    ]);

    register_sidebar([
        'name' => 'Hawaiian Footer - Explore',
        'id' => 'hawaiian-footer-explore',
        'description' => 'Footer column 2: Category links',
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="footer-widget-title">',
        'after_title' => '</h4>',
    ]);

    register_sidebar([
        'name' => 'Hawaiian Footer - Connect',
        'id' => 'hawaiian-footer-connect',
        'description' => 'Footer column 3: Social & newsletter',
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="footer-widget-title">',
        'after_title' => '</h4>',
    ]);
});

/**
 * Output custom Hawaiian footer.
 * Add shortcode [hawaiian_footer] or use the action hook.
 */
add_shortcode('hawaiian_footer', 'suspended_flavor_render_footer');

function suspended_flavor_render_footer() {
    ob_start();
    ?>
    <footer class="hawaiian-footer">
        <!-- Wave Pattern at Top -->
        <div class="footer-wave-top">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path fill="currentColor" fill-opacity="0.3" d="M0,60 C240,120 480,0 720,60 C960,120 1200,30 1440,80 L1440,120 L0,120 Z"></path>
                <path fill="currentColor" d="M0,90 C360,40 720,100 1080,60 C1260,40 1380,70 1440,60 L1440,120 L0,120 Z"></path>
            </svg>
        </div>

        <div class="footer-main">
            <div class="footer-container">
                <!-- Column 1: About -->
                <div class="footer-column footer-about">
                    <?php if (is_active_sidebar('hawaiian-footer-about')) : ?>
                        <?php dynamic_sidebar('hawaiian-footer-about'); ?>
                    <?php else : ?>
                        <h4 class="footer-widget-title">About Curtis J Cooks</h4>
                        <p class="footer-bio">Sharing authentic Hawaiian recipes and island flavors from my kitchen to yours. Every dish tells a story of tradition, family, and the spirit of aloha.</p>
                        <p class="footer-tagline">🌺 From Oahu with Aloha</p>
                    <?php endif; ?>
                </div>

                <!-- Column 2: Explore -->
                <div class="footer-column footer-explore">
                    <?php if (is_active_sidebar('hawaiian-footer-explore')) : ?>
                        <?php dynamic_sidebar('hawaiian-footer-explore'); ?>
                    <?php else : ?>
                        <h4 class="footer-widget-title">Explore Recipes</h4>
                        <ul class="footer-nav-list">
                            <li><a href="<?php echo esc_url(get_category_link(get_cat_ID('island-comfort'))); ?>">Island Comfort</a></li>
                            <li><a href="<?php echo esc_url(get_category_link(get_cat_ID('poke-seafood'))); ?>">Poke & Seafood</a></li>
                            <li><a href="<?php echo esc_url(get_category_link(get_cat_ID('tropical-treats'))); ?>">Tropical Treats</a></li>
                            <li><a href="<?php echo esc_url(get_category_link(get_cat_ID('island-drinks'))); ?>">Island Drinks</a></li>
                            <li><a href="<?php echo esc_url(get_category_link(get_cat_ID('top-articles'))); ?>">Top Articles</a></li>
                        </ul>
                    <?php endif; ?>
                </div>

                <!-- Column 3: Connect -->
                <div class="footer-column footer-connect">
                    <?php if (is_active_sidebar('hawaiian-footer-connect')) : ?>
                        <?php dynamic_sidebar('hawaiian-footer-connect'); ?>
                    <?php else : ?>
                        <h4 class="footer-widget-title">Connect</h4>
                        <div class="footer-social">
                            <a href="#" class="social-icon" aria-label="Facebook">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                            </a>
                            <a href="#" class="social-icon" aria-label="Instagram">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                            </a>
                            <a href="#" class="social-icon" aria-label="Pinterest">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>
                            </a>
                            <a href="#" class="social-icon" aria-label="YouTube">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </a>
                        </div>
                        <div class="footer-newsletter">
                            <p class="newsletter-text">Get recipes in your inbox!</p>
                            <form class="newsletter-form" action="#" method="post">
                                <input type="email" placeholder="Your email" required>
                                <button type="submit">Subscribe</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom">
            <div class="footer-container">
                <p class="footer-copyright">
                    © <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. Made with Aloha in Hawaii 🌴
                </p>
                <nav class="footer-legal">
                    <a href="<?php echo esc_url(get_privacy_policy_url()); ?>">Privacy Policy</a>
                    <span class="separator">|</span>
                    <a href="#">Terms of Use</a>
                </nav>
            </div>
        </div>
    </footer>
    <?php
    return ob_get_clean();
}

/**
 * Auto-add footer before closing body (alternative to shortcode).
 * Uncomment to enable automatic footer insertion.
 */
// add_action('wp_footer', function() {
//     if (!is_admin()) {
//         echo suspended_flavor_render_footer();
//     }
// }, 5);

/* =============================================
   Email Signup Form - "Join the Ohana"
   ============================================= */

/**
 * Hawaiian-styled email signup form shortcode.
 * Usage: [ohana_signup] or [ohana_signup action="your-form-action-url"]
 *
 * For integration with email providers, replace the form action
 * or use CSS classes to style existing forms.
 */
add_shortcode('ohana_signup', function($atts) {
    $atts = shortcode_atts([
        'action' => '#',
        'headline' => 'Join the Ohana',
        'subtext' => 'Get authentic Hawaiian recipes & island stories delivered to your inbox.',
        'button_text' => 'Send Me Recipes',
        'placeholder' => 'Enter your email',
        'style' => 'default', // default, compact, boxed
    ], $atts);

    $action = esc_url($atts['action']);
    $headline = esc_html($atts['headline']);
    $subtext = esc_html($atts['subtext']);
    $button_text = esc_html($atts['button_text']);
    $placeholder = esc_attr($atts['placeholder']);
    $style_class = 'ohana-signup-' . sanitize_html_class($atts['style']);
    $form_id = 'ohana-form-' . wp_rand(1000, 9999);

    return <<<HTML
    <div class="ohana-signup {$style_class}">
        <div class="ohana-signup-content">
            <h3 class="ohana-signup-headline">{$headline}</h3>
            <p class="ohana-signup-subtext">{$subtext}</p>
            <form class="ohana-signup-form" id="{$form_id}" action="{$action}" method="post">
                <div class="ohana-form-group">
                    <input type="email" name="email" placeholder="{$placeholder}" required class="ohana-email-input">
                    <button type="submit" class="ohana-submit-btn">{$button_text}</button>
                </div>
                <p class="ohana-privacy-note">We respect your privacy. Unsubscribe anytime.</p>
            </form>
            <div class="ohana-success-message" style="display: none;">
                <span class="ohana-success-icon">🌺</span>
                <p>Mahalo! Check your inbox</p>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var form = document.getElementById('{$form_id}');
        if (form && form.action === '#' || form.action === window.location.href + '#') {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var wrapper = form.closest('.ohana-signup');
                form.style.display = 'none';
                wrapper.querySelector('.ohana-success-message').style.display = 'block';
            });
        }
    })();
    </script>
HTML;
});

/* =============================================
   Site Images Helper Functions
   ============================================= */

/**
 * Get site image URL by filename (without extension)
 * Images should be uploaded to Media Library with matching titles
 */
function curtisjcooks_get_site_image($image_name) {
    $title_map = [
        'homepage-hero' => 'Homepage Hero - Hawaiian Feast',
        'homepage-about-section' => 'About Section - Hawaiian Ingredients',
        'homepage-features-banner' => 'Features Banner - Plate Lunch',
        'gallery-poke-bowl' => 'Hawaiian Poke Bowl',
        'gallery-spam-musubi' => 'Spam Musubi',
        'gallery-mai-tai' => 'Mai Tai Cocktail',
        'gallery-haupia' => 'Haupia Coconut Pudding',
        'gallery-plate-lunch' => 'Hawaiian Plate Lunch',
        'gallery-mochiko-chicken' => 'Mochiko Chicken',
        'gallery-loco-moco' => 'Loco Moco',
        'gallery-malasadas' => 'Malasadas',
        'category-header-breakfast' => 'Category - Hawaiian Breakfast',
        'category-header-comfort' => 'Category - Island Comfort',
        'category-header-drinks' => 'Category - Island Drinks',
        'category-header-treats' => 'Category - Tropical Treats',
        'category-header-seafood' => 'Category - Poke & Seafood',
        'category-header-snacks' => 'Category - Pupus & Snacks',
        'category-header-quick' => 'Category - Quick & Easy',
        'author-photo-curtis' => 'Curtis J - Author Photo',
        'og-social-share' => 'Social Share Image',
        'newsletter-background' => 'Newsletter Background',
        '404-page-image' => '404 Page Image',
        'loading-placeholder' => 'Loading Placeholder',
    ];

    $title = isset($title_map[$image_name]) ? $title_map[$image_name] : $image_name;

    $attachment = get_posts([
        'post_type' => 'attachment',
        'title' => $title,
        'posts_per_page' => 1,
    ]);

    if (!empty($attachment)) {
        return wp_get_attachment_url($attachment[0]->ID);
    }

    // Fallback: try by filename
    $attachments = get_posts([
        'post_type' => 'attachment',
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key' => '_wp_attached_file',
                'value' => $image_name,
                'compare' => 'LIKE',
            ],
        ],
    ]);

    if (!empty($attachments)) {
        return wp_get_attachment_url($attachments[0]->ID);
    }

    return false;
}

/**
 * Get category header image based on category slug
 */
function curtisjcooks_get_category_header_image($category_slug = null) {
    if (!$category_slug && is_category()) {
        $category_slug = get_queried_object()->slug;
    }

    $category_map = [
        'hawaiian-breakfast' => 'category-header-breakfast',
        'island-comfort' => 'category-header-comfort',
        'island-drinks' => 'category-header-drinks',
        'tropical-treats' => 'category-header-treats',
        'poke-seafood' => 'category-header-seafood',
        'poke-and-seafood' => 'category-header-seafood',
        'pupus-snacks' => 'category-header-snacks',
        'quick-easy' => 'category-header-quick',
    ];

    $image_name = isset($category_map[$category_slug]) ? $category_map[$category_slug] : null;

    if ($image_name) {
        return curtisjcooks_get_site_image($image_name);
    }

    return false;
}

/* =============================================
   Custom 404 Page
   ============================================= */

/**
 * Add 404 page styles
 */
add_action('wp_head', function() {
    if (!is_404()) return;
    ?>
    <style>
    .error-404-content {
        text-align: center;
        padding: 60px 20px;
        max-width: 600px;
        margin: 0 auto;
    }
    .error-404-image {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        margin-bottom: 30px;
    }
    .error-404-content h2 {
        color: var(--volcanic-black, #1a1a2e);
        font-family: 'Playfair Display', serif;
        font-size: 32px;
        margin-bottom: 15px;
    }
    .error-404-content p {
        color: #666;
        font-size: 18px;
        margin-bottom: 20px;
    }
    .button-404 {
        display: inline-block;
        background: var(--sunset-orange, #e07c24);
        color: white !important;
        padding: 12px 30px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        transition: background 0.3s ease;
    }
    .button-404:hover {
        background: var(--ocean-deep, #006994);
    }
    </style>
    <?php
});

/* =============================================
   Newsletter Hero Section Styles
   ============================================= */

/**
 * Add newsletter hero styles
 */
add_action('wp_head', function() {
    ?>
    <style>
    .ohana-signup-hero {
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        border-radius: 12px;
        overflow: hidden;
        margin: 40px 0;
    }
    .ohana-signup-hero-overlay {
        background: linear-gradient(135deg, rgba(0, 105, 148, 0.9), rgba(224, 124, 36, 0.85));
        padding: 60px 40px;
    }
    .ohana-signup-hero-content {
        max-width: 600px;
        margin: 0 auto;
        text-align: center;
        color: white;
    }
    .ohana-signup-hero-content h3 {
        font-family: 'Playfair Display', serif;
        font-size: 36px;
        margin-bottom: 15px;
        color: white;
    }
    .ohana-signup-hero-content p {
        font-size: 18px;
        margin-bottom: 25px;
        opacity: 0.95;
    }
    .ohana-signup-hero .ohana-form-group {
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }
    .ohana-signup-hero input[type="email"] {
        padding: 14px 20px;
        border: none;
        border-radius: 25px;
        font-size: 16px;
        min-width: 280px;
    }
    .ohana-signup-hero button {
        padding: 14px 30px;
        background: var(--volcanic-black, #1a1a2e);
        color: white;
        border: none;
        border-radius: 25px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.3s ease;
    }
    .ohana-signup-hero button:hover {
        transform: scale(1.05);
    }
    </style>
    <?php
});

/**
 * Newsletter hero shortcode with background image
 * Use: [ohana_signup_hero]
 */
add_shortcode('ohana_signup_hero', function($atts) {
    $atts = shortcode_atts([
        'action' => '#',
        'headline' => 'Join the Ohana',
        'subtext' => 'Get authentic Hawaiian recipes & island stories delivered to your inbox.',
        'button_text' => 'Send Me Recipes',
        'placeholder' => 'Enter your email',
    ], $atts);

    $bg_image = curtisjcooks_get_site_image('newsletter-background');
    $bg_style = $bg_image ? 'background-image: url(' . esc_url($bg_image) . ');' : '';

    $action = esc_url($atts['action']);
    $headline = esc_html($atts['headline']);
    $subtext = esc_html($atts['subtext']);
    $button_text = esc_html($atts['button_text']);
    $placeholder = esc_attr($atts['placeholder']);

    return <<<HTML
    <div class="ohana-signup-hero" style="{$bg_style}">
        <div class="ohana-signup-hero-overlay">
            <div class="ohana-signup-hero-content">
                <h3>{$headline}</h3>
                <p>{$subtext}</p>
                <form class="ohana-signup-form" action="{$action}" method="post">
                    <div class="ohana-form-group">
                        <input type="email" name="email" placeholder="{$placeholder}" required>
                        <button type="submit">{$button_text}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
HTML;
});

/**
 * Homepage Hero Section Shortcode
 * Use: [curtisjcooks_hero]
 */
add_shortcode('curtisjcooks_hero', function($atts) {
    $atts = shortcode_atts([
        'headline' => 'Taste the Aloha Spirit',
        'subheadline' => 'Authentic Hawaiian recipes bringing island flavors to your kitchen',
        'button_text' => 'Explore Recipes',
        'button_url' => '/recipes/',
    ], $atts);

    $bg_image = curtisjcooks_get_site_image('homepage-hero');
    $bg_style = $bg_image ? "background-image: url('" . esc_url($bg_image) . "');" : '';

    return <<<HTML
    <section class="cjc-hero" style="{$bg_style}">
        <div class="cjc-hero-overlay">
            <div class="cjc-hero-content">
                <h1>{$atts['headline']}</h1>
                <p class="cjc-hero-subheadline">{$atts['subheadline']}</p>
                <a href="{$atts['button_url']}" class="cjc-hero-button">{$atts['button_text']}</a>
            </div>
        </div>
    </section>
HTML;
});

/**
 * About Section Shortcode
 * Use: [curtisjcooks_about]
 */
add_shortcode('curtisjcooks_about', function($atts) {
    $atts = shortcode_atts([
        'headline' => 'Aloha, I\'m Curtis',
        'text' => 'Growing up in Hawaii, I learned that food is more than sustenance—it\'s how we share love, celebrate traditions, and connect with our roots. Through these recipes, I hope to bring a taste of the islands into your home.',
        'button_text' => 'My Story',
        'button_url' => '/about/',
    ], $atts);

    $image_url = curtisjcooks_get_site_image('homepage-about-section');

    return <<<HTML
    <section class="cjc-about">
        <div class="cjc-about-container">
            <div class="cjc-about-image">
                <img src="{$image_url}" alt="Hawaiian ingredients and cooking">
            </div>
            <div class="cjc-about-content">
                <h2>{$atts['headline']}</h2>
                <p>{$atts['text']}</p>
                <a href="{$atts['button_url']}" class="cjc-about-button">{$atts['button_text']}</a>
            </div>
        </div>
    </section>
HTML;
});

/**
 * Features Banner Shortcode
 * Use: [curtisjcooks_features]
 */
add_shortcode('curtisjcooks_features', function($atts) {
    $atts = shortcode_atts([
        'headline' => 'Island-Inspired Cooking',
        'features' => 'Authentic Recipes|Fresh Ingredients|Easy Instructions|Family Favorites',
    ], $atts);

    $bg_image = curtisjcooks_get_site_image('homepage-features-banner');
    $bg_style = $bg_image ? "background-image: url('" . esc_url($bg_image) . "');" : '';
    $features = explode('|', $atts['features']);

    $features_html = '';
    $icons = ['🌺', '🥥', '📝', '❤️'];
    foreach ($features as $i => $feature) {
        $icon = $icons[$i % count($icons)];
        $features_html .= "<div class='cjc-feature-item'><span class='cjc-feature-icon'>{$icon}</span><span>{$feature}</span></div>";
    }

    return <<<HTML
    <section class="cjc-features" style="{$bg_style}">
        <div class="cjc-features-overlay">
            <h2>{$atts['headline']}</h2>
            <div class="cjc-features-grid">
                {$features_html}
            </div>
        </div>
    </section>
HTML;
});

/**
 * Recipe Gallery Shortcode
 * Use: [curtisjcooks_gallery]
 */
add_shortcode('curtisjcooks_gallery', function($atts) {
    $atts = shortcode_atts([
        'headline' => 'Island Favorites',
        'columns' => 4,
    ], $atts);

    $gallery_images = [
        'gallery-poke-bowl' => ['title' => 'Poke Bowl', 'link' => '/category/poke-seafood/'],
        'gallery-spam-musubi' => ['title' => 'Spam Musubi', 'link' => '/category/pupus-snacks/'],
        'gallery-mai-tai' => ['title' => 'Mai Tai', 'link' => '/category/island-drinks/'],
        'gallery-haupia' => ['title' => 'Haupia', 'link' => '/category/tropical-treats/'],
        'gallery-plate-lunch' => ['title' => 'Plate Lunch', 'link' => '/category/island-comfort/'],
        'gallery-mochiko-chicken' => ['title' => 'Mochiko Chicken', 'link' => '/category/island-comfort/'],
        'gallery-loco-moco' => ['title' => 'Loco Moco', 'link' => '/category/hawaiian-breakfast/'],
        'gallery-malasadas' => ['title' => 'Malasadas', 'link' => '/category/tropical-treats/'],
    ];

    $items_html = '';
    foreach ($gallery_images as $key => $data) {
        $image_url = curtisjcooks_get_site_image($key);
        if ($image_url) {
            $items_html .= <<<ITEM
            <a href="{$data['link']}" class="cjc-gallery-item">
                <img src="{$image_url}" alt="{$data['title']}">
                <div class="cjc-gallery-overlay">
                    <span>{$data['title']}</span>
                </div>
            </a>
ITEM;
        }
    }

    return <<<HTML
    <section class="cjc-gallery">
        <h2>{$atts['headline']}</h2>
        <div class="cjc-gallery-grid columns-{$atts['columns']}">
            {$items_html}
        </div>
    </section>
HTML;
});

/**
 * Category Header Shortcode
 * Use: [curtisjcooks_category_header] or [curtisjcooks_category_header category="hawaiian-breakfast"]
 */
add_shortcode('curtisjcooks_category_header', function($atts) {
    $atts = shortcode_atts([
        'category' => '',
    ], $atts);

    $category_slug = $atts['category'];
    if (empty($category_slug) && is_category()) {
        $category_slug = get_queried_object()->slug;
        $category_name = get_queried_object()->name;
    } else {
        $cat = get_category_by_slug($category_slug);
        $category_name = $cat ? $cat->name : ucwords(str_replace('-', ' ', $category_slug));
    }

    $image_url = curtisjcooks_get_category_header_image($category_slug);
    $bg_style = $image_url ? "background-image: url('" . esc_url($image_url) . "');" : '';

    return <<<HTML
    <section class="cjc-category-header" style="{$bg_style}">
        <div class="cjc-category-header-overlay">
            <h1>{$category_name}</h1>
        </div>
    </section>
HTML;
});

/* =============================================
   Enhanced Homepage Shortcodes (React Concept)
   ============================================= */

/**
 * Floating Particles Background
 * Use: [cjc_floating_particles]
 */
add_shortcode('cjc_floating_particles', function() {
    return <<<HTML
    <div class="cjc-floating-particles">
        <span class="cjc-particle">🍍</span>
        <span class="cjc-particle">🥥</span>
        <span class="cjc-particle">🌺</span>
        <span class="cjc-particle">🍹</span>
        <span class="cjc-particle">🐟</span>
        <span class="cjc-particle">🌴</span>
    </div>
HTML;
});

/**
 * Enhanced Hero Section
 * Use: [cjc_hero_enhanced]
 */
add_shortcode('cjc_hero_enhanced', function($atts) {
    $atts = shortcode_atts([
        'badge' => 'Authentic Hawaiian Recipes',
        'headline_1' => 'Taste the',
        'headline_2' => 'Aloha Spirit',
        'description' => 'Bring the flavors of Hawaii to your kitchen with authentic recipes, local tips, and island-inspired cooking adventures.',
        'button_text' => 'Explore Recipes',
        'button_url' => '/recipes/',
        'button_2_text' => 'Watch Videos',
        'button_2_url' => '#',
    ], $atts);

    $hero_image = curtisjcooks_get_site_image('homepage-hero');

    return <<<HTML
    <section class="cjc-hero-enhanced">
        <div class="cjc-hero-bg-animation"></div>
        <div class="cjc-hero-container">
            <div class="cjc-hero-text">
                <div class="cjc-hero-badge">
                    <span>🔥</span>
                    <span>{$atts['badge']}</span>
                </div>
                <h1>
                    {$atts['headline_1']}
                    <span class="cjc-gradient-text">{$atts['headline_2']}</span>
                </h1>
                <p class="cjc-hero-description">{$atts['description']}</p>
                <div class="cjc-hero-buttons">
                    <a href="{$atts['button_url']}" class="cjc-btn-primary">{$atts['button_text']} 🌺</a>
                    <a href="{$atts['button_2_url']}" class="cjc-btn-secondary">{$atts['button_2_text']} ▶️</a>
                </div>
            </div>
            <div class="cjc-hero-image-stack">
                <div class="cjc-hero-main-image">
                    <img src="{$hero_image}" alt="Hawaiian Food">
                    <div class="cjc-hero-image-overlay">
                        <div>
                            <p class="cjc-overlay-title">Fresh Ahi Poke</p>
                            <p class="cjc-overlay-subtitle">Ready in 15 minutes</p>
                        </div>
                        <div class="cjc-stars">★★★★★</div>
                    </div>
                </div>
                <div class="cjc-floating-card top-right">🍹</div>
                <div class="cjc-floating-card bottom-left">
                    <p class="cjc-card-number">50+</p>
                    <p class="cjc-card-label">Recipes</p>
                </div>
            </div>
        </div>
        <div class="cjc-scroll-indicator">
            <span>Scroll to explore</span>
            <span>↓</span>
        </div>
    </section>
HTML;
});

/**
 * Stats Section with Animated Counters
 * Use: [cjc_stats]
 */
add_shortcode('cjc_stats', function($atts) {
    $atts = shortcode_atts([
        'recipes' => '50',
        'readers' => '12000',
        'rating' => '4.9',
    ], $atts);

    return <<<HTML
    <section class="cjc-stats-section">
        <div class="cjc-stats-container">
            <div class="cjc-stat-item cjc-scroll-animate">
                <div class="cjc-stat-number" data-target="{$atts['recipes']}" data-suffix="+">0</div>
                <div class="cjc-stat-label">Hawaiian Recipes</div>
            </div>
            <div class="cjc-stat-item cjc-scroll-animate" data-delay="1">
                <div class="cjc-stat-number" data-target="{$atts['readers']}" data-suffix="+">0</div>
                <div class="cjc-stat-label">Monthly Readers</div>
            </div>
            <div class="cjc-stat-item cjc-scroll-animate" data-delay="2">
                <div class="cjc-stat-number" data-target="{$atts['rating']}" data-suffix=" ★" data-decimals="1">0</div>
                <div class="cjc-stat-label">Average Rating</div>
            </div>
        </div>
    </section>
HTML;
});

/**
 * Category Pills Section
 * Use: [cjc_category_pills]
 */
add_shortcode('cjc_category_pills', function($atts) {
    $atts = shortcode_atts([
        'headline' => 'Explore by Category',
        'subtitle' => 'Find your next favorite Hawaiian dish',
    ], $atts);

    $categories = [
        ['id' => 'all', 'icon' => '🌺', 'name' => 'All Recipes', 'count' => 50, 'active' => true],
        ['id' => 'hawaiian-breakfast', 'icon' => '🍳', 'name' => 'Breakfast', 'count' => 8],
        ['id' => 'island-drinks', 'icon' => '🍹', 'name' => 'Island Drinks', 'count' => 12],
        ['id' => 'poke-seafood', 'icon' => '🐟', 'name' => 'Poke & Seafood', 'count' => 10],
        ['id' => 'island-comfort', 'icon' => '🍖', 'name' => 'Comfort Food', 'count' => 15],
        ['id' => 'tropical-treats', 'icon' => '🍰', 'name' => 'Tropical Treats', 'count' => 8],
    ];

    $pills_html = '';
    foreach ($categories as $i => $cat) {
        $active_class = !empty($cat['active']) ? ' active' : '';
        $link = $cat['id'] === 'all' ? '/recipes/' : '/category/' . $cat['id'] . '/';
        $pills_html .= <<<PILL
        <a href="{$link}" class="cjc-category-pill cjc-scroll-animate{$active_class}" data-delay="{$i}" data-category="{$cat['id']}">
            <span class="cjc-pill-icon">{$cat['icon']}</span>
            <div class="cjc-pill-info">
                <span class="cjc-pill-name">{$cat['name']}</span>
                <span class="cjc-pill-count">{$cat['count']} recipes</span>
            </div>
        </a>
PILL;
    }

    return <<<HTML
    <section class="cjc-categories-section">
        <div class="cjc-section-header">
            <h2>{$atts['headline']}</h2>
            <p class="cjc-section-subtitle">{$atts['subtitle']}</p>
        </div>
        <div class="cjc-category-pills">
            {$pills_html}
        </div>
    </section>
HTML;
});

/**
 * Enhanced Recipe Cards Section
 * Use: [cjc_recipes_enhanced]
 */
add_shortcode('cjc_recipes_enhanced', function($atts) {
    $atts = shortcode_atts([
        'headline' => 'Featured Recipes',
        'subtitle' => 'Handpicked favorites from the islands',
        'count' => 6,
    ], $atts);

    $recipes = new WP_Query([
        'posts_per_page' => intval($atts['count']),
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    ]);

    $cards_html = '';
    $delay = 0;

    if ($recipes->have_posts()) {
        while ($recipes->have_posts()) {
            $recipes->the_post();
            $image = get_the_post_thumbnail_url(get_the_ID(), 'large') ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600';
            $title = get_the_title();
            $link = get_permalink();
            $categories = get_the_category();
            $category = !empty($categories) ? $categories[0]->name : 'Recipe';
            $category_slug = !empty($categories) ? $categories[0]->slug : 'recipe';

            $cards_html .= <<<CARD
            <a href="{$link}" class="cjc-recipe-card-enhanced cjc-scroll-animate" data-delay="{$delay}" data-category="{$category_slug}">
                <div class="cjc-card-image">
                    <img src="{$image}" alt="{$title}">
                </div>
                <div class="cjc-card-overlay"></div>
                <div class="cjc-card-content">
                    <span class="cjc-card-category">{$category}</span>
                    <h3 class="cjc-card-title">{$title}</h3>
                    <div class="cjc-card-meta">
                        <span>⏱️ 20 min</span>
                        <span>•</span>
                        <span>View Recipe →</span>
                    </div>
                </div>
            </a>
CARD;
            $delay++;
        }
        wp_reset_postdata();
    }

    return <<<HTML
    <section class="cjc-recipes-enhanced">
        <div class="cjc-section-header">
            <div>
                <h2>{$atts['headline']}</h2>
                <p class="cjc-section-subtitle">{$atts['subtitle']}</p>
            </div>
            <a href="/recipes/" class="cjc-view-all-link">View All →</a>
        </div>
        <div class="cjc-recipes-grid-enhanced">
            {$cards_html}
        </div>
    </section>
HTML;
});

/**
 * Enhanced Newsletter Section
 * Use: [cjc_newsletter_enhanced]
 */
add_shortcode('cjc_newsletter_enhanced', function($atts) {
    $atts = shortcode_atts([
        'headline' => 'Join the Ohana! 🌺',
        'subtitle' => 'Get weekly Hawaiian recipes, cooking tips, and island inspiration delivered straight to your inbox.',
        'button_text' => 'Subscribe Free',
        'placeholder' => 'Enter your email',
        'note' => 'Join 12,000+ food lovers. Unsubscribe anytime.',
        'action' => '#',
    ], $atts);

    return <<<HTML
    <section class="cjc-newsletter-enhanced">
        <div class="cjc-newsletter-bg">
            <span>🌺</span>
            <span>🍍</span>
        </div>
        <div class="cjc-newsletter-content cjc-scroll-animate">
            <h2>{$atts['headline']}</h2>
            <p class="cjc-newsletter-subtitle">{$atts['subtitle']}</p>
            <form class="cjc-newsletter-form" action="{$atts['action']}" method="post">
                <input type="email" name="email" placeholder="{$atts['placeholder']}" required>
                <button type="submit">{$atts['button_text']}</button>
            </form>
            <p class="cjc-newsletter-note">{$atts['note']}</p>
        </div>
    </section>
HTML;
});

/**
 * Enhanced Footer
 * Use: [cjc_footer_enhanced]
 */
add_shortcode('cjc_footer_enhanced', function() {
    $year = date('Y');

    return <<<HTML
    <footer class="cjc-footer-enhanced">
        <div class="cjc-footer-container">
            <div class="cjc-footer-logo">
                <span>🌺</span>
                <span>CurtisJCooks</span>
            </div>
            <div class="cjc-footer-social">
                <a href="#" class="cjc-social-icon">📸</a>
                <a href="#" class="cjc-social-icon">📌</a>
                <a href="#" class="cjc-social-icon">▶️</a>
                <a href="#" class="cjc-social-icon">✉️</a>
            </div>
        </div>
        <div class="cjc-footer-bottom">
            <p>© {$year} CurtisJCooks. Made with Aloha 🌺</p>
        </div>
    </footer>
HTML;
});

/* =============================================
   SEO: Meta Descriptions & Open Graph Tags
   ============================================= */

/**
 * Add meta descriptions and Open Graph tags for better SEO and social sharing.
 */
add_action('wp_head', function() {
    $site_name = get_bloginfo('name');
    $default_description = 'Authentic Hawaiian recipes bringing island flavors to your kitchen. Discover poke, plate lunch, tropical treats, and more from CurtisJCooks.';

    // Get the Open Graph image
    $og_image = curtisjcooks_get_site_image('og-social-share');
    if (!$og_image) {
        $og_image = curtisjcooks_get_site_image('homepage-hero');
    }

    // Determine page-specific meta
    if (is_front_page()) {
        $title = $site_name . ' - Authentic Hawaiian Recipes';
        $description = $default_description;
        $url = home_url('/');
    } elseif (is_singular('post')) {
        $title = get_the_title() . ' - ' . $site_name;
        $excerpt = get_the_excerpt();
        $description = $excerpt ? wp_strip_all_tags($excerpt) : $default_description;
        $description = wp_trim_words($description, 25, '...');
        $url = get_permalink();

        // Use post thumbnail for Open Graph if available
        $post_image = get_the_post_thumbnail_url(get_the_ID(), 'large');
        if ($post_image) {
            $og_image = $post_image;
        }
    } elseif (is_category()) {
        $category = get_queried_object();
        $title = $category->name . ' Recipes - ' . $site_name;
        $description = $category->description ?: "Explore our {$category->name} recipes. " . $default_description;
        $url = get_category_link($category->term_id);

        // Try to get category-specific image
        $cat_image = curtisjcooks_get_category_header_image($category->slug);
        if ($cat_image) {
            $og_image = $cat_image;
        }
    } elseif (is_page()) {
        $title = get_the_title() . ' - ' . $site_name;
        $page_content = get_the_excerpt() ?: wp_trim_words(get_the_content(), 25, '...');
        $description = wp_strip_all_tags($page_content) ?: $default_description;
        $url = get_permalink();
    } else {
        $title = wp_title('|', false, 'right') . $site_name;
        $description = $default_description;
        $url = home_url($_SERVER['REQUEST_URI']);
    }

    // Escape values
    $title = esc_attr($title);
    $description = esc_attr($description);
    $url = esc_url($url);
    $og_image = $og_image ? esc_url($og_image) : '';
    ?>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo $description; ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?php echo is_singular('post') ? 'article' : 'website'; ?>">
    <meta property="og:url" content="<?php echo $url; ?>">
    <meta property="og:title" content="<?php echo $title; ?>">
    <meta property="og:description" content="<?php echo $description; ?>">
    <meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>">
    <?php if ($og_image): ?>
    <meta property="og:image" content="<?php echo $og_image; ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $title; ?>">
    <meta name="twitter:description" content="<?php echo $description; ?>">
    <?php if ($og_image): ?>
    <meta name="twitter:image" content="<?php echo $og_image; ?>">
    <?php endif; ?>

    <!-- Pinterest -->
    <?php if (is_singular('post') && $og_image): ?>
    <meta property="og:image:alt" content="<?php echo $title; ?>">
    <?php endif; ?>

    <?php
}, 5);

/**
 * Add JSON-LD structured data for recipes.
 * Helps with rich snippets in search results.
 */
add_action('wp_head', function() {
    if (!is_singular('post')) {
        return;
    }

    $post_id = get_the_ID();
    $title = get_the_title();
    $excerpt = get_the_excerpt() ?: wp_trim_words(get_the_content(), 50, '...');
    $image = get_the_post_thumbnail_url($post_id, 'large');
    $date_published = get_the_date('c');
    $date_modified = get_the_modified_date('c');
    $author = get_the_author();

    // Get prep/cook time if available
    $prep_time = get_post_meta($post_id, 'prep_time', true) ?: '15';
    $cook_time = get_post_meta($post_id, 'cook_time', true) ?: '30';

    // Basic Recipe schema
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Recipe',
        'name' => $title,
        'description' => wp_strip_all_tags($excerpt),
        'author' => [
            '@type' => 'Person',
            'name' => $author,
        ],
        'datePublished' => $date_published,
        'dateModified' => $date_modified,
        'prepTime' => 'PT' . intval($prep_time) . 'M',
        'cookTime' => 'PT' . intval($cook_time) . 'M',
        'totalTime' => 'PT' . (intval($prep_time) + intval($cook_time)) . 'M',
        'recipeCategory' => 'Main Course',
        'recipeCuisine' => 'Hawaiian',
    ];

    if ($image) {
        $schema['image'] = [$image];
    }

    ?>
    <script type="application/ld+json">
    <?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
    </script>
    <?php
}, 10);

/**
 * Add Pinterest-specific meta for recipe pins.
 */
add_action('wp_head', function() {
    if (!is_singular('post')) {
        return;
    }

    // Pinterest Rich Pin data (uses Open Graph, but we can add Pinterest-specific hints)
    $categories = get_the_category();
    $category_name = !empty($categories) ? $categories[0]->name : 'Hawaiian Recipe';
    ?>
    <!-- Pinterest Rich Pin hints -->
    <meta property="og:see_also" content="<?php echo esc_url(home_url('/recipes/')); ?>">
    <meta name="pinterest-rich-pin" content="true">
    <?php
}, 15);

/* =============================================
   Reading Time Calculator
   ============================================= */

/**
 * Calculate estimated reading time for a post.
 * Returns reading time in minutes.
 */
function curtisjcooks_get_reading_time($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // Average reading speed: 200 words/min

    return max(1, $reading_time); // Minimum 1 minute
}

/**
 * Display reading time with icon.
 * Usage: <?php curtisjcooks_reading_time(); ?> or [reading_time]
 */
function curtisjcooks_reading_time($post_id = null) {
    $time = curtisjcooks_get_reading_time($post_id);
    $text = $time === 1 ? '1 min read' : $time . ' min read';

    echo '<span class="reading-time">';
    echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
    echo esc_html($text);
    echo '</span>';
}

add_shortcode('reading_time', function($atts) {
    ob_start();
    curtisjcooks_reading_time();
    return ob_get_clean();
});

/* =============================================
   Breadcrumb Navigation
   ============================================= */

/**
 * Display breadcrumb navigation.
 * Usage: <?php curtisjcooks_breadcrumbs(); ?> or [cjc_breadcrumbs]
 */
function curtisjcooks_breadcrumbs() {
    if (is_front_page()) {
        return;
    }

    $home_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>';
    $separator = '<span class="cjc-breadcrumbs-separator">›</span>';

    echo '<nav class="cjc-breadcrumbs" aria-label="Breadcrumb">';
    echo '<div class="cjc-breadcrumbs-container">';
    echo '<ol class="cjc-breadcrumbs-list" itemscope itemtype="https://schema.org/BreadcrumbList">';

    // Home
    echo '<li class="cjc-breadcrumbs-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
    echo '<a href="' . esc_url(home_url('/')) . '" class="cjc-breadcrumbs-home" itemprop="item">' . $home_icon . '<span class="screen-reader-text" itemprop="name">Home</span></a>';
    echo '<meta itemprop="position" content="1">';
    echo '</li>';

    $position = 2;

    if (is_category()) {
        $category = get_queried_object();
        echo $separator;
        echo '<li class="cjc-breadcrumbs-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<a href="' . esc_url(home_url('/recipes/')) . '" itemprop="item"><span itemprop="name">Recipes</span></a>';
        echo '<meta itemprop="position" content="' . $position . '">';
        echo '</li>';
        $position++;

        echo $separator;
        echo '<li class="cjc-breadcrumbs-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<span class="cjc-breadcrumbs-current" itemprop="name">' . esc_html($category->name) . '</span>';
        echo '<meta itemprop="position" content="' . $position . '">';
        echo '</li>';

    } elseif (is_singular('post')) {
        $categories = get_the_category();

        echo $separator;
        echo '<li class="cjc-breadcrumbs-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<a href="' . esc_url(home_url('/recipes/')) . '" itemprop="item"><span itemprop="name">Recipes</span></a>';
        echo '<meta itemprop="position" content="' . $position . '">';
        echo '</li>';
        $position++;

        if (!empty($categories)) {
            echo $separator;
            echo '<li class="cjc-breadcrumbs-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
            echo '<a href="' . esc_url(get_category_link($categories[0]->term_id)) . '" itemprop="item"><span itemprop="name">' . esc_html($categories[0]->name) . '</span></a>';
            echo '<meta itemprop="position" content="' . $position . '">';
            echo '</li>';
            $position++;
        }

        echo $separator;
        echo '<li class="cjc-breadcrumbs-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<span class="cjc-breadcrumbs-current" itemprop="name">' . esc_html(get_the_title()) . '</span>';
        echo '<meta itemprop="position" content="' . $position . '">';
        echo '</li>';

    } elseif (is_page()) {
        $ancestors = get_post_ancestors(get_the_ID());

        if ($ancestors) {
            $ancestors = array_reverse($ancestors);
            foreach ($ancestors as $ancestor_id) {
                echo $separator;
                echo '<li class="cjc-breadcrumbs-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
                echo '<a href="' . esc_url(get_permalink($ancestor_id)) . '" itemprop="item"><span itemprop="name">' . esc_html(get_the_title($ancestor_id)) . '</span></a>';
                echo '<meta itemprop="position" content="' . $position . '">';
                echo '</li>';
                $position++;
            }
        }

        echo $separator;
        echo '<li class="cjc-breadcrumbs-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<span class="cjc-breadcrumbs-current" itemprop="name">' . esc_html(get_the_title()) . '</span>';
        echo '<meta itemprop="position" content="' . $position . '">';
        echo '</li>';

    } elseif (is_search()) {
        echo $separator;
        echo '<li class="cjc-breadcrumbs-item">';
        echo '<span class="cjc-breadcrumbs-current">Search: "' . esc_html(get_search_query()) . '"</span>';
        echo '</li>';
    }

    echo '</ol>';
    echo '</div>';
    echo '</nav>';
}

add_shortcode('cjc_breadcrumbs', function() {
    ob_start();
    curtisjcooks_breadcrumbs();
    return ob_get_clean();
});

/**
 * Auto-add breadcrumbs to posts and pages.
 */
add_action('et_before_main_content', 'curtisjcooks_breadcrumbs', 5);

/* =============================================
   Pin It Button
   ============================================= */

/**
 * Generate Pinterest share URL.
 */
function curtisjcooks_get_pinterest_url($url = null, $image = null, $description = null) {
    if (!$url) {
        $url = get_permalink();
    }
    if (!$image) {
        $image = get_the_post_thumbnail_url(get_the_ID(), 'large');
    }
    if (!$description) {
        $description = get_the_title() . ' - ' . get_bloginfo('name');
    }

    return 'https://pinterest.com/pin/create/button/?' . http_build_query([
        'url' => $url,
        'media' => $image,
        'description' => $description,
    ]);
}

/**
 * Pin It button HTML.
 */
function curtisjcooks_pin_button($image_url = null, $title = null) {
    $pinterest_url = curtisjcooks_get_pinterest_url(null, $image_url, $title);

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>';

    return '<a href="' . esc_url($pinterest_url) . '" class="pin-it-button" target="_blank" rel="noopener noreferrer" aria-label="Pin this recipe">' . $svg . ' Pin</a>';
}

/**
 * Add Pin It buttons to post images via JavaScript.
 */
add_action('wp_footer', function() {
    if (!is_singular('post')) {
        return;
    }

    $post_url = get_permalink();
    $post_title = get_the_title() . ' - ' . get_bloginfo('name');
    ?>
    <script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            // Find all images in post content
            var images = document.querySelectorAll('.cjc-single-content img, .entry-content img, .tasty-recipes img');

            images.forEach(function(img) {
                // Skip small images and icons
                if (img.width < 200 || img.height < 150) return;

                // Create wrapper if not already wrapped
                var parent = img.parentNode;
                if (!parent.classList.contains('pin-it-container')) {
                    var wrapper = document.createElement('div');
                    wrapper.className = 'pin-it-container';
                    wrapper.style.display = 'inline-block';
                    wrapper.style.position = 'relative';
                    parent.insertBefore(wrapper, img);
                    wrapper.appendChild(img);
                    parent = wrapper;
                }

                // Create Pin button
                var pinBtn = document.createElement('a');
                var imgSrc = img.src;
                var pinUrl = 'https://pinterest.com/pin/create/button/?url=' +
                    encodeURIComponent('<?php echo esc_js($post_url); ?>') +
                    '&media=' + encodeURIComponent(imgSrc) +
                    '&description=' + encodeURIComponent('<?php echo esc_js($post_title); ?>');

                pinBtn.href = pinUrl;
                pinBtn.className = 'pin-it-button';
                pinBtn.target = '_blank';
                pinBtn.rel = 'noopener noreferrer';
                pinBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg> Pin';

                parent.appendChild(pinBtn);
            });
        });
    })();
    </script>
    <?php
}, 100);

/* =============================================
   Recipe Actions Bar (Print, Pin, Share)
   ============================================= */

/**
 * Add recipe action buttons to posts.
 * Includes Print, Pin, and Share buttons.
 */
add_filter('the_content', function($content) {
    if (!is_singular('post') || is_admin() || is_feed()) {
        return $content;
    }

    $post_url = get_permalink();
    $post_title = get_the_title();
    $post_image = get_the_post_thumbnail_url(get_the_ID(), 'large');

    // Pinterest URL
    $pinterest_url = curtisjcooks_get_pinterest_url($post_url, $post_image, $post_title . ' - Hawaiian Recipe from CurtisJCooks');

    // Facebook share URL
    $facebook_url = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($post_url);

    $actions_bar = <<<HTML
    <div class="recipe-actions-bar">
        <button class="recipe-action-btn print" onclick="window.print();">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            Print Recipe
        </button>
        <a href="{$pinterest_url}" class="recipe-action-btn pin" target="_blank" rel="noopener noreferrer">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/>
            </svg>
            Pin It
        </a>
        <a href="{$facebook_url}" class="recipe-action-btn share" target="_blank" rel="noopener noreferrer">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="18" cy="5" r="3"/>
                <circle cx="6" cy="12" r="3"/>
                <circle cx="18" cy="19" r="3"/>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
            </svg>
            Share
        </a>
    </div>
HTML;

    // Add after the first paragraph or at the start
    $paragraphs = explode('</p>', $content);
    if (count($paragraphs) > 1) {
        $paragraphs[0] .= '</p>' . $actions_bar;
        $content = implode('</p>', $paragraphs);
    } else {
        $content = $actions_bar . $content;
    }

    return $content;
}, 15);

/**
 * Add reading time to post meta in single.php.
 */
add_filter('the_content', function($content) {
    if (!is_singular('post') || is_admin() || is_feed()) {
        return $content;
    }

    $reading_time = curtisjcooks_get_reading_time();
    $time_text = $reading_time === 1 ? '1 min read' : $reading_time . ' min read';

    $meta_html = <<<HTML
    <div class="cjc-post-meta">
        <span class="cjc-post-meta-item reading-time">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            {$time_text}
        </span>
    </div>
HTML;

    return $meta_html . $content;
}, 5);

/* =============================================
   Content Series Infrastructure
   ============================================= */

/**
 * Register Content Series Taxonomy
 */
add_action('init', function() {
    register_taxonomy('content_series', 'post', [
        'labels' => [
            'name' => 'Content Series',
            'singular_name' => 'Series',
            'search_items' => 'Search Series',
            'all_items' => 'All Series',
            'edit_item' => 'Edit Series',
            'update_item' => 'Update Series',
            'add_new_item' => 'Add New Series',
            'new_item_name' => 'New Series Name',
            'menu_name' => 'Content Series',
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'series'],
    ]);

    // Create default series if they don't exist
    $default_series = [
        'aloha-friday' => 'Aloha Friday',
        'plate-lunch-of-the-week' => 'Plate Lunch of the Week',
        'talk-story' => 'Talk Story',
        'hawaiian-cooking-101' => 'Hawaiian Cooking 101',
    ];

    foreach ($default_series as $slug => $name) {
        if (!term_exists($slug, 'content_series')) {
            wp_insert_term($name, 'content_series', ['slug' => $slug]);
        }
    }
});

/**
 * Series Badge Shortcode
 * Shows a badge for the post's series
 * Usage: [series_badge]
 */
add_shortcode('series_badge', function($atts) {
    $atts = shortcode_atts(['post_id' => null], $atts);
    $post_id = $atts['post_id'] ?: get_the_ID();
    
    $series = get_the_terms($post_id, 'content_series');
    if (!$series || is_wp_error($series)) {
        return '';
    }

    $term = $series[0];
    $colors = [
        'aloha-friday' => '#f97316',
        'plate-lunch-of-the-week' => '#14b8a6',
        'talk-story' => '#8b5cf6',
        'hawaiian-cooking-101' => '#ef4444',
    ];
    $color = $colors[$term->slug] ?? '#6b7280';

    return sprintf(
        '<a href="%s" class="cjc-series-badge" style="background: %s; color: white; padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-decoration: none; display: inline-block;">%s</a>',
        esc_url(get_term_link($term)),
        esc_attr($color),
        esc_html($term->name)
    );
});

/**
 * Display Series Posts
 * Usage: [series_posts series="aloha-friday" count="4"]
 */
add_shortcode('series_posts', function($atts) {
    $atts = shortcode_atts([
        'series' => '',
        'count' => 4,
        'columns' => 2,
    ], $atts);

    if (empty($atts['series'])) {
        return '<p>Please specify a series slug.</p>';
    }

    $posts = new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => intval($atts['count']),
        'tax_query' => [
            [
                'taxonomy' => 'content_series',
                'field' => 'slug',
                'terms' => $atts['series'],
            ],
        ],
    ]);

    if (!$posts->have_posts()) {
        return '<p>No posts found in this series.</p>';
    }

    $colors = [
        'aloha-friday' => '#f97316',
        'plate-lunch-of-the-week' => '#14b8a6',
        'talk-story' => '#8b5cf6',
        'hawaiian-cooking-101' => '#ef4444',
    ];
    $color = $colors[$atts['series']] ?? '#f97316';

    $output = '<div class="cjc-series-grid" style="display: grid; grid-template-columns: repeat(' . intval($atts['columns']) . ', 1fr); gap: 24px; margin: 30px 0;">';

    while ($posts->have_posts()) {
        $posts->the_post();
        $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400';
        
        $output .= sprintf(
            '<a href="%s" class="cjc-series-card" style="display: block; text-decoration: none; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s ease;">
                <div style="aspect-ratio: 16/10; overflow: hidden;">
                    <img src="%s" alt="%s" style="width: 100%%; height: 100%%; object-fit: cover;">
                </div>
                <div style="padding: 16px;">
                    <div style="font-size: 0.75rem; color: %s; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">%s</div>
                    <h3 style="margin: 0; font-size: 1.1rem; color: #1f2937; line-height: 1.4;">%s</h3>
                </div>
            </a>',
            esc_url(get_permalink()),
            esc_url($thumbnail),
            esc_attr(get_the_title()),
            esc_attr($color),
            esc_html(get_the_date('M j, Y')),
            esc_html(get_the_title())
        );
    }

    wp_reset_postdata();
    $output .= '</div>';

    return $output;
});

/**
 * Series Landing Page Shortcode
 * Creates a full landing page for a series
 * Usage: [series_landing series="hawaiian-cooking-101" title="Hawaiian Cooking 101" description="Master the fundamentals..."]
 */
add_shortcode('series_landing', function($atts) {
    $atts = shortcode_atts([
        'series' => '',
        'title' => '',
        'description' => '',
        'color' => '',
    ], $atts);

    if (empty($atts['series'])) {
        return '<p>Please specify a series slug.</p>';
    }

    $term = get_term_by('slug', $atts['series'], 'content_series');
    $title = $atts['title'] ?: ($term ? $term->name : 'Series');
    $description = $atts['description'] ?: ($term ? $term->description : '');

    $colors = [
        'aloha-friday' => ['#f97316', '#fb923c'],
        'plate-lunch-of-the-week' => ['#14b8a6', '#2dd4bf'],
        'talk-story' => ['#8b5cf6', '#a78bfa'],
        'hawaiian-cooking-101' => ['#ef4444', '#f87171'],
    ];
    $gradient = $colors[$atts['series']] ?? ['#f97316', '#fb923c'];

    $posts = new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => -1,
        'tax_query' => [
            [
                'taxonomy' => 'content_series',
                'field' => 'slug',
                'terms' => $atts['series'],
            ],
        ],
        'orderby' => 'date',
        'order' => 'ASC',
    ]);

    $output = sprintf(
        '<div class="cjc-series-landing">
            <div style="background: linear-gradient(135deg, %s 0%%, %s 100%%); padding: 60px 24px; text-align: center; color: white; border-radius: 16px; margin-bottom: 40px;">
                <h1 style="font-family: Playfair Display, Georgia, serif; font-size: 2.5rem; margin: 0 0 16px 0;">%s</h1>
                <p style="font-size: 1.1rem; max-width: 600px; margin: 0 auto; opacity: 0.95;">%s</p>
                <p style="margin-top: 20px; font-size: 0.9rem; opacity: 0.8;">%d articles in this series</p>
            </div>',
        esc_attr($gradient[0]),
        esc_attr($gradient[1]),
        esc_html($title),
        esc_html($description),
        $posts->post_count
    );

    if ($posts->have_posts()) {
        $output .= '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">';
        $count = 1;

        while ($posts->have_posts()) {
            $posts->the_post();
            $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400';
            $excerpt = wp_trim_words(get_the_excerpt(), 15);

            $output .= sprintf(
                '<a href="%s" style="display: flex; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); text-decoration: none; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                    <div style="flex: 0 0 100px; background: linear-gradient(135deg, %s, %s); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: bold;">%d</div>
                    <div style="padding: 20px; flex: 1;">
                        <h3 style="margin: 0 0 8px 0; font-size: 1.1rem; color: #1f2937;">%s</h3>
                        <p style="margin: 0; font-size: 0.9rem; color: #6b7280;">%s</p>
                    </div>
                </a>',
                esc_url(get_permalink()),
                esc_attr($gradient[0]),
                esc_attr($gradient[1]),
                $count,
                esc_html(get_the_title()),
                esc_html($excerpt)
            );
            $count++;
        }

        wp_reset_postdata();
        $output .= '</div>';
    }

    $output .= '</div>';

    return $output;
});

/**
 * Aloha Friday Widget
 * Shows this week's Aloha Friday post
 * Usage: [aloha_friday_widget]
 */
add_shortcode('aloha_friday_widget', function() {
    $post = get_posts([
        'post_type' => 'post',
        'posts_per_page' => 1,
        'tax_query' => [
            [
                'taxonomy' => 'content_series',
                'field' => 'slug',
                'terms' => 'aloha-friday',
            ],
        ],
    ]);

    if (empty($post)) {
        return '';
    }

    $post = $post[0];
    $thumbnail = get_the_post_thumbnail_url($post->ID, 'medium') ?: 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400';

    return sprintf(
        '<div class="cjc-aloha-friday-widget" style="background: linear-gradient(135deg, #f97316, #fb923c); border-radius: 16px; overflow: hidden; color: white;">
            <div style="padding: 20px 20px 10px 20px;">
                <span style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9;">🌺 Aloha Friday</span>
                <h3 style="margin: 8px 0 0 0; font-family: Playfair Display, Georgia, serif; font-size: 1.3rem;">%s</h3>
            </div>
            <a href="%s" style="display: block;">
                <img src="%s" alt="%s" style="width: 100%%; height: 180px; object-fit: cover;">
            </a>
            <div style="padding: 15px 20px;">
                <a href="%s" style="color: white; text-decoration: none; font-weight: 600;">Read This Week\'s Feature →</a>
            </div>
        </div>',
        esc_html($post->post_title),
        esc_url(get_permalink($post->ID)),
        esc_url($thumbnail),
        esc_attr($post->post_title),
        esc_url(get_permalink($post->ID))
    );
});

/**
 * Add series info to post display
 */
add_filter('the_content', function($content) {
    if (!is_singular('post') || is_admin()) {
        return $content;
    }

    $series = get_the_terms(get_the_ID(), 'content_series');
    if (!$series || is_wp_error($series)) {
        return $content;
    }

    $term = $series[0];
    $colors = [
        'aloha-friday' => ['#f97316', '#fb923c'],
        'plate-lunch-of-the-week' => ['#14b8a6', '#2dd4bf'],
        'talk-story' => ['#8b5cf6', '#a78bfa'],
        'hawaiian-cooking-101' => ['#ef4444', '#f87171'],
    ];
    $gradient = $colors[$term->slug] ?? ['#f97316', '#fb923c'];

    // Get other posts in series
    $related = new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => 3,
        'post__not_in' => [get_the_ID()],
        'tax_query' => [
            [
                'taxonomy' => 'content_series',
                'field' => 'term_id',
                'terms' => $term->term_id,
            ],
        ],
    ]);

    $series_box = sprintf(
        '<div style="background: linear-gradient(135deg, %s, %s); padding: 20px; border-radius: 12px; margin-bottom: 30px; color: white;">
            <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; margin-bottom: 8px;">Part of the Series</div>
            <a href="%s" style="color: white; text-decoration: none; font-size: 1.3rem; font-weight: 700; font-family: Playfair Display, Georgia, serif;">%s</a>
        </div>',
        esc_attr($gradient[0]),
        esc_attr($gradient[1]),
        esc_url(get_term_link($term)),
        esc_html($term->name)
    );

    return $series_box . $content;
}, 3);

/* =============================================
   Custom Mega Menu Header
   ============================================= */

/**
 * Register navigation menus
 */
add_action('after_setup_theme', function() {
    register_nav_menus([
        'hawaiian-primary' => 'Hawaiian Primary Menu',
        'hawaiian-mobile' => 'Hawaiian Mobile Menu',
    ]);
});

/**
 * Output custom Hawaiian header with mega menu
 */
add_action('wp_body_open', 'curtisjcooks_custom_header', 5);

function curtisjcooks_custom_header() {
    // Get categories for mega menu
    $categories = get_categories([
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_empty' => false,
    ]);

    // Get content series
    $series = get_terms([
        'taxonomy' => 'content_series',
        'hide_empty' => false,
    ]);

    // Series colors
    $series_colors = [
        'aloha-friday' => '#f97316',
        'plate-lunch-of-the-week' => '#14b8a6',
        'talk-story' => '#8b5cf6',
        'hawaiian-cooking-101' => '#ef4444',
    ];

    // Series icons
    $series_icons = [
        'aloha-friday' => '🌅',
        'plate-lunch-of-the-week' => '🍱',
        'talk-story' => '💬',
        'hawaiian-cooking-101' => '📚',
    ];
    ?>

    <style>
    /* Hide Divi's default header - aggressive override */
    #main-header,
    #top-header,
    header#main-header,
    .et_fixed_nav #main-header,
    .et-fixed-header#main-header,
    body #main-header,
    body.et_fixed_nav #main-header,
    #et-main-area #main-header {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
        pointer-events: none !important;
        position: absolute !important;
        top: -9999px !important;
        left: -9999px !important;
    }

    /* Remove Divi's page padding for fixed nav */
    .et_fixed_nav.et_show_nav #page-container,
    .et_fixed_nav #page-container,
    body.et_fixed_nav #page-container {
        padding-top: 0 !important;
    }

    /* Custom Hawaiian Header */
    .cjc-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 99999;
        background: white;
        box-shadow: 0 2px 20px rgba(0,0,0,0.1);
    }

    .cjc-header-inner {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 80px;
    }

    .cjc-logo {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 28px;
        font-weight: 700;
        text-decoration: none;
        background: linear-gradient(135deg, #f97316, #fb923c);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cjc-logo:hover {
        opacity: 0.9;
    }

    .cjc-nav {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cjc-nav-item {
        position: relative;
        padding: 28px 16px;
        font-size: 15px;
        font-weight: 600;
        color: #374151;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .cjc-nav-item:hover {
        color: #f97316;
    }

    .cjc-nav-item.has-mega::after {
        content: '▾';
        margin-left: 6px;
        font-size: 10px;
        opacity: 0.6;
    }

    /* Mega Menu Dropdown */
    .cjc-mega-menu {
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(10px);
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        padding: 32px;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        min-width: 600px;
        z-index: 100000;
    }

    .cjc-nav-item:hover .cjc-mega-menu {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }

    .cjc-mega-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 20px 0;
        padding-bottom: 12px;
        border-bottom: 2px solid #f3f4f6;
    }

    .cjc-mega-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .cjc-mega-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 10px;
        text-decoration: none;
        color: #374151;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .cjc-mega-link:hover {
        background: #fff7ed;
        color: #f97316;
        transform: translateX(4px);
    }

    .cjc-mega-link .icon {
        font-size: 1.5rem;
    }

    .cjc-mega-link .text {
        display: flex;
        flex-direction: column;
    }

    .cjc-mega-link .name {
        font-weight: 600;
        font-size: 0.95rem;
    }

    .cjc-mega-link .count {
        font-size: 0.75rem;
        color: #9ca3af;
        font-weight: 400;
    }

    /* Series Mega Menu */
    .cjc-series-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .cjc-series-card {
        display: block;
        padding: 20px;
        border-radius: 12px;
        text-decoration: none;
        color: white;
        transition: all 0.3s ease;
    }

    .cjc-series-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }

    .cjc-series-card .series-icon {
        font-size: 2rem;
        margin-bottom: 8px;
    }

    .cjc-series-card .series-name {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .cjc-series-card .series-desc {
        font-size: 0.8rem;
        opacity: 0.9;
    }

    /* Header Actions */
    .cjc-header-actions {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .cjc-search-toggle {
        background: none;
        border: none;
        padding: 8px;
        cursor: pointer;
        color: #374151;
        font-size: 20px;
        transition: color 0.3s ease;
    }

    .cjc-search-toggle:hover {
        color: #f97316;
    }

    .cjc-header-cta {
        background: linear-gradient(135deg, #f97316, #fb923c);
        color: white;
        padding: 12px 24px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .cjc-header-cta:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
        color: white;
    }

    /* Mobile Menu Toggle */
    .cjc-mobile-toggle {
        display: none;
        background: none;
        border: none;
        padding: 8px;
        cursor: pointer;
        flex-direction: column;
        gap: 5px;
    }

    .cjc-mobile-toggle span {
        display: block;
        width: 24px;
        height: 2px;
        background: #374151;
        transition: all 0.3s ease;
    }

    /* Search Overlay */
    .cjc-search-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.9);
        z-index: 100001;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .cjc-search-overlay.is-active {
        opacity: 1;
        visibility: visible;
    }

    .cjc-search-container {
        width: 100%;
        max-width: 600px;
        padding: 24px;
    }

    .cjc-search-form {
        display: flex;
        background: white;
        border-radius: 50px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }

    .cjc-search-input {
        flex: 1;
        border: none;
        padding: 20px 30px;
        font-size: 18px;
        outline: none;
    }

    .cjc-search-submit {
        background: linear-gradient(135deg, #f97316, #fb923c);
        border: none;
        padding: 20px 30px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.3s ease;
    }

    .cjc-search-submit:hover {
        opacity: 0.9;
    }

    .cjc-search-close {
        position: absolute;
        top: 30px;
        right: 30px;
        background: none;
        border: none;
        color: white;
        font-size: 40px;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.3s ease;
    }

    .cjc-search-close:hover {
        opacity: 1;
    }

    /* Page offset for fixed header */
    body {
        padding-top: 80px !important;
    }

    /* Mobile menu - hidden by default on desktop */
    .cjc-mobile-menu {
        display: none;
    }

    /* Mobile Styles */
    @media (max-width: 1024px) {
        .cjc-nav {
            display: none;
        }

        .cjc-mobile-toggle {
            display: flex;
        }

        .cjc-header-cta {
            display: none;
        }

        /* Mobile Menu */
        .cjc-mobile-menu {
            display: block;
            position: fixed;
            top: 80px;
            left: 0;
            right: 0;
            bottom: 0;
            background: white;
            z-index: 99998;
            padding: 24px;
            overflow-y: auto;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }

        .cjc-mobile-menu.is-active {
            transform: translateX(0);
        }

        .cjc-mobile-nav-item {
            display: block;
            padding: 16px 0;
            font-size: 18px;
            font-weight: 600;
            color: #374151;
            text-decoration: none;
            border-bottom: 1px solid #f3f4f6;
        }

        .cjc-mobile-nav-item:hover {
            color: #f97316;
        }

        .cjc-mobile-submenu {
            padding-left: 20px;
            margin-top: 8px;
        }

        .cjc-mobile-submenu a {
            display: block;
            padding: 10px 0;
            font-size: 15px;
            color: #6b7280;
            text-decoration: none;
        }

        .cjc-mobile-submenu a:hover {
            color: #f97316;
        }
    }

    @media (max-width: 640px) {
        .cjc-header-inner {
            height: 70px;
        }

        .cjc-logo {
            font-size: 22px;
        }

        body {
            padding-top: 70px !important;
        }

        .cjc-mobile-menu {
            top: 70px;
        }
    }
    </style>

    <header class="cjc-header">
        <div class="cjc-header-inner">
            <!-- Logo -->
            <a href="<?php echo home_url(); ?>" class="cjc-logo">
                🌺 CurtisJCooks
            </a>

            <!-- Desktop Navigation -->
            <nav class="cjc-nav">
                <a href="<?php echo home_url(); ?>" class="cjc-nav-item">Home</a>

                <!-- Recipes Mega Menu -->
                <div class="cjc-nav-item has-mega">
                    Recipes
                    <div class="cjc-mega-menu">
                        <h3 class="cjc-mega-title">🍽️ Browse by Category</h3>
                        <div class="cjc-mega-grid">
                            <?php
                            $category_icons = [
                                'hawaiian-breakfast' => '🍳',
                                'island-comfort' => '🍲',
                                'island-drinks' => '🍹',
                                'tropical-treats' => '🍨',
                                'poke-seafood' => '🐟',
                                'poke-and-seafood' => '🐟',
                                'pupus-snacks' => '🥟',
                                'quick-easy' => '⚡',
                            ];

                            foreach ($categories as $cat) {
                                if ($cat->slug === 'uncategorized') continue;
                                $icon = $category_icons[$cat->slug] ?? '🌴';
                                $count = $cat->count;
                                ?>
                                <a href="<?php echo get_category_link($cat->term_id); ?>" class="cjc-mega-link">
                                    <span class="icon"><?php echo $icon; ?></span>
                                    <span class="text">
                                        <span class="name"><?php echo esc_html($cat->name); ?></span>
                                        <span class="count"><?php echo $count; ?> recipe<?php echo $count !== 1 ? 's' : ''; ?></span>
                                    </span>
                                </a>
                                <?php
                            }
                            ?>
                        </div>
                        <div style="margin-top: 20px; padding-top: 16px; border-top: 2px solid #f3f4f6; text-align: center;">
                            <a href="<?php echo home_url('/recipes/'); ?>" style="color: #f97316; font-weight: 600; text-decoration: none;">View All Recipes →</a>
                        </div>
                    </div>
                </div>

                <!-- Series Mega Menu -->
                <div class="cjc-nav-item has-mega">
                    Series
                    <div class="cjc-mega-menu" style="min-width: 500px;">
                        <h3 class="cjc-mega-title">📚 Content Series</h3>
                        <div class="cjc-series-grid">
                            <?php
                            $series_descriptions = [
                                'aloha-friday' => 'Weekly Friday features & recipes',
                                'plate-lunch-of-the-week' => 'Classic Hawaiian plate lunches',
                                'talk-story' => 'Stories & Hawaiian culture',
                                'hawaiian-cooking-101' => 'Learn the fundamentals',
                            ];

                            if (!empty($series) && !is_wp_error($series)) {
                                foreach ($series as $term) {
                                    $color = $series_colors[$term->slug] ?? '#f97316';
                                    $icon = $series_icons[$term->slug] ?? '📖';
                                    $desc = $series_descriptions[$term->slug] ?? '';
                                    ?>
                                    <a href="<?php echo get_term_link($term); ?>" class="cjc-series-card" style="background: <?php echo $color; ?>;">
                                        <div class="series-icon"><?php echo $icon; ?></div>
                                        <div class="series-name"><?php echo esc_html($term->name); ?></div>
                                        <div class="series-desc"><?php echo esc_html($desc); ?></div>
                                    </a>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Guides Link -->
                <div class="cjc-nav-item has-mega">
                    Guides
                    <div class="cjc-mega-menu" style="min-width: 450px;">
                        <h3 class="cjc-mega-title">📖 Hawaiian Cooking Guides</h3>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <a href="<?php echo home_url('/guide-hawaiian-poke/'); ?>" class="cjc-mega-link">
                                <span class="icon">🐟</span>
                                <span class="text">
                                    <span class="name">The Complete Guide to Hawaiian Poke</span>
                                    <span class="count">History, techniques & recipes</span>
                                </span>
                            </a>
                            <a href="<?php echo home_url('/guide-plate-lunch/'); ?>" class="cjc-mega-link">
                                <span class="icon">🍱</span>
                                <span class="text">
                                    <span class="name">Mastering Hawaiian Plate Lunch</span>
                                    <span class="count">The soul of Hawaiian comfort food</span>
                                </span>
                            </a>
                            <a href="<?php echo home_url('/guide-hawaiian-ingredients/'); ?>" class="cjc-mega-link">
                                <span class="icon">🥥</span>
                                <span class="text">
                                    <span class="name">Essential Hawaiian Ingredients</span>
                                    <span class="count">From poi to li hing mui</span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <a href="<?php echo home_url('/about/'); ?>" class="cjc-nav-item">About</a>
            </nav>

            <!-- Header Actions -->
            <div class="cjc-header-actions">
                <button class="cjc-search-toggle" onclick="document.querySelector('.cjc-search-overlay').classList.add('is-active')">
                    🔍
                </button>
                <a href="<?php echo home_url('/recipes/'); ?>" class="cjc-header-cta">Browse Recipes</a>

                <!-- Mobile Toggle -->
                <button class="cjc-mobile-toggle" onclick="document.querySelector('.cjc-mobile-menu').classList.toggle('is-active')">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div class="cjc-mobile-menu">
        <a href="<?php echo home_url(); ?>" class="cjc-mobile-nav-item">Home</a>

        <div class="cjc-mobile-nav-item">Recipes</div>
        <div class="cjc-mobile-submenu">
            <?php foreach ($categories as $cat) {
                if ($cat->slug === 'uncategorized') continue;
                ?>
                <a href="<?php echo get_category_link($cat->term_id); ?>"><?php echo esc_html($cat->name); ?></a>
            <?php } ?>
            <a href="<?php echo home_url('/recipes/'); ?>" style="color: #f97316; font-weight: 600;">View All Recipes →</a>
        </div>

        <div class="cjc-mobile-nav-item">Series</div>
        <div class="cjc-mobile-submenu">
            <?php if (!empty($series) && !is_wp_error($series)) {
                foreach ($series as $term) { ?>
                    <a href="<?php echo get_term_link($term); ?>"><?php echo esc_html($term->name); ?></a>
                <?php }
            } ?>
        </div>

        <div class="cjc-mobile-nav-item">Guides</div>
        <div class="cjc-mobile-submenu">
            <a href="<?php echo home_url('/guide-hawaiian-poke/'); ?>">Guide to Hawaiian Poke</a>
            <a href="<?php echo home_url('/guide-plate-lunch/'); ?>">Mastering Plate Lunch</a>
            <a href="<?php echo home_url('/guide-hawaiian-ingredients/'); ?>">Hawaiian Ingredients</a>
        </div>

        <a href="<?php echo home_url('/about/'); ?>" class="cjc-mobile-nav-item">About</a>

        <div style="margin-top: 24px;">
            <a href="<?php echo home_url('/recipes/'); ?>" class="cjc-header-cta" style="display: block; text-align: center;">Browse All Recipes</a>
        </div>
    </div>

    <!-- Search Overlay -->
    <div class="cjc-search-overlay">
        <button class="cjc-search-close" onclick="document.querySelector('.cjc-search-overlay').classList.remove('is-active')">×</button>
        <div class="cjc-search-container">
            <form class="cjc-search-form" action="<?php echo home_url('/'); ?>" method="get">
                <input type="text" name="s" class="cjc-search-input" placeholder="Search recipes, ingredients, techniques..." autocomplete="off">
                <button type="submit" class="cjc-search-submit">Search</button>
            </form>
            <p style="color: white; text-align: center; margin-top: 20px; opacity: 0.7;">Try: "poke", "kalua pig", "mac salad"</p>
        </div>
    </div>

    <?php
}

/**
 * Close search on escape key
 */
add_action('wp_footer', function() {
    ?>
    <script>
    // FORCE HIDE DIVI HEADER - runs immediately
    (function() {
        function hideDiviHeader() {
            var diviHeader = document.getElementById('main-header');
            var topHeader = document.getElementById('top-header');
            if (diviHeader) {
                diviHeader.style.cssText = 'display: none !important; visibility: hidden !important; height: 0 !important; opacity: 0 !important;';
                diviHeader.remove();
            }
            if (topHeader) {
                topHeader.style.cssText = 'display: none !important; visibility: hidden !important;';
                topHeader.remove();
            }
            // Also remove page container padding
            var pageContainer = document.getElementById('page-container');
            if (pageContainer) {
                pageContainer.style.paddingTop = '0';
            }
        }

        // Run immediately
        hideDiviHeader();

        // Run on DOMContentLoaded
        document.addEventListener('DOMContentLoaded', hideDiviHeader);

        // Run after a short delay (in case Divi loads late)
        setTimeout(hideDiviHeader, 100);
        setTimeout(hideDiviHeader, 500);
    })();

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelector('.cjc-search-overlay')?.classList.remove('is-active');
            document.querySelector('.cjc-mobile-menu')?.classList.remove('is-active');
        }
    });

    // Close mobile menu when clicking a link
    document.querySelectorAll('.cjc-mobile-menu a').forEach(function(link) {
        link.addEventListener('click', function() {
            document.querySelector('.cjc-mobile-menu').classList.remove('is-active');
        });
    });
    </script>
    <?php
});
