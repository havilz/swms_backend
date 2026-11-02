@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')
@section('page_title', 'Manajemen Pengguna')

@section('content')
<div class="space-y-6">
    <!-- Action Bar & Filters -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex-1 flex flex-col sm:flex-row gap-3">
                <!-- Search -->
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama, email, atau telepon..."
                           class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none text-sm">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- Role Filter -->
                <div class="w-full sm:w-44">
                    <select name="role" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none text-sm bg-white">
                        <option value="">Semua Peran</option>
                        <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Warga / Pengguna</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="py-2 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-medium text-sm transition">
                        Cari
                    </button>
                    @if (request()->hasAny(['search', 'role']))
                        <a href="{{ route('admin.users.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-xs transition whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Pengguna
            </a>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/75 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Nama & Email</th>
                        <th class="px-6 py-3.5">Telepon</th>
                        <th class="px-6 py-3.5">Role</th>
                        <th class="px-6 py-3.5">Laporan Masuk</th>
                        <th class="px-6 py-3.5">Terdaftar Sejak</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-900">{{ $user->name }}</div>
                                <div class="text-xs text-slate-400">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                                {{ $user->phone ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($user->isAdmin())
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                        Administrator
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        Pengguna
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                                <span class="font-semibold text-slate-900">{{ $user->reports_count ?? 0 }}</span> laporan
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                                {{ $user->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 text-xs font-medium transition">
                                    Edit
                                </a>

                                @if (auth()->id() !== $user->id)
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 text-xs font-medium transition">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                Tidak ada data pengguna yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
