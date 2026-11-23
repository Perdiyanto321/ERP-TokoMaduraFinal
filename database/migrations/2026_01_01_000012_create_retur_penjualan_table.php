<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retur_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualan')->restrictOnDelete();
            $table->foreignId('diajukan_oleh')->constrained('users')->restrictOnDelete();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('nomor', 40)->unique();
            $table->dateTime('tanggal');
            $table->text('alasan');
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->decimal('nilai_refund', 15, 2)->default(0);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE retur_penjualan ADD CONSTRAINT chk_retur_refund CHECK (nilai_refund >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('retur_penjualan');
    }
};
