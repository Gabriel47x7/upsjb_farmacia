@php
    $isEditing = isset($sale);
    $defaultItems = $isEditing
        ? $sale->details->map(fn ($detail) => [
            'medicine_id' => $detail->medicine_id,
            'amount' => $detail->amount,
        ])->all()
        : [];
    $submittedItems = old('items', $defaultItems);
    $items = is_array($submittedItems)
        ? array_filter($submittedItems, fn ($item) => is_array($item))
        : $defaultItems;
@endphp

<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-body">
        <form action="{{ $isEditing ? route('sales.update', $sale) : route('sales.store') }}" method="POST">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            {{-- Datos generales de la venta --}}
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label for="customer_id" class="form-label">Cliente</label>
                    <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                        <option value="">-- Seleccione --</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}"
                                @selected(old('customer_id', $isEditing ? $sale->customer_id : '') == $customer->id)>
                                {{ $customer->name }} - DNI {{ $customer->dni }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6 mb-3">
                    <label for="user_id" class="form-label">Vendedor</label>
                    <input type="text" id="user_id" class="form-control"
                        value="{{ $isEditing ? $sale->user->name : auth()->user()->name }}" disabled>
                </div>
            </div>

            <h6 class="text-uppercase small text-muted mt-2 mb-2">Medicamentos</h6>

            {{-- Mostrar errores de productos, cantidades o falta de stock --}}
            @if ($errors->has('items') || $errors->has('items.*'))
                <div class="alert alert-danger py-2">
                    @foreach ($errors->get('items*') as $messages)
                        @foreach ((array) $messages as $message)
                            <div>{{ $message }}</div>
                        @endforeach
                    @endforeach
                </div>
            @endif

            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th class="small text-uppercase">Medicamento</th>
                        <th class="small text-uppercase text-center" style="width: 120px">Cantidad</th>
                        <th class="small text-uppercase text-end" style="width: 120px">Precio</th>
                        <th class="small text-uppercase text-end" style="width: 120px">Subtotal</th>
                        <th style="width: 50px"></th>
                    </tr>
                </thead>
                <tbody id="items">
                    @foreach ($items as $i => $item)
                        @include('management.sales.item-row', ['index' => $i, 'item' => $item])
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-semibold">Total (incluye IGV)</td>
                        <td class="text-end fw-bold" id="total">S/ 0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <button type="button" id="add-item" class="btn btn-outline-primary btn-sm mb-4">
                <i class="bi bi-plus-lg me-1"></i>Agregar medicamento
            </button>

            <div>
                <button type="submit" class="btn btn-primary text-white">
                    {{ $isEditing ? 'Guardar cambios' : 'Registrar venta' }}
                </button>
                <a href="{{ $isEditing ? route('sales.show', $sale) : route('sales.index') }}"
                    class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

{{-- Plantilla de una fila vacía para agregar productos con JavaScript --}}
<template id="item-template">
    @include('management.sales.item-row', ['index' => '__INDEX__', 'item' => []])
</template>

@push('scripts')
<script>
    const tbody = document.getElementById('items');
    const template = document.getElementById('item-template').innerHTML;
    let index = {{ $items ? max(array_keys($items)) + 1 : 0 }};

    function recalculate() {
        let total = 0;
        tbody.querySelectorAll('tr').forEach(row => {
            const option = row.querySelector('select').selectedOptions[0];
            const price = parseFloat(option?.dataset.price || 0);
            const amount = parseInt(row.querySelector('input').value || 0);
            const subtotal = price * amount;

            row.querySelector('.price').textContent = 'S/ ' + price.toFixed(2);
            row.querySelector('.subtotal').textContent = 'S/ ' + subtotal.toFixed(2);
            total += subtotal;
        });
        document.getElementById('total').textContent = 'S/ ' + total.toFixed(2);
    }

    document.getElementById('add-item').addEventListener('click', () => {
        tbody.insertAdjacentHTML('beforeend', template.replaceAll('__INDEX__', index++));
    });

    tbody.addEventListener('click', event => {
        if (event.target.closest('.remove-item')) {
            event.target.closest('tr').remove();
            recalculate();
        }
    });

    tbody.addEventListener('input', recalculate);

    if (index === 0) document.getElementById('add-item').click();
    recalculate();
</script>
@endpush
