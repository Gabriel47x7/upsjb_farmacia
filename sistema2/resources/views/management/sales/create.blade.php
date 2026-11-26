{{-- Hereda la plantilla padre: menú, encabezado y estilos --}}
@extends('template.administrador')

{{-- Rellena los huecos (@yield) de la plantilla --}}
@section('title', 'Ventas')
@section('name_section', 'Ventas')
@section('description', 'Registrar nueva venta')
@section('item_section', 'Nueva')
@section('route_section', route('sales.index'))
@section('section', 'Ventas')
@section('module', 'Gestión')

{{-- Formulario compartido para registrar y editar ventas --}}
@section('content')
    @include('management.sales.form')
@endsection
