<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CustomerController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
Route::resource('customers', CustomerController::class)->except('show');
Route::resource('categorias', CategoryController::class)->names('categorias');
Route::resource('medicines', MedicineController::class)->except('show');
Route::resource('roles', RoleController::class)->names('roles');