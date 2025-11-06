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
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderController extends Controller
{
    public function index(): Response
    {
        $workOrders = WorkOrder::with(['vehicle.cliente', 'mechanic'])->latest('entry_date')->paginate(15);
        return Inertia::render('WorkManagement/Index', ['workOrders' => $workOrders]);
    }

    public function create(Vehiculo $vehiculo): Response
    {
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
        $freshWorkOrder = WorkOrder::with([
            'vehicle.cliente',
            'mechanic',
            'products',
            'services',
            'externalCosts'
        ])->findOrFail($workOrder->id);

        logger('External Costs count: ' . $workOrder->externalCosts->count());
        logger('External Costs: ' . json_encode($workOrder->externalCosts));

        $mechanics = User::role('Mecanico')->orderBy('name')->get(['id', 'name']);

        return Inertia::render('WorkManagement/Show', [
            'workOrder' => $freshWorkOrder,
            'products' => Product::orderBy('name')->get(['id', 'name', 'price', 'current_stock']),
            'services' => Service::orderBy('name')->get(['id', 'name', 'price']),
            'mechanics' => $mechanics,
        ]);
    }

    // --- MÉTODOS PARA AÑADIR ÍTEMS ---
    public function addProduct(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate(['product_id' => ['required', 'exists:products,id'], 'quantity' => ['required', 'integer', 'min:1']]);
        DB::transaction(function () use ($validated, $workOrder) {
            $product = Product::lockForUpdate()->find($validated['product_id']);
            if ($product->current_stock < $validated['quantity']) {
                throw \Illuminate\Validation\ValidationException::withMessages(['quantity' => 'Stock insuficiente. Stock actual: ' . $product->current_stock]);
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
        if (!$workOrder->services()->where('service_id', $service->id)->exists()) {
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
            'price' => ['required', 'numeric', 'min:0']
        ]);

        // Crear el costo externo
        $workOrder->externalCosts()->create($validated);

        // Recargar la relación externalCosts antes de recalcular
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
            'status' => $workOrder->status === 'Pendiente' && $validated['mechanic_id'] ? 'En Progreso' : $workOrder->status
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

        // Recargar la relación externalCosts antes de recalcular
        $workOrder->load('externalCosts');
        $this->recalculateTotal($workOrder);

        return to_route('work-orders.show', $workOrder->id)->with('success', 'Costo externo eliminado de la orden.');
    }

    // --- MÉTODO PRIVADO PARA RECALCULAR TOTALES ---
    private function recalculateTotal(WorkOrder $workOrder)
    {
        // Asegurarnos de que todas las relaciones estén cargadas
        $workOrder->load(['products', 'services', 'externalCosts']);

        $totalProducts = $workOrder->products->sum(fn($p) => $p->pivot->unit_price * $p->pivot->quantity);
        $totalServices = $workOrder->services->sum('pivot.price');
        $totalExternalCosts = $workOrder->externalCosts->sum('price');

        $workOrder->total = $totalProducts + $totalServices + $totalExternalCosts;
        $workOrder->save();
    }
}