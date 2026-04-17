import { describe, it, expect } from 'vitest';
import { formatDate, truncate } from '../utils/format';

describe('truncate', () => {
    it('returns the string unchanged when shorter than limit', () => {
        expect(truncate('hello', 10)).toBe('hello');
    });

    it('truncates and appends ellipsis when over limit', () => {
        expect(truncate('hello world', 5)).toBe('hello…');
    });

    it('returns — for null/undefined', () => {
        expect(truncate(null, 5)).toBe('—');
        expect(truncate(undefined, 5)).toBe('—');
    });

    it('returns — for empty string', () => {
        expect(truncate('', 5)).toBe('—');
    });
});

describe('formatDate', () => {
    it('returns — for falsy values', () => {
        expect(formatDate(null)).toBe('—');
        expect(formatDate('')).toBe('—');
        expect(formatDate(undefined)).toBe('—');
    });

    it('formats a valid date string', () => {
        const result = formatDate('2024-01-15T00:00:00.000Z');
        expect(result).toMatch(/\d{2}\s\w+\s\d{4}/);
    });
});
