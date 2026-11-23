import { afterAll, beforeAll, describe, expect, it, vi } from 'vitest';
import { fromLocalInput, toLocalInput } from './datetime';

// London is on BST (UTC+1) in summer and on GMT (UTC+0) in winter.
beforeAll(() => {
    vi.stubEnv('TZ', 'Europe/London');
});

afterAll(() => {
    vi.unstubAllEnvs();
});

describe('toLocalInput', () => {
    it('shows an instant as the wall-clock time in the browser timezone', () => {
        expect(toLocalInput('2027-06-15T08:00:00.000000Z')).toBe('2027-06-15T09:00');
        expect(toLocalInput('2027-08-31T22:59:00+00:00')).toBe('2027-08-31T23:59');
        expect(toLocalInput('2027-12-01T09:00:00+00:00')).toBe('2027-12-01T09:00');
    });

    it('formats a Date the same way', () => {
        expect(toLocalInput(new Date(Date.UTC(2027, 5, 15, 8, 0)))).toBe('2027-06-15T09:00');
    });

    it('is empty for a missing or unreadable value', () => {
        expect(toLocalInput(null)).toBe('');
        expect(toLocalInput(undefined)).toBe('');
        expect(toLocalInput('')).toBe('');
        expect(toLocalInput('not-a-date')).toBe('');
    });
});

describe('fromLocalInput', () => {
    it('reads the input as browser-local time and returns the UTC instant', () => {
        expect(fromLocalInput('2027-06-15T09:00')).toBe('2027-06-15T08:00:00.000Z');
        expect(fromLocalInput('2027-08-31T23:59')).toBe('2027-08-31T22:59:00.000Z');
        expect(fromLocalInput('2027-12-01T09:00')).toBe('2027-12-01T09:00:00.000Z');
    });

    it('round-trips with toLocalInput', () => {
        expect(toLocalInput(fromLocalInput('2027-06-15T09:00'))).toBe('2027-06-15T09:00');
    });

    it('is empty for a cleared or partial input', () => {
        expect(fromLocalInput('')).toBe('');
        expect(fromLocalInput('2027-06-15')).toBe('');
    });
});
