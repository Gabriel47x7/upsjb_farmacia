<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Cliente::when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('dni', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->get();

        return view('management.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('management.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'   => 'required|string|max:150',
            'dni'      => 'required|string|size:8|unique:clientes,dni',
            'telefono' => 'nullable|string|max:15',
            'edad'     => 'nullable|integer|min:0|max:120',
        ]);

        Cliente::create($validated);

        return to_route('customers.index')->with('ok', 'Cliente registrado');
    }

    public function edit(Cliente $customer)
    {
        return view('management.customers.edit', compact('customer'));
    }

    public function update(Request $request, Cliente $customer)
    {
        $validated = $request->validate([
            'nombre'   => 'required|string|max:150',
            'dni'      => 'required|string|size:8|unique:clientes,dni,' . $customer->id,
            'telefono' => 'nullable|string|max:15',
            'edad'     => 'nullable|integer|min:0|max:120',
        ]);

        $customer->update($validated);

        return to_route('customers.index')->with('ok', 'Cliente actualizado');
    }

    public function destroy(Cliente $customer)
    {
        if ($customer->ventas()->exists()) {
            return to_route('customers.index')
                ->with('error', 'No se puede eliminar el cliente porque tiene ventas registradas');
        }

        $customer->delete();

        return to_route('customers.index')->with('ok', 'Cliente eliminado');
    }
}