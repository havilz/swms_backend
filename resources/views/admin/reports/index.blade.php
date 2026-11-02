@extends('layouts.admin')

@section('title', 'Daftar Laporan')
@section('page_title', 'Kelola Laporan Sampah')

@section('content')
<div class="space-y-6">
    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label for="search" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Pencarian</label>
                <div class="relative">
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                           placeholder="Cari judul, lokasi, atau nama pelapor..."
                           class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none text-sm">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Filter Status</label>
                <select id="status" name="status" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none text-sm bg-white">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Diproses</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div>
                <label for="date_from" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none text-sm">
            </div>

            <div>
                <label for="date_to" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none text-sm">
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-xs transition">
                    Terapkan
                </button>
                @if (request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.reports.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium transition" title="Reset filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Reports Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/75 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">ID</th>
                        <th class="px-6 py-3.5">Foto</th>
                        <th class="px-6 py-3.5">Pelapor</th>
                        <th class="px-6 py-3.5">Judul & Lokasi</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($reports as $report)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 font-mono text-xs text-slate-400">
                                #{{ $report->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($report->image_path)
                                    <img src="{{ Storage::url($report->image_path) }}" alt="Foto Sampah" class="w-12 h-12 object-cover rounded-lg border border-slate-200 shadow-2xs">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 text-xs">
                                        No pic
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-900">{{ $report->user->name ?? 'Pengguna' }}</div>
                                <div class="text-xs text-slate-400">{{ $report->user->phone ?? $report->user->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <div class="font-medium text-slate-900 line-clamp-1">{{ $report->title }}</div>
                                <div class="text-xs text-slate-500 line-clamp-1 mt-0.5">{{ $report->location_address }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($report->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Pending
                                    </span>
                                @elseif ($report->status === 'in_progress')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        Diproses
                                    </span>
                                @elseif ($report->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Selesai
                                    </span>
                                @elseif ($report->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                                {{ $report->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                <a href="{{ route('admin.reports.show', $report) }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-medium text-xs transition">
                                    Detail & Status
                                </a>
                                <form method="POST" action="{{ route('admin.reports.destroy', $report) }}" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-2 py-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus laporan">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                Tidak ada laporan yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reports->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
