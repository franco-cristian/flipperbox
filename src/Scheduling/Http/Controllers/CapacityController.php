<?php

namespace FlipperBox\Scheduling\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Scheduling\Models\DailyCapacity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CapacityController extends Controller
{
    public function index(Request $request): Response
    {
        $capacities = DailyCapacity::whereYear('date', $request->input('year', now()->year))
            ->whereMonth('date', $request->input('month', now()->month))
            ->orderBy('date')
            ->get();

        return Inertia::render('Scheduling/Admin/CapacityIndex', [
            'capacities' => $capacities,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'total_slots' => ['required', 'integer', 'min:0'],
        ]);

        DailyCapacity::updateOrCreate(
            ['date' => $validated['date']],
            ['total_slots' => $validated['total_slots']]
        );

        return back()->with('success', 'Capacidad del día guardada exitosamente.');
    }
}