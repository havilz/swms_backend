<!DOCTYPE html>
<html lang="id" class="h-full bg-[#e8eee7]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Administrator - SWMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full bg-[#e8eee7] text-slate-900 antialiased selection:bg-emerald-200 selection:text-emerald-950">
    <main class="grid min-h-screen lg:grid-cols-[minmax(280px,0.8fr)_minmax(460px,1.2fr)]">
        <section class="relative flex min-h-[290px] flex-col justify-between overflow-hidden bg-[#17392d] px-6 py-7 text-[#eef4eb] sm:px-10 lg:min-h-screen lg:px-14 lg:py-12" aria-labelledby="brand-title">
            <div class="relative z-10 flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#c3d8b9] text-sm font-bold tracking-tight text-[#17392d]">SW</span>
                <div>
                    <p class="text-base font-bold tracking-tight">SWMS</p>
                    <p class="text-xs text-[#b8cdb7]">Smart Waste Admin</p>
                </div>
            </div>

            <div class="relative z-10 mt-14 max-w-md lg:mt-0">
                <p class="mb-5 text-sm font-medium text-[#b8d4b6]">Ruang kendali laporan warga</p>
                <h1 id="brand-title" class="max-w-sm text-4xl font-semibold leading-[1.05] tracking-[-0.04em] text-[#f3f7f0] sm:text-5xl">Jaga kota tetap bersih, satu laporan pada satu waktu.</h1>
                <p class="mt-6 max-w-sm text-sm leading-7 text-[#c4d5c3]">Tinjau laporan, pantau pekerjaan petugas, dan berikan pembaruan yang jelas kepada warga.</p>
            </div>

            <div class="relative z-10 mt-12 grid max-w-md grid-cols-3 gap-4 border-t border-[#547062] pt-4 text-xs text-[#c4d5c3] lg:mt-0">
                <div>
                    <span class="block text-[#f3f7f0]">Laporan</span>
                    <span class="mt-1 block">Masuk dan ditinjau</span>
                </div>
                <div>
                    <span class="block text-[#f3f7f0]">Status</span>
                    <span class="mt-1 block">Terlihat jelas</span>
                </div>
                <div>
                    <span class="block text-[#f3f7f0]">Tindakan</span>
                    <span class="mt-1 block">Terarah dan tercatat</span>
                </div>
            </div>
        </section>

        <section class="flex items-center justify-center bg-[#e8eee7] px-5 py-10 sm:px-10 lg:px-16" aria-labelledby="login-title">
            <div class="w-full max-w-md">
                <div class="mb-8">
                    <p class="text-sm font-medium text-[#437052]">Portal administrator</p>
                    <h2 id="login-title" class="mt-2 text-3xl font-semibold tracking-[-0.03em] text-[#17392d]">Masuk ke dashboard</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Gunakan akun administrator untuk mengelola laporan sampah.</p>
                </div>

                <div class="rounded-2xl border border-[#cbd8c8] bg-[#f8fbf6] p-6 sm:p-8">
                    @if (session('success'))
                        <div class="mb-5 border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-5 border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-[#244333]">Email administrator</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                   class="mt-2 w-full rounded-lg border border-[#b9cbb8] bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#437052] focus:ring-2 focus:ring-[#437052]/20"
                                   placeholder="admin@swms.test">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-[#244333]">Kata sandi</label>
                            <input type="password" id="password" name="password" required autocomplete="current-password"
                                   class="mt-2 w-full rounded-lg border border-[#b9cbb8] bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#437052] focus:ring-2 focus:ring-[#437052]/20"
                                   placeholder="Masukkan kata sandi">
                        </div>

                        <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-[#a9bea9] text-[#2f6848] focus:ring-[#437052]">
                            Ingat saya di perangkat ini
                        </label>

                        <button type="submit" class="w-full rounded-lg bg-[#244c38] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#17392d] focus:outline-none focus:ring-2 focus:ring-[#437052] focus:ring-offset-2">
                            Masuk ke dashboard
                        </button>
                    </form>
                </div>

                <p class="mt-6 text-center text-xs text-slate-500">Akses ini hanya untuk administrator SWMS.</p>
            </div>
        </section>
    </main>
</body>
</html>
