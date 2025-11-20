<?php

namespace FlipperBox\ClientScheduling\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\ReservationConfirmed;
use App\Models\User;
use App\Notifications\ReservationCreated;
use FlipperBox\Scheduling\Models\DailyCapacity;
use FlipperBox\Scheduling\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
                'currentYear' => now()->year,
                'currentMonth' => now()->month,
            ]);
        }

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $capacities = DailyCapacity::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('date', '>=', today())
            ->orderBy('date')
            ->get()
            ->map(fn($capacity) => [
                'date' => $capacity->date->format('Y-m-d'),
                'available_slots' => $capacity->total_slots - $capacity->booked_slots,
            ]);

        $userReservations = Reservation::where('user_id', $user->id)
            ->orderBy('reservation_date', 'desc')
            ->get()
            ->map(function ($reservation) {
                $reservationDate = Carbon::parse($reservation->reservation_date)->startOfDay();
                $now = Carbon::now()->startOfDay();
                $hoursDifference = $now->diffInHours($reservationDate, false);

                return [
                    'id' => $reservation->id,
                    'reservation_date' => $reservation->reservation_date->format('Y-m-d'),
                    'vehicle_id' => $reservation->vehicle_id,
                    'status' => $reservation->status,
                    'can_cancel' => $hoursDifference > 24 && $reservation->status === 'Confirmada',
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

        // Verificar que el vehículo pertenece al usuario
        if (!$user->vehiculos()->where('id', $validated['vehicle_id'])->exists()) {
            return back()->with('error', 'El vehículo seleccionado no es válido.');
        }

        // Verificar que el usuario no tenga ya una reserva confirmada para esta fecha
        $alreadyBookedOnDate = Reservation::where('user_id', $user->id)
            ->where('reservation_date', $validated['reservation_date'])
            ->where('status', 'Confirmada')
            ->exists();

        if ($alreadyBookedOnDate) {
            throw ValidationException::withMessages([
                'reservation_date' => 'Ya tienes una reserva confirmada para este día.'
            ]);
        }

        // Solo verificar reservas del MISMO vehículo en la MISMA fecha
        $hasActiveReservationForVehicleOnThisDate = Reservation::where('vehicle_id', $validated['vehicle_id'])
            ->where('reservation_date', $validated['reservation_date'])
            ->where('status', 'Confirmada')
            ->exists();

        if ($hasActiveReservationForVehicleOnThisDate) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'Este vehículo ya tiene una reserva confirmada para esta fecha específica.'
            ]);
        }

        $reservation = DB::transaction(function () use ($validated, $user) {
            $capacity = DailyCapacity::where('date', $validated['reservation_date'])->lockForUpdate()->first();

            if (!$capacity || $capacity->booked_slots >= $capacity->total_slots) {
                throw ValidationException::withMessages([
                    'reservation_date' => 'No hay cupos disponibles para la fecha seleccionada.'
                ]);
            }

            // Creamos la reserva y la guardamos en una variable
            $newReservation = Reservation::create([
                'user_id' => $user->id,
                'vehicle_id' => $validated['vehicle_id'],
                'reservation_date' => $validated['reservation_date'],
                'notes' => $validated['notes'],
                'status' => 'Confirmada',
            ]);

            $capacity->increment('booked_slots');

            return $newReservation; // Devolvemos la reserva creada desde la transacción
        });

        // --- NUEVO SISTEMA DE NOTIFICACIONES ---
        // 1. Buscamos a los usuarios que tienen permiso de ver reservas (Admins)
        $admins = User::permission('gestionar reservas')->get();

        // 2. Enviamos la notificación (esto guardará en DB y enviará por Reverb)
        foreach ($admins as $admin) {
            $admin->notify(new ReservationCreated($reservation));
        }

        // 3. Enviar Email al Cliente (NUEVO CÓDIGO)
        Mail::to($user)->send(new ReservationConfirmed($reservation));

        return back()->with('success', '¡Tu reserva ha sido confirmada! Te esperamos.');
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($reservation->user_id !== $user->id) {
            return back()->with('error', 'No tienes permiso para cancelar esta reserva.');
        }

        if ($reservation->status !== 'Confirmada') {
            return back()->with('error', 'Esta reserva no se puede cancelar.');
        }

        $reservationDate = Carbon::parse($reservation->reservation_date);
        $now = Carbon::now();
        $hoursDifference = $now->diffInHours($reservationDate, false);

        if ($hoursDifference <= 24) {
            return back()->with('error', 'Solo puedes cancelar reservas con más de 24 horas de anticipación.');
        }

        DB::transaction(function () use ($reservation) {
            $reservation->update(['status' => 'Cancelada']);

            $capacity = DailyCapacity::where('date', $reservation->reservation_date)->first();
            if ($capacity && $capacity->booked_slots > 0) {
                $capacity->decrement('booked_slots');
            }
        });

        return back()->with('success', 'Reserva cancelada exitosamente.');
    }
}
