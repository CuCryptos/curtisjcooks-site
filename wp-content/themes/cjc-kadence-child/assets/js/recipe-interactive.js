(function () {
    'use strict';

    /* ======================================================================
       Utility: Parse fractional strings like "1 1/2" → 1.5
       ====================================================================== */
    function parseFraction(str) {
        if (!str) return 0;
        str = str.trim();

        // Handle unicode fractions first
        var unicodeMap = {
            '\u00BC': 0.25, // 1/4
            '\u00BD': 0.5,  // 1/2
            '\u00BE': 0.75, // 3/4
            '\u2153': 0.333333, // 1/3
            '\u2154': 0.666667  // 2/3
        };
        for (var uc in unicodeMap) {
            if (str.indexOf(uc) !== -1) {
                var before = str.replace(uc, '').trim();
                return (before ? parseFloat(before) : 0) + unicodeMap[uc];
            }
        }

        // "1 1/2" or "1/2"
        var parts = str.split(/\s+/);
        var total = 0;
        for (var i = 0; i < parts.length; i++) {
            if (parts[i].indexOf('/') !== -1) {
                var frac = parts[i].split('/');
                if (frac.length === 2 && parseFloat(frac[1]) !== 0) {
                    total += parseFloat(frac[0]) / parseFloat(frac[1]);
                }
            } else if (parts[i] !== '') {
                total += parseFloat(parts[i]) || 0;
            }
        }
        return total;
    }

    /* ======================================================================
       Utility: Format a decimal amount into a nice display string
       ====================================================================== */
    function formatAmount(num) {
        if (num === 0) return '0';

        var whole = Math.floor(num);
        var decimal = num - whole;

        // Common fractions with unicode display
        var fractions = [
            { val: 0.25, str: '\u00BC' },
            { val: 0.5,  str: '\u00BD' },
            { val: 0.75, str: '\u00BE' },
            { val: 0.333333, str: '\u2153' },
            { val: 0.666667, str: '\u2154' }
        ];

        for (var i = 0; i < fractions.length; i++) {
            if (Math.abs(decimal - fractions[i].val) < 0.01) {
                return whole > 0 ? whole + ' ' + fractions[i].str : fractions[i].str;
            }
        }

        // Fall back to rounded decimal (1 decimal place)
        if (decimal > 0.01) {
            return parseFloat(num.toFixed(2)).toString();
        }
        return whole.toString();
    }

    /* ======================================================================
       Utility: Extract post ID from body class "postid-XXX"
       ====================================================================== */
    function getPostId() {
        var bodyClasses = document.body.className;
        var match = bodyClasses.match(/postid-(\d+)/);
        return match ? match[1] : null;
    }

    /* ======================================================================
       Servings Scaler
       ====================================================================== */
    function initScaler() {
        var scalerValue = document.querySelector('.recipe-scaler__value');
        var scalerDown = document.querySelector('.recipe-scaler__btn--down');
        var scalerUp = document.querySelector('.recipe-scaler__btn--up');

        if (!scalerValue || !scalerDown || !scalerUp) return;

        var originalServings = parseInt(scalerValue.textContent, 10) || 1;

        // Store original amounts on first run
        var ingredientTexts = document.querySelectorAll('.recipe-ingredient__text[data-amount]');
        ingredientTexts.forEach(function (el) {
            if (!el.hasAttribute('data-original')) {
                el.setAttribute('data-original', el.getAttribute('data-amount'));
            }
        });

        function updateIngredients(newServings) {
            var ratio = newServings / originalServings;
            ingredientTexts.forEach(function (el) {
                var original = parseFraction(el.getAttribute('data-original'));
                var scaled = original * ratio;
                var formatted = formatAmount(scaled);
                var strong = el.querySelector('strong');
                if (strong) {
                    strong.textContent = formatted;
                }
            });
        }

        scalerDown.addEventListener('click', function () {
            var current = parseInt(scalerValue.textContent, 10) || 1;
            var next = Math.max(1, current - 1);
            scalerValue.textContent = next;
            updateIngredients(next);
        });

        scalerUp.addEventListener('click', function () {
            var current = parseInt(scalerValue.textContent, 10) || 1;
            var next = Math.min(99, current + 1);
            scalerValue.textContent = next;
            updateIngredients(next);
        });
    }

    /* ======================================================================
       Ingredient Checkboxes
       ====================================================================== */
    function initCheckboxes() {
        var checkboxes = document.querySelectorAll('.recipe-ingredient__checkbox');
        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', function () {
                var ingredient = cb.closest('.recipe-ingredient');
                if (!ingredient) return;
                if (cb.checked) {
                    ingredient.classList.add('recipe-ingredient--checked');
                } else {
                    ingredient.classList.remove('recipe-ingredient--checked');
                }
            });
        });
    }

    /* ======================================================================
       Save Recipe
       ====================================================================== */
    function initSaveRecipe() {
        var saveBtn = document.querySelector('.recipe-card__action-btn--save');
        if (!saveBtn) return;

        var postId = getPostId();
        if (!postId) return;

        var storageKey = 'cjc_saved_' + postId;

        // Restore saved state
        if (localStorage.getItem(storageKey) === 'true') {
            saveBtn.classList.add('recipe-card__action-btn--saved');
            saveBtn.innerHTML = '&#9829; Saved';
        }

        saveBtn.addEventListener('click', function () {
            var isSaved = localStorage.getItem(storageKey) === 'true';
            if (isSaved) {
                localStorage.removeItem(storageKey);
                saveBtn.classList.remove('recipe-card__action-btn--saved');
                saveBtn.innerHTML = '&#9825; Save';
            } else {
                localStorage.setItem(storageKey, 'true');
                saveBtn.classList.add('recipe-card__action-btn--saved');
                saveBtn.innerHTML = '&#9829; Saved';
            }
        });
    }

    /* ======================================================================
       Shopping List (Copy to Clipboard)
       ====================================================================== */
    function initShoppingList() {
        var shoppingBtn = document.querySelector('.recipe-card__shopping-btn');
        if (!shoppingBtn) return;

        shoppingBtn.addEventListener('click', function () {
            var ingredients = document.querySelectorAll('.recipe-ingredient__text');
            var lines = [];
            ingredients.forEach(function (el) {
                var text = el.textContent.trim();
                if (text) lines.push(text);
            });

            var listText = lines.join('\n');

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(listText).then(function () {
                    var originalText = shoppingBtn.textContent;
                    shoppingBtn.textContent = 'Copied!';
                    setTimeout(function () {
                        shoppingBtn.textContent = originalText;
                    }, 2000);
                }).catch(function () {
                    fallbackCopy(listText, shoppingBtn);
                });
            } else {
                fallbackCopy(listText, shoppingBtn);
            }
        });

        function fallbackCopy(text, btn) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                var originalText = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(function () {
                    btn.textContent = originalText;
                }, 2000);
            } catch (e) {
                // Silently fail
            }
            document.body.removeChild(textarea);
        }
    }

    /* ======================================================================
       Nutrition Toggle
       ====================================================================== */
    function initNutritionToggle() {
        var toggle = document.querySelector('.recipe-nutrition__toggle');
        var content = document.querySelector('.recipe-nutrition__content');
        if (!toggle || !content) return;

        toggle.addEventListener('click', function () {
            var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', !isExpanded);
            content.classList.toggle('recipe-nutrition__content--open');
        });
    }

    /* ======================================================================
       Initialization
       ====================================================================== */
    document.addEventListener('DOMContentLoaded', function () {
        initScaler();
        initCheckboxes();
        initSaveRecipe();
        initShoppingList();
        initNutritionToggle();
    });
})();
