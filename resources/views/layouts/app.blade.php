@props(['judul' => 'ERP Toko Madura', 'layarPenuh' => false])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $judul }} — {{ config('toko.nama') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .btn-primer {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.375rem;
            background-color: #1F2428; color: #FFFFFF; font-weight: 500;
            padding: 0.5rem 0.95rem; border-radius: 8px; border: 1px solid #1F2428;
            cursor: pointer; transition: all 0.15s ease;
        }
        .btn-primer:hover { background-color: #111417; }
        .btn-primer:disabled { opacity: 0.55; cursor: not-allowed; }

        .btn-sekunder {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.375rem;
            background-color: #FFFFFF; color: var(--teks); font-weight: 500;
            padding: 0.5rem 0.95rem; border-radius: 8px; border: 1px solid var(--garis-kuat);
            cursor: pointer; transition: all 0.15s ease;
        }
        .btn-sekunder:hover { background-color: #F4F5F7; }

        .btn-bahaya {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.375rem;
            background-color: var(--bahaya); color: #FFFFFF; font-weight: 500;
            padding: 0.5rem 0.95rem; border-radius: 8px; border: 1px solid var(--bahaya);
            cursor: pointer;
        }

        .input-kontrol {
            width: 100%; background-color: #FFFFFF; color: var(--teks);
            border: 1px solid var(--garis-kuat); border-radius: 8px;
            padding: 0.45rem 0.7rem; font-size: 14px;
        }
        .input-kontrol:focus { border-color: #1F2428; outline: none; }
    </style>
</head>
<body class="min-h-screen bg-[#F3F4F6] text-[#181B1E] antialiased">
    @php
        $user = auth()->user();
        $user?->loadMissing(['role', 'karyawan']);
        $peran = $user?->role?->nama ?? '';
    @endphp

    <div x-data="{
            sidebarTerbuka: localStorage.getItem('erp_sidebar') !== 'tutup',
            mobileMenu: false,
            toggleSidebar() {
                this.sidebarTerbuka = !this.sidebarTerbuka;
                localStorage.setItem('erp_sidebar', this.sidebarTerbuka ? 'buka' : 'tutup');
            }
         }"
         class="flex min-h-screen">

        {{-- Backdrop Mobile --}}
        <div x-show="mobileMenu" x-cloak @click="mobileMenu = false"
             class="fixed inset-0 z-30 bg-black/30 lg:hidden"></div>

        {{-- Sidebar Navigasi Collapsible --}}
        @unless ($layarPenuh)
            <aside
                :class="[
                    sidebarTerbuka ? 'w-[244px]' : 'w-[76px]',
                    mobileMenu ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
                ]"
                class="fixed lg:sticky top-0 left-0 z-40 h-screen shrink-0 bg-white border-r border-[#E6E8EC] flex flex-col justify-between transition-all duration-200 ease-in-out select-none"
            >
                {{-- Header Brand + Tombol Close / Open Sidebar --}}
                <div>
                    <div class="h-[64px] px-4 flex items-center justify-between border-b border-[#F0F1F4]">
                        <a href="{{ route('dasbor') }}" class="flex items-center gap-2.5 overflow-hidden">
                            <div class="h-9 w-9 rounded-[10px] bg-[#1F2428] text-white flex items-center justify-center font-extrabold text-[15px] shrink-0">
                                TM
                            </div>
                            <div x-show="sidebarTerbuka" x-transition.opacity class="truncate">
                                <p class="text-[15px] font-bold tracking-tight text-[#181B1E] leading-tight">Toko Madura</p>
                                <p class="text-[11px] text-[#7A828C]">ERP Operasional</p>
                            </div>
                        </a>

                        {{-- Tombol Close / Open Sidebar --}}
                        <button
                            type="button"
                            @click="toggleSidebar()"
                            class="hidden lg:inline-flex h-8 w-8 items-center justify-center rounded-[8px] text-[#6B7280] hover:bg-[#F3F4F6] hover:text-[#181B1E] transition cursor-pointer shrink-0"
                            :title="sidebarTerbuka ? 'Tutup Sidebar' : 'Buka Sidebar'"
                        >
                            <svg x-show="sidebarTerbuka" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                            </svg>
                            <svg x-show="!sidebarTerbuka" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                            </svg>
                        </button>

                        {{-- Tombol Close Khusus Mobile --}}
                        <button type="button" @click="mobileMenu = false" class="lg:hidden p-1.5 text-[#6B7280]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Menu Navigasi --}}
                    <nav class="p-3 space-y-1.5 overflow-y-auto max-h-[calc(100vh-230px)] text-[13.5px]">
                        {{-- Dasbor --}}
                        <a href="{{ route('dasbor') }}" title="Dashboard"
                           class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('dasbor') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                            <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span x-show="sidebarTerbuka" class="truncate">Dashboard</span>
                        </a>

                        @if (in_array($peran, ['admin', 'kasir']))
                            <div x-show="sidebarTerbuka" class="pt-3 pb-1 px-3 text-[10.5px] font-semibold uppercase tracking-wider text-[#9CA3AF]">Penjualan</div>
                            <a href="{{ route('kasir.index') }}" title="Kasir (POS)"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('kasir.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Kasir (POS)</span>
                            </a>
                            <a href="{{ route('penjualan.index') }}" title="Riwayat Penjualan"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('penjualan.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Transaksi Jual</span>
                            </a>
                            <a href="{{ route('retur.index') }}" title="Retur Penjualan"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('retur.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Retur Barang</span>
                            </a>
                            <a href="{{ route('kasbon.index') }}" title="Piutang Kasbon"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('kasbon.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Piutang Kasbon</span>
                            </a>
                        @endif

                        @if (in_array($peran, ['admin', 'gudang']))
                            <div x-show="sidebarTerbuka" class="pt-3 pb-1 px-3 text-[10.5px] font-semibold uppercase tracking-wider text-[#9CA3AF]">Inventori & PO</div>
                            <a href="{{ route('produk.index') }}" title="Produk & Harga"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('produk.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Produk & Harga</span>
                            </a>
                            <a href="{{ route('kategori.index') }}" title="Kategori"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('kategori.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Kategori</span>
                            </a>
                            <a href="{{ route('stok.index') }}" title="Kartu Stok"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('stok.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Kartu Stok</span>
                            </a>
                            <a href="{{ route('restok.index') }}" title="Saran Restok"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('restok.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Saran Restok</span>
                            </a>
                            <a href="{{ route('po.index') }}" title="Purchase Order"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('po.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Purchase Order</span>
                            </a>
                            <a href="{{ route('supplier.index') }}" title="Supplier"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('supplier.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Supplier</span>
                            </a>
                        @endif

                        @if (in_array($peran, ['admin', 'keuangan']))
                            <div x-show="sidebarTerbuka" class="pt-3 pb-1 px-3 text-[10.5px] font-semibold uppercase tracking-wider text-[#9CA3AF]">Keuangan</div>
                            <a href="{{ route('utang.index') }}" title="Utang Supplier"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('utang.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Utang Supplier</span>
                            </a>
                            <a href="{{ route('biaya.index') }}" title="Biaya Operasional"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('biaya.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Biaya Operasional</span>
                            </a>
                            <a href="{{ route('kas.index') }}" title="Rekap Kas"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('kas.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Rekap Kas</span>
                            </a>
                            <a href="{{ route('laporan.penjualan') }}" title="Laporan Bisnis"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('laporan.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Laporan Bisnis</span>
                            </a>
                        @endif

                        @if ($peran === 'admin')
                            <div x-show="sidebarTerbuka" class="pt-3 pb-1 px-3 text-[10.5px] font-semibold uppercase tracking-wider text-[#9CA3AF]">Sistem</div>
                            <a href="{{ route('pengguna.index') }}" title="Pengguna & Karyawan"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('pengguna.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Pengguna</span>
                            </a>
                            <a href="{{ route('log.index') }}" title="Log Aktivitas"
                               class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 font-medium transition {{ request()->routeIs('log.*') ? 'bg-[#F3F4F6] text-[#181B1E] font-semibold' : 'text-[#6B7280] hover:bg-[#F8F9FA] hover:text-[#181B1E]' }}">
                                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span x-show="sidebarTerbuka" class="truncate">Log Audit</span>
                            </a>
                        @endif
                    </nav>
                </div>

                {{-- Bagian Bawah Sidebar: Kartu Pintasan Gelap (Sesuai Referensi) + Logout --}}
                <div class="p-3 space-y-2.5 border-t border-[#F0F1F4]">
                    <div x-show="sidebarTerbuka" class="rounded-[14px] bg-[#1F2428] p-3.5 text-white">
                        <p class="text-[13px] font-semibold leading-snug">Mode Kasir Cepat</p>
                        <p class="text-[11px] text-[#9CA3AF] mt-0.5 leading-relaxed">Akses layar penuh transaksi & pemindaian barcode.</p>
                        <a href="{{ in_array($peran, ['admin', 'kasir']) ? route('kasir.index') : route('restok.index') }}"
                           class="mt-3 block w-full rounded-[8px] bg-[#343A40] hover:bg-[#434A52] py-1.5 text-center text-[12px] font-medium text-white transition">
                            {{ in_array($peran, ['admin', 'kasir']) ? 'Buka Kasir POS' : 'Cek Saran Restok' }}
                        </a>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar"
                                class="flex w-full items-center gap-3 rounded-[10px] px-3 py-2 text-[13px] font-medium text-[#6B7280] hover:bg-red-50 hover:text-[#B03A2E] transition cursor-pointer">
                            <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span x-show="sidebarTerbuka">Keluar</span>
                        </button>
                    </form>
                </div>
            </aside>
        @endunless

        {{-- Area Utama --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Topbar Bersih (Menyatu dengan Latar) --}}
            <header class="h-[64px] shrink-0 px-5 lg:px-8 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    @unless ($layarPenuh)
                        <button type="button" @click="mobileMenu = true"
                                class="lg:hidden rounded-[8px] bg-white p-2 text-[#181B1E] border border-[#E6E8EC]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    @else
                        <a href="{{ route('dasbor') }}" class="text-[13px] font-semibold text-[#181B1E] hover:underline">&larr; Dasbor</a>
                    @endunless

                    <h1 class="text-[22px] font-bold tracking-tight text-[#181B1E]">{{ $judul }}</h1>
                </div>

                {{-- Kolom Pencarian Cepat Produk + Profil Pengguna --}}
                <div class="flex items-center gap-3.5">
                    @if (in_array($peran, ['admin', 'gudang']))
                        <form method="GET" action="{{ route('produk.index') }}" class="hidden sm:flex items-center rounded-full bg-white border border-[#E6E8EC] px-3.5 py-1.5 w-60 focus-within:border-[#1F2428] transition">
                            <svg class="w-4 h-4 text-[#9CA3AF] mr-2 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" name="cari" placeholder="Cari produk / kode..." class="w-full bg-transparent text-[13px] text-[#181B1E] placeholder-[#9CA3AF] focus:outline-none">
                        </form>
                    @endif

                    <a href="{{ route('restok.index') }}" title="Notifikasi Stok Menipis"
                       class="relative inline-flex h-9 w-9 items-center justify-center rounded-full bg-white border border-[#E6E8EC] text-[#4B5563] hover:text-[#181B1E] transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </a>

                    <div class="flex items-center gap-2.5 rounded-full bg-white border border-[#E6E8EC] py-1 pl-1.5 pr-3">
                        <div class="h-7 w-7 rounded-full bg-[#1F2428] text-white text-[11px] font-bold flex items-center justify-center uppercase">
                            {{ substr($user?->username ?? 'U', 0, 2) }}
                        </div>
                        <div class="hidden md:block leading-tight">
                            <p class="text-[12px] font-semibold text-[#181B1E]">{{ $user?->karyawan?->nama ?? $user?->username }}</p>
                            <p class="text-[10px] text-[#6B7280] capitalize">{{ $peran }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 {{ $layarPenuh ? 'p-3' : 'px-5 lg:px-8 pb-8 pt-2 max-w-[1360px] w-full mx-auto' }}">
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-toast />
</body>
</html>
