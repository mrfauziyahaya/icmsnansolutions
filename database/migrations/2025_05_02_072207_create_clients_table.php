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
        Schema::create('clients', function (Blueprint $table) {
            $table->id('client_id');
            $table->string('name', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('plate', 50)->nullable();
            $table->string('vehicle_model', 100)->nullable();
            $table->text('address1')->nullable();
            $table->string('insurance_company', 255)->nullable();
            $table->decimal('nettpremium', 15, 2)->nullable();
            $table->decimal('premium', 15, 2)->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('renewal_date')->nullable();
            $table->enum('status', ['Active', 'Expiring', 'Expired'])->nullable();
            $table->text('address2')->nullable();
            $table->string('city', 255)->nullable();
            $table->string('state', 255)->nullable();
            $table->string('postcode', 20)->nullable();
            $table->string('mykad_companyno', 255)->nullable();
            $table->date('inception_date');
            $table->date('reminder_date')->nullable();
            $table->string('document_name', 255)->nullable();
            $table->string('document_path', 500)->nullable();
            $table->string('document_type', 100)->nullable();
            $table->datetime('document_uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
