@extends('template.administrador')

@section('title', 'CATEGORIES')
@section('name_section', 'CATEGORIAS')
@section('description', 'Listado de categorias')
@section('item_section', 'Inicio')
@section('section', 'categorias')
@section('module', 'configuraciones')

@section('content')
<div class="card bg-white border-0 shadow-sm border-0 border-top border-secondary">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold text-dark">Listado de Categorías</h5>
            <a href="{{ route('categorias.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-plus-lg"></i> Nueva Categoría
            </a>
        </div>
        <table id="display" class="table table-sm table-hover w-100 bg-white">
            <thead class="table-light">
                <tr>
                    <th class="text-center">#</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $contador = 1;
                @endphp
                @foreach($categorias as $categoria)
                <tr>
                    <td class="text-center align-middle">{{ $contador++ }}</td>
                    <td class="text-start align-middle">{{ $categoria->nombre }}</td>
                    <td class="text-start align-middle">{{ $categoria->descripcion }}</td>
                    <td class="text-center align-middle">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('categorias.edit', $categoria) }}">Editar</a>
                                </li>
                                <li>
                                    <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" class="d-inline" 
                                    onsubmit="return confirm('¿Estás seguro de eliminar este registro?')">
                                     @csrf
                                      @method('DELETE')
                                          <button type="submit" class="dropdown-item text-danger">Eliminar</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection