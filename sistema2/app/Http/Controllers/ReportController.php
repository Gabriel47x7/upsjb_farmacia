<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    // Muestra el reporte de ventas con sus filtros y opciones.
    public function sales(Request $request): View
    {
        $filters = $this->validatedSalesFilters($request);
        $sales = $this->salesQuery($filters)->get();
        $from = $filters['from'];
        $to = $filters['to'];
        $summary = [
            'count' => $sales->count(),
            'total' => $sales->sum('total'),
            'igv' => $sales->sum('igv'),
            'average' => $sales->avg('total') ?? 0,
        ];
        $chart = $sales
            ->groupBy(fn ($sale) => $sale->date->format('d/m'))
            ->map(fn ($group) => round($group->sum('total'), 2));

        return view('principal.reports.sales', [
            'sales' => $sales,
            'summary' => $summary,
            'chart' => $chart,
            'filters' => $filters,
            'from' => $from,
            'to' => $to,
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    // Genera el PDF aplicando exactamente los mismos filtros del reporte.
    public function salesPdf(Request $request): Response
    {
        $filters = $this->validatedSalesFilters($request);
        $sales = $this->salesQuery($filters)->get();

        $appliedFilters = array_filter([
            'Desde' => $filters['from'] ?? null,
            'Hasta' => $filters['to'] ?? null,
            'Cliente' => isset($filters['customer_id'])
                ? Customer::find($filters['customer_id'])?->name
                : null,
            'Vendedor' => isset($filters['user_id'])
                ? User::find($filters['user_id'])?->name
                : null,
            'Categoría' => isset($filters['category_id'])
                ? Category::find($filters['category_id'])?->name
                : null,
        ], fn ($value) => $value !== null && $value !== '');

        return Pdf::loadView('principal.reports.sales-pdf', [
            'sales' => $sales,
            'filters' => $filters,
            'appliedFilters' => $appliedFilters,
            'totalIgv' => $sales->sum('igv'),
            'totalSales' => $sales->sum('total'),
            'generatedAt' => now(),
        ])->download('reporte-ventas-'.now()->format('Ymd').'.pdf');
    }

    // Exporta el reporte de ventas a Excel respetando los filtros seleccionados.
    public function salesExcel(Request $request)
    {
        $filters = $this->validatedSalesFilters($request);

        return Excel::download(
            new SalesExport($filters),
            'reporte-ventas-'.now()->format('Ymd').'.xlsx'
        );
    }

    // Valida una sola vez los filtros compartidos por la vista y el PDF.
    private function validatedSalesFilters(Request $request): array
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $today = now();
        $filters['from'] = $filters['from'] ?? $today->copy()->startOfMonth()->toDateString();
        $filters['to'] = $filters['to'] ?? $today->toDateString();

        return $filters;
    }

    // Centraliza la consulta para que el listado y el PDF siempre coincidan.
    private function salesQuery(array $filters): Builder
    {
        $query = Sale::query()->with(['customer', 'user']);

        if (! empty($filters['from'])) {
            $query->whereDate('date', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('date', '<=', $filters['to']);
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->whereHas('details.medicine', function (Builder $medicineQuery) use ($filters) {
                $medicineQuery->where('category_id', $filters['category_id']);
            });
        }

        return $query->orderByDesc('date');
    }
}
