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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();

            // Payer
            $table->string('payer_name');
            $table->string('payer_email');
            $table->string('payer_phone');

            // What is being paid for
            $table->enum('purpose', ['road_tax', 'insurance', 'both']);
            $table->string('vehicle_plate');
            $table->string('vehicle_type');
            $table->text('notes')->nullable();

            // Money
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('MYR');

            // Gateway
            $table->string('gateway');                       // chip, fiuu, atome, ahapay, senangpay
            $table->string('method')->nullable();            // channel within the gateway
            $table->string('gateway_reference')->nullable()->index();
            $table->text('checkout_url')->nullable();

            // Outcome
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('callback_payload')->nullable();    // raw webhook, kept for audit/disputes

            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
