<?php

namespace FlipperBox\ClientScheduling\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Scheduling\Models\DailyCapacity;
use FlipperBox\Scheduling\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('vehiculos');

        if ($user->vehiculos->isEmpty()) {
            return Inertia::render('ClientScheduling/BookingIndex', [
                'hasVehicles' => false,
                'capacities' => [],
                'userReservations' => [],
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

        // Pasamos las reservas activas del usuario al frontend con información de cancelación
        $userReservations = Reservation::where('user_id', $user->id)
            ->where('status', 'Confirmada')
            ->get()
            ->map(function ($reservation) {
                $reservationDate = Carbon::parse($reservation->reservation_date);
                $now = Carbon::now();
                $hoursDifference = $now->diffInHours($reservationDate, false);
                
                return [
                    'id' => $reservation->id,
                    'reservation_date' => $reservation->reservation_date,
                    'vehicle_id' => $reservation->vehicle_id,
                    'can_cancel' => $hoursDifference > 24, // Puede cancelar si hay más de 24 horas
                    'hours_until_reservation' => $hoursDifference,
                ];
            });

        return Inertia::render('ClientScheduling/BookingIndex', [
            'hasVehicles' => true,
            'vehicles' => $user->vehiculos,
            'capacities' => $capacities,
            'currentYear' => (int)$year,
            'currentMonth' => (int)$month,
            'userReservations' => $userReservations,
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

        if (! $user->vehiculos()->where('id', $validated['vehicle_id'])->exists()) {
            return back()->with('error', 'El vehículo seleccionado no es válido.');
        }

        // 1. Prevenir que un usuario reserve el mismo día dos veces.
        $alreadyBookedOnDate = Reservation::where('user_id', $user->id)
            ->where('reservation_date', $validated['reservation_date'])
            ->where('status', 'Confirmada')
            ->exists();

        if ($alreadyBookedOnDate) {
            throw ValidationException::withMessages([
                'reservation_date' => 'Ya tienes una reserva confirmada para este día.',
            ]);
        }

        // 2. Prevenir que el MISMO VEHÍCULO se reserve dos veces.
        $hasActiveReservationForVehicle = Reservation::where('vehicle_id', $validated['vehicle_id'])
            ->where('status', 'Confirmada')
            ->exists();

        if ($hasActiveReservationForVehicle) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'Este vehículo ya tiene una reserva activa. Completa o cancela la reserva actual antes de solicitar una nueva para este vehículo.',
            ]);
        }

        DB::transaction(function () use ($validated, $user) {
            $capacity = DailyCapacity::where('date', $validated['reservation_date'])->lockForUpdate()->first();

            if (!$capacity || $capacity->booked_slots >= $capacity->total_slots) {
                throw ValidationException::withMessages([
                    'reservation_date' => 'No hay cupos disponibles para la fecha seleccionada. Por favor, elige otra.',
                ]);
            }

            Reservation::create([
                'user_id' => $user->id,
                'vehicle_id' => $validated['vehicle_id'],
                'reservation_date' => $validated['reservation_date'],
                'notes' => $validated['notes'],
                'status' => 'Confirmada',
            ]);

            $capacity->increment('booked_slots');
        });

        return back()->with('success', '¡Tu reserva ha sido confirmada! Te esperamos.');
    }

    // Nuevo método para cancelar reservas
    public function destroy(Reservation $reservation): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Verificar que la reserva pertenece al usuario
        if ($reservation->user_id !== $user->id) {
            return back()->with('error', 'No tienes permiso para cancelar esta reserva.');
        }

        // Verificar que la reserva está confirmada
        if ($reservation->status !== 'Confirmada') {
            return back()->with('error', 'Esta reserva no está confirmada o ya fue cancelada.');
        }

        // Verificar que hay al menos 24 horas de anticipación
        $reservationDate = Carbon::parse($reservation->reservation_date);
        $now = Carbon::now();
        $hoursDifference = $now->diffInHours($reservationDate, false);

        if ($hoursDifference <= 24) {
            return back()->with('error', 'Solo puedes cancelar reservas con al menos 24 horas de anticipación.');
        }

        DB::transaction(function () use ($reservation) {
            // Cambiar el estado de la reserva
            $reservation->update(['status' => 'Cancelada']);

            // Liberar el cupo
            $capacity = DailyCapacity::where('date', $reservation->reservation_date)->first();
            if ($capacity && $capacity->booked_slots > 0) {
                $capacity->decrement('booked_slots');
            }
        });

        return back()->with('success', 'Reserva cancelada exitosamente.');
    }
}