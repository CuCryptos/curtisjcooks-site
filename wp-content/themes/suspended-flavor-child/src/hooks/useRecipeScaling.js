/**
 * useRecipeScaling Hook
 *
 * Handles recipe scaling calculations and state.
 */

import { useState, useCallback, useMemo } from 'react';
import { scaleQuantity, parseQuantity } from '../utils/fractionUtils';

/**
 * Hook for managing recipe scaling.
 *
 * @param {number} baseServings - The original number of servings.
 * @param {array} ingredients - The ingredients array from recipe data.
 * @returns {object} Scaling state and methods.
 */
export default function useRecipeScaling(baseServings = 4, ingredients = []) {
    const [currentServings, setCurrentServings] = useState(baseServings);

    // Calculate the multiplier
    const multiplier = useMemo(() => {
        if (!baseServings || baseServings === 0) {
            return 1;
        }
        return currentServings / baseServings;
    }, [currentServings, baseServings]);

    // Scale a single amount
    const scaleAmount = useCallback((amount) => {
        return scaleQuantity(amount, multiplier);
    }, [multiplier]);

    // Get scaled ingredients
    const scaledIngredients = useMemo(() => {
        if (multiplier === 1) {
            return ingredients;
        }

        return ingredients.map((group) => ({
            ...group,
            items: group.items?.map((item) => ({
                ...item,
                scaledAmount: scaleQuantity(item.amount, multiplier),
            })) || [],
        }));
    }, [ingredients, multiplier]);

    // Preset scaling options
    const presets = useMemo(() => {
        if (!baseServings) return [];

        return [
            { label: '0.5x', servings: Math.max(1, Math.round(baseServings * 0.5)) },
            { label: '1x', servings: baseServings },
            { label: '2x', servings: baseServings * 2 },
            { label: '3x', servings: baseServings * 3 },
        ];
    }, [baseServings]);

    // Set servings with bounds checking
    const setServings = useCallback((value) => {
        const num = parseInt(value, 10);
        if (isNaN(num) || num < 1) {
            setCurrentServings(1);
        } else if (num > 999) {
            setCurrentServings(999);
        } else {
            setCurrentServings(num);
        }
    }, []);

    // Increase servings
    const increaseServings = useCallback(() => {
        setCurrentServings((prev) => Math.min(prev + 1, 999));
    }, []);

    // Decrease servings
    const decreaseServings = useCallback(() => {
        setCurrentServings((prev) => Math.max(prev - 1, 1));
    }, []);

    // Reset to base
    const resetServings = useCallback(() => {
        setCurrentServings(baseServings);
    }, [baseServings]);

    // Check if scaled
    const isScaled = multiplier !== 1;

    return {
        currentServings,
        baseServings,
        multiplier,
        setServings,
        increaseServings,
        decreaseServings,
        resetServings,
        scaleAmount,
        scaledIngredients,
        presets,
        isScaled,
    };
}
