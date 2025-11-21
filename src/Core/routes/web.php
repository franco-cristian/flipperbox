<?php

use App\Http\Controllers\ProfileController;
use FlipperBox\Core\Http\Controllers\ChatbotController;
use FlipperBox\Core\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
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

// --- RUTA DEL DASHBOARD PRINCIPAL ---
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:Admin'])
    ->name('dashboard');

// --- Rutas de Gestión de Perfil (Accesible para cualquier usuario autenticado) ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/notifications/mark-as-read', function (Request $request) {
    $request->user()->unreadNotifications->markAsRead();

    return back();
})->middleware('auth')->name('notifications.markAsRead');

// Ruta del Chatbot (Pública)
Route::post('/chatbot/ask', [ChatbotController::class, 'handle'])->name('chatbot.ask');

// --- Inclusión de las Rutas de Autenticación (Login, Registro, etc.) ---
require __DIR__.'/../../../routes/auth.php';
