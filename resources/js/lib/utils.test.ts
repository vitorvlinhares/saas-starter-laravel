import { describe, expect, it } from 'vitest';
import { cn } from './utils';

describe('cn', () => {
    it('joins truthy class names and drops falsy ones', () => {
        const disabled: boolean = false;

        expect(cn('a', 'b', disabled && 'c', undefined, 'd')).toBe('a b d');
    });

    it('lets a later Tailwind class win over a conflicting earlier one', () => {
        expect(cn('px-2 py-1', 'px-4')).toBe('py-1 px-4');
    });

    it('merges conditional class objects', () => {
        expect(cn('base', { hidden: true, block: false })).toBe('base hidden');
    });
});
