<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            // ── Identificador Odoo ─────────────────────────────────────
            $table->string('odoo_ref', 20)->nullable()->after('id')
                  ->comment('Referencia cruzada con Odoo (REP-XXXXX)');

            // ── Clasificación del vuelo ─────────────────────────────────
            $table->string('tipo_servicio', 100)->nullable()->after('aircraft_id')
                  ->comment('Escala Comercial Pasajero, Carguero, Técnica, etc.');
            $table->char('origen', 3)->nullable()->after('tipo_servicio');
            $table->char('destino', 3)->nullable()->after('origen');
            $table->string('gate', 20)->nullable()->after('destino');

            // ── Tiempos operacionales ───────────────────────────────────
            $table->datetime('eta')->nullable()->after('gate');
            $table->datetime('ata')->nullable()->after('eta');
            $table->datetime('std')->nullable()->after('ata');
            $table->datetime('block_in')->nullable()->after('std');
            $table->datetime('block_off')->nullable()->after('block_in');
            $table->time('start_offloading')->nullable()->after('block_off');
            $table->time('end_offloading')->nullable()->after('start_offloading');
            $table->time('start_loading')->nullable()->after('end_offloading');
            $table->time('end_loading')->nullable()->after('start_loading');

            // ── Tráfico ────────────────────────────────────────────────
            $table->unsignedSmallInteger('pax_in')->nullable()->after('end_loading');
            $table->unsignedSmallInteger('pax_out')->nullable()->after('pax_in');
            $table->unsignedSmallInteger('bags_offloading')->nullable()->after('pax_out');
            $table->unsignedSmallInteger('bags_loading')->nullable()->after('bags_offloading');
            $table->unsignedSmallInteger('ulds_offloading')->nullable()->after('bags_loading');
            $table->unsignedSmallInteger('ulds_loading')->nullable()->after('ulds_offloading');
            $table->decimal('kgs_inbound', 10, 2)->nullable()->after('ulds_loading');
            $table->decimal('kgs_outbound', 10, 2)->nullable()->after('kgs_inbound');

            // ── Demoras ────────────────────────────────────────────────
            $table->string('delay_codes', 200)->nullable()->after('kgs_outbound');
            $table->string('delay_responsibility', 20)->nullable()->after('delay_codes')
                  ->comment('INCONTROLABLE / CONTROLABLE / EN TIEMPO');
            $table->text('observaciones')->nullable()->after('delay_responsibility');

            // ── Recursos ───────────────────────────────────────────────
            $table->foreignId('leader_id')->nullable()->constrained('users')
                  ->nullOnDelete()->after('observaciones')
                  ->comment('Líder CCO que atendió el vuelo');

            // ── Financiero (solo visible para Contabilidad) ─────────────
            $table->decimal('vuelo_pagado', 10, 2)->default(0)->after('leader_id');
            $table->decimal('fumigacion',   10, 2)->default(0)->after('vuelo_pagado');
        });
    }

    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dropConstrainedForeignId('leader_id');
            $table->dropColumn([
                'odoo_ref', 'tipo_servicio', 'origen', 'destino', 'gate',
                'eta', 'ata', 'std', 'block_in', 'block_off',
                'start_offloading', 'end_offloading', 'start_loading', 'end_loading',
                'pax_in', 'pax_out', 'bags_offloading', 'bags_loading',
                'ulds_offloading', 'ulds_loading', 'kgs_inbound', 'kgs_outbound',
                'delay_codes', 'delay_responsibility', 'observaciones',
                'vuelo_pagado', 'fumigacion',
            ]);
        });
    }
};
