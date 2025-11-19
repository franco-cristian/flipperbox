<?php

namespace FlipperBox\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Inventory\Models\Product;
use FlipperBox\WorkManagement\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // --- MÉTRICA 1: RESUMEN FINANCIERO (CON CASTEO A FLOAT) ---
        $ingresosMes = (float) WorkOrder::where('status', 'Completada')
            ->whereMonth('completion_date', now()->month)
            ->whereYear('completion_date', now()->year)
            ->sum('total');

        // --- MÉTRICA 2: ACTIVIDAD DEL TALLER ---
        $activityData = WorkOrder::select(
                DB::raw('DATE(completion_date) as date'),
                DB::raw('count(*) as count')
            )
            ->where('status', 'Completada')
            ->whereBetween('completion_date', [now()->subDays(29), now()])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('count', 'date');

        $dailyActivityChart = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyActivityChart['labels'][] = Carbon::parse($date)->format('d/m');
            $dailyActivityChart['data'][] = $activityData->get($date, 0);
        }

        // --- MÉTRICA 3: TOP 5 PRODUCTOS MÁS VENDIDOS DEL MES ---
        $topProducts = DB::table('work_order_product')
            ->join('products', 'work_order_product.product_id', '=', 'products.id')
            ->join('work_orders', 'work_order_product.work_order_id', '=', 'work_orders.id')
            ->select(
                'products.name',
                DB::raw('SUM(work_order_product.quantity * work_order_product.unit_price) as total_revenue')
            )
            ->where('work_orders.status', 'Completada')
            ->whereMonth('work_orders.completion_date', now()->month)
            ->whereYear('work_orders.completion_date', now()->year)
            ->groupBy('products.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();

        // --- MÉTRICA 4: PRODUCTOS CON STOCK BAJO ---
        $lowStockProducts = Product::where('current_stock', '<=', DB::raw('min_threshold'))
            ->orderBy('current_stock', 'asc')
            ->limit(5)
            ->get(['name', 'current_stock', 'min_threshold']);

        // --- MÉTRICA 5: ESTADO DE ÓRDENES DE TRABAJO (GRÁFICO CIRCULAR) ---
        $workOrderStatus = WorkOrder::select('status', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        $workOrderStatusChart = [
            'labels' => $workOrderStatus->keys(),
            'data' => $workOrderStatus->values(),
        ];
        
        // --- DATOS PARA NOTIFICACIONES ---
        $notifications = $user->unreadNotifications()->limit(5)->get();

        return Inertia::render('Dashboard', [
            'ingresosMes' => $ingresosMes,
            'dailyActivityChart' => $dailyActivityChart,
            'topProducts' => $topProducts,
            'lowStockProducts' => $lowStockProducts,
            'workOrderStatusChart' => $workOrderStatusChart,
            'notifications' => $notifications,
        ]);
    }
}