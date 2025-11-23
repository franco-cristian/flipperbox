<?php

namespace FlipperBox\WorkManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use FlipperBox\Crm\Models\Vehiculo;
use FlipperBox\Inventory\Models\Product;
use FlipperBox\WorkManagement\Models\ExternalCost;
use FlipperBox\WorkManagement\Models\Service;
use FlipperBox\WorkManagement\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $query = WorkOrder::with(['vehicle.cliente', 'mechanic'])->latest('entry_date');

        // Lógica de Búsqueda
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhereHas('vehicle', function ($q) use ($search) {
                        $q->where('patente', 'ilike', "%{$search}%")
                            ->orWhere('marca', 'ilike', "%{$search}%")
                            ->orWhere('modelo', 'ilike', "%{$search}%");
                    })
                    ->orWhereHas('vehicle.cliente', function ($q) use ($search) {
                        $q->where('name', 'ilike', "%{$search}%")
                            ->orWhere('apellido', 'ilike', "%{$search}%");
                    });
            });
        }

        return Inertia::render('WorkManagement/Index', [
            'workOrders' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(Vehiculo $vehiculo): Response
    {
        // La relación 'cliente' ahora es 'user'
        return Inertia::render('WorkManagement/Create', ['vehiculo' => $vehiculo->load('cliente')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(['vehicle_id' => ['required', 'exists:vehiculos,id'], 'description' => ['required', 'string']]);
        $workOrder = WorkOrder::create($validated);

        return to_route('work-orders.show', $workOrder->id)->with('success', 'Orden de Trabajo creada exitosamente.');
    }

    public function show(WorkOrder $workOrder): Response
    {
        $workOrder->load(['vehicle.cliente', 'mechanic', 'products', 'services', 'externalCosts']);
        $mechanics = User::role('Mecanico')->orderBy('name')->get(['id', 'name']);

        return Inertia::render('WorkManagement/Show', [
            'workOrder' => $workOrder,
            'products' => Product::orderBy('name')->get(['id', 'name', 'price', 'current_stock']),
            'services' => Service::orderBy('name')->get(['id', 'name', 'price']),
            'mechanics' => $mechanics,
        ]);
    }

    /**
     * Actualiza el estado de una Orden de Trabajo.
     */
    public function update(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['En Progreso', 'Completada', 'Cancelada'])],
        ]);

        if ($workOrder->status === 'Completada' && $validated['status'] !== 'Cancelada') {
            return back()->with('error', 'Una orden completada solo puede ser cancelada.');
        }

        $originalStatus = $workOrder->status;
        $workOrder->status = $validated['status'];

        if ($validated['status'] === 'Completada') {
            $workOrder->completion_date = now();
        }

        // Si se cancela una orden que tenía productos, el Observer se encargará de devolver el stock.
        if ($originalStatus !== 'Cancelada' && $validated['status'] === 'Cancelada') {
            // La lógica de devolución de stock se ha movido al WorkOrderObserver en el evento 'updating'.
            // Aquí solo guardamos el cambio de estado.
        }

        $workOrder->save();

        return back()->with('success', 'Estado de la orden actualizado.');
    }

    /**
     * Elimina (Soft Delete) una Orden de Trabajo.
     */
    public function destroy(WorkOrder $workOrder): RedirectResponse
    {
        if ($workOrder->status === 'Completada') {
            return to_route('work-orders.index')->with('error', 'No se puede eliminar una orden de trabajo completada.');
        }

        $workOrder->delete(); // El Observer se encargará de devolver el stock

        return to_route('work-orders.index')->with('success', 'Orden de trabajo eliminada exitosamente.');
    }

    // --- MÉTODOS PARA AÑADIR ÍTEMS ---
    public function addProduct(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate(['product_id' => ['required', 'exists:products,id'], 'quantity' => ['required', 'integer', 'min:1']]);
        DB::transaction(function () use ($validated, $workOrder) {
            $product = Product::lockForUpdate()->find($validated['product_id']);
            if ($product->current_stock < $validated['quantity']) {
                throw ValidationException::withMessages(['quantity' => 'Stock insuficiente. Stock actual: '.$product->current_stock]);
            }
            $product->decrement('current_stock', $validated['quantity']);
            $existingProduct = $workOrder->products()->where('product_id', $product->id)->first();
            if ($existingProduct) {
                $newQuantity = $existingProduct->pivot->quantity + $validated['quantity'];
                $workOrder->products()->updateExistingPivot($product->id, ['quantity' => $newQuantity]);
            } else {
                $workOrder->products()->attach($product->id, ['quantity' => $validated['quantity'], 'unit_price' => $product->price]);
            }
            $this->recalculateTotal($workOrder);
        });

        return to_route('work-orders.show', $workOrder->id)->with('success', 'Producto agregado.');
    }

    public function addService(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate(['service_id' => ['required', 'exists:services,id']]);
        $service = Service::find($validated['service_id']);
        if (! $workOrder->services()->where('service_id', $service->id)->exists()) {
            $workOrder->services()->attach($service->id, ['price' => $service->price]);
            $this->recalculateTotal($workOrder);
        }

        return to_route('work-orders.show', $workOrder->id)->with('success', 'Servicio agregado.');
    }

    public function addExternalCost(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'cost' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);
        $workOrder->externalCosts()->create($validated);
        $workOrder->load('externalCosts');
        $this->recalculateTotal($workOrder);

        return to_route('work-orders.show', $workOrder->id)->with('success', 'Costo externo agregado.');
    }

    // --- MÉTODO PARA ASIGNAR MECÁNICO ---
    public function assignMechanic(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate(['mechanic_id' => ['nullable', 'exists:users,id']]);
        $workOrder->update([
            'mechanic_id' => $validated['mechanic_id'],
            'status' => $workOrder->status === 'Pendiente' && $validated['mechanic_id'] ? 'En Progreso' : $workOrder->status,
        ]);

        return to_route('work-orders.show', $workOrder->id)->with('success', 'Mecánico asignado/actualizado.');
    }

    // --- MÉTODOS PARA ELIMINAR ÍTEMS ---
    public function removeProduct(WorkOrder $workOrder, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($workOrder, $product) {
            $pivot = DB::table('work_order_product')->where('work_order_id', $workOrder->id)->where('product_id', $product->id)->first();
            if ($pivot) {
                $productToUpdate = Product::lockForUpdate()->find($product->id);
                $productToUpdate->increment('current_stock', $pivot->quantity);
                $workOrder->products()->detach($product->id);
                $this->recalculateTotal($workOrder);
            }
        });

        return to_route('work-orders.show', $workOrder->id)->with('success', 'Producto eliminado de la orden.');
    }

    public function removeService(WorkOrder $workOrder, Service $service): RedirectResponse
    {
        $workOrder->services()->detach($service->id);
        $this->recalculateTotal($workOrder);

        return to_route('work-orders.show', $workOrder->id)->with('success', 'Servicio eliminado de la orden.');
    }

    public function removeExternalCost(ExternalCost $externalCost): RedirectResponse
    {
        $workOrder = $externalCost->workOrder;
        $externalCost->delete();
        $workOrder->load('externalCosts');
        $this->recalculateTotal($workOrder);

        return to_route('work-orders.show', $workOrder->id)->with('success', 'Costo externo eliminado de la orden.');
    }

    // --- MÉTODO PRIVADO PARA RECALCULAR TOTALES ---
    private function recalculateTotal(WorkOrder $workOrder)
    {
        $workOrder->load(['products', 'services', 'externalCosts']);
        $totalProducts = $workOrder->products->sum(fn ($p) => $p->pivot->unit_price * $p->pivot->quantity);
        $totalServices = $workOrder->services->sum('pivot.price');
        $totalExternalCosts = $workOrder->externalCosts->sum('price');
        $workOrder->total = $totalProducts + $totalServices + $totalExternalCosts;
        $workOrder->save();
    }
}
