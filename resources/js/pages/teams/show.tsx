import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface TeamShowProps {
    team: { id: number; name: string; personal_team: boolean; owner_id: number };
    members: { id: number; name: string; email: string; role: string }[];
    invitations: { id: number; email: string; role: string; expired: boolean }[];
    roles: { value: string; label: string }[];
    permissions: { update: boolean; manageMembers: boolean; delete: boolean };
    plan: { key: string; name: string };
    currentUserId: number;
}

export default function TeamShow({ team, members, invitations, roles, permissions, currentUserId }: TeamShowProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Teams', href: '/teams' },
        { title: team.name, href: `/teams/${team.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={team.name} />

            <div className="flex flex-1 flex-col gap-6 rounded-xl p-4">
                {permissions.update && <RenameTeamForm team={team} />}

                <MembersSection team={team} members={members} roles={roles} permissions={permissions} currentUserId={currentUserId} />

                {permissions.manageMembers && <InviteSection team={team} invitations={invitations} roles={roles} />}

                {permissions.delete && <DangerZone team={team} />}
            </div>
        </AppLayout>
    );
}

function RenameTeamForm({ team }: { team: TeamShowProps['team'] }) {
    const { data, setData, patch, processing, errors } = useForm({ name: team.name });

    const rename: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route('teams.update', team.id), { preserveScroll: true });
    };

    return (
        <div className="space-y-4 rounded-lg border p-4">
            <HeadingSmall title="Team name" />
            <form onSubmit={rename} className="flex items-end gap-4">
                <div className="grid flex-1 gap-2">
                    <Label htmlFor="team-name">Name</Label>
                    <Input id="team-name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                    <InputError message={errors.name} />
                </div>
                <Button type="submit" disabled={processing}>
                    Save
                </Button>
            </form>
        </div>
    );
}

function MembersSection({
    team,
    members,
    roles,
    permissions,
    currentUserId,
}: {
    team: TeamShowProps['team'];
    members: TeamShowProps['members'];
    roles: TeamShowProps['roles'];
    permissions: TeamShowProps['permissions'];
    currentUserId: number;
}) {
    return (
        <div className="space-y-4 rounded-lg border p-4">
            <HeadingSmall title="Members" description={`${members.length} people on this team`} />
            <div className="divide-y">
                {members.map((member) => (
                    <div key={member.id} className="flex items-center justify-between gap-4 py-3">
                        <div>
                            <p className="font-medium">{member.name}</p>
                            <p className="text-muted-foreground text-sm">{member.email}</p>
                        </div>
                        <MemberActions team={team} member={member} roles={roles} permissions={permissions} currentUserId={currentUserId} />
                    </div>
                ))}
            </div>
        </div>
    );
}

function MemberActions({
    team,
    member,
    roles,
    permissions,
    currentUserId,
}: {
    team: TeamShowProps['team'];
    member: TeamShowProps['members'][number];
    roles: TeamShowProps['roles'];
    permissions: TeamShowProps['permissions'];
    currentUserId: number;
}) {
    const roleForm = useForm({ role: member.role });
    const removeForm = useForm();

    const isOwner = member.id === team.owner_id;
    const isSelf = member.id === currentUserId;

    if (isOwner) {
        return <Badge variant="secondary">Owner</Badge>;
    }

    return (
        <div className="flex items-center gap-2">
            {permissions.manageMembers ? (
                <Select
                    value={roleForm.data.role}
                    onValueChange={(role) => {
                        roleForm.setData('role', role);
                        roleForm.patch(route('teams.members.update', [team.id, member.id]), { preserveScroll: true });
                    }}
                >
                    <SelectTrigger className="w-32">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {roles.map((role) => (
                            <SelectItem key={role.value} value={role.value}>
                                {role.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            ) : (
                <Badge variant="outline">{member.role}</Badge>
            )}

            {(permissions.manageMembers || isSelf) && (
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={removeForm.processing}
                    onClick={() => removeForm.delete(route('teams.members.destroy', [team.id, member.id]))}
                >
                    {isSelf ? 'Leave' : 'Remove'}
                </Button>
            )}
        </div>
    );
}

function InviteSection({
    team,
    invitations,
    roles,
}: {
    team: TeamShowProps['team'];
    invitations: TeamShowProps['invitations'];
    roles: TeamShowProps['roles'];
}) {
    const { data, setData, post, processing, errors, reset } = useForm({ email: '', role: roles[0]?.value ?? 'member' });
    const cancelForm = useForm();

    const invite: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('teams.invitations.store', team.id), {
            preserveScroll: true,
            onSuccess: () => reset('email'),
        });
    };

    return (
        <div className="space-y-4 rounded-lg border p-4">
            <HeadingSmall title="Invite people" />

            <form onSubmit={invite} className="flex items-end gap-4">
                <div className="grid flex-1 gap-2">
                    <Label htmlFor="invite-email">Email</Label>
                    <Input
                        id="invite-email"
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        placeholder="person@example.com"
                    />
                    <InputError message={errors.email} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="invite-role">Role</Label>
                    <Select value={data.role} onValueChange={(role) => setData('role', role)}>
                        <SelectTrigger id="invite-role" className="w-32">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {roles.map((role) => (
                                <SelectItem key={role.value} value={role.value}>
                                    {role.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <Button type="submit" disabled={processing}>
                    Invite
                </Button>
            </form>

            {invitations.length > 0 && (
                <div className="divide-y border-t pt-2">
                    {invitations.map((invitation) => (
                        <div key={invitation.id} className="flex items-center justify-between gap-4 py-2">
                            <div className="flex items-center gap-2">
                                <span>{invitation.email}</span>
                                <Badge variant="outline">{invitation.role}</Badge>
                                {invitation.expired && <Badge variant="destructive">Expired</Badge>}
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                disabled={cancelForm.processing}
                                onClick={() => cancelForm.delete(route('teams.invitations.destroy', [team.id, invitation.id]))}
                            >
                                Cancel
                            </Button>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

function DangerZone({ team }: { team: TeamShowProps['team'] }) {
    const { data, setData, delete: destroy, processing, errors, reset } = useForm({ name: '' });

    const deleteTeam: FormEventHandler = (e) => {
        e.preventDefault();
        destroy(route('teams.destroy', team.id));
    };

    if (team.personal_team) {
        return null;
    }

    return (
        <div className="border-destructive/50 space-y-4 rounded-lg border p-4">
            <HeadingSmall title="Danger zone" description="Deleting a team is permanent and cannot be undone." />

            <Dialog onOpenChange={() => reset()}>
                <DialogTrigger asChild>
                    <Button variant="destructive">Delete team</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogTitle>Delete {team.name}?</DialogTitle>
                    <DialogDescription>Type the team name to confirm. This will cancel any active subscription.</DialogDescription>
                    <form onSubmit={deleteTeam} className="space-y-4">
                        <div className="grid gap-2">
                            <Label htmlFor="confirm-name" className="sr-only">
                                Team name
                            </Label>
                            <Input id="confirm-name" value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder={team.name} />
                            <InputError message={errors.name} />
                        </div>
                        <DialogFooter>
                            <DialogClose asChild>
                                <Button type="button" variant="secondary">
                                    Cancel
                                </Button>
                            </DialogClose>
                            <Button type="submit" variant="destructive" disabled={processing}>
                                Delete team
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    );
}
