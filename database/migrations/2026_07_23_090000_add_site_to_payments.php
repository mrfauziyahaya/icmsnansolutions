<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One app now serves several domains (nansolutions.com.my, reniu.my) that
     * share this table. `site` records which one a payment originated from —
     * it drives credential selection, webhook verification and admin filtering.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('site', 30)->default('nansolutions')->after('reference')->index();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('site');
        });
    }
};
