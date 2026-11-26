@extends('template.administrador')

@section('title', 'Reporte de ventas')
@section('name_section', 'Reporte de ventas')
@section('description', 'Consulta las ventas por periodo, cliente, vendedor y categoría')
@section('item_section', 'Inicio')
@section('route_section', route('reports.sales'))
@section('section', 'Reportes')
@section('module', 'Ventas')

@section('content')
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-body">
        @include('partials.alerts')

        <form method="GET" action="{{ route('reports.sales') }}" class="row g-3 align-items-end mb-4">
            <div class="col-12 col-sm-6 col-lg">
                <label for="from" class="form-label">Desde</label>
                <input type="date" id="from" name="from" class="form-control @error('from') is-invalid @enderror" value="{{ $from }}">
                @error('from')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <label for="to" class="form-label">Hasta</label>
                <input type="date" id="to" name="to" class="form-control @error('to') is-invalid @enderror" value="{{ $to }}">
                @error('to')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <label for="customer_id" class="form-label">Cliente</label>
                <select id="customer_id" name="customer_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(($filters['customer_id'] ?? '') == $customer->id)>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <label for="user_id" class="form-label">Vendedor</label>
                <select id="user_id" name="user_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <label for="category_id" class="form-label">Categoría</label>
                <select id="category_id" name="category_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary rounded-3 text-uppercase text-white">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
                <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary">Limpiar</a>
                <a href="{{ route('reports.sales.pdf', request()->query()) }}" class="btn btn-danger rounded-3 text-uppercase text-white">
                    <i class="bi bi-file-earmark-pdf me-1"></i>Exportar PDF
                </a>
                <a href="{{ route('reports.sales.excel', request()->query()) }}" class="btn btn-success rounded-3 text-uppercase text-white">
                    <i class="bi bi-file-earmark-excel me-1"></i>Exportar Excel
                </a>
            </div>
        </form>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100">
                    <div class="card-body">
                        <p class="text-secondary text-uppercase small fw-semibold mb-2">N.° de ventas</p>
                        <h3 class="mb-0">{{ $summary['count'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100">
                    <div class="card-body">
                        <p class="text-secondary text-uppercase small fw-semibold mb-2">Total vendido (S/)</p>
                        <h3 class="mb-0">{{ number_format($summary['total'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100">
                    <div class="card-body">
                        <p class="text-secondary text-uppercase small fw-semibold mb-2">IGV (S/)</p>
                        <h3 class="mb-0">{{ number_format($summary['igv'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100">
                    <div class="card-body">
                        <p class="text-secondary text-uppercase small fw-semibold mb-2">Venta promedio (S/)</p>
                        <h3 class="mb-0">{{ number_format($summary['average'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-white border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-3">
                <h5 class="mb-0">Total vendido por día (S/)</h5>
            </div>
            <div class="card-body">
                <canvas id="chartReport" height="90"></canvas>
            </div>
        </div>

        <h6 class="text-uppercase small text-muted mb-3">
            Total de ventas encontradas: {{ $sales->count() }}
        </h6>
        <div class="table-responsive">
            <table id="display" class="table table-sm table-hover w-100 bg-white">
                <thead>
                    <tr>
                        <th class="text-center">N.°</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th class="text-end">IGV</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td class="text-center">
                                <a href="{{ route('sales.show', $sale) }}">{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</a>
                            </td>
                            <td>{{ $sale->date->format('d/m/Y H:i') }}</td>
                            <td>{{ $sale->customer->name }}</td>
                            <td>{{ $sale->user->name }}</td>
                            <td class="text-end">S/ {{ number_format($sale->igv, 2) }}</td>
                            <td class="text-end">S/ {{ number_format($sale->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No hay ventas para los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="4" class="text-end">Totales</td>
                        <td class="text-end">S/ {{ number_format($sales->sum('igv'), 2) }}</td>
                        <td class="text-end">S/ {{ number_format($sales->sum('total'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
    const labels = @json($chart->keys());
    const values = @json($chart->values());

    new Chart(document.getElementById('chartReport'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total vendido (S/)',
                data: values,
                backgroundColor: '#ff0a41'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush
