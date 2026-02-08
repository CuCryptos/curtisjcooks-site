/**
 * RecipeActions Component
 *
 * Action buttons for print, scaling, and cook mode.
 */

import { useState } from 'react';

export default function RecipeActions({
    onPrint,
    onToggleCookMode,
    onToggleNutrition,
    showNutrition,
    scaling,
}) {
    const [showScaling, setShowScaling] = useState(false);

    return (
        <div className="cjc-recipe-actions">
            {/* Scaling Controls */}
            <div className="cjc-recipe-scaling">
                <button
                    type="button"
                    className={`cjc-recipe-action-btn cjc-recipe-scale-toggle ${showScaling ? 'is-active' : ''}`}
                    onClick={() => setShowScaling(!showScaling)}
                    aria-expanded={showScaling}
                >
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/>
                    </svg>
                    <span>
                        {scaling.currentServings} {scaling.currentServings === 1 ? 'serving' : 'servings'}
                    </span>
                    {scaling.isScaled && (
                        <span className="cjc-recipe-scaled-badge">Scaled</span>
                    )}
                </button>

                {showScaling && (
                    <div className="cjc-recipe-scaling-panel">
                        <div className="cjc-recipe-scaling-controls">
                            <button
                                type="button"
                                className="cjc-recipe-scaling-btn"
                                onClick={scaling.decreaseServings}
                                disabled={scaling.currentServings <= 1}
                                aria-label="Decrease servings"
                            >
                                -
                            </button>
                            <input
                                type="number"
                                className="cjc-recipe-scaling-input"
                                value={scaling.currentServings}
                                onChange={(e) => scaling.setServings(e.target.value)}
                                min="1"
                                max="999"
                                aria-label="Number of servings"
                            />
                            <button
                                type="button"
                                className="cjc-recipe-scaling-btn"
                                onClick={scaling.increaseServings}
                                disabled={scaling.currentServings >= 999}
                                aria-label="Increase servings"
                            >
                                +
                            </button>
                        </div>

                        <div className="cjc-recipe-scaling-presets">
                            {scaling.presets.map((preset) => (
                                <button
                                    key={preset.label}
                                    type="button"
                                    className={`cjc-recipe-preset-btn ${
                                        scaling.currentServings === preset.servings ? 'is-active' : ''
                                    }`}
                                    onClick={() => scaling.setServings(preset.servings)}
                                >
                                    {preset.label}
                                </button>
                            ))}
                        </div>

                        {scaling.isScaled && (
                            <button
                                type="button"
                                className="cjc-recipe-reset-btn"
                                onClick={scaling.resetServings}
                            >
                                Reset to original ({scaling.baseServings})
                            </button>
                        )}
                    </div>
                )}
            </div>

            {/* Action Buttons */}
            <div className="cjc-recipe-action-buttons">
                {/* Print Button */}
                <button
                    type="button"
                    className="cjc-recipe-action-btn"
                    onClick={onPrint}
                    aria-label="Print recipe"
                >
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                        <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
                    </svg>
                    <span>Print</span>
                </button>

                {/* Nutrition Toggle */}
                {onToggleNutrition && (
                    <button
                        type="button"
                        className={`cjc-recipe-action-btn ${showNutrition ? 'is-active' : ''}`}
                        onClick={onToggleNutrition}
                        aria-expanded={showNutrition}
                        aria-label="Toggle nutrition facts"
                    >
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                        </svg>
                        <span>Nutrition</span>
                    </button>
                )}

                {/* Cook Mode Button */}
                <button
                    type="button"
                    className="cjc-recipe-action-btn cjc-recipe-cook-mode-btn"
                    onClick={onToggleCookMode}
                    aria-label="Enter cook mode"
                >
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                        <path d="M8.1 13.34l2.83-2.83L3.91 3.5a4.008 4.008 0 000 5.66l4.19 4.18zm6.78-1.81c1.53.71 3.68.21 5.27-1.38 1.91-1.91 2.28-4.65.81-6.12-1.46-1.46-4.2-1.1-6.12.81-1.59 1.59-2.09 3.74-1.38 5.27L3.7 19.87l1.41 1.41L12 14.41l6.88 6.88 1.41-1.41L13.41 13l1.47-1.47z"/>
                    </svg>
                    <span>Cook Mode</span>
                </button>
            </div>
        </div>
    );
}
