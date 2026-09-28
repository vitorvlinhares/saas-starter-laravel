import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface Stat {
    count: number;
    limit: number | null;
}

interface RecentProject {
    id: number;
    name: string;
    created_at: string;
}

interface DashboardProps {
    stats: {
        members: Stat;
        projects: Stat;
        plan: string;
    };
    recentProjects: RecentProject[];
}

function StatCard({ title, stat }: { title: string; stat: Stat }) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-muted-foreground text-sm font-medium">{title}</CardTitle>
            </CardHeader>
            <CardContent>
                <p className="font-[Michroma] text-3xl">
                    {stat.count}
                    {stat.limit !== null && <span className="text-muted-foreground text-lg"> / {stat.limit}</span>}
                </p>
            </CardContent>
        </Card>
    );
}

export default function Dashboard({ stats, recentProjects }: DashboardProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <StatCard title="Members" stat={stats.members} />
                    <StatCard title="Projects" stat={stats.projects} />
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-muted-foreground text-sm font-medium">Plan</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="font-[Michroma] text-3xl">{stats.plan}</p>
                        </CardContent>
                    </Card>
                </div>

                <Card className="flex-1">
                    <CardHeader>
                        <CardTitle>Recent projects</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {recentProjects.length === 0 ? (
                            <p className="text-muted-foreground text-sm">
                                No projects yet.{' '}
                                <Link href={route('projects.index')} className="text-primary underline underline-offset-4">
                                    Create one
                                </Link>
                                .
                            </p>
                        ) : (
                            <ul className="divide-y">
                                {recentProjects.map((project) => (
                                    <li key={project.id} className="flex items-center justify-between py-2">
                                        <span>{project.name}</span>
                                        <span className="text-muted-foreground text-sm">{new Date(project.created_at).toLocaleDateString()}</span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
