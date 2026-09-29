import { renderHook } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import { useInitials } from './use-initials';

describe('useInitials', () => {
    it('takes the first and last name initials for a full name', () => {
        const { result } = renderHook(() => useInitials());

        expect(result.current('Vitor Linhares')).toBe('VL');
    });

    it('uses the middle names too when picking first and last', () => {
        const { result } = renderHook(() => useInitials());

        expect(result.current('Ada Augusta King Lovelace')).toBe('AL');
    });

    it('falls back to a single initial for a one-word name', () => {
        const { result } = renderHook(() => useInitials());

        expect(result.current('Madonna')).toBe('M');
    });

    it('returns an empty string for an empty name', () => {
        const { result } = renderHook(() => useInitials());

        expect(result.current('')).toBe('');
    });
});
