/**
 * CurtisJCooks Enhanced Animations
 * Scroll animations, counters, and interactive elements
 */

(function() {
    'use strict';

    // Intersection Observer for scroll animations
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');

                // If it's a counter, start counting
                if (entry.target.classList.contains('cjc-stat-number')) {
                    animateCounter(entry.target);
                }
            }
        });
    }, observerOptions);

    // Observe all scroll-animated elements
    function initScrollAnimations() {
        const animatedElements = document.querySelectorAll('.cjc-scroll-animate, .cjc-stat-number');
        animatedElements.forEach(el => scrollObserver.observe(el));
    }

    // Counter animation
    function animateCounter(element) {
        if (element.dataset.animated) return;
        element.dataset.animated = 'true';

        const target = parseFloat(element.dataset.target) || 0;
        const duration = parseInt(element.dataset.duration) || 2000;
        const suffix = element.dataset.suffix || '';
        const prefix = element.dataset.prefix || '';
        const decimals = element.dataset.decimals ? parseInt(element.dataset.decimals) : 0;

        const startTime = performance.now();
        const startValue = 0;

        function updateCounter(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Easing function (ease-out)
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const currentValue = startValue + (target - startValue) * easeOut;

            if (decimals > 0) {
                element.textContent = prefix + currentValue.toFixed(decimals) + suffix;
            } else {
                element.textContent = prefix + Math.floor(currentValue).toLocaleString() + suffix;
            }

            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            }
        }

        requestAnimationFrame(updateCounter);
    }

    // Category pill filtering (if enabled)
    function initCategoryPills() {
        const pills = document.querySelectorAll('.cjc-category-pill[data-category]');
        const cards = document.querySelectorAll('.cjc-recipe-card-enhanced[data-category]');

        pills.forEach(pill => {
            pill.addEventListener('click', (e) => {
                e.preventDefault();

                // Update active state
                pills.forEach(p => p.classList.remove('active'));
                pill.classList.add('active');

                const category = pill.dataset.category;

                // Filter cards
                cards.forEach(card => {
                    if (category === 'all' || card.dataset.category === category) {
                        card.style.display = 'block';
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 50);
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
    }

    // Smooth scroll for anchor links
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    // Parallax effect for hero background
    function initParallax() {
        const hero = document.querySelector('.cjc-hero-enhanced');
        if (!hero) return;

        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const heroHeight = hero.offsetHeight;

            if (scrolled < heroHeight) {
                const bgAnimation = hero.querySelector('.cjc-hero-bg-animation');
                if (bgAnimation) {
                    bgAnimation.style.transform = `translateY(${scrolled * 0.3}px)`;
                }
            }
        });
    }

    // Initialize everything when DOM is ready
    function init() {
        initScrollAnimations();
        initCategoryPills();
        initSmoothScroll();
        initParallax();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
