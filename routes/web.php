<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\GlobalSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Customer Management
    Route::resource('customers', CustomerController::class);

    // Device Management & Repair Logs
    Route::resource('devices', DeviceController::class);
    Route::post('/devices/{id}/repair-log', [DeviceController::class, 'addRepairLog'])->name('devices.repair-log');
    Route::post('/devices/{id}/mark-delivered', [DeviceController::class, 'markAsDelivered'])->name('devices.mark-delivered');
    Route::patch('/devices/{id}/status', [DeviceController::class, 'updateStatus'])->name('devices.update-status');

    // Billing & Financial Transactions
    Route::resource('bills', BillController::class);
    Route::post('/bills/{id}/add-transaction', [BillController::class, 'addTransaction'])->name('bills.add-transaction');
    Route::get('/due-list', [App\Http\Controllers\BillController::class, 'dueList'])->name('bills.due-list');
    Route::post('/bills/{id}/adjust', [App\Http\Controllers\BillController::class, 'adjustBill'])->name('bills.adjust');
    Route::get('bills/{bill}/print', [App\Http\Controllers\BillController::class, 'print'])->name('bills.print');

    // Expense Management
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class)->only(['index', 'store', 'destroy']);

    // Inventory Management
    Route::resource('inventory', InventoryController::class);

    // Global Search
    Route::get('/global-search', [GlobalSearchController::class, 'search'])->name('global.search');
});

// Public Device Tracking Route (No Auth Required)
Route::get('/track/{code}', [\App\Http\Controllers\DeviceController::class, 'track'])->name('track.device');

require __DIR__.'/auth.php';
