<x-app-layout judul="Log Aktivitas Audit">
    <div class="mb-5">
        <h2 class="text-[18px] font-semibold text-[var(--teks)]">Log Aktivitas Sistem</h2>
        <p class="text-[13px] text-[var(--teks-redup)]">Catatan audit permanen seluruh aksi penting dan upaya akses ditolak.</p>
    </div>

    <form method="GET" action="{{ route('log.index') }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-3.5">
        <div>
            <label class="block text-xs font-medium text-[var(--teks-redup)] mb-1">Pengguna</label>
            <select name="user_id" class="input-kontrol w-44">
                <option value="">Semua Pengguna</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->username }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-[var(--teks-redup)] mb-1">Kata Kunci Aksi</label>
            <input type="text" name="aksi" value="{{ request('aksi') }}" placeholder="Contoh: akses_ditolak" class="input-kontrol w-52">
        </div>
        <button type="submit" class="btn-primer">Filter</button>
        <a href="{{ route('log.index') }}" class="btn-sekunder">Reset</a>
    </form>

    <x-tabel>
        <x-slot:head>
            <tr>
                <th class="px-4 py-2.5">Waktu</th>
                <th class="px-4 py-2.5">Pengguna</th>
                <th class="px-4 py-2.5">Aksi</th>
                <th class="px-4 py-2.5">Entitas</th>
                <th class="px-4 py-2.5">Detail</th>
            </tr>
        </x-slot:head>

        @forelse ($logs as $log)
            <tr class="h-[40px] hover:bg-[var(--primer-muda)]/50">
                <td class="px-4 py-2 whitespace-nowrap text-[13px] text-[var(--teks-redup)]">{{ $log->waktu->format('d/m/Y H:i:s') }}</td>
                <td class="px-4 py-2 font-medium">{{ $log->user?->username ?? 'Sistem' }}</td>
                <td class="px-4 py-2">
                    <span class="font-mono text-[12px] {{ $log->aksi === 'akses_ditolak' ? 'text-[var(--bahaya)] font-semibold' : 'text-[var(--teks)]' }}">
                        {{ $log->aksi }}
                    </span>
                </td>
                <td class="px-4 py-2 text-[13px]">{{ $log->entitas }}{{ $log->entitas_id ? " #{$log->entitas_id}" : '' }}</td>
                <td class="px-4 py-2 font-mono text-[12px] text-[var(--teks-redup)]">{{ $log->detail ? json_encode($log->detail) : '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="p-4 text-center text-[var(--teks-redup)]">Belum ada catatan aktivitas.</td>
            </tr>
        @endforelse
    </x-tabel>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</x-app-layout>
