<?php

namespace App\Http\Controllers;

use App\Models\Categorias;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = Categorias::orderBy('nombre')->get();
            
        return view('settings.categorias.index', compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
public function create()
    {
        return view('settings.categorias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    // Validar los campos según tu base de datos
    $request->validate([
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
    ]);

    // Guardar en la base de datos
    Categorias::create([
        'nombre' => $request->nombre,
        'descripcion' => $request->descripcion,
    ]);

    // Redireccionar al listado con un mensaje de éxito
    return redirect()->route('categorias.index')->with('success', 'Categoría creada con éxito.');
}

    /**
     * Display the specified resource.
     */
    public function show(Categorias $categoria)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categorias $categoria)
    {
        return view('settings.categorias.edit', compact('categoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categorias $categoria)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categorias $categoria)
    {
        //
    }
}