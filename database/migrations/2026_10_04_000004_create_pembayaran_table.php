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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            // Satu reservasi hanya memiliki satu pembayaran.
            $table->foreignId('reservasi_id')->unique()->constrained('reservasi')->cascadeOnDelete();
            $table->unsignedInteger('jumlah');
            $table->string('nama_pengirim')->nullable();
            $table->string('bukti_transfer')->nullable();
            $table->enum('status', [
                'menunggu_pembayaran',
                'menunggu_verifikasi',
                'valid',
                'tidak_valid',
            ])->default('menunggu_pembayaran');
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
