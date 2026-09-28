import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <AppLogoIcon className="text-foreground h-5 w-auto" />
            <div className="ml-2 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate font-[Michroma] leading-none font-medium">SaaS Starter</span>
            </div>
        </>
    );
}
