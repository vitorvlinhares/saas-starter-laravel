import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/react';

interface ErrorPageProps {
    status: 403 | 404 | 500 | 503;
}

const messages: Record<ErrorPageProps['status'], { title: string; description: string }> = {
    403: {
        title: 'Forbidden',
        description: "You don't have permission to access this page.",
    },
    404: {
        title: 'Page not found',
        description: "The page you're looking for doesn't exist or was moved.",
    },
    500: {
        title: 'Server error',
        description: 'Something went wrong on our end. Try again in a moment.',
    },
    503: {
        title: 'Unavailable',
        description: "We're down for maintenance. Check back shortly.",
    },
};

export default function ErrorPage({ status }: ErrorPageProps) {
    const { title, description } = messages[status];

    return (
        <div className="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 text-center">
            <Head title={title} />

            <Link href="/" className="flex flex-col items-center gap-4">
                <AppLogoIcon strong className="text-foreground h-auto w-11" />
            </Link>

            <div className="space-y-2">
                <p className="font-[Michroma] text-5xl">{status}</p>
                <h1 className="text-xl font-medium">{title}</h1>
                <p className="text-muted-foreground max-w-sm text-sm">{description}</p>
            </div>

            <Button asChild>
                <Link href="/">Go back home</Link>
            </Button>
        </div>
    );
}
