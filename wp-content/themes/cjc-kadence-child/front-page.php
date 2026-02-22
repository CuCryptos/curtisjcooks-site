<?php
/**
 * CJC Kadence Child — Front Page (Homepage)
 * Interactive Island Experience — time-aware, culturally rich, with recipe picker.
 *
 * @package CJC_Kadence_Child
 */

defined('ABSPATH') || exit;

get_header();

$uploads_url = content_url('/uploads/site-images');

// --- Hawaiian proverbs (ʻōlelo noʻeau) ---
$proverbs = [
    ['haw' => 'ʻAi no i ka ʻono, a māʻona', 'en' => 'Eat what is delicious until satisfied'],
    ['haw' => 'He aliʻi ka ʻāina, he kauwā ke kanaka', 'en' => 'The land is chief, man is its servant'],
    ['haw' => 'ʻAʻohe hana nui ke alu ʻia', 'en' => 'No task is too big when done together'],
    ['haw' => 'Ma ka hana ka ʻike', 'en' => 'In working, one learns'],
    ['haw' => 'E ʻai i ka mea i loaʻa', 'en' => 'Eat what is available'],
    ['haw' => 'Aia ke ola i ka hana', 'en' => 'Life is in the work'],
];
shuffle($proverbs);
$proverb_index = 0;

// --- Time-aware featured section ---
$hour = (int) current_time('G');
$day_of_week = (int) current_time('N'); // 1=Mon, 7=Sun
$is_weekend = in_array($day_of_week, [6, 7]);

if ($hour < 11) {
    $time_cat = 873; // Hawaiian Breakfast
} elseif ($hour < 17) {
    $time_cat = 866; // Quick & Easy
} else {
    $time_cat = 860; // Island Comfort
}
if ($is_weekend && $hour >= 11) {
    $time_cat = 874; // Pupus & Snacks
}

// --- Picker recipe data ---
$picker_categories = [
    860 => ['time' => 'long',   'vibes' => ['comfort']],
    859 => ['time' => 'medium', 'vibes' => ['fresh']],
    861 => ['time' => 'medium', 'vibes' => ['comfort']],
    862 => ['time' => 'quick',  'vibes' => ['party']],
    873 => ['time' => 'quick',  'vibes' => ['comfort']],
    874 => ['time' => 'medium', 'vibes' => ['party']],
    866 => ['time' => 'quick',  'vibes' => ['fresh']],
    107 => ['time' => 'long',   'vibes' => ['comfort']],
];

$picker_recipes = [];
foreach ($picker_categories as $cat_id => $meta) {
    $q = new WP_Query([
        'post_type'      => 'post',
        'posts_per_page' => 4,
        'cat'            => $cat_id,
        'post_status'    => 'publish',
        'orderby'        => 'rand',
    ]);
    while ($q->have_posts()) {
        $q->the_post();
        $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
        if (!$thumb) continue;
        $cats = get_the_category();
        $picker_recipes[] = [
            'id'           => get_the_ID(),
            'title'        => get_the_title(),
            'url'          => get_permalink(),
            'image'        => $thumb,
            'excerpt'      => wp_trim_words(get_the_excerpt(), 12, '...'),
            'category'     => !empty($cats) ? $cats[0]->name : '',
            'time_bucket'  => $meta['time'],
            'vibe_buckets' => $meta['vibes'],
        ];
    }
    wp_reset_postdata();
}
shuffle($picker_recipes);
?>

<!-- 1. Time-Aware Hero -->
<section class="homepage-hero">
    <img class="homepage-hero__image"
         src="<?php echo esc_url($uploads_url . '/homepage-hero.png'); ?>"
         alt="Hawaiian food spread on a rustic wooden table">
    <div class="homepage-hero__overlay" aria-hidden="true"></div>

    <!-- Floating bokeh particles (CSS animated) -->
    <span class="homepage-hero__bokeh" style="left:10%;bottom:20%;width:6px;height:6px;--bokeh-duration:14s;--bokeh-delay:0s;--bokeh-opacity:0.2" aria-hidden="true"></span>
    <span class="homepage-hero__bokeh" style="left:25%;bottom:10%;width:4px;height:4px;--bokeh-duration:18s;--bokeh-delay:3s;--bokeh-opacity:0.15" aria-hidden="true"></span>
    <span class="homepage-hero__bokeh" style="left:50%;bottom:30%;width:8px;height:8px;--bokeh-duration:12s;--bokeh-delay:1s;--bokeh-opacity:0.18" aria-hidden="true"></span>
    <span class="homepage-hero__bokeh" style="left:65%;bottom:15%;width:5px;height:5px;--bokeh-duration:16s;--bokeh-delay:5s;--bokeh-opacity:0.22" aria-hidden="true"></span>
    <span class="homepage-hero__bokeh" style="left:80%;bottom:25%;width:7px;height:7px;--bokeh-duration:20s;--bokeh-delay:2s;--bokeh-opacity:0.16" aria-hidden="true"></span>
    <span class="homepage-hero__bokeh" style="left:40%;bottom:5%;width:5px;height:5px;--bokeh-duration:15s;--bokeh-delay:7s;--bokeh-opacity:0.2" aria-hidden="true"></span>

    <div class="homepage-hero__content">
        <p class="homepage-hero__greeting" lang="haw">Aloha</p>
        <h1 class="homepage-hero__tagline">Authentic Hawaiian Recipes &amp; Island Flavors</h1>
        <p class="homepage-hero__subtitle">From our kitchen to yours — the flavors, stories, and traditions of Hawai'i</p>
        <a class="homepage-hero__cta" href="<?php echo esc_url(get_category_link(26)); ?>">Explore Recipes</a>
    </div>

    <!-- Scroll indicator -->
    <div class="homepage-hero__scroll-indicator" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12l7 7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </div>
</section>

<!-- Proverb Divider 1 -->
<?php if (isset($proverbs[$proverb_index])) : $p = $proverbs[$proverb_index++]; ?>
<div class="kapa-divider kapa-divider--triangle" aria-hidden="true"></div>
<div class="kapa-proverb" data-reveal>
    <span class="kapa-proverb__hawaiian" lang="haw"><?php echo esc_html($p['haw']); ?></span>
    <span class="kapa-proverb__english"><?php echo esc_html($p['en']); ?></span>
</div>
<?php endif; ?>

<!-- 2. "What Should I Cook?" Picker -->
<section class="recipe-picker" aria-label="Recipe finder" data-reveal>
    <h2 class="homepage-section__header">E &#x02BB;ai k&#x0101;kou — What Should I Cook?</h2>

    <div class="picker-progress" aria-hidden="true">
        <span class="picker-progress__dot active"></span>
        <span class="picker-progress__dot"></span>
        <span class="picker-progress__dot"></span>
    </div>

    <!-- Step 1: Time -->
    <div class="picker-step picker-step--time picker-step--active" aria-live="polite">
        <p class="picker-step__prompt">How much time do you have?</p>
        <div class="picker-cards">
            <button class="picker-card" data-time="quick" type="button">
                <span class="picker-card__emoji" aria-hidden="true">&#x26A1;</span>
                <span class="picker-card__label">Quick</span>
                <span class="picker-card__detail">30 minutes or less</span>
                <span class="picker-card__hawaiian" lang="haw">Wikiwiki</span>
            </button>
            <button class="picker-card" data-time="medium" type="button">
                <span class="picker-card__emoji" aria-hidden="true">&#x1F373;</span>
                <span class="picker-card__label">About an Hour</span>
                <span class="picker-card__detail">Worth the effort</span>
                <span class="picker-card__hawaiian" lang="haw">Ho&#x02BB;om&#x0101;kaukau</span>
            </button>
            <button class="picker-card" data-time="long" type="button">
                <span class="picker-card__emoji" aria-hidden="true">&#x1F33A;</span>
                <span class="picker-card__label">Take Your Time</span>
                <span class="picker-card__detail">Low &amp; slow, worth the wait</span>
                <span class="picker-card__hawaiian" lang="haw">Ahonui</span>
            </button>
        </div>
    </div>

    <!-- Step 2: Vibe -->
    <div class="picker-step picker-step--vibe" aria-live="polite">
        <p class="picker-step__prompt">What's the vibe?</p>
        <div class="picker-pills">
            <button class="picker-pill" data-vibe="comfort" type="button">Comfort Me</button>
            <button class="picker-pill" data-vibe="fresh" type="button">Keep It Fresh</button>
            <button class="picker-pill" data-vibe="party" type="button">We're Having People Over</button>
            <button class="picker-pill" data-vibe="surprise" type="button">Surprise Me</button>
        </div>
    </div>

    <!-- Step 3: Result -->
    <div class="picker-step picker-step--result" aria-live="polite">
        <div class="picker-result">
            <img class="picker-result__image" src="" alt="" loading="lazy">
            <span class="picker-result__badge"></span>
            <h3 class="picker-result__title"></h3>
            <p class="picker-result__excerpt"></p>
            <a class="picker-result__link" href="#">Let's Cook &rarr;</a>
        </div>
        <button class="picker-reset" type="button">Pick Again &#x21BB;</button>
    </div>
</section>

<!-- Picker recipe data (read by homepage.js) -->
<script type="application/json" id="picker-recipe-data"><?php echo wp_json_encode($picker_recipes); ?></script>

<!-- Proverb Divider 2 -->
<?php if (isset($proverbs[$proverb_index])) : $p = $proverbs[$proverb_index++]; ?>
<div class="kapa-divider kapa-divider--wave" aria-hidden="true"></div>
<div class="kapa-proverb" data-reveal>
    <span class="kapa-proverb__hawaiian" lang="haw"><?php echo esc_html($p['haw']); ?></span>
    <span class="kapa-proverb__english"><?php echo esc_html($p['en']); ?></span>
</div>
<?php endif; ?>

<!-- 3. Featured Recipes (Time-Aware) -->
<?php
// Query: prioritize time-specific category, fall back to all recipes (cat 26)
$featured_args = [
    'post_type'      => 'post',
    'posts_per_page' => 6,
    'post_status'    => 'publish',
    'cat'            => $time_cat,
    'orderby'        => 'date',
    'order'          => 'DESC',
];
$featured_query = new WP_Query($featured_args);

// Fallback: if time-specific category has fewer than 6 posts, query all recipes
if ($featured_query->found_posts < 6) {
    $featured_query = new WP_Query(array_merge($featured_args, ['cat' => 26]));
}

$featured_ids = [];
$reveal_delay = 0;

if ($featured_query->have_posts()) :
?>
<section class="homepage-section" data-reveal>
    <h2 class="homepage-section__header" data-time-heading>Featured Recipes</h2>
    <div class="homepage-featured__grid">
        <?php while ($featured_query->have_posts()) : $featured_query->the_post();
            $featured_ids[] = get_the_ID();
            $cats = get_the_category();
            $cat  = !empty($cats) ? $cats[0] : null;
        ?>
        <a class="featured-card" href="<?php the_permalink(); ?>" data-reveal data-reveal-delay="<?php echo esc_attr($reveal_delay); ?>">
            <?php if (has_post_thumbnail()) : ?>
                <img class="featured-card__image"
                     src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'medium_large')); ?>"
                     alt="<?php echo esc_attr(get_the_title()); ?>"
                     loading="lazy">
            <?php endif; ?>
            <div class="featured-card__body">
                <?php if ($cat) : ?>
                    <span class="featured-card__category"><?php echo esc_html($cat->name); ?></span>
                <?php endif; ?>
                <h3 class="featured-card__title"><?php the_title(); ?></h3>
                <p class="featured-card__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 15, '...')); ?></p>
            </div>
        </a>
        <?php $reveal_delay += 100; endwhile; ?>
    </div>
    <div class="homepage-section__footer" data-reveal>
        <a class="homepage-section__view-all" href="<?php echo esc_url(get_category_link(26)); ?>">View All Recipes &rarr;</a>
    </div>
</section>
<?php
    wp_reset_postdata();
endif;
?>

<!-- Proverb Divider 3 -->
<?php if (isset($proverbs[$proverb_index])) : $p = $proverbs[$proverb_index++]; ?>
<div class="kapa-divider kapa-divider--zigzag" aria-hidden="true"></div>
<div class="kapa-proverb" data-reveal>
    <span class="kapa-proverb__hawaiian" lang="haw"><?php echo esc_html($p['haw']); ?></span>
    <span class="kapa-proverb__english"><?php echo esc_html($p['en']); ?></span>
</div>
<?php endif; ?>

<!-- 3b. Browse by Category -->
<?php
$category_ids = [860, 866, 862, 859, 873, 861, 874, 107];
$category_tiles = [];

foreach ($category_ids as $cat_id) {
    $cat = get_category($cat_id);
    if (!$cat || is_wp_error($cat) || $cat->count === 0) {
        continue;
    }

    // Get featured image from the latest post in this category
    $tile_image = '';
    $latest = new WP_Query([
        'post_type'      => 'post',
        'posts_per_page' => 1,
        'cat'            => $cat_id,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
    if ($latest->have_posts()) {
        $latest->the_post();
        $tile_image = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
    }
    wp_reset_postdata();

    $category_tiles[] = [
        'name'  => $cat->name,
        'count' => $cat->count,
        'url'   => get_category_link($cat_id),
        'image' => $tile_image,
    ];
}

if (!empty($category_tiles)) :
?>
<section class="homepage-categories" data-reveal>
    <h2 class="homepage-section__header">Browse by Category</h2>
    <div class="homepage-categories__grid">
        <?php foreach ($category_tiles as $tile) : ?>
        <a class="category-tile"
           href="<?php echo esc_url($tile['url']); ?>"
           <?php if ($tile['image']) : ?>
               style="background-image: url('<?php echo esc_url($tile['image']); ?>')"
           <?php endif; ?>>
            <div class="category-tile__overlay"></div>
            <div class="category-tile__content">
                <span class="category-tile__name"><?php echo esc_html($tile['name']); ?></span>
                <span class="category-tile__count"><?php echo esc_html($tile['count'] . ' ' . _n('Recipe', 'Recipes', $tile['count'])); ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- 4. About / Story Teaser -->
<section class="homepage-about">
    <div class="homepage-about__image-wrap" data-reveal="left">
        <img class="homepage-about__image"
             src="<?php echo esc_url($uploads_url . '/author-photo-curtis.png'); ?>"
             alt="Curtis Vaughan in the kitchen"
             loading="lazy">
    </div>
    <div class="homepage-about__text" data-reveal="right">
        <h2 class="homepage-about__heading">Aloha — I'm Curtis</h2>
        <p>Welcome to CurtisJCooks, where I share the authentic flavors and traditions of Hawaiian cuisine. From plate lunches to poke bowls, every recipe here carries the spirit of the islands — born from generations of local cooking, plantation-era fusion, and the aloha that makes Hawaiian food so special.</p>
        <p>Whether you're on the mainland dreaming of island flavors or a local looking for your next kitchen project, I'm glad you're here.</p>
        <a class="homepage-about__link" href="<?php echo esc_url(home_url('/about/')); ?>">Read My Story &rarr;</a>
    </div>
</section>

<!-- Proverb Divider 4 -->
<?php if (isset($proverbs[$proverb_index])) : $p = $proverbs[$proverb_index++]; ?>
<div class="kapa-divider kapa-divider--wave" aria-hidden="true"></div>
<div class="kapa-proverb" data-reveal>
    <span class="kapa-proverb__hawaiian" lang="haw"><?php echo esc_html($p['haw']); ?></span>
    <span class="kapa-proverb__english"><?php echo esc_html($p['en']); ?></span>
</div>
<?php endif; ?>

<!-- 5. Latest Posts (Asymmetric Grid) -->
<?php
$latest_args = [
    'post_type'      => 'post',
    'posts_per_page' => 4,
    'post_status'    => 'publish',
    'post__not_in'   => $featured_ids,
    'orderby'        => 'date',
    'order'          => 'DESC',
];
$latest_query = new WP_Query($latest_args);

if ($latest_query->have_posts()) :
    $latest_count = 0;
?>
<section class="homepage-section" data-reveal>
    <h2 class="homepage-section__header">Latest from the Kitchen</h2>
    <div class="homepage-latest__grid">
        <?php while ($latest_query->have_posts()) : $latest_query->the_post();
            $latest_count++;
            $cats = get_the_category();
            $cat  = !empty($cats) ? $cats[0] : null;

            if ($latest_count === 1) : ?>
                <!-- Lead card -->
                <a class="homepage-latest__lead" href="<?php the_permalink(); ?>" data-reveal>
                    <?php if (has_post_thumbnail()) : ?>
                        <img class="homepage-latest__lead-image"
                             src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'large')); ?>"
                             alt="<?php echo esc_attr(get_the_title()); ?>"
                             loading="lazy">
                    <?php endif; ?>
                    <div class="homepage-latest__lead-body">
                        <?php if ($cat) : ?>
                            <span class="featured-card__category"><?php echo esc_html($cat->name); ?></span>
                        <?php endif; ?>
                        <h3 class="homepage-latest__lead-title"><?php the_title(); ?></h3>
                        <p class="homepage-latest__lead-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 25, '...')); ?></p>
                        <span class="homepage-latest__date"><?php echo esc_html(get_the_date()); ?></span>
                    </div>
                </a>
                <div class="homepage-latest__sidebar">
            <?php else : ?>
                <!-- Small card -->
                <a class="latest-card-sm" href="<?php the_permalink(); ?>" data-reveal data-reveal-delay="<?php echo esc_attr(($latest_count - 2) * 150); ?>">
                    <?php if (has_post_thumbnail()) : ?>
                        <img class="latest-card-sm__thumb"
                             src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'thumbnail')); ?>"
                             alt="<?php echo esc_attr(get_the_title()); ?>"
                             loading="lazy">
                    <?php endif; ?>
                    <div class="latest-card-sm__body">
                        <h4 class="latest-card-sm__title"><?php the_title(); ?></h4>
                        <span class="homepage-latest__date"><?php echo esc_html(get_the_date()); ?></span>
                    </div>
                </a>
            <?php endif; ?>
        <?php endwhile; ?>
                </div><!-- .homepage-latest__sidebar -->
    </div>
</section>
<?php
    wp_reset_postdata();
endif;
?>

<!-- Final Divider -->
<div class="kapa-divider kapa-divider--zigzag" aria-hidden="true"></div>

<!-- 6. Footer -->
<footer class="cjc-footer lava-rock-bg">
    <div class="cjc-footer__wave-border" aria-hidden="true"></div>
    <p>&copy; <?php echo esc_html(date('Y')); ?>
        <a href="<?php echo esc_url(home_url('/')); ?>">CurtisJCooks.com</a>
        &mdash; Authentic Hawaiian Recipes &amp; Island Flavors
    </p>
</footer>

<?php get_footer(); ?>
