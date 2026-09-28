import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

const features = [
    {
        title: 'Multi-tenant by default',
        description: 'Teams are tenants, scoped with a fail-closed query scope. No team in context means no data leaks.',
    },
    {
        title: 'Stripe billing built in',
        description: 'Laravel Cashier, subscription plans and usage limits enforced per team, ready for test mode.',
    },
    {
        title: 'Inertia + React + shadcn/ui',
        description: 'Laravel 12 backend, a typed React frontend, and a component library that stays out of the way.',
    },
];

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="LV² SaaS Starter" />
            <div className="bg-background text-foreground min-h-screen">
                <header className="border-b">
                    <div className="mx-auto flex h-16 max-w-5xl items-center justify-between px-6">
                        <div className="flex items-center gap-2">
                            <AppLogoIcon className="text-foreground h-5 w-auto" />
                            <span className="font-[Michroma] text-sm">SaaS Starter</span>
                        </div>

                        <nav className="flex items-center gap-4">
                            {auth.user ? (
                                <Button asChild>
                                    <Link href={route('dashboard')}>Dashboard</Link>
                                </Button>
                            ) : (
                                <>
                                    <Link href={route('login')} className="text-muted-foreground hover:text-foreground text-sm">
                                        Log in
                                    </Link>
                                    <Button asChild>
                                        <Link href={route('register')}>Register</Link>
                                    </Button>
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                <main className="mx-auto max-w-5xl px-6">
                    <div className="border-b py-24">
                        <p className="text-primary font-mono text-sm">LV² Tech &amp; Strategy</p>
                        <h1 className="mt-4 max-w-2xl font-[Michroma] text-3xl leading-tight md:text-4xl">
                            A multi-tenant SaaS starter, built to be read.
                        </h1>
                        <p className="text-muted-foreground mt-6 max-w-xl">
                            Teams, roles, invitations and Stripe billing, wired end to end on Laravel 12 and Inertia. Open source, meant as a
                            portfolio piece and a real starting point.
                        </p>
                        <div className="mt-8 flex gap-4">
                            {!auth.user && (
                                <Button asChild size="lg">
                                    <Link href={route('register')}>Get started</Link>
                                </Button>
                            )}
                            <Button asChild variant="outline" size="lg">
                                <a href="https://github.com/vitorvlinhares/saas-starter-laravel" target="_blank" rel="noopener noreferrer">
                                    View on GitHub
                                </a>
                            </Button>
                        </div>
                    </div>

                    <div className="grid gap-px border-b sm:grid-cols-3">
                        {features.map((feature) => (
                            <div key={feature.title} className="border-l py-8 pr-6 first:border-l-0 sm:py-12">
                                <h2 className="font-medium">{feature.title}</h2>
                                <p className="text-muted-foreground mt-2 text-sm">{feature.description}</p>
                            </div>
                        ))}
                    </div>
                </main>

                <footer className="text-muted-foreground mx-auto max-w-5xl px-6 py-8 text-sm">
                    <a href="https://github.com/vitorvlinhares/saas-starter-laravel" className="hover:text-foreground">
                        github.com/vitorvlinhares/saas-starter-laravel
                    </a>
                </footer>
            </div>
        </>
    );
}
