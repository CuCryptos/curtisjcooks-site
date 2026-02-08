/**
 * CJC Recipe Frontend Entry Point
 *
 * Hydrates server-rendered recipe cards with React interactivity.
 */

import { createRoot } from 'react-dom/client';
import RecipeCard from './components/recipe/RecipeCard';

// Import styles
import './styles/recipe.css';

/**
 * Initialize recipe cards when DOM is ready.
 */
function initRecipeCards() {
    // Find all recipe card containers
    const containers = document.querySelectorAll('.cjc-recipe-card-container');

    containers.forEach((container) => {
        const recipeId = container.dataset.recipeId;
        if (!recipeId) {
            return;
        }

        // Get recipe data from script tag
        const dataScript = document.getElementById(`cjc-recipe-data-${recipeId}`);
        if (!dataScript) {
            console.warn(`Recipe data not found for recipe ${recipeId}`);
            return;
        }

        let data;
        try {
            data = JSON.parse(dataScript.textContent);
        } catch (e) {
            console.error(`Failed to parse recipe data for recipe ${recipeId}:`, e);
            return;
        }

        // Remove the static noscript content
        const noscriptContent = container.querySelector('.cjc-recipe-noscript');
        if (noscriptContent) {
            noscriptContent.remove();
        }

        // Create React root and render
        const root = createRoot(container);
        root.render(
            <RecipeCard recipeId={parseInt(recipeId, 10)} data={data} />
        );
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRecipeCards);
} else {
    initRecipeCards();
}

// Export for potential external use
export { RecipeCard };
