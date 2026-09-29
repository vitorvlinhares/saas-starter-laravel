import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import InputError from './input-error';

describe('InputError', () => {
    it('renders the message when one is given', () => {
        render(<InputError message="This field is required." />);

        expect(screen.getByText('This field is required.')).toBeInTheDocument();
    });

    it('renders nothing when there is no message', () => {
        const { container } = render(<InputError />);

        expect(container).toBeEmptyDOMElement();
    });
});
