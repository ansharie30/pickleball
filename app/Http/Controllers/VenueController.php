<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VenueController extends Controller
{
    public function index()
    {
        $venues = Venue::withCount('courts')->get();

        return Inertia::render('Venues/Index', [
            'venues' => $venues,
        ]);
    }

    public function create()
    {
        return Inertia::render('Venues/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $validated['owner_id'] = $request->user()->id;

        Venue::create($validated);

        return redirect()->route('venues.index');
    }
}