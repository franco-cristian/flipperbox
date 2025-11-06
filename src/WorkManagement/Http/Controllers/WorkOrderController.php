<?php

namespace FlipperBox\WorkManagement\Http\Controllers;

use App\Http\Controllers\Controller;
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
        $workOrders = WorkOrder::with(['vehicle.cliente', 'mechanic'])
            ->latest('entry_date')->paginate(15);
        return Inertia::render('WorkManagement/Index', ['workOrders' => $workOrders]);
    }

    public function create(Vehiculo $vehiculo): Response
    {
        return Inertia::render('WorkManagement/Create', ['vehiculo' => $vehiculo->load('cliente')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehiculos,id'],
            'description' => ['required', 'string'],
        ]);
        $workOrder = WorkOrder::create($validated);
        return to_route('work-orders.show', $workOrder->id);
    }

    public function show(WorkOrder $workOrder): Response
    {
        $workOrder->load(['vehicle.cliente', 'mechanic', 'products', 'services', 'externalCosts']);
        return Inertia::render('WorkManagement/Show', [
            'workOrder' => $workOrder,
            'products' => Product::orderBy('name')->get(['id', 'name', 'price', 'current_stock']),
            'services' => Service::orderBy('name')->get(['id', 'name', 'price']),
        ]);
    }
    
    // --- MÉTODOS PARA AÑADIR ÍTEMS ---
    public function addProduct(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);
        
        DB::transaction(function () use ($validated, $workOrder) {
            $product = Product::lockForUpdate()->find($validated['product_id']);
            if ($product->current_stock < $validated['quantity']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quantity' => 'Stock insuficiente. Stock actual: ' . $product->current_stock,
                ]);
            }
            $product->decrement('current_stock', $validated['quantity']);
            
            // Lógica para evitar duplicados: si el producto ya existe, actualiza la cantidad
            $existingProduct = $workOrder->products()->where('product_id', $product->id)->first();
            if ($existingProduct) {
                $newQuantity = $existingProduct->pivot->quantity + $validated['quantity'];
                $workOrder->products()->updateExistingPivot($product->id, ['quantity' => $newQuantity]);
            } else {
                $workOrder->products()->attach($product->id, [
                    'quantity' => $validated['quantity'],
                    'unit_price' => $product->price,
                ]);
            }

            $this->recalculateTotal($workOrder);
        });

        return to_route('work-orders.show', $workOrder->id)->with('success', 'Producto agregado.');
    }

    public function addService(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate(['service_id' => ['required', 'exists:services,id']]);
        $service = Service::find($validated['service_id']);
        
        // Evitar duplicados
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
            'price' => ['required', 'numeric', 'min:0'],
        ]);
        $workOrder->externalCosts()->create($validated);
        $this->recalculateTotal($workOrder);
        return to_route('work-orders.show', $workOrder->id)->with('success', 'Costo externo agregado.');
    }

    // --- MÉTODOS PARA ELIMINAR ÍTEMS ---

    public function removeProduct(WorkOrder $workOrder, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($workOrder, $product) {
            // Buscamos el registro en la tabla pivot para saber la cantidad a devolver
            $pivot = DB::table('work_order_product')
                ->where('work_order_id', $workOrder->id)
                ->where('product_id', $product->id)
                ->first();

            if ($pivot) {
                // Devolvemos el stock
                $productToUpdate = Product::lockForUpdate()->find($product->id);
                $productToUpdate->increment('current_stock', $pivot->quantity);
                
                // Eliminamos la relación
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
        $workOrder = $externalCost->workOrder; // Guardamos la referencia antes de borrar
        $externalCost->delete();
        $this->recalculateTotal($workOrder);
        return to_route('work-orders.show', $workOrder->id)->with('success', 'Costo externo eliminado de la orden.');
    }


    // --- MÉTODO PRIVADO PARA RECALCULAR TOTALES ---
    private function recalculateTotal(WorkOrder $workOrder)
    {
        // Forzamos a recargar las relaciones desde la base de datos
        $workOrder->load(['products', 'services', 'externalCosts']);
        
        $totalProducts = $workOrder->products->sum(fn ($p) => $p->pivot->unit_price * $p->pivot->quantity);
        $totalServices = $workOrder->services->sum('pivot.price');
        $totalExternalCosts = $workOrder->externalCosts->sum('price');

        $workOrder->total = $totalProducts + $totalServices + $totalExternalCosts;
        $workOrder->save();
    }
}