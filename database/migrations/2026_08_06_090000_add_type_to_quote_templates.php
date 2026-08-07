<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_templates', function (Blueprint $table) {
            // Which quote form this row belongs to (comprehensive / 3rd party /
            // motor variants). Existing rows are all the original comprehensive.
            $table->string('type')->default('comprehensive')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('quote_templates', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
