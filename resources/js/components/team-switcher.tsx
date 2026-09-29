import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { useInitials } from '@/hooks/use-initials';
import { useIsMobile } from '@/hooks/use-mobile';
import { type SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { Check, ChevronsUpDown, Plus } from 'lucide-react';

export function TeamSwitcher() {
    const { currentTeam, teams } = usePage<SharedData>().props;
    const { state } = useSidebar();
    const isMobile = useIsMobile();
    const getInitials = useInitials();

    if (!currentTeam) {
        return null;
    }

    const switchTeam = (teamId: number) => {
        if (teamId === currentTeam.id) {
            return;
        }

        router.put(route('current-team.update'), { team_id: teamId });
    };

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <Tooltip>
                    <DropdownMenu>
                        <TooltipTrigger asChild>
                            <DropdownMenuTrigger asChild>
                                <SidebarMenuButton size="lg" className="text-sidebar-accent-foreground data-[state=open]:bg-sidebar-accent">
                                    <div className="bg-sidebar-accent flex size-8 shrink-0 items-center justify-center rounded-sm font-mono text-xs font-semibold">
                                        {getInitials(currentTeam.name)}
                                    </div>
                                    <div className="grid flex-1 text-left text-sm leading-tight group-data-[collapsible=icon]:hidden">
                                        <span className="truncate font-medium">{currentTeam.name}</span>
                                        <span className="text-muted-foreground truncate text-xs">{currentTeam.plan} plan</span>
                                    </div>
                                    <ChevronsUpDown className="ml-auto size-4 group-data-[collapsible=icon]:hidden" />
                                </SidebarMenuButton>
                            </DropdownMenuTrigger>
                        </TooltipTrigger>
                        <DropdownMenuContent
                            className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                            align="start"
                            side={isMobile ? 'bottom' : state === 'collapsed' ? 'left' : 'bottom'}
                        >
                            {teams.map((team) => (
                                <DropdownMenuItem key={team.id} onClick={() => switchTeam(team.id)} className="justify-between">
                                    <span className="truncate">{team.name}</span>
                                    {team.id === currentTeam.id && <Check className="size-4" />}
                                </DropdownMenuItem>
                            ))}
                            <DropdownMenuSeparator />
                            <DropdownMenuItem asChild>
                                <Link href={route('teams.index')} className="flex items-center gap-2">
                                    <Plus className="size-4" />
                                    Manage teams
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <TooltipContent side="right" align="center" hidden={state !== 'collapsed' || isMobile}>
                        {currentTeam.name}
                    </TooltipContent>
                </Tooltip>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
