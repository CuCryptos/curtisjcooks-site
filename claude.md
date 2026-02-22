# CurtisJCooks.com - Project Context for Claude

## Project Overview
CurtisJCooks.com is a Hawaiian food and recipe blog built on WordPress. The site features authentic Hawaiian recipes, cultural food stories, and local island cuisine.

## Current Site Statistics (Updated Feb 22, 2026)
- **Published Posts:** ~142 (including 4 new brain-sourced posts added Feb 21)
- **Published Pages:** 16 (including 7 pillar/guide pages)
- **SEO Plugin:** Rank Math SEO (active)
- **Sitemap:** https://curtisjcooks.com/sitemap_index.xml
- **Analytics:** Google Analytics GA4 (Measurement ID: `G-7C8X9QJD7V`) + Independent Analytics (IAWP) free version
  - GA4 added to theme `functions.php` via `wp_head` action (skips logged-in users)
  - Originally deployed Feb 9 via widget; moved to theme code Feb 22 during Kadence deployment
  - GA4 provides: traffic sources, engagement time, user flow, geographic data
  - IAWP provides: basic page view counts (stored in local database)
- **Caching:** Triple-layer: Bluehost Endurance (level 2) + Nginx + Cloudflare CDN (2-hour TTL)

## Environment

### Local Development
- **Platform:** Local by Flywheel
- **Local URL:** https://curtisjcookscom1.local
- **WordPress Root:** `/Users/curtisvaughan/Local Sites/curtisjcookscom1/app/public/`
- **Active Theme:** cjc-kadence-child (child of Kadence Free) — deployed to live Feb 22, 2026
- **Previous Theme:** suspended-flavor-child (Divi-based, installed as fallback)
- **Database Prefix:** `hMq_`
- **PHP:** Via Local's lightning-services
- **API Keys (.env):** `/Users/curtisvaughan/Local Sites/curtisjcookscom1/.env`
  - `X_BEARER_TOKEN` — X/Twitter API v2 (app-only)
  - `ANTHROPIC_API_KEY` — Claude API (Haiku for brain analysis)
  - `GOOGLE_AI_API_KEY` — Gemini/Imagen for image generation
  - `WP_SITE_URL`, `WP_USERNAME`, `WP_APP_PASSWORD` — WordPress REST API auth

### Live Site
- **URL:** https://curtisjcooks.com
- **Admin:** https://curtisjcooks.com/wp-admin
- **Username:** curtv74
- **Hosting:** Bluehost
- **cPanel:** https://box4190.bluehost.com:2083
- **cPanel User:** tptsgqmy
- **File Manager Path:** public_html/wp-content/themes/cjc-kadence-child/
- **SSH:** Not available via curtisjcooks.com. Ports 22, 2222, and 21 (FTP) are open on `box4190.bluehost.com` directly, but SSH keys in `~/.ssh/curtisjcooks_*` are rejected. Password auth would be needed.
- **Admin SSO:** Can get wp-admin session cookies via `GET /wp-json/newfold-sso/v1/sso` (with REST API auth). Returns a one-time login URL that provides full admin cookies.

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
| Recipes (parent) | 26 | 124 |
| Island Comfort | 860 | 34 |
| Quick & Easy | 866 | 20 |
| Island Drinks | 862 | 20 |
| Poke & Seafood | 859 | 19 |
| Top Articles | 857 | 16 |
| Kitchen Essentials | 101 | 16 |
| Hawaiian Breakfast | 873 | 13 |
| Tropical Treats | 861 | 12 |
| Pupus & Snacks | 874 | 12 |
| Kitchen Skills | 107 | 11 |

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
- `post` - Standard blog posts/recipes (recipe meta stored as post meta)

### Recipe System
**Current Status (Feb 22, 2026):** Recipe classes are **disabled** on live. Only meta field registration is active in `functions.php`. The full class system caused fatal errors during theme activation and was removed as an emergency fix.

**Classes located in** `/wp-content/themes/cjc-kadence-child/inc/recipe/`:
- `class-cjc-recipe-post-type.php` - Custom post type registration
- `class-cjc-recipe-meta.php` - Recipe metadata fields
- `class-cjc-recipe-rest-api.php` - REST API endpoints
- `class-cjc-recipe-block.php` - Gutenberg block
- `class-cjc-recipe-schema.php` - Structured data/schema markup
- `class-cjc-recipe-migration.php` - Migration from Tasty Recipes

**What's active on live:** Only `register_post_meta()` calls in `functions.php` (no class loading). This enables REST API read/write of recipe fields and allows `single.php` to render recipe cards from post meta.

**Recipe meta migration:** 106/126 posts have been migrated with structured recipe data (ingredients, instructions, times, yield, notes) via Python script that parses HTML content. Migration script at `/tmp/migrate_recipe_meta.py`.

**Plugin version:** A standalone plugin (`cjc-recipe-system`) exists at `/wp-content/plugins/cjc-recipe-system/` (GitHub: `CuCryptos/cjc-recipe-system`). NOT deployed on live.

**MCP recipe tools** (`recipe_list`, `recipe_get`, etc.) return 404 on live because the `cjc_recipe` custom post type is not registered (classes disabled). Recipe data lives in standard `post` meta fields instead.

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

### Theme Files (cjc-kadence-child — active on local + live)
- `/wp-content/themes/cjc-kadence-child/functions.php` - Theme functionality (recipe meta, GA4, fonts, perf)
- `/wp-content/themes/cjc-kadence-child/single.php` - Immersive recipe post template
- `/wp-content/themes/cjc-kadence-child/front-page.php` - Custom homepage template
- `/wp-content/themes/cjc-kadence-child/style.css` - Global overrides
- `/wp-content/themes/cjc-kadence-child/assets/css/tokens.css` - Design tokens
- `/wp-content/themes/cjc-kadence-child/assets/css/components.css` - Component styles
- `/wp-content/themes/cjc-kadence-child/assets/css/patterns.css` - Kapa cloth patterns

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
- **WP Pusher** - Active, deploys theme from GitHub (`CuCryptos/cjc-kadence-child`, branch: `main`)
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
**Primary method: GitHub + WP Pusher** (automated deployment)
```
1. Edit files locally in cjc-kadence-child/
2. Commit and push to GitHub: CuCryptos/cjc-kadence-child (branch: main)
3. In wp-admin: WP Pusher → Themes → Update Theme
4. Clear cache (see below)
```

**Fallback: cPanel File Manager** (for emergency fixes without git)
```
1. Go to Bluehost cPanel → File Manager
2. Navigate to: public_html/wp-content/themes/cjc-kadence-child/
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

### Custom Widgets (REMOVED)
The old Divi-based custom widgets (custom_html-2 through custom_html-7) were part of the `suspended-flavor-child` theme and are no longer active. Their functionality has been replaced by:
- **Navigation:** Kadence header builder + Hawaiian Nav menu (ID 985 on live)
- **GA4 tracking:** Now in theme `functions.php` via `wp_head` action
- **Recipe features:** Built into `single.php` template (progress bar, recipe card, related recipes)
- **Jump to Recipe:** Built into `single.php` sticky nav

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

### CDATA Wrapper in MCP-Created Posts
- Posts created via `wp_create_post` may have content wrapped in `<p><![CDATA[...]]></p>`
- This renders as broken HTML on the frontend (literal CDATA text visible)
- **Fix:** Use `wp_update_post` to replace content with clean HTML (strip the CDATA wrapper)
- **Prevention:** When creating posts, ensure content is plain HTML without CDATA tags

### Plugin Causes Fatal 500 Error on Live Site
If a plugin defines classes already loaded by the theme (e.g., `cjc-recipe-system`):
1. The live site will crash with "Cannot declare class" fatal error
2. WordPress recovery mode shows error page but doesn't auto-fix
3. **Fix:** Use cPanel File Manager to rename/delete the plugin folder in `public_html/wp-content/plugins/`
4. **Prevention:** Always update the live `functions.php` with a guard (e.g., `if ( ! defined( 'PLUGIN_CONSTANT' ) )`) BEFORE activating the plugin
5. The REST API, SSO, and wp-admin will all be inaccessible during the outage — cPanel File Manager is the only fix without SSH

### Template Not Loading Custom CSS
- Check if page builder (Divi/Elementor) is overriding template
- Appearance → Theme Builder shows custom templates
- Inline CSS in PHP templates may conflict with external stylesheets

### WordPress Customizer Preview Crashes (Duplicate Class Errors)
The Customizer loads BOTH the active and preview theme's `functions.php` simultaneously. If both define the same PHP classes, you get "Cannot declare class" fatal errors.
- **`get_option('stylesheet')` does NOT work as a guard** — the Customizer filters this value to return the preview theme name
- **Fix:** Use `$wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'stylesheet'")` to bypass Customizer filters
- Best practice: don't load heavy class files in `functions.php` at all during theme preview

### WordPress Theme Pause (5.2+ Fatal Error Recovery)
If a theme crashes on activation, WordPress pauses it via the `_paused_themes` database option. The theme stays paused even after code is fixed.
- **Symptoms:** "There has been a critical error" message; recovery email sent to admin
- **Fix:** Use recovery email link to access wp-admin, then update theme code (via WP Pusher) and reactivate
- The paused state persists across code changes — must explicitly reactivate

---

## CJC Hawaiian Food Brain (Content Intelligence)

An X/Twitter content intelligence pipeline that monitors trending Hawaiian food topics and generates prioritized content briefs. The brain provides data and ideas — the MCP handles all writing and publishing.

**Location:** `/tmp/cjc_brain/`

**Environment:** API keys stored in `/Users/curtisvaughan/Local Sites/curtisjcookscom1/.env`

### Architecture
```
cjc_brain/
├── .env → symlinks to project .env
├── config.py          # Constants, X search queries, category mappings
├── db.py              # SQLite schema (5 tables) + helpers
├── sources.py         # 30 X accounts to monitor (3 tiers)
├── fetcher.py         # X API v2 Recent Search
├── analyzer.py        # Claude Haiku — extract signals from tweets
├── generator.py       # Aggregate signals → content briefs (NO writing)
├── wp_client.py       # WordPress REST API (fallback for sync)
├── run.py             # CLI entry point
├── live_site_posts.json  # Live site post export (source of truth for dedup)
└── cjc_brain.db       # SQLite database (created on first run)
```

### Pipeline Commands
```bash
python3 -m cjc_brain fetch      # Pull tweets from X API
python3 -m cjc_brain analyze    # Analyze signals with Claude Haiku
python3 -m cjc_brain generate   # Aggregate into content briefs
python3 -m cjc_brain report     # Print content calendar (--detail for full briefs)
python3 -m cjc_brain sync       # Sync existing WP posts for duplicate detection
python3 -m cjc_brain all        # Run full pipeline: sync → fetch → analyze → generate → report
```

### Content Workflow (Brain → MCP → Publish)
1. Run `python3 -m cjc_brain all` to get prioritized content briefs
2. Review briefs in the report output
3. Use MCP `wp_create_post` to write posts based on briefs (in site voice)
4. Use MCP `generate_recipe_image` for featured images
5. Review drafts, fix any issues (CDATA wrappers, broken links, missing tags)
6. Publish via `wp_update_post` with status="publish"
7. Update `live_site_posts.json` to keep duplicate detection current

### Duplicate Detection (3 strategies)
1. **Substring match** — "loco moco" found in "Aloha Friday: The Perfect Loco Moco"
2. **Keyword overlap** — topic keywords are subset of existing title keywords (filtered by stop words)
3. **Fuzzy match** — SequenceMatcher ratio >= 0.7

### Updating Live Site Posts for Dedup
The brain uses `live_site_posts.json` as the source of truth (not local WordPress).
To refresh it: use MCP `wp_list_posts` (per_page=100, status=publish) and save the output
to `/tmp/cjc_brain/live_site_posts.json`.

### Key Config
- `SEARCH_QUERIES` — 6 X API queries covering Hawaiian dishes, ingredients, and food culture
- `MIN_SIGNAL_SCORE` — 0.6 threshold for content suggestions
- `HAIKU_MODEL` — `claude-haiku-4-5-20251001` (signal analysis)
- Priority formula: `relevance*0.4 + trending*0.3 + signal_density*0.2 + credibility*0.1`

### Important Notes
- The brain does NOT write posts or generate HTML — it only produces content briefs
- The MCP tools handle all writing in the site's voice and style
- `live_site_posts.json` must be kept up-to-date after publishing new posts
- X API rate limit: 450 requests/15 min (app-only auth), fetcher handles 429 retries

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
1. Create as draft with pillar link to relevant guide page
2. Add internal links to 2-3 related posts (verify slugs exist on live site)
3. Generate featured image with Imagen 4.0 / `generate_recipe_image`
4. Assign to correct category (not just "Recipes")
5. Add relevant tags from existing tag taxonomy
6. Set SEO-friendly slug
7. Include Hawaiian cultural context
8. Use Hawaiian terms with translations (not awkward pronunciations)
9. **Review before publishing:** Check for CDATA wrappers, broken links, missing tags/slugs
10. After publishing, update `live_site_posts.json` for brain dedup

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

### February 22, 2026 - Site Redesign: "Modern Hawaiian Luxury" (DEPLOYED TO LIVE)

**Design Direction:** Modern Hawaiian Luxury with Cultural Soul — immersive recipe pages with kapa cloth patterns, Hawaiian color palette, and premium food blog feel.

**Theme:** `cjc-kadence-child` (child of Kadence Free) — active on both local and live.

**GitHub:** https://github.com/CuCryptos/cjc-kadence-child (deployed via WP Pusher)

**Theme Location:** `/wp-content/themes/cjc-kadence-child/`

**Theme File Structure:**
```
cjc-kadence-child/
├── style.css                    # Kadence header overrides, global overrides
├── functions.php                # Recipe system, fonts, CSS/JS enqueue, perf optimizations
├── single.php                   # Immersive recipe page template
├── assets/css/
│   ├── tokens.css               # Design tokens (colors, fonts, spacing, shadows)
│   ├── patterns.css             # Kapa cloth SVG patterns (triangle, wave, zigzag, diamond)
│   ├── components.css           # Hero, recipe card, sidebar, related recipes, footer
│   └── print.css                # Print stylesheet
├── assets/js/
│   ├── recipe-interactive.js    # Ingredient checkboxes, servings scaler, nutrition toggle
│   └── scroll-observer.js       # Sticky nav, reading progress, scroll animations
├── assets/svg/                  # Standalone SVG pattern files (4 files)
├── build/                       # React Gutenberg recipe block (compiled)
│   ├── recipe.js / recipe.css
│   └── recipe-editor.js / recipe-editor.css
└── inc/
    ├── recipe/                  # 6 PHP classes (post type, meta, REST, schema, block, migration)
    └── migration.php            # WP-CLI Divi shortcode stripping
```

**Design Tokens (Hawaiian Color Palette):**
- `--cjc-ocean-deep: #0e7490` (teal — primary accent)
- `--cjc-sunset-gold: #d97706` (amber — secondary accent)
- `--cjc-volcanic-earth: #9a3412` (burnt orange — headings)
- `--cjc-lava-black: #1c1917` (near-black — text)
- `--cjc-coconut-cream: #fffbeb` (warm cream — backgrounds)
- `--cjc-warm-sand: #fef3c7` (light gold — section backgrounds)
- `--cjc-reef-gray: #57534e` (warm gray — body text)
- `--cjc-koa-wood: #78350f` (dark brown — recipe card header)
- Fonts: Lora (heading), Source Sans 3 (body), Playfair Display (accent)

**Single Post Template Features (single.php):**
1. Reading progress bar
2. Full-bleed hero with featured image, teal tint overlay, kapa diamond texture, wave edge
3. Category pill, title with sunset-gold wave accent, meta with golden dot separators
4. Kapa triangle divider
5. Sticky recipe nav (Story / Ingredients / Steps / Notes)
6. Story content area with floating sidebar (5 related posts from same category, desktop only)
7. Jump to Recipe button
8. Kapa wave divider
9. Recipe card: koa wood header, prep/cook/total times, servings scaler, checkable ingredients, numbered steps, chef's notes, nutrition facts (collapsible)
10. Kapa zigzag divider
11. "More Island Recipes" section (3 random posts from same category, warm-sand background)
12. Hawaiian footer (lava rock texture, wave border)

**Header (Kadence Customized via CSS):**
- Centered layout: logo on top, nav below
- Menu: Recipes (dropdown with 7 category sub-items) → Guides (dropdown) → About
- Menu ID 1013 ("Hawaiian Nav") on local; Menu ID 985 on live — assigned to `primary`, `mobile`, `cjc-primary` locations
- On single posts: transparent with white text overlaying hero, dark gradient for readability
- Logo image inverted to white on single posts via CSS filter

**Known Issues / Gotchas:**
- Google Fonts must be enqueued as 3 separate `wp_enqueue_style()` calls (WordPress `esc_url()` breaks multi-family `&` in URLs)
- WP QUADS ad plugin injects a 300x250 ad at top of post content — hidden via `.recipe-story > .quads-location:first-child { display: none }`
- Kadence header has deeply nested elements that all need `background: transparent !important` on single posts
- `overflow: hidden` on hero clips the `::before` wave edge — positioned at `bottom: -1px`
- Recipe card only renders when post has `_cjc_recipe_ingredients` or `_cjc_recipe_instructions` meta

**What's Done:**
- [x] Design tokens (tokens.css)
- [x] Kapa patterns (patterns.css) — dual-color SVG dividers at 50-60% opacity
- [x] Immersive single post template (single.php)
- [x] Component styles (components.css) — hero, recipe card, related recipes, footer
- [x] Recipe system classes migrated (6 PHP classes + Gutenberg block)
- [x] Google Fonts fix (3 separate enqueues)
- [x] Theme activated on local site
- [x] Performance optimizations (emoji removal, script defer, jQuery migrate removal)
- [x] Hawaiian nav menu with dropdowns (Recipes, Guides, About)
- [x] Centered header layout (logo top, nav below)
- [x] Transparent header on single posts with white text
- [x] Floating sidebar with related posts (desktop only, sticky)
- [x] Loco Moco test post populated with full recipe metadata
- [x] Homepage template (front-page.php) — hero, category picker, featured recipes, browse by category, about, latest posts
- [x] Category/archive page template with hero and pill nav
- [x] Mobile responsive testing and fixes
- [x] Deployed to live site via GitHub + WP Pusher (Feb 22, 2026)
- [x] Hawaiian Nav menu created on live (ID 985) with full dropdown structure
- [x] Hero image uploaded to live media library (ID 6472)
- [x] Recipe meta migrated to 106/126 live posts (Python parser)
- [x] GA4 tracking moved from widget to theme functions.php
- [x] Kadence header builder configured on live
- [x] SEO noindex for non-Hawaiian category posts

**What's Next (TODO):**
- [ ] Re-enable full recipe system classes (disabled due to activation crash — needs debugging)
- [ ] Migrate remaining ~20 posts missing recipe meta data
- [ ] Clear Bluehost caches (Endurance + Cloudflare)
- [ ] Mobile testing on live site
- [ ] Archive page testing on live site
- [ ] Print stylesheet verification
- [ ] Migrate live site Divi shortcodes (only 1 post: #4089 Mai Tai Moments)
- [ ] SEO verification (schema output, meta tags, Open Graph)
- [ ] Performance audit (Core Web Vitals, Lighthouse)
- [ ] Verify recipe features plugin compatibility with new theme

**WP-CLI Access (Local):**
```bash
WP="/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/posix/wp"
PHP="/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.27+1/bin/darwin-arm64/bin/php"
SOCK="/Users/curtisvaughan/Library/Application Support/Local/run/JdKBCEfQI/mysql/mysqld.sock"
P="/Users/curtisvaughan/Local Sites/curtisjcookscom1/app/public"
$PHP -d "mysqli.default_socket=$SOCK" $WP <command> --path="$P"
```

**Design Documents:**
- Design doc: `docs/plans/2026-02-21-cjc-redesign-design.md`
- Implementation plan: `docs/plans/2026-02-21-cjc-redesign-plan.md`

---

### February 22, 2026 - Theme Deployment to Live Site
- **Deployed cjc-kadence-child** to live site via GitHub + WP Pusher
  - GitHub repo: `CuCryptos/cjc-kadence-child` (public)
  - WP Pusher auto-deploys from `main` branch
  - Kadence Free parent theme installed on live
- **Resolved critical errors during deployment:**
  - Customizer preview crash: WordPress loads both themes' functions.php simultaneously — fixed with direct `$wpdb->get_var()` query to bypass Customizer filters
  - Theme activation crash: Recipe system classes caused fatal error on live — disabled classes, kept only meta registration
  - WordPress theme pause: Used recovery email to regain wp-admin access
- **Created Hawaiian Nav menu on live** (ID 985) via REST API
  - Recipes dropdown (7 sub-items), Guides dropdown (2 sub-items), About
  - Assigned to primary, mobile, cjc-primary locations
- **Uploaded homepage hero** to live media library (ID 6472)
  - Updated front-page.php and functions.php with fallback path logic
- **Migrated recipe meta data** to 106/126 live posts
  - Python script (`/tmp/migrate_recipe_meta.py`) parses HTML content with BeautifulSoup
  - Extracts ingredients (from `<li>` items only), instructions, times, yield, notes
  - Writes structured JSON to `_cjc_recipe_*` meta fields via REST API
- **Moved GA4 tracking** from widget (custom_html-7) to theme functions.php
  - `wp_head` action, skips logged-in users
  - Old Divi widgets no longer render under Kadence theme
- **Removed old Divi widgets** — all functionality replaced by theme templates

### February 21, 2026 - CJC Brain + New Posts
- **Built CJC Hawaiian Food Brain** — X/Twitter content intelligence pipeline
  - Location: `/tmp/cjc_brain/` (Python + SQLite)
  - Monitors X for trending Hawaiian food topics via 6 search queries
  - Claude Haiku analyzes tweets for relevance, topic extraction, content angles
  - Aggregates signals into prioritized content briefs (no writing — MCP handles that)
  - 3-strategy duplicate detection: substring, keyword overlap, fuzzy matching
  - Uses `live_site_posts.json` (138 posts) as source of truth for dedup
  - Full pipeline: sync → fetch → analyze → generate → report
- **Published 4 new posts** from brain-sourced content briefs:
  - [Bacon Avocado Spam Musubi](https://curtisjcooks.com/bacon-avocado-spam-musubi/) (#6440) — Pupus & Snacks
  - [Ahi Tuna Poke Stacks](https://curtisjcooks.com/ahi-tuna-poke-stacks/) (#6441) — Poke & Seafood
  - [Ahi Tuna Burger on Sweet Hawaiian Bun](https://curtisjcooks.com/ahi-tuna-burger-sweet-hawaiian-bun/) (#6442) — Quick & Easy / Poke & Seafood
  - [Gourmet Poke Bowl with Eel, Spicy Salmon, Mango](https://curtisjcooks.com/gourmet-poke-bowl-eel-spicy-salmon-mango/) (#6443) — Poke & Seafood
  - All posts: AI-generated featured images (Imagen), tagged, internal links verified
  - Fixed CDATA wrapper bug in MCP-created posts before publishing
- **Workflow established:** Brain finds topics → MCP writes in site voice → Review → Publish

### February 19-20, 2026 - Major Content Expansion + Internal Linking
- **Added ~22 new posts** across all categories (recipes, talk story, guides, gear reviews)
- **Bridge linking audit** — Added internal links across all posts for SEO
- **Tagged 41 untagged posts** with appropriate tags from the site taxonomy
- **Verified Tasty Pins** meta integration for Pinterest-optimized images

### February 9, 2026 - Analytics + Recipe Plugin (Local Only)
- **Added Google Analytics GA4** (Measurement ID: `G-7C8X9QJD7V`)
  - Originally deployed via custom_html-7 widget in sidebar-2
  - Moved to theme `functions.php` on Feb 22 during Kadence deployment (widget no longer renders)
  - Tracks traffic sources, engagement, user flow, geographic data
  - Runs alongside IAWP (both active)
- **Built CJC Recipe System plugin** (local only, NOT on live site)
  - Packaged recipe system from theme into standalone plugin at `/wp-content/plugins/cjc-recipe-system/`
  - Pushed to GitHub: https://github.com/CuCryptos/cjc-recipe-system
  - Tested and working on local site
  - **Deployment to live site failed** — activated before updating live `functions.php` with class guard, causing fatal 500 error (duplicate class declarations)
  - Plugin was removed from live site via cPanel File Manager to restore the site
  - To deploy in future: update live `functions.php` FIRST, then install/activate plugin
- **Discovered SSO endpoint** for getting wp-admin session cookies: `GET /wp-json/newfold-sso/v1/sso`
  - Returns one-time login URL that provides full admin cookies
  - Useful for accessing admin-only pages (plugin upload, IAWP analytics, etc.)

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

### Live vs. Local Differences (Post-Deployment)
Both local and live now run `cjc-kadence-child`. Remaining differences:
- **Menu IDs:** Local uses menu ID 1013 ("Hawaiian Nav"); live uses menu ID 985 (created via REST API with same structure)
- **Hero image path:** Local uses `/uploads/site-images/homepage-hero.png`; live falls back to `/uploads/2026/02/homepage-hero.png` (media library). Code in functions.php and front-page.php handles both paths.
- **Recipe classes:** Full recipe system (CPT, blocks, schema) loads on local; disabled on live (only meta registration active)
- **Old theme:** `suspended-flavor-child` still installed on live as fallback; can reactivate if needed

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
