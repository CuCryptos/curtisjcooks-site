# CurtisJCooks.com - Project Context for Claude

## Project Overview
CurtisJCooks.com is a Hawaiian food and recipe blog built on WordPress. The site features authentic Hawaiian recipes, cultural food stories, and local island cuisine.

## Current Site Statistics (Updated Feb 8, 2026)
- **Published Posts:** ~120 (including 20 new posts added Feb 8)
- **Published Pages:** 16 (including 7 pillar/guide pages)
- **SEO Plugin:** Rank Math SEO (active)
- **Sitemap:** https://curtisjcooks.com/sitemap_index.xml
- **Analytics:** Independent Analytics (IAWP) - free version, tracks page views only (no time-on-page or click tracking)
- **Caching:** Triple-layer: Bluehost Endurance (level 2) + Nginx + Cloudflare CDN (2-hour TTL)

## Environment

### Local Development
- **Platform:** Local by Flywheel
- **Local URL:** https://curtisjcookscom1.local
- **WordPress Root:** `/Users/curtisvaughan/Local Sites/curtisjcookscom1/app/public/`
- **Theme:** suspended-flavor-child (child theme of Flavor/Flavor flavor)
- **Database Prefix:** `hMq_`
- **PHP:** Via Local's lightning-services

### Live Site
- **URL:** https://curtisjcooks.com
- **Admin:** https://curtisjcooks.com/wp-admin
- **Username:** curtv74
- **Hosting:** Bluehost
- **cPanel:** https://box4190.bluehost.com:2083
- **cPanel User:** tptsgqmy
- **File Manager Path:** public_html/wp-content/themes/suspended-flavor-child/
- **SSH:** Not available (Bluehost rejects key imports)

### Database Access (Local)
```bash
# MySQL socket
/Users/curtisvaughan/Library/Application Support/Local/run/JdKBCEfQI/mysql/mysqld.sock

# Connect command
/Applications/Local.app/Contents/Resources/extraResources/lightning-services/mysql-8.0.35+4/bin/darwin-arm64/bin/mysql -u root -proot --socket="/Users/curtisvaughan/Library/Application Support/Local/run/JdKBCEfQI/mysql/mysqld.sock" -e "QUERY" local
```

## Content Structure

### Post Categories (with current counts)
| Category | ID | Posts |
|----------|-----|-------|
| Recipes (parent) | 26 | 106 |
| Island Comfort | 860 | 31 |
| Top Articles | 857 | 25 |
| Quick & Easy | 866 | 17 |
| Poke & Seafood | 859 | 16 |
| Tropical Treats | 861 | 11 |
| Island Drinks | 862 | 11 |
| Hawaiian Breakfast | 873 | 10 |
| Pupus & Snacks | 874 | 10 |
| Kitchen Essentials | 101 | 10 |
| Kitchen Skills | 107 | 8 |

### Pillar Pages (SEO Hub-and-Spoke Architecture)
| Page | Slug | Category Covered |
|------|------|------------------|
| Complete Guide to Hawaiian Poke | guide-hawaiian-poke | Poke & Seafood |
| Mastering Hawaiian Plate Lunch | guide-plate-lunch | Island Comfort |
| Essential Hawaiian Ingredients | guide-hawaiian-ingredients | Cross-category |
| Hawaiian Breakfast Guide | hawaiian-breakfast-guide | Hawaiian Breakfast |
| Island Drinks Guide | hawaiian-drinks-guide | Island Drinks |
| Hawaiian Desserts Guide | hawaiian-desserts-guide | Tropical Treats |
| Hawaiian Pupus Guide | hawaiian-pupus-guide | Pupus & Snacks |

**Note:** CJC Auto Schema plugin uses slugs (not IDs) to detect pillar pages for FAQ schema.

**Internal Linking Strategy:**
- Each cluster post links back to its pillar page
- Pillar pages cross-link to each other
- Related posts link to each other within categories

### Custom Post Types
- `post` - Standard blog posts/recipes
- `cjc_recipe` - Custom recipe post type (with structured metadata)

### Recipe System
Located in `/wp-content/themes/suspended-flavor-child/inc/recipe/`:
- `class-cjc-recipe-post-type.php` - Custom post type registration
- `class-cjc-recipe-meta.php` - Recipe metadata fields
- `class-cjc-recipe-rest-api.php` - REST API endpoints
- `class-cjc-recipe-block.php` - Gutenberg block
- `class-cjc-recipe-schema.php` - Structured data/schema markup

### Recipe Metadata Fields (prefix: `_cjc_recipe_`)
- `description`, `author_name`, `keywords`
- `prep_time`, `cook_time`, `total_time`
- `yield`, `yield_number`
- `category`, `cuisine`, `method`, `diet`
- `ingredients` (JSON array)
- `instructions` (JSON array)
- `notes`, `video_url`
- Nutrition: `calories`, `fat`, `carbohydrates`, `protein`, etc.

## Content Conventions

### Post Format
Posts follow a storytelling narrative style:
1. Opening hook/personal story
2. "What Is [Dish]?" section
3. "The Cultural Significance" section
4. "How It's Traditionally Served" (bulleted list)
5. "Ingredients" section with Fresh vs. Store-Bought guidance
6. Essential Tools list
7. Optional Add-ins list
8. Recipe card (if applicable)
9. Key Things to Know

### Writing Style
- Personal, conversational tone
- First-person narrative with Hawaiian cultural context
- Use Hawaiian words with translations (e.g., "laulau (pronounced lah-oo-lah-oo)")
- Include cultural significance and history
- Practical tips for mainland cooks finding ingredients

## Image Management

### Directories
- `/wp-content/uploads/recipe-images/` - High-quality recipe food photos
- `/wp-content/uploads/site-images/` - Homepage & structural images
- `/wp-content/uploads/generated-images/` - AI-generated featured images
- `/wp-content/uploads/YYYY/MM/` - Date-based WordPress uploads

### Image Naming Convention
`{post_id}-{post-slug}.png`
Example: `5332-authentic-laulau-recipe.png`

### Image Generation (Gemini/Imagen)
```python
from google import genai
from google.genai import types

client = genai.Client(api_key="GEMINI_API_KEY")
response = client.models.generate_images(
    model="imagen-4.0-generate-001",
    prompt="Professional food photography of...",
    config=types.GenerateImagesConfig(
        number_of_images=1,
        aspect_ratio="16:9",
        safety_filter_level="BLOCK_LOW_AND_ABOVE",
        person_generation="DONT_ALLOW"
    )
)
```

## Key Files

### Import Scripts
- `/wp-content/themes/suspended-flavor-child/import-posts.php` - Bulk post import script
- Run via: `https://curtisjcookscom1.local/import-posts.php` (copy to root first)

### Theme Files
- `/wp-content/themes/suspended-flavor-child/functions.php` - Theme functionality
- `/wp-content/themes/suspended-flavor-child/single.php` - Single post template
- `/wp-content/themes/suspended-flavor-child/style.css` - Custom CSS

## Common Tasks

### Create New Posts (Preferred Method - MCP)
Use MCP tools to create posts directly on live site:
1. `wp_create_post` with title, content, status="draft"
2. `wp_upload_media` with local image path
3. `wp_set_featured_image` to assign image
4. `wp_update_post` to add categories
5. Review in wp-admin, publish when ready

### Create New Posts via Import Script (Bulk)
For bulk imports when MCP is impractical:
1. Add post array to `import-posts.php`
2. Copy script to WordPress root
3. Visit URL while logged in as admin
4. Delete script from root after import

### Generate Featured Images
Option 1 - Gemini/Imagen API:
```python
# Use Imagen 4.0 for food photography
model="imagen-4.0-generate-001"
aspect_ratio="16:9"
```

Option 2 - MCP Recipe Image Tool:
```
generate_recipe_image(dish_name="Loco Moco", filename="loco-moco", post_id=1234)
```
Note: Requires GEMINI_API_KEY environment variable

### Sync Content to Live Site
**No manual sync needed** - MCP connects directly to live site.
- Posts created via MCP → immediately on live site
- Images uploaded via MCP → immediately in live media library
- Theme files → edit via Bluehost File Manager

## Installed Plugins
- **Rank Math SEO** - Active, handles sitemaps and SEO
- **CJC Auto Schema** - Active, auto-generates Recipe and FAQ schema (see below)
- **CJC Recipe Features** - Active, adds print/save/scale/shopping list to recipes (see below)
- **Yoast SEO** - Inactive (using Rank Math instead)
- Duplicator (migration)
- [Check wp-content/plugins/ for full list]

## CJC Auto Schema Plugin

Custom plugin that auto-generates JSON-LD schema markup for the site.

**GitHub:** https://github.com/CuCryptos/cjc-auto-schema

**Location:** `/wp-content/plugins/cjc-auto-schema/`

### Features

**FAQ Schema (Pillar Pages)**
Automatically adds FAQPage schema to 7 pillar pages (35 Q&A pairs total):
- `guide-hawaiian-poke` - 5 Q&As about poke
- `guide-plate-lunch` - 5 Q&As about plate lunch
- `guide-hawaiian-ingredients` - 5 Q&As about ingredients
- `hawaiian-breakfast-guide` - 5 Q&As about breakfast
- `hawaiian-drinks-guide` - 5 Q&As about drinks
- `hawaiian-desserts-guide` - 5 Q&As about desserts
- `hawaiian-pupus-guide` - 5 Q&As about pupus

**Recipe Schema (Recipe Posts)**
Parses post content to extract and generate Recipe schema for posts in:
- Category ID 26 (Recipes) and child categories (859, 860, 861, 862, 866, 873, 874)

Extracts:
- Ingredients (from headings + lists)
- Instructions (from headings + lists)
- Prep/Cook/Total time
- Yield/Servings
- Category based on WordPress category

### Plugin Structure
```
cjc-auto-schema/
├── cjc-auto-schema.php          # Main plugin file
├── includes/
│   ├── class-recipe-parser.php  # Parse post content for recipe data
│   ├── class-recipe-schema.php  # Generate Recipe JSON-LD
│   ├── class-faq-schema.php     # Generate FAQ JSON-LD (hardcoded data)
│   └── class-schema-output.php  # Output schemas in wp_head
└── admin/
    └── class-admin-settings.php # Admin UI at Tools > CJC Auto Schema
```

### Admin Testing
Go to **Tools → CJC Auto Schema** to:
- View pillar page status
- Test recipe parsing on any post
- Preview generated schema

### Verification
- View page source, search for `CJC Auto Schema`
- Test with Google Rich Results: https://search.google.com/test/rich-results
- Monitor in Search Console: Enhancements → FAQs / Recipes

## CJC Recipe Features Plugin

Lightweight plugin that adds interactive features to recipe posts.

**GitHub:** https://github.com/CuCryptos/cjc-recipe-features

**Location:** `/wp-content/plugins/cjc-recipe-features/`

### Features

**Print Recipe**
- Clean, printer-friendly layout
- Hides site header/footer/sidebar/ads
- Print CSS media query styles

**Save/Bookmark Recipes**
- Heart icon button on recipe posts
- Stores saved recipe IDs in browser localStorage
- Persists across sessions
- Virtual page at `/saved-recipes/` lists all saved recipes

**Ingredient Scaling**
- Servings dropdown (0.5x, 1x, 2x, 3x, 4x)
- Parses ingredient amounts from HTML content
- Handles fractions (1/2, 1/4, 3/4) and unicode fractions (½, ¼, ¾)
- Shows "Scaled to Xx" indicator
- Scale preference persists per recipe

**Shopping List**
- "Add to Shopping List" button
- Stores ingredients in localStorage
- Virtual page at `/shopping-list/`
- Checkboxes to mark items purchased
- Clear checked / Clear all buttons

### Plugin Structure
```
cjc-recipe-features/
├── cjc-recipe-features.php      # Main plugin file (all PHP)
└── assets/
    ├── css/
    │   ├── recipe-features.css  # Main styles
    │   └── print.css            # Print media styles
    └── js/
        └── recipe-features-full.js  # All JavaScript
```

### Configuration
The plugin targets posts in category ID 26 (Recipes) and child categories. To change:
```php
define('CJC_RECIPE_CATEGORY_ID', 26);
```

### Virtual Pages
- `/saved-recipes/` - Grid of saved recipes from localStorage
- `/shopping-list/` - Shopping list with checkboxes, grouped by recipe

**Note:** After activation, go to Settings → Permalinks and click "Save Changes" to register the virtual page URLs.

### Data Storage
All data stored in browser localStorage (no database):
- `cjc_saved_recipes` - Array of saved recipe objects
- `cjc_shopping_list` - Array of shopping list items
- `cjc_recipe_scale_{post_id}` - Scale preference per recipe

### Technical Notes
- PHP 8.3 compatible (avoids DOMDocument parsing issues)
- Ingredient detection via JavaScript (looks for "Ingredients" heading + list)
- No external dependencies

## SEO Configuration

### Sitemap
- **Index:** https://curtisjcooks.com/sitemap_index.xml
- **Posts:** https://curtisjcooks.com/post-sitemap.xml
- **Pages:** https://curtisjcooks.com/page-sitemap.xml
- **Categories:** https://curtisjcooks.com/category-sitemap.xml

### Robots.txt
```
User-agent: *
Disallow: /wp-admin/
Allow: /wp-admin/admin-ajax.php
Sitemap: https://curtisjcooks.com/sitemap_index.xml
```

### External Authority Links (for E-E-A-T)
- FDA food safety guidelines (in Poke Guide)
- Hawaii Tourism Authority (in Poke Guide)
- University of Hawaii resources (in Ingredients Guide)

## Skills Installed
OpenClaw skills available in `.agents/skills/`:
- openai-image-gen, gemini, github, notion, slack, and more

## MCP Server: CurtisJCooks WordPress

An MCP server is configured for direct WordPress admin access to the **LIVE site**.

**IMPORTANT:** The MCP server connects directly to curtisjcooks.com (live), NOT the local site. All MCP operations affect the live site immediately.

**Location:** `.mcp/curtisjcooks-wp/`

**Available Tools:**

### Post Management
- `wp_list_posts` - List posts by status, search, category
- `wp_get_post` - Get single post by ID
- `wp_create_post` - Create new post (draft or publish)
- `wp_update_post` - Update existing post
- `wp_delete_post` - Delete/trash post

### Categories & Tags
- `wp_list_categories` - List all categories
- `wp_create_category` - Create new category
- `wp_list_tags` - List all tags
- `wp_create_tag` - Create new tag

### Media Management
- `wp_list_media` - List media library items
- `wp_upload_media` - Upload image from local file path to live site
- `wp_set_featured_image` - Set featured image for post

### Recipe Tools (Custom)
- `recipe_list` - List recipes with filtering
- `recipe_get` - Get recipe with full metadata
- `recipe_create` - Create recipe with structured ingredients/instructions
- `recipe_update` - Update recipe metadata
- `recipe_parse_ingredients` - Parse plain text ingredients to JSON
- `recipe_estimate_nutrition` - Estimate nutrition from ingredients

### Site Info
- `wp_get_site_info` - Get site information

**Authentication:**
- Uses WordPress Application Password
- Credentials stored in `.mcp/curtisjcooks-wp/index.js`

---

## Workflows

### Upload Local Images to Live Site
Use when local images need to be synced to the live site:
```
1. Locate image in local uploads: /wp-content/uploads/YYYY/MM/filename.png
2. Use wp_upload_media with local file_path
3. Use wp_set_featured_image to assign to post
```
Example:
```
wp_upload_media(file_path="/Users/curtisvaughan/Local Sites/curtisjcookscom1/app/public/wp-content/uploads/2026/01/recipe-image.png")
→ Returns media_id
wp_set_featured_image(post_id=1234, media_id=5678)
```

### Edit Theme Files on Live Site
**Cannot use SSH** - Bluehost rejects SSH key imports. Use cPanel File Manager instead:
```
1. Go to Bluehost cPanel → File Manager
2. Navigate to: public_html/wp-content/themes/suspended-flavor-child/
3. Right-click file → Edit
4. Make changes → Save
5. Clear cache (see below)
```

### Clear Caches After Changes
When CSS/theme changes don't appear:
```
1. Bluehost cPanel → Caching → Clear All Cache
2. WordPress caching plugin (if any) → Purge/Clear
3. Browser: Use Incognito window OR Cmd+Shift+R (Mac) / Ctrl+Shift+R (Windows)
```

### Fix Post Title Encoding Issues
Posts imported incorrectly may have `&#8217;` instead of apostrophes:
```
1. wp_list_posts to find posts with encoded titles
2. wp_update_post with proper characters: Hawaii's not Hawaii&#8217;s
```

### Create New Recipe Post
```
1. wp_create_post with title, content, status="draft"
2. wp_upload_media for featured image
3. wp_set_featured_image to assign image
4. wp_update_post with categories array
5. Review in wp-admin, then publish
```

### Bulk Image Sync (Local → Live)
When multiple images are missing on live:
```
1. List local images: ls /wp-content/uploads/YYYY/MM/
2. For each image:
   - wp_upload_media(file_path=local_path)
   - Note returned media_id
   - wp_set_featured_image(post_id, media_id)
```

### Deploy Custom HTML Widget to Live Site
Use when adding JS/CSS features that can't go through theme files:
```
1. Create self-contained HTML with <style> and <script> tags
2. POST to /wp-json/wp/v2/widgets with:
   - id_base: "custom_html"
   - sidebar: "sidebar-2"
   - instance.raw.content: (your HTML)
3. Widget will land in wp_inactive_widgets — MUST follow with:
   PUT /wp-json/wp/v2/widgets/{widget-id} with sidebar: "sidebar-2"
4. Only sidebar-2 ("Footer Area #1") renders on pages
5. Verify with: curl site.com/any-page/ | grep "your-css-class"
6. Cached pages take up to 2 hours to update
```

### Current Custom Widgets on Live Site
| Widget ID | Sidebar | Purpose |
|-----------|---------|---------|
| custom_html-2 | sidebar-2 | Floating discovery bar + mid-content recipe callout |
| custom_html-3 | sidebar-2 | Progress bar + prev/next nav + category page heroes |

### Debug Template Loading
If theme changes don't appear, verify template is loading:
```php
// Add to top of single.php after <?php
echo "<!-- TEMPLATE: single.php LOADED -->";
```
View page source - if comment missing, another template is overriding.

---

## Troubleshooting

### CSS Changes Not Appearing
1. Clear all caches (Bluehost + browser)
2. Test in Incognito window
3. Check for parent theme CSS overriding (use `!important`)
4. Verify file saved correctly in File Manager

### Images Not Showing on Live Site
- Local and live media libraries are separate
- Use `wp_upload_media` to sync local images to live
- Check media_id references - local IDs don't match live IDs

### WordPress REST API Encodes Characters
- Titles returned from API show `&#8217;` for apostrophes
- This is display encoding - actual title may be correct
- Use wp_update_post with proper characters to fix

### Template Not Loading Custom CSS
- Check if page builder (Divi/Elementor) is overriding template
- Appearance → Theme Builder shows custom templates
- Inline CSS in PHP templates may conflict with external stylesheets

---

## Notes
- Always create posts as drafts for review before publishing
- Use Hawaiian cultural context in all recipe content
- Featured images should be 16:9 aspect ratio
- SEO meta descriptions: 150-160 characters
- MCP changes affect LIVE site immediately - be careful with deletions
- Local site is for development/testing only - not synced automatically

---

## Content Guidelines

### Site Focus
The site is 100% focused on **Hawaiian cuisine**. All content should be:
- Authentic Hawaiian recipes (local style)
- Hawaiian-influenced dishes (plantation era fusion)
- Pacific Island recipes with Hawaiian connections
- Korean, Japanese, Filipino, Portuguese dishes as they relate to Hawaiian food culture

**Do NOT create content for:**
- Generic mainland American recipes
- Non-Hawaiian ethnic cuisines (Mexican, Italian, German, etc.)
- Weight loss / diet content
- Product reviews unrelated to Hawaiian cooking

### New Post Checklist
1. Create with pillar link to relevant guide page
2. Add internal links to 2-3 related posts
3. Generate featured image with Imagen 4.0
4. Assign to correct category (not just "Recipes")
5. Include Hawaiian cultural context
6. Use Hawaiian terms with pronunciations

### Image Generation Prompts
Use `buildFoodPhotoPrompt()` in MCP server or format as:
```
Professional food photography of [dish name], Hawaiian cuisine.
Shot from [angle] with [lighting].
[background description].
Mood: [mood description].
High resolution, sharp focus, shallow depth of field.
```

---

## Recent Changes Log

### February 8, 2026 - Batch Content + Bounce Rate Overhaul
- **Added 20 new recipe posts** across all categories via parallel agent deployment:
  - Batch 1 (Classic Recipes): Saimin, Portuguese Bean Soup, Squid Luau, Char Siu Pork, Teriyaki Chicken, Hawaiian Chili, Chow Fun, Fried Saimin, Katsu Curry, Misoyaki Butterfish
  - Batch 2 (Mix of Everything): Taro Waffles, Mochi Ice Cream, Mac Nut Cookies, Korean Fried Chicken Wings, Ahi Katsu, Li Hing Mui Margarita, Furikake Salmon, Chicken Adobo, Hawaiian Chili Peppers Guide, History of Plate Lunch
  - All posts include AI-generated featured images (Imagen 4.0)
- **Bounce Rate Investigation** - Diagnosed 100% bounce rate:
  - Navigation was NOT the issue (live site already has full mega-menu header)
  - Triple caching confirmed (Endurance + Nginx + Cloudflare)
  - IAWP free version only tracks page views, not engagement/time-on-page
  - 100% bounce rate is real user behavior (recipe search → read → leave)
- **Inline Cross-Links** - Added 110 natural inline recipe links across 38 posts
  - Links woven into narrative text, serving suggestions, tips sections
  - Fixed 4 broken URLs in existing posts
  - Each post now has 2-6 contextual links to related recipes
- **Deployed 3 Custom HTML Widgets** (sidebar-2, via REST API) for bounce rate reduction:
  1. **Floating Discovery Bar** (`custom_html-2`) - Category pill links + dynamic recipe suggestions
     - Slides up at 12% scroll with Explore category pills
     - Expands at 45% scroll showing 3 recipe cards from same category (REST API)
     - Dismissible per session (sessionStorage)
     - Only on single-post pages
  2. **Mid-Content Callout** (`custom_html-2`) - "Craving More Island Flavors?" box
     - Injected before Tips/Serving Suggestions section via JS
     - 3 recipe cards with thumbnails from same category
     - Warm gradient design matching site tokens
  3. **Progress Bar + Prev/Next Nav + Category Heroes** (`custom_html-3`)
     - Reading progress bar (orange gradient, top of page)
     - Previous/Next recipe navigation appended to post content
     - Category archive hero sections with descriptions + cross-category pills

### Live Site Widget Deployment Notes
- Widgets deployed via `POST /wp-json/wp/v2/widgets` with `custom_html` type
- **IMPORTANT:** Widgets create in `wp_inactive_widgets` by default — must follow with `PUT` to move to `sidebar-2`
- sidebar-2 ("Footer Area #1") is the only footer sidebar that renders on pages
- sidebar-3 through sidebar-7 do NOT render (Divi doesn't output them)
- Changes take up to 2 hours to appear on cached pages (Cloudflare + Endurance)

### Live vs. Local Code Divergence
- **The live site has code NOT present in local files:**
  - Full mega-menu navigation header (local functions.php hides Divi header but live site has custom replacement)
  - Series system (Aloha Friday, Hawaiian Cooking 101, Plate Lunch of the Week, Talk Story)
  - Custom header component (`cjc-header` class)
- **Local file changes to functions.php and style.css** (navigation code) were made during investigation but are NOT deployed and NOT needed
- Always verify against the live site before making assumptions based on local files

### Earlier February 2026
- **Created CJC Recipe Features plugin** - Interactive features for recipe posts
  - Print recipe with clean layout
  - Save/bookmark recipes (localStorage)
  - Ingredient scaling (0.5x to 4x)
  - Shopping list with combine duplicates
  - Virtual pages: /saved-recipes/ and /shopping-list/
  - PHP 8.3 compatible
  - GitHub: https://github.com/CuCryptos/cjc-recipe-features
- **Created CJC Auto Schema plugin** - Auto-generates Recipe and FAQ schema
  - FAQ schema for 7 pillar pages (35 Q&A pairs)
  - Recipe schema for all posts in Recipes category
  - GitHub: https://github.com/CuCryptos/cjc-auto-schema
- Created 4 new pillar pages (Breakfast, Drinks, Desserts, Pupus)
- Added 14 new cluster posts across all categories
- Implemented internal linking between pillars and clusters
- Added external authority links for E-E-A-T
- Deleted 39 off-brand posts (German, Mexican, weight loss content)
- Generated 50+ AI featured images using Imagen 4.0
- Fixed Gemini API configuration (model: imagen-4.0-generate-001)
- Google Search Console: 221 discovered pages (194 posts, 12 pages, 15 categories)
