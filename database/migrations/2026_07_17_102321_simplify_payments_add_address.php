<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The checkout form was simplified to name/email/phone/address/amount.
     * Add address; make the old vehicle/purpose columns nullable so records
     * created by the simplified form validate, while keeping existing data.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->text('address')->nullable()->after('payer_phone');
        });

        DB::statement("ALTER TABLE payments MODIFY COLUMN purpose ENUM('road_tax', 'insurance', 'both') NULL");
        DB::statement("ALTER TABLE payments MODIFY COLUMN vehicle_plate VARCHAR(255) NULL");
        DB::statement("ALTER TABLE payments MODIFY COLUMN vehicle_type VARCHAR(255) NULL");
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('address');
        });

        DB::statement("ALTER TABLE payments MODIFY COLUMN purpose ENUM('road_tax', 'insurance', 'both') NOT NULL");
        DB::statement("ALTER TABLE payments MODIFY COLUMN vehicle_plate VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE payments MODIFY COLUMN vehicle_type VARCHAR(255) NOT NULL");
    }
};
