import { SVGAttributes } from 'react';

interface AppLogoIconProps extends SVGAttributes<SVGElement> {
    /**
     * Optical adjustment for tiny sizes (sidebar, favicon-like spots):
     * thickens the hairline strokes so the mark stays legible below ~40px wide.
     */
    compact?: boolean;
    /** Heavier optical adjustment, for a more prominent mark. */
    strong?: boolean;
}

const LETTERS =
    'M24.6875 80.0V42.5H29.375V76.09375H59.0625V80.0ZM86.5 80.0 66.28515625 42.5H71.65625L89.625 76.6796875L107.59375 42.5H112.8916015625L92.75 80.0Z';

export default function AppLogoIcon({ compact = false, strong = false, ...props }: AppLogoIconProps) {
    // Half of the extra stroke, so bars grow by the same amount as the letters.
    const grow = strong ? 2.25 : compact ? 1.5 : 0;
    const viewBox = `${22.7 - grow} ${40.5 - grow} ${114.9 + grow * 2} ${41.5 + grow * 2}`;

    return (
        <svg {...props} viewBox={viewBox} xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path
                fill="currentColor"
                stroke={grow ? 'currentColor' : undefined}
                strokeWidth={grow ? grow * 2 : undefined}
                strokeLinejoin="miter"
                d={LETTERS}
            />
            <rect fill="var(--primary)" x={120.39 - grow} y={42.5 - grow} width={4.69 + grow * 2} height={17.25 + grow * 2} />
            <rect fill="var(--primary)" x={130.94 - grow} y={42.5 - grow} width={4.69 + grow * 2} height={17.25 + grow * 2} />
        </svg>
    );
}
