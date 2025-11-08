<?php
namespace FlipperBox\Crm\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateUserAsClientAction
{
    public function execute(array $data): User
    {
        // Añadimos la contraseña por defecto
        $data['password'] = Hash::make('password');

        $user = User::create($data);

        // Asignamos el rol 'Cliente'
        $clienteRole = Role::findByName('Cliente');
        $user->assignRole($clienteRole);

        return $user;
    }
}