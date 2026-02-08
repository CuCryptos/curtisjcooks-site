<?php
/**
 * CLI Helper for creating posts and categories
 * Run from Local's Site Shell: wp eval-file wp-content/themes/suspended-flavor-child/cli-helper.php
 * Or via browser (remove after use): yoursite.local/wp-content/themes/suspended-flavor-child/cli-helper.php?action=setup
 */

// Only allow CLI or authenticated admin
if (php_sapi_name() !== 'cli') {
    require_once dirname(__FILE__) . '/../../../wp-load.php';
    if (!current_user_can('manage_options')) {
        die('Unauthorized');
    }
}

$action = isset($_GET['action']) ? $_GET['action'] : (isset($argv[1]) ? $argv[1] : 'setup');

switch ($action) {
    case 'setup':
        setup_categories();
        break;
    case 'create-posts':
        setup_categories();
        create_all_posts();
        break;
    case 'create-post':
        echo "Use the create_draft_post() function with post data.\n";
        break;
    default:
        echo "Available actions: setup, create-posts\n";
}

/**
 * Create Hawaiian recipe categories if they don't exist
 */
function setup_categories() {
    $categories = [
        'hawaiian-breakfast' => 'Hawaiian Breakfast',
        'pupus-snacks' => 'Pupus & Snacks',
        'island-comfort' => 'Island Comfort',
        'island-drinks' => 'Island Drinks',
        'poke-seafood' => 'Poke & Seafood',
        'tropical-treats' => 'Tropical Treats',
        'top-articles' => 'Top Articles',
    ];

    echo "Setting up categories...\n";

    foreach ($categories as $slug => $name) {
        $existing = get_category_by_slug($slug);

        if ($existing) {
            echo "✓ Category exists: {$name} (ID: {$existing->term_id})\n";
        } else {
            $result = wp_insert_category([
                'cat_name' => $name,
                'category_nicename' => $slug,
            ]);

            if (is_wp_error($result)) {
                echo "✗ Failed to create: {$name} - {$result->get_error_message()}\n";
            } else {
                echo "✓ Created category: {$name} (ID: {$result})\n";
            }
        }
    }

    echo "\nCategories setup complete!\n";
}

/**
 * Create a draft post from provided data
 */
function create_draft_post($data) {
    $defaults = [
        'title' => '',
        'slug' => '',
        'content' => '',
        'categories' => [],
        'meta_description' => '',
        'status' => 'draft',
    ];

    $data = array_merge($defaults, $data);

    if (empty($data['title']) || empty($data['content'])) {
        return new WP_Error('missing_data', 'Title and content are required');
    }

    // Get category IDs
    $category_ids = [];
    foreach ($data['categories'] as $cat_slug) {
        $cat = get_category_by_slug($cat_slug);
        if ($cat) {
            $category_ids[] = $cat->term_id;
        }
    }

    // Create the post
    $post_id = wp_insert_post([
        'post_title' => $data['title'],
        'post_name' => $data['slug'],
        'post_content' => $data['content'],
        'post_status' => $data['status'],
        'post_category' => $category_ids,
        'post_type' => 'post',
    ]);

    if (is_wp_error($post_id)) {
        return $post_id;
    }

    // Set meta description for Yoast or RankMath
    if (!empty($data['meta_description'])) {
        // Yoast SEO
        update_post_meta($post_id, '_yoast_wpseo_metadesc', $data['meta_description']);
        // RankMath
        update_post_meta($post_id, 'rank_math_description', $data['meta_description']);
        // Generic
        update_post_meta($post_id, '_meta_description', $data['meta_description']);
    }

    return $post_id;
}

/**
 * Create all Hawaiian breakfast recipe posts
 */
function create_all_posts() {
    echo "\nCreating Hawaiian Breakfast posts...\n\n";

    $posts = get_hawaiian_breakfast_posts();

    foreach ($posts as $post_data) {
        // Check if post with this slug already exists
        $existing = get_page_by_path($post_data['slug'], OBJECT, 'post');
        if ($existing) {
            echo "⏭ Skipping (exists): {$post_data['title']} (ID: {$existing->ID})\n";
            continue;
        }

        $result = create_draft_post($post_data);

        if (is_wp_error($result)) {
            echo "✗ Failed: {$post_data['title']} - {$result->get_error_message()}\n";
        } else {
            echo "✓ Created: {$post_data['title']} (ID: {$result})\n";
        }
    }

    echo "\nPost creation complete!\n";
}

/**
 * Get all Hawaiian breakfast post data
 */
function get_hawaiian_breakfast_posts() {
    return [
        // Post 1: Portuguese Sausage and Eggs
        [
            'title' => 'Portuguese Sausage and Eggs: The Ultimate Hawaiian Breakfast',
            'slug' => 'portuguese-sausage-eggs-hawaiian-breakfast',
            'categories' => ['hawaiian-breakfast'],
            'meta_description' => 'Learn to make authentic Portuguese sausage and eggs, a beloved Hawaiian breakfast staple. This easy recipe brings the flavors of the islands to your morning table.',
            'content' => '<!-- wp:paragraph -->
<p>If you\'ve ever visited Hawaii and ordered breakfast at a local diner, you\'ve probably seen Portuguese sausage on the menu. This smoky, slightly sweet sausage is a cornerstone of Hawaiian breakfast cuisine, and when paired with perfectly fried eggs and steamed rice, it creates one of the most satisfying morning meals you\'ll ever taste.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>The History of Portuguese Sausage in Hawaii</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Portuguese immigrants arrived in Hawaii in the late 1800s to work on sugar plantations, bringing with them their culinary traditions. Linguiça, the traditional Portuguese sausage, was adapted to local tastes and became what we now know as Hawaiian Portuguese sausage. Unlike its mainland counterpart, Hawaiian Portuguese sausage is typically milder, slightly sweeter, and has a distinctive smoky flavor that pairs perfectly with eggs and rice.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>What Makes This Breakfast Special</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The magic of Portuguese sausage and eggs lies in its simplicity. The sausage releases its flavorful oils as it cooks, which you then use to fry your eggs. This creates eggs with crispy, lacy edges and a rich, smoky flavor that no amount of butter could replicate. Add a scoop of hot steamed rice to soak up all those delicious juices, and you\'ve got a breakfast that will keep you energized all morning.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Where to Find Portuguese Sausage</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>If you\'re on the mainland, look for brands like Aidells or Silva at your local grocery store. In Hawaii, the go-to brands are Redondo\'s and Gouvea\'s. You can also find Portuguese sausage online through Amazon or specialty Hawaiian food retailers. In a pinch, linguiça from your local store will work, though the flavor profile will be slightly different.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Tips for Perfect Results</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
<li><strong>Slice thickness matters</strong> - About 1/4 inch thick gives you the best ratio of crispy edges to juicy center</li>
<li><strong>Don\'t crowd the pan</strong> - Give each slice room to get proper contact with the pan surface</li>
<li><strong>Medium heat is key</strong> - Too high and the sugars in the sausage will burn before the center cooks</li>
<li><strong>Save that fat</strong> - The rendered fat is liquid gold for cooking your eggs</li>
<li><strong>Fresh rice is best</strong> - Day-old rice works for fried rice, but this breakfast deserves freshly steamed rice</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Serving Suggestions</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>While this dish is perfect on its own, here are some ways to make it even better:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>Add a drizzle of Sriracha or your favorite hot sauce</li>
<li>Include a side of kimchi for a Korean-Hawaiian fusion twist</li>
<li>Top with green onions for freshness</li>
<li>Serve with a side of fresh papaya or pineapple</li>
<li>Add a splash of shoyu (soy sauce) on your rice</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Make It a Full Hawaiian Breakfast</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>To create an authentic Hawaiian breakfast plate, serve your Portuguese sausage and eggs with:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>Two scoops of white rice</li>
<li>A scoop of macaroni salad</li>
<li>Fresh tropical fruit</li>
<li>A cup of Kona coffee</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>This combination might seem unusual if you\'re not familiar with Hawaiian cuisine, but trust me—once you try it, you\'ll understand why locals have been eating this way for generations.</p>
<!-- /wp:paragraph -->

<!-- wp:tasty/tasty-recipe -->
<div class="tasty-recipes-entry-content">
<h2>Portuguese Sausage and Eggs</h2>
<p class="tasty-recipes-description">A classic Hawaiian breakfast featuring smoky Portuguese sausage, perfectly fried eggs, and steamed rice. This simple yet satisfying meal brings the authentic flavors of island mornings to your kitchen.</p>

<h3>Details</h3>
<ul>
<li><strong>Prep Time:</strong> 5 minutes</li>
<li><strong>Cook Time:</strong> 15 minutes</li>
<li><strong>Total Time:</strong> 20 minutes</li>
<li><strong>Servings:</strong> 2</li>
<li><strong>Cuisine:</strong> Hawaiian</li>
<li><strong>Course:</strong> Breakfast</li>
</ul>

<h3>Ingredients</h3>
<ul>
<li>8 oz Portuguese sausage (about 1/2 package)</li>
<li>4 large eggs</li>
<li>2 cups cooked white rice, hot</li>
<li>Salt and pepper to taste</li>
<li>Green onions for garnish (optional)</li>
<li>Shoyu (soy sauce) for serving (optional)</li>
</ul>

<h3>Instructions</h3>
<ol>
<li>Slice the Portuguese sausage into 1/4-inch thick rounds. You should get about 12-16 slices.</li>
<li>Heat a large skillet or cast iron pan over medium heat. No oil needed—the sausage will release its own fat.</li>
<li>Add the sausage slices in a single layer. Cook for 2-3 minutes per side until golden brown and slightly crispy on the edges.</li>
<li>Remove the sausage to a plate and keep warm. Leave the rendered fat in the pan.</li>
<li>Crack the eggs into the hot pan with the sausage fat. For sunny-side up, cook until whites are set but yolks are still runny, about 3-4 minutes. For over-easy, flip and cook 30 seconds more.</li>
<li>Season eggs with salt and pepper.</li>
<li>Divide hot rice between two plates. Top with sausage slices and eggs.</li>
<li>Garnish with sliced green onions if desired. Serve immediately with shoyu on the side.</li>
</ol>

<h3>Notes</h3>
<ul>
<li>Redondo\'s and Gouvea\'s are authentic Hawaiian brands. On the mainland, look for Silva or Aidells Portuguese sausage.</li>
<li>For extra flavor, add a splash of the rendered sausage fat over your rice.</li>
<li>This dish is traditionally served with a scoop of macaroni salad on the side.</li>
</ul>

<h3>Nutrition (per serving)</h3>
<ul>
<li>Calories: 580</li>
<li>Protein: 28g</li>
<li>Carbohydrates: 45g</li>
<li>Fat: 32g</li>
</ul>
</div>
<!-- /wp:tasty/tasty-recipe -->

<!-- wp:paragraph -->
<p>Portuguese sausage and eggs isn\'t just a meal—it\'s a taste of Hawaiian morning culture. Whether you\'re reminiscing about a trip to the islands or discovering this combination for the first time, this breakfast will bring a little aloha to your day.</p>
<!-- /wp:paragraph -->',
        ],

        // Post 2: Spam and Rice
        [
            'title' => 'Spam and Rice: Hawaii\'s Iconic Breakfast Comfort Food',
            'slug' => 'spam-and-rice-hawaiian-breakfast',
            'categories' => ['hawaiian-breakfast'],
            'meta_description' => 'Discover why Spam and rice is Hawaii\'s most beloved breakfast. Learn the authentic way to prepare this island classic with perfectly crispy Spam and fluffy rice.',
            'content' => '<!-- wp:paragraph -->
<p>In Hawaii, Spam isn\'t just acceptable—it\'s celebrated. The islands consume more Spam per capita than anywhere else in the world, and for good reason. When prepared properly, this humble canned meat transforms into something magical, especially when paired with hot steamed rice and a runny fried egg.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Why Hawaii Loves Spam</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Spam arrived in Hawaii during World War II when fresh meat was scarce and military rations were abundant. What started as a necessity became a beloved tradition. Today, Hawaii consumes over 7 million cans of Spam annually—that\'s about 5 cans per person per year. You\'ll find Spam on menus everywhere from McDonald\'s to fine dining restaurants.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>The Secret to Perfect Spam</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The key to delicious Spam is getting those edges perfectly crispy while keeping the center tender. This requires slicing it to the right thickness and using medium-high heat. Too thin and it dries out; too thick and you won\'t get that satisfying crunch. A quarter-inch is the sweet spot.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Many locals also like to glaze their Spam with a mixture of shoyu (soy sauce) and sugar, creating a sweet-savory caramelized coating that\'s absolutely irresistible.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Spam Varieties for Your Breakfast</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>While classic Spam works great, Hawaii has inspired several varieties worth trying:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>Spam Less Sodium</strong> - 25% less salt, good if you\'re watching sodium intake</li>
<li><strong>Spam Tocino</strong> - Filipino-inspired sweet cured flavor</li>
<li><strong>Spam Teriyaki</strong> - Pre-glazed with teriyaki flavor</li>
<li><strong>Spam Portuguese Sausage</strong> - Combines two Hawaiian breakfast favorites</li>
<li><strong>Spam with Bacon</strong> - Extra smoky and rich</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Building the Perfect Plate</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The classic Spam breakfast plate includes:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>3-4 slices of pan-fried Spam</li>
<li>Two fried eggs (over-easy or sunny-side up)</li>
<li>Two scoops of hot white rice</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>The key is eating it all together—break your yolk over the rice, add a piece of Spam, and get a bit of everything in each bite. That combination of salty, savory, and rich is what makes this breakfast so satisfying.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Pro Tips from the Islands</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
<li><strong>Dry your Spam</strong> - Pat slices with paper towel before frying for better browning</li>
<li><strong>Use a non-stick pan</strong> - Spam can stick without enough fat rendered</li>
<li><strong>Don\'t flip too early</strong> - Let it develop a crust before turning</li>
<li><strong>Season after cooking</strong> - Spam is already salty, taste before adding more</li>
<li><strong>Try it with furikake</strong> - This rice seasoning adds amazing umami flavor</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Beyond Breakfast</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Once you\'ve mastered Spam and rice for breakfast, you\'re ready to try Spam musubi—Hawaii\'s favorite grab-and-go snack. It\'s essentially Spam sushi: grilled Spam on a block of rice, wrapped with nori seaweed. You\'ll find them at every convenience store, gas station, and grocery store in Hawaii.</p>
<!-- /wp:paragraph -->

<!-- wp:tasty/tasty-recipe -->
<div class="tasty-recipes-entry-content">
<h2>Spam and Rice Hawaiian Breakfast</h2>
<p class="tasty-recipes-description">The ultimate Hawaiian comfort food breakfast featuring perfectly crispy pan-fried Spam, fluffy white rice, and fried eggs. Simple, satisfying, and authentically island-style.</p>

<h3>Details</h3>
<ul>
<li><strong>Prep Time:</strong> 5 minutes</li>
<li><strong>Cook Time:</strong> 10 minutes</li>
<li><strong>Total Time:</strong> 15 minutes</li>
<li><strong>Servings:</strong> 2</li>
<li><strong>Cuisine:</strong> Hawaiian</li>
<li><strong>Course:</strong> Breakfast</li>
</ul>

<h3>Ingredients</h3>
<h4>For the Spam</h4>
<ul>
<li>1 can (12 oz) Spam Classic or Less Sodium</li>
<li>1 tablespoon vegetable oil (optional)</li>
<li>1 tablespoon shoyu (soy sauce) (optional, for glaze)</li>
<li>1 teaspoon sugar (optional, for glaze)</li>
</ul>

<h4>For the Plate</h4>
<ul>
<li>4 large eggs</li>
<li>2 cups hot cooked white rice</li>
<li>Furikake rice seasoning (optional)</li>
<li>Sliced green onions (optional)</li>
</ul>

<h3>Instructions</h3>
<ol>
<li>Remove Spam from can and slice into 8 pieces, about 1/4-inch thick each.</li>
<li>Pat slices dry with paper towels for better browning.</li>
<li>Heat a large non-stick skillet over medium-high heat. Add oil if using, or cook Spam in its own rendered fat.</li>
<li>Add Spam slices in a single layer. Cook without moving for 2-3 minutes until golden brown on the bottom.</li>
<li>Flip and cook another 2-3 minutes until the second side is crispy.</li>
<li>Optional: In the last minute, add shoyu and sugar to pan. Flip Spam to coat both sides with the glaze. Remove when caramelized.</li>
<li>In the same pan (add a little oil if needed), fry eggs to your liking. Sunny-side up or over-easy are traditional.</li>
<li>Divide rice between two plates (use an ice cream scoop for the classic \"scoop\" shape).</li>
<li>Add 4 slices of Spam and 2 eggs to each plate.</li>
<li>Sprinkle furikake over rice and garnish with green onions if desired.</li>
</ol>

<h3>Notes</h3>
<ul>
<li>For extra crispy Spam, slice thinner (1/8 inch) but watch carefully to prevent burning.</li>
<li>Spam Less Sodium is recommended if you\'re sensitive to salt.</li>
<li>In Hawaii, this is often served with a side of kimchi or pickled vegetables.</li>
</ul>

<h3>Nutrition (per serving)</h3>
<ul>
<li>Calories: 650</li>
<li>Protein: 32g</li>
<li>Carbohydrates: 48g</li>
<li>Fat: 38g</li>
</ul>
</div>
<!-- /wp:tasty/tasty-recipe -->

<!-- wp:paragraph -->
<p>Don\'t knock it until you try it. Spam and rice might not sound gourmet, but there\'s a reason millions of people in Hawaii eat it regularly. It\'s honest, satisfying food that fuels hard-working mornings—and once you taste properly prepared Spam with hot rice and runny eggs, you\'ll understand the love.</p>
<!-- /wp:paragraph -->',
        ],

        // Post 3: Hawaiian Acai Bowl
        [
            'title' => 'Hawaiian Acai Bowl: Tropical Breakfast Paradise',
            'slug' => 'hawaiian-acai-bowl-recipe',
            'categories' => ['hawaiian-breakfast'],
            'meta_description' => 'Make an authentic Hawaiian acai bowl at home with this easy recipe. Loaded with tropical fruits, granola, and honey, it\'s like sunshine in a bowl.',
            'content' => '<!-- wp:paragraph -->
<p>Walk down any street in Honolulu and you\'ll spot people carrying purple-filled bowls topped with colorful fruits. Acai bowls have become synonymous with Hawaiian beach culture, and for good reason—they\'re refreshing, nutritious, and absolutely delicious. While acai berries actually come from Brazil, Hawaii has made the acai bowl its own with tropical toppings and island flair.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>What Makes Hawaiian Acai Bowls Special</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The Hawaiian twist on acai bowls comes from the toppings. While mainland versions might include strawberries and blueberries, Hawaiian bowls showcase local tropical fruits: fresh papaya, lilikoi (passion fruit), coconut, and of course, pineapple. The result is a bowl that tastes like a tropical vacation.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>The Perfect Acai Base</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The secret to a great acai bowl is the base consistency. It should be thick like soft-serve ice cream—thick enough to support toppings without them sinking, but smooth enough to eat with a spoon. This means using frozen fruit and minimal liquid. Too much liquid and you\'ll end up with a smoothie; too little and your blender will struggle.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>For the acai itself, look for frozen acai packets or puree. Sambazon is the most widely available brand and works well. Avoid acai juice or powder for bowls—you need the frozen puree to achieve the right texture.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Essential Hawaiian Toppings</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A proper Hawaiian acai bowl includes a balance of textures and flavors:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>Crunchy</strong> - Granola, toasted coconut flakes, macadamia nuts</li>
<li><strong>Fresh</strong> - Sliced banana, strawberries, blueberries</li>
<li><strong>Tropical</strong> - Fresh pineapple, mango, papaya, coconut</li>
<li><strong>Sweet</strong> - Local honey, agave, or coconut nectar drizzle</li>
<li><strong>Extra</strong> - Chia seeds, hemp hearts, bee pollen, cacao nibs</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Where to Find Ingredients</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Most grocery stores now carry frozen acai packets in the frozen fruit section. For authentic Hawaiian-style toppings:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>Macadamia nuts</strong> - Available at most grocery stores, or order Hawaiian-grown online</li>
<li><strong>Lilikoi (passion fruit)</strong> - Check Asian grocery stores or farmer\'s markets</li>
<li><strong>Coconut flakes</strong> - Look for unsweetened, large flake coconut</li>
<li><strong>Local honey</strong> - Hawaiian honey varieties like macadamia blossom are available online</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Tips for the Best Bowl</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
<li><strong>Freeze your fruit</strong> - Fresh bananas will make a watery base. Freeze them first.</li>
<li><strong>Use a powerful blender</strong> - A high-speed blender like Vitamix makes the smoothest base</li>
<li><strong>Work quickly</strong> - The base starts melting immediately, so prep toppings first</li>
<li><strong>Chill your bowl</strong> - A frozen bowl keeps everything cold longer</li>
<li><strong>Layer strategically</strong> - Put granola on last so it stays crunchy</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Health Benefits</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Acai bowls aren\'t just delicious—they\'re packed with nutrition:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>Acai berries are rich in antioxidants, particularly anthocyanins</li>
<li>Bananas provide potassium and natural energy</li>
<li>Tropical fruits offer vitamin C and digestive enzymes</li>
<li>Granola adds fiber and sustained energy</li>
<li>Nuts provide healthy fats and protein</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>That said, be mindful of portion sizes and added sweeteners. A large acai bowl loaded with granola and honey can pack 600+ calories.</p>
<!-- /wp:paragraph -->

<!-- wp:tasty/tasty-recipe -->
<div class="tasty-recipes-entry-content">
<h2>Hawaiian Acai Bowl</h2>
<p class="tasty-recipes-description">A thick, creamy acai bowl loaded with tropical Hawaiian fruits, crunchy granola, and toasted coconut. This refreshing breakfast tastes like a beach vacation in a bowl.</p>

<h3>Details</h3>
<ul>
<li><strong>Prep Time:</strong> 10 minutes</li>
<li><strong>Total Time:</strong> 10 minutes</li>
<li><strong>Servings:</strong> 2</li>
<li><strong>Cuisine:</strong> Hawaiian</li>
<li><strong>Course:</strong> Breakfast</li>
</ul>

<h3>Ingredients</h3>
<h4>Acai Base</h4>
<ul>
<li>2 packets (200g) frozen acai puree</li>
<li>1 large frozen banana</li>
<li>1/2 cup frozen pineapple chunks</li>
<li>1/4 cup coconut milk or almond milk</li>
<li>1 tablespoon honey (optional)</li>
</ul>

<h4>Toppings</h4>
<ul>
<li>1/2 cup granola</li>
<li>1 banana, sliced</li>
<li>1/2 cup fresh pineapple chunks</li>
<li>1/2 cup fresh strawberries, sliced</li>
<li>2 tablespoons toasted coconut flakes</li>
<li>2 tablespoons macadamia nuts, roughly chopped</li>
<li>2 tablespoons local honey for drizzling</li>
<li>Fresh mint for garnish (optional)</li>
</ul>

<h3>Instructions</h3>
<ol>
<li>Run the frozen acai packets under warm water for 10-15 seconds to slightly soften. Break into pieces.</li>
<li>Add acai pieces, frozen banana, frozen pineapple, and coconut milk to a high-speed blender.</li>
<li>Blend on low, using the tamper to push ingredients down. Increase speed gradually until smooth but very thick. Add honey if desired. The mixture should be thicker than a smoothie.</li>
<li>If the mixture is too thick, add coconut milk 1 tablespoon at a time. If too thin, add more frozen fruit.</li>
<li>Divide the acai base between two chilled bowls.</li>
<li>Arrange toppings in sections on top of the base: banana slices, pineapple, strawberries, coconut, and macadamia nuts.</li>
<li>Sprinkle granola over the top.</li>
<li>Drizzle with honey and garnish with fresh mint if using.</li>
<li>Serve immediately with a spoon.</li>
</ol>

<h3>Notes</h3>
<ul>
<li>For an extra thick base, freeze your bowl for 10 minutes before adding the acai mixture.</li>
<li>Substitute any tropical fruits you have: mango, papaya, kiwi, or dragon fruit all work well.</li>
<li>Make it vegan by using agave or maple syrup instead of honey.</li>
<li>Add a tablespoon of almond butter to the base for extra protein and creaminess.</li>
</ul>

<h3>Nutrition (per serving)</h3>
<ul>
<li>Calories: 420</li>
<li>Protein: 8g</li>
<li>Carbohydrates: 72g</li>
<li>Fat: 14g</li>
<li>Fiber: 10g</li>
</ul>
</div>
<!-- /wp:tasty/tasty-recipe -->

<!-- wp:paragraph -->
<p>There\'s something special about eating an acai bowl—it feels indulgent and healthy at the same time. Make this Hawaiian version at home and close your eyes with each bite. You might just hear the waves.</p>
<!-- /wp:paragraph -->',
        ],

        // Post 4: Hawaiian Sweet Bread French Toast
        [
            'title' => 'Hawaiian Sweet Bread French Toast: Tropical Breakfast Indulgence',
            'slug' => 'hawaiian-sweet-bread-french-toast',
            'categories' => ['hawaiian-breakfast'],
            'meta_description' => 'Transform Hawaiian sweet bread into the most decadent French toast you\'ve ever tasted. This tropical breakfast recipe features thick-cut bread soaked in coconut custard.',
            'content' => '<!-- wp:paragraph -->
<p>If you\'ve never made French toast with Hawaiian sweet bread, you\'re missing out on one of life\'s greatest breakfast pleasures. The soft, slightly sweet bread soaks up the custard like a sponge while developing a caramelized, almost brioche-like crust. It\'s French toast elevated to an entirely new level.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Why Hawaiian Sweet Bread Makes the Best French Toast</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>King\'s Hawaiian bread was created in Hilo in 1950 by Robert Taira, who used a family recipe brought from Japan. The bread\'s unique characteristics make it perfect for French toast:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>Soft, pillowy texture</strong> - Absorbs custard without falling apart</li>
<li><strong>Natural sweetness</strong> - Caramelizes beautifully when cooked</li>
<li><strong>Rich, eggy crumb</strong> - Creates a creamy interior</li>
<li><strong>Sturdy structure</strong> - Holds up to soaking and flipping</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>The Coconut Custard Secret</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>While regular French toast custard is delicious, adding coconut milk takes this dish into tropical territory. The coconut adds richness and a subtle flavor that complements the sweet bread perfectly. Combined with a hint of vanilla and cinnamon, it creates a custard that transforms each slice into something extraordinary.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Choosing Your Bread</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>For the best results, use the round King\'s Hawaiian loaf rather than the dinner rolls. The loaf allows you to cut thick slices (about 1 inch) that can soak up maximum custard while staying intact. If you can only find rolls, cut them in half horizontally and press down slightly to flatten.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Pro tip: Day-old bread actually works better than fresh. Slightly stale bread absorbs more custard without becoming soggy. If your bread is fresh, leave slices out for an hour or toast them lightly in a 300°F oven for 5 minutes.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Tropical Topping Ideas</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Take your Hawaiian French toast over the top with these island-inspired toppings:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>Coconut Syrup</strong> - Available in stores or make your own with coconut cream and sugar</li>
<li><strong>Macadamia Nuts</strong> - Toasted and roughly chopped for crunch</li>
<li><strong>Fresh Tropical Fruit</strong> - Pineapple, mango, papaya, or passion fruit</li>
<li><strong>Toasted Coconut</strong> - Adds texture and intensifies the coconut flavor</li>
<li><strong>Lilikoi Butter</strong> - Passion fruit curd that melts into pools of tangy sweetness</li>
<li><strong>Whipped Cream</strong> - Light and fluffy, flavored with vanilla</li>
<li><strong>Haupia</strong> - Coconut pudding, traditional Hawaiian style</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Cooking Tips</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
<li><strong>Medium-low heat</strong> - Higher heat will burn the outside before the inside cooks</li>
<li><strong>Don\'t skip the soaking</strong> - Each slice needs at least 30 seconds per side to absorb custard</li>
<li><strong>Use butter AND oil</strong> - Butter for flavor, oil to prevent burning</li>
<li><strong>Don\'t press down</strong> - Let the bread cook undisturbed for even browning</li>
<li><strong>Keep warm in oven</strong> - Set finished slices on a rack in a 200°F oven while cooking the rest</li>
</ul>
<!-- /wp:list -->

<!-- wp:tasty/tasty-recipe -->
<div class="tasty-recipes-entry-content">
<h2>Hawaiian Sweet Bread French Toast</h2>
<p class="tasty-recipes-description">Thick slices of Hawaiian sweet bread soaked in coconut custard and griddled to golden perfection. Topped with tropical fruits, macadamia nuts, and coconut syrup for the ultimate island breakfast.</p>

<h3>Details</h3>
<ul>
<li><strong>Prep Time:</strong> 10 minutes</li>
<li><strong>Cook Time:</strong> 20 minutes</li>
<li><strong>Total Time:</strong> 30 minutes</li>
<li><strong>Servings:</strong> 4</li>
<li><strong>Cuisine:</strong> Hawaiian</li>
<li><strong>Course:</strong> Breakfast</li>
</ul>

<h3>Ingredients</h3>
<h4>Coconut Custard</h4>
<ul>
<li>4 large eggs</li>
<li>1/2 cup coconut milk (full-fat)</li>
<li>1/4 cup whole milk</li>
<li>2 tablespoons sugar</li>
<li>1 teaspoon vanilla extract</li>
<li>1/2 teaspoon cinnamon</li>
<li>Pinch of salt</li>
</ul>

<h4>French Toast</h4>
<ul>
<li>1 loaf King\'s Hawaiian Sweet Bread, cut into 8 thick slices (about 1 inch)</li>
<li>3 tablespoons butter, divided</li>
<li>1 tablespoon vegetable oil</li>
</ul>

<h4>Toppings</h4>
<ul>
<li>1 cup fresh pineapple, diced</li>
<li>2 bananas, sliced</li>
<li>1/4 cup macadamia nuts, toasted and chopped</li>
<li>1/4 cup toasted coconut flakes</li>
<li>Coconut syrup or maple syrup</li>
<li>Powdered sugar for dusting</li>
</ul>

<h3>Instructions</h3>
<ol>
<li>Preheat oven to 200°F and place a baking rack on a sheet pan.</li>
<li>In a shallow dish, whisk together eggs, coconut milk, whole milk, sugar, vanilla, cinnamon, and salt until well combined.</li>
<li>Heat a large skillet or griddle over medium-low heat. Add 1 tablespoon butter and the oil.</li>
<li>Dip each bread slice in the custard, letting it soak for 30 seconds per side. Don\'t rush this step.</li>
<li>Place soaked bread in the pan (work in batches, don\'t overcrowd). Cook for 3-4 minutes until golden brown on the bottom.</li>
<li>Flip and cook another 3-4 minutes until the second side is golden and the center is cooked through.</li>
<li>Transfer finished slices to the oven to keep warm. Add more butter between batches as needed.</li>
<li>To serve, stack 2 slices per plate. Top with fresh pineapple, banana slices, macadamia nuts, and toasted coconut.</li>
<li>Drizzle generously with coconut syrup and dust with powdered sugar.</li>
</ol>

<h3>Notes</h3>
<ul>
<li>If you can\'t find King\'s Hawaiian loaf, brioche or challah are good substitutes.</li>
<li>Make coconut syrup by simmering 1 cup coconut cream with 1/2 cup sugar until slightly thickened.</li>
<li>For extra decadence, top with a dollop of whipped cream or vanilla ice cream.</li>
</ul>

<h3>Nutrition (per serving)</h3>
<ul>
<li>Calories: 520</li>
<li>Protein: 14g</li>
<li>Carbohydrates: 58g</li>
<li>Fat: 26g</li>
</ul>
</div>
<!-- /wp:tasty/tasty-recipe -->

<!-- wp:paragraph -->
<p>This Hawaiian Sweet Bread French Toast is perfect for weekend mornings when you have time to enjoy breakfast slowly. The combination of pillowy bread, rich custard, and tropical toppings makes every bite feel like a celebration. It\'s also impressive enough to serve for brunch with guests—they\'ll think you worked much harder than you actually did.</p>
<!-- /wp:paragraph -->',
        ],

        // Post 5: Eggs Benedict with Kalua Pork
        [
            'title' => 'Hawaiian Eggs Benedict with Kalua Pork',
            'slug' => 'hawaiian-eggs-benedict-kalua-pork',
            'categories' => ['hawaiian-breakfast'],
            'meta_description' => 'A Hawaiian twist on classic Eggs Benedict featuring smoky kalua pork, perfectly poached eggs, and rich hollandaise on a crispy English muffin. Brunch perfection.',
            'content' => '<!-- wp:paragraph -->
<p>Take the classic brunch dish and give it an island makeover. Instead of Canadian bacon, we\'re using tender, smoky kalua pork—the same slow-cooked pork you\'d find at a Hawaiian luau. The salty, deeply flavored meat pairs perfectly with runny poached eggs and creamy hollandaise, creating a dish that\'s become a signature at Hawaiian brunch spots.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>What is Kalua Pork?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Traditionally, kalua pork is made by cooking a whole pig in an imu—an underground oven lined with hot lava rocks and banana leaves. The pork cooks slowly for hours, absorbing smoky flavors and becoming incredibly tender. The meat is then shredded and seasoned simply with Hawaiian sea salt.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>For home cooks, we can achieve similar results using a slow cooker with liquid smoke. It\'s not quite the same as imu-cooked pork, but it\'s delicious and practical. Many grocery stores also sell pre-made kalua pork—just look for it near the pulled pork.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>The Art of Poaching Eggs</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Perfectly poached eggs are essential for Eggs Benedict. Here are the keys to success:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>Use fresh eggs</strong> - The whites hold together better</li>
<li><strong>Vinegar helps</strong> - A splash of white vinegar helps the whites set faster</li>
<li><strong>Gentle simmer</strong> - Boiling water will tear the eggs apart</li>
<li><strong>Create a whirlpool</strong> - Swirling the water helps wrap the white around the yolk</li>
<li><strong>3 minutes is perfect</strong> - For runny yolks with set whites</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>Don\'t stress if your first few attempts aren\'t picture-perfect. Even \"ugly\" poached eggs taste delicious under hollandaise.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Hollandaise Made Easy</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Hollandaise has a reputation for being difficult, but it\'s actually straightforward once you understand the technique. The key is controlling temperature—too hot and the eggs scramble, too cool and the sauce won\'t emulsify.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>The blender method (included in the recipe) is nearly foolproof. Hot butter is streamed into egg yolks while the blender runs, creating a silky sauce in under a minute. It\'s the method most restaurants use.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Building the Perfect Bite</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The order matters for the best experience:</p>
<!-- /wp:paragraph -->

<!-- wp:list {"ordered":true} -->
<ol>
<li><strong>Crispy English muffin</strong> - Toast until golden for texture contrast</li>
<li><strong>Butter the muffin</strong> - A thin layer adds richness</li>
<li><strong>Pile on the pork</strong> - Generous portions, slightly warm</li>
<li><strong>Poached egg on top</strong> - Centered for even yolk distribution</li>
<li><strong>Hollandaise cascade</strong> - Drizzled over everything</li>
<li><strong>Garnish</strong> - Green onions and a crack of black pepper</li>
</ol>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Making Kalua Pork at Home</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>If you want to make your own kalua pork (which I highly recommend), here\'s a simple slow cooker method:</p>
<!-- /wp:paragraph -->

<!-- wp:list {"ordered":true} -->
<ol>
<li>Score a 4-5 lb pork butt (shoulder) all over with a knife</li>
<li>Rub with 2 tablespoons Hawaiian sea salt and 2 tablespoons liquid smoke</li>
<li>Wrap in banana leaves (or skip if unavailable)</li>
<li>Cook on low for 16-20 hours until falling apart</li>
<li>Shred and season with more salt to taste</li>
</ol>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>This makes enough pork for multiple meals—use leftovers for rice bowls, tacos, or sandwiches.</p>
<!-- /wp:paragraph -->

<!-- wp:tasty/tasty-recipe -->
<div class="tasty-recipes-entry-content">
<h2>Hawaiian Eggs Benedict with Kalua Pork</h2>
<p class="tasty-recipes-description">Classic Eggs Benedict gets an island upgrade with smoky, tender kalua pork. Perfectly poached eggs and silky hollandaise make this the ultimate Hawaiian brunch dish.</p>

<h3>Details</h3>
<ul>
<li><strong>Prep Time:</strong> 15 minutes</li>
<li><strong>Cook Time:</strong> 15 minutes</li>
<li><strong>Total Time:</strong> 30 minutes</li>
<li><strong>Servings:</strong> 4</li>
<li><strong>Cuisine:</strong> Hawaiian</li>
<li><strong>Course:</strong> Breakfast, Brunch</li>
</ul>

<h3>Ingredients</h3>
<h4>Blender Hollandaise</h4>
<ul>
<li>3 large egg yolks</li>
<li>1 tablespoon fresh lemon juice</li>
<li>1/2 teaspoon Dijon mustard</li>
<li>1/2 cup (1 stick) unsalted butter, melted and hot</li>
<li>Pinch of cayenne pepper</li>
<li>Salt to taste</li>
</ul>

<h4>Poached Eggs</h4>
<ul>
<li>8 large eggs, very fresh</li>
<li>2 tablespoons white vinegar</li>
<li>Water for poaching</li>
</ul>

<h4>Assembly</h4>
<ul>
<li>4 English muffins, split</li>
<li>2 tablespoons butter, softened</li>
<li>2 cups kalua pork, warmed (store-bought or homemade)</li>
<li>Sliced green onions for garnish</li>
<li>Freshly cracked black pepper</li>
</ul>

<h3>Instructions</h3>
<h4>Make the Hollandaise</h4>
<ol>
<li>Add egg yolks, lemon juice, mustard, and cayenne to a blender.</li>
<li>Blend on low speed for about 5 seconds to combine.</li>
<li>With the blender running on low, slowly drizzle in the hot melted butter in a thin, steady stream. This should take about 30 seconds.</li>
<li>The sauce will emulsify and thicken. Season with salt to taste.</li>
<li>Transfer to a small bowl and cover with plastic wrap touching the surface. Keep warm.</li>
</ol>

<h4>Poach the Eggs</h4>
<ol>
<li>Fill a large, deep pan with about 3 inches of water. Add vinegar and bring to a gentle simmer (tiny bubbles, not a rolling boil).</li>
<li>Crack each egg into a small bowl first (this helps with a clean drop).</li>
<li>Stir the water to create a gentle whirlpool. Slide in an egg. Cook for 3 minutes for runny yolks.</li>
<li>Remove with a slotted spoon and drain on a paper towel-lined plate. Repeat with remaining eggs. (You can poach 2-3 at a time once you\'re comfortable.)</li>
</ol>

<h4>Assemble</h4>
<ol>
<li>Toast English muffins until golden. Spread with butter.</li>
<li>Place two muffin halves on each plate.</li>
<li>Top each half with a generous portion of warm kalua pork.</li>
<li>Place a poached egg on top of the pork.</li>
<li>Spoon hollandaise generously over each egg.</li>
<li>Garnish with green onions and black pepper. Serve immediately.</li>
</ol>

<h3>Notes</h3>
<ul>
<li>If hollandaise gets too thick, whisk in a teaspoon of warm water.</li>
<li>Eggs can be poached up to an hour ahead and kept in cold water. Reheat in simmering water for 30 seconds before serving.</li>
<li>No kalua pork? Use pulled pork with a few drops of liquid smoke, or substitute ham.</li>
<li>Add a slice of fresh tomato or sautéed spinach for extra vegetables.</li>
</ul>

<h3>Nutrition (per serving, 2 eggs)</h3>
<ul>
<li>Calories: 680</li>
<li>Protein: 38g</li>
<li>Carbohydrates: 28g</li>
<li>Fat: 46g</li>
</ul>
</div>
<!-- /wp:tasty/tasty-recipe -->

<!-- wp:paragraph -->
<p>Hawaiian Eggs Benedict with Kalua Pork is proof that sometimes the best dishes come from blending traditions. The smoky pork brings something special that Canadian bacon simply can\'t match. Once you try this version, you might never go back to the original.</p>
<!-- /wp:paragraph -->',
        ],
    ];
}

// If running from CLI with WP-CLI loaded
if (defined('WP_CLI') && WP_CLI) {
    setup_categories();
}
