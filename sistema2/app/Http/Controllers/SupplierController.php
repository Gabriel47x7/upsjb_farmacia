<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::latest()->get();

        return view('management.suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('management.suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:100',
            'ruc' => 'required|digits:11|unique:suppliers,ruc',
            'phone' => 'nullable|digits:9',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|max:150',
        ]);

        Supplier::create($validated);

        return to_route('suppliers.index')->with('ok', 'Proveedor registrado');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        return view('management.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|max:100',
            'ruc' => ['required', 'digits:11', Rule::unique('suppliers', 'ruc')->ignore($supplier->id)],
            'phone' => 'nullable|digits:9',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|max:150',
        ]);

        $supplier->update($validated);

        return to_route('suppliers.index')->with('ok', 'Proveedor actualizado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return to_route('suppliers.index')->with('ok', 'Proveedor eliminado');
    }
}
