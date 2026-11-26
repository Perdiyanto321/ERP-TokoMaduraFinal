<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('piutang')) {
            Schema::create('piutang', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pelanggan_id')->constrained('pelanggan')->restrictOnDelete();
                $table->foreignId('penjualan_id')->unique()->constrained('penjualan')->restrictOnDelete();
                $table->decimal('jumlah', 15, 2);
                $table->decimal('sisa', 15, 2);
                $table->date('jatuh_tempo')->nullable();
                $table->enum('status', ['belum_lunas', 'sebagian', 'lunas', 'batal'])->default('belum_lunas');
                $table->timestamps();

                $table->index(['pelanggan_id', 'status']);
            });

            DB::statement('ALTER TABLE piutang ADD CONSTRAINT chk_piutang_nominal CHECK (jumlah >= 0 AND sisa >= 0 AND sisa <= jumlah)');
        }

        if (! Schema::hasTable('retur_penjualan')) {
            Schema::create('retur_penjualan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('penjualan_id')->constrained('penjualan')->restrictOnDelete();
                $table->foreignId('diproses_oleh')->constrained('users')->restrictOnDelete();
                $table->string('nomor', 30)->unique();
                $table->dateTime('tanggal');
                $table->enum('penyelesaian', ['potong_piutang', 'tunai_kembali']);
                $table->decimal('total_retur', 15, 2);
                $table->text('alasan');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('detail_retur')) {
            Schema::create('detail_retur', function (Blueprint $table) {
                $table->id();
                $table->foreignId('retur_id')->constrained('retur_penjualan')->cascadeOnDelete();
                $table->foreignId('detail_penjualan_id')->constrained('detail_penjualan')->restrictOnDelete();
                $table->unsignedInteger('qty_retur');
                $table->enum('kondisi', ['layak_jual', 'rusak'])->default('layak_jual');
                $table->decimal('subtotal', 15, 2);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('utang')) {
            Schema::create('utang', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained('supplier')->restrictOnDelete();
                $table->foreignId('pembelian_id')->unique()->constrained('pembelian')->restrictOnDelete();
                $table->decimal('jumlah', 15, 2);
                $table->decimal('sisa', 15, 2);
                $table->date('jatuh_tempo')->nullable();
                $table->enum('status', ['belum_lunas', 'sebagian', 'lunas', 'batal'])->default('belum_lunas');
                $table->timestamps();
            });

            DB::statement('ALTER TABLE utang ADD CONSTRAINT chk_utang_nominal CHECK (jumlah >= 0 AND sisa >= 0 AND sisa <= jumlah)');
        }

        // Pastikan kolom referensi piutang_id & utang_id tersedia pada tabel pembayaran
        Schema::table('pembayaran', function (Blueprint $table) {
            if (! Schema::hasColumn('pembayaran', 'piutang_id')) {
                $table->foreignId('piutang_id')->nullable()->after('pembelian_id')->constrained('piutang')->restrictOnDelete();
            }
            if (! Schema::hasColumn('pembayaran', 'utang_id')) {
                $table->foreignId('utang_id')->nullable()->after('piutang_id')->constrained('utang')->restrictOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            if (Schema::hasColumn('pembayaran', 'utang_id')) {
                $table->dropConstrainedForeignId('utang_id');
            }
            if (Schema::hasColumn('pembayaran', 'piutang_id')) {
                $table->dropConstrainedForeignId('piutang_id');
            }
        });
        Schema::dropIfExists('utang');
        Schema::dropIfExists('detail_retur');
        Schema::dropIfExists('retur_penjualan');
        Schema::dropIfExists('piutang');
    }
};
