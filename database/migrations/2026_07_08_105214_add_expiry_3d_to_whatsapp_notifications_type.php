<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE whatsapp_notifications MODIFY COLUMN type ENUM('expiry_30d', 'expiry_14d', 'expiry_3d', 'policy_created', 'policy_updated', 'policy_renewed')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE whatsapp_notifications MODIFY COLUMN type ENUM('expiry_30d', 'expiry_14d', 'policy_created', 'policy_updated', 'policy_renewed')");
    }
};
