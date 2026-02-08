/**
 * Fraction Utilities
 *
 * Utilities for handling recipe quantities and fractions.
 */

// Common fractions and their decimal equivalents
const FRACTIONS = {
    '1/8': 0.125,
    '1/4': 0.25,
    '1/3': 0.333,
    '3/8': 0.375,
    '1/2': 0.5,
    '5/8': 0.625,
    '2/3': 0.667,
    '3/4': 0.75,
    '7/8': 0.875,
};

// Unicode fraction characters
const UNICODE_FRACTIONS = {
    '\u00BC': 0.25,  // ¼
    '\u00BD': 0.5,   // ½
    '\u00BE': 0.75,  // ¾
    '\u2153': 0.333, // ⅓
    '\u2154': 0.667, // ⅔
    '\u215B': 0.125, // ⅛
    '\u215C': 0.375, // ⅜
    '\u215D': 0.625, // ⅝
    '\u215E': 0.875, // ⅞
};

// Reverse mapping for decimal to fraction
const DECIMAL_TO_FRACTION = {
    0.125: '1/8',
    0.25: '1/4',
    0.333: '1/3',
    0.375: '3/8',
    0.5: '1/2',
    0.625: '5/8',
    0.667: '2/3',
    0.75: '3/4',
    0.875: '7/8',
};

/**
 * Parse a quantity string to a decimal number.
 *
 * Handles formats like:
 * - "2" -> 2
 * - "1/2" -> 0.5
 * - "1 1/2" -> 1.5
 * - "2-3" -> 2.5 (average)
 * - "½" -> 0.5 (Unicode)
 *
 * @param {string} str - The quantity string.
 * @returns {number} The parsed decimal value.
 */
export function parseQuantity(str) {
    if (!str || typeof str !== 'string') {
        return 0;
    }

    str = str.trim();

    // Handle empty or non-numeric
    if (!str) {
        return 0;
    }

    // Handle range (e.g., "2-3" -> average)
    if (str.includes('-')) {
        const parts = str.split('-').map((s) => parseQuantity(s.trim()));
        if (parts.length === 2 && parts[0] > 0 && parts[1] > 0) {
            return (parts[0] + parts[1]) / 2;
        }
    }

    // Handle Unicode fractions
    for (const [char, value] of Object.entries(UNICODE_FRACTIONS)) {
        if (str.includes(char)) {
            const whole = str.replace(char, '').trim();
            return (whole ? parseFloat(whole) : 0) + value;
        }
    }

    // Handle "1 1/2" format (whole number + fraction)
    const mixedMatch = str.match(/^(\d+)\s+(\d+)\/(\d+)$/);
    if (mixedMatch) {
        const whole = parseInt(mixedMatch[1], 10);
        const numerator = parseInt(mixedMatch[2], 10);
        const denominator = parseInt(mixedMatch[3], 10);
        return whole + (numerator / denominator);
    }

    // Handle simple fraction "1/2"
    const fractionMatch = str.match(/^(\d+)\/(\d+)$/);
    if (fractionMatch) {
        const numerator = parseInt(fractionMatch[1], 10);
        const denominator = parseInt(fractionMatch[2], 10);
        return numerator / denominator;
    }

    // Handle regular number
    const num = parseFloat(str);
    return isNaN(num) ? 0 : num;
}

/**
 * Format a decimal number as a fraction or mixed number.
 *
 * @param {number} value - The decimal value to format.
 * @param {object} options - Formatting options.
 * @param {boolean} options.useFractions - Whether to use fractions (default true).
 * @param {number} options.precision - Decimal precision for non-fraction display (default 2).
 * @returns {string} The formatted quantity string.
 */
export function formatQuantity(value, options = {}) {
    const { useFractions = true, precision = 2 } = options;

    if (value === 0) {
        return '';
    }

    if (!useFractions) {
        // Round to precision and remove trailing zeros
        return parseFloat(value.toFixed(precision)).toString();
    }

    const whole = Math.floor(value);
    const decimal = value - whole;

    // Find closest fraction
    let closestFraction = '';
    let closestDiff = 1;

    for (const [frac, dec] of Object.entries(FRACTIONS)) {
        const diff = Math.abs(decimal - dec);
        if (diff < closestDiff && diff < 0.05) {
            closestDiff = diff;
            closestFraction = frac;
        }
    }

    // If very close to 0 or 1
    if (decimal < 0.05) {
        return whole > 0 ? whole.toString() : '';
    }
    if (decimal > 0.95) {
        return (whole + 1).toString();
    }

    // Build result
    if (closestFraction) {
        if (whole > 0) {
            return `${whole} ${closestFraction}`;
        }
        return closestFraction;
    }

    // No matching fraction, use decimal
    return parseFloat(value.toFixed(precision)).toString();
}

/**
 * Scale a quantity by a multiplier.
 *
 * @param {string} originalAmount - The original amount string.
 * @param {number} multiplier - The scaling multiplier.
 * @returns {string} The scaled amount as a formatted string.
 */
export function scaleQuantity(originalAmount, multiplier) {
    if (!originalAmount || multiplier === 1) {
        return originalAmount;
    }

    const value = parseQuantity(originalAmount);
    if (value === 0) {
        return originalAmount; // Non-numeric, return as-is
    }

    const scaled = value * multiplier;
    return formatQuantity(scaled);
}

/**
 * Check if a string contains a parseable quantity.
 *
 * @param {string} str - The string to check.
 * @returns {boolean} True if the string contains a quantity.
 */
export function hasQuantity(str) {
    if (!str || typeof str !== 'string') {
        return false;
    }

    // Check for numbers, fractions, or Unicode fraction characters
    return /[\d¼½¾⅓⅔⅛⅜⅝⅞]/.test(str);
}

export default {
    parseQuantity,
    formatQuantity,
    scaleQuantity,
    hasQuantity,
};
