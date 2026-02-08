<?php
/**
 * Template Name: About Page
 * Custom about page for CurtisJCooks
 */

get_header();

$author_image = function_exists('curtisjcooks_get_site_image') ? curtisjcooks_get_site_image('author-photo-curtis') : '';
?>

<style>
/* About Page Styles */
.cjc-about-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 60px 24px;
}

.cjc-about-hero {
    text-align: center;
    margin-bottom: 60px;
}

.cjc-about-photo {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #f97316;
    margin-bottom: 24px;
    box-shadow: 0 10px 40px rgba(249, 115, 22, 0.2);
}

.cjc-about-hero h1 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 3rem;
    color: #1f2937;
    margin: 0 0 16px 0;
}

.cjc-about-hero .cjc-tagline {
    font-size: 1.25rem;
    color: #6b7280;
}

.cjc-about-content {
    font-size: 1.125rem;
    line-height: 1.8;
    color: #374151;
}

.cjc-about-content h2 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 2rem;
    color: #1f2937;
    margin: 48px 0 24px 0;
    padding-top: 24px;
    border-top: 2px solid #f3f4f6;
}

.cjc-about-content h2:first-of-type {
    border-top: none;
    margin-top: 0;
    padding-top: 0;
}

.cjc-about-content p {
    margin-bottom: 24px;
}

.cjc-highlight-box {
    background: linear-gradient(135deg, #fff7ed 0%, #fef3c7 100%);
    border-left: 4px solid #f97316;
    padding: 24px;
    margin: 32px 0;
    border-radius: 0 12px 12px 0;
}

.cjc-highlight-box p {
    margin: 0;
    font-style: italic;
    color: #92400e;
}

.cjc-fun-facts {
    background: #f9fafb;
    padding: 32px;
    border-radius: 16px;
    margin: 48px 0;
}

.cjc-fun-facts h3 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.5rem;
    color: #1f2937;
    margin: 0 0 24px 0;
    text-align: center;
}

.cjc-fun-facts ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 16px;
}

.cjc-fun-facts li {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.cjc-fun-facts li::before {
    content: "🌺";
    font-size: 1.25rem;
}

.cjc-fun-facts strong {
    color: #f97316;
}

.cjc-cta-section {
    text-align: center;
    padding: 48px;
    background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
    border-radius: 16px;
    margin-top: 48px;
}

.cjc-cta-section h3 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.75rem;
    color: white;
    margin: 0 0 16px 0;
}

.cjc-cta-section p {
    color: rgba(255, 255, 255, 0.9);
    margin: 0 0 24px 0;
}

.cjc-cta-button {
    display: inline-block;
    background: #f97316;
    color: white !important;
    padding: 14px 32px;
    border-radius: 50px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
}

.cjc-cta-button:hover {
    background: #ea580c;
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}
</style>

<div id="main-content">
    <article class="cjc-about-page">

        <div class="cjc-about-hero">
            <?php if ($author_image): ?>
                <img src="<?php echo esc_url($author_image); ?>" alt="Curtis J" class="cjc-about-photo">
            <?php else: ?>
                <div class="cjc-about-photo" style="background: linear-gradient(135deg, #f97316, #fb923c); display: flex; align-items: center; justify-content: center; font-size: 4rem; color: white;">🌺</div>
            <?php endif; ?>
            <h1>Aloha, I'm Curtis!</h1>
            <p class="cjc-tagline">Bringing the taste of Hawaii to kitchens everywhere</p>
        </div>

        <div class="cjc-about-content">

            <h2>From Island Kid to Recipe Creator</h2>

            <p>Growing up on the islands, my grandmother's kitchen was my first classroom. The smell of kalua pork slow-cooking in the imu, the sizzle of Spam hitting a hot pan for musubi, the sweet aroma of haupia setting in the fridge – these weren't just meals, they were lessons in love, tradition, and the true meaning of ohana.</p>

            <p>Every dish had a story. Every recipe was a connection to generations past. And every meal was an opportunity to bring people together.</p>

            <div class="cjc-highlight-box">
                <p>"In Hawaii, we don't just cook food – we cook memories. Every plate lunch tells a story, every pupu platter starts a conversation, and every family recipe carries the love of those who came before us."</p>
            </div>

            <h2>Why I Started CurtisJCooks</h2>

            <p>After moving to the mainland, I realized how much I missed the flavors of home. I also discovered that authentic Hawaiian recipes were surprisingly hard to find online. There were plenty of "tropical" recipes out there, but not the real local grindz I grew up with.</p>

            <p>That's when CurtisJCooks was born. My mission is simple: to share genuine Hawaiian recipes with anyone who wants to experience the aloha spirit through food. Whether you're a Hawaii transplant missing home, a curious foodie exploring new cuisines, or someone who just wants to make a killer poke bowl – you're welcome here.</p>

            <h2>What You'll Find Here</h2>

            <p>Every recipe on this site comes from my heart (and my family's recipe box). You'll find:</p>

            <ul>
                <li><strong>Authentic Hawaiian recipes</strong> passed down through generations</li>
                <li><strong>Modern twists</strong> on island classics for the contemporary kitchen</li>
                <li><strong>Stories and tips</strong> that bring context and meaning to each dish</li>
                <li><strong>Practical guidance</strong> for cooking Hawaiian food anywhere in the world</li>
            </ul>

            <div class="cjc-fun-facts">
                <h3>Fun Facts About Me</h3>
                <ul>
                    <li><strong>Favorite Hawaiian dish:</strong> Loco Moco – nothing beats that runny egg over rice and gravy</li>
                    <li><strong>Go-to comfort food:</strong> Spam musubi, especially after a long day</li>
                    <li><strong>Most missed island:</strong> Oahu – Waikiki sunsets hit different</li>
                    <li><strong>Secret ingredient:</strong> Li hing mui powder on everything (trust me)</li>
                    <li><strong>Kitchen anthem:</strong> Anything by Israel Kamakawiwo'ole</li>
                </ul>
            </div>

            <h2>Join the Ohana</h2>

            <p>Hawaiian culture is built on the concept of ohana – family. And on this site, you're part of mine. I'm here to help you succeed in the kitchen, answer your questions, and celebrate your cooking wins.</p>

            <p>Got a question about a recipe? Want to share your own Hawaiian food memories? Just want to talk story? I'd love to hear from you.</p>

            <div class="cjc-cta-section">
                <h3>Ready to Start Cooking?</h3>
                <p>Browse my collection of authentic Hawaiian recipes and bring the islands to your table.</p>
                <a href="<?php echo home_url('/recipes/'); ?>" class="cjc-cta-button">Explore Recipes →</a>
            </div>

        </div>

    </article>
</div>

<?php get_footer(); ?>
