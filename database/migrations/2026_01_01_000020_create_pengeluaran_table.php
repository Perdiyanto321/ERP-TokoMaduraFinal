<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('kategori_biaya', 50);
            $table->decimal('nominal', 15, 2);
            $table->date('tanggal');
            $table->string('keterangan', 255);
            $table->timestamps();

            $table->index('tanggal');
        });

        DB::statement('ALTER TABLE pengeluaran ADD CONSTRAINT chk_pengeluaran_nominal CHECK (nominal > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};
