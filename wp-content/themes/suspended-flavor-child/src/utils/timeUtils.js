/**
 * Time Utilities
 *
 * Utilities for parsing and formatting cooking times.
 */

/**
 * Parse a time string to total minutes.
 *
 * Handles formats like:
 * - "30 minutes" -> 30
 * - "1 hour" -> 60
 * - "1 hour 30 minutes" -> 90
 * - "1h 30m" -> 90
 * - "PT1H30M" (ISO 8601) -> 90
 * - "90" -> 90 (assumes minutes)
 *
 * @param {string} str - The time string.
 * @returns {number} Total minutes.
 */
export function parseTimeToMinutes(str) {
    if (!str || typeof str !== 'string') {
        return 0;
    }

    str = str.trim().toLowerCase();

    // Handle ISO 8601 duration (PT1H30M)
    if (str.startsWith('pt')) {
        let minutes = 0;
        const hoursMatch = str.match(/(\d+)h/);
        const minsMatch = str.match(/(\d+)m/);
        if (hoursMatch) {
            minutes += parseInt(hoursMatch[1], 10) * 60;
        }
        if (minsMatch) {
            minutes += parseInt(minsMatch[1], 10);
        }
        return minutes;
    }

    let totalMinutes = 0;

    // Match hours
    const hoursMatch = str.match(/(\d+)\s*(?:hour|hr|h)\s*/i);
    if (hoursMatch) {
        totalMinutes += parseInt(hoursMatch[1], 10) * 60;
    }

    // Match minutes
    const minsMatch = str.match(/(\d+)\s*(?:minute|min|m)\s*/i);
    if (minsMatch) {
        totalMinutes += parseInt(minsMatch[1], 10);
    }

    // If no matches but is a plain number, assume minutes
    if (totalMinutes === 0) {
        const num = parseInt(str, 10);
        if (!isNaN(num)) {
            return num;
        }
    }

    return totalMinutes;
}

/**
 * Format minutes to a human-readable time string.
 *
 * @param {number} minutes - Total minutes.
 * @param {object} options - Formatting options.
 * @param {string} options.format - 'long' (1 hour 30 minutes) or 'short' (1h 30m).
 * @returns {string} Formatted time string.
 */
export function formatMinutesToTime(minutes, options = {}) {
    const { format = 'long' } = options;

    if (!minutes || minutes <= 0) {
        return '';
    }

    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;

    const parts = [];

    if (format === 'short') {
        if (hours > 0) {
            parts.push(`${hours}h`);
        }
        if (mins > 0) {
            parts.push(`${mins}m`);
        }
    } else {
        if (hours > 0) {
            parts.push(`${hours} ${hours === 1 ? 'hour' : 'hours'}`);
        }
        if (mins > 0) {
            parts.push(`${mins} ${mins === 1 ? 'minute' : 'minutes'}`);
        }
    }

    return parts.join(' ');
}

/**
 * Convert minutes to ISO 8601 duration format.
 *
 * @param {number} minutes - Total minutes.
 * @returns {string} ISO 8601 duration (e.g., "PT1H30M").
 */
export function minutesToISO8601(minutes) {
    if (!minutes || minutes <= 0) {
        return '';
    }

    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;

    let iso = 'PT';
    if (hours > 0) {
        iso += `${hours}H`;
    }
    if (mins > 0) {
        iso += `${mins}M`;
    }

    return iso;
}

/**
 * Parse an ISO 8601 duration to minutes.
 *
 * @param {string} iso - ISO 8601 duration string.
 * @returns {number} Total minutes.
 */
export function iso8601ToMinutes(iso) {
    return parseTimeToMinutes(iso);
}

/**
 * Calculate total time from prep and cook times.
 *
 * @param {string} prepTime - Prep time string.
 * @param {string} cookTime - Cook time string.
 * @returns {string} Total time as formatted string.
 */
export function calculateTotalTime(prepTime, cookTime) {
    const prepMinutes = parseTimeToMinutes(prepTime);
    const cookMinutes = parseTimeToMinutes(cookTime);
    const total = prepMinutes + cookMinutes;
    return formatMinutesToTime(total);
}

/**
 * Format time for display in recipe cards.
 *
 * @param {string} time - Time string (human-readable or ISO 8601).
 * @returns {string} Formatted display string.
 */
export function formatTimeForDisplay(time) {
    if (!time) {
        return '';
    }

    // If already in a nice format, return as-is
    if (/^\d+\s*(hour|minute|min|hr)/i.test(time)) {
        return time;
    }

    // Parse and reformat
    const minutes = parseTimeToMinutes(time);
    return formatMinutesToTime(minutes);
}

export default {
    parseTimeToMinutes,
    formatMinutesToTime,
    minutesToISO8601,
    iso8601ToMinutes,
    calculateTotalTime,
    formatTimeForDisplay,
};
