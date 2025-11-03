<?php

namespace FlipperBox\Crm\Actions;

use FlipperBox\Crm\Models\Cliente;
use Illuminate\Support\Facades\Log;

class CrearNuevoClienteAction
{
    public function execute(array $data): Cliente
    {
        // Creamos el cliente con los datos ya validados por el Form Request
        $cliente = Cliente::create([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'email' => $data['email'] ?? null,
            'telefono' => $data['telefono'],
            'documento_tipo' => $data['documento_tipo'] ?? null,
            'documento_valor' => $data['documento_valor'] ?? null,
        ]);

        Log::info("Nuevo cliente creado: ID {$cliente->id} - {$cliente->nombre} {$cliente->apellido}");

        return $cliente;
    }
}