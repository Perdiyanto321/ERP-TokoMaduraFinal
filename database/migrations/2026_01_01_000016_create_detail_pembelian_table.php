<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pembelian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')->constrained('pembelian')->restrictOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->restrictOnDelete();
            $table->unsignedInteger('qty_pesan');
            $table->decimal('harga_beli', 15, 2);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE detail_pembelian ADD CONSTRAINT chk_detail_pembelian_qty CHECK (qty_pesan > 0)');
        DB::statement('ALTER TABLE detail_pembelian ADD CONSTRAINT chk_detail_pembelian_harga CHECK (harga_beli >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pembelian');
    }
};
