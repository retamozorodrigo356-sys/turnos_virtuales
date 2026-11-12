<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel de atención') · Turnos Ciudadanos</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-100 text-slate-900 antialiased">
    <header class="bg-[#0b2b45] text-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6">
            <a href="{{ route('agent.index') }}" class="flex items-center gap-2.5">
                <span class="flex size-8 items-center justify-center rounded-lg bg-emerald-400/15 text-emerald-300 ring-1 ring-emerald-400/40">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V6a2 2 0 0 1 2-2h1a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2m0-4h4m8-8V6a2 2 0 0 1 2-2h1a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-2m0-4h4"/>
                    </svg>
                </span>
                <span class="text-sm font-semibold tracking-wide">Turnos Ciudadanos</span>
            </a>

            <nav class="flex flex-wrap items-center gap-1 text-sm">
                <a href="{{ route('agent.index') }}" class="rounded-lg px-3 py-1.5 transition hover:bg-white/10 {{ request()->routeIs('agent.*') ? 'bg-white/10 font-semibold' : 'text-white/75' }}">Agente</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.index') }}" class="rounded-lg px-3 py-1.5 transition hover:bg-white/10 {{ request()->routeIs('admin.*') ? 'bg-white/10 font-semibold' : 'text-white/75' }}">Administración</a>
                @endif
                <a href="{{ route('display') }}" target="_blank" class="rounded-lg px-3 py-1.5 text-white/75 transition hover:bg-white/10">Pantalla</a>
                <a href="{{ route('kiosk.index') }}" class="rounded-lg px-3 py-1.5 text-white/75 transition hover:bg-white/10">Kiosco</a>
            </nav>

            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-medium leading-tight">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] uppercase tracking-wider text-emerald-300">{{ auth()->user()->isAdmin() ? 'Administrador' : 'Agente de atención' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-white/20 px-3 py-1.5 text-sm text-white/80 transition hover:bg-white/10">Salir</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        @include('partials.flash')
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
