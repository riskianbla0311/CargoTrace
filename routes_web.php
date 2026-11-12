<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    DashboardController,
    ShipmentController,
    AdminController
};

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    // Public customer registration
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/create', [ShipmentController::class, 'create'])
        ->middleware('role:admin,operator')->name('shipments.create');
    Route::post('/shipments', [ShipmentController::class, 'store'])
        ->middleware('role:admin,operator')->name('shipments.store');
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
    Route::post('/shipments/{shipment}/tracking', [ShipmentController::class, 'tracking'])
        ->middleware('role:admin,operator')->name('shipments.tracking');
    Route::post('/shipments/{shipment}/exceptions', [ShipmentController::class, 'exception'])
        ->middleware('role:admin,operator')->name('shipments.exceptions');

    Route::get('/exceptions', [ShipmentController::class, 'exceptions'])
        ->middleware('role:admin,operator')->name('exceptions.index');
    Route::patch('/exceptions/{exception}', [ShipmentController::class, 'updateException'])
        ->middleware('role:admin,operator')->name('exceptions.update');

    Route::get('/notifications', [DashboardController::class, 'notifications'])
        ->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [DashboardController::class, 'readNotification'])
        ->name('notifications.read');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::patch('/users/{user}/role', [AdminController::class, 'updateRole'])->name('users.role');
        Route::get('/carriers', [AdminController::class, 'carriers'])->name('carriers');
        Route::post('/carriers', [AdminController::class, 'storeCarrier'])->name('carriers.store');
        Route::get('/locations', [AdminController::class, 'locations'])->name('locations');
        Route::post('/locations', [AdminController::class, 'storeLocation'])->name('locations.store');
        Route::get('/audit-logs', [AdminController::class, 'audit'])->name('audit');
    });
});
