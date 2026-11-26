<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>404 — Halaman Tidak Ditemukan</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-[var(--latar)] flex items-center justify-center p-4">
    <div class="max-w-md rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-6 text-center">
        <p class="text-xs font-semibold uppercase tracking-wider text-[var(--teks-redup)]">Kode 404</p>
        <h1 class="mt-1 text-[20px] font-semibold text-[var(--teks)]">Halaman Tidak Ditemukan</h1>
        <p class="mt-2 text-[14px] text-[var(--teks-redup)]">Alamat yang Anda buka tidak tersedia atau data dokumen yang dicari tidak ditemukan.</p>
        <a href="{{ url('/dasbor') }}" class="mt-5 inline-block rounded-[3px] bg-[var(--primer)] px-4 py-2 text-sm font-medium text-white">Kembali ke Dasbor</a>
    </div>
</body>
</html>
