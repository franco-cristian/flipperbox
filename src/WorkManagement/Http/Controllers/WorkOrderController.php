<?php

namespace FlipperBox\WorkManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\WorkManagement\Models\WorkOrder;
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderController extends Controller
{
    public function index(): Response
    {
        $workOrders = WorkOrder::with(['vehicle.cliente', 'mechanic'])
            ->latest('entry_date')
            ->paginate(10);

        return Inertia::render('WorkManagement/Index', [
            'workOrders' => $workOrders,
        ]);
    }
}