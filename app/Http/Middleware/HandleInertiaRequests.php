<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            // Heredamos cualquier prop compartida por el middleware padre.
            ...parent::share($request),

            // Compartimos la información de autenticación de forma "lazy" (diferida).
            // El código dentro de la función solo se ejecutará si el frontend lo necesita.
            'auth' => function () use ($request) {
                return [
                    'user' => $request->user() ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        // Los permisos solo se calculan si el usuario existe.
                        'permissions' => $request->user()->getAllPermissions()->pluck('name'),
                    ] : null,
                ];
            },

            // Compartimos los mensajes flash también de forma "lazy".
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            // Comparte las notificaciones en cada petición si el usuario está logueado.
            'notifications' => $request->user()
                ? $request->user()->notifications()->limit(5)->get()
                : [],
        ];
    }
}
