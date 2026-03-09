<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_flight_supervisor')->default(false);
            $table->boolean('is_billing_supervisor')->default(false);
            $table->boolean('is_admin_vuelos')->default(false);
            $table->boolean('is_archived')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_flight_supervisor', 'is_billing_supervisor', 'is_admin_vuelos', 'is_archived']);
        });
    }
};
