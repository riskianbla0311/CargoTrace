
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| LOGIN & REGISTER
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    // Halaman Login
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    // Proses Login
    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');

    // Halaman Register Customer
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    // Proses Register Customer
    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');
});


/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED PAGES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');


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

    Route::put('/shipments/{shipment}', [ShipmentController::class, 'update'])
        ->middleware('role:admin,operator')
        ->name('shipments.update');

    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])
        ->name('shipments.show');

    // TRACKING
    Route::post('/shipments/{shipment}/tracking', [ShipmentController::class, 'tracking'])
        ->middleware('role:admin,operator')
        ->name('shipments.tracking');

    // SHIPMENT EXCEPTIONS
    Route::post('/shipments/{shipment}/exceptions', [ShipmentController::class, 'exception'])
        ->middleware('role:admin,operator')
        ->name('shipments.exceptions');


    /*
    |--------------------------------------------------------------------------
    | EXCEPTIONS
    |--------------------------------------------------------------------------
    */

    Route::get('/exceptions', [ShipmentController::class, 'exceptions'])
        ->middleware('role:admin,operator')
        ->name('exceptions.index');

    Route::patch('/exceptions/{exception}', [ShipmentController::class, 'updateException'])
        ->middleware('role:admin,operator')
        ->name('exceptions.update');


    /*
    |--------------------------------------------------------------------------
    | NOTIFICATIONS
    |--------------------------------------------------------------------------
    */

    Route::get('/notifications', [DashboardController::class, 'notifications'])
        ->name('notifications.index');

    Route::patch('/notifications/{notification}/read', [DashboardController::class, 'readNotification'])
        ->name('notifications.read');


    /*
    |--------------------------------------------------------------------------
    | ADMIN MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            // USER MANAGEMENT
            Route::get('/users', [AdminController::class, 'users'])
                ->name('users');
            Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
            Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');

            Route::patch('/users/{user}/role', [AdminController::class, 'updateRole'])
                ->name('users.role');


            // CARRIER MANAGEMENT
            Route::get('/carriers', [AdminController::class, 'carriers'])
                ->name('carriers');

            Route::post('/carriers', [AdminController::class, 'storeCarrier'])
                ->name('carriers.store');
            Route::patch('/carriers/{carrier}', [AdminController::class, 'updateCarrier'])->name('carriers.update');


            // LOCATION MANAGEMENT
            Route::get('/locations', [AdminController::class, 'locations'])
                ->name('locations');

            Route::post('/locations', [AdminController::class, 'storeLocation'])
                ->name('locations.store');
            Route::patch('/locations/{location}', [AdminController::class, 'updateLocation'])->name('locations.update');


            // AUDIT LOGS
            Route::get('/audit-logs', [AdminController::class, 'audit'])
                ->name('audit');

        });

});
