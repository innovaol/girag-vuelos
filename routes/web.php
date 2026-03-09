<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\FlightsIndex;
use App\Livewire\FlightsCreate;
use App\Livewire\FlightsEdit;
use App\Livewire\AirlinesIndex;
use App\Livewire\AircraftIndex;
use App\Livewire\DocumentTypesIndex;
use App\Livewire\UsersIndex;
use App\Livewire\DocumentView;
use App\Livewire\OdooImport;

// ── RUTA DE DIAGNÓSTICO TEMPORAL (borrar después) ──────────────────────────────
Route::get('/debug-zip', function () {
    return response()->json([
        'zip'      => class_exists('ZipArchive'),
        'php_ini'  => php_ini_loaded_file(),
        'ext_list' => implode(', ', get_loaded_extensions()),
        'php_ver'  => PHP_VERSION,
    ]);
});

// ─── Auth ──────────────────────────────────────────────────────────────────────
Route::get('/login', App\Livewire\Auth\Login::class)->name('login')->middleware('guest');
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// ─── Protected ─────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', Dashboard::class)->name('dashboard');

    // Vuelos
    Route::get('/vuelos', FlightsIndex::class)->name('flights.index');
    Route::get('/vuelos/nuevo', FlightsCreate::class)->name('flights.create')->middleware('can:create-flight');
    Route::get('/vuelos/{flight}/editar', FlightsEdit::class)->name('flights.edit')->middleware('can:edit-flight');

    // Documentos (descarga / vista previa)
    Route::get('/documentos/{document}/ver', DocumentView::class)->name('documents.view');

    // Catálogos (solo admin_vuelos)
    Route::middleware('can:manage-catalogs')->group(function () {
        Route::get('/catalogos/aerolineas',       AirlinesIndex::class)->name('airlines.index');
        Route::get('/catalogos/aeronaves',         AircraftIndex::class)->name('aircraft.index');
        Route::get('/catalogos/tipos-documento',   DocumentTypesIndex::class)->name('document-types.index');
        Route::get('/catalogos/usuarios',          UsersIndex::class)->name('users.index');
    });

    // Importar desde Odoo (billing supervisor o admin)
    Route::get('/importar/odoo', OdooImport::class)
        ->name('odoo.import')
        ->middleware('can:export-sage');

    // API para carga dinámica de aeronaves por aerolínea (para el formulario de vuelos)
    Route::get('/api/aeronaves/{airline}', function (\App\Models\Airline $airline) {
        $aircrafts = $airline->aircraft()->active()->orderBy('registration_number')->get(['id', 'registration_number']);
        return response()->json(['aircrafts' => $aircrafts]);
    })->name('api.aircrafts');
});
