<?php

namespace FlipperBox\ClientScheduling\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Crm\Models\Vehiculo;
use FlipperBox\Scheduling\Models\DailyCapacity;
use FlipperBox\Scheduling\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('vehicles');

        if ($user->vehicles->isEmpty()) {
            return Inertia::render('ClientScheduling/BookingIndex', [
                'vehicles' => [],
                'capacities' => [],
            ]);
        }

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $capacities = DailyCapacity::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('date', '>=', today())
            ->orderBy('date')
            ->get()
            ->map(fn ($capacity) => [
                'date' => $capacity->date->format('Y-m-d'),
                'available_slots' => $capacity->total_slots - $capacity->booked_slots,
            ]);

        return Inertia::render('ClientScheduling/BookingIndex', [
            'vehicles' => $user->vehicles,
            'capacities' => $capacities,
            'currentYear' => (int)$year,
            'currentMonth' => (int)$month,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehiculos,id'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->vehicles()->where('id', $validated['vehicle_id'])->exists()) {
            return back()->with('error', 'El vehículo seleccionado no es válido.');
        }

        DB::transaction(function () use ($validated, $user) {
            $capacity = DailyCapacity::where('date', $validated['reservation_date'])->lockForUpdate()->first();

            if (!$capacity || $capacity->booked_slots >= $capacity->total_slots) {
                throw ValidationException::withMessages([
                    'reservation_date' => 'No hay cupos disponibles para la fecha seleccionada.',
                ]);
            }

            Reservation::create([
                'user_id' => $user->id,
                'vehicle_id' => $validated['vehicle_id'],
                'reservation_date' => $validated['reservation_date'],
                'notes' => $validated['notes'],
                'status' => 'Pendiente',
            ]);

            $capacity->increment('booked_slots');
        });

        return to_route('cliente.dashboard')->with('success', 'Tu solicitud de reserva ha sido enviada. Recibirás una notificación cuando sea confirmada.');
    }
}