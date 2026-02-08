/**
 * useLocalStorage Hook
 *
 * Persist state to localStorage with automatic JSON serialization.
 */

import { useState, useEffect, useCallback } from 'react';

/**
 * Hook for persisting state to localStorage.
 *
 * @param {string} key - The localStorage key.
 * @param {*} initialValue - The initial value if nothing is stored.
 * @returns {[*, function, function]} [value, setValue, removeValue]
 */
export default function useLocalStorage(key, initialValue) {
    // Get stored value or use initial value
    const [storedValue, setStoredValue] = useState(() => {
        if (typeof window === 'undefined') {
            return initialValue;
        }

        try {
            const item = window.localStorage.getItem(key);
            return item ? JSON.parse(item) : initialValue;
        } catch (error) {
            console.warn(`Error reading localStorage key "${key}":`, error);
            return initialValue;
        }
    });

    // Update localStorage when value changes
    useEffect(() => {
        if (typeof window === 'undefined') {
            return;
        }

        try {
            if (storedValue === undefined) {
                window.localStorage.removeItem(key);
            } else {
                window.localStorage.setItem(key, JSON.stringify(storedValue));
            }
        } catch (error) {
            console.warn(`Error setting localStorage key "${key}":`, error);
        }
    }, [key, storedValue]);

    // Setter function
    const setValue = useCallback((value) => {
        setStoredValue((prev) => {
            const newValue = value instanceof Function ? value(prev) : value;
            return newValue;
        });
    }, []);

    // Remove function
    const removeValue = useCallback(() => {
        setStoredValue(undefined);
        if (typeof window !== 'undefined') {
            window.localStorage.removeItem(key);
        }
    }, [key]);

    return [storedValue, setValue, removeValue];
}

/**
 * Hook specifically for recipe checkbox states.
 *
 * @param {number} recipeId - The recipe ID.
 * @returns {object} { checkedItems, toggleItem, clearAll, isChecked }
 */
export function useRecipeChecklist(recipeId) {
    const key = `cjc-recipe-checklist-${recipeId}`;
    const [checkedItems, setCheckedItems] = useLocalStorage(key, {});

    const toggleItem = useCallback((itemId) => {
        setCheckedItems((prev) => ({
            ...prev,
            [itemId]: !prev[itemId],
        }));
    }, [setCheckedItems]);

    const clearAll = useCallback(() => {
        setCheckedItems({});
    }, [setCheckedItems]);

    const isChecked = useCallback((itemId) => {
        return !!checkedItems[itemId];
    }, [checkedItems]);

    return {
        checkedItems,
        toggleItem,
        clearAll,
        isChecked,
    };
}
