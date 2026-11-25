<?php

namespace App\Http\Controllers;

use App\Models\Categorias;
use App\Models\Medicamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MedicineController extends Controller
{
    const LOW_STOCK = 10;

    // Listado de medicamentos con búsqueda y filtros
    public function index(Request $request)
    {
        $medicines = Medicamento::with('categoria')
            ->when($request->search, function ($query,$search) {
                $query->where('nombre', 'like', "\%{$search}%");
            })
            ->when($request->categoria_id, function ($query,$categoriaId) {
                $query->where('categoria_id',$categoriaId);
            })
            ->when($request->boolean('low_stock'), function ($query) {$query->where('stock', '<=', self::LOW_STOCK);
            })
            ->orderBy('nombre')
            ->get();

        $categories = Categorias::orderBy('nombre')->get();$lowStock = self::LOW_STOCK;

        return view('management.medicines.index', compact('medicines', 'categories', 'lowStock'));
    }

    // Formulario para crear
    public function create()
    {
        $categories = Categorias::orderBy('nombre')->get();

        return view('management.medicines.create', compact('categories'));
    }

    // Guardar nuevo medicamento
    public function store(Request $request)
    {
        $validated =$request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombre'       => 'required|max:120',
            'precio'       => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] =$request->file('image')->store('medicines', 'public');
        }

        Medicamento::create($validated);

        return to_route('medicines.index')->with('ok', 'Medicamento registrado');
    }

    // Formulario para editar
    public function edit(Medicamento $medicine)
    {
        $categories = Categorias::orderBy('nombre')->get();

        return view('management.medicines.edit', compact('medicine', 'categories'));
    }

    // Actualizar medicamento
    public function update(Request $request, Medicamento$medicine)
    {
        $validated =$request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombre'       => 'required|max:120',
            'precio'       => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($medicine->image) {
                Storage::disk('public')->delete($medicine->image);
            }
            $validated['image'] =$request->file('image')->store('medicines', 'public');
        } else {
            unset($validated['image']);
        }

        $medicine->update($validated);

        return to_route('medicines.index')->with('ok', 'Medicamento actualizado');
    }

    // Eliminar medicamento (solo si no se ha vendido)
    public function destroy(Medicamento $medicine)
    {
        if ($medicine->detallesVenta()->exists()) {
            return to_route('medicines.index')
                ->with('error', 'No se puede eliminar el medicamento porque tiene ventas registradas');
        }

        if ($medicine->image) {
            Storage::disk('public')->delete($medicine->image);
        }

        $medicine->delete();

        return to_route('medicines.index')->with('ok', 'Medicamento eliminado');
    }
}