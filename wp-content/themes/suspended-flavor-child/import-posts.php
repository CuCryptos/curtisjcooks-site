<?php
/**
 * CurtisJCooks.com - Bulk Post Import Script
 *
 * Run via browser (while logged in as admin):
 * https://curtisjcookscom1.local/wp-content/themes/suspended-flavor-child/import-posts.php
 *
 * Or via WP-CLI:
 * cd ~/Local\ Sites/curtisjcookscom1/app/public
 * wp eval-file wp-content/themes/suspended-flavor-child/import-posts.php
 *
 * All posts are created as DRAFTS so you can review before publishing.
 */

// Load WordPress if running via browser
if (!defined('ABSPATH')) {
    require_once dirname(__FILE__) . '/../../../wp-load.php';

    // Only allow logged-in admins
    if (!current_user_can('manage_options')) {
        die('Unauthorized - you must be logged in as an administrator.');
    }

    // Output as plain text for browser
    header('Content-Type: text/plain; charset=utf-8');
}

echo "===========================================\n";
echo "CurtisJCooks.com Post Import Script\n";
echo "===========================================\n\n";

// ============================================
// STEP 1: Create Categories
// ============================================

echo "Creating categories...\n";

$categories_to_create = array(
    'hawaiian-breakfast' => 'Hawaiian Breakfast',
    'pupus-snacks' => 'Pupus & Snacks',
    'hawaiian-traditional' => 'Hawaiian Traditional',
);

foreach ($categories_to_create as $slug => $name) {
    $existing = get_term_by('slug', $slug, 'category');
    if (!$existing) {
        $result = wp_insert_term($name, 'category', array('slug' => $slug));
        if (!is_wp_error($result)) {
            echo "  ✓ Created category: $name\n";
        } else {
            echo "  ✗ Error creating $name: " . $result->get_error_message() . "\n";
        }
    } else {
        echo "  - Category exists: $name\n";
    }
}

// Get all category IDs we'll need
$cat_ids = array(
    'hawaiian-breakfast' => get_term_by('slug', 'hawaiian-breakfast', 'category')->term_id ?? 0,
    'pupus-snacks' => get_term_by('slug', 'pupus-snacks', 'category')->term_id ?? 0,
    'recipes' => get_term_by('slug', 'recipes', 'category')->term_id ?? 0,
    'island-drinks' => get_term_by('slug', 'island-drinks', 'category')->term_id ?? 0,
    'tropical-treats' => get_term_by('slug', 'tropical-treats', 'category')->term_id ?? 0,
    'island-comfort' => get_term_by('slug', 'island-comfort', 'category')->term_id ?? 0,
    'poke-seafood' => get_term_by('slug', 'poke-seafood', 'category')->term_id ?? 0,
    'top-articles' => get_term_by('slug', 'top-articles', 'category')->term_id ?? 0,
    'hawaiian-traditional' => get_term_by('slug', 'hawaiian-traditional', 'category')->term_id ?? 0,
);

echo "\n";

// ============================================
// STEP 2: Define All Posts
// ============================================

$posts = array(

// ============================================
// BATCH 1: HAWAIIAN BREAKFAST
// ============================================

array(
    'title' => 'Portuguese Sausage and Eggs – The Ultimate Local Hawaiian Breakfast',
    'slug' => 'portuguese-sausage-eggs-hawaiian-breakfast',
    'meta_description' => 'Learn how to make authentic Portuguese sausage and eggs, Hawaii\'s favorite local breakfast. Crispy sausage, over-easy eggs, and two scoops rice – just like the islands.',
    'categories' => array($cat_ids['hawaiian-breakfast'], $cat_ids['recipes']),
    'tags' => array('breakfast', 'portuguese sausage', 'local food', 'quick breakfast'),
    'content' => '
<p>If there\'s one smell that takes me straight back to my childhood kitchen on Oahu, it\'s Portuguese sausage hitting a hot pan at 6 AM.</p>

<p>That snap and sizzle. The way the edges get all crispy and caramelized while the inside stays juicy. My dad standing at the stove in his work clothes, spatula in hand, rice cooker already done its job.</p>

<p>"You like your eggs runny or what?"</p>

<p>Always runny. Always.</p>

<h2>The History Nobody Tells You</h2>

<p>Portuguese sausage came to Hawaii with immigrants from the Azores and Madeira in the late 1800s. They came to work the sugar plantations, and they brought their <em>linguiça</em> with them.</p>

<p>But here\'s the thing – Hawaiian Portuguese sausage isn\'t quite the same as what you\'d find in Portugal or even on the mainland. Local brands like Redondo\'s and Gouvea\'s have their own thing going on. Sweeter. More garlicky. That distinctive red color from paprika.</p>

<p>It became so embedded in local culture that you can\'t walk into a diner, plate lunch spot, or hotel breakfast buffet in Hawaii without seeing it. McDonald\'s in Hawaii? They serve Portuguese sausage. That\'s how deep it runs.</p>

<h2>What Makes This Breakfast Special</h2>

<p>This isn\'t fancy. It\'s not trying to be.</p>

<p>It\'s three simple things done right:</p>
<ul>
<li><strong>Portuguese sausage</strong> – sliced on the bias and fried until the edges crisp</li>
<li><strong>Eggs</strong> – over easy so the yolk runs into everything</li>
<li><strong>Rice</strong> – two scoops, always two scoops</li>
</ul>

<p>That\'s it. That\'s the whole breakfast.</p>

<p>Some people add kimchi on the side (Korean influence). Some add shoyu on their rice (Japanese influence). That\'s Hawaii – layers of culture in every meal.</p>

<h2>Tips Before You Start</h2>

<p><strong>Finding Portuguese sausage:</strong> If you\'re on the mainland, look for Linguiça at grocery stores – it\'s the closest substitute. Some Costco locations carry Silva\'s brand. Or order Redondo\'s online if you want the real deal.</p>

<p><strong>The rice situation:</strong> Use short-grain Japanese-style rice, not long grain. It should be slightly sticky. This isn\'t negotiable.</p>

<p><strong>Egg technique:</strong> Medium heat, butter in the pan, and don\'t touch the eggs until the whites are set. Then one confident flip. Practice makes perfect – or just make them over-medium if you\'re nervous.</p>

<h2>Portuguese Sausage and Eggs – Local Hawaiian Breakfast</h2>

<p><strong>The quintessential Hawaiian breakfast – crispy Portuguese sausage with over-easy eggs and two scoops of rice. Simple, satisfying, and straight from the islands.</strong></p>

<p><strong>Prep Time:</strong> 5 minutes | <strong>Cook Time:</strong> 10 minutes | <strong>Servings:</strong> 2</p>

<h3>Ingredients</h3>

<p><strong>For the Sausage:</strong></p>
<ul>
<li>8 oz Portuguese sausage (or linguiça), sliced ½-inch thick on the bias</li>
<li>1 teaspoon vegetable oil</li>
</ul>

<p><strong>For the Eggs:</strong></p>
<ul>
<li>4 large eggs</li>
<li>2 tablespoons butter</li>
<li>Salt and pepper to taste</li>
</ul>

<p><strong>For Serving:</strong></p>
<ul>
<li>2 cups cooked short-grain rice, warm</li>
<li>Shoyu (soy sauce), optional</li>
<li>Sliced green onions, optional</li>
<li>Kimchi, optional</li>
</ul>

<h3>Instructions</h3>

<ol>
<li><strong>Cook the sausage:</strong> Heat oil in a large skillet over medium-high heat. Add sausage slices in a single layer. Cook 2-3 minutes per side until edges are crispy and caramelized. Transfer to a plate lined with paper towels.</li>
<li><strong>Prep your plates:</strong> While sausage cooks, scoop rice onto two plates – two rounded scoops each, side by side. (Use an ice cream scoop for that authentic plate lunch look.)</li>
<li><strong>Fry the eggs:</strong> Reduce heat to medium. Add butter to the same skillet (don\'t wipe it out – that sausage flavor is gold). Once butter foams, crack eggs into the pan. Season with salt and pepper. Cook until whites are set but yolks are still runny, about 3 minutes. For over-easy, carefully flip and cook 30 seconds more.</li>
<li><strong>Plate it up:</strong> Arrange sausage slices next to rice. Top with eggs, letting the yolks rest on the rice so they break and soak in.</li>
<li><strong>Serve immediately</strong> with shoyu on the side for drizzling over rice. Add kimchi and green onions if that\'s your style.</li>
</ol>

<h3>Notes</h3>
<ul>
<li><strong>Sausage substitutes:</strong> Mainland linguiça works well. In a pinch, kielbasa is acceptable but sweeter than authentic.</li>
<li><strong>Make it a plate:</strong> Add a scoop of mac salad to make it a full breakfast plate.</li>
<li><strong>Meal prep:</strong> Cook extra sausage and rice for quick breakfasts all week.</li>
</ul>
'
),

array(
    'title' => 'Spam and Rice – Hawaii\'s Iconic Breakfast Done Right',
    'slug' => 'spam-and-rice-hawaiian-breakfast',
    'meta_description' => 'Spam and rice isn\'t just a breakfast in Hawaii – it\'s a way of life. Learn how to make this local favorite with perfectly crispy spam and the right rice technique.',
    'categories' => array($cat_ids['hawaiian-breakfast'], $cat_ids['recipes']),
    'tags' => array('breakfast', 'spam', 'local food', 'quick breakfast', 'budget-friendly'),
    'content' => '
<p>Let me tell you something that might surprise mainlanders: Hawaii consumes more Spam per capita than anywhere else in the world.</p>

<p>Seven million cans a year. On islands with less than 1.5 million people.</p>

<p>We put it in everything. Musubi. Fried rice. Saimin. But the purest expression? Spam and rice for breakfast. Maybe some eggs. Definitely some shoyu.</p>

<h2>Why Hawaii Loves Spam (And You Should Too)</h2>

<p>I know, I know. Spam gets a bad rap on the mainland. Mystery meat. Processed junk.</p>

<p>But in Hawaii, Spam is legitimately beloved. And there\'s history behind it.</p>

<p>During World War II, fresh meat was scarce in the Pacific. Spam, with its long shelf life, became a military staple. American GIs introduced it throughout the islands, and it stuck – not because people had no choice, but because it genuinely fit into local cuisine.</p>

<p>Think about it: Spam is salty, savory, and crisps up beautifully. It pairs perfectly with rice. It absorbs the flavors of whatever you cook it with. For a place where Asian and American food cultures collided, Spam was a natural bridge.</p>

<p>My mom kept a stack of Spam cans in the pantry at all times. Emergency dinner? Spam fried rice. Quick breakfast before school? Spam and eggs. Beach day cooler? Spam musubi wrapped in foil.</p>

<h2>The Secret to Perfect Spam</h2>

<p>Here\'s what most people get wrong: they don\'t cook it long enough.</p>

<p>You want that Spam <strong>crispy</strong>. Almost burnt on the edges. The Maillard reaction transforms it from salty luncheon meat into something magical – caramelized, slightly sweet, with texture.</p>

<p>Slice it thin (about ¼ inch), get your pan ripping hot, and don\'t crowd it. Let each slice develop a proper crust before flipping.</p>

<h2>Spam and Rice – Classic Hawaiian Breakfast</h2>

<p><strong>Crispy fried Spam with fluffy rice and eggs – the breakfast that fueled generations of Hawaii\'s kids. Simple, satisfying, and surprisingly delicious.</strong></p>

<p><strong>Prep Time:</strong> 5 minutes | <strong>Cook Time:</strong> 10 minutes | <strong>Servings:</strong> 2</p>

<h3>Ingredients</h3>
<ul>
<li>1 can (12 oz) Spam Classic (or Spam Less Sodium)</li>
<li>1 tablespoon vegetable oil</li>
<li>4 large eggs</li>
<li>2 tablespoons butter</li>
<li>2 cups cooked short-grain rice, warm</li>
<li>Shoyu (soy sauce), for serving</li>
<li>Furikake (Japanese rice seasoning), optional</li>
<li>Sriracha or hot sauce, optional</li>
</ul>

<h3>Instructions</h3>
<ol>
<li><strong>Slice the Spam:</strong> Remove Spam from can and slice into 8 pieces, about ¼-inch thick each.</li>
<li><strong>Fry the Spam:</strong> Heat oil in a large skillet over medium-high heat until shimmering. Add Spam slices in a single layer (work in batches if needed). Cook 2-3 minutes per side until deeply golden and crispy on edges. Transfer to a plate.</li>
<li><strong>Cook the eggs:</strong> Reduce heat to medium. Add butter to the same skillet. Once melted, crack in eggs and cook to your preference – fried, over easy, or scrambled. Season with a pinch of salt (remember, the Spam is salty).</li>
<li><strong>Assemble:</strong> Divide rice between two plates (two scoops each). Arrange Spam slices alongside. Add eggs on top of or next to the rice.</li>
<li><strong>Finish:</strong> Drizzle shoyu over rice. Sprinkle with furikake if using. Serve with hot sauce on the side.</li>
</ol>

<h3>Notes</h3>
<ul>
<li><strong>Spam varieties:</strong> Classic is traditional, but Less Sodium is good if you\'re watching salt. Spam Tocino (Filipino-style) adds a sweet twist.</li>
<li><strong>Crispy is key:</strong> Don\'t rush the Spam. That crust is everything.</li>
</ul>
'
),

array(
    'title' => 'Hawaiian Acai Bowl – Thick, Creamy, and Loaded with Toppings',
    'slug' => 'hawaiian-acai-bowl-recipe',
    'meta_description' => 'Make a thick, creamy Hawaiian-style acai bowl at home. The secret is in the blend technique and fresh island-inspired toppings. Better than any bowl you\'ll buy.',
    'categories' => array($cat_ids['hawaiian-breakfast'], $cat_ids['recipes']),
    'tags' => array('breakfast', 'acai', 'healthy', 'smoothie bowl', 'tropical'),
    'content' => '
<p>Walk down any main street in a Hawaii beach town – Haleiwa, Kailua, Lahaina – and you\'ll find at least three acai bowl shops within a block of each other.</p>

<p>Lines out the door. People sitting on curbs with purple-stained mouths. Instagram photos being carefully composed.</p>

<p>Acai bowls are everywhere now, but Hawaii was early to the game. The Brazilian superfood found a perfect home in our smoothie-loving, health-conscious, tropical fruit paradise.</p>

<h2>The Hawaii Acai Bowl Difference</h2>

<p>Not all acai bowls are created equal.</p>

<p>The ones you get at mainland chain smoothie shops? Thin. Melty. More juice than substance. You\'re basically eating purple soup with granola floating in it.</p>

<p>A proper Hawaiian acai bowl is <strong>thick</strong>. Like, eat-it-with-a-spoon-and-it-holds-its-shape thick. The toppings don\'t sink. The texture is almost ice cream-like.</p>

<p>The secret? Frozen banana, less liquid, and a proper high-powered blender.</p>

<h2>Hawaiian-Style Acai Bowl</h2>

<p><strong>A thick, creamy acai bowl loaded with fresh tropical toppings – the way they make them in Hawaii. The secret is in the blend technique.</strong></p>

<p><strong>Prep Time:</strong> 10 minutes | <strong>Servings:</strong> 1</p>

<h3>Ingredients</h3>

<p><strong>For the Base:</strong></p>
<ul>
<li>2 frozen acai packets (100g each), unsweetened</li>
<li>1 frozen banana</li>
<li>¼ cup frozen blueberries</li>
<li>3-4 tablespoons coconut milk, almond milk, or apple juice</li>
<li>1 tablespoon honey or agave (optional, to taste)</li>
</ul>

<p><strong>For Topping:</strong></p>
<ul>
<li>½ banana, sliced</li>
<li>¼ cup fresh strawberries, sliced</li>
<li>¼ cup granola</li>
<li>2 tablespoons shredded coconut</li>
<li>1 tablespoon honey, for drizzling</li>
<li>Fresh blueberries</li>
<li>Macadamia nuts, chopped (optional)</li>
</ul>

<h3>Instructions</h3>
<ol>
<li><strong>Break up the acai:</strong> Run the frozen acai packets under warm water for 10-15 seconds to soften slightly. Break into chunks and add to a high-powered blender.</li>
<li><strong>Add frozen fruit:</strong> Add frozen banana and frozen blueberries to blender.</li>
<li><strong>Blend thick:</strong> Add just 3 tablespoons of liquid to start. Blend on high, using the tamper tool to push ingredients down. The mixture should be very thick – almost like soft serve. Add another tablespoon of liquid only if absolutely necessary.</li>
<li><strong>Transfer immediately:</strong> Scoop the thick acai base into a bowl. Work quickly – it melts fast!</li>
<li><strong>Arrange toppings:</strong> Place banana slices along one section, strawberries in another, then granola, coconut, and any other toppings in organized sections. Drizzle with honey.</li>
<li><strong>Serve immediately</strong> with a spoon. Eat before it melts!</li>
</ol>
'
),

array(
    'title' => 'Hawaiian Sweet Bread French Toast – Soft, Buttery, Unbelievably Good',
    'slug' => 'hawaiian-sweet-bread-french-toast',
    'meta_description' => 'Use Hawaiian sweet bread to make the most incredible French toast you\'ve ever had. Soft, buttery, with a hint of sweetness – this is weekend breakfast perfection.',
    'categories' => array($cat_ids['hawaiian-breakfast'], $cat_ids['recipes']),
    'tags' => array('breakfast', 'french toast', 'brunch', 'weekend breakfast', 'sweet bread'),
    'content' => '
<p>If you\'ve ever made French toast with King\'s Hawaiian bread, you know.</p>

<p>You <em>know</em>.</p>

<p>That soft, pillowy bread soaks up the egg custard like a sponge, then turns golden and almost caramelized on the outside while staying impossibly tender inside. It\'s not even fair to regular French toast.</p>

<h2>The Story of Hawaiian Sweet Bread</h2>

<p>Robert Taira opened a bakery in Hilo in 1950. He\'d learned to make Portuguese sweet bread – <em>pão doce</em> – from local bakers whose families had brought the recipe from Madeira. But Taira added his own twist: more eggs, more butter, a softer crumb.</p>

<p>He called it "Hawaiian Sweet Bread," and people lost their minds over it.</p>

<h2>Why It Works So Perfectly</h2>

<p>Regular French toast bread needs to be sturdy – brioche, challah, thick-cut white bread. Something that won\'t fall apart when soaked.</p>

<p>Hawaiian sweet bread breaks all those rules. It\'s soft. It\'s delicate. And somehow, it holds up beautifully because of all that egg and butter in the dough. Like recognizes like.</p>

<h2>Hawaiian Sweet Bread French Toast</h2>

<p><strong>Impossibly soft, perfectly golden French toast made with Hawaiian sweet bread. The built-in sweetness and buttery texture take this breakfast classic to another level.</strong></p>

<p><strong>Prep Time:</strong> 10 minutes | <strong>Cook Time:</strong> 15 minutes | <strong>Servings:</strong> 4</p>

<h3>Ingredients</h3>

<p><strong>For the Custard:</strong></p>
<ul>
<li>4 large eggs</li>
<li>½ cup whole milk</li>
<li>2 tablespoons heavy cream</li>
<li>1 tablespoon sugar</li>
<li>1 teaspoon vanilla extract</li>
<li>½ teaspoon cinnamon</li>
<li>Pinch of salt</li>
</ul>

<p><strong>For Cooking:</strong></p>
<ul>
<li>1 package King\'s Hawaiian Sweet Rolls (12 rolls) or 1 loaf Hawaiian sweet bread, sliced 1-inch thick</li>
<li>4 tablespoons butter, divided</li>
</ul>

<p><strong>For Serving:</strong></p>
<ul>
<li>Pure maple syrup, warmed</li>
<li>Fresh berries</li>
<li>Powdered sugar</li>
<li>Whipped cream (optional)</li>
</ul>

<h3>Instructions</h3>
<ol>
<li><strong>Preheat oven</strong> to 200°F and place a wire rack on a baking sheet inside. This keeps finished French toast warm and crispy.</li>
<li><strong>Make the custard:</strong> Whisk eggs, milk, cream, sugar, vanilla, cinnamon, and salt in a shallow bowl until well combined.</li>
<li><strong>Prep the bread:</strong> If using rolls, slice each in half horizontally. If using a loaf, cut into 1-inch slices.</li>
<li><strong>Heat the pan:</strong> Melt 1 tablespoon butter in a large skillet or griddle over medium heat.</li>
<li><strong>Dip and cook:</strong> Working in batches, quickly dip bread slices in custard – just 2-3 seconds per side. Don\'t soak! Place in pan and cook until golden brown, about 2 minutes per side.</li>
<li><strong>Keep warm:</strong> Transfer finished pieces to the oven while you cook remaining batches.</li>
<li><strong>Serve:</strong> Stack 3 pieces per plate. Top with butter, dust with powdered sugar, add fresh berries, and drizzle generously with warm maple syrup.</li>
</ol>
'
),

array(
    'title' => 'Hawaiian Eggs Benedict with Kalua Pork – Brunch, Island Style',
    'slug' => 'hawaiian-eggs-benedict-kalua-pork',
    'meta_description' => 'Upgrade your eggs Benedict with smoky kalua pork and creamy hollandaise on a toasted Hawaiian sweet roll. This Hawaiian twist is brunch perfection.',
    'categories' => array($cat_ids['hawaiian-breakfast'], $cat_ids['recipes']),
    'tags' => array('breakfast', 'brunch', 'eggs benedict', 'kalua pork', 'weekend breakfast'),
    'content' => '
<p>Every Hawaiian brunch spot worth its salt has some version of eggs Benedict on the menu. And almost all of them have figured out the same thing:</p>

<p>Why use Canadian bacon when you\'ve got kalua pork?</p>

<p>The swap makes so much sense it\'s almost obvious. Smoky, tender shredded pork instead of the usual ham. Rich hollandaise dripping down. A runny poached egg tying it all together.</p>

<p>And if you\'re smart? You put it on a toasted Hawaiian sweet roll instead of an English muffin.</p>

<h2>The Blender Hollandaise Method</h2>

<p>Traditional hollandaise is made in a double boiler, whisking constantly, praying to the egg gods that it doesn\'t scramble or break.</p>

<p>I\'m going to teach you the blender method. It takes 2 minutes and works every single time. No culinary degree required.</p>

<p>The trick: hot melted butter, poured slowly into running blender with egg yolks. That\'s it. The heat from the butter cooks the yolks just enough. The blender emulsifies everything.</p>

<h2>Hawaiian Eggs Benedict with Kalua Pork</h2>

<p><strong>Smoky kalua pork, perfectly poached eggs, and creamy hollandaise on toasted Hawaiian sweet rolls. This island-style eggs Benedict is brunch at its absolute best.</strong></p>

<p><strong>Prep Time:</strong> 15 minutes | <strong>Cook Time:</strong> 20 minutes | <strong>Servings:</strong> 4</p>

<h3>Ingredients</h3>

<p><strong>For the Blender Hollandaise:</strong></p>
<ul>
<li>3 large egg yolks</li>
<li>1 tablespoon fresh lemon juice</li>
<li>½ teaspoon Dijon mustard</li>
<li>Pinch of cayenne pepper</li>
<li>½ cup (1 stick) unsalted butter, melted and hot</li>
<li>Salt to taste</li>
</ul>

<p><strong>For Assembly:</strong></p>
<ul>
<li>1½ cups kalua pork, shredded</li>
<li>4 Hawaiian sweet rolls, split in half</li>
<li>8 large eggs (for poaching)</li>
<li>1 tablespoon white vinegar</li>
<li>2 tablespoons butter</li>
<li>Fresh chives, chopped, for garnish</li>
</ul>

<h3>Instructions</h3>

<p><strong>Make the Hollandaise:</strong></p>
<ol>
<li>Add egg yolks, lemon juice, Dijon, and cayenne to a blender. Blend on low for 5 seconds to combine.</li>
<li>With the blender running on low, slowly drizzle in the hot melted butter in a thin, steady stream. This should take about 30 seconds.</li>
<li>The sauce will thicken and become creamy. Season with salt. Keep warm.</li>
</ol>

<p><strong>Prepare the Pork:</strong></p>
<ol start="4">
<li>Heat a skillet over medium-high heat. Add kalua pork in an even layer. Cook without stirring for 2-3 minutes until edges get crispy. Stir once and cook another minute. Set aside.</li>
</ol>

<p><strong>Toast the Rolls:</strong></p>
<ol start="5">
<li>Butter the cut sides of Hawaiian rolls. Toast in a pan or under the broiler until golden brown. Set on plates.</li>
</ol>

<p><strong>Poach the Eggs:</strong></p>
<ol start="6">
<li>Fill a large, deep skillet with 3 inches of water. Bring to a gentle simmer. Add vinegar.</li>
<li>Crack each egg into a small bowl first. Create a gentle whirlpool in the water and slip eggs in one at a time.</li>
<li>Poach for 3-4 minutes until whites are set but yolks are still runny. Remove with a slotted spoon.</li>
</ol>

<p><strong>Assemble:</strong></p>
<ol start="9">
<li>Place two toasted roll halves on each plate, cut side up.</li>
<li>Top each half with a mound of crispy kalua pork.</li>
<li>Place a poached egg on top of each.</li>
<li>Drizzle generously with hollandaise.</li>
<li>Garnish with chives and serve immediately.</li>
</ol>
'
),

// ============================================
// BATCH 2: PUPUS & SNACKS
// ============================================

array(
    'title' => 'Spam Musubi – Hawaii\'s Favorite Snack (Perfect Every Time)',
    'slug' => 'spam-musubi-recipe',
    'meta_description' => 'Learn to make authentic Spam musubi with this easy recipe. Crispy fried Spam, seasoned rice, wrapped in nori – the iconic Hawaiian snack done right.',
    'categories' => array($cat_ids['pupus-snacks'], $cat_ids['recipes']),
    'tags' => array('snacks', 'spam', 'musubi', 'local food', 'meal prep'),
    'content' => '
<p>If I had to choose one food that represents modern Hawaii, it would be Spam musubi.</p>

<p>Not poke. Not kalua pig. Spam musubi.</p>

<p>It\'s at every 7-Eleven (yes, Hawaiian 7-Elevens are legendary). Every ABC Store. Every potluck. Every kid\'s lunch box. Every gas station counter, wrapped in plastic, rotating slowly under a heat lamp.</p>

<h2>What Even Is Musubi?</h2>

<p>Think of it as a Hawaiian-Japanese mashup.</p>

<p>Japanese <em>onigiri</em> (rice balls) met American Spam during the World War II era. Locals started putting crispy fried Spam on top of rice blocks and wrapping it in nori seaweed.</p>

<p>Simple. Portable. Delicious.</p>

<h2>The Secrets to Perfect Musubi</h2>

<ol>
<li><strong>The rice matters.</strong> Short-grain Japanese rice, seasoned with a little rice vinegar, sugar, and salt.</li>
<li><strong>Crispy Spam is non-negotiable.</strong> Fry it until the edges are caramelized and slightly crispy.</li>
<li><strong>The glaze is key.</strong> Soy sauce + sugar brushed on the Spam while it\'s still hot.</li>
<li><strong>Get a musubi mold.</strong> A $5 musubi press from Amazon makes them look professional.</li>
<li><strong>Wrap it right.</strong> Nori goes shiny-side down. The rice should still be warm when you wrap.</li>
</ol>

<h2>Spam Musubi – Classic Hawaiian Style</h2>

<p><strong>Crispy fried Spam glazed with sweet soy sauce, pressed with seasoned rice, wrapped in nori seaweed. Hawaii\'s most iconic grab-and-go snack.</strong></p>

<p><strong>Prep Time:</strong> 15 minutes | <strong>Cook Time:</strong> 10 minutes | <strong>Servings:</strong> 8 musubi</p>

<h3>Ingredients</h3>

<p><strong>For the Sushi Rice:</strong></p>
<ul>
<li>3 cups cooked short-grain Japanese rice, warm</li>
<li>2 tablespoons rice vinegar</li>
<li>1 tablespoon sugar</li>
<li>1 teaspoon salt</li>
</ul>

<p><strong>For the Spam:</strong></p>
<ul>
<li>1 can (12 oz) Spam Classic, sliced into 8 pieces</li>
<li>1 tablespoon vegetable oil</li>
<li>3 tablespoons soy sauce</li>
<li>2 tablespoons sugar</li>
</ul>

<p><strong>For Assembly:</strong></p>
<ul>
<li>4 sheets nori seaweed, cut in half lengthwise</li>
<li>Musubi mold</li>
</ul>

<h3>Instructions</h3>
<ol>
<li><strong>Season the Rice:</strong> Mix rice vinegar, sugar, and salt until dissolved. Fold gently into warm rice. Cover with a damp towel.</li>
<li><strong>Cook the Spam:</strong> Heat oil in a large skillet over medium-high heat. Add Spam slices in a single layer. Cook 2-3 minutes per side until golden and crispy.</li>
<li>While still in pan, add soy sauce and sugar. Let it bubble and coat the Spam, flipping to glaze both sides. Remove and set on a plate.</li>
<li><strong>Assemble:</strong> Lay a half-sheet of nori shiny-side down on a clean surface.</li>
<li>Place the musubi mold in the center of the nori.</li>
<li>Scoop about ⅓ cup rice into the mold. Press down firmly. The rice should be about ¾ inch thick.</li>
<li>Place one slice of glazed Spam on top of the rice.</li>
<li>Remove the mold by pushing down on the press while lifting the mold up.</li>
<li>Fold one side of nori up and over the musubi. Fold the other side over, pressing gently to seal.</li>
<li>Set seam-side down. Repeat with remaining ingredients.</li>
</ol>

<p><strong>Serve</strong> immediately, or wrap individually in plastic wrap for later.</p>
'
),

array(
    'title' => 'Poke Nachos – Hawaiian-Style Nachos with Fresh Ahi',
    'slug' => 'poke-nachos-recipe',
    'meta_description' => 'Crispy wonton chips loaded with fresh ahi poke, spicy mayo, and all the toppings. Poke nachos are the ultimate Hawaiian pupu for game day or parties.',
    'categories' => array($cat_ids['pupus-snacks'], $cat_ids['poke-seafood'], $cat_ids['recipes']),
    'tags' => array('appetizers', 'poke', 'ahi', 'party food', 'game day'),
    'content' => '
<p>Somewhere along the way, someone in Hawaii looked at a plate of nachos, then looked at a bowl of poke, and said: "What if?"</p>

<p>That person is a genius.</p>

<p>Poke nachos have exploded in popularity over the last decade. Every sports bar in Honolulu has their version. It\'s the pupu that gets ordered for the table every time. The one that disappears fastest.</p>

<h2>Why This Works So Ridiculously Well</h2>

<ul>
<li><strong>Crispy wonton chips</strong> instead of tortilla chips – lighter, crunchier</li>
<li><strong>Fresh ahi poke</strong> – cool, raw, rich, and briny</li>
<li><strong>Creamy drizzles</strong> – spicy mayo, wasabi aioli, unagi sauce</li>
<li><strong>Crunchy toppings</strong> – green onions, sesame seeds, tobiko, jalapeños</li>
</ul>

<h2>Poke Nachos with Fresh Ahi</h2>

<p><strong>Crispy wonton chips piled high with fresh ahi poke, drizzled with spicy mayo and unagi sauce, topped with all the fixings. The ultimate Hawaiian pupu.</strong></p>

<p><strong>Prep Time:</strong> 20 minutes | <strong>Cook Time:</strong> 10 minutes | <strong>Servings:</strong> 6 (as appetizer)</p>

<h3>Ingredients</h3>

<p><strong>For the Poke:</strong></p>
<ul>
<li>1 lb sashimi-grade ahi tuna, diced into ½-inch cubes</li>
<li>¼ cup soy sauce</li>
<li>1 tablespoon sesame oil</li>
<li>1 teaspoon rice vinegar</li>
<li>1 teaspoon Sriracha</li>
<li>2 green onions, thinly sliced</li>
<li>1 teaspoon sesame seeds</li>
</ul>

<p><strong>For the Wonton Chips:</strong></p>
<ul>
<li>1 package wonton wrappers (about 30)</li>
<li>Vegetable oil, for frying</li>
<li>Sea salt</li>
</ul>

<p><strong>For the Spicy Mayo:</strong></p>
<ul>
<li>½ cup mayonnaise</li>
<li>2 tablespoons Sriracha</li>
<li>1 teaspoon lime juice</li>
</ul>

<p><strong>For Topping:</strong></p>
<ul>
<li>Unagi sauce, for drizzling</li>
<li>Sliced jalapeños</li>
<li>Tobiko (optional)</li>
<li>Sliced avocado (optional)</li>
</ul>

<h3>Instructions</h3>
<ol>
<li><strong>Make the Poke:</strong> Combine soy sauce, sesame oil, rice vinegar, and Sriracha in a bowl. Add diced ahi, green onions, and sesame seeds. Toss gently. Refrigerate.</li>
<li><strong>Make the Spicy Mayo:</strong> Whisk together mayo, Sriracha, and lime juice. Transfer to a squeeze bottle.</li>
<li><strong>Fry the Chips:</strong> Cut wonton wrappers diagonally into triangles. Heat 2 inches of oil to 350°F. Fry wontons in batches, about 30 seconds per side until golden. Drain on paper towels. Season with salt.</li>
<li><strong>Assemble (right before serving):</strong> Spread wonton chips on a large platter. Spoon poke generously over chips. Drizzle spicy mayo in zigzags. Drizzle unagi sauce. Top with jalapeños, tobiko, and avocado.</li>
</ol>

<p><strong>Serve immediately</strong> – the chips will get soggy if you wait!</p>
'
),

array(
    'title' => 'Manapua – Hawaii\'s Char Siu Bao (Steamed or Baked)',
    'slug' => 'manapua-recipe-hawaiian-bao',
    'meta_description' => 'Learn to make manapua, Hawaii\'s beloved char siu pork buns. Fluffy steamed buns filled with sweet BBQ pork – a local favorite since plantation days.',
    'categories' => array($cat_ids['pupus-snacks'], $cat_ids['recipes']),
    'tags' => array('snacks', 'pork', 'dim sum', 'chinese-hawaiian', 'baking'),
    'content' => '
<p>"Eh, you like manapua?"</p>

<p>If you grew up in Hawaii, you heard this question approximately one million times. From the manapua man\'s truck rolling through the neighborhood, playing that distinctive jingle. From your aunty who always had a stash from Char Hung Sut.</p>

<p>Manapua is Hawaii\'s version of Chinese <em>char siu bao</em> – steamed or baked buns filled with sweet BBQ pork. The name comes from the Hawaiian words "mea ono pua\'a" (delicious pork thing).</p>

<h2>Steamed vs. Baked</h2>

<p><strong>Steamed manapua</strong> (traditional dim sum style): Fluffy, white, pillowy dough. Sticky, sweet filling. Classic.</p>

<p><strong>Baked manapua</strong> (Hawaii innovation): Golden brown exterior. Slightly sweet, bread-like dough. Glazed top. Easier to transport.</p>

<h2>Manapua – Hawaiian Char Siu Bao</h2>

<p><strong>Fluffy buns filled with sweet BBQ pork, steamed or baked. Hawaii\'s beloved manapua is the perfect snack, party food, or meal prep staple.</strong></p>

<p><strong>Prep Time:</strong> 30 minutes (plus 2 hours rising) | <strong>Cook Time:</strong> 20 minutes | <strong>Servings:</strong> 16 buns</p>

<h3>Ingredients</h3>

<p><strong>For the Dough:</strong></p>
<ul>
<li>1 package (2¼ tsp) active dry yeast</li>
<li>1 cup warm water (110°F)</li>
<li>¼ cup sugar, divided</li>
<li>3½ cups all-purpose flour</li>
<li>2 tablespoons vegetable oil</li>
<li>½ teaspoon salt</li>
<li>½ teaspoon baking powder</li>
</ul>

<p><strong>For the Char Siu Filling:</strong></p>
<ul>
<li>2 cups char siu pork, diced small</li>
<li>2 tablespoons oyster sauce</li>
<li>1 tablespoon soy sauce</li>
<li>1 tablespoon hoisin sauce</li>
<li>2 teaspoons sugar</li>
<li>1 teaspoon sesame oil</li>
<li>1 tablespoon cornstarch mixed with 2 tablespoons water</li>
<li>2 green onions, finely chopped</li>
</ul>

<h3>Instructions</h3>
<ol>
<li>Dissolve yeast and 1 tablespoon sugar in warm water. Let sit 5-10 minutes until foamy.</li>
<li>Combine flour, remaining sugar, salt, and baking powder. Add yeast mixture and oil. Stir until dough forms.</li>
<li>Knead 8-10 minutes until smooth. Place in oiled bowl, cover. Let rise 1.5-2 hours until doubled.</li>
<li>For filling: Heat oyster sauce, soy sauce, hoisin sauce, sugar, and sesame oil in a saucepan. Add diced char siu. Add cornstarch slurry and cook until thick. Stir in green onions. Cool completely.</li>
<li>Punch down dough. Divide into 16 pieces. Flatten each into a 4-inch circle.</li>
<li>Place 1 heaping tablespoon filling in center. Gather edges up, pinch and twist to seal. Place seam-side down.</li>
<li><strong>To Steam:</strong> Steam over high heat 12-15 minutes until puffy.</li>
<li><strong>To Bake:</strong> Brush with egg wash, sprinkle sesame seeds. Bake at 350°F for 18-22 minutes until golden.</li>
</ol>
'
),

array(
    'title' => 'Hurricane Popcorn – Hawaii\'s Addictive Furikake Snack',
    'slug' => 'hurricane-popcorn-recipe',
    'meta_description' => 'Hurricane popcorn is Hawaii\'s most addictive snack – buttery popcorn tossed with furikake and crispy mochi rice crackers. One bowl is never enough.',
    'categories' => array($cat_ids['pupus-snacks'], $cat_ids['recipes']),
    'tags' => array('snacks', 'popcorn', 'furikake', 'party food', 'movie night'),
    'content' => '
<p>Warning: Hurricane popcorn is dangerous.</p>

<p>Not dangerous like "might hurt you" dangerous. Dangerous like "you will eat the entire bowl and then make another batch" dangerous.</p>

<p>It\'s the snack Hawaii invented for movie nights, potlucks, and "I need something salty NOW" moments. Once you try it, regular butter-and-salt popcorn just won\'t cut it anymore.</p>

<h2>What Makes It "Hurricane" Popcorn</h2>

<p>Legend has it the name comes from the chaotic mix of ingredients – popcorn, rice crackers, nori, and furikake all tossed together like a hurricane hit your snack bowl.</p>

<h2>The Essential Components</h2>

<ol>
<li><strong>Good popcorn</strong> – Pop your own</li>
<li><strong>Real butter</strong> – Melted and hot</li>
<li><strong>Furikake</strong> – Japanese rice seasoning (nori komi variety is classic)</li>
<li><strong>Arare (rice crackers)</strong> – The little crescent moon or pillow-shaped crackers</li>
<li><strong>Optional extras:</strong> Li hing mui powder, garlic salt, mochi crunch</li>
</ol>

<h2>Hurricane Popcorn</h2>

<p><strong>Hawaii\'s most addictive snack – buttery popcorn tossed with nori furikake and crispy rice crackers. Once you try it, regular popcorn will never be the same.</strong></p>

<p><strong>Prep Time:</strong> 5 minutes | <strong>Cook Time:</strong> 5 minutes | <strong>Servings:</strong> 8 cups</p>

<h3>Ingredients</h3>
<ul>
<li>½ cup popcorn kernels (or 8 cups popped popcorn)</li>
<li>3 tablespoons vegetable oil (if popping)</li>
<li>4 tablespoons butter, melted</li>
<li>3-4 tablespoons furikake (nori komi or nori fume style)</li>
<li>1 cup arare (Japanese rice crackers)</li>
<li>½ teaspoon garlic salt (optional)</li>
<li>Li hing mui powder, to taste (optional)</li>
</ul>

<h3>Instructions</h3>
<ol>
<li><strong>Pop the popcorn:</strong> Heat oil in a large pot over medium-high heat with 3 kernels inside. When they pop, add remaining kernels. Cover and shake occasionally. Remove from heat when popping slows. Transfer to a very large bowl.</li>
<li><strong>First butter layer:</strong> Drizzle half the melted butter over popcorn. Toss to coat.</li>
<li><strong>First furikake layer:</strong> Sprinkle half the furikake over popcorn. Toss well.</li>
<li><strong>Second layer:</strong> Drizzle remaining butter. Sprinkle remaining furikake. Toss thoroughly.</li>
<li><strong>Add the crunch:</strong> Gently fold in arare rice crackers.</li>
<li><strong>Season:</strong> Add garlic salt and li hing mui powder if desired.</li>
</ol>

<p><strong>Serve immediately</strong> in a big bowl for sharing.</p>
'
),

array(
    'title' => 'Mochiko Chicken – Hawaii\'s Crispy, Sweet, Garlicky Fried Chicken',
    'slug' => 'mochiko-chicken-recipe',
    'meta_description' => 'Mochiko chicken is Hawaii\'s local-style fried chicken – crispy, sweet, garlicky, and impossibly addictive. The secret is the sweet rice flour coating.',
    'categories' => array($cat_ids['pupus-snacks'], $cat_ids['island-comfort'], $cat_ids['recipes']),
    'tags' => array('chicken', 'fried chicken', 'appetizers', 'party food', 'local food'),
    'content' => '
<p>Every potluck in Hawaii has one dish that disappears first.</p>

<p>It\'s mochiko chicken.</p>

<p>Every. Single. Time.</p>

<h2>What Is Mochiko Chicken?</h2>

<p>Mochiko is sweet rice flour (also called glutinous rice flour). When used as a coating for fried chicken, it creates an incredibly crispy, slightly chewy crust that stays crunchy way longer than regular flour.</p>

<p>The marinade is where the magic happens – soy sauce, sugar, garlic, ginger, and egg. The chicken soaks in it overnight, so every bite is seasoned through and through.</p>

<h2>Critical Tips</h2>

<ol>
<li><strong>Marinate overnight.</strong> This isn\'t optional. Minimum 4 hours. Ideally overnight.</li>
<li><strong>Don\'t skip the egg in the marinade.</strong> It helps the coating stick.</li>
<li><strong>Use boneless thighs.</strong> They stay juicier than breast.</li>
<li><strong>Fry at 350°F.</strong> Too hot and the coating burns. Too cool and you get greasy chicken.</li>
<li><strong>Drain on a wire rack.</strong> Not paper towels, which trap steam.</li>
</ol>

<h2>Mochiko Chicken – Hawaii\'s Favorite Fried Chicken</h2>

<p><strong>Unbelievably crispy fried chicken with a sweet, garlicky flavor. The mochiko coating stays crunchy for hours. Hawaii potluck essential.</strong></p>

<p><strong>Prep Time:</strong> 20 minutes (plus overnight marinating) | <strong>Cook Time:</strong> 15 minutes | <strong>Servings:</strong> 6</p>

<h3>Ingredients</h3>

<p><strong>For the Marinade:</strong></p>
<ul>
<li>2 lbs boneless, skinless chicken thighs, cut into 2-inch pieces</li>
<li>¼ cup soy sauce</li>
<li>¼ cup sugar</li>
<li>2 eggs, beaten</li>
<li>4 cloves garlic, minced</li>
<li>1 tablespoon fresh ginger, minced</li>
<li>2 green onions, finely chopped</li>
<li>1 tablespoon sesame oil</li>
<li>¼ teaspoon black pepper</li>
</ul>

<p><strong>For the Coating:</strong></p>
<ul>
<li>1 cup mochiko (sweet rice flour)</li>
<li>¼ cup cornstarch</li>
</ul>

<p><strong>For Frying:</strong></p>
<ul>
<li>Vegetable oil, for deep frying (about 4 cups)</li>
</ul>

<h3>Instructions</h3>
<ol>
<li><strong>Marinate:</strong> Whisk together soy sauce, sugar, eggs, garlic, ginger, green onions, sesame oil, and pepper. Add chicken and toss to coat. Cover and refrigerate overnight.</li>
<li><strong>Prepare coating:</strong> Whisk together mochiko and cornstarch in a shallow bowl.</li>
<li><strong>Dredge:</strong> Remove chicken from marinade (don\'t shake off excess). Dredge in mochiko mixture, pressing to coat all sides. Let rest 10 minutes.</li>
<li><strong>Fry:</strong> Heat oil to 350°F. Fry chicken in batches of 6-8 pieces for 5-7 minutes, turning occasionally, until deep golden brown and cooked through (165°F internal).</li>
<li>Transfer to wire rack. Let oil return to 350°F between batches.</li>
</ol>

<p><strong>Serve</strong> hot, warm, or room temperature – still delicious hours later.</p>
'
),

// ============================================
// BATCH 3: DRINKS
// ============================================

array(
    'title' => 'Blue Hawaii Cocktail – The Original Island Drink Recipe',
    'slug' => 'blue-hawaii-cocktail-recipe',
    'meta_description' => 'Make an authentic Blue Hawaii cocktail at home. This iconic turquoise drink was invented in Waikiki in 1957 and still tastes like a Hawaiian vacation.',
    'categories' => array($cat_ids['island-drinks'], $cat_ids['recipes']),
    'tags' => array('cocktails', 'rum', 'tropical drinks', 'party drinks'),
    'content' => '
<p>That electric blue color. You\'ve seen it in every cheesy beach movie, every tiki bar, every resort pool bar in the world.</p>

<p>But the Blue Hawaii has a real history, and it starts exactly where you\'d hope – in Waikiki.</p>

<h2>The Origin Story</h2>

<p>1957. The Hilton Hawaiian Village. A bartender named Harry Yee.</p>

<p>Bols, the Dutch liqueur company, asked Yee to create a cocktail showcasing their new Blue Curaçao. Harry mixed it with rum, pineapple juice, and sweet and sour mix, served it in a tall glass with an orchid garnish.</p>

<p>The Blue Hawaii was born.</p>

<h2>Blue Hawaii Cocktail</h2>

<p><strong>The original turquoise cocktail invented in Waikiki in 1957. Light rum, Blue Curaçao, and pineapple juice create the perfect tropical vacation in a glass.</strong></p>

<p><strong>Prep Time:</strong> 5 minutes | <strong>Servings:</strong> 1 cocktail</p>

<h3>Ingredients</h3>
<ul>
<li>1 oz light rum (white rum)</li>
<li>1 oz vodka</li>
<li>½ oz Blue Curaçao</li>
<li>3 oz pineapple juice</li>
<li>1 oz fresh sweet and sour mix</li>
<li>Ice</li>
<li>Pineapple wedge and cherry, for garnish</li>
</ul>

<h3>Instructions</h3>
<ol>
<li><strong>Combine ingredients:</strong> Fill a cocktail shaker with ice. Add rum, vodka, Blue Curaçao, pineapple juice, and sweet and sour mix.</li>
<li><strong>Shake well</strong> for 15-20 seconds until very cold.</li>
<li><strong>Strain</strong> into a hurricane glass or tall glass filled with fresh ice.</li>
<li><strong>Garnish</strong> with a pineapple wedge and maraschino cherry.</li>
</ol>

<p><strong>Serve immediately</strong> – the color is most vibrant when fresh.</p>

<h3>Notes</h3>
<ul>
<li><strong>Blue Hawaiian variation:</strong> Add 1 oz cream of coconut and reduce pineapple juice to 2 oz for a creamier version.</li>
<li><strong>Fresh sour mix:</strong> Equal parts lemon juice, lime juice, and simple syrup.</li>
</ul>
'
),

array(
    'title' => 'Lava Flow – Hawaii\'s Frozen Strawberry-Piña Colada',
    'slug' => 'lava-flow-drink-recipe',
    'meta_description' => 'The Lava Flow is Hawaii\'s signature frozen cocktail – creamy piña colada swirled with strawberry puree to look like flowing lava. Here\'s how to make it at home.',
    'categories' => array($cat_ids['island-drinks'], $cat_ids['recipes']),
    'tags' => array('cocktails', 'rum', 'frozen drinks', 'tropical drinks', 'blender drinks'),
    'content' => '
<p>Picture a piña colada and a strawberry daiquiri had a baby. Now picture that baby looking like an erupting volcano.</p>

<p>That\'s the Lava Flow.</p>

<p>The red strawberry puree swirled through creamy white piña colada creates a dramatic visual that looks like lava flowing down a Hawaiian mountainside.</p>

<h2>Lava Flow</h2>

<p><strong>Hawaii\'s famous frozen cocktail – creamy piña colada swirled with strawberry puree to create a volcanic lava effect. Tropical, beautiful, and irresistible.</strong></p>

<p><strong>Prep Time:</strong> 10 minutes | <strong>Servings:</strong> 2 cocktails</p>

<h3>Ingredients</h3>

<p><strong>For the Piña Colada Base:</strong></p>
<ul>
<li>3 oz light rum</li>
<li>2 oz cream of coconut (like Coco Lopez)</li>
<li>4 oz pineapple juice</li>
<li>1 cup frozen pineapple chunks</li>
<li>1 cup ice</li>
</ul>

<p><strong>For the Strawberry "Lava":</strong></p>
<ul>
<li>1 cup fresh or frozen strawberries</li>
<li>1 oz light rum</li>
<li>1 tablespoon simple syrup</li>
<li>2-3 tablespoons water or pineapple juice (to thin)</li>
</ul>

<h3>Instructions</h3>
<ol>
<li><strong>Make the Strawberry Lava:</strong> Blend strawberries, 1 oz rum, simple syrup, and water until smooth. Set aside in a squeeze bottle or measuring cup.</li>
<li><strong>Make the Piña Colada Base:</strong> Blend 3 oz rum, cream of coconut, pineapple juice, frozen pineapple, and ice until thick and smooth.</li>
<li><strong>Assemble:</strong> Divide piña colada base between two glasses.</li>
<li>Slowly pour/drizzle strawberry puree down the inside edge of each glass. The red will sink and swirl through the white, creating the "lava" effect.</li>
</ol>

<p><strong>Garnish</strong> with a strawberry, pineapple wedge, and paper umbrella.</p>

<p><strong>Note:</strong> Virgin version – skip all rum, increase pineapple juice to 5 oz.</p>
'
),

array(
    'title' => 'POG – Hawaii\'s Passion Orange Guava Juice (Make It Fresh)',
    'slug' => 'pog-juice-recipe',
    'meta_description' => 'POG juice is Hawaii\'s iconic tropical juice blend – passion fruit, orange, and guava. Learn to make it fresh at home, plus a cocktail version.',
    'categories' => array($cat_ids['island-drinks'], $cat_ids['recipes']),
    'tags' => array('juice', 'non-alcoholic', 'tropical drinks', 'breakfast drinks'),
    'content' => '
<p>Ask anyone from Hawaii what they drank growing up, and POG will come up within the first three answers.</p>

<p>Passion fruit. Orange. Guava.</p>

<p>P.O.G.</p>

<p>That sweet, tangy, bright tropical flavor in the distinctive orange carton. Every school cafeteria. Every convenience store. Every cooler at the beach.</p>

<h2>The POG Story</h2>

<p>POG was created in 1971 at Haleakala Dairy on Maui. Mary Soon, a product development specialist, combined three tropical juices that grew abundantly in Hawaii and created something magical.</p>

<h2>Fresh POG Juice (Passion Orange Guava)</h2>

<p><strong>Hawaii\'s iconic juice made fresh – tropical passion fruit, bright orange, and sweet guava blended into the most refreshing drink of your childhood.</strong></p>

<p><strong>Prep Time:</strong> 10 minutes | <strong>Servings:</strong> 4 cups</p>

<h3>Ingredients</h3>

<p><strong>Fresh Version:</strong></p>
<ul>
<li>4 fresh passion fruits (or ½ cup frozen passion fruit pulp)</li>
<li>2 cups fresh orange juice</li>
<li>1 cup guava nectar or puree</li>
<li>2-4 tablespoons sugar or honey (to taste)</li>
</ul>

<p><strong>Easy Version:</strong></p>
<ul>
<li>½ cup passion fruit nectar (Goya brand works)</li>
<li>2 cups orange juice</li>
<li>1 cup guava nectar</li>
</ul>

<h3>Instructions</h3>
<ol>
<li>Cut passion fruits in half. Scoop pulp into a fine-mesh strainer. Press to extract juice.</li>
<li>Combine passion fruit juice, orange juice, and guava nectar in a pitcher.</li>
<li>Stir well. Taste and add sugar if needed.</li>
<li>Chill thoroughly before serving over ice.</li>
</ol>

<p><strong>POG cocktail:</strong> Add 1.5 oz vodka or light rum per glass.</p>
'
),

array(
    'title' => 'Chi Chi – Hawaii\'s Vodka Piña Colada',
    'slug' => 'chi-chi-drink-recipe',
    'meta_description' => 'The Chi Chi is Hawaii\'s answer to the piña colada – vodka instead of rum, creamy coconut, and tropical pineapple. Lighter, smoother, and dangerously easy to drink.',
    'categories' => array($cat_ids['island-drinks'], $cat_ids['recipes']),
    'tags' => array('cocktails', 'vodka', 'frozen drinks', 'tropical drinks'),
    'content' => '
<p>If you\'ve ever sat at a bar in Hawaii and heard someone order a Chi Chi, you might have wondered what exactly makes it different from a piña colada.</p>

<p>Simple answer: vodka instead of rum.</p>

<p>That one swap changes everything. The Chi Chi is lighter, smoother, and lets the tropical flavors of pineapple and coconut shine without the heavier molasses notes of rum.</p>

<h2>Chi Chi</h2>

<p><strong>Hawaii\'s vodka version of the piña colada – lighter, smoother, and dangerously easy to drink. Creamy coconut and pineapple with clean vodka punch.</strong></p>

<p><strong>Prep Time:</strong> 5 minutes | <strong>Servings:</strong> 1 cocktail</p>

<h3>Ingredients (Frozen Version)</h3>
<ul>
<li>2 oz vodka</li>
<li>2 oz cream of coconut (like Coco Lopez)</li>
<li>4 oz pineapple juice</li>
<li>1 cup ice</li>
<li>Pineapple wedge and cherry, for garnish</li>
</ul>

<h3>Instructions</h3>
<ol>
<li>Add vodka, cream of coconut, pineapple juice, and ice to a blender.</li>
<li>Blend until smooth and slushy.</li>
<li>Pour into a hurricane glass or large glass.</li>
<li>Garnish with pineapple wedge and cherry.</li>
</ol>

<p><strong>Serve immediately.</strong></p>

<p><strong>On the Rocks Version:</strong> Shake vodka, 1.5 oz cream of coconut, and 3 oz pineapple juice with ice. Strain into a glass with fresh ice.</p>
'
),

// ============================================
// BATCH 3: DESSERTS
// ============================================

array(
    'title' => 'Butter Mochi – Hawaii\'s Chewy Coconut Dessert',
    'slug' => 'butter-mochi-recipe',
    'meta_description' => 'Butter mochi is Hawaii\'s beloved chewy, buttery, coconut-y dessert. Dense, sweet, and completely addictive – this one-bowl recipe is foolproof.',
    'categories' => array($cat_ids['tropical-treats'], $cat_ids['recipes']),
    'tags' => array('dessert', 'mochi', 'coconut', 'baking', 'potluck'),
    'content' => '
<p>If you\'ve never had butter mochi, let me prepare you: the texture is unlike anything else.</p>

<p>It\'s chewy. Almost gooey in the center. Dense but not heavy. Slightly crispy on the edges. Imagine if a brownie and a mochi had a baby, then that baby was raised by coconut.</p>

<h2>What Makes It "Butter Mochi"</h2>

<p><strong>Butter:</strong> A full stick goes into this thing. It\'s rich.</p>

<p><strong>Mochi:</strong> Made with mochiko (sweet rice flour), which gives it that distinctive chewy, slightly stretchy texture.</p>

<h2>Butter Mochi</h2>

<p><strong>Hawaii\'s beloved chewy coconut dessert – buttery, dense, and impossibly addictive. Made with sweet rice flour for that signature mochi texture.</strong></p>

<p><strong>Prep Time:</strong> 15 minutes | <strong>Cook Time:</strong> 1 hour | <strong>Servings:</strong> 24 pieces</p>

<h3>Ingredients</h3>
<ul>
<li>1 lb (16 oz) mochiko (sweet rice flour)</li>
<li>2½ cups sugar</li>
<li>1 teaspoon baking powder</li>
<li>½ cup (1 stick) butter, melted</li>
<li>5 large eggs</li>
<li>1 can (13.5 oz) coconut milk</li>
<li>1 can (12 oz) evaporated milk</li>
<li>1 teaspoon vanilla extract</li>
</ul>

<h3>Instructions</h3>
<ol>
<li><strong>Preheat oven</strong> to 350°F. Grease a 9x13 inch baking pan.</li>
<li><strong>Mix dry ingredients:</strong> Whisk together mochiko, sugar, and baking powder.</li>
<li><strong>Mix wet ingredients:</strong> Combine melted butter, eggs, coconut milk, evaporated milk, and vanilla. Whisk until smooth.</li>
<li><strong>Combine:</strong> Pour wet into dry. Stir until just combined and no dry spots remain. Don\'t overmix.</li>
<li><strong>Pour</strong> batter into prepared pan.</li>
<li><strong>Bake</strong> for 55-65 minutes. The top should be lightly golden and edges set.</li>
<li><strong>Cool completely</strong> in the pan – at least 2 hours, preferably overnight.</li>
<li><strong>Cut</strong> into squares with a plastic knife (metal sticks).</li>
</ol>

<h3>Notes</h3>
<ul>
<li><strong>Storage:</strong> Keep covered at room temperature up to 3 days. Do not refrigerate.</li>
<li><strong>Variations:</strong> Add 1 tablespoon ube extract for purple mochi. Fold in ½ cup sweetened shredded coconut.</li>
</ul>
'
),

array(
    'title' => 'Chocolate Haupia Pie – Hawaii\'s Famous Two-Layer Dessert',
    'slug' => 'chocolate-haupia-pie-recipe',
    'meta_description' => 'Chocolate haupia pie is Hawaii\'s most famous dessert – rich chocolate custard topped with creamy coconut haupia in a flaky crust. Ted\'s Bakery made it famous; now you can make it at home.',
    'categories' => array($cat_ids['tropical-treats'], $cat_ids['recipes']),
    'tags' => array('dessert', 'pie', 'chocolate', 'coconut', 'haupia'),
    'content' => '
<p>If there\'s a dessert that defines modern Hawaii, it\'s chocolate haupia pie.</p>

<p>Two layers: rich chocolate custard on the bottom, creamy coconut haupia on top. All in a flaky crust, crowned with whipped cream.</p>

<p>Every bakery has a version. Ted\'s Bakery on the North Shore of Oahu is probably the most famous – people drive an hour just for a slice.</p>

<h2>Chocolate Haupia Pie</h2>

<p><strong>Hawaii\'s most beloved dessert – a layer of rich chocolate custard, a layer of creamy coconut haupia, all in a buttery crust. This is the one everyone asks for.</strong></p>

<p><strong>Prep Time:</strong> 30 minutes | <strong>Cook Time:</strong> 15 minutes | <strong>Chill Time:</strong> 4 hours | <strong>Servings:</strong> 8 slices</p>

<h3>Ingredients</h3>

<p><strong>For the Crust:</strong></p>
<ul>
<li>1 pre-made 9-inch pie crust, baked</li>
</ul>

<p><strong>For the Chocolate Layer:</strong></p>
<ul>
<li>1 cup whole milk</li>
<li>½ cup sugar</li>
<li>¼ cup cornstarch</li>
<li>¼ cup unsweetened cocoa powder</li>
<li>½ cup semi-sweet chocolate chips</li>
<li>1 tablespoon butter</li>
<li>½ teaspoon vanilla extract</li>
</ul>

<p><strong>For the Haupia Layer:</strong></p>
<ul>
<li>1 can (13.5 oz) coconut milk</li>
<li>½ cup whole milk</li>
<li>½ cup sugar</li>
<li>¼ cup cornstarch</li>
</ul>

<p><strong>For Topping:</strong></p>
<ul>
<li>1 cup heavy whipping cream</li>
<li>2 tablespoons powdered sugar</li>
<li>Chocolate shavings</li>
</ul>

<h3>Instructions</h3>
<ol>
<li><strong>Chocolate Layer:</strong> Whisk milk, sugar, cornstarch, and cocoa in a saucepan. Cook over medium heat, whisking constantly, until thickened (5-7 minutes). Remove from heat. Stir in chocolate chips, butter, and vanilla until smooth. Pour into cooled pie crust. Let cool 15-20 minutes.</li>
<li><strong>Haupia Layer:</strong> Whisk coconut milk, milk, sugar, and cornstarch in a clean saucepan. Cook over medium heat until thickened (5-7 minutes). Carefully pour over chocolate layer. Spread gently.</li>
<li><strong>Chill:</strong> Refrigerate at least 4 hours or overnight.</li>
<li><strong>Top:</strong> Whip cream with powdered sugar. Spread over pie. Garnish with chocolate shavings.</li>
</ol>

<p><strong>Keep refrigerated.</strong> Best eaten within 2 days.</p>
'
),

array(
    'title' => 'Hawaiian Shave Ice – What It Is and How to Make It at Home',
    'slug' => 'hawaiian-shave-ice-guide',
    'meta_description' => 'Everything you need to know about Hawaiian shave ice – what makes it different from snow cones, the best flavors, toppings, and how to make it at home.',
    'categories' => array($cat_ids['tropical-treats'], $cat_ids['recipes']),
    'tags' => array('dessert', 'shave ice', 'summer', 'frozen treats'),
    'content' => '
<p>Let me clear something up right now: shave ice is NOT a snow cone.</p>

<p>If you\'ve never had real Hawaiian shave ice and think it\'s the same as those crunchy, icy cups you get at carnivals – you\'re in for a revelation.</p>

<h2>The Difference Is Everything</h2>

<p><strong>Snow cones:</strong> Crushed ice. Chunky. The syrup slides to the bottom.</p>

<p><strong>Shave ice:</strong> Ice shaved into delicate, snow-like flakes. So fine that the syrup absorbs completely into the ice. Every bite is perfectly flavored. The texture is almost creamy.</p>

<h2>The Add-Ons</h2>

<ul>
<li><strong>Ice cream</strong> at the bottom (yes, under the ice)</li>
<li><strong>Azuki beans</strong> (sweet Japanese red beans)</li>
<li><strong>Mochi balls</strong></li>
<li><strong>Li hing mui powder</strong> (salty dried plum)</li>
<li><strong>Condensed milk drizzle</strong> (called "snow cap")</li>
</ul>

<h2>Hawaiian Shave Ice</h2>

<p><strong>Real Hawaiian shave ice – impossibly fine ice that absorbs tropical syrups completely. Plus how to add the classic local toppings.</strong></p>

<p><strong>Prep Time:</strong> 10 minutes | <strong>Servings:</strong> 2</p>

<h3>Ingredients</h3>
<ul>
<li>Ice blocks or large ice cubes – about 4-6 cups ice per serving</li>
<li>Shave ice machine or high-powered blender</li>
<li>Flavored syrups (blue raspberry, strawberry, pineapple, coconut, etc.)</li>
</ul>

<p><strong>Optional Toppings:</strong></p>
<ul>
<li>Scoop of vanilla ice cream</li>
<li>Sweetened condensed milk (snow cap)</li>
<li>Azuki beans</li>
<li>Mochi balls</li>
<li>Li hing mui powder</li>
</ul>

<h3>Instructions</h3>
<ol>
<li>If using a shave ice machine, follow manufacturer instructions for shaving ice blocks.</li>
<li>If using a blender, pulse ice in small batches until very fine (almost powdery).</li>
<li>Pack shaved ice firmly into a cup or bowl, mounding it high.</li>
<li>If using ice cream, place a scoop in the bottom before adding ice.</li>
<li>Drizzle 2-3 syrups over the ice, letting them absorb.</li>
<li>Top with condensed milk drizzle and other toppings as desired.</li>
</ol>

<p><strong>Eat immediately</strong> – it melts fast!</p>
'
),

array(
    'title' => 'Lilikoi Bars – Hawaiian Passion Fruit Lemon Bars',
    'slug' => 'lilikoi-bars-passion-fruit-recipe',
    'meta_description' => 'Lilikoi bars are Hawaii\'s tropical twist on lemon bars – tangy passion fruit curd on a buttery shortbread crust. Bright, sunny, and absolutely addictive.',
    'categories' => array($cat_ids['tropical-treats'], $cat_ids['recipes']),
    'tags' => array('dessert', 'passion fruit', 'lilikoi', 'bars', 'baking'),
    'content' => '
<p>Every house in Hawaii with a lilikoi vine knows the situation: you either have zero passion fruit or you have hundreds.</p>

<p>When the vine goes off, you\'re scrambling to use them. Juice. Jam. Butter. Frozen pulp. And these bars – always these bars.</p>

<h2>What Is Lilikoi?</h2>

<p>Lilikoi is the Hawaiian word for passion fruit. The purple-skinned variety grows wild throughout the islands, climbing fences and trees.</p>

<p>The flavor is intensely tropical – tart, aromatic, floral, sweet. Lilikoi is one of those fruits that tastes exactly like Hawaii smells.</p>

<h2>Lilikoi Bars</h2>

<p><strong>Tangy passion fruit curd on a buttery shortbread crust – Hawaii\'s tropical twist on the classic lemon bar. Bright, sunny, and dangerously addictive.</strong></p>

<p><strong>Prep Time:</strong> 20 minutes | <strong>Cook Time:</strong> 45 minutes | <strong>Chill Time:</strong> 2 hours | <strong>Servings:</strong> 16 bars</p>

<h3>Ingredients</h3>

<p><strong>For the Shortbread Crust:</strong></p>
<ul>
<li>1 cup (2 sticks) butter, softened</li>
<li>½ cup powdered sugar</li>
<li>2 cups all-purpose flour</li>
<li>¼ teaspoon salt</li>
</ul>

<p><strong>For the Lilikoi Filling:</strong></p>
<ul>
<li>4 large eggs</li>
<li>1½ cups granulated sugar</li>
<li>½ cup fresh lilikoi (passion fruit) pulp and juice (about 6-8 fruits)</li>
<li>¼ cup fresh lemon juice</li>
<li>¼ cup all-purpose flour</li>
<li>½ teaspoon baking powder</li>
</ul>

<p><strong>For Topping:</strong></p>
<ul>
<li>Powdered sugar, for dusting</li>
</ul>

<h3>Instructions</h3>
<ol>
<li><strong>Make the Crust:</strong> Preheat oven to 350°F. Line a 9x13 inch pan with parchment. Beat butter and powdered sugar until creamy. Add flour and salt. Press evenly into pan. Bake 18-20 minutes until edges are lightly golden.</li>
<li><strong>Make the Filling:</strong> Prepare lilikoi – cut passion fruits in half, scoop pulp into a fine-mesh strainer. Press to extract juice. Whisk eggs and sugar. Add lilikoi juice, lemon juice, flour, baking powder. Whisk until smooth.</li>
<li><strong>Bake:</strong> Pour filling over hot crust. Bake 22-25 minutes until filling is set but still slightly jiggly in center.</li>
<li><strong>Cool and Chill:</strong> Cool completely in pan. Refrigerate at least 2 hours until firm.</li>
<li><strong>Serve:</strong> Cut into squares. Dust generously with powdered sugar.</li>
</ol>

<p><strong>Storage:</strong> Refrigerate up to 4 days. Can be frozen up to 2 months.</p>
'
),

// ============================================
// BATCH 5: HAWAIIAN TRADITIONAL
// ============================================

array(
    'title' => 'How to Make Authentic Laulau: Hawaiian Pork Wrapped in Taro Leaves',
    'slug' => 'authentic-laulau-recipe',
    'meta_description' => 'Learn to make authentic Hawaiian laulau – tender pork wrapped in taro leaves and steamed for hours. This traditional luau dish brings pure aloha to your kitchen.',
    'categories' => array($cat_ids['hawaiian-traditional'], $cat_ids['recipes']),
    'tags' => array('laulau', 'taro', 'pork', 'luau', 'traditional hawaiian', 'imu'),
    'content' => '
<p>Ever unwrapped a steaming bundle at a luau and wondered what\'s inside those dark green leaves? That\'s laulau, one of Hawaii\'s most beloved traditional dishes. This ancient cooking method combines tender pork with earthy taro leaves for a flavor that\'s pure aloha.</p>

<p>Laulau (pronounced lah-oo-lah-oo) translates to "wrapping" in Hawaiian, perfectly describing this bundle of deliciousness. At its core, laulau consists of salted butterfish or pork shoulder wrapped first in young taro leaves (lū\'au), then encased in ti leaves (lau ki) before being steamed until the meat falls apart and the taro leaves melt into a spinach-like texture.</p>

<h2>The Cultural Significance of Laulau</h2>

<p>Laulau holds a sacred place in Hawaiian food culture. Traditionally prepared in an imu—an underground oven lined with hot rocks and banana leaves—this dish was reserved for special celebrations and religious ceremonies.</p>

<p>The wrapping technique preserved food during long voyages and allowed Hawaiian families to cook large quantities for community gatherings. The taro plant itself is considered an ancestor in Hawaiian mythology, making dishes featuring its leaves deeply spiritual.</p>

<p>Today, laulau remains a must-have at baby luaus, graduations, and any celebration worth its Hawaiian salt.</p>

<h2>How Laulau Is Traditionally Served</h2>

<ul>
<li>Laulau is traditionally served still wrapped in its ti leaves, which diners peel back to reveal the treasure inside</li>
<li>It\'s always accompanied by poi—the combination of salty pork and slightly sour poi is considered the perfect pairing</li>
<li>Rice is the modern accompaniment, with the pork juices mixed in for extra flavor</li>
<li>Lomi lomi salmon often sits alongside to provide a fresh, acidic contrast to the rich laulau</li>
<li>At luaus, laulau is typically served as a main course alongside kalua pig and chicken long rice</li>
</ul>

<h2>Ingredients for Authentic Laulau</h2>

<p><strong>Fresh vs. Store-Bought Options</strong></p>

<p>While some stores sell pre-made frozen laulau, nothing compares to homemade. Fresh taro leaves can be found at Asian markets or Hawaiian specialty stores on the mainland—look for leaves that are dark green and free of holes. If you can\'t find fresh taro leaves, some cooks substitute spinach or collard greens, though the flavor differs significantly. Ti leaves are essential for the outer wrapper and can be ordered online if not available locally. Never substitute the ti leaves with banana leaves for authentic results.</p>

<h3>Essential Tools</h3>
<ul>
<li>Large steamer pot or roasting pan with a rack</li>
<li>Aluminum foil (if ti leaves are unavailable)</li>
<li>Kitchen twine or ti leaf strips for tying</li>
<li>Sharp knife for butterflying pork</li>
<li>Large bowl for salting the pork</li>
</ul>

<h3>Optional Add-ins for Flavor</h3>
<ul>
<li>Hawaiian sea salt (alaea salt)</li>
<li>Salted butterfish (traditional addition)</li>
<li>Sweet potato chunks</li>
<li>Banana peppers for heat</li>
<li>Coconut milk brushed on leaves</li>
</ul>

<h2>Key Things to Know</h2>

<ul>
<li>The dish showcases the Hawaiian imu (underground oven) cooking tradition, though modern methods use stovetop steamers</li>
<li>Taro leaves (lū\'au) must be cooked thoroughly—raw leaves contain calcium oxalate which can irritate the throat</li>
<li>Authentic laulau requires patience, with steaming times of 4-6 hours for optimal tenderness</li>
<li>The dish is naturally gluten-free and rich in vitamins A and C from the taro leaves</li>
</ul>
'
),

array(
    'title' => 'Kalua Pig: Master the Art of Hawaiian Pulled Pork',
    'slug' => 'kalua-pig-recipe',
    'meta_description' => 'Make authentic kalua pig at home without digging a pit. This Hawaiian pulled pork uses just three ingredients for smoky, tender meat that melts in your mouth.',
    'categories' => array($cat_ids['hawaiian-traditional'], $cat_ids['recipes']),
    'tags' => array('kalua pig', 'pulled pork', 'luau', 'traditional hawaiian', 'slow cooker', 'imu'),
    'content' => '
<p>Close your eyes and imagine the moment a whole pig emerges from an underground oven at a Hawaiian luau—smoke rising, meat glistening, the air thick with the scent of earth and sea salt. That\'s kalua pig, the crown jewel of Hawaiian feasts.</p>

<p>The good news? You don\'t need to dig a pit in your backyard to make it.</p>

<h2>What Is Kalua Pig?</h2>

<p>Kalua pig (also spelled kālua) is the Hawaiian version of pulled pork, though comparing it to mainland BBQ doesn\'t do it justice. The word "kalua" means "to cook in an underground oven," describing the traditional imu method where a whole pig is wrapped in banana and ti leaves, then slow-roasted over hot lava rocks buried in the earth.</p>

<p>The result is incredibly tender meat with a distinctive smoky flavor and melt-in-your-mouth texture. Unlike American pulled pork with its sweet, tangy sauces, kalua pig celebrates simplicity—just pork, salt, and smoke.</p>

<h2>The Cultural Significance of Kalua Pig</h2>

<p>No Hawaiian celebration is complete without kalua pig. The imu ceremony itself is steeped in tradition, typically overseen by the most experienced cook in the family. Preparing the underground oven begins at dawn, with the pig going in by mid-morning and emerging hours later as the centerpiece of the feast.</p>

<p>The first cut of meat is traditionally offered to the land and ancestors before anyone eats. At baby luaus (first birthday celebrations), weddings, and graduations, kalua pig represents abundance, community, and the host\'s generosity. The preparation brings families together, with multiple generations working side by side.</p>

<h2>How Kalua Pig Is Traditionally Served</h2>

<ul>
<li>Kalua pig is shredded by hand and piled high on platters for guests to serve themselves</li>
<li>It\'s always paired with poi—eating kalua pig without poi is considered incomplete</li>
<li>Steamed rice serves as the modern carbohydrate companion</li>
<li>Cabbage cooked with the pork drippings creates a classic side dish called kalua pig and cabbage</li>
<li>Hawaiian sweet bread rolls are used to make sliders at more casual gatherings</li>
</ul>

<h2>Ingredients for Kalua Pig</h2>

<p><strong>Fresh vs. Store-Bought Options</strong></p>

<p>For the most authentic flavor, choose a bone-in pork shoulder (also called pork butt) with the fat cap intact. The fat is essential—it bastes the meat during the long cooking process. Some Hawaiian markets sell pre-seasoned kalua pork ready for the slow cooker, but seasoning your own gives you control over the salt level. Avoid pre-trimmed lean cuts, as the fat is what makes kalua pig so succulent. For smoking at home, use real Hawaiian mesquite wood chips or quality liquid smoke made from actual smoked water.</p>

<h3>Essential Tools</h3>
<ul>
<li>Slow cooker (6-quart or larger) OR roasting pan with lid</li>
<li>Meat thermometer</li>
<li>Two forks for shredding</li>
<li>Banana leaves (for wrapping, if available)</li>
<li>Heavy-duty aluminum foil</li>
</ul>

<h3>Optional Add-ins for Flavor</h3>
<ul>
<li>Liquid smoke (1-2 tablespoons)</li>
<li>Hawaiian alaea (red) salt</li>
<li>Ti leaves for wrapping</li>
<li>Banana leaves for lining the pot</li>
<li>Pineapple juice (modern variation)</li>
</ul>

<h2>Key Things to Know</h2>

<ul>
<li>The name "kalua" refers to the cooking method, not a flavor or sauce</li>
<li>Home cooks can replicate the smoky, tender results using a slow cooker, oven, or Instant Pot</li>
<li>Liquid smoke is the secret ingredient for achieving authentic imu flavor without the pit</li>
<li>True kalua pig uses only three ingredients: pork, Hawaiian salt, and smoke</li>
</ul>
'
),

array(
    'title' => 'How to Make Haupia: Hawaiian Coconut Pudding Dessert',
    'slug' => 'haupia-recipe',
    'meta_description' => 'Make authentic Hawaiian haupia with just four ingredients in 20 minutes. This jiggly coconut pudding is naturally dairy-free, gluten-free, and pure tropical bliss.',
    'categories' => array($cat_ids['hawaiian-traditional'], $cat_ids['tropical-treats'], $cat_ids['recipes']),
    'tags' => array('haupia', 'coconut', 'dessert', 'luau', 'traditional hawaiian', 'vegan', 'gluten-free'),
    'content' => '
<p>That jiggly white square on your luau plate isn\'t tofu—it\'s haupia, Hawaii\'s beloved coconut pudding that tastes like a tropical cloud. Simple yet addictive, this traditional dessert has been cooling down Hawaiian meals for generations.</p>

<p>With just four ingredients and twenty minutes, you can bring this island sweetness to your table.</p>

<h2>What Is Haupia?</h2>

<p>Haupia (how-pee-ah) is a coconut milk-based dessert that falls somewhere between pudding and gelatin in texture. Unlike creamy mainland puddings, haupia sets firm enough to be cut into squares yet melts on your tongue with pure coconut flavor.</p>

<p>The traditional version uses pia (Polynesian arrowroot) as a thickener, giving it a distinctive clean texture different from cornstarch-thickened versions. At its core, haupia is beautifully simple: coconut milk, sugar, water, and a thickener—nothing more, nothing less.</p>

<h2>The Cultural Significance of Haupia</h2>

<p>Haupia represents the ingenious simplicity of Hawaiian cuisine. Before sugar became common, early versions were only lightly sweetened or not at all. The dessert was traditionally wrapped in ti leaves and served at luaus alongside savory dishes to cleanse the palate.</p>

<p>Today, haupia has evolved beyond the traditional squares—it\'s the star of haupia pie (with a macadamia nut crust), a topping for shave ice, and the filling in chocolate haupia pie, which locals debate as passionately as mainlanders argue about pizza styles. Any Hawaiian celebration without haupia on the dessert table is simply incomplete.</p>

<h2>How Haupia Is Traditionally Served</h2>

<ul>
<li>Cut into 2-inch squares and served on ti leaves for traditional presentation</li>
<li>Placed at each setting on the luau plate, providing a sweet contrast to savory dishes</li>
<li>Layered with chocolate pudding for the famous chocolate haupia pie</li>
<li>Topped on Hawaiian sweet bread for haupia french toast</li>
<li>Used as a filling for malasadas (Portuguese-Hawaiian donuts)</li>
</ul>

<h2>Ingredients for Haupia</h2>

<p><strong>Fresh vs. Store-Bought Options</strong></p>

<p>For the creamiest haupia, use full-fat canned coconut milk or, even better, fresh coconut cream. Avoid "lite" coconut milk—the lower fat content produces a watery texture. Many local cooks prefer specific brands like Aroy-D or Chaokoh for consistent results. While instant haupia mix is available and convenient, homemade versions have superior coconut flavor. For thickening, cornstarch works well for home cooks, though purists seek out arrowroot (pia) for its cleaner texture and slight sheen.</p>

<h3>Essential Tools</h3>
<ul>
<li>Medium saucepan</li>
<li>Whisk</li>
<li>8x8 or 9x9 inch baking pan</li>
<li>Measuring cups and spoons</li>
<li>Spatula for scraping</li>
</ul>

<h3>Optional Add-ins for Flavor</h3>
<ul>
<li>Vanilla extract (1/2 teaspoon)</li>
<li>Toasted coconut flakes for garnish</li>
<li>Macadamia nuts (chopped)</li>
<li>Passion fruit puree swirl</li>
<li>Li hing mui powder dusting</li>
</ul>

<h2>Key Things to Know</h2>

<ul>
<li>Traditional haupia uses coconut cream and Polynesian arrowroot (pia), though modern versions use cornstarch</li>
<li>Haupia has a unique firm-yet-creamy texture that holds its shape when cut into squares</li>
<li>The dessert is naturally dairy-free, gluten-free, and vegan</li>
<li>Haupia is incredibly versatile—served alone, as pie filling, or as a topping for other Hawaiian treats</li>
</ul>
'
),

array(
    'title' => 'Lomi Lomi Salmon: The Fresh Side Dish Every Hawaiian Plate Needs',
    'slug' => 'lomi-lomi-salmon-recipe',
    'meta_description' => 'Make authentic lomi lomi salmon – Hawaii\'s refreshing side dish of cured salmon, tomatoes, and sweet onions massaged together. The perfect balance to rich luau dishes.',
    'categories' => array($cat_ids['hawaiian-traditional'], $cat_ids['poke-seafood'], $cat_ids['recipes']),
    'tags' => array('lomi lomi salmon', 'salmon', 'side dish', 'luau', 'traditional hawaiian', 'no cook'),
    'content' => '
<p>See that bright, colorful salad on the corner of your luau plate? That\'s lomi lomi salmon—a refreshing mix of cured salmon, tomatoes, and onions that\'s been massaged (literally) into Hawaiian food history. This no-cook dish brings a burst of freshness to balance all those rich, slow-cooked meats.</p>

<h2>What Is Lomi Lomi Salmon?</h2>

<p>Lomi lomi salmon is Hawaii\'s answer to ceviche—a fresh, no-cook dish that combines cubed salted salmon with diced tomatoes, sweet Maui onions, and sometimes green onions. The name comes from the Hawaiian word "lomi," meaning to massage, knead, or press, describing how the ingredients are gently mixed by hand rather than stirred with a spoon.</p>

<p>This technique allows the oils from the salmon to coat the vegetables while keeping everything in distinct, tender pieces. The result is a refreshing, salty-sweet-tangy side dish that cuts through the richness of kalua pig and laulau.</p>

<h2>The Cultural Significance of Lomi Lomi Salmon</h2>

<p>Lomi lomi salmon perfectly represents Hawaii\'s multicultural food evolution. Before Western contact, Hawaiians didn\'t have salmon—the fish was introduced by New England traders who brought barrels of salted salmon to trade for Hawaiian goods in the early 1800s.</p>

<p>Native Hawaiians adapted this new ingredient to their taste, combining it with locally abundant tomatoes and onions using their traditional lomi (massage) preparation technique. The dish has become so integral to Hawaiian cuisine that no luau plate is complete without it. It\'s a delicious example of how Hawaiian culture absorbs and transforms outside influences.</p>

<h2>How Lomi Lomi Salmon Is Traditionally Served</h2>

<ul>
<li>Served in a bowl or mound alongside poi, with the two often eaten together</li>
<li>Placed on the corner of a luau plate to add color and brightness</li>
<li>Served chilled, making it a refreshing contrast to warm dishes</li>
<li>Scooped with poi or eaten with rice</li>
<li>Sometimes served on crackers as a pupus (appetizer)</li>
</ul>

<h2>Ingredients for Lomi Lomi Salmon</h2>

<p><strong>Fresh vs. Store-Bought Options</strong></p>

<p>The most authentic lomi lomi starts with salting your own fresh salmon—a process that takes 24-48 hours but produces superior texture and flavor. Look for sashimi-grade salmon if available. For a quicker version, use store-bought lox, smoked salmon, or pre-cured salmon (though smoked salmon will alter the traditional flavor profile). Tomatoes should be firm Roma or vine-ripened varieties that hold their shape. For onions, Maui sweet onions are ideal, but Vidalia or any sweet white onion works well. Always use fresh ingredients—this is a dish where quality shows.</p>

<h3>Essential Tools</h3>
<ul>
<li>Sharp knife for dicing</li>
<li>Large mixing bowl (glass or ceramic preferred)</li>
<li>Clean hands for the "lomi" technique</li>
<li>Covered container for chilling</li>
<li>Cutting board</li>
</ul>

<h3>Optional Add-ins for Flavor</h3>
<ul>
<li>Crushed ice (mixed in for serving)</li>
<li>Green onions (chopped)</li>
<li>Hawaiian chili peppers (minced)</li>
<li>Limu (seaweed)</li>
<li>Cucumber (diced)</li>
</ul>

<h2>Key Things to Know</h2>

<ul>
<li>The name "lomi" means "to massage" in Hawaiian, describing the gentle hand-mixing technique</li>
<li>The dish was created after Western traders introduced salted salmon to Hawaii in the early 1800s</li>
<li>Traditional recipes call for salting your own salmon, though modern versions use store-bought cured salmon</li>
<li>Lomi lomi salmon is served cold, making it perfect for hot weather and outdoor gatherings</li>
</ul>
'
),

array(
    'title' => 'How to Make Poke: Hawaii\'s Original Raw Fish Salad',
    'slug' => 'poke-recipe',
    'meta_description' => 'Learn to make authentic Hawaiian poke the traditional way – fresh fish, sea salt, seaweed, and kukui nut. Simple, pure, and nothing like mainland poke bowls.',
    'categories' => array($cat_ids['hawaiian-traditional'], $cat_ids['poke-seafood'], $cat_ids['recipes']),
    'tags' => array('poke', 'ahi', 'tuna', 'raw fish', 'traditional hawaiian', 'seafood'),
    'content' => '
<p>Before poke bowls became a mainland obsession, this raw fish dish was a simple Hawaiian fisherman\'s snack—cubed catch of the day seasoned with sea salt, seaweed, and kukui nuts. Somewhere between sushi and ceviche, poke (pronounced poh-kay) is Hawaii\'s gift to seafood lovers everywhere.</p>

<h2>What Is Poke?</h2>

<p>Poke is Hawaii\'s beloved raw fish salad, with a history stretching back to ancient Polynesian fishermen who seasoned their fresh catch with whatever was available—sea salt, limu (seaweed), and inamona (roasted candlenut). The word "poke" simply means "to slice or cut crosswise into pieces" in Hawaiian.</p>

<p>Traditional Hawaiian poke keeps it simple: cubed raw fish, Hawaiian salt, seaweed, and kukui nut—no rice, no spicy mayo, no mango. It\'s about tasting the ocean, not burying it under toppings. The mainland poke bowl craze has evolved far from these roots, but authentic Hawaiian poke remains a study in simplicity.</p>

<h2>The Cultural Significance of Poke</h2>

<p>Poke is woven into everyday Hawaiian life in a way few dishes are. It\'s the snack you grab from the supermarket seafood counter, the pupus (appetizer) at every backyard party, the quick lunch eaten standing at the kitchen counter.</p>

<p>Unlike luau foods reserved for celebrations, poke is democratic—fishermen and executives alike eat it weekly. The dish connects modern Hawaiians to their fishing heritage; the simple preparation honors the ocean\'s bounty by letting the fish\'s quality speak for itself. Debates over "authentic" poke versus "mainland" poke stir surprising passion among locals, reflecting how deeply this dish defines Hawaiian food identity.</p>

<h2>How Poke Is Traditionally Served</h2>

<ul>
<li>Eaten alone as a snack or pupus, scooped with fingers or a fork</li>
<li>Served over hot rice as a quick meal (though purists consider this "modern")</li>
<li>Displayed in deli cases at Hawaiian supermarkets where you can mix and match styles</li>
<li>Passed around at parties in a communal bowl with toothpicks</li>
<li>At high-end restaurants, plated simply with minimal garnish to highlight the fish</li>
</ul>

<h2>Ingredients for Authentic Poke</h2>

<p><strong>Fresh vs. Store-Bought Options</strong></p>

<p>Poke is 90% about fish quality. Buy sushi-grade ahi (yellowfin tuna) or aku (skipjack) from a trusted fishmonger—the fish should smell like the ocean, never "fishy." If the fish isn\'t pristine, don\'t make poke. Period. For limu (seaweed), ogo and limu kohu are traditional choices, available at Asian markets or online. Hawaiian sea salt (pa\'akai) offers better flavor than table salt. Inamona (roasted kukui nut) can be found at Hawaiian specialty stores or substituted with macadamia nuts, though the flavor differs. Avoid pre-made poke unless from a reputable Hawaiian market.</p>

<h3>Essential Tools</h3>
<ul>
<li>Sharp knife for clean fish cuts</li>
<li>Cutting board (wood or plastic)</li>
<li>Glass or ceramic mixing bowl</li>
<li>Measuring spoons</li>
<li>Serving bowl</li>
</ul>

<h3>Optional Add-ins for Flavor</h3>
<ul>
<li>Ogo or limu kohu (seaweed)</li>
<li>Inamona (roasted kukui nut) or macadamia nuts</li>
<li>Sweet Maui onion (thinly sliced)</li>
<li>Hawaiian chili pepper flakes</li>
<li>Sesame oil (Japanese-influenced version)</li>
</ul>

<h2>Key Things to Know</h2>

<ul>
<li>Poke (poh-kay) means "to slice or cut" in Hawaiian and refers to any cubed raw fish dish</li>
<li>Traditional Hawaiian poke uses simple ingredients: fresh fish, sea salt, limu (seaweed), and inamona (roasted kukui nut)</li>
<li>Modern "mainland" poke with heavy sauces and toppings differs significantly from authentic Hawaiian versions</li>
<li>Sushi-grade ahi (yellowfin tuna) or aku (skipjack tuna) are the traditional fish choices</li>
<li>Fresh poke should be eaten within hours of preparation for optimal flavor and texture</li>
</ul>
'
),

array(
    'title' => 'Chicken Long Rice: Hawaiian Comfort Food at Its Best',
    'slug' => 'chicken-long-rice-recipe',
    'meta_description' => 'Make authentic chicken long rice – Hawaiian comfort food with silky glass noodles and tender chicken in ginger broth. A luau essential that warms the soul.',
    'categories' => array($cat_ids['hawaiian-traditional'], $cat_ids['island-comfort'], $cat_ids['recipes']),
    'tags' => array('chicken long rice', 'glass noodles', 'soup', 'luau', 'traditional hawaiian', 'comfort food'),
    'content' => '
<p>Despite its name, there\'s no rice in chicken long rice. This savory, soupy Hawaiian dish features silky glass noodles swimming with tender chicken in a ginger-infused broth. Brought to Hawaii by Chinese immigrants, it\'s become a luau essential that warms the soul.</p>

<h2>What Is Chicken Long Rice?</h2>

<p>Chicken long rice is a Hawaiian adaptation of Chinese glass noodle soup that has become a staple at luaus and local gatherings. The "long rice" refers to long, translucent bean thread noodles (also called cellophane or glass noodles) made from mung bean starch.</p>

<p>These slippery noodles absorb the flavors of the ginger-chicken broth, becoming wonderfully silky. The dish features bone-in chicken pieces (thighs are preferred for flavor), loads of fresh ginger, and green onions. Depending on the cook, it\'s served soupy like a brothy noodle dish or drier as a side, with the noodles absorbing most of the liquid.</p>

<h2>The Cultural Significance of Chicken Long Rice</h2>

<p>Chicken long rice represents Hawaii\'s beautiful fusion of cultures. When Chinese laborers arrived to work the sugar plantations in the mid-1800s, they brought their food traditions. Glass noodle dishes were adapted to local tastes and available ingredients, eventually becoming so beloved that no luau is complete without a large pot of chicken long rice.</p>

<p>The dish exemplifies "local food"—cuisine that transcends any single ethnic origin to become uniquely Hawaiian. It sits alongside kalua pig and laulau at celebrations, showing how Hawaii integrates diverse cultures into a cohesive food identity.</p>

<h2>How Chicken Long Rice Is Traditionally Served</h2>

<ul>
<li>Ladled into bowls as a communal dish at luaus and family gatherings</li>
<li>Served on the plate lunch alongside rice, kalua pig, and mac salad</li>
<li>Eaten as a main course soup during cooler months</li>
<li>Topped generously with sliced green onions</li>
<li>Sometimes served with a side of chili pepper water for added heat</li>
</ul>

<h2>Ingredients for Chicken Long Rice</h2>

<p><strong>Fresh vs. Store-Bought Options</strong></p>

<p>For the most authentic flavor, use bone-in, skin-on chicken thighs—the bones and skin add richness to the broth. Fresh ginger is non-negotiable; the amount should be generous (2-3 inches for a standard pot). Bean thread noodles are sold dried at any Asian market; look for brands made from mung bean starch for the best texture. Avoid "rice stick" noodles, which are different. Hawaiian cooks often use chicken bouillon for extra depth, though homemade stock works beautifully. Avoid shortcut broths—this dish\'s soul is in its simmered-all-day flavor.</p>

<h3>Essential Tools</h3>
<ul>
<li>Large pot or Dutch oven</li>
<li>Strainer or colander</li>
<li>Kitchen shears for cutting noodles</li>
<li>Ladle for serving</li>
<li>Large bowl for soaking noodles</li>
</ul>

<h3>Optional Add-ins for Flavor</h3>
<ul>
<li>Shiitake mushrooms (sliced)</li>
<li>Napa cabbage</li>
<li>Sesame oil (a few drops)</li>
<li>Fish sauce (splash for depth)</li>
<li>Won bok (Chinese cabbage)</li>
</ul>

<h2>Key Things to Know</h2>

<ul>
<li>The "long rice" refers to the cellophane noodles, not actual rice</li>
<li>The dish was introduced by Chinese immigrant laborers in Hawaii during the 1800s</li>
<li>It can be served as a soup, a side dish, or a main course depending on broth consistency</li>
<li>Chicken long rice is naturally gluten-free when made with pure bean thread noodles</li>
</ul>
'
),

array(
    'title' => 'Squid Luau: Creamy Coconut Taro Leaf Stew',
    'slug' => 'squid-luau-recipe',
    'meta_description' => 'Discover squid luau – Hawaii\'s best-kept culinary secret. This velvety stew of octopus and taro leaves in coconut milk is rich, creamy, and unforgettable.',
    'categories' => array($cat_ids['hawaiian-traditional'], $cat_ids['poke-seafood'], $cat_ids['recipes']),
    'tags' => array('squid luau', 'octopus', 'taro leaves', 'coconut', 'luau', 'traditional hawaiian'),
    'content' => '
<p>Rich, creamy, and unlike anything on the mainland—squid luau (pronounced loo-ow) is one of Hawaii\'s best-kept culinary secrets. This velvety stew combines tender octopus or squid with taro leaves cooked in coconut milk. It\'s an acquired taste that becomes an addiction.</p>

<h2>What Is Squid Luau?</h2>

<p>Squid luau (also spelled lū\'au) is a luxuriously creamy stew where tender pieces of octopus or squid melt into silky taro leaves, all bound together by rich coconut milk. The dish showcases lū\'au (taro leaves), which when properly cooked become soft and almost spinach-like, absorbing the coconut milk until everything melds into velvety goodness.</p>

<p>Don\'t let the name fool you—while it\'s called "squid" luau, many Hawaiian families use octopus (he\'e), and some modern versions use chicken for those less adventurous. The result is rich, savory, and unmistakably Hawaiian.</p>

<h2>The Cultural Significance of Squid Luau</h2>

<p>Squid luau connects to ancient Hawaiian food traditions where both taro and seafood were dietary staples. The dish utilizes every part of the sacred taro plant—while the root becomes poi, the leaves become lū\'au.</p>

<p>Traditionally, this stew was cooked in an imu alongside other luau dishes, allowing the flavors to develop over hours. The dish represents how Native Hawaiians honored ocean and land resources equally. Today, squid luau is a beloved luau plate staple, though many non-Hawaiians overlook it. For locals, it\'s often the first dish they reach for—a nostalgic taste of home that\'s hard to replicate anywhere else.</p>

<h2>How Squid Luau Is Traditionally Served</h2>

<ul>
<li>Served in a small portion on the traditional luau plate</li>
<li>Eaten with poi, which balances the coconut richness</li>
<li>Enjoyed over hot rice to soak up the creamy sauce</li>
<li>Passed family-style in a large bowl at gatherings</li>
<li>Sometimes served as a pupus with crackers or bread for dipping</li>
</ul>

<h2>Ingredients for Squid Luau</h2>

<p><strong>Fresh vs. Store-Bought Options</strong></p>

<p>Fresh taro leaves (lū\'au) are essential and can be found at Hawaiian markets or ordered online. Frozen taro leaves work in a pinch. Never use raw taro leaves without proper cooking time—they contain calcium oxalate that causes throat irritation until fully cooked (minimum 1-2 hours). For the protein, cleaned and prepared octopus is available frozen at Asian markets (often labeled "tako"); it\'s usually pre-tenderized. Fresh squid works but has different texture. Full-fat coconut milk or cream is essential—light versions won\'t achieve the proper richness.</p>

<h3>Essential Tools</h3>
<ul>
<li>Large heavy pot with lid</li>
<li>Wooden spoon</li>
<li>Sharp knife for cutting octopus</li>
<li>Measuring cups for coconut milk</li>
<li>Timer (for taro leaf cooking)</li>
</ul>

<h3>Optional Add-ins for Flavor</h3>
<ul>
<li>Butter (for added richness)</li>
<li>Hawaiian salt to taste</li>
<li>Chicken (as protein substitute)</li>
<li>Baking soda (helps tenderize taro leaves)</li>
<li>Onion (diced)</li>
</ul>

<h2>Key Things to Know</h2>

<ul>
<li>The dish name comes from "lū\'au," the Hawaiian word for taro leaves (same word that names the feast)</li>
<li>Taro leaves must be cooked for at least 1-2 hours to neutralize naturally occurring calcium oxalate</li>
<li>Despite its name, squid luau more commonly features octopus (he\'e) than squid in traditional recipes</li>
<li>The dish is rich in protein, iron, and vitamins A and C</li>
</ul>
'
),

array(
    'title' => 'Pipikaula: Hawaiian Beef Jerky That Melts in Your Mouth',
    'slug' => 'pipikaula-recipe',
    'meta_description' => 'Make authentic pipikaula – Hawaiian dried beef that\'s thick, tender, and charred on the outside. This paniolo (cowboy) food will change how you think about jerky.',
    'categories' => array($cat_ids['hawaiian-traditional'], $cat_ids['pupus-snacks'], $cat_ids['recipes']),
    'tags' => array('pipikaula', 'beef jerky', 'dried beef', 'paniolo', 'traditional hawaiian', 'bbq'),
    'content' => '
<p>Forget gas station jerky—pipikaula is Hawaii\'s original dried beef, and it\'s in a class of its own. These thick, tender strips are salted, sun-dried, then grilled until the outside chars while the inside stays juicy. It\'s Hawaiian paniolo (cowboy) food that\'ll change how you think about dried meat forever.</p>

<h2>What Is Pipikaula?</h2>

<p>Pipikaula (pee-pee-cow-la) is Hawaii\'s answer to beef jerky, but calling it jerky doesn\'t do it justice. These thick strips of beef flank or brisket are cured in Hawaiian salt, air-dried (traditionally in the sun), then grilled or broiled until the exterior caramelizes while the inside stays remarkably tender.</p>

<p>Unlike the thin, tough jerky you find in convenience stores, pipikaula is cut thick—about 1/2 inch—and retains enough fat to keep it moist. The result is smoky, salty, and beefy, with a charred exterior giving way to pink, juicy meat. It\'s often called Hawaiian cowboy food for good reason.</p>

<h2>The Cultural Significance of Pipikaula</h2>

<p>Pipikaula tells the story of Hawaii\'s paniolo (cowboy) culture. When Spanish and Portuguese vaqueros came to Hawaii in the 1830s to teach Hawaiians how to manage the wild cattle roaming the islands, they brought dried beef traditions.</p>

<p>Hawaiian paniolos adapted these methods, creating pipikaula as a way to preserve meat in the tropical climate. The dish became ranching food—portable, protein-rich, and incredibly satisfying after a long day on horseback. Today, pipikaula is found at local plate lunch spots, Hawaiian BBQs, and family gatherings, keeping the paniolo spirit alive.</p>

<h2>How Pipikaula Is Traditionally Served</h2>

<ul>
<li>Sliced thin after grilling and served as pupus (appetizers)</li>
<li>Included on plate lunches with rice and mac salad</li>
<li>Eaten straight off the grill at backyard BBQs</li>
<li>Served with beer as the ultimate Hawaiian bar snack</li>
<li>Offered alongside poi and other traditional dishes at celebrations</li>
</ul>

<h2>Ingredients for Pipikaula</h2>

<p><strong>Fresh vs. Store-Bought Options</strong></p>

<p>Start with well-marbled beef flank steak or brisket—the fat is crucial for keeping pipikaula tender. Cut with the grain into strips about 1/2 inch thick and 1-2 inches wide. Hawaiian sea salt (pa\'akai) or alaea (red salt) are traditional; their mineral content adds flavor kosher salt can\'t match. While sun-drying for 1-2 days is traditional, modern cooks use dehydrators or air-dry in the refrigerator for 2-3 days. Some local markets sell pre-dried pipikaula ready for grilling—look for thick, pliable strips, not hard jerky.</p>

<h3>Essential Tools</h3>
<ul>
<li>Sharp knife for slicing beef</li>
<li>Baking sheet or drying rack</li>
<li>Grill or broiler</li>
<li>Refrigerator (for modern drying method)</li>
<li>Resealable bags or containers</li>
</ul>

<h3>Optional Add-ins for Flavor</h3>
<ul>
<li>Hawaiian chili pepper flakes</li>
<li>Soy sauce (in marinade)</li>
<li>Brown sugar (light touch)</li>
<li>Garlic (minced)</li>
<li>Sesame seeds (for garnish)</li>
</ul>

<h2>Key Things to Know</h2>

<ul>
<li>The name combines "pipi" (Hawaiian for beef) and "kaula" (rope or string)</li>
<li>Unlike jerky, pipikaula is thick-cut and remains tender due to its fat content and serving method</li>
<li>The dish dates back to Hawaii\'s ranching era when Portuguese and Spanish vaqueros taught Hawaiians cattle ranching</li>
<li>Traditional preparation involves salt-curing and sun-drying, followed by grilling</li>
</ul>
'
),

array(
    'title' => 'Kulolo: Traditional Hawaiian Taro Coconut Pudding',
    'slug' => 'kulolo-recipe',
    'meta_description' => 'Make authentic kulolo – Hawaii\'s dense, fudge-like taro coconut pudding. This traditional dessert showcases the sacred kalo plant in its most delicious form.',
    'categories' => array($cat_ids['hawaiian-traditional'], $cat_ids['tropical-treats'], $cat_ids['recipes']),
    'tags' => array('kulolo', 'taro', 'coconut', 'dessert', 'luau', 'traditional hawaiian'),
    'content' => '
<p>If haupia is Hawaii\'s light coconut dessert, kulolo is its rich, earthy cousin. This dense, sticky pudding made from taro and coconut has been satisfying Hawaiian sweet tooths for generations. With its deep purple color and fudge-like texture, kulolo is the dessert for anyone who thinks they\'ve tried everything Hawaiian.</p>

<h2>What Is Kulolo?</h2>

<p>Kulolo is a dense, sticky Hawaiian pudding that showcases the earthy sweetness of taro root. Unlike poi, which uses cooked taro, kulolo combines raw grated taro with coconut milk and sugar, then slow-bakes or steams until everything transforms into a thick, fudge-like treat.</p>

<p>The result is deeply purple-brown, with a chewy texture that\'s almost caramelized on the edges. The flavor is complex—earthy from the taro, rich from the coconut, sweet but not overwhelming. It\'s Hawaiian comfort food in dessert form, dense and satisfying in a way that airy haupia isn\'t.</p>

<h2>The Cultural Significance of Kulolo</h2>

<p>Kulolo honors kalo (taro), which Hawaiians consider a sacred ancestor plant. While poi uses the taro corm, kulolo showcases how versatile this staple is. Traditionally, kulolo was wrapped in ti leaves and slow-cooked in the imu alongside savory dishes, absorbing smoky undertones.</p>

<p>The dessert was often made for special occasions and offered at hula ceremonies—its preparation required patience and skill, making it a labor of love. Today, kulolo is less common than haupia but treasured by those who know it. Finding authentic kulolo often means knowing a local aunty who makes it for special occasions.</p>

<h2>How Kulolo Is Traditionally Served</h2>

<ul>
<li>Sliced into squares or wedges from a baking pan</li>
<li>Served at room temperature or slightly warm</li>
<li>Offered alongside haupia at luaus for contrasting textures</li>
<li>Cut into small pieces for dessert buffets</li>
<li>Wrapped in ti leaves as a gift for guests at celebrations</li>
</ul>

<h2>Ingredients for Kulolo</h2>

<p><strong>Fresh vs. Store-Bought Options</strong></p>

<p>Authentic kulolo requires fresh taro root, which must be handled carefully due to its calcium oxalate content—use gloves when grating raw taro. Look for large taro corms at Asian markets; avoid small, soft, or moldy ones. Full-fat coconut milk or fresh coconut cream is essential; light versions won\'t achieve the proper richness. Brown sugar is traditional, but some cooks use coconut sugar for deeper flavor. While some Hawaiian bakeries sell kulolo, homemade versions have superior texture. Poi should not be substituted for fresh grated taro—the textures are completely different.</p>

<h3>Essential Tools</h3>
<ul>
<li>Food processor or box grater</li>
<li>9x13 baking pan or loaf pan</li>
<li>Heavy-duty aluminum foil or ti leaves</li>
<li>Disposable gloves (for handling raw taro)</li>
<li>Mixing bowl</li>
</ul>

<h3>Optional Add-ins for Flavor</h3>
<ul>
<li>Vanilla extract (1 teaspoon)</li>
<li>Macadamia nuts (chopped)</li>
<li>Toasted coconut flakes</li>
<li>Ti leaves for wrapping</li>
<li>Banana (mashed, for a variation)</li>
</ul>

<h2>Key Things to Know</h2>

<ul>
<li>Kulolo has a dense, fudge-like texture and deep purple-brown color</li>
<li>Traditional kulolo was steamed in ti leaf bundles in the imu (underground oven)</li>
<li>It\'s one of the few Hawaiian desserts that showcase the sacred kalo (taro) plant</li>
<li>Kulolo is naturally gluten-free and can be made vegan with coconut sugar</li>
</ul>
'
),

array(
    'title' => 'Hawaiian Macaroni Salad: The Creamy Side That Makes the Plate',
    'slug' => 'hawaiian-macaroni-salad-recipe',
    'meta_description' => 'Make authentic Hawaiian mac salad – creamier, sweeter, and softer than mainland versions. The secret to this plate lunch essential that ties everything together.',
    'categories' => array($cat_ids['hawaiian-traditional'], $cat_ids['island-comfort'], $cat_ids['recipes']),
    'tags' => array('macaroni salad', 'mac salad', 'plate lunch', 'side dish', 'traditional hawaiian', 'comfort food'),
    'content' => '
<p>Every Hawaiian plate lunch has three essential elements: protein, rice, and the mysterious scoop of creamy, slightly sweet macaroni salad that ties it all together. This isn\'t your mainland church potluck mac salad—it\'s softer, creamier, and absolutely essential to the local food experience.</p>

<h2>What Is Hawaiian Macaroni Salad?</h2>

<p>Hawaiian macaroni salad is a creamy, slightly sweet pasta salad that\'s become inseparable from the island plate lunch experience. What makes it distinctly Hawaiian?</p>

<p>First, the texture—the pasta is cooked beyond al dente until very soft, then rested so it absorbs the dressing. Second, the creaminess—there\'s significantly more mayonnaise than mainland versions, creating an almost saucy consistency. Third, the simplicity—traditional recipes use just macaroni, mayo, milk, grated carrot, and maybe celery and onion. No fancy add-ins, no tangy dressings, just pure creamy comfort that balances the salty, savory proteins.</p>

<h2>The Cultural Significance of Hawaiian Macaroni Salad</h2>

<p>Hawaiian macaroni salad emerged from the multicultural plantation era when workers from Japan, China, Portugal, the Philippines, and more worked side by side in the sugar cane fields. The plate lunch evolved as a practical way to fuel long work days—cheap, filling, and portable in sectioned lunch wagons.</p>

<p>American-style macaroni salad was adapted to local tastes, becoming sweeter and creamier to complement the bold flavors of dishes like kalua pig and chicken katsu. Today, locals judge plate lunch spots as much by their mac salad as their entrées. A bad mac salad can tank an otherwise good meal.</p>

<h2>How Hawaiian Macaroni Salad Is Traditionally Served</h2>

<ul>
<li>As one scoop on the standard Hawaiian plate lunch alongside two scoops of rice</li>
<li>Made in large batches for family gatherings, luaus, and potlucks</li>
<li>Served cold or at room temperature</li>
<li>Placed on the plate to be mixed with bites of protein and rice</li>
<li>Offered at virtually every local restaurant, from diners to beach wagons</li>
</ul>

<h2>Ingredients for Hawaiian Macaroni Salad</h2>

<p><strong>Fresh vs. Store-Bought Options</strong></p>

<p>Use regular elbow macaroni, not "fancy" pasta shapes. The brand matters less than the cooking method—boil it 2-3 minutes beyond the package directions until very soft but not falling apart. Best Foods (Hellmann\'s) mayonnaise is the preferred brand among Hawaiian cooks; its slight sweetness is part of the authentic flavor. Whole milk helps achieve the creamy consistency. For vegetables, keep it minimal: finely grated carrot (not chunked), tiny diced celery, and optionally minced onion. Apple cider vinegar adds a subtle tang, but go easy—this isn\'t meant to be tangy.</p>

<h3>Essential Tools</h3>
<ul>
<li>Large pot for boiling pasta</li>
<li>Colander</li>
<li>Large mixing bowl</li>
<li>Box grater or food processor (for carrots)</li>
<li>Rubber spatula</li>
</ul>

<h3>Optional Add-ins for Flavor</h3>
<ul>
<li>Apple cider vinegar (1-2 teaspoons)</li>
<li>Sugar (1 teaspoon)</li>
<li>Hard-boiled eggs (diced)</li>
<li>Celery (finely minced)</li>
<li>Green onion (garnish)</li>
</ul>

<h2>Key Things to Know</h2>

<ul>
<li>Hawaiian macaroni salad is creamier, sweeter, and simpler than mainland versions</li>
<li>The pasta is intentionally overcooked until very soft, then rested to absorb the dressing</li>
<li>The salad uses more mayonnaise and less add-ins than typical American mac salads</li>
<li>It\'s a staple of the Hawaiian plate lunch, served alongside rice and a protein</li>
<li>The dish evolved from American influences during Hawaii\'s plantation era</li>
</ul>
'
),

); // End $posts array

// ============================================
// STEP 3: Create Posts
// ============================================

echo "Creating posts...\n\n";

$created = 0;
$errors = 0;

foreach ($posts as $post_data) {
    // Check if post already exists
    $existing = get_page_by_path($post_data['slug'], OBJECT, 'post');
    if ($existing) {
        echo "  - Exists: {$post_data['title']}\n";
        continue;
    }

    // Create the post
    $post_id = wp_insert_post(array(
        'post_title'    => $post_data['title'],
        'post_name'     => $post_data['slug'],
        'post_content'  => trim($post_data['content']),
        'post_status'   => 'draft', // All posts created as drafts
        'post_author'   => 1,
        'post_type'     => 'post',
        'post_category' => array_filter($post_data['categories']),
    ));

    if (is_wp_error($post_id)) {
        echo "  ✗ Error: {$post_data['title']} - " . $post_id->get_error_message() . "\n";
        $errors++;
        continue;
    }

    // Add tags
    if (!empty($post_data['tags'])) {
        wp_set_post_tags($post_id, $post_data['tags']);
    }

    // Add meta description (Yoast SEO)
    update_post_meta($post_id, '_yoast_wpseo_metadesc', $post_data['meta_description']);

    // Add meta description (RankMath SEO)
    update_post_meta($post_id, 'rank_math_description', $post_data['meta_description']);

    echo "  ✓ Created: {$post_data['title']}\n";
    $created++;
}

echo "\n===========================================\n";
echo "Import Complete!\n";
echo "===========================================\n";
echo "Posts created: $created\n";
echo "Errors: $errors\n";
echo "Status: All posts created as DRAFTS\n";
echo "\nNext steps:\n";
echo "1. Go to WordPress Admin > Posts\n";
echo "2. Review each draft post\n";
echo "3. Add featured images\n";
echo "4. Add WP Tasty recipe cards manually\n";
echo "5. Publish when ready\n";
echo "===========================================\n";
