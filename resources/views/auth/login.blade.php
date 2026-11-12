@extends('layouts.public')

@section('title', 'Acceso funcionarios · Turnos Ciudadanos')

@section('content')
    <div class="mx-auto flex max-w-md flex-col justify-center px-4 py-14 sm:px-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl">
            <div class="flex size-12 items-center justify-center rounded-2xl bg-[#0b2b45] text-emerald-300">
                <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                </svg>
            </div>

            <h1 class="mt-5 text-2xl font-bold tracking-tight text-slate-900">Acceso de funcionarios</h1>
            <p class="mt-1 text-sm text-slate-600">Ingrese con su cuenta para gestionar la atención ciudadana.</p>

            @include('partials.flash')

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Correo institucional</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="mt-1.5 w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Contraseña</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" class="mt-1.5 w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    Recordarme en este dispositivo
                </label>

                <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">Ingresar</button>
            </form>

            @if(app()->environment('local'))
                <div class="mt-6 rounded-xl bg-slate-50 p-4 text-xs text-slate-600">
                    <p class="font-semibold text-slate-800">Cuentas de demostración</p>
                    <p class="mt-1">Administrador: admin@turnos.test · password</p>
                    <p>Agente: agente@turnos.test · password</p>
                </div>
            @endif
        </div>

        <a href="{{ route('kiosk.index') }}" class="mt-6 text-center text-sm font-semibold text-emerald-700 hover:text-emerald-800">← Volver al kiosco ciudadano</a>
    </div>
@endsection
