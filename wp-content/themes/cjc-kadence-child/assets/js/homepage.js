(function () {
    'use strict';

    /* ======================================================================
       Constants
       ====================================================================== */
    var REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ======================================================================
       Module 1: Time-Aware Content
       Detects visitor's local time and day, then updates the hero section
       with Hawaiian greetings, subtitles, CTA text, and CSS custom
       properties that shift the page's warmth based on time of day.
       ====================================================================== */
    function initTimeAwareness() {
        var now = new Date();
        var hour = now.getHours();
        var day = now.getDay(); // 0 = Sunday, 6 = Saturday
        var isWeekend = (day === 0 || day === 6);

        /* ----------------------------------------------------------
           Determine time period and base content
           ---------------------------------------------------------- */
        var period;    // 'morning' | 'afternoon' | 'evening'
        var greeting;
        var subtitle;
        var ctaText;
        var warmth;    // CSS custom property value
        var timeHeading;

        if (hour < 11) {
            period = 'morning';
            greeting = 'Aloha Kakahiaka';
            subtitle = 'Start your day with island flavors';
            ctaText = 'Breakfast Ideas';
            warmth = 0.08;
            timeHeading = 'N\u0101 Mea\u02BBai Kakahiaka \u2014 Morning Favorites';
        } else if (hour < 17) {
            period = 'afternoon';
            greeting = 'Aloha Awakea';
            subtitle = 'Quick bites and island favorites';
            ctaText = 'Find Something Quick';
            warmth = 0.15;
            timeHeading = 'N\u0101 Mea\u02BBai Awakea \u2014 Afternoon Picks';
        } else {
            period = 'evening';
            greeting = 'Aloha Ahiahi';
            subtitle = 'Settle in for something special';
            ctaText = 'What\u2019s for Dinner?';
            warmth = 0.25;
            timeHeading = 'N\u0101 Mea\u02BBai Ahiahi \u2014 Tonight\u2019s Inspiration';
        }

        /* ----------------------------------------------------------
           Weekend override (Saturday/Sunday, 11am or later)
           ---------------------------------------------------------- */
        if (isWeekend && hour >= 11) {
            subtitle = 'Weekend vibes \u2014 time to cook something special';
            ctaText = 'Weekend Cooking';
            timeHeading = 'N\u0101 Mea\u02BBai \u2014 Weekend Cooking';
        }

        /* ----------------------------------------------------------
           Update DOM elements
           ---------------------------------------------------------- */
        var greetingEl = document.querySelector('.homepage-hero__greeting');
        var subtitleEl = document.querySelector('.homepage-hero__subtitle');
        var ctaEl = document.querySelector('.homepage-hero__cta');
        var timeHeadingEl = document.querySelector('[data-time-heading]');

        if (greetingEl) greetingEl.textContent = greeting;
        if (subtitleEl) subtitleEl.textContent = subtitle;
        if (ctaEl) ctaEl.textContent = ctaText;
        if (timeHeadingEl) timeHeadingEl.textContent = timeHeading;

        /* ----------------------------------------------------------
           Set CSS custom property for hero warmth overlay
           ---------------------------------------------------------- */
        document.documentElement.style.setProperty('--hero-warmth', warmth);

        /* ----------------------------------------------------------
           Set data attributes on body for CSS hooks
           ---------------------------------------------------------- */
        document.body.setAttribute('data-time-period', period);
        document.body.setAttribute('data-is-weekend', isWeekend ? 'true' : 'false');
    }

    /* ======================================================================
       Module 2: Scroll Reveals
       Uses IntersectionObserver to add a `.revealed` class to elements
       with `[data-reveal]` as they scroll into view. Supports an optional
       `data-reveal-delay` attribute (milliseconds) for staggered reveals.
       Respects prefers-reduced-motion by revealing everything immediately.
       ====================================================================== */
    function initScrollReveals() {
        var revealElements = document.querySelectorAll('[data-reveal]');
        if (!revealElements.length) return;

        /* If the user prefers reduced motion, reveal everything at once */
        if (REDUCED_MOTION) {
            revealElements.forEach(function (el) {
                el.classList.add('revealed');
            });
            return;
        }

        /* Create a single observer for all reveal elements */
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;

                var el = entry.target;
                var delay = parseInt(el.getAttribute('data-reveal-delay'), 10) || 0;

                /* Unobserve immediately so we only trigger once */
                observer.unobserve(el);

                if (delay > 0) {
                    setTimeout(function () {
                        el.classList.add('revealed');
                    }, delay);
                } else {
                    el.classList.add('revealed');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -40px 0px'
        });

        /* Observe every [data-reveal] element */
        revealElements.forEach(function (el) {
            observer.observe(el);
        });
    }

    /* ======================================================================
       Module 3: Header Scroll
       Toggles `.header--scrolled` on the body when the user scrolls past
       90% of the viewport height. Uses requestAnimationFrame throttling
       on a passive scroll listener. Also hides the hero scroll-indicator
       when the hero section scrolls out of view.
       ====================================================================== */
    function initHeaderScroll() {
        var ticking = false;
        var scrolled = false;

        /* ----------------------------------------------------------
           Logo swap: Kadence only renders one logo in the HTML.
           On transparent pages it's the white logo. When the header
           switches to frosted glass on scroll, we swap to dark.
           Must also swap srcset (browser uses it over src).
           ---------------------------------------------------------- */
        var logoImgs = document.querySelectorAll('.site-header .custom-logo');
        var darkSrc = (typeof cjcLogos !== 'undefined' && cjcLogos.dark) ? cjcLogos.dark : '';
        var whiteSrc = (typeof cjcLogos !== 'undefined' && cjcLogos.white) ? cjcLogos.white : '';

        function swapLogo(newSrc) {
            if (!newSrc) return;
            logoImgs.forEach(function (img) {
                img.src = newSrc;
                /* Replace srcset/data-srcset: swap the filename stem */
                ['srcset', 'data-srcset'].forEach(function (attr) {
                    var val = img.getAttribute(attr);
                    if (!val) return;
                    /* Replace "transparent-white" with "transparent-dark" or vice versa */
                    if (newSrc.indexOf('transparent-dark') !== -1) {
                        img.setAttribute(attr, val.replace(/transparent-white/g, 'transparent-dark'));
                    } else {
                        img.setAttribute(attr, val.replace(/transparent-dark/g, 'transparent-white'));
                    }
                });
            });
        }

        /* ----------------------------------------------------------
           Scroll threshold toggle (90% of viewport)
           ---------------------------------------------------------- */
        window.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;

            requestAnimationFrame(function () {
                var threshold = window.innerHeight * 0.9;
                var scrollY = window.pageYOffset || document.documentElement.scrollTop;
                var isPastThreshold = scrollY > threshold;

                if (isPastThreshold !== scrolled) {
                    scrolled = isPastThreshold;
                    if (scrolled) {
                        document.body.classList.add('header--scrolled');
                        swapLogo(darkSrc);
                    } else {
                        document.body.classList.remove('header--scrolled');
                        swapLogo(whiteSrc);
                    }
                }

                ticking = false;
            });
        }, { passive: true });

        /* ----------------------------------------------------------
           Hide scroll indicator when hero leaves viewport
           ---------------------------------------------------------- */
        var hero = document.querySelector('.homepage-hero');
        var scrollIndicator = document.querySelector('.homepage-hero__scroll-indicator');

        if (hero && scrollIndicator) {
            var heroObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        scrollIndicator.classList.remove('hidden');
                    } else {
                        scrollIndicator.classList.add('hidden');
                    }
                });
            }, { threshold: 0.5 });

            heroObserver.observe(hero);
        }
    }

    /* ======================================================================
       Module 4: Recipe Picker
       A two-step interactive picker that helps visitors find a recipe
       based on time of day and mood/vibe. Recipe data is read from a
       JSON script element in the page. Supports keyboard navigation.
       ====================================================================== */
    function initPicker() {
        var picker = document.querySelector('.recipe-picker');
        if (!picker) return;

        /* ----------------------------------------------------------
           Parse recipe data from inline JSON
           ---------------------------------------------------------- */
        var dataEl = document.getElementById('picker-recipe-data');
        if (!dataEl) return;

        var recipes;
        try {
            recipes = JSON.parse(dataEl.textContent);
        } catch (e) {
            return; // Silently bail if JSON is invalid
        }

        if (!recipes || !recipes.length) return;

        /* ----------------------------------------------------------
           State
           ---------------------------------------------------------- */
        var state = { time: null, vibe: null };

        /* ----------------------------------------------------------
           Step elements and progress dots
           ---------------------------------------------------------- */
        var stepTime = picker.querySelector('.picker-step--time');
        var stepVibe = picker.querySelector('.picker-step--vibe');
        var stepResult = picker.querySelector('.picker-step--result');
        var steps = [stepTime, stepVibe, stepResult];
        var progressDots = picker.querySelectorAll('.picker-progress__dot');

        /* ----------------------------------------------------------
           setStep(num): Toggle active step and progress dots
           Steps are 1-indexed (1 = time, 2 = vibe, 3 = result)
           ---------------------------------------------------------- */
        function setStep(num) {
            steps.forEach(function (step, index) {
                if (!step) return;
                if (index === num - 1) {
                    step.classList.add('picker-step--active');
                } else {
                    step.classList.remove('picker-step--active');
                }
            });

            progressDots.forEach(function (dot, index) {
                if (index === num - 1) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        /* ----------------------------------------------------------
           Step 1: Time-of-day selection
           ---------------------------------------------------------- */
        var timeButtons = picker.querySelectorAll('[data-time]');

        timeButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                state.time = btn.getAttribute('data-time');

                /* Toggle selected class on time cards */
                timeButtons.forEach(function (b) {
                    b.classList.remove('picker-card--selected');
                });
                btn.classList.add('picker-card--selected');

                /* Advance to step 2 after brief delay */
                setTimeout(function () {
                    setStep(2);
                }, 400);
            });

            /* Keyboard support: Enter and Space */
            btn.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    btn.click();
                }
            });
        });

        /* ----------------------------------------------------------
           Step 2: Vibe/mood selection
           ---------------------------------------------------------- */
        var vibePills = picker.querySelectorAll('[data-vibe]');

        vibePills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                state.vibe = pill.getAttribute('data-vibe');

                /* Toggle selected class on vibe pills */
                vibePills.forEach(function (p) {
                    p.classList.remove('picker-pill--selected');
                });
                pill.classList.add('picker-pill--selected');

                /* Show result after brief delay */
                setTimeout(function () {
                    showResult();
                }, 400);
            });

            /* Keyboard support: Enter and Space */
            pill.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    pill.click();
                }
            });
        });

        /* ----------------------------------------------------------
           showResult(): Filter recipes and display a random match
           ---------------------------------------------------------- */
        function showResult() {
            var matches = [];

            /* Primary filter: match both time bucket and vibe */
            matches = recipes.filter(function (recipe) {
                var timeMatch = recipe.time_bucket === state.time;
                var vibeMatch = state.vibe === 'surprise' ||
                    (recipe.vibe_buckets && recipe.vibe_buckets.indexOf(state.vibe) !== -1);
                return timeMatch && vibeMatch;
            });

            /* Fallback 1: match vibe only */
            if (!matches.length) {
                matches = recipes.filter(function (recipe) {
                    return state.vibe === 'surprise' ||
                        (recipe.vibe_buckets && recipe.vibe_buckets.indexOf(state.vibe) !== -1);
                });
            }

            /* Fallback 2: any recipe */
            if (!matches.length) {
                matches = recipes;
            }

            /* Pick a random match */
            var recipe = matches[Math.floor(Math.random() * matches.length)];

            /* Populate result elements */
            var resultImage = picker.querySelector('.picker-result__image');
            var resultTitle = picker.querySelector('.picker-result__title');
            var resultExcerpt = picker.querySelector('.picker-result__excerpt');
            var resultLink = picker.querySelector('.picker-result__link');
            var resultBadge = picker.querySelector('.picker-result__badge');

            if (resultImage) {
                resultImage.src = recipe.image || '';
                resultImage.alt = recipe.title || '';
            }
            if (resultTitle) resultTitle.textContent = recipe.title || '';
            if (resultExcerpt) resultExcerpt.textContent = recipe.excerpt || '';
            if (resultLink) resultLink.href = recipe.url || '#';
            if (resultBadge) resultBadge.textContent = recipe.category || '';

            setStep(3);
        }

        /* ----------------------------------------------------------
           Reset button: clear state and return to step 1
           ---------------------------------------------------------- */
        var resetBtn = picker.querySelector('.picker-reset');

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                state.time = null;
                state.vibe = null;

                /* Remove all selected classes */
                timeButtons.forEach(function (btn) {
                    btn.classList.remove('picker-card--selected');
                });
                vibePills.forEach(function (pill) {
                    pill.classList.remove('picker-pill--selected');
                });

                setStep(1);
            });
        }

        /* ----------------------------------------------------------
           Initialize at step 1
           ---------------------------------------------------------- */
        setStep(1);
    }

    /* ======================================================================
       Module 5: Initialization
       All modules are called when the DOM is ready.
       ====================================================================== */
    document.addEventListener('DOMContentLoaded', function () {
        initTimeAwareness();
        initScrollReveals();
        initHeaderScroll();
        initPicker();
    });
})();
