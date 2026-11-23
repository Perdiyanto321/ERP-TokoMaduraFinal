<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('supplier')->restrictOnDelete();
            $table->foreignId('dibuat_oleh')->constrained('users')->restrictOnDelete();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('nomor_po', 40)->unique();
            $table->date('tanggal');
            $table->enum('status', ['draf', 'disetujui', 'dikirim', 'diterima_sebagian', 'selesai', 'dibatalkan'])->default('draf');
            $table->enum('jenis_bayar', ['tunai', 'kredit']);
            $table->date('jatuh_tempo')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();

            $table->index('status');
        });

        DB::statement('ALTER TABLE pembelian ADD CONSTRAINT chk_pembelian_total CHECK (total >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian');
    }
};
