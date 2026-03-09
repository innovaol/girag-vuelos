<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\DocumentType;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Super admin ──────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@girag.com'],
            [
                'name'                  => 'admin',
                'password'              => Hash::make('admin1234'),
                'is_flight_supervisor'  => true,
                'is_billing_supervisor' => true,
                'is_admin_vuelos'       => true,
            ]
        );

        // ─── Tipos de documento por defecto ───────────────────────────
        $docTypes = [
            'Reporte de Rampa',
            'Reporte de Fumigación',
            'Bill of Lading',
            'Carta de Porte',
            'Manifiesto de Carga',
            'Otro',
        ];

        foreach ($docTypes as $name) {
            DocumentType::firstOrCreate(['name' => $name]);
        }
    }
}
