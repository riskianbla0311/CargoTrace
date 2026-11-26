
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\ReportsController;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/test-auth', function () {
    return response()->json([
        'authenticated' => auth()->check(),
        'user_id' => auth()->id(),
        'email' => auth()->user()?->email,
        'role' => auth()->user()?->role,
        'session_id' => session()->getId(),
    ]);
});

    // SHIPMENTS
    Route::get('/shipments', [ShipmentController::class, 'index'])
        ->name('shipments.index');

    Route::get('/shipments/create', [ShipmentController::class, 'create'])
        ->middleware('role:admin,operator')
        ->name('shipments.create');

    Route::post('/shipments', [ShipmentController::class, 'store'])
        ->middleware('role:admin,operator')
        ->name('shipments.store');

    Route::get('/shipments/{shipment}/edit', [ShipmentController::class, 'edit'])
        ->middleware('role:admin,operator')
        ->name('shipments.edit');

    Route::patch('/shipments/{shipment}/archive', [ShipmentController::class, 'archive'])
        ->middleware('role:admin,operator')
        ->name('shipments.archive');

    // Archived shipments: admin and operator can view; only admin can restore.
    Route::get('/shipments/archived', [ShipmentController::class, 'archived'])
        ->middleware('role:admin,operator')
        ->name('shipments.archived');

    Route::patch('/shipments/{shipment}/restore', [ShipmentController::class, 'restore'])
        ->middleware('role:admin')
        ->withTrashed()
        ->name('shipments.restore');

    Route::put('/shipments/{shipment}', [ShipmentController::class, 'update'])
        ->middleware('role:admin,operator')
        ->name('shipments.update');

    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])
        ->name('shipments.show');

    // Lightweight polling endpoint: reads saved events, does not simulate GPS.
    Route::get('/shipments/{shipment}/tracking/live', [ShipmentController::class, 'liveTracking'])
        ->name('shipments.tracking.live');

    // TRACKING UPDATE
    Route::post('/shipments/{shipment}/tracking', [ShipmentController::class, 'tracking'])
        ->middleware('role:admin,operator')
        ->name('shipments.tracking');

    // MULTIMODAL SHIPMENT LEGS
    Route::post('/shipments/{shipment}/legs', [ShipmentController::class, 'storeLeg'])
        ->middleware('role:admin,operator')
        ->name('shipments.legs.store');

    Route::patch('/shipments/{shipment}/legs/{leg}', [ShipmentController::class, 'updateLeg'])
        ->middleware('role:admin,operator')
        ->name('shipments.legs.update');

    Route::delete('/shipments/{shipment}/legs/{leg}', [ShipmentController::class, 'destroyLeg'])
        ->middleware('role:admin,operator')
        ->name('shipments.legs.destroy');

    // SHIPMENT EXCEPTIONS
    Route::post('/shipments/{shipment}/exceptions', [ShipmentController::class, 'exception'])
        ->middleware('role:admin,operator')
        ->name('shipments.exceptions');

    // TRACKING PAGE
    Route::get('/tracking', [TrackingController::class, 'index'])
        ->name('tracking.index');

    // EXCEPTIONS
    Route::get('/exceptions', [ShipmentController::class, 'exceptions'])
        ->middleware('role:admin,operator')
        ->name('exceptions.index');

    Route::patch('/exceptions/{exception}', [ShipmentController::class, 'updateException'])
        ->middleware('role:admin,operator')
        ->name('exceptions.update');

    // REPORTS
    Route::get('/reports', [ReportsController::class, 'index'])
        ->middleware('role:admin,operator')
        ->name('reports.index');

    // NOTIFICATIONS
    Route::get('/notifications', [DashboardController::class, 'notifications'])
        ->name('notifications.index');

    Route::patch('/notifications/{notification}/read', [DashboardController::class, 'readNotification'])
        ->name('notifications.read');

    // ADMIN
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('/users', [AdminController::class, 'users'])
                ->name('users');

            Route::post('/users', [AdminController::class, 'storeUser'])
                ->name('users.store');

            Route::put('/users/{user}', [AdminController::class, 'updateUser'])
                ->name('users.update');

            Route::patch('/users/{user}/role', [AdminController::class, 'updateRole'])
                ->name('users.role');

            Route::get('/carriers', [AdminController::class, 'carriers'])
                ->name('carriers');

            Route::post('/carriers', [AdminController::class, 'storeCarrier'])
                ->name('carriers.store');

            Route::patch('/carriers/{carrier}', [AdminController::class, 'updateCarrier'])
                ->name('carriers.update');

            Route::get('/locations', [AdminController::class, 'locations'])
                ->name('locations');

            Route::post('/locations', [AdminController::class, 'storeLocation'])
                ->name('locations.store');

            Route::patch('/locations/{location}', [AdminController::class, 'updateLocation'])
                ->name('locations.update');

            Route::patch('/locations/{location}/toggle-status', [AdminController::class, 'toggleLocationStatus'])
                ->name('locations.toggle-status');

            Route::get('/audit-logs', [AdminController::class, 'audit'])
                ->name('audit');
        });
});
