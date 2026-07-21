<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin comparison quotes. A unique id per row so the same vehicle can be
     * quoted again next year (same reg number, new record/values).
     */
    public function up(): void
    {
        Schema::create('quote_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('First Party Comprehensive');
            $table->string('vehicle_reg_number')->index();
            $table->string('vehicle_model')->nullable();
            $table->json('data');                 // shared + per-column inputs
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_templates');
    }
};
