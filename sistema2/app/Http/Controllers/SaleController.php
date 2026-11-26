<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    // Listado de ventas (con cliente y vendedor cargados)
    public function index()
    {
        $sales = Sale::with(['customer', 'user'])->latest('date')->get();

        return view('management.sales.index', compact('sales'));
    }

    // Formulario para registrar una venta
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $medicines = Medicine::where('stock', '>', 0)->orderBy('name')->get();
        $medicinePrices = [];
        $stockForSale = [];

        return view('management.sales.create', compact(
            'customers',
            'medicines',
            'medicinePrices',
            'stockForSale',
        ));
    }

    // Mostrar el formulario de edición y permitir conservar el precio histórico
    public function edit(Sale $sale)
    {
        $sale->load(['details', 'user']);
        $customers = Customer::orderBy('name')->get();

        $saleMedicineIds = $sale->details->pluck('medicine_id');
        $submittedItems = old('items', []);
        $submittedItems = is_array($submittedItems)
            ? array_filter($submittedItems, fn ($item) => is_array($item))
            : [];
        $submittedMedicineIds = collect($submittedItems)->pluck('medicine_id')->filter();
        $medicineIds = $saleMedicineIds->merge($submittedMedicineIds)->unique()->values();

        $medicines = Medicine::query()
            ->where('stock', '>', 0)
            ->orWhereIn('id', $medicineIds)
            ->orderBy('name')
            ->get();

        $medicinePrices = $sale->details->pluck('price_unit', 'medicine_id')->all();
        $stockForSale = $sale->details
            ->groupBy('medicine_id')
            ->map(fn ($details) => (int) $details->sum('amount'))
            ->all();

        return view('management.sales.edit', compact(
            'sale',
            'customers',
            'medicines',
            'medicinePrices',
            'stockForSale',
        ));
    }

    // Guardar la venta con sus detalles
    public function store(Request $request)
    {
        $validated = $this->validateSaleData($request);

        // Transacción: si algo falla, no se guarda nada
        $sale = DB::transaction(function () use ($validated) {
            // 1. Crear la venta (el total se calcula después)
            $sale = Sale::create([
                'customer_id' => $validated['customer_id'],
                'user_id' => auth()->id(),
                'date' => now(),
                'total' => 0,
                'igv' => 0,
            ]);

            $total = 0;

            // 2. Registrar cada medicamento vendido
            foreach ($validated['items'] as $item) {
                // lockForUpdate(): bloquea la fila hasta terminar, para que dos ventas a la vez no vendan el mismo stock
                $medicine = Medicine::lockForUpdate()->findOrFail($item['medicine_id']);

                if ($medicine->stock < $item['amount']) {
                    // Lanzar la excepción deshace todo lo guardado en la transacción (rollback)
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$medicine->name} (disponible: {$medicine->stock}).",
                    ]);
                }

                // El precio se toma de la base de datos, no del formulario
                $subtotal = $medicine->price * $item['amount'];

                $sale->details()->create([
                    'medicine_id' => $medicine->id,
                    'amount' => $item['amount'],
                    'price_unit' => $medicine->price,
                    'subtotal' => $subtotal,
                ]);

                // 3. Descontar el stock
                $medicine->decrement('stock', $item['amount']);

                $total += $subtotal;
            }

            // 4. Guardar total e IGV (el precio ya incluye el 18% de IGV)
            $sale->update([
                'total' => $total,
                'igv' => round($total * 18 / 118, 2),
            ]);

            return $sale;
        });

        return to_route('sales.show', $sale)->with('ok', 'Venta registrada');
    }

    // Actualizar una venta y ajustar el inventario sin perder los precios históricos
    public function update(Request $request, Sale $sale)
    {
        $validated = $this->validateSaleData($request);

        DB::transaction(function () use ($sale, $validated) {
            $lockedSale = Sale::query()
                ->whereKey($sale->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $oldDetails = $lockedSale->details()->lockForUpdate()->get();
            $historicalPrices = $oldDetails->pluck('price_unit', 'medicine_id');

            // Bloquear todos los medicamentos en el mismo orden para evitar conflictos entre ventas
            $medicineIds = collect($validated['items'])
                ->pluck('medicine_id')
                ->merge($oldDetails->pluck('medicine_id'))
                ->unique()
                ->sort()
                ->values();
            $medicines = Medicine::query()
                ->whereIn('id', $medicineIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Devolver primero el stock de la venta original
            foreach ($oldDetails as $detail) {
                $medicine = $medicines->get($detail->medicine_id);

                if (! $medicine) {
                    throw ValidationException::withMessages([
                        'items' => 'No se pudo devolver el stock de un medicamento de esta venta.',
                    ]);
                }

                $medicine->increment('stock', $detail->amount);
            }

            $lockedSale->details()->delete();
            $total = 0;

            // Guardar los nuevos detalles y descontar el stock dentro de la misma transacción
            foreach ($validated['items'] as $item) {
                $medicine = $medicines->get((int) $item['medicine_id']);

                if (! $medicine) {
                    throw ValidationException::withMessages([
                        'items' => 'El medicamento seleccionado ya no está disponible. Actualiza la página e inténtalo nuevamente.',
                    ]);
                }

                if ($medicine->stock < $item['amount']) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$medicine->name} (disponible: {$medicine->stock}).",
                    ]);
                }

                $unitPrice = $historicalPrices->has($medicine->id)
                    ? (float) $historicalPrices->get($medicine->id)
                    : (float) $medicine->price;
                $subtotal = round($unitPrice * $item['amount'], 2);

                $lockedSale->details()->create([
                    'medicine_id' => $medicine->id,
                    'amount' => $item['amount'],
                    'price_unit' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $medicine->decrement('stock', $item['amount']);
                $total += $subtotal;
            }

            $lockedSale->update([
                'customer_id' => $validated['customer_id'],
                'total' => $total,
                'igv' => round($total * 18 / 118, 2),
            ]);
        });

        return to_route('sales.show', $sale)->with('ok', 'Venta actualizada correctamente');
    }

    // Ver el detalle de una venta
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'details.medicine']);

        return view('management.sales.show', compact('sale'));
    }

    // Anular venta: devuelve el stock y la elimina
    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            foreach ($sale->details as $detail) {
                Medicine::where('id', $detail->medicine_id)->increment('stock', $detail->amount);
            }

            // Los detalles se borran solos (cascadeOnDelete en la migración)
            $sale->delete();
        });

        return to_route('sales.index')->with('ok', 'Venta anulada y stock devuelto');
    }

    // Validar los datos comunes al registrar o editar una venta
    private function validateSaleData(Request $request): array
    {
        return $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|distinct|exists:medicines,id',
            'items.*.amount' => 'required|integer|min:1',
        ], [
            'items.required' => 'Debe agregar al menos un medicamento.',
            'items.*.medicine_id.distinct' => 'No repita el mismo medicamento, aumente la cantidad.',
        ]);
    }
}
