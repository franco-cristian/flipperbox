<?php

namespace FlipperBox\Scheduling\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Scheduling\Models\DailyCapacity;
use FlipperBox\Scheduling\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->withQueryString(); // Importante para que la paginación mantenga los filtros

        return Inertia::render('Scheduling/Admin/ReservationIndex', [
            'reservations' => $reservations,
            'filters' => $request->only(['status', 'date']), // Pasamos los filtros a la vista
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

        // Si la reserva no estaba 'Confirmada' y ahora sí lo está, o viceversa, ajustamos el cupo.
        // Esta lógica previene que un administrador cancele una reserva sin que se libere el cupo.
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

        $reservation->update(['status' => $newStatus]);

        return back()->with('success', 'El estado de la reserva ha sido actualizado.');
    }
}