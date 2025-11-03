<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
|
| Estas rutas son accesibles para cualquier visitante, incluso si no ha
| iniciado sesión. Aquí se encuentra la página de bienvenida.
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| Rutas de Administración (Protegidas)
|--------------------------------------------------------------------------
|
| Este grupo de rutas requiere que el usuario haya iniciado sesión ('auth'),
| haya verificado su email ('verified') y, crucialmente, que tenga
| el rol de 'Admin' ('role:Admin'). Si un usuario sin este rol
| intenta acceder, recibirá un error 403 (Prohibido).
|
*/
Route::middleware(['auth', 'verified', 'role:Admin'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| Rutas de Autenticación
|--------------------------------------------------------------------------
|
| Este archivo, proporcionado por Laravel Breeze, contiene todas las rutas
| necesarias para el proceso de login, registro, logout, recuperación
| de contraseña, etc.
|
*/
require __DIR__.'/../../../routes/auth.php';