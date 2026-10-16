<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 30)->unique();

            $table->foreignId('device_id')
                ->constrained('devices')
                ->restrictOnDelete();

            $table->foreignId('technician_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('status', 30)->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
