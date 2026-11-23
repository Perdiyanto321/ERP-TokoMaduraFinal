<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasir_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggan')->restrictOnDelete();
            $table->string('nomor', 40)->unique();
            $table->dateTime('tanggal');
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('jenis_bayar', ['tunai', 'transfer', 'qris', 'kasbon']);
            $table->decimal('jumlah_dibayar', 15, 2)->default(0);
            $table->enum('status', ['selesai', 'batal'])->default('selesai');
            $table->string('idempotency_key', 64)->unique();
            $table->text('alasan_batal')->nullable();
            $table->foreignId('dibatalkan_oleh')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['tanggal', 'status']);
        });

        DB::statement('ALTER TABLE penjualan ADD CONSTRAINT chk_penjualan_total CHECK (total >= 0)');
        DB::statement('ALTER TABLE penjualan ADD CONSTRAINT chk_penjualan_dibayar CHECK (jumlah_dibayar >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
