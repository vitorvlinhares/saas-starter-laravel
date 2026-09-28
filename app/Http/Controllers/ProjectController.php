<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Example of a tenant-scoped resource: every query below is automatically
 * limited to the current team by the BelongsToTeam trait.
 */
class ProjectController extends Controller
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function index(): Response
    {
        $plan = $this->tenant->team()->plan();

        return Inertia::render('projects/index', [
            'projects' => Project::with('creator:id,name')
                ->latest()
                ->get()
                ->map(fn (Project $project) => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'creator' => $project->creator?->name,
                    'created_at' => $project->created_at->toIso8601String(),
                ]),
            'limit' => $plan->limit('projects'),
            'planName' => $plan->name,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $plan = $this->tenant->team()->plan();

        if (! $plan->allows('projects', Project::count())) {
            throw ValidationException::withMessages([
                'name' => "The {$plan->name} plan allows up to {$plan->limit('projects')} projects. Upgrade to create more.",
            ]);
        }

        Project::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return back();
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return back();
    }
}
