import HeadingSmall from '@/components/heading-small';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { TriangleAlert } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Billing',
        href: '/billing',
    },
];

interface Plan {
    key: string;
    name: string;
    monthly_price: number;
    limits: { members: number | null; projects: number | null };
    features: string[];
    is_free: boolean;
}

interface BillingIndexProps {
    team: { id: number; name: string };
    currentPlan: string;
    plans: Plan[];
    subscription: { status: string; on_grace_period: boolean; ends_at: string | null } | null;
    usage: { members: number; projects: number };
    currency: string;
    canManage: boolean;
    stripeConfigured: boolean;
}

function formatPrice(cents: number, currency: string): string {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(cents / 100);
}

export default function BillingIndex({ team, currentPlan, plans, subscription, usage, currency, canManage, stripeConfigured }: BillingIndexProps) {
    const [processingPlan, setProcessingPlan] = useState<string | null>(null);

    const subscribe = (plan: string) => {
        setProcessingPlan(plan);
        router.post(route('billing.checkout'), { plan }, { preserveScroll: true, onFinish: () => setProcessingPlan(null) });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Billing" />

            <div className="flex flex-1 flex-col gap-6 rounded-xl p-4">
                <HeadingSmall title="Billing" description={`Plan and usage for ${team.name}`} />

                {!stripeConfigured && (
                    <Alert>
                        <TriangleAlert />
                        <AlertTitle>Stripe is not configured</AlertTitle>
                        <AlertDescription>Set the STRIPE_* variables in .env to enable checkout and the billing portal.</AlertDescription>
                    </Alert>
                )}

                {subscription?.on_grace_period && (
                    <Alert variant="destructive">
                        <TriangleAlert />
                        <AlertTitle>Subscription ending</AlertTitle>
                        <AlertDescription>
                            Your subscription is cancelled and will end on{' '}
                            {subscription.ends_at && new Date(subscription.ends_at).toLocaleDateString()}.
                        </AlertDescription>
                    </Alert>
                )}

                <div className="text-muted-foreground flex gap-6 text-sm">
                    <span>
                        Members: <span className="text-foreground font-medium">{usage.members}</span>
                    </span>
                    <span>
                        Projects: <span className="text-foreground font-medium">{usage.projects}</span>
                    </span>
                </div>

                <div className="grid gap-4 sm:grid-cols-3">
                    {plans.map((plan) => {
                        const isCurrent = plan.key === currentPlan;

                        return (
                            <Card key={plan.key} className={isCurrent ? 'border-primary' : undefined}>
                                <CardHeader>
                                    <div className="flex items-center justify-between">
                                        <CardTitle>{plan.name}</CardTitle>
                                        {isCurrent && <Badge>Current plan</Badge>}
                                    </div>
                                    <p className="text-2xl font-semibold">
                                        {plan.is_free ? 'Free' : formatPrice(plan.monthly_price, currency)}
                                        {!plan.is_free && <span className="text-muted-foreground text-sm font-normal">/mo</span>}
                                    </p>
                                </CardHeader>
                                <CardContent>
                                    <ul className="text-muted-foreground space-y-1 text-sm">
                                        {plan.features.map((feature) => (
                                            <li key={feature}>{feature}</li>
                                        ))}
                                    </ul>
                                </CardContent>
                                {canManage && !plan.is_free && !isCurrent && (
                                    <CardFooter>
                                        <Button
                                            type="button"
                                            className="w-full"
                                            disabled={processingPlan !== null}
                                            onClick={() => subscribe(plan.key)}
                                        >
                                            {subscription ? 'Switch to this plan' : 'Subscribe'}
                                        </Button>
                                    </CardFooter>
                                )}
                            </Card>
                        );
                    })}
                </div>

                {canManage && subscription && (
                    <a href={route('billing.portal')} className="text-primary text-sm underline underline-offset-4">
                        Manage billing
                    </a>
                )}
            </div>
        </AppLayout>
    );
}
