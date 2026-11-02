<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f3f6f3]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - SWMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full text-slate-900 antialiased">
    <div class="min-h-screen bg-[#f3f6f3] lg:flex lg:h-screen lg:gap-2 lg:overflow-hidden lg:p-2">
        <aside class="hidden w-[228px] shrink-0 flex-col rounded-xl bg-[#17392d] px-3 py-3 text-[#eef4eb] lg:flex lg:h-full">
            <div class="flex h-12 items-center px-3">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3" aria-label="SWMS dashboard">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#c3d8b9] text-sm font-bold tracking-tight text-[#17392d]">SW</span>
                    <span>
                        <span class="block text-base font-bold tracking-tight text-white">SWMS</span>
                        <span class="block text-[11px] text-[#b8cdb7]">Smart Waste Admin</span>
                    </span>
                </a>
            </div>

            <nav class="mt-8 flex flex-1 flex-col gap-1" aria-label="Navigasi utama">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex min-h-10 items-center gap-3 rounded-lg px-3 text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#c3d8b9] text-[#17392d]' : 'text-[#c4d5c3] hover:bg-[#244c38] hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 12l9-9 9 9M5 10v10h14V10"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.reports.index') }}"
                   class="flex min-h-10 items-center gap-3 rounded-lg px-3 text-sm font-medium transition {{ request()->routeIs('admin.reports.*') ? 'bg-[#c3d8b9] text-[#17392d]' : 'text-[#c4d5c3] hover:bg-[#244c38] hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6 4h12v16H6zM9 8h6M9 12h6M9 16h3"/>
                    </svg>
                    Laporan sampah
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="flex min-h-10 items-center gap-3 rounded-lg px-3 text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-[#c3d8b9] text-[#17392d]' : 'text-[#c4d5c3] hover:bg-[#244c38] hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16 20H4v-1a6 6 0 0112 0v1zm-6-9a4 4 0 100-8 4 4 0 000 8zm6-5h4m-2-2v4"/>
                    </svg>
                    Manajemen user
                </a>
            </nav>

            <div class="border-t border-[#547062] px-3 pt-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#c3d8b9] text-sm font-semibold text-[#17392d]">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-semibold text-white">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="truncate text-[11px] text-[#b8cdb7]">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-xs font-medium text-[#b8cdb7] transition hover:bg-[#244c38] hover:text-white">
                        Keluar dari dashboard
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-h-screen min-w-0 flex-1 flex-col bg-[#f7f8f2] lg:h-full lg:min-h-0 lg:rounded-2xl lg:border lg:border-[#d8e0d5]">
            <header class="flex min-h-14 items-center justify-between gap-4 bg-[#f3f6f3] px-4 sm:px-6 lg:bg-[#f7f8f2] lg:px-8" role="banner">
                <div class="flex min-w-0 items-center gap-3">
                    <details class="relative lg:hidden">
                        <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700" aria-label="Buka menu navigasi">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </summary>
                        <nav class="absolute left-0 top-12 z-20 w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-lg" aria-label="Navigasi mobile">
                            <a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-3 py-3 text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-slate-100 text-slate-900' : 'text-slate-600' }}">Dashboard</a>
                            <a href="{{ route('admin.reports.index') }}" class="block rounded-lg px-3 py-3 text-sm font-medium {{ request()->routeIs('admin.reports.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600' }}">Laporan sampah</a>
                            <a href="{{ route('admin.users.index') }}" class="block rounded-lg px-3 py-3 text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600' }}">Manajemen user</a>
                        </nav>
                    </details>
                    <h1 class="truncate text-lg font-semibold tracking-tight text-slate-900 sm:text-xl">@yield('page_title', 'Dashboard')</h1>
                </div>
                <div class="hidden items-center gap-3 text-right sm:flex">
                    <span class="text-xs text-slate-500">{{ auth()->user()->name ?? 'Administrator' }}</span>
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600" aria-hidden="true"></span>
                    <span class="text-xs font-medium text-slate-700">Online</span>
                </div>
            </header>

            <main class="min-h-0 flex-1 overflow-y-auto px-4 pb-8 pt-5 sm:px-6 lg:px-8">
                @if (session('success'))
                    <div class="mb-5 flex items-start gap-3 border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 12l4 4L19 6"/>
                        </svg>
                        <p class="font-medium">{{ session('success') }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-5 flex items-start gap-3 border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert">
                        <p class="font-medium">{{ session('error') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="alert">
                        <p class="font-semibold">Periksa kembali input Anda.</p>
                        <ul class="mt-1 list-disc space-y-0.5 pl-5 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
