<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Carga los indicadores y datos que consume el dashboard.
    public function index(): View
    {
        $hoy = Carbon::today('America/Lima');
        $inicioMes = $hoy->copy()->startOfMonth();
        $finMes = $hoy->copy()->endOfMonth();

        $ventasHoy = DB::table('sales')
            ->whereBetween('date', [$hoy->copy()->startOfDay(), $hoy->copy()->endOfDay()])
            ->sum('total');

        $ventasMes = DB::table('sales')
            ->whereBetween('date', [$inicioMes, $finMes])
            ->sum('total');

        $totalClientes = DB::table('customers')->count();

        $medicamentosStockBajo = DB::table('medicines')
            ->where('stock', '<=', 10)
            ->count();

        $inicioSemana = $hoy->copy()->subDays(6);
        $ventasSemana = DB::table('sales')
            ->selectRaw('DATE(date) AS fecha, SUM(total) AS total')
            ->whereBetween('date', [$inicioSemana->copy()->startOfDay(), $hoy->copy()->endOfDay()])
            ->groupByRaw('DATE(date)')
            ->pluck('total', 'fecha');

        $fechasUltimosDias = [];
        $totalesUltimosDias = [];
        for ($fecha = $inicioSemana->copy(); $fecha->lte($hoy); $fecha->addDay()) {
            $clave = $fecha->format('Y-m-d');
            $fechasUltimosDias[] = $fecha->format('d/m');
            $totalesUltimosDias[] = (float) ($ventasSemana[$clave] ?? 0);
        }

        $ventasPorCategoria = DB::table('detail_sales')
            ->join('sales', 'detail_sales.sale_id', '=', 'sales.id')
            ->join('medicines', 'detail_sales.medicine_id', '=', 'medicines.id')
            ->join('categories', 'medicines.category_id', '=', 'categories.id')
            ->whereBetween('sales.date', [$inicioMes, $finMes])
            ->groupBy('categories.name')
            ->selectRaw('categories.name AS categoria, SUM(detail_sales.subtotal) AS total')
            ->orderByDesc('total')
            ->get();

        $categorias = $ventasPorCategoria->pluck('categoria')->values()->all();
        $totalesCategorias = $ventasPorCategoria->pluck('total')->map(fn ($total) => (float) $total)->values()->all();

        $topMedicamentos = DB::table('detail_sales')
            ->join('sales', 'detail_sales.sale_id', '=', 'sales.id')
            ->join('medicines', 'detail_sales.medicine_id', '=', 'medicines.id')
            ->whereBetween('sales.date', [$inicioMes, $finMes])
            ->groupBy('medicines.id', 'medicines.name')
            ->selectRaw('medicines.name AS medicamento, SUM(detail_sales.amount) AS unidades')
            ->orderByDesc('unidades')
            ->limit(5)
            ->get();

        $nombresMedicamentos = $topMedicamentos->pluck('medicamento')->values()->all();
        $unidadesMedicamentos = $topMedicamentos->pluck('unidades')->map(fn ($unidades) => (int) $unidades)->values()->all();

        return view('principal.dashboard.index', [
            'ventasHoy' => (float) $ventasHoy,
            'ventasMes' => (float) $ventasMes,
            'totalClientes' => $totalClientes,
            'medicamentosStockBajo' => $medicamentosStockBajo,
            'fechasUltimosDias' => $fechasUltimosDias,
            'totalesUltimosDias' => $totalesUltimosDias,
            'categorias' => $categorias,
            'totalesCategorias' => $totalesCategorias,
            'nombresMedicamentos' => $nombresMedicamentos,
            'unidadesMedicamentos' => $unidadesMedicamentos,
        ]);
    }
}
