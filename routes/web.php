<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TournamentController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\CourtController;
use App\Http\Controllers\QuickMatchController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\PlayerPortalController;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('tournaments', TournamentController::class);
    Route::post('/divisions/{division}/generate-matches', [TournamentController::class, 'generateMatches'])
    ->name('divisions.generate-matches');

    Route::get('/divisions/{division}/standings', [TournamentController::class, 'standings'])
    ->name('divisions.standings');
    Route::resource('courts', CourtController::class)->only(['index', 'create', 'store']);
    Route::patch('/courts/{court}/status', [CourtController::class, 'updateStatus'])->name('courts.status');
    Route::patch('/matches/{match}/court', [MatchController::class, 'assignCourt'])->name('matches.assign-court');
    Route::get('/divisions/{division}/teams/create', [TeamController::class, 'create'])->name('teams.create');
    Route::post('/divisions/{division}/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::get('/tournaments/{tournament}/divisions/create', [TournamentController::class, 'createDivision'])->name('divisions.create');
    Route::post('/tournaments/{tournament}/divisions', [TournamentController::class, 'storeDivision'])->name('divisions.store');
    Route::get('/divisions/{division}/bracket', [TournamentController::class, 'bracket'])->name('divisions.bracket');

    Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
    Route::get('/players/{player}', [PlayerController::class, 'show'])->name('players.show');

    Route::get('/matches/{match}/edit', [MatchController::class, 'edit'])->name('matches.edit');
    Route::patch('/matches/{match}', [MatchController::class, 'update'])->name('matches.update');
    Route::delete('/matches/{match}', [MatchController::class, 'destroy'])->name('matches.destroy');

    Route::resource('venues', VenueController::class)->only(['index', 'create', 'store']);

});

Route::get('/my-portal', [PlayerPortalController::class, 'index'])
    ->middleware('auth')
    ->name('player.portal');

Route::get('/courts/{court}/quick-match', [QuickMatchController::class, 'create'])->name('quick-match.create');
Route::post('/courts/{court}/quick-match', [QuickMatchController::class, 'store'])->name('quick-match.store');
Route::get('/matches/{match}', [MatchController::class, 'show'])->name('matches.show');
Route::post('/matches/{match}/score', [MatchController::class, 'updateScore'])->name('matches.score');
Route::get('/watch/{match}', [MatchController::class, 'publicShow'])->name('matches.public');
Route::get('/live-scores', [MatchController::class, 'publicIndex'])->name('matches.public-index');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::get('/dashboard', function () {
    if (!request()->user()->isAdmin()) {
        return redirect()->route('player.portal');
    }

    return Inertia::render('Dashboard', [
        'stats' => [
            'total_tournaments' => \App\Models\Tournament::count(),
            'total_divisions' => \App\Models\Division::count(),
            'total_teams' => \App\Models\TeamModel::count(),
            'total_courts' => \App\Models\Court::count(),
            'matches_in_progress' => \App\Models\MatchModel::where('status', 'in_progress')->count(),
            'matches_completed' => \App\Models\MatchModel::where('status', 'completed')->count(),
        ],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';