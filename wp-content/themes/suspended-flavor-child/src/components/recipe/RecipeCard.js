/**
 * RecipeCard Component
 *
 * Main container component for the recipe card.
 */

import { useState, useEffect } from 'react';
import RecipeHeader from './RecipeHeader';
import RecipeIngredients from './RecipeIngredients';
import RecipeInstructions from './RecipeInstructions';
import RecipeNutrition from './RecipeNutrition';
import RecipeActions from './RecipeActions';
import RecipeCookMode from './RecipeCookMode';
import useRecipeScaling from '../../hooks/useRecipeScaling';
import { useRecipeChecklist } from '../../hooks/useLocalStorage';

export default function RecipeCard({ recipeId, data }) {
    const [showCookMode, setShowCookMode] = useState(false);
    const [showNutrition, setShowNutrition] = useState(false);

    // Initialize scaling with base servings from recipe
    const scaling = useRecipeScaling(
        data.yield_number || 4,
        data.ingredients || []
    );

    // Initialize checklist persistence
    const checklist = useRecipeChecklist(recipeId);

    // Handle print
    const handlePrint = () => {
        window.print();
    };

    // Handle cook mode toggle
    const toggleCookMode = () => {
        setShowCookMode((prev) => !prev);
    };

    // Handle nutrition toggle
    const toggleNutrition = () => {
        setShowNutrition((prev) => !prev);
    };

    // Check if we have nutrition data
    const hasNutrition = data.nutrition && Object.values(data.nutrition).some((v) => v);

    return (
        <article className="cjc-recipe-card" data-recipe-id={recipeId}>
            {/* Header with image, title, times */}
            <RecipeHeader
                title={data.title}
                description={data.description}
                imageUrl={data.image_url}
                prepTime={data.prep_time}
                cookTime={data.cook_time}
                totalTime={data.total_time}
                yield={data.yield}
                category={data.category}
                cuisine={data.cuisine}
            />

            {/* Action buttons */}
            <RecipeActions
                onPrint={handlePrint}
                onToggleCookMode={toggleCookMode}
                onToggleNutrition={hasNutrition ? toggleNutrition : null}
                showNutrition={showNutrition}
                scaling={scaling}
            />

            {/* Nutrition panel (collapsible) */}
            {hasNutrition && showNutrition && (
                <RecipeNutrition nutrition={data.nutrition} />
            )}

            {/* Ingredients */}
            <RecipeIngredients
                ingredients={scaling.scaledIngredients}
                checklist={checklist}
                isScaled={scaling.isScaled}
            />

            {/* Instructions */}
            <RecipeInstructions instructions={data.instructions || []} />

            {/* Notes */}
            {data.notes && (
                <div className="cjc-recipe-notes">
                    <h3 className="cjc-recipe-section-title">Notes</h3>
                    <div
                        className="cjc-recipe-notes-content"
                        dangerouslySetInnerHTML={{ __html: data.notes }}
                    />
                </div>
            )}

            {/* Cook Mode Overlay */}
            {showCookMode && (
                <RecipeCookMode
                    title={data.title}
                    instructions={data.instructions || []}
                    onClose={toggleCookMode}
                />
            )}
        </article>
    );
}
