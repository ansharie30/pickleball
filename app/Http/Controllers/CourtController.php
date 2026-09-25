<?php

namespace App\Http\Controllers;

use App\Models\Court;
use App\Models\Venue;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourtController extends Controller
{
    public function index()
    {
        $courts = Court::with('venue')->get();

        return Inertia::render('Courts/Index', [
            'courts' => $courts,
            'venues' => Venue::all(['id', 'name']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Courts/Create', [
            'venues' => Venue::all(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'venue_id' => 'required|exists:venues,id',
            'name' => 'required|string|max:255',
        ]);

        $validated['qr_code_token'] = \Illuminate\Support\Str::random(16);
        $validated['status'] = 'available';

        Court::create($validated);

        return redirect()->route('courts.index');
    }

    public function updateStatus(Request $request, Court $court)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,in_use,reserved,maintenance',
        ]);

        $court->update($validated);

        return back();
    }
}