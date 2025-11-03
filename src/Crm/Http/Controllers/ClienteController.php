<?php
namespace FlipperBox\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Crm\Models\Cliente;
use Inertia\Inertia;
use Inertia\Response;

class ClienteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Crm/Clientes/Index', [
            'clientes' => Cliente::latest()->paginate(10),
        ]);
    }
}