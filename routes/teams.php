<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Teams\CurrentTeamController;
use App\Http\Controllers\Teams\TeamController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Controllers\Teams\TeamMemberController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'team'])->group(function () {
    // Teams
    Route::get('teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('teams', [TeamController::class, 'store'])->name('teams.store');
    Route::get('teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::patch('teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

    Route::put('current-team', [CurrentTeamController::class, 'update'])->name('current-team.update');

    // Members
    Route::patch('teams/{team}/members/{user}', [TeamMemberController::class, 'update'])->name('teams.members.update');
    Route::delete('teams/{team}/members/{user}', [TeamMemberController::class, 'destroy'])->name('teams.members.destroy');

    // Invitations
    Route::post('teams/{team}/invitations', [TeamInvitationController::class, 'store'])->name('teams.invitations.store');
    Route::delete('teams/{team}/invitations/{invitation}', [TeamInvitationController::class, 'destroy'])
        ->scopeBindings()
        ->name('teams.invitations.destroy');

    // Tenant-scoped example resource
    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Billing (Stripe via Laravel Cashier)
    Route::get('billing', [BillingController::class, 'show'])->name('billing');
    Route::post('billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
});

// Accepting an invitation only needs a signed-in user; guests are sent to
// login/register and come back here afterwards (intended URL).
Route::get('invitations/{token}', [TeamInvitationController::class, 'accept'])
    ->middleware('auth')
    ->name('invitations.accept');
