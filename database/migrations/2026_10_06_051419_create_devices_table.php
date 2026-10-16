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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('owner', 120);
            $table->string('area', 120);
            $table->string('brand', 80);
            $table->string('model', 120);
            $table->string('serial_number', 120)->unique();
            $table->string('type', 80);
            $table->date('received_at');
            $table->string('priority', 20)->default('medium');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
