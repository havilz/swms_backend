@extends('layouts.admin')

@section('title', 'Tambah Pengguna')
@section('page_title', 'Tambah Pengguna Baru')

@section('content')
<div class="max-w-2xl">
    <div class="mb-4">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-800 font-medium">
            &larr; Kembali ke Daftar Pengguna
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       placeholder="Contoh: Ahmad Fauzi"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none text-sm">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           placeholder="user@example.com"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none text-sm">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Nomor Telepon (WhatsApp)</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                           placeholder="0812xxxxxxxx"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="role" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Peran Akun</label>
                    <select id="role" name="role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none text-sm bg-white font-medium">
                        <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>Warga / Pengguna Biasa</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Kata Sandi Awal</label>
                    <input type="password" id="password" name="password" required minlength="8"
                           placeholder="Minimal 8 karakter"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none text-sm">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-sm font-medium transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm transition">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
