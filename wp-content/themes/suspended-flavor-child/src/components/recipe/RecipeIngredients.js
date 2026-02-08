/**
 * RecipeIngredients Component
 *
 * Displays the ingredient list with checkboxes and scaling.
 */

export default function RecipeIngredients({ ingredients = [], checklist, isScaled }) {
    if (!ingredients || ingredients.length === 0) {
        return null;
    }

    // Flatten all items for unique IDs
    const generateItemId = (groupIndex, itemIndex) => {
        return `ingredient-${groupIndex}-${itemIndex}`;
    };

    return (
        <section className="cjc-recipe-ingredients">
            <h3 className="cjc-recipe-section-title">Ingredients</h3>

            {isScaled && (
                <div className="cjc-recipe-scaled-notice">
                    <span className="cjc-recipe-scaled-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/>
                        </svg>
                    </span>
                    Quantities adjusted for your serving size
                </div>
            )}

            {ingredients.map((group, groupIndex) => (
                <div key={groupIndex} className="cjc-recipe-ingredient-group">
                    {group.title && (
                        <h4 className="cjc-recipe-ingredient-group-title">{group.title}</h4>
                    )}

                    <ul className="cjc-recipe-ingredient-list">
                        {group.items?.map((item, itemIndex) => {
                            const itemId = generateItemId(groupIndex, itemIndex);
                            const isChecked = checklist.isChecked(itemId);
                            const amount = isScaled && item.scaledAmount ? item.scaledAmount : item.amount;

                            return (
                                <li
                                    key={itemIndex}
                                    className={`cjc-recipe-ingredient-item ${isChecked ? 'is-checked' : ''}`}
                                >
                                    <label className="cjc-recipe-ingredient-label">
                                        <input
                                            type="checkbox"
                                            checked={isChecked}
                                            onChange={() => checklist.toggleItem(itemId)}
                                            className="cjc-recipe-ingredient-checkbox"
                                        />
                                        <span className="cjc-recipe-ingredient-checkbox-custom" aria-hidden="true">
                                            {isChecked && (
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                                </svg>
                                            )}
                                        </span>
                                        <span className="cjc-recipe-ingredient-text">
                                            {amount && (
                                                <span className={`cjc-recipe-ingredient-amount ${isScaled ? 'is-scaled' : ''}`}>
                                                    {amount}
                                                </span>
                                            )}
                                            {item.unit && (
                                                <span className="cjc-recipe-ingredient-unit">{item.unit}</span>
                                            )}
                                            <span className="cjc-recipe-ingredient-name">{item.name}</span>
                                            {item.notes && (
                                                <span className="cjc-recipe-ingredient-notes">, {item.notes}</span>
                                            )}
                                        </span>
                                    </label>
                                </li>
                            );
                        })}
                    </ul>
                </div>
            ))}

            {/* Clear all button */}
            {Object.keys(checklist.checkedItems || {}).length > 0 && (
                <button
                    type="button"
                    onClick={checklist.clearAll}
                    className="cjc-recipe-clear-checklist"
                >
                    Clear all checked items
                </button>
            )}
        </section>
    );
}
