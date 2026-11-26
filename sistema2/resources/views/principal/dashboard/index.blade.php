@extends('template.administrador')

@section('title', 'Dashboard')
@section('name_section', 'Dashboard')
@section('description', 'Resumen de estadísticas')
@section('item_section', 'Inicio')
@section('route_section', route('dashboard.index'))
@section('section', 'Dashboard')
@section('module', 'Principal')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100">
            <div class="card-body">
                <p class="text-secondary text-uppercase small fw-semibold mb-2">Ventas de hoy</p>
                <h3 class="mb-0">S/ {{ number_format($ventasHoy, 2, '.', ',') }}</h3>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100">
            <div class="card-body">
                <p class="text-secondary text-uppercase small fw-semibold mb-2">Ventas del mes</p>
                <h3 class="mb-0">S/ {{ number_format($ventasMes, 2, '.', ',') }}</h3>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100">
            <div class="card-body">
                <p class="text-secondary text-uppercase small fw-semibold mb-2">Total de clientes</p>
                <h3 class="mb-0">{{ $totalClientes }}</h3>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card bg-white border-0 shadow-sm border-5 border-top" style="border-top-color: #ff0a41 !important;">
            <div class="card-body">
                <p class="text-secondary text-uppercase small fw-semibold mb-2">Stock bajo</p>
                <h3 class="mb-0">{{ $medicamentosStockBajo }}</h3>
                <small class="text-secondary">Medicamentos con 10 unidades o menos</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card bg-white border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h5 class="mb-0">Ventas de los últimos 7 días</h5>
            </div>
            <div class="card-body">
                <canvas id="ventasSieteDias" height="140"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card bg-white border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h5 class="mb-0">Ventas por categoría</h5>
            </div>
            <div class="card-body">
                <canvas id="ventasPorCategoria" height="220"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card bg-white border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h5 class="mb-0">Top 5 medicamentos del mes</h5>
            </div>
            <div class="card-body">
                <canvas id="topMedicamentos" height="180"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
    const colores = ['#ff0a41', '#323232', '#4b5563'];

    const fechasUltimosDias = @json($fechasUltimosDias);
    const totalesUltimosDias = @json($totalesUltimosDias);
    const categorias = @json($categorias);
    const totalesCategorias = @json($totalesCategorias);
    const nombresMedicamentos = @json($nombresMedicamentos);
    const unidadesMedicamentos = @json($unidadesMedicamentos);

    new Chart(document.getElementById('ventasSieteDias'), {
        type: 'line',
        data: {
            labels: fechasUltimosDias,
            datasets: [{
                label: 'Ventas (S/)',
                data: totalesUltimosDias,
                borderColor: '#ff0a41',
                backgroundColor: 'rgba(255, 10, 65, 0.12)',
                borderWidth: 3,
                pointBackgroundColor: '#323232',
                pointBorderColor: '#323232',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => 'S/ ' + Number(context.raw).toFixed(2)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => 'S/ ' + value
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('ventasPorCategoria'), {
        type: 'doughnut',
        data: {
            labels: categorias,
            datasets: [{
                data: totalesCategorias,
                backgroundColor: categorias.map((_, index) => colores[index % colores.length]),
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (context) => context.label + ': S/ ' + Number(context.raw).toFixed(2)
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('topMedicamentos'), {
        type: 'bar',
        data: {
            labels: nombresMedicamentos,
            datasets: [{
                label: 'Unidades vendidas',
                data: unidadesMedicamentos,
                backgroundColor: '#ff0a41',
                borderColor: '#323232',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
</script>
@endpush