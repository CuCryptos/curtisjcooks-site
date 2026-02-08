/**
 * RecipeCookMode Component
 *
 * Fullscreen cooking mode overlay with step-by-step navigation.
 */

import { useState, useEffect, useCallback } from 'react';

export default function RecipeCookMode({ title, instructions = [], onClose }) {
    // Flatten all steps into a single array
    const allSteps = instructions.flatMap((group, groupIndex) =>
        (group.steps || []).map((step, stepIndex) => ({
            ...step,
            groupTitle: group.title,
            groupIndex,
            stepIndex,
        }))
    );

    const [currentStep, setCurrentStep] = useState(0);
    const totalSteps = allSteps.length;

    // Keyboard navigation
    const handleKeyDown = useCallback((e) => {
        switch (e.key) {
            case 'ArrowRight':
            case ' ':
                e.preventDefault();
                setCurrentStep((prev) => Math.min(prev + 1, totalSteps - 1));
                break;
            case 'ArrowLeft':
                e.preventDefault();
                setCurrentStep((prev) => Math.max(prev - 1, 0));
                break;
            case 'Escape':
                onClose();
                break;
            case 'Home':
                e.preventDefault();
                setCurrentStep(0);
                break;
            case 'End':
                e.preventDefault();
                setCurrentStep(totalSteps - 1);
                break;
            default:
                break;
        }
    }, [totalSteps, onClose]);

    // Attach keyboard listener
    useEffect(() => {
        document.addEventListener('keydown', handleKeyDown);
        return () => document.removeEventListener('keydown', handleKeyDown);
    }, [handleKeyDown]);

    // Lock body scroll
    useEffect(() => {
        document.body.style.overflow = 'hidden';
        return () => {
            document.body.style.overflow = '';
        };
    }, []);

    // Keep screen awake (if supported)
    useEffect(() => {
        let wakeLock = null;

        const requestWakeLock = async () => {
            if ('wakeLock' in navigator) {
                try {
                    wakeLock = await navigator.wakeLock.request('screen');
                } catch (err) {
                    console.log('Wake Lock error:', err);
                }
            }
        };

        requestWakeLock();

        return () => {
            if (wakeLock) {
                wakeLock.release();
            }
        };
    }, []);

    if (totalSteps === 0) {
        return null;
    }

    const step = allSteps[currentStep];
    const progress = ((currentStep + 1) / totalSteps) * 100;

    return (
        <div className="cjc-recipe-cook-mode" role="dialog" aria-modal="true" aria-label="Cook mode">
            {/* Header */}
            <header className="cjc-cook-mode-header">
                <div className="cjc-cook-mode-title">
                    <h2>{title}</h2>
                    {step.groupTitle && (
                        <span className="cjc-cook-mode-section">{step.groupTitle}</span>
                    )}
                </div>
                <button
                    type="button"
                    className="cjc-cook-mode-close"
                    onClick={onClose}
                    aria-label="Exit cook mode"
                >
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </header>

            {/* Progress bar */}
            <div className="cjc-cook-mode-progress">
                <div
                    className="cjc-cook-mode-progress-bar"
                    style={{ width: `${progress}%` }}
                    role="progressbar"
                    aria-valuenow={currentStep + 1}
                    aria-valuemin="1"
                    aria-valuemax={totalSteps}
                />
            </div>

            {/* Main content */}
            <main className="cjc-cook-mode-content">
                <div className="cjc-cook-mode-step-indicator">
                    Step {currentStep + 1} of {totalSteps}
                </div>

                <div
                    className="cjc-cook-mode-step-text"
                    dangerouslySetInnerHTML={{ __html: step.text }}
                />
            </main>

            {/* Navigation */}
            <footer className="cjc-cook-mode-nav">
                <button
                    type="button"
                    className="cjc-cook-mode-nav-btn cjc-cook-mode-prev"
                    onClick={() => setCurrentStep((prev) => Math.max(prev - 1, 0))}
                    disabled={currentStep === 0}
                    aria-label="Previous step"
                >
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                    </svg>
                    <span>Previous</span>
                </button>

                <div className="cjc-cook-mode-steps">
                    {allSteps.map((_, index) => (
                        <button
                            key={index}
                            type="button"
                            className={`cjc-cook-mode-step-dot ${index === currentStep ? 'is-current' : ''} ${index < currentStep ? 'is-completed' : ''}`}
                            onClick={() => setCurrentStep(index)}
                            aria-label={`Go to step ${index + 1}`}
                            aria-current={index === currentStep ? 'step' : undefined}
                        />
                    ))}
                </div>

                <button
                    type="button"
                    className="cjc-cook-mode-nav-btn cjc-cook-mode-next"
                    onClick={() => setCurrentStep((prev) => Math.min(prev + 1, totalSteps - 1))}
                    disabled={currentStep === totalSteps - 1}
                    aria-label="Next step"
                >
                    <span>Next</span>
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                    </svg>
                </button>
            </footer>

            {/* Keyboard hints */}
            <div className="cjc-cook-mode-hints">
                <span><kbd>&larr;</kbd> Previous</span>
                <span><kbd>&rarr;</kbd> / <kbd>Space</kbd> Next</span>
                <span><kbd>Esc</kbd> Exit</span>
            </div>
        </div>
    );
}
