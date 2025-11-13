<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes();

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Solo los usuarios que devuelvan 'true' en esta función podrán unirse al canal.
Broadcast::channel('admin-notifications', function ($user) {
    // Solo permitimos el acceso si el usuario tiene el permiso de gestionar reservas.
    return $user !== null && $user->can('gestionar reservas');
});