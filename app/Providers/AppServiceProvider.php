<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ─── Gates (espejando los permisos del legacy Django) ──────────────

        // Cualquier usuario activo puede crear / editar vuelos pendientes
        Gate::define('create-flight', fn (User $user) => $user->canCreateFlight());
        Gate::define('edit-flight',   fn (User $user) => $user->canCreateFlight());

        // Solo supervisores de vuelos pueden aprobar (VoBo)
        Gate::define('approve-flight', fn (User $user) => $user->canApproveFlight());

        // Solo facturadores pueden marcar como facturado
        Gate::define('bill-flight', fn (User $user) => $user->canMarkAsBilled());

        // Solo admin_vuelos puede revertir un vuelo a pendiente
        Gate::define('admin-vuelos', fn (User $user) => $user->canAdminVuelos());

        // Ver vuelo: cualquier usuario activo
        Gate::define('view-flight', fn (User $user) => !$user->is_archived);

        // Eliminar vuelo: solo quien lo creó (y sólo si está pendiente, validado en el componente)
        Gate::define('delete-flight', fn (User $user) => $user->canCreateFlight());

        // Exportar a Sage: solo facturadores
        Gate::define('export-sage', fn (User $user) => $user->canMarkAsBilled());

        // Gestión de catálogos (aerolíneas, aeronaves, tipos de documento, usuarios)
        Gate::define('manage-catalogs', fn (User $user) => $user->canAdminVuelos());
    }
}
