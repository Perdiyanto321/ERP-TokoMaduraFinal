<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
            $table->string('kode', 30)->unique();
            $table->string('nama', 150)->index();
            $table->string('satuan', 30);
            $table->string('satuan_beli', 30);
            $table->unsignedInteger('isi_per_satuan_beli')->default(1);
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->decimal('hpp_rata_rata', 15, 2)->default(0);
            $table->integer('stok')->default(0);
            $table->unsignedInteger('stok_minimum')->default(0);
            $table->unsignedInteger('stok_target')->default(0);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE produk ADD CONSTRAINT chk_produk_stok CHECK (stok >= 0)');
        DB::statement('ALTER TABLE produk ADD CONSTRAINT chk_produk_harga_jual CHECK (harga_jual >= 0)');
        DB::statement('ALTER TABLE produk ADD CONSTRAINT chk_produk_harga_beli CHECK (harga_beli >= 0)');
        DB::statement('ALTER TABLE produk ADD CONSTRAINT chk_produk_isi CHECK (isi_per_satuan_beli >= 1)');
        DB::statement('ALTER TABLE produk ADD CONSTRAINT chk_produk_target CHECK (stok_target >= stok_minimum)');
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
