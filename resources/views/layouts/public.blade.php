<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Turnos Ciudadanos')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="flex min-h-full flex-col bg-slate-100 text-slate-900 antialiased">
    <header class="bg-[#0b2b45] text-white shadow-lg">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <a href="{{ route('kiosk.index') }}" class="flex items-center gap-3">
                <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-400/15 text-emerald-300 ring-1 ring-emerald-400/40">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V6a2 2 0 0 1 2-2h1a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2m0-4h4m8-8V6a2 2 0 0 1 2-2h1a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-2m0-4h4"/>
                    </svg>
                </span>
                <span>
                    <span class="block text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">Municipalidad</span>
                    <span class="block text-lg font-semibold leading-tight">Turnos Ciudadanos</span>
                </span>
            </a>

            <nav class="flex flex-wrap items-center gap-1 text-sm">
                <a href="{{ route('kiosk.index') }}" class="rounded-lg px-3 py-2 transition hover:bg-white/10 {{ request()->routeIs('kiosk.*', 'turns.*') ? 'bg-white/10 font-semibold' : '' }}">Sacar turno</a>
                <a href="{{ route('display') }}" class="rounded-lg px-3 py-2 transition hover:bg-white/10 {{ request()->routeIs('display*') ? 'bg-white/10 font-semibold' : '' }}">Pantalla de sala</a>
                @auth
                    <a href="{{ route('agent.index') }}" class="rounded-lg px-3 py-2 transition hover:bg-white/10 {{ request()->routeIs('agent.*') ? 'bg-white/10 font-semibold' : '' }}">Consola de agente</a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.index') }}" class="rounded-lg px-3 py-2 transition hover:bg-white/10 {{ request()->routeIs('admin.*') ? 'bg-white/10 font-semibold' : '' }}">Administración</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-white/20 px-3 py-2 text-white/80 transition hover:bg-white/10">Salir</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg border border-white/20 px-3 py-2 text-white/80 transition hover:bg-white/10">Acceso funcionarios</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white/60 py-4 text-center text-xs text-slate-500">
        Sistema de turnos virtuales y gestión de colas en tiempo real · Atención al ciudadano
    </footer>

    @stack('scripts')
</body>
</html>
