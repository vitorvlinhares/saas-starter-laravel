import AppLogoIcon from './app-logo-icon';

/**
 * Sidebar brand block.
 * Collapsed: the mark alone, filling the 32px icon column.
 * Expanded: a larger, heavier mark + product name.
 */
export default function AppLogo() {
    return (
        <>
            <div className="text-sidebar-foreground flex h-8 shrink-0 items-center justify-center">
                <AppLogoIcon strong className="h-auto w-11 group-data-[collapsible=icon]:hidden" />
                <AppLogoIcon compact className="hidden h-auto w-8 group-data-[collapsible=icon]:block" />
            </div>
            <span className="ml-1.5 truncate text-sm font-semibold group-data-[collapsible=icon]:hidden">SaaS Starter</span>
        </>
    );
}
