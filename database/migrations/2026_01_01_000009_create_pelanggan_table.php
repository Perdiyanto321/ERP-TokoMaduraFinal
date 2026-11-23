<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 120);
            $table->string('no_hp', 25)->nullable();
            $table->decimal('batas_kasbon', 15, 2)->default(0);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE pelanggan ADD CONSTRAINT chk_pelanggan_batas_kasbon CHECK (batas_kasbon >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};
