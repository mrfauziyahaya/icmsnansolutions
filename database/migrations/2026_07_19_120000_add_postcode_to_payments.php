<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Atome (and general billing) needs a postcode; the checkout collects only
     * a single address line, so add a dedicated nullable column for it.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('postcode', 12)->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('postcode');
        });
    }
};
