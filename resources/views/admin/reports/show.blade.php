@extends('layouts.admin')

@section('title', 'Detail Laporan #' . $report->id)
@section('page_title', 'Detail Laporan #' . $report->id)

@section('content')
<div class="space-y-6 max-w-6xl">
    <!-- Back Button & Header Summary -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-800 font-medium transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Laporan
        </a>

        <!-- Current Status Badge -->
        <div>
            @if ($report->status === 'pending')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Status: Menunggu Verifikasi (Pending)
                </span>
            @elseif ($report->status === 'in_progress')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> Status: Sedang Ditangani (In Progress)
                </span>
            @elseif ($report->status === 'completed')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Status: Selesai Ditangani (Completed)
                </span>
            @elseif ($report->status === 'rejected')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Status: Laporan Ditolak (Rejected)
                </span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Report Content (2 Columns) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Details Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Judul Laporan</span>
                    <h2 class="text-xl font-bold text-slate-900 mt-1">{{ $report->title }}</h2>
                </div>

                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Deskripsi Masalah</span>
                    <p class="text-sm text-slate-700 mt-1.5 leading-relaxed whitespace-pre-line">{{ $report->description }}</p>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Lokasi / Alamat</span>
                    <p class="text-sm text-slate-800 font-medium mt-1">{{ $report->location_address }}</p>

                    @if ($report->latitude && $report->longitude)
                        <div class="mt-3 flex items-center gap-3">
                            <span class="text-xs text-slate-500 font-mono">
                                Koordinat: {{ $report->latitude }}, {{ $report->longitude }}
                            </span>
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $report->latitude }},{{ $report->longitude }}"
                               target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 text-xs text-emerald-600 hover:text-emerald-700 font-medium hover:underline">
                                📍 Buka di Google Maps &rarr;
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Foto Sampah -->
                <div class="pt-4 border-t border-slate-100">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-3">Foto Bukti Sampah</span>
                    @if ($report->image_path)
                        <div class="rounded-xl overflow-hidden border border-slate-200 max-w-lg bg-slate-50">
                            <img src="{{ Storage::url($report->image_path) }}" alt="Foto Sampah" class="w-full h-auto object-cover max-h-96">
                        </div>
                    @else
                        <div class="p-8 rounded-xl bg-slate-50 border border-dashed border-slate-300 text-center text-slate-400 text-sm">
                            Tidak ada foto yang dilampirkan pada laporan ini.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Existing Admin Notes If Any -->
            @if ($report->admin_notes)
                <div class="bg-amber-50/50 rounded-2xl border border-amber-200/80 p-5 shadow-xs">
                    <span class="text-xs font-semibold text-amber-800 uppercase tracking-wider block mb-1">Catatan Tindak Lanjut Terakhir</span>
                    <p class="text-sm text-amber-900 leading-relaxed">{{ $report->admin_notes }}</p>
                </div>
            @endif
        </div>

        <!-- Sidebar Actions & Reporter Info (1 Column) -->
        <div class="space-y-6">
            <!-- Update Status Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <h3 class="text-base font-bold text-slate-900 mb-4">Tindak Lanjut & Update Status</h3>

                <form method="POST" action="{{ route('admin.reports.status', $report) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Ubah Status Laporan</label>
                        <select id="status" name="status" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none text-sm bg-white font-medium">
                            <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Menunggu (Pending)</option>
                            <option value="in_progress" {{ $report->status === 'in_progress' ? 'selected' : '' }}>Dalam Penanganan (In Progress)</option>
                            <option value="completed" {{ $report->status === 'completed' ? 'selected' : '' }}>Selesai Diangkut (Completed)</option>
                            <option value="rejected" {{ $report->status === 'rejected' ? 'selected' : '' }}>Tolak Laporan (Rejected)</option>
                        </select>
                    </div>

                    <div>
                        <label for="admin_notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Catatan / Alasan untuk Pengguna</label>
                        <textarea id="admin_notes" name="admin_notes" rows="3"
                                  placeholder="Contoh: Petugas sedang menuju ke lokasi, atau alasan jika ditolak..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none text-sm">{{ old('admin_notes', $report->admin_notes) }}</textarea>
                        <span class="text-[11px] text-slate-400">Catatan ini akan dilihat oleh pengguna di aplikasi mobile.</span>
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm transition">
                        Simpan Perubahan
                    </button>
                </form>
            </div>

            <!-- Reporter Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                <h3 class="text-base font-bold text-slate-900">Informasi Pelapor</h3>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center font-bold text-lg">
                        {{ strtoupper(substr($report->user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 text-sm">{{ $report->user->name ?? 'Pengguna' }}</p>
                        <span class="text-xs text-slate-400 block">{{ $report->user->email ?? '-' }}</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 text-xs space-y-2 text-slate-600">
                    <div class="flex justify-between">
                        <span class="text-slate-400">No. Telepon:</span>
                        <span class="font-medium text-slate-800">{{ $report->user->phone ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Waktu Lapor:</span>
                        <span class="font-medium text-slate-800">{{ $report->created_at->translatedFormat('d M Y, H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Delete Action -->
            <div class="pt-2">
                <form method="POST" action="{{ route('admin.reports.destroy', $report) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan ini secara permanen?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2 px-4 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition">
                        Hapus Laporan Ini
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
