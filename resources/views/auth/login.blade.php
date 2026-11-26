<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — {{ config('toko.nama') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .bg-foto-utama {
            background-color: #074e5b;
            background-image: url("{{ asset('images/bg-login.jpg') }}");
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
        }

        /* Kapsul Input dengan Transisi Hover & Focus yang Halus */
        .pill-input-wrap {
            background-color: #BCEEEB;
            border: 1.5px solid transparent;
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
        }
        .pill-input-wrap:hover {
            background-color: #a8e7e3;
            border-color: rgba(44, 197, 188, 0.45);
        }
        .pill-input-wrap:focus-within {
            background-color: #FFFFFF;
            border-color: #2CC5BC;
            box-shadow: 0 0 0 4px rgba(44, 197, 188, 0.22), 0 4px 12px rgba(15, 41, 58, 0.08);
            transform: translateY(-1px);
        }
        .pill-input-wrap .ikon-field {
            color: #ffffff;
            transition: color 0.2s ease;
        }
        .pill-input-wrap:hover .ikon-field {
            color: #167c76;
        }
        .pill-input-wrap:focus-within .ikon-field {
            color: #2CC5BC;
        }
        .pill-input-field:focus-visible {
            box-shadow: none !important;
            outline: none !important;
        }
    </style>
</head>
<body class="min-h-screen bg-foto-utama relative flex items-center justify-center p-4 sm:p-8">
    {{-- Lapisan gelap tipis di latar luar agar kartu utama menonjol --}}
    <div class="fixed inset-0 bg-[#03232b]/25 backdrop-blur-[1px] pointer-events-none"></div>

    {{-- Kartu Utama Split-Screen dengan Efek Sobekan Kertas Organik --}}
    <div x-data="{
            lihatSandi: false,
            username: @js(old('username', '')),
            pilihPeran(user) {
                this.username = user;
                this.$nextTick(() => this.$refs.inputSandi.focus());
            }
         }"
         class="relative z-10 w-full max-w-[920px] min-h-[520px] rounded-[4px] overflow-hidden flex flex-col md:flex-row"
         style="box-shadow: 0 28px 60px -12px rgba(2, 20, 26, 0.72);">

        {{-- Panel Kiri: Area Form Putih + Tepi Sobekan Kertas Organik --}}
        <div class="relative w-full md:w-[43%] bg-white px-8 sm:px-10 py-12 flex flex-col justify-center z-20">
            {{-- SVG Tepi Sobekan Kertas Organik di Sisi Kanan Panel Putih --}}
            <svg class="hidden md:block absolute top-0 -right-[42px] h-full w-[43px] text-white pointer-events-none select-none drop-shadow-[3px_0_4px_rgba(0,0,0,0.18)]"
                 viewBox="0 0 44 520" preserveAspectRatio="none" fill="currentColor" aria-hidden="true">
                <path d="M0,0 L14,0 C17,9 11,17 16,25 C22,33 13,42 19,51 C26,60 15,69 21,78 C28,88 18,96 24,106 C30,115 16,124 22,134 C29,144 19,153 27,163 C35,173 21,182 28,192 C34,202 18,211 25,221 C32,231 20,241 28,251 C37,261 23,271 31,281 C39,291 22,301 29,311 C36,321 19,331 26,341 C33,351 21,361 29,371 C38,381 24,391 31,401 C37,411 20,421 27,431 C34,441 18,451 25,461 C32,471 19,481 26,491 C33,501 21,511 24,520 L0,520 Z" />
            </svg>

            <div class="w-full max-w-[275px] mx-auto md:mx-0">
                <h1 class="text-[26px] font-bold tracking-wide text-[#0f293a] uppercase mb-7">
                    MASUK
                </h1>

                <form method="POST" action="{{ route('login.proses') }}" class="space-y-4">
                    @csrf

                    {{-- Input Pill Username --}}
                    <div>
                        <label for="username" class="sr-only">Username</label>
                        <div class="pill-input-wrap flex items-center rounded-full px-4 py-2.5">
                            <svg class="ikon-field w-4 h-4 shrink-0 mr-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                x-model="username"
                                autofocus
                                autocomplete="username"
                                placeholder="username"
                                class="pill-input-field w-full bg-transparent text-[13.5px] font-medium text-[#0f293a] placeholder-[#3b6e75] focus:outline-none"
                                required
                            >
                        </div>
                        @error('username')
                            <p class="mt-1.5 pl-3 text-[11.5px] font-medium text-[#B03A2E]">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Input Pill Password + Toggle Show/Hide --}}
                    <div>
                        <label for="password" class="sr-only">Kata Sandi</label>
                        <div class="pill-input-wrap flex items-center rounded-full px-4 py-2.5">
                            <svg class="ikon-field w-4 h-4 shrink-0 mr-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <input
                                id="password"
                                name="password"
                                x-ref="inputSandi"
                                :type="lihatSandi ? 'text' : 'password'"
                                autocomplete="current-password"
                                placeholder="kata sandi"
                                class="pill-input-field w-full bg-transparent text-[13.5px] font-medium text-[#0f293a] placeholder-[#3b6e75] focus:outline-none"
                                required
                            >
                            <button
                                type="button"
                                @click="lihatSandi = !lihatSandi"
                                class="ml-2 text-[#3b6e75] hover:text-[#0f293a] focus:outline-none cursor-pointer"
                                :title="lihatSandi ? 'Sembunyikan sandi' : 'Lihat sandi'"
                            >
                                <svg x-show="!lihatSandi" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="lihatSandi" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 pl-3 text-[11.5px] font-medium text-[#B03A2E]">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Checkbox Pernyataan Operasional --}}
                    <label class="flex items-center gap-2.5 pt-1 cursor-pointer select-none group">
                        <input
                            type="checkbox"
                            name="ingat_sesi"
                            checked
                            class="h-4 w-4 rounded-[3px] border-0 accent-[#2CC5BC] cursor-pointer"
                        >
                        <span class="text-[11.5px] text-[#55646E] group-hover:text-[#0f293a] transition-colors">
                            Saya masuk sesuai <span class="text-[#239e97] underline">shift & peran tugas</span>
                        </span>
                    </label>

                    {{-- Tombol Pill Masuk --}}
                    <div class="pt-2">
                        <button
                            type="submit"
                            class="rounded-full bg-[#2CC5BC] hover:bg-[#23aba3] hover:shadow-md hover:-translate-y-[1px] active:translate-y-0 px-9 py-2.5 text-[12px] font-bold tracking-wider text-white uppercase shadow-sm transition-all duration-150 cursor-pointer"
                        >
                            MASUK
                        </button>
                    </div>
                </form>

                {{-- Pintasan Isi Cepat Username Berdasarkan Peran --}}
                <div class="mt-8 pt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-2.5">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400">Pilih Cepat Username</p>
                        <span x-show="username" x-cloak class="text-[10.5px] font-medium text-[#239e97]" x-text="'Akun: ' + username"></span>
                    </div>
                    <div class="flex items-center gap-2.5 text-[11px] font-bold text-white">
                        <button
                            type="button"
                            @click="pilihPeran('admin')"
                            :class="username === 'admin' ? 'ring-2 ring-offset-2 ring-[#3b5998] scale-110' : 'opacity-90 hover:opacity-100 hover:scale-110'"
                            class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#3b5998] shadow-xs transition-all cursor-pointer"
                            title="Isi username: admin (Admin)"
                        >A</button>
                        <button
                            type="button"
                            @click="pilihPeran('kasir')"
                            :class="username === 'kasir' ? 'ring-2 ring-offset-2 ring-[#db4437] scale-110' : 'opacity-90 hover:opacity-100 hover:scale-110'"
                            class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#db4437] shadow-xs transition-all cursor-pointer"
                            title="Isi username: kasir (Kasir)"
                        >K</button>
                        <button
                            type="button"
                            @click="pilihPeran('gudang')"
                            :class="username === 'gudang' ? 'ring-2 ring-offset-2 ring-[#38a1f3] scale-110' : 'opacity-90 hover:opacity-100 hover:scale-110'"
                            class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#38a1f3] shadow-xs transition-all cursor-pointer"
                            title="Isi username: gudang (Gudang)"
                        >G</button>
                        <button
                            type="button"
                            @click="pilihPeran('keuangan')"
                            :class="username === 'keuangan' ? 'ring-2 ring-offset-2 ring-[#2F7D52] scale-110' : 'opacity-90 hover:opacity-100 hover:scale-110'"
                            class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#2F7D52] shadow-xs transition-all cursor-pointer"
                            title="Isi username: keuangan (Keuangan)"
                        >U</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel Kanan: Gambar Fotografi Toko Ritel + Tipografi Sudut Kanan Bawah --}}
        <div class="relative flex-1 min-h-[240px] md:min-h-full bg-foto-utama flex flex-col justify-end p-8 sm:p-10 text-white overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-t from-[#03262e]/80 via-transparent to-black/15 pointer-events-none"></div>

            <div class="relative z-10 text-right mt-auto">
                <h2 class="text-[24px] sm:text-[28px] font-extrabold tracking-wide uppercase leading-tight drop-shadow-[0_2px_6px_rgba(0,0,0,0.65)]">
                    {{ config('toko.nama') }}
                </h2>
                <p class="text-[13px] sm:text-[15px] font-semibold tracking-widest uppercase text-white/95 mt-0.5 drop-shadow-[0_1px_4px_rgba(0,0,0,0.65)]">
                    SISTEM MANAJEMEN RITEL & STOK
                </p>
            </div>
        </div>
    </div>
</body>
</html>
