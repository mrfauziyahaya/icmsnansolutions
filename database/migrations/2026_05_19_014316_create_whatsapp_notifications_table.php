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
        Schema::create('whatsapp_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->index();
            $table->enum('type', ['expiry_30d', 'expiry_14d', 'policy_created', 'policy_updated', 'policy_renewed']);
            $table->string('recipient_phone');
            $table->text('message');
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notifications');
    }
};
