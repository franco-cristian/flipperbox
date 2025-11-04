<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rutas del Módulo Core
|--------------------------------------------------------------------------
|
| Este archivo contiene las rutas fundamentales de la aplicación, como la
| página de bienvenida, el dashboard principal y la gestión de perfiles.
| Todas estas rutas se cargan a través del CoreServiceProvider.
|
*/

// --- Ruta de Bienvenida (Pública) ---
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// --- Ruta del Dashboard Principal (Accesible para cualquier usuario autenticado) ---
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'role:Admin'])->name('dashboard');

// --- Rutas de Gestión de Perfil (Accesible para cualquier usuario autenticado) ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- Inclusión de las Rutas de Autenticación (Login, Registro, etc.) ---
require __DIR__.'/../../../routes/auth.php';