<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->string('flight_number', 50);
            $table->date('flight_date');
            $table->foreignId('airline_id')->constrained('airlines')->onDelete('restrict');
            $table->foreignId('aircraft_id')->nullable()->constrained('aircraft')->onDelete('restrict');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('billing_user_id')->nullable()->constrained('users')->onDelete('restrict');
            $table->string('status', 20)->default('pending'); // pending, approved, billed
            // Workflow tracking
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('billed_at')->nullable();
            // Sage 50 tracking
            $table->string('sage_exported_at')->nullable();
            $table->timestamps();
        });

        // flight_supervisors pivot table (legacy: supervisors M2M)
        Schema::create('flight_supervisors', function (Blueprint $table) {
            $table->foreignId('flight_id')->constrained('flights')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->primary(['flight_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_supervisors');
        Schema::dropIfExists('flights');
    }
};
