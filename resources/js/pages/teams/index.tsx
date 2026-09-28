import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Teams',
        href: '/teams',
    },
];

interface Team {
    id: number;
    name: string;
    personal_team: boolean;
    role: string;
    members_count: number;
    is_current: boolean;
}

interface TeamsIndexProps {
    teams: Team[];
}

export default function TeamsIndex({ teams }: TeamsIndexProps) {
    const [open, setOpen] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({ name: '' });

    const createTeam: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('teams.store'), {
            onSuccess: () => {
                reset();
                setOpen(false);
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Teams" />

            <div className="flex flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <HeadingSmall title="Teams" description="Teams you belong to" />

                    <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild>
                            <Button>New team</Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogTitle>Create a team</DialogTitle>
                            <form onSubmit={createTeam} className="space-y-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} autoFocus />
                                    <InputError message={errors.name} />
                                </div>
                                <DialogFooter>
                                    <Button type="submit" disabled={processing}>
                                        Create
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <div className="divide-y rounded-lg border">
                    {teams.map((team) => (
                        <Link
                            key={team.id}
                            href={route('teams.show', team.id)}
                            className="hover:bg-accent flex items-center justify-between gap-4 p-4"
                        >
                            <div className="flex items-center gap-2">
                                <span className="font-medium">{team.name}</span>
                                {team.is_current && <Badge variant="secondary">Current</Badge>}
                                {team.personal_team && <Badge variant="outline">Personal</Badge>}
                            </div>
                            <div className="text-muted-foreground flex items-center gap-4 text-sm">
                                <span>{team.role}</span>
                                <span>
                                    {team.members_count} member{team.members_count === 1 ? '' : 's'}
                                </span>
                            </div>
                        </Link>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
