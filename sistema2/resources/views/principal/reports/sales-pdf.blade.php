<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de ventas</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; color: #323232; font-size: 11px; }
        h1 { margin: 0 0 8px; color: #323232; font-size: 21px; }
        p { margin: 4px 0; }
        .muted { color: #4b5563; }
        .filters { margin: 14px 0; padding: 9px; border: 1px solid #d1d5db; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { padding: 7px 6px; border: 1px solid #d1d5db; }
        th { background: #f3f4f6; text-align: left; }
        .number { text-align: right; white-space: nowrap; }
        .center { text-align: center; }
        tfoot td { font-weight: bold; background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Reporte de ventas</h1>
    <p class="muted">
        Rango de fechas:
        {{ $filters['from'] ?? 'Sin límite inicial' }} — {{ $filters['to'] ?? 'Sin límite final' }}
    </p>

    <div class="filters">
        <strong>Filtros aplicados:</strong>
        @forelse ($appliedFilters as $label => $value)
            <span>{{ $label }}: {{ $value }}@if (!$loop->last); @endif</span>
        @empty
            <span>Ninguno</span>
        @endforelse
    </div>

    <table>
        <thead>
            <tr>
                <th class="center">N.°</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th class="number">IGV</th>
                <th class="number">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $sale)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $sale->date->format('d/m/Y H:i') }}</td>
                    <td>{{ $sale->customer->name }}</td>
                    <td>{{ $sale->user->name }}</td>
                    <td class="number">S/ {{ number_format($sale->igv, 2) }}</td>
                    <td class="number">S/ {{ number_format($sale->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">No hay ventas para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="number">Totales</td>
                <td class="number">S/ {{ number_format($totalIgv, 2) }}</td>
                <td class="number">S/ {{ number_format($totalSales, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <p class="muted" style="margin-top: 14px;">
        Fecha de generación: {{ $generatedAt->format('d/m/Y H:i') }}
    </p>
</body>
</html>
