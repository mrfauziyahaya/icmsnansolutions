<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Payment notifications aren't tied to a Client, and need a new type value.
     * Raw MySQL ALTERs (matching the existing enum migrations); no-op on other
     * drivers so the sqlite test database doesn't choke on ENUM/MODIFY syntax.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE whatsapp_notifications MODIFY COLUMN client_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE whatsapp_notifications MODIFY COLUMN type ENUM('expiry_30d', 'expiry_14d', 'expiry_3d', 'policy_created', 'policy_updated', 'policy_renewed', 'payment_received')");
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
