import { renderHook } from '@testing-library/react';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { useIsMobile } from './use-mobile';

function mockViewportWidth(width: number) {
    window.innerWidth = width;
    window.matchMedia = vi.fn().mockImplementation((query: string) => ({
        matches: false,
        media: query,
        addEventListener: vi.fn(),
        removeEventListener: vi.fn(),
    }));
}

describe('useIsMobile', () => {
    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('is true under the mobile breakpoint', () => {
        mockViewportWidth(500);

        const { result } = renderHook(() => useIsMobile());

        expect(result.current).toBe(true);
    });

    it('is false at or above the mobile breakpoint', () => {
        mockViewportWidth(1024);

        const { result } = renderHook(() => useIsMobile());

        expect(result.current).toBe(false);
    });
});
