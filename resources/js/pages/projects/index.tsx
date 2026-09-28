import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/projects',
    },
];

interface Project {
    id: number;
    name: string;
    description: string | null;
    creator: string | null;
    created_at: string;
}

interface ProjectsIndexProps {
    projects: Project[];
    limit: number | null;
    planName: string;
}

export default function ProjectsIndex({ projects, limit, planName }: ProjectsIndexProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        description: '',
    });

    const createProject: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('projects.store'), {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Projects" />

            <div className="flex flex-1 flex-col gap-6 rounded-xl p-4">
                <HeadingSmall title="Projects" description={`${projects.length}${limit !== null ? ` / ${limit}` : ''} on the ${planName} plan`} />

                <form onSubmit={createProject} className="grid gap-4 rounded-lg border p-4 sm:grid-cols-2">
                    <div className="grid gap-2">
                        <Label htmlFor="name">Name</Label>
                        <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder="Project name" />
                        <InputError message={errors.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="description">Description</Label>
                        <Input
                            id="description"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder="Optional description"
                        />
                        <InputError message={errors.description} />
                    </div>

                    <div className="sm:col-span-2">
                        <Button type="submit" disabled={processing}>
                            Create project
                        </Button>
                    </div>
                </form>

                <div className="divide-y rounded-lg border">
                    {projects.length === 0 && <p className="text-muted-foreground p-4 text-sm">No projects yet.</p>}

                    {projects.map((project) => (
                        <ProjectRow key={project.id} project={project} />
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}

function ProjectRow({ project }: { project: Project }) {
    const { delete: destroy, processing } = useForm();

    return (
        <div className="flex items-center justify-between gap-4 p-4">
            <div>
                <p className="font-medium">{project.name}</p>
                {project.description && <p className="text-muted-foreground text-sm">{project.description}</p>}
                <p className="text-muted-foreground text-xs">
                    Created by {project.creator ?? 'someone'} on {new Date(project.created_at).toLocaleDateString()}
                </p>
            </div>
            <Button
                type="button"
                variant="outline"
                size="sm"
                disabled={processing}
                onClick={() => destroy(route('projects.destroy', project.id), { preserveScroll: true })}
            >
                Delete
            </Button>
        </div>
    );
}
