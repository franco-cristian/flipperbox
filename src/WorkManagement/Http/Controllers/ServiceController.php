<?php

namespace FlipperBox\WorkManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\WorkManagement\Http\Requests\StoreServiceRequest;
use FlipperBox\WorkManagement\Http\Requests\UpdateServiceRequest;
use FlipperBox\WorkManagement\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Service::latest();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        return Inertia::render('WorkManagement/Services/Index', [
            'services' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('WorkManagement/Services/Create');
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        Service::create($request->validated());

        return to_route('services.index')->with('success', 'Servicio creado exitosamente.');
    }

    public function edit(Service $service): Response
    {
        return Inertia::render('WorkManagement/Services/Edit', [
            'service' => $service,
        ]);
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return to_route('services.index')->with('success', 'Servicio actualizado exitosamente.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        try {
            $service->delete();
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first('error'));
        }

        return to_route('services.index')->with('success', 'Servicio eliminado exitosamente.');
    }
}
