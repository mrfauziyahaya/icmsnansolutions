<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Payment notifications aren't tied to a Client, and need a new type value.
     *
     * MySQL gets raw ALTERs to match the existing enum migrations. Other drivers
     * can't parse ENUM/MODIFY, but skipping them entirely left sqlite with a
     * NOT NULL client_id — so every payment_received insert threw in tests, was
     * swallowed by the caller's try/catch, and the logging went untested.
     * Apply the same shape through the schema builder instead.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE whatsapp_notifications MODIFY COLUMN client_id BIGINT UNSIGNED NULL");
            DB::statement("ALTER TABLE whatsapp_notifications MODIFY COLUMN type ENUM('expiry_30d', 'expiry_14d', 'expiry_3d', 'policy_created', 'policy_updated', 'policy_renewed', 'payment_received')");

            return;
        }

        Schema::table('whatsapp_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->change();
            $table->string('type')->change();   // drops the enum check constraint
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE whatsapp_notifications MODIFY COLUMN type ENUM('expiry_30d', 'expiry_14d', 'expiry_3d', 'policy_created', 'policy_updated', 'policy_renewed')");
        // client_id left nullable — reverting could fail on existing payment rows.
    }
};
