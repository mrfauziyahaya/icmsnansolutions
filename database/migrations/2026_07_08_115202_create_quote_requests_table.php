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
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();

            // Step 1 — Maklumat Pemilik Kenderaan
            $table->string('nama_pemilik');
            $table->string('no_ic');
            $table->string('poskod');
            $table->string('no_plate');

            // Step 2 — Maklumat Kenderaan
            $table->boolean('ehailing')->default(false);
            $table->string('ehailing_usage')->nullable();   // Harian / Tahunan
            $table->boolean('tukar_milik')->nullable();      // Ya / Tidak
            $table->string('whatsapp');

            // Step 3 — Perlindungan
            $table->string('jenis_perlindungan');
            $table->json('perlindungan_tambahan')->nullable();
            $table->decimal('jumlah_perlindungan_cermin', 10, 2)->nullable();

            // Step 4 — Jenis Pembayaran
            $table->string('jenis_pembayaran');

            // Admin
            $table->boolean('is_read')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
