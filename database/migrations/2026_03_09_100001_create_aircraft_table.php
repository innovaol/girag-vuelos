<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aircraft', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique(); // Legacy 'aeronave'
            $table->foreignId('airline_id')->constrained('airlines')->onDelete('restrict');
            $table->foreignId('parent_aircraft_id')->nullable()->constrained('aircraft')->onDelete('restrict');
            $table->boolean('is_archived')->default(false);
            $table->string('model')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aircraft');
    }
};
