/**
 * RecipeHeader Component
 *
 * Displays recipe image, title, description, and time metadata.
 */

import { formatTimeForDisplay } from '../../utils/timeUtils';

export default function RecipeHeader({
    title,
    description,
    imageUrl,
    prepTime,
    cookTime,
    totalTime,
    yield: recipeYield,
    category,
    cuisine,
}) {
    const formattedPrepTime = formatTimeForDisplay(prepTime);
    const formattedCookTime = formatTimeForDisplay(cookTime);
    const formattedTotalTime = formatTimeForDisplay(totalTime);

    return (
        <header className="cjc-recipe-header">
            {imageUrl && (
                <div className="cjc-recipe-image">
                    <img src={imageUrl} alt={title} loading="lazy" />
                </div>
            )}

            <div className="cjc-recipe-header-content">
                <h2 className="cjc-recipe-title">{title}</h2>

                {description && (
                    <div
                        className="cjc-recipe-description"
                        dangerouslySetInnerHTML={{ __html: description }}
                    />
                )}

                {/* Time metadata */}
                <div className="cjc-recipe-meta">
                    {formattedPrepTime && (
                        <div className="cjc-recipe-meta-item">
                            <span className="cjc-recipe-meta-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                                </svg>
                            </span>
                            <span className="cjc-recipe-meta-label">Prep</span>
                            <span className="cjc-recipe-meta-value">{formattedPrepTime}</span>
                        </div>
                    )}

                    {formattedCookTime && (
                        <div className="cjc-recipe-meta-item">
                            <span className="cjc-recipe-meta-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                                    <path d="M8.1 13.34l2.83-2.83L3.91 3.5a4.008 4.008 0 000 5.66l4.19 4.18zm6.78-1.81c1.53.71 3.68.21 5.27-1.38 1.91-1.91 2.28-4.65.81-6.12-1.46-1.46-4.2-1.1-6.12.81-1.59 1.59-2.09 3.74-1.38 5.27L3.7 19.87l1.41 1.41L12 14.41l6.88 6.88 1.41-1.41L13.41 13l1.47-1.47z"/>
                                </svg>
                            </span>
                            <span className="cjc-recipe-meta-label">Cook</span>
                            <span className="cjc-recipe-meta-value">{formattedCookTime}</span>
                        </div>
                    )}

                    {formattedTotalTime && (
                        <div className="cjc-recipe-meta-item cjc-recipe-meta-total">
                            <span className="cjc-recipe-meta-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                                    <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                                </svg>
                            </span>
                            <span className="cjc-recipe-meta-label">Total</span>
                            <span className="cjc-recipe-meta-value">{formattedTotalTime}</span>
                        </div>
                    )}

                    {recipeYield && (
                        <div className="cjc-recipe-meta-item">
                            <span className="cjc-recipe-meta-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                </svg>
                            </span>
                            <span className="cjc-recipe-meta-label">Yield</span>
                            <span className="cjc-recipe-meta-value">{recipeYield}</span>
                        </div>
                    )}
                </div>

                {/* Category/Cuisine tags */}
                {(category || cuisine) && (
                    <div className="cjc-recipe-tags">
                        {category && (
                            <span className="cjc-recipe-tag cjc-recipe-tag-category">
                                {category}
                            </span>
                        )}
                        {cuisine && (
                            <span className="cjc-recipe-tag cjc-recipe-tag-cuisine">
                                {cuisine}
                            </span>
                        )}
                    </div>
                )}
            </div>
        </header>
    );
}
