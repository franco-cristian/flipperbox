<?php

namespace FlipperBox\Scheduling\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Scheduling\Models\DailyCapacity;
use FlipperBox\Scheduling\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    /**
     * Muestra una lista filtrable y paginada de todas las reservas.
     */
    public function index(Request $request): Response
    {
        $reservations = Reservation::with(['user', 'vehicle'])
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->input('date'), function ($query, $date) {
                $query->whereDate('reservation_date', $date);
            })
            ->orderBy('reservation_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Scheduling/Admin/ReservationIndex', [
            'reservations' => $reservations,
            'filters' => $request->only(['status', 'date']),
        ]);
    }

    /**
     * Actualiza el estado de una reserva.
     */
    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['Confirmada', 'Cancelada', 'Asistió', 'Ausente'])],
        ]);

        $originalStatus = $reservation->status;
        $newStatus = $validated['status'];
        $reservationDate = Carbon::parse($reservation->reservation_date)->startOfDay();

        // --- REGLA DE NEGOCIO : VALIDACIÓN DE FECHA ---
        // Solo permite marcar como Asistió o Ausente si la fecha de la reserva ya pasó o es el día de hoy.
        if (in_array($newStatus, ['Asistió', 'Ausente']) && $reservationDate->isFuture()) {
            return back()->with('error', 'No se puede marcar como "Asistió" o "Ausente" una reserva que aún no ha ocurrido.');
        }

        // --- REGLA DE NEGOCIO: UNA ORDEN COMPLETADA NO SE PUEDE MODIFICAR ---
        if (in_array($originalStatus, ['Asistió', 'Ausente', 'Cancelada'])) {
            return back()->with('error', 'No se puede cambiar el estado de una reserva que ya ha sido finalizada o cancelada.');
        }

        // --- LÓGICA DE GESTIÓN DE CUPOS ---
        // Si la reserva no estaba 'Confirmada' y ahora sí lo está, o viceversa, ajustamos el cupo.
        if ($originalStatus !== 'Confirmada' && $newStatus === 'Confirmada') {
            DB::transaction(function () use ($reservation) {
                $capacity = DailyCapacity::where('date', $reservation->reservation_date)->lockForUpdate()->first();
                if ($capacity) {
                    $capacity->increment('booked_slots');
                }
            });
        } elseif ($originalStatus === 'Confirmada' && $newStatus !== 'Confirmada') {
            DB::transaction(function () use ($reservation) {
                $capacity = DailyCapacity::where('date', $reservation->reservation_date)->lockForUpdate()->first();
                if ($capacity && $capacity->booked_slots > 0) {
                    $capacity->decrement('booked_slots');
                }
            });
        }

        // Finalmente, actualizamos el estado en la base de datos
        $reservation->update(['status' => $newStatus]);

        return back()->with('success', 'El estado de la reserva ha sido actualizado.');
    }
}
