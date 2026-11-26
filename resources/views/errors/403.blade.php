<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>403 — Akses Ditolak</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-[var(--latar)] flex items-center justify-center p-4">
    <div class="max-w-md rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-6 text-center">
        <p class="text-xs font-semibold uppercase tracking-wider text-[var(--bahaya)]">Kode 403</p>
        <h1 class="mt-1 text-[20px] font-semibold text-[var(--teks)]">Akses Tidak Diizinkan</h1>
        <p class="mt-2 text-[14px] text-[var(--teks-redup)]">Peran akun Anda tidak memiliki izin untuk membuka halaman atau menjalankan tindakan ini. Kejadian ini telah dicatat di log audit.</p>
        <a href="{{ url('/dasbor') }}" class="mt-5 inline-block rounded-[3px] bg-[var(--primer)] px-4 py-2 text-sm font-medium text-white">Kembali ke Dasbor</a>
    </div>
</body>
</html>
