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
        Schema::create('reservasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Satu jadwal hanya boleh dipakai oleh satu reservasi (anti double-booking).
            // Nullable: saat reservasi dibatalkan/ditolak, jadwal_id di-set NULL
            // supaya slotnya bebas dipakai reservasi lain (NULL tidak dihitung unique di MySQL).
            $table->foreignId('jadwal_id')->nullable()->unique()->constrained('jadwal')->cascadeOnDelete();
            $table->unsignedInteger('total_harga');
            $table->enum('status', [
                'menunggu_pembayaran',
                'menunggu_verifikasi',
                'dikonfirmasi',
                'ditolak',
                'dibatalkan',
            ])->default('menunggu_pembayaran');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservasi');
    }
};
