(function () {
    'use strict';

    /* ======================================================================
       Reading Progress Bar
       ====================================================================== */
    function initReadingProgress() {
        var progressBar = document.querySelector('.reading-progress');
        if (!progressBar) return;

        window.addEventListener('scroll', function () {
            var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            var docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            var percentage = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
            progressBar.style.width = percentage + '%';
        }, { passive: true });
    }

    /* ======================================================================
       Sticky Nav — Show when hero is out of view
       ====================================================================== */
    function initStickyNav() {
        var hero = document.querySelector('.recipe-hero');
        var nav = document.querySelector('.recipe-sticky-nav');
        if (!hero || !nav) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    nav.classList.remove('recipe-sticky-nav--visible');
                } else {
                    nav.classList.add('recipe-sticky-nav--visible');
                }
            });
        }, { threshold: 0 });

        observer.observe(hero);
    }

    /* ======================================================================
       Active Section Highlighting
       ====================================================================== */
    function initActiveSections() {
        var navLinks = document.querySelectorAll('.recipe-sticky-nav__link');
        if (!navLinks.length) return;

        var sectionIds = [];
        navLinks.forEach(function (link) {
            var section = link.getAttribute('data-section');
            if (section) sectionIds.push(section);
        });

        var sections = [];
        sectionIds.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) sections.push(el);
        });

        if (!sections.length) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                var sectionId = entry.target.id;
                var correspondingLink = document.querySelector(
                    '.recipe-sticky-nav__link[data-section="' + sectionId + '"]'
                );
                if (!correspondingLink) return;

                if (entry.isIntersecting) {
                    navLinks.forEach(function (link) {
                        link.classList.remove('recipe-sticky-nav__link--active');
                    });
                    correspondingLink.classList.add('recipe-sticky-nav__link--active');
                }
            });
        }, {
            rootMargin: '-20% 0px -60% 0px',
            threshold: 0
        });

        sections.forEach(function (section) {
            observer.observe(section);
        });
    }

    /* ======================================================================
       Smooth Scroll for Nav Links
       ====================================================================== */
    function initSmoothScroll() {
        var navLinks = document.querySelectorAll('.recipe-sticky-nav__link');
        navLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var sectionId = link.getAttribute('data-section') || link.getAttribute('href').replace('#', '');
                var target = document.getElementById(sectionId);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }

    /* ======================================================================
       Jump to Recipe Button
       ====================================================================== */
    function initJumpToRecipe() {
        var jumpBtn = document.querySelector('.jump-to-recipe');
        var story = document.querySelector('.recipe-story');
        var recipeCard = document.querySelector('.recipe-card');
        if (!jumpBtn || !story) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    jumpBtn.classList.add('jump-to-recipe--visible');
                } else {
                    jumpBtn.classList.remove('jump-to-recipe--visible');
                }
            });
        }, { threshold: 0 });

        observer.observe(story);

        jumpBtn.addEventListener('click', function () {
            if (recipeCard) {
                recipeCard.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    /* ======================================================================
       Scroll Animations (respects prefers-reduced-motion)
       ====================================================================== */
    function initScrollAnimations() {
        var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (prefersReducedMotion.matches) return;

        var animateTargets = document.querySelectorAll(
            '.recipe-story, .recipe-card, .related-recipes, .recipe-nutrition'
        );

        if (!animateTargets.length) return;

        animateTargets.forEach(function (el) {
            el.style.opacity = '0';
            el.style.transform = 'translateY(24px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        });

        var observer = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        animateTargets.forEach(function (el) {
            observer.observe(el);
        });
    }

    /* ======================================================================
       Initialization
       ====================================================================== */
    document.addEventListener('DOMContentLoaded', function () {
        initReadingProgress();
        initStickyNav();
        initActiveSections();
        initSmoothScroll();
        initJumpToRecipe();
        initScrollAnimations();
    });
})();
