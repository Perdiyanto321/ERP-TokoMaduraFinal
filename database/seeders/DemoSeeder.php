<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Pelanggan;
use App\Models\Pengeluaran;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoriService;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(InventoriService $inventoriService): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $keuangan = User::where('username', 'keuangan')->firstOrFail();

        // 1. Kategori Produk
        $kategoriList = [
            'Sembako' => Kategori::firstOrCreate(['nama' => 'Sembako']),
            'Minuman' => Kategori::firstOrCreate(['nama' => 'Minuman']),
            'Mie & Makanan Instan' => Kategori::firstOrCreate(['nama' => 'Mie & Makanan Instan']),
            'Kebutuhan Rumah Tangga' => Kategori::firstOrCreate(['nama' => 'Kebutuhan Rumah Tangga']),
            'Rokok & Tembakau' => Kategori::firstOrCreate(['nama' => 'Rokok & Tembakau']),
        ];

        // 2. Supplier
        Supplier::firstOrCreate(['nama' => 'UD Sumber Pangan Madura'], [
            'kontak' => '0812-3000-1122',
            'alamat' => 'Jl. Trunojoyo No. 45, Bangkalan',
        ]);
        Supplier::firstOrCreate(['nama' => 'PT Distribusi Jaya Abadi'], [
            'kontak' => '0813-5500-8899',
            'alamat' => 'Kawasan Pergudangan Margomulyo, Surabaya',
        ]);
        Supplier::firstOrCreate(['nama' => 'CV Tirta Segar Nusantara'], [
            'kontak' => '0857-4411-2233',
            'alamat' => 'Jl. Raya Socah No. 8, Bangkalan',
        ]);

        // 3. Pelanggan Kasbon
        Pelanggan::firstOrCreate(['nama' => 'Warung Bu Halimah'], [
            'no_hp' => '0812-9988-7766',
            'batas_kasbon' => 1500000,
        ]);
        Pelanggan::firstOrCreate(['nama' => 'Pak Slamet (Bengkel)'], [
            'no_hp' => '0852-3344-5566',
            'batas_kasbon' => 500000,
        ]);
        Pelanggan::firstOrCreate(['nama' => 'Kos Putri Melati'], [
            'no_hp' => '0878-1122-3344',
            'batas_kasbon' => 1000000,
        ]);

        // 4. 15 Produk Realistis Toko Madura
        $daftarProduk = [
            ['Sembako', 'SMB-001', 'Beras Ramos Setra 5kg', 'sak', 'sak', 1, 64000, 70000, 25, 8, 25],
            ['Sembako', 'SMB-002', 'Gula Pasir Gulaku 1kg', 'bungkus', 'karton', 20, 310000, 17500, 40, 15, 60],
            ['Sembako', 'SMB-003', 'Minyak Goreng Bimoli 1L', 'pouch', 'karton', 12, 198000, 18500, 10, 12, 36], // Di bawah minimum
            ['Sembako', 'SMB-004', 'Telur Ayam Negeri 1kg', 'kg', 'peti', 15, 375000, 28000, 30, 10, 45],
            ['Sembako', 'SMB-005', 'Tepung Terigu Segitiga Biru 1kg', 'bungkus', 'karton', 12, 132000, 12500, 24, 10, 36],
            ['Mie & Makanan Instan', 'MIE-001', 'Indomie Goreng Original', 'bungkus', 'karton', 40, 112000, 3500, 80, 40, 120],
            ['Mie & Makanan Instan', 'MIE-002', 'Indomie Kuah Soto Mie', 'bungkus', 'karton', 40, 108000, 3300, 35, 40, 120], // Di bawah minimum
            ['Mie & Makanan Instan', 'MIE-003', 'Sarden ABC Tomat 155g', 'kaleng', 'karton', 24, 216000, 10500, 18, 10, 48],
            ['Minuman', 'MNM-001', 'Air Mineral Le Minerale 600ml', 'botol', 'karton', 24, 48000, 3000, 48, 24, 96],
            ['Minuman', 'MNM-002', 'Kopi Kapal Api Special 165g', 'bungkus', 'karton', 20, 270000, 15500, 15, 10, 40],
            ['Minuman', 'MNM-003', 'Teh Celup Sariwangi Isi 25', 'kotak', 'karton', 24, 132000, 6500, 8, 10, 30], // Di bawah minimum
            ['Kebutuhan Rumah Tangga', 'KRT-001', 'Gas LPG 3kg (Isi Ulang)', 'tabung', 'tabung', 1, 18000, 21000, 12, 10, 30],
            ['Kebutuhan Rumah Tangga', 'KRT-002', 'Sabun Cuci Rinso Anti Noda 770g', 'bungkus', 'karton', 12, 228000, 21500, 14, 8, 24],
            ['Kebutuhan Rumah Tangga', 'KRT-003', 'Sabun Mandi Lifebuoy Total 10', 'batang', 'karton', 48, 168000, 4500, 36, 15, 60],
            ['Rokok & Tembakau', 'RKK-001', 'Gudang Garam Surya 16', 'bungkus', 'slop', 10, 335000, 36000, 20, 10, 30],
        ];

        foreach ($daftarProduk as $item) {
            [$kat, $kode, $nama, $satuan, $satuanBeli, $isi, $hargaBeli, $hargaJual, $stokAwal, $min, $target] = $item;
            $hppSatuan = round($hargaBeli / $isi, 2);

            $produk = Produk::firstOrCreate(
                ['kode' => $kode],
                [
                    'kategori_id' => $kategoriList[$kat]->id,
                    'nama' => $nama,
                    'satuan' => $satuan,
                    'satuan_beli' => $satuanBeli,
                    'isi_per_satuan_beli' => $isi,
                    'harga_beli' => $hargaBeli,
                    'harga_jual' => $hargaJual,
                    'hpp_rata_rata' => $hppSatuan,
                    'stok' => 0,
                    'stok_minimum' => $min,
                    'stok_target' => $target,
                    'status' => 'aktif',
                ]
            );

            if ($produk->wasRecentlyCreated && $stokAwal > 0) {
                $inventoriService->ubahStok(
                    produkId: $produk->id,
                    qtyBertanda: $stokAwal,
                    jenis: 'stok_awal',
                    userId: $admin->id,
                    sumberTipe: 'seeder',
                    sumberId: null
                );
            }
        }

        // 5. Sampel Pengeluaran Operasional
        if (Pengeluaran::count() === 0) {
            Pengeluaran::create([
                'user_id' => $keuangan->id,
                'kategori_biaya' => 'listrik',
                'nominal' => 350000,
                'tanggal' => now()->startOfMonth()->addDays(2)->toDateString(),
                'keterangan' => 'Token listrik toko bulan berjalan',
            ]);
            Pengeluaran::create([
                'user_id' => $keuangan->id,
                'kategori_biaya' => 'kemasan',
                'nominal' => 85000,
                'tanggal' => now()->subDays(2)->toDateString(),
                'keterangan' => 'Pembelian kantong kresek ukuran sedang & besar',
            ]);
        }
    }
}
