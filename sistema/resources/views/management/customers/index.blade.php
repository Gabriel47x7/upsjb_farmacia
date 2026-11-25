@extends('template.administrador')

@section('title', 'Clientes')
@section('name_section', 'Clientes')
@section('description', 'Listado de registros de clientes')
@section('item_section', 'Inicio')
@section('route_section', route('customers.index'))
@section('section', 'Clientes')
@section('module', 'Gestión')

@section('content')
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-header bg-white">
        <div class="row">
            <div class="col-12 col-md-3 mb-2 mb-md-0">
                <a href="{{ route('customers.create') }}" class="btn btn-primary rounded-3 text-uppercase text-white w-100">
                    <i class="bi bi-plus-lg me-2"></i><span class="small fw-medium">Nuevo registro</span>
                </a>
            </div>

            <div class="col-12 col-md-9">
                <form action="{{ route('customers.index') }}" method="GET" class="row g-2 align-items-center justify-content-end">
                    <div class="col-12 col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o DNI"
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-12 col-md-4 d-flex gap-1">
                        <button type="submit" class="btn btn-outline-primary w-100" title="Buscar">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary w-100" title="Limpiar búsqueda">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card-body">
        @include('partials.alertas')

        <h6 class="text-uppercase small text-muted mb-3">Total de registros encontrados: {{ $customers->count() }}</h6>

        <table id="display" class="table table-sm table-hover w-100 bg-white">
            <thead>
                <tr>
                    <th class="text-center align-middle text-uppercase small">N°</th>
                    <th class="text-center align-middle text-uppercase small">DNI</th>
                    <th class="text-center align-middle text-uppercase small">Nombre</th>
                    <th class="text-center align-middle text-uppercase small">Teléfono</th>
                    <th class="text-center align-middle text-uppercase small">Edad</th>
                    <th class="text-center align-middle text-uppercase small">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td class="text-center align-middle">{{ $loop->iteration }}</td>
                        <td class="text-center align-middle">{{ $customer->dni }}</td>
                        <td class="text-start align-middle">{{ $customer->nombre }}</td>
                        <td class="text-center align-middle">{{ $customer->telefono ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $customer->edad ?? '-' }}</td>
                        <td class="text-center align-middle">
                            <div class="dropdown">
                                <button class="btn btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('customers.edit', $customer) }}">Editar</a>
                                    </li>
                                    <li>
                                        <form action="{{ route('customers.destroy', $customer) }}" method="POST"
                                            onsubmit="return confirm('¿Seguro que desea eliminar este cliente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">Eliminar</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">No se encontraron clientes</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection