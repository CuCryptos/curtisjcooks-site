# CurtisJCooks.com - Project Context for Claude

## Project Overview
CurtisJCooks.com is a Hawaiian food and recipe blog built on WordPress. The site features authentic Hawaiian recipes, cultural food stories, and local island cuisine.

## Current Site Statistics (Updated Feb 23, 2026)
- **Published Posts:** ~165 (17 new posts/pages added Feb 23 from content strategy; live_site_posts.json refreshed to 165)
- **Published Pages:** 17 (including 8 pillar/guide pages — BBQ grilling guide added Feb 23)
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

### Post Categories (with approximate counts as of Feb 23)
| Category | ID | Posts |
|----------|-----|-------|
| Recipes (parent) | 26 | ~140 |
| Island Comfort | 860 | ~40 |
| Quick & Easy | 866 | ~21 |
| Island Drinks | 862 | 20 |
| Poke & Seafood | 859 | ~22 |
| Top Articles / Hawaiian Culture | 857 | ~23 |
| Kitchen Essentials | 101 | ~19 |
| Hawaiian Breakfast | 873 | 13 |
| Tropical Treats | 861 | ~14 |
| Pupus & Snacks | 874 | ~15 |
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
| Hawaiian BBQ and Grilling Guide | hawaiian-bbq-grilling-guide | Cross-category (PAGE, pillar template) |

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
- `/wp-content/themes/cjc-kadence-child/functions.php` - Theme functionality (recipe meta, GA4, fonts, perf, 404→homepage redirect for deleted `?p=` URLs)
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
- **WP Pusher** - Active, deploys theme from GitHub (`CuCryptos/cjc-kadence-child`, branch: `main`). No auto-deploy webhook configured — must trigger manually via admin POST (see "Edit Theme Files on Live Site" workflow).
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

### Page Management
- `wp_list_pages` - List pages by status, search
- `wp_get_page` - Get single page by ID
- `wp_create_page` - Create new page (supports `template` param, e.g., `page-pillar.php`)
- `wp_update_page` - Update existing page
- `wp_delete_page` - Delete/trash page

### AI Image Generation
- `generate_recipe_image` - Generate food photography via Google Imagen AI, uploads to WP media library, optionally sets as featured image. Params: `dish_name`, `filename`, `post_id` (optional), `mood`, `background`, `angle`, `lighting`
- `generate_image_prompt` - Generate a detailed prompt for AI image generation
- `generate_recipe_post` - Generate full blog post content (requires ANTHROPIC_API_KEY)
- `generate_recipe_description` - Generate cultural story for recipe card
- `generate_seo_content` - Generate SEO metadata

### Site Info
- `wp_get_site_info` - Get site information

**Authentication:**
- Uses WordPress Application Password
- Credentials in `.mcp/curtisjcooks-wp/lib/config.js` (hardcoded fallbacks + env var overrides)

### Reusable MCP Package

A generic, credentials-stripped version of the MCP server is available at `/tmp/wp-mcp-server/`.

**What's different from the CJC version:**
- All config via env vars only (no hardcoded credentials)
- Generic food blog prompts (not Hawaiian-specific)
- Configurable server name, recipe post type, meta prefix
- Custom prompts via `CUSTOM_PROMPTS_PATH` env var
- Hawaiian prompts preserved as `prompts/hawaiian-prompts.example.js`
- Includes README with full setup guide

**To use on another project:**
1. Copy `/tmp/wp-mcp-server/` to the new project
2. Run `npm install`
3. Add to `.mcp.json` with WordPress credentials in the `env` block
4. Restart Claude Code

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
**Primary method: GitHub + WP Pusher** (automated deployment via CLI)
```
1. Edit files locally in cjc-kadence-child/
2. Bump CJC_CHILD_VERSION in functions.php (cache busting for CSS/JS)
3. Commit and push to GitHub: CuCryptos/cjc-kadence-child (branch: main)
4. Deploy + clear caches (see commands below)
```

**Programmatic WP Pusher Deploy (no manual wp-admin needed):**
```bash
# Step 1: Get admin cookies via SSO
curl -s "https://curtisjcooks.com/wp-json/newfold-sso/v1/sso" \
  -u 'curtv74:HD01 sRu6 OjE7 KbQS snOi UVn9' \
  -c /tmp/wp_cookies.txt
# Returns a JSON string with SSO URL — follow it:
curl -s -L -b /tmp/wp_cookies.txt -c /tmp/wp_cookies.txt "$SSO_URL" -o /dev/null

# Step 2: Get WP Pusher nonce from themes page
curl -s -b /tmp/wp_cookies.txt \
  'https://curtisjcooks.com/wp-admin/admin.php?page=wppusher-themes' \
  | grep -oE '"_wpnonce"[^/]* value="[^"]*"'
# Extract the nonce value (e.g., dd50191336)

# Step 3: Trigger theme update
curl -s -X POST -b /tmp/wp_cookies.txt \
  'https://curtisjcooks.com/wp-admin/admin.php?page=wppusher-themes' \
  -d '_wpnonce=NONCE_HERE' \
  -d '_wp_http_referer=/wp-admin/admin.php?page=wppusher-themes' \
  -d 'wppusher[action]=update-theme' \
  -d 'wppusher[repository]=CuCryptos/cjc-kadence-child' \
  -d 'wppusher[stylesheet]=cjc-kadence-child'
# Look for "Theme was successfully updated." in response

# Step 4: Clear all caches (run all three)
curl -s -X POST 'https://curtisjcooks.com/wp-json/wp-super-cache/v1/cache' \
  -u 'curtv74:HD01 sRu6 OjE7 KbQS snOi UVn9' \
  -H 'Content-Type: application/json' -d '{"wp_delete_cache":true}'
curl -s -b /tmp/wp_cookies.txt \
  'https://curtisjcooks.com/wp-admin/admin.php?page=wppusher-themes&nfd_purge_all=1' \
  -o /dev/null
```

**WP Pusher webhook (alternative, but may be blocked by ModSecurity):**
```bash
curl -s -X POST \
  'https://curtisjcooks.com/?wppusher-hook&token=d07c87ffa7d57417d9a49e469b4ce7e7f5956bf19ef65f163ccc177f628b26f5&package=Y3VydGlzamNvb2tzLWNoaWxkLXRoZW1l' \
  -H 'Content-Type: application/json' \
  -H 'X-GitHub-Event: push' \
  -d '{"ref":"refs/heads/main","repository":{"full_name":"CuCryptos/cjc-kadence-child"}}'
```

**Important deployment notes:**
- Always bump `CJC_CHILD_VERSION` when changing CSS/JS — Cloudflare CDN caches files by URL including `?ver=` parameter for up to 24 hours
- WordPress theme editor **cannot edit PHP files** on this host (Bluehost loopback check fails with Cloudflare). Use WP Pusher instead.
- Theme editor CAN edit CSS files as a fallback, but version won't change so CDN may serve stale content.

**Fallback: cPanel File Manager** (for emergency fixes without git)
```
1. Go to Bluehost cPanel → File Manager
2. Navigate to: public_html/wp-content/themes/cjc-kadence-child/
3. Right-click file → Edit
4. Make changes → Save
5. Clear cache (see below)
```

### Clear Caches After Changes
Triple-layer caching means all three must be cleared:
```bash
# 1. WP Super Cache (REST API — no admin cookies needed)
curl -s -X POST 'https://curtisjcooks.com/wp-json/wp-super-cache/v1/cache' \
  -u 'curtv74:HD01 sRu6 OjE7 KbQS snOi UVn9' \
  -H 'Content-Type: application/json' -d '{"wp_delete_cache":true}'

# 2. Bluehost Endurance + Nginx (requires admin cookies from SSO)
curl -s -b /tmp/wp_cookies.txt \
  'https://curtisjcooks.com/wp-admin/admin.php?page=wppusher-themes&nfd_purge_all=1'

# 3. Cloudflare CDN: No direct purge available. Cache busting via version
#    parameter (?ver=X.Y.Z) is the primary strategy. Cloudflare TTL is ~24 hours.
#    Always bump CJC_CHILD_VERSION in functions.php when changing CSS/JS.
```

**Verification after deploy:**
```bash
# Check CSS version is updated
curl -s 'https://curtisjcooks.com/?v='$(date +%s) | grep -oE "style\.css\?ver=[0-9.]+"
# Check transparent header on category page
curl -s 'https://curtisjcooks.com/category/island-comfort/' | grep -oE '(transparent-header|non-transparent-header)'
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

## CJC Pinterest Pin Generator

Automated Pinterest pin image generator that creates branded pins for blog posts.

**Location:** `/tmp/cjc_pinterest/`

### Architecture
```
cjc_pinterest/
├── __main__.py        # CLI entry point
├── config.py          # Pin dimensions (1000x1500), colors, category→board mapping
├── fetcher.py         # Fetches posts/pages + featured images from WP REST API
├── copywriter.py      # Generates pin copy (title, description, hashtags, board, hook)
├── renderer.py        # Shared rendering utilities (fonts, text wrapping, gradients, pills)
├── pipeline.py        # Orchestrates pin generation, writes manifest CSV
└── templates/
    ├── hero_bold.py   # Template 1: Bold hero with color block header
    ├── full_bleed.py  # Template 2: Full bleed photo with gradient overlay
    └── split_card.py  # Template 3: Split layout (photo top, text bottom)
```

### CLI Commands
```bash
cd /tmp/cjc_pinterest
python3 -m cjc_pinterest generate --post-id 1234       # Single post
python3 -m cjc_pinterest generate --page-id 5019       # Single page
python3 -m cjc_pinterest generate --recent 5            # Last 5 posts
python3 -m cjc_pinterest generate --all                 # All posts
python3 -m cjc_pinterest list                           # List generated pins
```

### Output
- **Pin images:** `/tmp/cjc_pinterest/output/{post_id}-{slug}/pin-{1,2,3}-{variant}.png`
- **Per-post data:** `pin-data.json` in each post directory
- **Master manifest:** `/tmp/cjc_pinterest/output/manifest.csv` (all pins with copy/board/URL)
- **Pin dimensions:** 1000x1500px (2:3 Pinterest optimal)

### Fonts
Located at `/tmp/cjc_pinterest/fonts/`:
- Playfair Display (Bold, Regular) — titles
- Source Sans 3 (SemiBold, Regular) — body text

### Category → Board Mapping
Each WordPress category maps to a Pinterest board name:
- 860 (Island Comfort) → "Hawaiian Comfort Food"
- 859 (Poke & Seafood) → "Hawaiian Poke & Seafood"
- 873 (Hawaiian Breakfast) → "Hawaiian Breakfast Ideas"
- 861 (Tropical Treats) → "Hawaiian Desserts & Treats"
- 862 (Island Drinks) → "Hawaiian Drinks & Cocktails"
- 874 (Pupus & Snacks) → "Hawaiian Pupus & Appetizers"
- 866 (Quick & Easy) → "Quick Hawaiian Recipes"
- 857 (Hawaiian Culture) → "Hawaiian Food Culture"
- 107 (Kitchen Skills) → "Hawaiian Cooking Tips"
- 101 (Kitchen Essentials) → "Hawaiian Kitchen Essentials"

### Known Issues / Workarounds
- **Cloudflare image download timeouts:** The fetcher downloads featured images from the live site via curl, which can timeout behind Cloudflare. **Workaround:** Copy images from local generated-images directory (`/Users/curtisvaughan/Local%20Sites/curtisjcookscom1/app/wp-content/uploads/generated-images/`) to the pin output directory, then render templates directly via Python.
- **URL-encoded local paths:** Local Sites uses `%20` encoding in directory names (`Local%20Sites`), not regular spaces.
- **Draft posts inaccessible:** The fetcher uses the public WP REST API which can't access draft posts. Publish first, then generate pins.

### Current Stats
- **Total pins generated:** 102 pins across 34 posts (as of Feb 23, 2026)
- **3 variants per post:** hero-bold, full-bleed, split-card

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

### February 23, 2026 - Comprehensive Content Strategy Execution

**Content Strategy Review:** Performed full gap analysis across all categories, identifying missing foundational recipes, "What Is" explainers, pillar pages, guides, and affiliate content. Executed the entire priority list in one session.

**17 New Posts/Pages Created (all published with AI-generated featured images):**

**Recipes (4):**
| Title | ID | Slug | Categories | Media ID |
|-------|----|------|------------|----------|
| Hawaiian Macaroni Salad | 6538 | hawaiian-macaroni-salad-recipe | 860, 866, 26 | 6536 |
| Chicken Long Rice | 6539 | chicken-long-rice-hawaiian | 860, 26 | 6537 |
| Coconut Shrimp | 6546 | coconut-shrimp-recipe | 859, 874, 26 | 6545 |
| Haupia | 6554 | haupia-recipe | 861, 26 | 6552 |

**"What Is..." Informational (6):**
| Title | ID | Slug | Categories | Media ID |
|-------|----|------|------------|----------|
| What Is Poke? | 6547 | what-is-poke | 859, 857, 26 | 6550 |
| What Is a Plate Lunch? | 6555 | what-is-a-plate-lunch | 860, 857, 26 | 6553 |
| What Is Laulau? | 6558 | what-is-laulau | 860, 857, 26 | 6556 |
| What Is Spam Musubi? | 6559 | what-is-spam-musubi | 874, 857, 26 | 6557 |
| What Is Kalua Pig? | 6563 | what-is-kalua-pig | 860, 857, 26 | 6560 |
| What Is Poi? | 6564 | what-is-poi | 857, 26 | 6561 |

**Guides (4):**
| Title | ID | Slug | Type | Media ID |
|-------|----|------|------|----------|
| Hawaiian BBQ and Grilling Guide | 6568 | hawaiian-bbq-grilling-guide | PAGE (pillar, `page-pillar.php`) | 6565 |
| Hawaiian Side Dishes | 6569 | hawaiian-side-dishes-guide | post | 6566 |
| Hawaiian Meal Prep | 6570 | hawaiian-meal-prep-guide | post | 6567 |
| Hawaiian Thanksgiving Menu | 6578 | hawaiian-thanksgiving-menu | post | 6576 |

**Affiliate (3):**
| Title | ID | Slug | Categories | Media ID |
|-------|----|------|------------|----------|
| Best Bento Boxes | 6573 | best-bento-boxes-hawaiian-plate-lunch | 101, 26 | 6571 |
| Best Hawaiian Cookbooks | 6574 | best-hawaiian-cookbooks | 101, 857 | 6572 |
| Best Seasonings & Spices | 6577 | best-hawaiian-seasonings-spices | 101, 26 | 6575 |

**Pillar Page Updates:**
- Updated Plate Lunch pillar page (ID 5019) with links to new Mac Salad recipe and Chicken Long Rice in their respective sections

**Pinterest Pin Generation:**
- Generated 48 new pins (3 variants each for all 17 new posts, minus 3 already generated)
- Total pin count: 102 pins across 34 posts
- Used local image copy workaround for Cloudflare timeout issues
- Built CJC Pinterest Pin Generator system at `/tmp/cjc_pinterest/`

**Rewritten Pillar Pages (7):** All existing pillar pages were comprehensively rewritten earlier in this session with expanded content, better internal linking, and improved structure.

**`live_site_posts.json` refreshed** — Updated from 149 to 165 posts by merging 16 new entries from MCP API with existing file.

**CJC Brain Pipeline:** Ran full pipeline (`python3 -m cjc_brain all`). Result: 0 new briefs — all 13 potential topics caught as duplicates against the 165-post library. Content gaps are well covered.

**Fixed 404 on `/?p=4654`:**
- Deleted post ID 4654 was returning 404 via `?p=` query parameter
- Rank Math redirects can't catch query string URLs (only URL paths)
- Added `template_redirect` hook in `functions.php` to catch ALL 404s on `?p=` URLs and 301 redirect to homepage:
  ```php
  add_action('template_redirect', function () {
      if (is_404() && isset($_GET['p'])) {
          wp_redirect(home_url('/'), 301);
          exit;
      }
  });
  ```
- Committed to git, pushed to GitHub, deployed via WP Pusher, caches cleared
- Verified: `/?p=4654` now returns HTTP 301 to homepage

**Packaged MCP Server for Reuse:**
- Created `/tmp/wp-mcp-server/` — a generic, credentials-stripped version of the CJC MCP server
- 14 source files, ~98KB, 26 tools across 3 modules (WordPress, Recipes, AI)
- All config via env vars only (no hardcoded CurtisJCooks credentials)
- Generic food blog prompts with custom prompt override support (`CUSTOM_PROMPTS_PATH`)
- Hawaiian prompts preserved as `prompts/hawaiian-prompts.example.js`
- Full README with setup guide, tool reference, config docs
- Tested: all modules load correctly with env-var-only config

---

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
- Transparent header on ALL page types (homepage, posts, pages, archives, categories)
- White text/logo overlays hero images; Kadence native logo swap handles white logo (media ID 5361)

**Kadence Transparent Header — How It Works:**
- **There is NO `kadence_transparent_header` filter.** Do not use it — it doesn't exist in Kadence.
- Transparent header is controlled via the `kadence_post_layout` filter. Set `$layout['transparent'] = 'enable'` to activate.
- Kadence also has per-type Customizer toggles (`transparent_header_archive`, `transparent_header_page`, `transparent_header_post`) that can DISABLE it — our filter overrides all of them.
- The filter is in `functions.php`:
  ```php
  add_filter('kadence_post_layout', function ($layout) {
      if (is_page() || is_singular('post')) {
          $layout['title'] = 'normal';  // Our templates own the title area
      }
      $layout['transparent'] = 'enable';  // Immersive hero on all page types
      return $layout;
  });
  ```
- Kadence adds `transparent-header` / `mobile-transparent-header` body classes and sets `#masthead { position: absolute; z-index: 100; background: transparent; }` via inline CSS.
- Our `style.css` reinforces with `.transparent-header #masthead, .transparent-header .site-header, ...` selectors using `background: transparent !important` to override Kadence's `#masthead { background: #ffffff; }` inline rule.
- White text for nav/logo applied via `.transparent-header .site-header a { color: white !important; }` in `style.css`.
- Homepage scroll behavior: frosted glass header on scroll via `header--scrolled` class (JS in `homepage.js`).

**Known Issues / Gotchas:**
- Google Fonts must be enqueued as 3 separate `wp_enqueue_style()` calls (WordPress `esc_url()` breaks multi-family `&` in URLs)
- WP QUADS ad plugin injects a 300x250 ad at top of post content — hidden via `.recipe-story > .quads-location:first-child { display: none }`
- Kadence header has deeply nested elements that all need `background: transparent !important`
- Kadence outputs `#masthead { background: #ffffff; }` as inline CSS — must use `.transparent-header #masthead` selector (higher specificity) to override
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
- [x] Transparent header on ALL page types (posts, pages, archives, categories) with white text/logo
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
- [ ] Migrate recipe meta for 17 new posts (Feb 23) — these have recipe content in HTML but no structured `_cjc_recipe_*` meta
- [x] ~~Refresh `live_site_posts.json` for CJC Brain dedup~~ — Done Feb 23, refreshed to 165 posts
- [ ] Add BBQ pillar page to CJC Auto Schema FAQ data (currently only 7 pillar pages have FAQ schema)
- [ ] Clear Bluehost caches (Endurance + Cloudflare)
- [ ] Mobile testing on live site
- [ ] Archive page testing on live site
- [ ] Print stylesheet verification
- [ ] Migrate live site Divi shortcodes (only 1 post: #4089 Mai Tai Moments)
- [ ] SEO verification (schema output, meta tags, Open Graph)
- [ ] Performance audit (Core Web Vitals, Lighthouse)
- [ ] Verify recipe features plugin compatibility with new theme
- [ ] Upload Pinterest pins to Pinterest (102 pins ready in manifest.csv)
- [ ] Add Pinterest Pin Generator config for new BBQ pillar page to `PILLAR_PAGE_MAP`

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
