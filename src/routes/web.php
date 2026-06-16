<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CardTypeBannerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\MetricsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Tarjeta pública (sin auth)
Route::get('/card/{slug}', [CardController::class, 'show'])->name('card.show');

// Auth
Route::get('/login', function () {
    return Auth::check() ? redirect()->route('admin.dashboard') : view('auth.login');
})->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout');

// Raíz → redirige
Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'admin.dashboard' : 'login');
});

// Panel de administración (protegido)
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Sucursales
    Route::resource('branches', BranchController::class);

    // Empleados
    Route::resource('employees', EmployeeController::class);
    Route::get('employees/{employee}/qr', [EmployeeController::class, 'downloadQr'])->name('employees.qr');
    Route::get('employees/{employee}/qr-preview', [EmployeeController::class, 'qrPreview'])->name('employees.qr-preview');

    // Banners predeterminados
    Route::get('card-banners', [CardTypeBannerController::class, 'index'])->name('card-banners.index');
    Route::post('card-banners', [CardTypeBannerController::class, 'update'])->name('card-banners.update');

    // Métricas
    Route::get('metrics', [MetricsController::class, 'index'])->name('metrics');
});
