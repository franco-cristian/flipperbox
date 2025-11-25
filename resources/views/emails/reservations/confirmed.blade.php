<x-mail::message>
# ¡Hola, {{ $reservation->user->name }}!

Tu reserva ha sido confirmada exitosamente. Te esperamos en nuestro taller.

## Detalles de la Reserva

- **Fecha:** {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('d/m/Y') }}
- **Vehículo:** {{ $reservation->vehicle->marca }} {{ $reservation->vehicle->modelo }} ({{ $reservation->vehicle->patente }})
@if($reservation->notes)
- **Tus Notas:** {{ $reservation->notes }}
@endif

<x-mail::button :url="$url">
Ver Mi Reserva
</x-mail::button>

Si necesitas cancelar o reprogramar, puedes hacerlo desde tu portal hasta 24 horas antes.

Gracias,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>