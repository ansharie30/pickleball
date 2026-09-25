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

Route::middleware(['auth'])->group(function () {
    Route::resource('tournaments', TournamentController::class);
    Route::post('/divisions/{division}/generate-matches', [TournamentController::class, 'generateMatches'])
    ->name('divisions.generate-matches');
    
    Route::get('/divisions/{division}/standings', [TournamentController::class, 'standings'])
    ->name('divisions.standings');
    Route::get('/watch/{match}', [MatchController::class, 'publicShow'])->name('matches.public');
    Route::resource('courts', CourtController::class)->only(['index', 'create', 'store']);
    Route::patch('/courts/{court}/status', [CourtController::class, 'updateStatus'])->name('courts.status');
    Route::patch('/matches/{match}/court', [MatchController::class, 'assignCourt'])->name('matches.assign-court');
    Route::get('/divisions/{division}/teams/create', [TeamController::class, 'create'])->name('teams.create');
    Route::post('/divisions/{division}/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::get('/tournaments/{tournament}/divisions/create', [TournamentController::class, 'createDivision'])->name('divisions.create');
    Route::post('/tournaments/{tournament}/divisions', [TournamentController::class, 'storeDivision'])->name('divisions.store');
    Route::get('/divisions/{division}/bracket', [TournamentController::class, 'bracket'])->name('divisions.bracket');
});

Route::get('/courts/{court}/quick-match', [QuickMatchController::class, 'create'])->name('quick-match.create');
Route::post('/courts/{court}/quick-match', [QuickMatchController::class, 'store'])->name('quick-match.store');
Route::get('/matches/{match}', [MatchController::class, 'show'])->name('matches.show');
Route::post('/matches/{match}/score', [MatchController::class, 'updateScore'])->name('matches.score');
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
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
