{{-- Hereda la plantilla padre: menú, encabezado y estilos --}}
@extends('template.administrador')

@section('title', 'Editar venta')
@section('name_section', 'Editar venta')
@section('description', 'Modificar los datos y productos de la venta')
@section('item_section', 'Editar')
@section('route_section', route('sales.index'))
@section('section', 'Ventas')
@section('module', 'Gestión')

@section('content')
    @include('management.sales.form')
@endsection
