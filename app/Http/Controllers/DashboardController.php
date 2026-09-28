<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Tenancy\TenantContext;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(TenantContext $tenant): Response
    {
        $team = $tenant->team();
        $plan = $team->plan();

        return Inertia::render('dashboard', [
            'stats' => [
                'members' => ['count' => $team->users()->count(), 'limit' => $plan->limit('members')],
                'projects' => ['count' => Project::count(), 'limit' => $plan->limit('projects')],
                'plan' => $plan->name,
            ],
            'recentProjects' => Project::latest()->limit(5)->get(['id', 'name', 'created_at']),
        ]);
    }
}
