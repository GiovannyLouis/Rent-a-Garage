<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Penyewaan Garasi')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <header class="bg-slate-900 text-white shadow">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-4 px-4 py-3.5">
            <a href="{{ route('home') }}" class="text-base font-bold tracking-tight text-white">
                Sistem Penyewaan Garasi
            </a>

            <nav class="flex flex-wrap items-center gap-4 text-sm">
                @auth
                    @if (auth()->user()->role === \App\Models\User::ROLE_ADMIN)
                        <a href="{{ route('admin.dashboard') }}" class="text-slate-200 hover:text-white">Dashboard</a>
                        <a href="{{ route('admin.verifications.index') }}" class="text-slate-200 hover:text-white">Verifikasi Penyewa</a>
                        <a href="{{ route('admin.garages.index') }}" class="text-slate-200 hover:text-white">Kelola Garasi</a>
                    @else
                        <a href="{{ route('penyewa.dashboard') }}" class="text-slate-200 hover:text-white">Dashboard</a>
                    @endif

                    <span class="border-l border-slate-700 pl-4 text-slate-300">
                        {{ auth()->user()->nama }}
                        <span class="ml-1 rounded bg-slate-700 px-1.5 py-0.5 text-xs font-semibold uppercase text-slate-200">
                            {{ auth()->user()->role }}
                        </span>
                    </span>

                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="cursor-pointer rounded bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-slate-200 hover:text-white">Login</a>
                    <a href="{{ route('register') }}" class="rounded bg-blue-600 px-3 py-1.5 font-medium text-white hover:bg-blue-700">
                        Daftar
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        @if (session('success'))
            <div class="mb-5 rounded-md border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @yield('content')
        </div>
    </main>
</body>
</html>