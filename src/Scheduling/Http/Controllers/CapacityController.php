<?php

namespace FlipperBox\Scheduling\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Scheduling\Models\DailyCapacity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class CapacityController extends Controller
{
    public function index(Request $request): Response
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        // Obtener capacidades para el mes especÃ­fico
        $capacities = DailyCapacity::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get()
            ->map(function ($capacity) {
                return [
                    'id' => $capacity->id,
                    'date' => $capacity->date->format('Y-m-d'),
                    'total_slots' => $capacity->total_slots,
                    'booked_slots' => $capacity->booked_slots,
                    'created_at' => $capacity->created_at,
                    'updated_at' => $capacity->updated_at,
                ];
            });

        return Inertia::render('Scheduling/Admin/CapacityIndex', [
            'capacities' => $capacities,
            'currentYear' => (int) $year,
            'currentMonth' => (int) $month,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'total_slots' => ['required', 'integer', 'min:1'],
        ]);

        DailyCapacity::updateOrCreate(
            ['date' => $validated['date']],
            ['total_slots' => $validated['total_slots']]
        );

        $date = Carbon::parse($validated['date']);

        return redirect()->route('admin.scheduling.capacities.index', [
            'year' => $date->year,
            'month' => $date->month,
        ])->with('success', 'Capacidad guardada correctamente.');
    }
}
