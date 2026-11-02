@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Ringkasan sistem')

@section('content')
<div class="space-y-6">
    <section class="flex flex-col gap-3 rounded-xl border border-[#cbd8c8] bg-[#e8eee7] p-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-medium text-emerald-700">Operasional laporan sampah</p>
            <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">Mulai dari laporan yang masih menunggu verifikasi, lalu lanjutkan antrean yang sedang diproses.</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="inline-flex min-h-9 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
            Buka semua laporan
        </a>
    </section>

    <section class="grid grid-cols-2 gap-3 xl:grid-cols-5" aria-label="Ringkasan laporan">
        <div class="min-h-28 rounded-xl border border-[#cbd8c8] border-t-4 border-t-[#527765] bg-[#f0f5ed] p-4 sm:p-5">
            <p class="text-sm text-slate-500">Semua laporan</p>
            <p class="mt-3 text-3xl font-semibold tabular-nums tracking-tight text-slate-900">{{ number_format($stats['total_reports']) }}</p>
            <p class="mt-1 text-xs text-slate-500">seluruh riwayat masuk</p>
        </div>
        <div class="min-h-28 rounded-xl border border-[#ead9a7] border-t-4 border-t-[#c59028] bg-[#fff8e5] p-4 sm:p-5">
            <p class="text-sm text-slate-500">Menunggu verifikasi</p>
            <p class="mt-3 text-3xl font-semibold tabular-nums tracking-tight text-amber-700">{{ number_format($stats['pending']) }}</p>
            <p class="mt-1 text-xs text-slate-500">perlu ditinjau admin</p>
        </div>
        <div class="min-h-28 rounded-xl border border-[#c4ddd7] border-t-4 border-t-[#397d73] bg-[#edf6f3] p-4 sm:p-5">
            <p class="text-sm text-slate-500">Sedang diproses</p>
            <p class="mt-3 text-3xl font-semibold tabular-nums tracking-tight text-sky-700">{{ number_format($stats['in_progress']) }}</p>
            <p class="mt-1 text-xs text-slate-500">berada di antrean petugas</p>
        </div>
        <div class="min-h-28 rounded-xl border border-[#bed8c0] border-t-4 border-t-[#3e8050] bg-[#edf7ed] p-4 sm:p-5">
            <p class="text-sm text-slate-500">Selesai</p>
            <p class="mt-3 text-3xl font-semibold tabular-nums tracking-tight text-emerald-700">{{ number_format($stats['completed']) }}</p>
            <p class="mt-1 text-xs text-slate-500">sudah ditangani</p>
        </div>
        <div class="col-span-2 min-h-28 rounded-xl border border-[#d2d8bf] border-t-4 border-t-[#788452] bg-[#f2f4e8] p-4 sm:p-5 xl:col-span-1">
            <p class="text-sm text-slate-500">Pengguna terdaftar</p>
            <p class="mt-3 text-3xl font-semibold tabular-nums tracking-tight text-slate-900">{{ number_format($stats['total_users']) }}</p>
            <p class="mt-1 text-xs text-slate-500">akun warga aktif di sistem</p>
        </div>
    </section>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
        <section class="min-w-0" aria-labelledby="recent-reports-title">
            <div class="mb-3 flex items-end justify-between gap-4">
                <div>
                    <h2 id="recent-reports-title" class="text-base font-semibold text-slate-900">Laporan terbaru</h2>
                    <p class="mt-1 text-sm text-slate-500">Antrean terbaru yang masuk ke sistem.</p>
                </div>
                <a href="{{ route('admin.reports.index') }}" class="shrink-0 text-sm font-medium text-emerald-700 hover:text-emerald-900 hover:underline">Lihat semua</a>
            </div>

            <div class="overflow-hidden rounded-xl border border-[#cbd8c8] bg-[#f8fbf6]">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[680px] text-left text-sm">
                        <thead class="border-b border-slate-200 text-xs font-medium text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Pelapor</th>
                                <th class="px-5 py-3">Laporan</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Masuk</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($recentReports as $report)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-5 py-4 align-top">
                                        <p class="font-medium text-slate-900">{{ $report->user->name ?? 'Pengguna' }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $report->user->email ?? '—' }}</p>
                                    </td>
                                    <td class="max-w-xs px-5 py-4 align-top">
                                        <p class="truncate font-medium text-slate-900">{{ $report->title }}</p>
                                        <p class="mt-1 truncate text-xs text-slate-500">{{ $report->location_address }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 align-top">
                                        @if ($report->status === 'pending')
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">Menunggu</span>
                                        @elseif ($report->status === 'in_progress')
                                            <span class="inline-flex rounded-full bg-sky-100 px-2.5 py-1 text-xs font-medium text-sky-800">Diproses</span>
                                        @elseif ($report->status === 'completed')
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">Selesai</span>
                                        @elseif ($report->status === 'rejected')
                                            <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-medium text-rose-800">Ditolak</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 align-top text-xs tabular-nums text-slate-500">{{ $report->created_at->translatedFormat('d M Y, H:i') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right align-top">
                                        <a href="{{ route('admin.reports.show', $report) }}" class="font-medium text-slate-700 underline decoration-slate-300 underline-offset-4 hover:text-emerald-700">Buka</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">Belum ada laporan sampah yang masuk.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <aside class="space-y-5" aria-label="Tindakan berikutnya">
            <section>
                <h2 class="text-base font-semibold text-slate-900">Tindakan berikutnya</h2>
                <p class="mt-1 text-sm text-slate-500">Antrean yang paling membutuhkan perhatian.</p>
            </section>

            <div class="rounded-xl border border-[#cbd8c8] bg-[#f8fbf6]">
                <a href="{{ route('admin.reports.index', ['status' => 'pending']) }}" class="flex items-start justify-between gap-4 border-b border-slate-100 p-4 transition hover:bg-slate-50">
                    <span>
                        <span class="block text-sm font-medium text-slate-900">Tinjau laporan baru</span>
                        <span class="mt-1 block text-xs leading-5 text-slate-500">Pastikan lokasi dan isi laporan sudah jelas.</span>
                    </span>
                    <span class="shrink-0 text-lg font-semibold tabular-nums text-amber-700">{{ number_format($stats['pending']) }}</span>
                </a>
                <a href="{{ route('admin.reports.index', ['status' => 'in_progress']) }}" class="flex items-start justify-between gap-4 p-4 transition hover:bg-slate-50">
                    <span>
                        <span class="block text-sm font-medium text-slate-900">Pantau pekerjaan berjalan</span>
                        <span class="mt-1 block text-xs leading-5 text-slate-500">Periksa laporan yang menunggu penyelesaian.</span>
                    </span>
                    <span class="shrink-0 text-lg font-semibold tabular-nums text-sky-700">{{ number_format($stats['in_progress']) }}</span>
                </a>
            </div>

            <div class="border-t border-slate-200 pt-4">
                <p class="text-xs leading-5 text-slate-500">Status ditetapkan melalui detail laporan. Gunakan catatan admin untuk memberi konteks yang dapat dibaca pengguna mobile.</p>
            </div>
        </aside>
    </div>
</div>
@endsection
